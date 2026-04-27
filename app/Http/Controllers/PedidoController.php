<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use App\Models\Pedido;
use App\Models\Plato;
use App\Models\Preparacion;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Gestión de Pedidos y Órdenes.
 * Mesero: crear/ver pedidos, agregar órdenes.
 * Cocinero: ver órdenes en cocina, cambiar estado.
 */
class PedidoController extends Controller
{
    /*------------------------------------------------------------------
     | MESERO — listar pedidos activos
     ------------------------------------------------------------------*/
    public function index(): View
    {
        $pedidos = Pedido::with(['cliente', 'mesero', 'ordenes.plato'])
            ->orderByDesc('id')
            ->paginate(15);

        return view('pedidos.index', compact('pedidos'));
    }

    /*------------------------------------------------------------------
     | MESERO — crear pedido
     ------------------------------------------------------------------*/
    public function create(): View
    {
        $clientes = Usuario::whereHas('roles', fn($q) => $q->where('nombre', Rol::CLIENTE))
            ->orderBy('nombre')->get();
        $platos = Plato::with('tipo')->orderBy('nombre')->get();

        return view('pedidos.create', compact('clientes', 'platos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'cliente_id' => ['required', 'exists:usuarios,id'],
            'platos'     => ['required', 'array', 'min:1'],
            'platos.*'   => ['exists:platos,id'],
            'cantidades' => ['required', 'array'],
            'cantidades.*' => ['integer', 'min:1'],
        ]);

        DB::transaction(function () use ($request) {
            $pedido = Pedido::create([
                'cliente_id' => $request->cliente_id,
                'mesero_id'  => session('usuario_id'),
            ]);

            foreach ($request->platos as $i => $plato_id) {
                $cantidad = $request->cantidades[$i] ?? 1;
                Orden::create([
                    'plato_id'   => $plato_id,
                    'pedido_id'  => $pedido->id,
                    'estado'     => Orden::ESTADO_PENDIENTE,
                    'cantidad'   => $cantidad,
                    'solicitado' => now(),
                ]);
            }
        });

        return redirect()->route('pedidos.index')
            ->with('success', 'Pedido registrado correctamente.');
    }

    /*------------------------------------------------------------------
     | MESERO — ver pedido
     ------------------------------------------------------------------*/
    public function show(Pedido $pedido): View
    {
        $pedido->load(['cliente', 'mesero', 'ordenes.plato.tipo']);
        return view('pedidos.show', compact('pedido'));
    }

    /*------------------------------------------------------------------
     | COCINERO — ver órdenes en cocina
     ------------------------------------------------------------------*/
    public function cocina(): View
    {
        $ordenes = Orden::enCocina()
            ->with(['plato', 'pedido.cliente'])
            ->orderBy('solicitado')
            ->get();

        return view('pedidos.cocina', compact('ordenes'));
    }

    /*------------------------------------------------------------------
     | COCINERO — cambiar estado de orden
     ------------------------------------------------------------------*/
    public function cambiarEstado(Request $request, Orden $orden): RedirectResponse
    {
        $request->validate([
            'estado' => ['required', 'integer', 'between:0,3'],
        ]);

        $orden->update(['estado' => $request->estado]);

        // Registrar preparacion si pasa a En Preparacion
        if ($request->estado == Orden::ESTADO_EN_PREPARACION) {
            Preparacion::firstOrCreate([
                'cocinero_id' => session('usuario_id'),
                'orden_id'    => $orden->id,
            ]);
        }

        return back()->with('success', 'Estado actualizado: ' . $orden->getEtiquetaEstado());
    }

    /*------------------------------------------------------------------
     | MESERO — entregar órdenes listas
     ------------------------------------------------------------------*/
    public function listas(): View
    {
        $ordenes = Orden::listas()
            ->with(['plato', 'pedido.cliente'])
            ->orderBy('solicitado')
            ->get();

        return view('pedidos.listas', compact('ordenes'));
    }

    public function entregar(Orden $orden): RedirectResponse
    {
        $orden->update(['estado' => Orden::ESTADO_ENTREGADA]);
        return back()->with('success', 'Orden marcada como entregada.');
    }
}