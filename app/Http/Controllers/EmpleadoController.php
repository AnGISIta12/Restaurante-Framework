<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Gestión de Empleados — solo Administrador.
 */
class EmpleadoController extends Controller
{
    public function index(): View
    {
        $empleados = Usuario::whereHas('roles', function ($q) {
            $q->where('nombre', '!=', Rol::CLIENTE);
        })->with('roles')->orderBy('nombre')->get();

        return view('empleados.index', compact('empleados'));
    }

    public function create(): View
    {
        $roles = Rol::where('nombre', '!=', Rol::CLIENTE)->orderBy('nombre')->get();
        return view('empleados.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre'   => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'rol_id'   => ['required', 'exists:roles,id'],
        ], [
            'nombre.required'   => 'El nombre es obligatorio.',
            'password.min'      => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'rol_id.required'   => 'Debes seleccionar un rol.',
        ]);

        if (Usuario::where('nombre', $request->nombre)->exists()) {
            return back()->withInput()
                ->withErrors(['nombre' => 'Ese nombre ya está registrado.']);
        }

        DB::transaction(function () use ($request) {
            $resultado = DB::selectOne(
                "INSERT INTO usuarios (nombre, clave, fecha_clave)
                 VALUES (?, sha256((?::text)::bytea), NOW())
                 RETURNING id",
                [$request->nombre, $request->password]
            );

            DB::table('actuaciones')->insert([
                'rol_id'     => $request->rol_id,
                'usuario_id' => $resultado->id,
            ]);
        });

        return redirect()->route('empleados.index')
            ->with('success', 'Empleado creado correctamente.');
    }

    public function edit(Usuario $usuario): View
    {
        $roles      = Rol::where('nombre', '!=', Rol::CLIENTE)->orderBy('nombre')->get();
        $rolActual  = $usuario->roles()->value('id');
        return view('empleados.edit', compact('usuario', 'roles', 'rolActual'));
    }

    public function update(Request $request, Usuario $usuario): RedirectResponse
    {
        $request->validate([
            'rol_id' => ['required', 'exists:roles,id'],
        ]);

        DB::table('actuaciones')
            ->where('usuario_id', $usuario->id)
            ->update(['rol_id' => $request->rol_id]);

        return redirect()->route('empleados.index')
            ->with('success', "Rol de {$usuario->nombre} actualizado.");
    }

    public function destroy(Usuario $usuario): RedirectResponse
    {
        $nombre = $usuario->nombre;
        DB::table('actuaciones')->where('usuario_id', $usuario->id)->delete();
        $usuario->delete();

        return redirect()->route('empleados.index')
            ->with('success', "Empleado '{$nombre}' eliminado.");
    }
}