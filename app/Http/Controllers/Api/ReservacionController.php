<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservacion;
use App\Models\Horario;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @brief Controlador API para la gestión de reservaciones.
 */
class ReservacionController extends Controller
{
    /**
     * @brief Lista todas las reservaciones.
     * 
     * @return JsonResponse Lista de reservaciones con sus relaciones.
     */
    public function index(): JsonResponse
    {
        $reservaciones = Reservacion::with(['cliente', 'horario.mesa'])->get();
        return response()->json($reservaciones);
    }

    /**
     * @brief Crea una nueva reservación y asigna mesa si se proporciona.
     * 
     * @param Request $request Campos: mesa_id, fecha_hora, cliente_nombre (o cliente_id), cantidad.
     * @return JsonResponse La reservación creada.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mesa_id'        => ['required', 'exists:mesas,id'],
            'fecha_hora'     => ['required', 'date'],
            'cliente_nombre' => ['required', 'string', 'max:255'],
            'cantidad'       => ['required', 'integer', 'min:1'],
        ]);

        return DB::transaction(function () use ($validated) {
            // Buscamos o creamos un usuario para el cliente_nombre
            $cliente = Usuario::firstOrCreate(
                ['nombre' => $validated['cliente_nombre']],
                ['rol_id' => 3] // Asumiendo rol de cliente
            );

            $reservacion = Reservacion::create([
                'cliente_id' => $cliente->id,
                'cantidad'   => $validated['cantidad'],
                'estado'     => 1, // Confirmada
            ]);

            Horario::create([
                'mesa_id'        => $validated['mesa_id'],
                'reservacion_id' => $reservacion->id,
                'inicio'         => $validated['fecha_hora'],
                'duracion'       => '01:30:00',
            ]);

            return response()->json([
                'message' => 'Reservación creada con éxito',
                'data' => $reservacion->load('horario')
            ], 201);
        });
    }

    /**
     * @brief Muestra una reservación específica.
     * 
     * @param int $id ID de la reservación.
     * @return JsonResponse Datos de la reservación.
     */
    public function show(int $id): JsonResponse
    {
        $reservacion = Reservacion::with(['cliente', 'horario.mesa'])->findOrFail($id);
        return response()->json($reservacion);
    }

    /**
     * @brief Actualiza una reservación existente.
     * 
     * @param Request $request Campos a actualizar.
     * @param int $id ID de la reservación.
     * @return JsonResponse Reservación actualizada.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $reservacion = Reservacion::findOrFail($id);

        $validated = $request->validate([
            'mesa_id'    => ['sometimes', 'exists:mesas,id'],
            'fecha_hora' => ['sometimes', 'date'],
            'cantidad'   => ['sometimes', 'integer', 'min:1'],
            'estado'     => ['sometimes', 'integer'],
        ]);

        DB::transaction(function () use ($validated, $reservacion) {
            if (isset($validated['cantidad'])) $reservacion->cantidad = $validated['cantidad'];
            if (isset($validated['estado'])) $reservacion->estado = $validated['estado'];
            $reservacion->save();

            if (isset($validated['mesa_id']) || isset($validated['fecha_hora'])) {
                $horario = $reservacion->horario ?: new Horario(['reservacion_id' => $reservacion->id]);
                if (isset($validated['mesa_id'])) $horario->mesa_id = $validated['mesa_id'];
                if (isset($validated['fecha_hora'])) $horario->inicio = $validated['fecha_hora'];
                $horario->save();
            }
        });

        return response()->json([
            'message' => 'Reservación actualizada correctamente',
            'data' => $reservacion->load('horario')
        ]);
    }

    /**
     * @brief Elimina una reservación.
     * 
     * @param int $id ID de la reservación a eliminar.
     * @return JsonResponse Mensaje de éxito.
     */
    public function destroy(int $id): JsonResponse
    {
        $reservacion = Reservacion::findOrFail($id);
        $reservacion->delete(); // On delete cascade debería manejar el horario si está configurado

        return response()->json([
            'message' => "Reservación {$id} eliminada correctamente"
        ]);
    }
}
