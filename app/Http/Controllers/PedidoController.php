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
    
    // MESERO — listar pedidos activos
    
    public function index(): View
    {
        $pedidos = Pedido::with(['cliente', 'mesero', 'ordenes.plato'])
            ->orderByDesc('id_pedido')
            ->paginate(15);

        return view('pedidos.index', compact('pedidos'));
    }

   
    // MESERO — crear pedido
    public function create(): View
    {
        $clientes = Usuario::whereHas('roles', fn($q) => $q->where('nombre', Rol::CLIENTE)) // Obtenemos solo los usuarios que tienen el rol de Cliente para mostrarlos en un dropdown al crear un nuevo pedido desde el panel del Mesero, lo que permite al Mesero seleccionar el cliente para el cual se está creando el pedido, facilitando la asociación del pedido con el cliente correcto y mejorando la experiencia de gestión de pedidos en el restaurante
            ->orderBy('nombre')->get();
        $platos = Plato::with('tipo')->orderBy('nombre')->get(); // Obtenemos todos los platos con su tipo para mostrarlos en un dropdown al crear un nuevo pedido desde el panel del Mesero, lo que permite al Mesero seleccionar los platos que el cliente desea ordenar de manera organizada y fácil de navegar, mejorando la experiencia de gestión de pedidos en el restaurante

        return view('pedidos.create', compact('clientes', 'platos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([ // Validamos los datos del formulario para crear un nuevo pedido, asegurándonos de que se seleccione un cliente válido, se agreguen al menos un plato con su cantidad correspondiente, y que los platos seleccionados existan en la base de datos, lo que garantiza que el pedido se cree con información completa y válida desde el panel del Mesero
            'cliente_id' => ['required', 'exists:usuarios,id_usuario'],
            'platos'     => ['required', 'array', 'min:1'],
            'platos.*'   => ['exists:platos,id_plato'],
            'cantidades' => ['required', 'array'],
            'cantidades.*' => ['integer', 'min:1'],
        ]);

        DB::transaction(function () use ($request) { // Usamos una transacción para asegurar que la creación del pedido y las órdenes asociadas se realicen correctamente o se deshagan en caso de error, lo que garantiza la integridad de los datos en el sistema al crear un nuevo pedido desde el panel del Mesero
            $pedido = Pedido::create([
                'cliente_id' => $request->cliente_id,
                'mesero_id'  => session('usuario_id'),
            ]);

            foreach ($request->platos as $i => $plato_id) { // Iteramos sobre los platos seleccionados para crear una orden por cada plato en el pedido, asignando la cantidad correspondiente de cada plato según lo ingresado en el formulario, lo que permite al Mesero agregar múltiples platos con sus cantidades al mismo pedido de manera eficiente y organizada desde el panel del Mesero
                $cantidad = $request->cantidades[$i] ?? 1;
                Orden::create([
                    'plato_id'   => $plato_id,
                    'pedido_id'  => $pedido->id_pedido,
                    'estado'     => Orden::ESTADO_PENDIENTE,
                    'cantidad'   => $cantidad,
                    'solicitado' => now(),
                ]);
            }
        });

        return redirect()->route('pedidos.index')
            ->with('success', 'Pedido registrado correctamente.');
    }


    // MESERO — ver pedido
    
    public function show(Pedido $pedido): View
    {
        $pedido->load(['cliente', 'mesero', 'ordenes.plato.tipo']); // Cargamos el pedido con la información del cliente, mesero, y las órdenes con sus platos y tipos para mostrar un detalle completo del pedido en el panel del Mesero, lo que permite al Mesero revisar toda la información relacionada con el pedido de manera organizada y detallada, facilitando la gestión y atención al cliente en el restaurante
        return view('pedidos.show', compact('pedido'));
    }

    // COCINERO — ver órdenes en cocina
    
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
                'orden_id'    => $orden->id_orden,
            ]);
        }

        return back()->with('success', 'Estado actualizado: ' . $orden->getEtiquetaEstado());
    }

    // MESERO — entregar órdenes listas
    
    public function listas(): View
    {
        $ordenes = Orden::listas()
            ->with(['plato', 'pedido.cliente'])
            ->orderBy('solicitado')
            ->get();

        return view('pedidos.listas', compact('ordenes')); // Mostramos las órdenes que están listas para ser entregadas al cliente en el panel del Mesero, lo que permite al Mesero identificar rápidamente cuáles pedidos están listos para ser entregados y así brindar un mejor servicio al cliente en el restaurante
    }

    public function entregar(Orden $orden): RedirectResponse
    {
        $orden->update(['estado' => Orden::ESTADO_ENTREGADA]); // Actualizamos el estado de la orden a Entregada (3) para indicar que el mesero ha entregado la orden al cliente, lo que permite mantener un registro actualizado del estado de cada orden en el sistema y facilita la gestión de pedidos y atención al cliente en el restaurante
        return back()->with('success', 'Orden marcada como entregada.');
    }
}