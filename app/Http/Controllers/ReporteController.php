<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Orden;
use App\Models\Pedido;
use App\Models\Plato;
use App\Models\Reservacion;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

//Reportes y Estadísticas — solo Administrador.
class ReporteController extends Controller
{
    public function index(): View
    {
        // Resumen general
        $totalMesas       = Mesa::count(); // Contamos el total de mesas registradas en la base de datos para mostrar esta información en el dashboard de reportes del Administrador
        $cupoTotal        = Mesa::capacidadTotal(); // Asi con cada uno xd
        $totalReservaciones = Reservacion::count();
        $totalPedidos     = Pedido::count();
        $totalOrdenes     = Orden::count();

        // Platos más pedidos (top 5)
        $platosTop = Plato::select('platos.*')
            ->selectRaw('SUM(ordenes.cantidad) as total_pedido')
            ->join('ordenes', 'platos.id_plato', '=', 'ordenes.plato_id')
            ->groupBy('platos.id_plato', 'platos.tipo_id', 'platos.nombre',
                      'platos.descripcion', 'platos.tiempo', 'platos.precio')
            ->orderByDesc('total_pedido')
            ->limit(5) // los mas top 
            ->get();

        // Ingresos por plato
        $ingresos = DB::table('ordenes')
            ->join('platos', 'ordenes.plato_id', '=', 'platos.id_plato')
            ->selectRaw('platos.nombre, SUM(platos.precio * ordenes.cantidad) as ingreso') // Calculamos el ingreso total generado por cada plato multiplicando su precio por la cantidad ordenada y sumando para obtener el total por plato
            ->groupBy('platos.nombre') // Agrupamos por nombre de plato para obtener el ingreso total por cada plato individualmente, lo que nos permite identificar cuáles son los platos que generan más ingresos para el restaurante
            ->orderByDesc('ingreso')
            ->limit(10)
            ->get();

        // Reservaciones por estado
        $reservacionesPorEstado = DB::table('reservaciones')
            ->selectRaw('estado, COUNT(*) as total') // Contamos el número de reservaciones para cada estado (Pendiente, Confirmada, Cancelada) utilizando una consulta que agrupa por el campo 'estado' y cuenta el total de reservaciones en cada grupo, lo que nos permite mostrar un resumen de cómo están distribuidas las reservaciones según su estado actual
            ->groupBy('estado')
            ->get();

        // Órdenes por estado
        $ordenesPorEstado = DB::table('ordenes')
            ->selectRaw('estado, COUNT(*) as total') // Contamos el número de órdenes para cada estado (Pendiente, Confirmada, Cancelada) utilizando una consulta que agrupa por el campo 'estado' y cuenta el total de órdenes en cada grupo, lo que nos permite mostrar un resumen de cómo están distribuidas las órdenes según su estado actual
            ->groupBy('estado')
            ->get();

        return view('reportes.index', compact( // Pasamos todas las variables calculadas a la vista para mostrar el dashboard de reportes del Administrador con toda la información relevante sobre el desempeño del restaurante, incluyendo el resumen general, los platos más pedidos, los ingresos por plato y la distribución de reservaciones y órdenes por estado
            'totalMesas', 'cupoTotal', 'totalReservaciones',
            'totalPedidos', 'totalOrdenes', 'platosTop',
            'ingresos', 'reservacionesPorEstado', 'ordenesPorEstado'
        ));
    }

    public function reservaciones(): View
    {
        $reservaciones = Reservacion::with(['cliente', 'horario.mesa'])
            ->orderByDesc('id_reservacion')
            ->paginate(20); // Cargamos las reservaciones con la información del cliente y la mesa asociada al horario, ordenadas por ID de reservación en orden descendente para mostrar las reservaciones más recientes primero, y paginamos los resultados para mostrar 20 reservaciones por página en el reporte de reservaciones del Administrador

        return view('reportes.reservaciones', compact('reservaciones'));
    }

    public function pedidos(): View
    {
        $pedidos = Pedido::with(['cliente', 'mesero', 'ordenes.plato']) // Cargamos los pedidos con la información del cliente, el mesero y los platos asociados a las órdenes de cada pedido para mostrar un reporte detallado de los pedidos realizados por los clientes, ordenados por ID de pedido en orden descendente para mostrar los pedidos más recientes primero, y paginamos los resultados para mostrar 20 pedidos por página en el reporte de pedidos del Administrador
            ->orderByDesc('id_pedido')
            ->paginate(20); // Cargamos los pedidos con la información del cliente, el mesero y los platos ordenados, ordenados por ID de pedido en orden descendente para mostrar los pedidos más recientes primero, y paginamos los resultados para mostrar 20 pedidos por página en el reporte de pedidos del Administrador

        return view('reportes.pedidos', compact('pedidos'));
    }
}