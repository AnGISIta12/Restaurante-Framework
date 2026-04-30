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
    //MAITRE — Registrar reservación
    /**
     * @brief Formulario para registrar una reservación (como Maitre).
     * @return View
     */
    public function create(): View
    {
        $clientes = Usuario::whereHas('roles', function ($q) { // Obtenemos los usuarios que tienen el rol de Cliente para mostrarlos en un dropdown al registrar una nueva reservación desde el panel del Maitre, lo que permite asociar la reservación a un cliente específico
            $q->where('nombre', Rol::CLIENTE); // Filtramos los usuarios para obtener solo aquellos que tienen el rol de Cliente, ya que las reservaciones deben estar asociadas a clientes registrados en el sistema
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
    public function store(Request $request): RedirectResponse // Valida y guarda una nueva reservación en la base de datos, asociándola al cliente seleccionado y estableciendo su estado inicial como Pendiente (0), lo que indica que la reservación ha sido registrada pero aún no se le ha asignado una mesa ni se ha confirmado
    {
        $request->validate([ // Validamos los datos del formulario para registrar una nueva reservación, asegurándonos de que el cliente seleccionado exista en la base de datos y que la cantidad de personas para la reservación sea un número entero entre 1 y 20, lo que nos permite garantizar que las reservaciones sean válidas y estén asociadas a clientes registrados en el sistema
            'cliente_id' => ['required', 'exists:usuarios,id_usuario'],
            'cantidad'   => ['required', 'integer', 'min:1', 'max:20'],
        ], [
            'cliente_id.required' => 'Debes seleccionar un cliente.',
            'cliente_id.exists'   => 'El cliente seleccionado no existe.',
            'cantidad.required'   => 'El número de personas es obligatorio.',
            'cantidad.min'        => 'Debe haber al menos 1 persona.',
            'cantidad.max'        => 'No puede superar 20 personas por reservación.',
        ]);

        Reservacion::create([ // Creamos una nueva reservación en la base de datos con el cliente seleccionado, la cantidad de personas y el estado inicial como Pendiente (0), lo que indica que la reservación ha sido registrada pero aún no se le ha asignado una mesa ni se ha confirmado
            'cliente_id' => $request->cliente_id,
            'cantidad'   => $request->cantidad,
            'estado'     => Reservacion::ESTADO_PENDIENTE,
        ]);

        return redirect()->route('reservaciones.proximas') // Redirigimos al listado de reservaciones próximas del Maitre con un mensaje de éxito después de registrar la nueva reservación, lo que permite al Maitre ver la nueva reservación en el contexto de las reservaciones próximas y tomar acciones como asignar una mesa o confirmar la reservación
            ->with('success', 'Reservación registrada correctamente.'); //PRUEBA: mensaje de éxito después de registrar la nueva reservación, lo que permite al Maitre ver una confirmación visual de que la acción se realizó correctamente y que la nueva reservación ha sido registrada en el sistema
    }

    //MAITRE — Asignar mesa
    
    /**
     * @brief Muestra el formulario de asignación de mesa a reservaciones pendientes.
     * @return View
     */
    public function asignar(): View
    {
        $reservaciones = Reservacion::sinAsignar() // Obtenemos las reservaciones que están en estado Pendiente o Confirmada pero aún no tienen una mesa asignada, lo que nos permite mostrar solo las reservaciones que requieren atención para asignarles una mesa en el panel de asignación del Maitre
            ->with('cliente') // Cargamos la información del cliente asociado a cada reservación para mostrarla en el formulario de asignación, lo que permite al Maitre identificar fácilmente a qué cliente corresponde cada reservación al momento de asignar una mesa
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

        $reservacion = Reservacion::findOrFail($request->reservacion_id); // Obtenemos la reservación seleccionada para asignarle una mesa, lo que nos permite validar su estado y cantidad de personas antes de realizar la asignación, asegurándonos de que la reservación esté en un estado válido (Pendiente o Confirmada) y que la mesa seleccionada tenga suficiente capacidad para acomodar a las personas de la reservación
        $cupoTotal   = Mesa::capacidadTotal();

        // Asientos ya ocupados en la ventana actual de 2 horas
        $ocupados = (int) DB::table('horarios')
            ->join('reservaciones', 'horarios.reservacion_id', '=', 'reservaciones.id_reservacion')
            ->whereRaw("horarios.inicio BETWEEN NOW() - INTERVAL '2 hours' AND NOW() + INTERVAL '2 hours'") // Contamos el número de asientos ocupados en la ventana de tiempo actual de 2 horas (desde 2 horas antes hasta 2 horas después del momento actual) para tener una estimación de cuántas personas ya están ocupando mesas en ese período, lo que nos permite validar que al asignar una nueva mesa para la reservación seleccionada no se supere el cupo total del restaurante considerando las reservaciones ya asignadas y en curso
            ->sum('reservaciones.cantidad'); // Sumamos la cantidad de personas de las reservaciones que tienen horarios asignados en esa ventana de tiempo para obtener el total de asientos ocupados, lo que nos permite validar que al asignar una nueva mesa para la reservación seleccionada no se supere el cupo total del restaurante considerando las reservaciones ya asignadas y en curso

        if (($ocupados + $reservacion->cantidad) > $cupoTotal) { // Validamos que al asignar la mesa para la reservación seleccionada no se supere el cupo total del restaurante sumando la cantidad de personas de la reservación actual a los asientos ya ocupados en la ventana de tiempo, lo que nos permite garantizar que el restaurante no exceda su capacidad máxima de personas al asignar mesas para las reservaciones
            return redirect()->route('reservaciones.asignar')
                ->withErrors([ // Si al asignar la mesa para la reservación seleccionada se superaría el cupo total del restaurante, volvemos al formulario de asignación con un mensaje de error específico que indica que no se puede realizar la asignación debido a que el restaurante alcanzaría su capacidad máxima de personas, lo que permite al Maitre entender claramente la razón por la cual no se puede asignar la mesa y tomar acciones como seleccionar una mesa diferente o ajustar la cantidad de personas en la reservación
                    'cupo' => "No se puede asignar: el restaurante superaría su cupo máximo de {$cupoTotal} personas.",
                ]);
        }

        DB::transaction(function () use ($request, $reservacion) {
            Horario::create([
                'mesa_id'        => $request->mesa_id,
                'reservacion_id' => $reservacion->id_reservacion,
                'inicio'         => now(),
                'duracion'       => '01:30:00', // Asumimos una duración estándar de 1 hora y 30 minutos para la reservación, lo que nos permite calcular el tiempo durante el cual la mesa estará ocupada por esa reservación y así validar la disponibilidad de mesas para otras reservaciones en ese período
            ]);

            $reservacion->update(['estado' => Reservacion::ESTADO_ASIGNADA]); // Actualizamos el estado de la reservación a Asignada (2) después de crear el horario con la mesa asignada, lo que indica que la reservación ha sido asignada a una mesa y está en proceso de ser atendida, lo que permite al Maitre y al personal del restaurante identificar fácilmente las reservaciones que ya tienen una mesa asignada y están en curso de atención
        });

        return redirect()->route('reservaciones.asignar')
            ->with('success', 'Mesa asignada correctamente.');
    }

    // MAITRE — Verificar disponibilidad
    
    /**
     * @brief Muestra la disponibilidad de mesas para una fecha dada.
     * @param  Request $request  Query param: fecha (Y-m-d)
     * @return View
     */
    public function verificar(Request $request): View
    {
        $fecha = $request->get('fecha', now()->toDateString());

        $mesas = Mesa::with(['horarios' => function ($q) use ($fecha) {
            $q->whereRaw("DATE(inicio) = ?", [$fecha]) // Cargamos los horarios de cada mesa para la fecha especificada, lo que nos permite mostrar la disponibilidad de mesas para ese día en el panel de verificación del Maitre, permitiendo al Maitre identificar fácilmente qué mesas están ocupadas o libres en esa fecha
              ->with('reservacion.cliente'); // Cargamos la información de la reservación y el cliente asociado a cada horario para mostrar detalles adicionales sobre las reservaciones que ocupan las mesas en la fecha especificada, lo que permite al Maitre tener una visión completa de la situación de las mesas y las reservaciones para ese día
        }])->orderBy('id_mesa')->get();

        return view('reservaciones.verificar', compact('mesas', 'fecha'));
    }

    //MAITRE — Validar cupo total
    /**         
     * @brief Muestra el panel de capacidad total del restaurante para hoy.
     * @return View
     */
    public function cupo(): View
    {
        $cupoTotal  = Mesa::capacidadTotal();
        $numMesas   = Mesa::count();
        $ocupadas   = (int) Horario::hoy()->distinct('mesa_id')->count('mesa_id'); // Contamos el número de mesas que tienen horarios asignados para hoy utilizando una consulta que filtra los horarios por la fecha actual y cuenta el número de mesas distintas que están ocupadas, lo que nos permite calcular cuántas mesas están ocupadas en el día actual para mostrar esta información en el panel de capacidad total del Maitre, permitiéndole entender rápidamente el nivel de ocupación del restaurante en ese día
        $libres     = $numMesas - $ocupadas;
        $porcentaje = $numMesas > 0 ? round($ocupadas / $numMesas * 100) : 0; // Calculamos el porcentaje de mesas ocupadas para mostrar una métrica visual del nivel de ocupación del restaurante en el panel de capacidad total del Maitre, lo que permite al Maitre entender rápidamente qué tan lleno está el restaurante en ese momento y tomar decisiones informadas sobre la gestión de las reservaciones y la asignación de mesas

        $reservasHoy = Horario::hoy() // Cargamos los horarios de hoy con la información de la reservación y el cliente asociado para mostrar un listado detallado de las reservaciones que están ocupando mesas en el día actual, lo que permite al Maitre tener una visión completa de las reservaciones en curso y los clientes que están siendo atendidos en ese momento
            ->with('mesa', 'reservacion.cliente')
            ->orderBy('inicio')
            ->get();

        return view('reservaciones.cupo', compact(
            'cupoTotal', 'numMesas', 'ocupadas', 'libres', 'porcentaje', 'reservasHoy'
        ));
    }

    //MAITRE — Reservaciones próximas
    /**
     * @brief Lista las reservaciones próximas o pendientes de asignar.
     * @return View
     */
    public function proximas(): View
    {
        $reservaciones = Reservacion::with(['cliente', 'horario.mesa'])
            ->where(function ($q) {
                $q->whereHas('horario', fn($h) => $h->where('inicio', '>=', now())) // Filtramos las reservaciones para obtener solo aquellas que tienen un horario asignado con una fecha de inicio en el futuro (próximas) o que están en estado Pendiente o Confirmada sin importar si tienen horario asignado o no, lo que nos permite mostrar en el panel de reservaciones próximas del Maitre tanto las reservaciones que ya tienen una mesa asignada y están programadas para el futuro, como las reservaciones que aún no tienen una mesa asignada pero están en estado Pendiente o Confirmada y requieren atención para ser asignadas a una mesa
                  ->orWhereIn('estado', [
                      Reservacion::ESTADO_PENDIENTE, // Incluimos las reservaciones que están en estado Pendiente (0) o Confirmada (1) sin importar si tienen horario asignado o no, ya que estas reservaciones requieren atención para ser asignadas a una mesa y confirmadas, lo que permite al Maitre identificar fácilmente las reservaciones que aún no tienen una mesa asignada pero están en proceso de ser atendidas
                      Reservacion::ESTADO_CONFIRMADA, //
                  ]);
            })
            ->orderByRaw("(SELECT inicio FROM horarios WHERE reservacion_id = reservaciones.id_reservacion LIMIT 1) ASC NULLS LAST")
            ->limit(30) // Limitamos a las 30 reservaciones más próximas para mostrar en el panel de reservaciones próximas del Maitre, lo que permite enfocarse en las reservaciones que requieren atención inmediata sin sobrecargar la vista con demasiadas reservaciones futuras o pendientes, facilitando la gestión y asignación de mesas para las reservaciones más relevantes en el corto plazo
            ->get();

        // Notificación: reservaciones que empiezan en los próximos 30 minutos
        $porEmpezar = Horario::proximos(30) // Obtenemos los horarios de las reservaciones que están programadas para empezar en los próximos 30 minutos para mostrar una notificación al Maitre sobre las reservaciones que requieren atención inmediata, lo que permite al Maitre prepararse para atender a los clientes de esas reservaciones y gestionar eficientemente la asignación de mesas y el personal necesario para atenderlas
            ->with('reservacion.cliente', 'mesa')
            ->get();

        return view('reservaciones.proximas', compact('reservaciones', 'porEmpezar'));
    }


     // CLIENTE — Solicitar reservación

    /**
     * @brief Formulario para que el cliente solicite una reservación.
     * @return View
     */
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
    
    /**
     * @brief Reporte completo de todas las reservaciones (para Administrador).
     * @return View
     */
    public function reporte(): View
    {
        $reservaciones = Reservacion::with(['cliente', 'horario.mesa']) // Cargamos todas las reservaciones con la información del cliente y la mesa asociada al horario para mostrar un reporte completo de todas las reservaciones en el panel de reportes del Administrador, lo que permite al Administrador tener una visión detallada de todas las reservaciones registradas en el sistema, incluyendo quién las hizo, qué mesa tienen asignada (si es que ya se les asignó una) y cuándo están programadas, facilitando la gestión y análisis de las reservaciones para tomar decisiones informadas sobre la operación del restaurante
            ->orderByRaw("(SELECT inicio FROM horarios WHERE reservacion_id = reservaciones.id_reservacion LIMIT 1) DESC NULLS LAST")
            ->get(); // Ordenamos las reservaciones por la fecha de inicio del horario asociado en orden descendente para mostrar primero las reservaciones más recientes o próximas, lo que permite al Administrador enfocarse en las reservaciones que requieren atención inmediata o que están programadas para el futuro cercano, facilitando la gestión y análisis de las reservaciones en el panel de reportes del Administrador

        return view('reportes.reservaciones', compact('reservaciones'));
    }
}