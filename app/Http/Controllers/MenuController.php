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
        $tipos  = Tipo::with(['platos' => fn($q) => $q->orderBy('nombre')])->get();
        $platos = Plato::with('tipo')->orderBy('nombre')->get();
        return view('menu.index', compact('tipos', 'platos'));
    }

    public function create(): View
    {
        $tipos = Tipo::orderBy('nombre')->get();
        return view('menu.create', compact('tipos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
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

        Plato::create($request->only(['nombre', 'tipo_id', 'precio', 'descripcion', 'tiempo']));

        return redirect()->route('menu.index')
            ->with('success', 'Plato creado correctamente.');
    }

    public function edit(Plato $plato): View
    {
        $tipos = Tipo::orderBy('nombre')->get();
        return view('menu.edit', compact('plato', 'tipos'));
    }

    public function update(Request $request, Plato $plato): RedirectResponse
    {
        $request->validate([
            'nombre'      => ['required', 'string', 'max:100'],
            'tipo_id'     => ['required', 'exists:tipos,id'],
            'precio'      => ['required', 'numeric', 'min:0'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'tiempo'      => ['nullable', 'string'],
        ]);

        $plato->update($request->only(['nombre', 'tipo_id', 'precio', 'descripcion', 'tiempo']));

        return redirect()->route('menu.index')
            ->with('success', "Plato '{$plato->nombre}' actualizado.");
    }

    public function destroy(Plato $plato): RedirectResponse
    {
        $nombre = $plato->nombre;
        $plato->delete();

        return redirect()->route('menu.index')
            ->with('success', "Plato '{$nombre}' eliminado.");
    }
}