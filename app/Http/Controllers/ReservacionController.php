<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use App\Models\Mesa;
use App\Models\Reservacion;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * @brief Controlador de gestión de reservaciones.
 *
 * Cubre los casos de uso:
 *   - Registrar reservación (Maitre y Cliente)
 *   - Asignar mesa (Maitre)
 *   - Verificar disponibilidad de mesas (Maitre)
 *   - Validar cupo total (Maitre)
 *   - Consultar reservaciones próximas (Maitre)
 *   - Historial de reservaciones (Cliente)
 */
class ReservacionController extends Controller
{
    /*------------------------------------------------------------------
     | MAITRE — Registrar reservación
     ------------------------------------------------------------------*/

    /**
     * @brief Formulario para registrar una reservación (como Maitre).
     * @return View
     */
    public function create(): View
    {
        $clientes = Usuario::whereHas('roles', function ($q) {
            $q->where('nombre', Rol::CLIENTE);
        })->orderBy('nombre')->get();

        return view('reservaciones.create', compact('clientes'));
    }

    /**
     * @brief Almacena una nueva reservación.
     * @param  Request $request  Campos: cliente_id, cantidad
     * @return RedirectResponse
     * @pre  El cliente debe existir; la cantidad debe ser entre 1 y 20.
     * @post Se crea la reservación en estado Pendiente (0).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'cliente_id' => ['required', 'exists:usuarios,id_usuario'],
            'cantidad'   => ['required', 'integer', 'min:1', 'max:20'],
        ], [
            'cliente_id.required' => 'Debes seleccionar un cliente.',
            'cliente_id.exists'   => 'El cliente seleccionado no existe.',
            'cantidad.required'   => 'El número de personas es obligatorio.',
            'cantidad.min'        => 'Debe haber al menos 1 persona.',
            'cantidad.max'        => 'No puede superar 20 personas por reservación.',
        ]);

        Reservacion::create([
            'cliente_id' => $request->cliente_id,
            'cantidad'   => $request->cantidad,
            'estado'     => Reservacion::ESTADO_PENDIENTE,
        ]);

        return redirect()->route('reservaciones.proximas')
            ->with('success', 'Reservación registrada correctamente.');
    }

    /*------------------------------------------------------------------
     | MAITRE — Asignar mesa
     ------------------------------------------------------------------*/

    /**
     * @brief Muestra el formulario de asignación de mesa a reservaciones pendientes.
     * @return View
     */
    public function asignar(): View
    {
        $reservaciones = Reservacion::sinAsignar()
            ->with('cliente')
            ->orderBy('id_reservacion')
            ->get();

        // Para cada reservación calculamos las mesas disponibles con suficiente capacidad
        $mesasPorReservacion = [];
        foreach ($reservaciones as $reservacion) {
            $mesasPorReservacion[$reservacion->id_reservacion] = Mesa::disponibles()
                ->conCapacidad($reservacion->cantidad)
                ->orderBy('sillas')
                ->get();
        }

        return view('reservaciones.asignar', compact('reservaciones', 'mesasPorReservacion'));
    }

    /**
     * @brief Procesa la asignación de una mesa a una reservación.
     *
     * Valida que no se supere el cupo total del restaurante antes
     * de realizar la asignación.
     *
     * @param  Request $request  Campos: reservacion_id, mesa_id
     * @return RedirectResponse
     * @pre  La reservación debe estar pendiente/confirmada y la mesa disponible.
     * @post Se crea el horario y la reservación pasa a estado Asignada (2).
     */
    public function guardarAsignacion(Request $request): RedirectResponse
    {
        $request->validate([
            'reservacion_id' => ['required', 'exists:reservaciones,id_reservacion'],
            'mesa_id'        => ['required', 'exists:mesas,id_mesa'],
        ]);

        $reservacion = Reservacion::findOrFail($request->reservacion_id);
        $cupoTotal   = Mesa::capacidadTotal();

        // Asientos ya ocupados en la ventana actual de 2 horas
        $ocupados = (int) DB::table('horarios')
            ->join('reservaciones', 'horarios.reservacion_id', '=', 'reservaciones.id_reservacion')
            ->whereRaw("horarios.inicio BETWEEN NOW() - INTERVAL '2 hours' AND NOW() + INTERVAL '2 hours'")
            ->sum('reservaciones.cantidad');

        if (($ocupados + $reservacion->cantidad) > $cupoTotal) {
            return redirect()->route('reservaciones.asignar')
                ->withErrors([
                    'cupo' => "No se puede asignar: el restaurante superaría su cupo máximo de {$cupoTotal} personas.",
                ]);
        }

        DB::transaction(function () use ($request, $reservacion) {
            Horario::create([
                'mesa_id'        => $request->mesa_id,
                'reservacion_id' => $reservacion->id_reservacion,
                'inicio'         => now(),
                'duracion'       => '01:30:00',
            ]);

            $reservacion->update(['estado' => Reservacion::ESTADO_ASIGNADA]);
        });

        return redirect()->route('reservaciones.asignar')
            ->with('success', 'Mesa asignada correctamente.');
    }

    /*------------------------------------------------------------------
     | MAITRE — Verificar disponibilidad
     ------------------------------------------------------------------*/

    /**
     * @brief Muestra la disponibilidad de mesas para una fecha dada.
     * @param  Request $request  Query param: fecha (Y-m-d)
     * @return View
     */
    public function verificar(Request $request): View
    {
        $fecha = $request->get('fecha', now()->toDateString());

        $mesas = Mesa::with(['horarios' => function ($q) use ($fecha) {
            $q->whereRaw("DATE(inicio) = ?", [$fecha])
              ->with('reservacion.cliente');
        }])->orderBy('id_mesa')->get();

        return view('reservaciones.verificar', compact('mesas', 'fecha'));
    }

    /*------------------------------------------------------------------
     | MAITRE — Validar cupo total
     ------------------------------------------------------------------*/

    /**
     * @brief Muestra el panel de capacidad total del restaurante para hoy.
     * @return View
     */
    public function cupo(): View
    {
        $cupoTotal  = Mesa::capacidadTotal();
        $numMesas   = Mesa::count();
        $ocupadas   = (int) Horario::hoy()->distinct('mesa_id')->count('mesa_id');
        $libres     = $numMesas - $ocupadas;
        $porcentaje = $numMesas > 0 ? round($ocupadas / $numMesas * 100) : 0;

        $reservasHoy = Horario::hoy()
            ->with('mesa', 'reservacion.cliente')
            ->orderBy('inicio')
            ->get();

        return view('reservaciones.cupo', compact(
            'cupoTotal', 'numMesas', 'ocupadas', 'libres', 'porcentaje', 'reservasHoy'
        ));
    }

    /*------------------------------------------------------------------
     | MAITRE — Reservaciones próximas
     ------------------------------------------------------------------*/

    /**
     * @brief Lista las reservaciones próximas o pendientes de asignar.
     * @return View
     */
    public function proximas(): View
    {
        $reservaciones = Reservacion::with(['cliente', 'horario.mesa'])
            ->where(function ($q) {
                $q->whereHas('horario', fn($h) => $h->where('inicio', '>=', now()))
                  ->orWhereIn('estado', [
                      Reservacion::ESTADO_PENDIENTE,
                      Reservacion::ESTADO_CONFIRMADA,
                  ]);
            })
            ->orderByRaw("(SELECT inicio FROM horarios WHERE reservacion_id = reservaciones.id_reservacion LIMIT 1) ASC NULLS LAST")
            ->limit(30)
            ->get();

        // Notificación: reservaciones que empiezan en los próximos 30 minutos
        $porEmpezar = Horario::proximos(30)
            ->with('reservacion.cliente', 'mesa')
            ->get();

        return view('reservaciones.proximas', compact('reservaciones', 'porEmpezar'));
    }

    /*------------------------------------------------------------------
     | CLIENTE — Solicitar reservación
     ------------------------------------------------------------------*/

    /**
     * @brief Formulario para que el cliente solicite una reservación.
     * @return View
     */
    public function solicitar(): View
    {
        $mesas = Mesa::orderBy('id_mesa')->get();
        
        // Generar un listado de horarios de ejemplo para el dropdown
        $horarios = [
            '13:00', '13:30', '14:00', '14:30', '15:00',
            '15:30', '16:00', '16:30', '17:00', '17:30',
            '18:00', '18:30', '19:00', '19:30', '20:00',
            '20:30', '21:00', '21:30', '22:00'
        ];

        return view('reservaciones.solicitar', compact('mesas', 'horarios'));
    }

    /**
     * @brief Procesa la solicitud de reservación del cliente.
     * @param  Request $request  Campo: cantidad
     * @return RedirectResponse
     * @pre  El usuario en sesión debe ser Cliente.
     * @post Se crea la reservación en estado Pendiente asociada al cliente.
     */
    public function guardarSolicitud(Request $request): RedirectResponse
    {
        $request->validate([
            'cantidad' => ['required', 'integer', 'min:1', 'max:20'],
            'fecha'    => ['required', 'date', 'after_or_equal:today'],
            'hora'     => ['required'],
            'mesa_id'  => ['required', 'exists:mesas,id_mesa'],
        ]);

        DB::transaction(function () use ($request) {
            $reservacion = Reservacion::create([
                'cliente_id' => session('usuario_id'),
                'cantidad'   => $request->cantidad,
                'estado'     => Reservacion::ESTADO_PENDIENTE,
            ]);

            Horario::create([
                'mesa_id'        => $request->mesa_id,
                'reservacion_id' => $reservacion->id_reservacion,
                'inicio'         => $request->fecha . ' ' . $request->hora . ':00',
                'duracion'       => '01:30:00',
            ]);
        });

        return redirect()->route('cliente.reservaciones')
            ->with('success', 'Reservación enviada. El maître te confirmará pronto.');
    }

    /*------------------------------------------------------------------
     | CLIENTE — Historial de reservaciones
     ------------------------------------------------------------------*/

    /**
     * @brief Lista el historial de reservaciones del cliente autenticado.
     * @return View
     */
    public function historialCliente(): View
    {
        $reservaciones = Reservacion::with(['horario.mesa'])
            ->where('cliente_id', session('usuario_id'))
            ->get();

        return view('reservaciones.historial-cliente', compact('reservaciones'));
    }

    /*------------------------------------------------------------------
     | ADMIN — Reporte de reservaciones
     ------------------------------------------------------------------*/

    /**
     * @brief Reporte completo de todas las reservaciones (para Administrador).
     * @return View
     */
    public function reporte(): View
    {
        $reservaciones = Reservacion::with(['cliente', 'horario.mesa'])
            ->orderByRaw("(SELECT inicio FROM horarios WHERE reservacion_id = reservaciones.id_reservacion LIMIT 1) DESC NULLS LAST")
            ->get();

        return view('reportes.reservaciones', compact('reservaciones'));
    }
}