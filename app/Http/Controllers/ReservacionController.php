<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionRestaurante;
use App\Models\Horario;
use App\Models\Mesa;
use App\Models\Reservacion;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;


class ReservacionController extends Controller
{
    //MAITRE — Registrar reservación
   
    public function create(): View
    {
        $clientes = Usuario::whereHas('roles', function ($q) { // Obtenemos los usuarios que tienen el rol de Cliente 
            $q->where('nombre', Rol::CLIENTE); // Filtramos los usuarios para obtener solo aquellos que tienen el rol de Cliente, ya que las reservaciones deben estar asociadas a clientes registrados en el sistema
        })->orderBy('nombre')->get();

        return view('reservaciones.create', compact('clientes'));
    }

    
    public function store(Request $request): RedirectResponse // Valida y guarda una nueva reservación en la base de datos, 
    {
        $request->validate([ // Validamos los datos del formulario para registrar una nueva reservación
            'cliente_id' => ['required', 'exists:usuarios,id_usuario'],
            'cantidad'   => ['required', 'integer', 'min:1', 'max:20'],
        ], [
            'cliente_id.required' => 'Debes seleccionar un cliente.',
            'cliente_id.exists'   => 'El cliente seleccionado no existe.',
            'cantidad.required'   => 'El número de personas es obligatorio.',
            'cantidad.min'        => 'Debe haber al menos 1 persona.',
            'cantidad.max'        => 'No puede superar 20 personas por reservación.',
        ]);

        Reservacion::create([ // Creamos una nueva reservación en la base de datos con el cliente seleccionado, la cantidad de personas y el estado inicial como Pendiente (0)
            'cliente_id' => $request->cliente_id,
            'cantidad'   => $request->cantidad,
            'estado'     => Reservacion::ESTADO_PENDIENTE,
        ]);

        return redirect()->route('reservaciones.proximas') // Redirigimos al listado de reservaciones próximas del Maitre 
            ->with('success', 'Reservación registrada correctamente.'); //PRUEBA: mensaje de éxito después de registrar la nueva reservación
    }

    public function asignar(): View
    {
        $reservaciones = Reservacion::sinAsignar() // Obtenemos las reservaciones que están en estado Pendiente o Confirmada 
            ->with('cliente') // Cargamos la información del cliente asociado a cada reservación para mostrarla en el formulario de asignación
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

    
    public function guardarAsignacion(Request $request): RedirectResponse
    {
        $request->validate([
            'reservacion_id' => ['required', 'exists:reservaciones,id_reservacion'],
            'mesa_id'        => ['required', 'exists:mesas,id_mesa'],
        ]);

        $reservacion = Reservacion::findOrFail($request->reservacion_id); // Obtenemos la reservación seleccionada para asignarle una mesa

        // Obtenemos el límite efectivo de sillas del restaurante desde la
        // configuración del Administrador (manual si > 0, o automático por mesas)
        $config    = ConfiguracionRestaurante::actual();
        $cupoTotal = $config->limiteEfectivo();

        // Asientos ya ocupados en la ventana actual de 2 horas
        $ocupados = (int) DB::table('horarios')
            ->join('reservaciones', 'horarios.reservacion_id', '=', 'reservaciones.id_reservacion')
            ->whereRaw("horarios.inicio BETWEEN NOW() - INTERVAL '2 hours' AND NOW() + INTERVAL '2 hours'") // Contamos el número de asientos ocupados en la ventana de tiempo actual de 2 horas (desde 2 horas antes hasta 2 horas después del momento actual) 
            
            ->sum('reservaciones.cantidad'); // Sumamos la cantidad de personas de las reservaciones que tienen horarios asignados en esa ventana de tiempo para obtener el total de asientos ocupados

        if (($ocupados + $reservacion->cantidad) > $cupoTotal) { // Validamos que al asignar la mesa para la reservación seleccionada no se supere el cupo total del restaurante sumando la cantidad de personas de la reservación actual a los asientos 
        
            return redirect()->route('reservaciones.asignar')
                ->withErrors([ // Si al asignar la mesa para la reservación seleccionada se superaría el cupo total del restaurante, volvemos al formulario de asignación con un mensaje de error específico que indica que no se puede realizar la asignación debido a que el restaurante alcanzaría su capacidad máxima de personas
                    'cupo' => "No se puede asignar: el restaurante superaría su cupo máximo de {$cupoTotal} personas (límite configurado por el Administrador).",
                ]);
        }

        // Verificamos si al asignar la mesa se estaría cerca del umbral de
        // alerta configurado por el Administrador para mostrar un aviso
        $alertaPost = $config->verificarAlerta($ocupados + $reservacion->cantidad);

        DB::transaction(function () use ($request, $reservacion) {
            Horario::create([
                'mesa_id'        => $request->mesa_id,
                'reservacion_id' => $reservacion->id_reservacion,
                'inicio'         => now(),
                'duracion'       => '01:30:00', // Asumimos una duración estándar de 1 hora y 30 minutos para la reservación
            ]);

            $reservacion->update(['estado' => Reservacion::ESTADO_ASIGNADA]); // Actualizamos el estado de la reservación a Asignada (2) después de crear el horario con la mesa asignada
        });

        // Si después de la asignación se alcanza el umbral de alerta,
        // agregamos una advertencia al mensaje de éxito para notificar al Maitre
        $mensaje = 'Mesa asignada correctamente.';
        if ($alertaPost['alerta']) {
            $mensaje .= " ⚠️ ¡Atención! El restaurante está al {$alertaPost['porcentaje']}% de su capacidad ({$alertaPost['restantes']} sillas restantes).";
        }

        return redirect()->route('reservaciones.asignar')
            ->with('success', $mensaje);
    }

    // MAI— Verificar disponibilidad
    
    
    public function verificar(Request $request): View
    {
        $fecha = $request->get('fecha', now()->toDateString());

        $mesas = Mesa::with(['horarios' => function ($q) use ($fecha) {
            $q->whereRaw("DATE(inicio) = ?", [$fecha]) // Cargamos los horarios de cada mesa para la fecha especificada
            
              ->with('reservacion.cliente'); // Cargamos la información de la reservación y el cliente asociado a cada horario para mostrar detalles adicionales sobre las reservaciones que ocupan las mesas en la fecha especificada
        }])->orderBy('id_mesa')->get();

        return view('reservaciones.verificar', compact('mesas', 'fecha'));
    }

    // MAI - Validar cupo total
    /**
     * @brief Muestra el estado de ocupación del restaurante con alertas de capacidad.
     *
     * Calcula la ocupación actual de mesas y sillas, utilizando el límite
     * configurado por el Administrador para determinar si se debe mostrar
     * una alerta visual de proximidad al límite de capacidad.
     *
     * @return View  Vista reservaciones.cupo con datos de ocupación y alertas.
     */
    public function cupo(): View
    {
        // Obtenemos la configuración del restaurante para usar el límite
        // efectivo de sillas (manual o automático) y los datos de alerta
        $config     = ConfiguracionRestaurante::actual();
        $cupoTotal  = $config->limiteEfectivo();
        $numMesas   = Mesa::count();
        $ocupadas   = (int) Horario::hoy()->distinct('mesa_id')->count('mesa_id'); // Contamos el número de mesas que tienen horarios asignados para hoy utilizando una consulta que filtra los horarios por la fecha actual y cuenta el número de mesas distintas que están ocupadas
        
        $libres     = $numMesas - $ocupadas;
        $porcentaje = $numMesas > 0 ? round($ocupadas / $numMesas * 100) : 0; // Calculamos el porcentaje de mesas ocupadas para mostrar una métrica visual del nivel de ocupación del restaurante en el panel de capacidad total del Maitre

        // Calculamos la ocupación en sillas (no mesas) para verificar el
        // umbral de alerta configurado por el Administrador
        $sillasOcupadas = (int) DB::table('horarios')
            ->join('reservaciones', 'horarios.reservacion_id', '=', 'reservaciones.id_reservacion')
            ->whereRaw("horarios.inicio BETWEEN NOW() - INTERVAL '2 hours' AND NOW() + INTERVAL '2 hours'")
            ->sum('reservaciones.cantidad');

        // Verificamos si la ocupación actual supera el umbral de alerta
        // para mostrar alertas visuales prominentes en la vista de cupo
        $alerta = $config->verificarAlerta($sillasOcupadas);

        $reservasHoy = Horario::hoy() // Cargamos los horarios de hoy con la información de la reservación y el cliente asociado para mostrar un listado detallado de las reservaciones que están ocupando mesas en el día actual, lo que permite al Maitre tener una visión completa de las reservaciones en curso y los clientes que están siendo atendidos en ese momento
            ->with('mesa', 'reservacion.cliente')
            ->orderBy('inicio')
            ->get();

        return view('reservaciones.cupo', compact(
            'cupoTotal', 'numMesas', 'ocupadas', 'libres', 'porcentaje',
            'reservasHoy', 'alerta', 'sillasOcupadas', 'config'
        ));
    }

    // MAI - Reservaciones próximas
    
    public function proximas(): View
{
    $reservaciones = Reservacion::with(['cliente', 'horario.mesa'])
        ->orderBy('id_reservacion')
        ->get();

    $porEmpezar = Horario::proximos(30)
        ->with('reservacion.cliente', 'mesa')
        ->orderBy('inicio')
        ->get();

    return view('reservaciones.proximas', compact('reservaciones', 'porEmpezar'));
}


     // CLIENTE — Solicitar reservación

    public function solicitar(): View
    {
        $mesas = Mesa::orderBy('id_mesa')->get(); // Obtenemos todas las mesas ordenadas por ID para mostrarlas en un dropdown al solicitar una nueva reservación desde el panel del Cliente, lo que permite al cliente seleccionar la mesa que desea reservar al momento de solicitar la reservación, facilitando el proceso de solicitud y permitiendo al cliente elegir la mesa que prefiera según su disponibilidad
        
        // Generar un listado de horarios de ejemplo para el dropdown
        $horarios = [
            '13:00', '13:30', '14:00', '14:30', '15:00',
            '15:30', '16:00', '16:30', '17:00', '17:30',
            '18:00', '18:30', '19:00', '19:30', '20:00',
            '20:30', '21:00', '21:30', '22:00'
        ];

        return view('reservaciones.solicitar', compact('mesas', 'horarios'));
    }

    
    public function guardarSolicitud(Request $request): RedirectResponse
    {
        $request->validate([
            'cantidad' => ['required', 'integer', 'min:1', 'max:20'],
            'fecha'    => ['required', 'date', 'after_or_equal:today'],
            'hora'     => ['required'],
            'mesa_id'  => ['required', 'exists:mesas,id_mesa'],
        ]);

        DB::transaction(function () use ($request) {
            $reservacion = Reservacion::create([ // Creamos una nueva reservación en la base de datos con el cliente en sesión, la cantidad de personas, la fecha y hora solicitada, y el estado inicial como Pendiente (0), lo que indica que la reservación ha sido registrada por el cliente pero aún no se le ha asignado una mesa ni se ha confirmado, lo que permite al Maitre revisar las nuevas reservaciones solicitadas por los clientes y tomar acciones para asignarles una mesa y confirmar la reservación
                'cliente_id' => session('usuario_id'),
                'cantidad'   => $request->cantidad,
                'estado'     => Reservacion::ESTADO_PENDIENTE,
            ]);

            Horario::create([ // Creamos un nuevo horario para la reservación con la mesa seleccionada, la fecha y hora solicitada, y una duración estándar de 1 hora y 30 minutos, lo que nos permite registrar el horario en el que el cliente ha solicitado la reservación y así mostrar esta información en el panel de reservaciones próximas del Maitre para que pueda revisar y asignar una mesa si es necesario, además de tener un registro completo de las reservaciones solicitadas por los clientes
                'mesa_id'        => $request->mesa_id,
                'reservacion_id' => $reservacion->id_reservacion,
                'inicio'         => $request->fecha . ' ' . $request->hora . ':00',
                'duracion'       => '01:30:00',
            ]);
        });

        return redirect()->route('cliente.reservaciones')
            ->with('success', 'Reservación enviada. El maître te confirmará pronto.');
    }

    // CLIENTE — Historial de reservaciones
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

    // ADMIN — Reporte de reservaciones
    
    public function reporte(): View
    {
        $reservaciones = Reservacion::with(['cliente', 'horario.mesa']) // Cargamos todas las reservaciones con la información del cliente y la mesa asociada al horario para mostrar un reporte completo de todas las reservaciones en el panel de reportes del Administrador, lo que permite al Administrador tener una visión detallada de todas las reservaciones registradas en el sistema, incluyendo quién las hizo, qué mesa tienen asignada (si es que ya se les asignó una) y cuándo están programadas, facilitando la gestión y análisis de las reservaciones para tomar decisiones informadas sobre la operación del restaurante
            ->orderByRaw("(SELECT inicio FROM horarios WHERE reservacion_id = reservaciones.id_reservacion LIMIT 1) DESC NULLS LAST")
            ->get(); // Ordenamos las reservaciones por la fecha de inicio del horario asociado en orden descendente para mostrar primero las reservaciones más recientes o próximas, lo que permite al Administrador enfocarse en las reservaciones que requieren atención inmediata o que están programadas para el futuro cercano, facilitando la gestión y análisis de las reservaciones en el panel de reportes del Administrador

        return view('reportes.reservaciones', compact('reservaciones'));
    }
}
