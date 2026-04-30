<?php

namespace App\Http\Controllers;

use App\Models\Plato;
use App\Models\Tipo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestión del Menú — accesible por Administrador y Maitre.
 * Cocinero puede ver el menú (solo lectura).
 */
class MenuController extends Controller
{
    public function index(): View
    {
        $tipos  = Tipo::with(['platos' => fn($q) => $q->orderBy('nombre')])->get(); // Cargamos los tipos con sus platos ordenados por nombre para mostrar el menú organizado por categorías
        $platos = Plato::with('tipo')->orderBy('nombre')->get(); // Cargamos todos los platos con su tipo para mostrar el menú completo en una tabla ordenada por nombre, aunque también podríamos mostrarlo agrupado por tipo usando la relación cargada en $tipos
        return view('menu.index', compact('tipos', 'platos')); // Pasamos tanto los tipos con sus platos como la lista completa de platos a la vista para mostrar el menú de forma organizada y permitir acciones de edición y eliminación para Administrador y Maitre
    }

    public function create(): View
    {
        $tipos = Tipo::orderBy('nombre')->get(); // Obtenemos los tipos de plato ordenados alfabéticamente para mostrar en el dropdown de selección al crear un nuevo plato
        return view('menu.create', compact('tipos'));
    }

    public function store(Request $request): RedirectResponse // Valida y guarda un nuevo plato en la base de datos
    {
        $request->validate([ // Validamos los datos del formulario para crear un nuevo plato, asegurándonos de que el nombre, tipo y precio sean proporcionados y válidos, mientras que la descripción y tiempo de preparación son opcionales
            'nombre'      => ['required', 'string', 'max:100'],
            'tipo_id'     => ['required', 'exists:tipos,id'],
            'precio'      => ['required', 'numeric', 'min:0'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'tiempo'      => ['nullable', 'string'],
        ], [
            'nombre.required'  => 'El nombre del plato es obligatorio.',
            'tipo_id.required' => 'Debes seleccionar un tipo.',
            'precio.required'  => 'El precio es obligatorio.',
            'precio.numeric'   => 'El precio debe ser un número.',
        ]);

        Plato::create($request->only(['nombre', 'tipo_id', 'precio', 'descripcion', 'tiempo'])); // Creamos un nuevo plato en la base de datos con los datos validados del formulario

        return redirect()->route('menu.index')
            ->with('success', 'Plato creado correctamente.');
    }

    public function edit(Plato $plato): View
    {
        $tipos = Tipo::orderBy('nombre')->get(); // Obtenemos los tipos de plato ordenados alfabéticamente para mostrar en el dropdown de selección al editar un plato existente, permitiendo cambiar su tipo si es necesario
        return view('menu.edit', compact('plato', 'tipos'));
    }

    public function update(Request $request, Plato $plato): RedirectResponse
    {
        $request->validate([ // Validamos los datos del formulario para actualizar un plato existente, asegurándonos de que el nombre, tipo y precio sean proporcionados y válidos, mientras que la descripción y tiempo de preparación son opcionales
            'nombre'      => ['required', 'string', 'max:100'],
            'tipo_id'     => ['required', 'exists:tipos,id'],
            'precio'      => ['required', 'numeric', 'min:0'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'tiempo'      => ['nullable', 'string'],
        ]);

        $plato->update($request->only(['nombre', 'tipo_id', 'precio', 'descripcion', 'tiempo'])); // Actualizamos el plato existente en la base de datos con los datos validados del formulario

        return redirect()->route('menu.index')
            ->with('success', "Plato '{$plato->nombre}' actualizado.");
    }

    public function destroy(Plato $plato): RedirectResponse // Elimina un plato de la base de datos
    {
        $nombre = $plato->nombre; // Guardamos el nombre del plato antes de eliminarlo para mostrarlo en el mensaje de éxito después de la eliminación
        $plato->delete();

        return redirect()->route('menu.index')
            ->with('success', "Plato '{$nombre}' eliminado.");
    }
}