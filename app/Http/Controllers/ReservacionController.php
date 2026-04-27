<?php

namespace App\Http\Controllers;

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
            'cliente_id' => ['required', 'exists:usuarios,id'],
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
            ->orderBy('id')
            ->get();

        // Para cada reservación calculamos las mesas disponibles con suficiente capacidad
        $mesasPorReservacion = [];
        foreach ($reservaciones as $reservacion) {
            $mesasPorReservacion[$reservacion->id] = Mesa::disponibles()
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
            'reservacion_id' => ['required', 'exists:reservaciones,id'],
            'mesa_id'        => ['required', 'exists:mesas,id'],
        ]);

        $reservacion = Reservacion::findOrFail($request->reservacion_id);
        $cupoTotal   = Mesa::capacidadTotal();

        // Asientos ya ocupados en la ventana actual de 2 horas
        $ocupados = (int) DB::table('reservaciones')
            ->whereNotNull('mesa_id')
            ->whereRaw("CAST(fecha || ' ' || hora AS TIMESTAMP) BETWEEN NOW() - INTERVAL '2 hours' AND NOW() + INTERVAL '2 hours'")
            ->sum('cantidad');

        if (($ocupados + $reservacion->cantidad) > $cupoTotal) {
            return redirect()->route('reservaciones.asignar')
                ->withErrors([
                    'cupo' => "No se puede asignar: el restaurante superaría su cupo máximo de {$cupoTotal} personas.",
                ]);
        }

        $reservacion->update([
            'estado' => Reservacion::ESTADO_ASIGNADA,
            'mesa_id' => $request->mesa_id,
            // Si la reservacion aún no tenía fecha/hora, tendrías que definirla. 
            // Supondremos que ya la tiene o la guardamos con now() si era la logica anterior
            'fecha' => $reservacion->fecha ?? now()->toDateString(),
            'hora'  => $reservacion->hora ?? now()->toTimeString(),
        ]);

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

        $mesas = Mesa::with(['reservaciones' => function ($q) use ($fecha) {
            $q->whereDate('fecha', $fecha)
              ->with('cliente');
        }])->orderBy('id')->get();

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
        // Mesas ocupadas hoy
        $ocupadas   = (int) Reservacion::whereDate('fecha', now()->toDateString())
            ->whereNotNull('mesa_id')
            ->distinct('mesa_id')
            ->count('mesa_id');
            
        $libres     = $numMesas - $ocupadas;
        $porcentaje = $numMesas > 0 ? round($ocupadas / $numMesas * 100) : 0;

        $reservasHoy = Reservacion::whereDate('fecha', now()->toDateString())
            ->whereNotNull('mesa_id')
            ->with(['mesa', 'cliente'])
            ->orderBy('hora')
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
        $reservaciones = Reservacion::with(['cliente', 'mesa'])
            ->where(function ($q) {
                $q->whereDate('fecha', '>', now()->toDateString())
                  ->orWhere(function ($sq) {
                      $sq->whereDate('fecha', '=', now()->toDateString())
                         ->whereTime('hora', '>=', now()->toTimeString());
                  })
                  ->orWhereIn('estado', [
                      Reservacion::ESTADO_PENDIENTE,
                      Reservacion::ESTADO_CONFIRMADA,
                  ]);
            })
            ->orderByRaw("fecha ASC NULLS LAST, hora ASC NULLS LAST")
            ->limit(30)
            ->get();

        // Notificación: reservaciones que empiezan en los próximos 30 minutos
        $porEmpezar = Reservacion::whereDate('fecha', now()->toDateString())
            ->whereTime('hora', '>=', now()->toTimeString())
            ->whereTime('hora', '<=', now()->addMinutes(30)->toTimeString())
            ->with(['cliente', 'mesa'])
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
        return view('reservaciones.solicitar');
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
        ]);

        Reservacion::create([
            'cliente_id' => session('usuario_id'),
            'cantidad'   => $request->cantidad,
            'estado'     => Reservacion::ESTADO_PENDIENTE,
        ]);

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
        $reservaciones = Reservacion::with(['mesa'])
            ->where('cliente_id', session('usuario_id'))
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
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
        $reservaciones = Reservacion::with(['cliente', 'mesa'])
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->get();

        return view('reportes.reservaciones', compact('reservaciones'));
    }
}