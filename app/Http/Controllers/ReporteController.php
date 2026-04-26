<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Orden;
use App\Models\Pedido;
use App\Models\Plato;
use App\Models\Reservacion;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Reportes y Estadísticas — solo Administrador.
 */
class ReporteController extends Controller
{
    public function index(): View
    {
        // Resumen general
        $totalMesas       = Mesa::count();
        $cupoTotal        = Mesa::capacidadTotal();
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
            ->limit(5)
            ->get();

        // Ingresos por plato
        $ingresos = DB::table('ordenes')
            ->join('platos', 'ordenes.plato_id', '=', 'platos.id_plato')
            ->selectRaw('platos.nombre, SUM(platos.precio * ordenes.cantidad) as ingreso')
            ->groupBy('platos.nombre')
            ->orderByDesc('ingreso')
            ->limit(10)
            ->get();

        // Reservaciones por estado
        $reservacionesPorEstado = DB::table('reservaciones')
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->get();

        // Órdenes por estado
        $ordenesPorEstado = DB::table('ordenes')
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->get();

        return view('reportes.index', compact(
            'totalMesas', 'cupoTotal', 'totalReservaciones',
            'totalPedidos', 'totalOrdenes', 'platosTop',
            'ingresos', 'reservacionesPorEstado', 'ordenesPorEstado'
        ));
    }

    public function reservaciones(): View
    {
        $reservaciones = Reservacion::with(['cliente', 'horario.mesa'])
            ->orderByDesc('id_reservacion')
            ->paginate(20);

        return view('reportes.reservaciones', compact('reservaciones'));
    }

    public function pedidos(): View
    {
        $pedidos = Pedido::with(['cliente', 'mesero', 'ordenes.plato'])
            ->orderByDesc('id_pedido')
            ->paginate(20);

        return view('reportes.pedidos', compact('pedidos'));
    }
}