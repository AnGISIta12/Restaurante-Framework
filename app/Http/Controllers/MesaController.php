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

// AÑADIMOS LOS BRIEF DE NUESTRA BD PARA MAYOR GESTION Y LOGICA DE NEGOCIO EN LOS CONTROLADORES, ASI QUEDA TODO MAS CLARO Y ORDENADO, ADEMAS DE QUE SEGUIMOS LAS BUENAS PRACTICAS DE DOCUMENTACION PARA FACILITAR EL MANTENIMIENTO FUTURO DEL CODIGO
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
        $mesas       = Mesa::orderBy('id_mesa')->get(); // Obtenemos todas las mesas ordenadas por su ID para mostrar un listado organizado
        $cupoTotal   = Mesa::capacidadTotal(); // Calculamos la capacidad total del restaurante sumando las sillas de todas las mesas para mostrar esta información en el dashboard del Administrador

        return view('mesas.index', compact('mesas', 'cupoTotal')); // Lo mostramos :)
    }

    /**
     * @brief Muestra el formulario de creación de mesa.
     * @return View
     */
    public function create(): View
    {
        return view('mesas.create'); // Mostramos el formulario para crear una nueva mesa, donde el Administrador puede ingresar el número de sillas que tendrá la mesa
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
        $request->validate([ // Validamos el campo 'sillas' para asegurarnos de que sea un número entero entre 1 y 50, ya que una mesa no puede tener menos de 1 silla ni más de 50 sillas
            'sillas' => ['required', 'integer', 'min:1', 'max:50'],
        ], [
            'sillas.required' => 'El número de sillas es obligatorio.',
            'sillas.integer'  => 'Las sillas deben ser un número entero.',
            'sillas.min'      => 'La mesa debe tener al menos 1 silla.',
            'sillas.max'      => 'Una mesa no puede tener más de 50 sillas.',
        ]);

        Mesa::create(['sillas' => $request->sillas]); // Creamos una nueva mesa en la base de datos con el número de sillas proporcionado por el Administrador a través del formulario

        return redirect()->route('mesas.index')
            ->with('success', 'Mesa creada correctamente.'); // Redirigimos al listado de mesas con un mensaje de éxito después de crear la nueva mesa
    }

    /**
     * @brief Muestra el formulario de edición de una mesa.
     * @param  Mesa $mesa
     * @return View
     */
    public function edit(Mesa $mesa): View
    {
        return view('mesas.edit', compact('mesa')); // Mostramos el formulario de edición para la mesa especificada, permitiendo al Administrador modificar el número de sillas
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
        $request->validate([ // Validamos el campo 'sillas' para asegurarnos de que sea un número entero entre 1 y 50, ya que una mesa no puede tener menos de 1 silla ni más de 50 sillas
            'sillas' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $mesa->update(['sillas' => $request->sillas]); // Actualizamos el número de sillas de la mesa en la base de datos con el nuevo valor proporcionado por el Administrador a través del formulario de edición
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
        $idMesa = $mesa->id_mesa; // Guardamos el ID de la mesa antes de eliminarla para mostrarlo en el mensaje de éxito después de la eliminación

        // Verificar que no tenga horarios activos
        if ($mesa->horarios()->activos()->exists()) {
            return redirect()->route('mesas.index') // Redirigimos al listado de mesas con un mensaje de error si la mesa tiene horarios activos asociados, ya que no se puede eliminar una mesa que está reservada para algún horario
                ->withErrors(['error' => "La mesa {$idMesa} tiene reservaciones activas y no puede eliminarse."]);
        }

        $mesa->delete(); // Eliminamos la mesa de la base de datos si no tiene horarios activos asociados, permitiendo al Administrador eliminar mesas que ya no se utilizan o que fueron creadas por error

        return redirect()->route('mesas.index')
            ->with('success', "Mesa {$idMesa} eliminada correctamente.");
    }
}