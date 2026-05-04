<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use App\Models\Pedido;
use App\Models\Plato;
use App\Models\Preparacion;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PedidoController extends Controller
{
    public function index(): View
    {
        $pedidos = Pedido::with(['cliente', 'mesero', 'ordenes.plato'])
            ->orderByDesc('id_pedido')
            ->get();

        return view('pedidos.index', compact('pedidos'));
    }

    public function create(): View
    {
        $clientes = Usuario::whereHas('roles', function ($q) {
                $q->where('nombre', Rol::CLIENTE);
            })
            ->orderBy('nombre')
            ->get();

        $platos = Plato::with('tipo')
            ->orderBy('nombre')
            ->get();

        return view('pedidos.create', compact('clientes', 'platos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'cliente_id'    => ['required', 'exists:usuarios,id_usuario'],
            'platos'        => ['required', 'array', 'min:1'],
            'platos.*'      => ['required', 'exists:platos,id_plato'],
            'cantidades'    => ['required', 'array'],
            'cantidades.*'  => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($request) {
            $pedido = Pedido::create([
                'cliente_id' => $request->cliente_id,
                'mesero_id'  => session('usuario_id'),
            ]);

            foreach ($request->platos as $i => $platoId) {
                Orden::create([
                    'plato_id'   => $platoId,
                    'pedido_id'  => $pedido->id_pedido,
                    'estado'     => Orden::ESTADO_PENDIENTE,
                    'cantidad'   => $request->cantidades[$i] ?? 1,
                    'solicitado' => now(),
                ]);
            }
        });

        return redirect()
            ->route('pedidos.index')
            ->with('success', 'Pedido registrado correctamente.');
    }

    public function show(Pedido $pedido): View
    {
        $pedido->load(['cliente', 'mesero', 'ordenes.plato.tipo']);

        return view('pedidos.show', compact('pedido'));
    }

    public function cocina(): View
    {
        $ordenes = Orden::enCocina()
            ->with(['plato', 'pedido.cliente'])
            ->orderBy('pedido_id')
            ->orderBy('solicitado')
            ->get();

        return view('pedidos.cocina', compact('ordenes'));
    }

    public function cambiarEstado(Request $request, Orden $orden): RedirectResponse
    {
        $request->validate([
            'estado' => ['required', 'integer', 'between:0,3'],
        ]);

        $orden->update([
            'estado' => $request->estado,
        ]);

        if ((int) $request->estado === Orden::ESTADO_EN_PREPARACION) {
            Preparacion::firstOrCreate([
                'cocinero_id' => session('usuario_id'),
                'orden_id'    => $orden->id_orden,
            ]);
        }

        return back()->with('success', 'Estado actualizado: ' . $orden->getEtiquetaEstado());
    }

    public function listas(): View
    {
        $ordenes = Orden::listas()
            ->with(['plato', 'pedido.cliente'])
            ->orderBy('pedido_id')
            ->orderBy('solicitado')
            ->get();

        return view('pedidos.listas', compact('ordenes'));
    }

    public function entregar(Orden $orden): RedirectResponse
    {
        $orden->update([
            'estado' => Orden::ESTADO_ENTREGADA,
        ]);

        return back()->with('success', 'Orden marcada como entregada.');
    }
}
