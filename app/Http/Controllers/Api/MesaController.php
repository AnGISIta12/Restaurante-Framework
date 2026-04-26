<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @brief Controlador API para la gestión de mesas.
 */
class MesaController extends Controller
{
    /**
     * @brief Lista todas las mesas registradas.
     * 
     * @return JsonResponse Lista de mesas en formato JSON.
     */
    public function index(): JsonResponse
    {
        $mesas = Mesa::all();
        return response()->json($mesas);
    }

    /**
     * @brief Almacena una nueva mesa en el sistema.
     * 
     * @param Request $request Contiene 'capacidad' (int) y 'estado' (string/int).
     * @return JsonResponse La mesa creada y código 201.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'capacidad' => ['required', 'integer', 'min:1', 'max:50'],
            'estado'    => ['nullable', 'string'], // Campo opcional según lógica de negocio
        ]);

        // Mapeo de 'capacidad' a 'sillas' del modelo existente
        $mesa = Mesa::create([
            'sillas' => $validated['capacidad'],
            // Si el modelo soportara estado, se asignaría aquí
        ]);

        return response()->json([
            'message' => 'Mesa creada correctamente',
            'data' => $mesa
        ], 201);
    }

    /**
     * @brief Muestra los detalles de una mesa específica.
     * 
     * @param int $id ID de la mesa.
     * @return JsonResponse Datos de la mesa.
     */
    public function show(int $id): JsonResponse
    {
        $mesa = Mesa::findOrFail($id);
        return response()->json($mesa);
    }

    /**
     * @brief Actualiza los datos de una mesa existente.
     * 
     * @param Request $request Campos a actualizar ('capacidad', 'estado').
     * @param int $id ID de la mesa a actualizar.
     * @return JsonResponse Mensaje de éxito y datos actualizados.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $mesa = Mesa::findOrFail($id);

        $validated = $request->validate([
            'capacidad' => ['sometimes', 'required', 'integer', 'min:1', 'max:50'],
            'estado'    => ['nullable', 'string'],
        ]);

        if (isset($validated['capacidad'])) {
            $mesa->sillas = $validated['capacidad'];
        }
        
        $mesa->save();

        return response()->json([
            'message' => 'Mesa actualizada correctamente',
            'data' => $mesa
        ]);
    }

    /**
     * @brief Elimina una mesa del sistema.
     * 
     * @param int $id ID de la mesa a eliminar.
     * @return JsonResponse Mensaje de éxito.
     */
    public function destroy(int $id): JsonResponse
    {
        $mesa = Mesa::findOrFail($id);
        $mesa->delete();

        return response()->json([
            'message' => "Mesa {$id} eliminada correctamente"
        ]);
    }
}
