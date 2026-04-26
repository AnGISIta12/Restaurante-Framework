<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * @brief Controlador de gestión de mesas del restaurante.
 *
 * Permite al Administrador crear, editar y eliminar mesas.
 * Acceso restringido al rol Administrador mediante middleware.
 */
class MesaController extends Controller
{
    /**
     * @brief Lista todas las mesas con la capacidad total del restaurante.
     * @return View
     * @pre  Usuario autenticado con rol Administrador.
     * @post Se muestra el listado de mesas y la suma total de sillas.
     */
    public function index(): View
    {
        $mesas       = Mesa::orderBy('id_mesa')->get();
        $cupoTotal   = Mesa::capacidadTotal();

        return view('mesas.index', compact('mesas', 'cupoTotal'));
    }

    /**
     * @brief Muestra el formulario de creación de mesa.
     * @return View
     */
    public function create(): View
    {
        return view('mesas.create');
    }

    /**
     * @brief Almacena una nueva mesa.
     * @param  Request $request  Campo: sillas (entero 1-50)
     * @return RedirectResponse
     * @pre  El campo sillas debe ser un entero entre 1 y 50.
     * @post Se crea la mesa y se redirige al listado con mensaje de éxito.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'sillas' => ['required', 'integer', 'min:1', 'max:50'],
        ], [
            'sillas.required' => 'El número de sillas es obligatorio.',
            'sillas.integer'  => 'Las sillas deben ser un número entero.',
            'sillas.min'      => 'La mesa debe tener al menos 1 silla.',
            'sillas.max'      => 'Una mesa no puede tener más de 50 sillas.',
        ]);

        Mesa::create(['sillas' => $request->sillas]);

        return redirect()->route('mesas.index')
            ->with('success', 'Mesa creada correctamente.');
    }

    /**
     * @brief Muestra el formulario de edición de una mesa.
     * @param  Mesa $mesa
     * @return View
     */
    public function edit(Mesa $mesa): View
    {
        return view('mesas.edit', compact('mesa'));
    }

    /**
     * @brief Actualiza el número de sillas de una mesa.
     * @param  Request $request  Campo: sillas
     * @param  Mesa    $mesa
     * @return RedirectResponse
     * @pre  La mesa debe existir y el campo sillas ser válido.
     * @post Se actualiza la mesa y se redirige al listado.
     */
    public function update(Request $request, Mesa $mesa): RedirectResponse
    {
        $request->validate([
            'sillas' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $mesa->update(['sillas' => $request->sillas]);

        return redirect()->route('mesas.index')
            ->with('success', "Mesa {$mesa->id_mesa} actualizada correctamente.");
    }

    /**
     * @brief Elimina una mesa del sistema.
     * @param  Mesa $mesa
     * @return RedirectResponse
     * @pre  La mesa no debe tener horarios activos asociados.
     * @post La mesa es eliminada y se redirige al listado.
     */
    public function destroy(Mesa $mesa): RedirectResponse
    {
        $idMesa = $mesa->id_mesa;

        // Verificar que no tenga horarios activos
        if ($mesa->horarios()->activos()->exists()) {
            return redirect()->route('mesas.index')
                ->withErrors(['error' => "La mesa {$idMesa} tiene reservaciones activas y no puede eliminarse."]);
        }

        $mesa->delete();

        return redirect()->route('mesas.index')
            ->with('success', "Mesa {$idMesa} eliminada correctamente.");
    }
}