<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

// Gestión de Empleados — solo para el Rol del Administrador.
class EmpleadoController extends Controller
{
    public function index(): View // Muestra la lista de empleados (usuarios con roles distintos a Cliente)
    {
        $empleados = Usuario::whereHas('roles', function ($q) {
            $q->where('nombre', '!=', Rol::CLIENTE); // Solo usuarios que no sean Clientes
        })->with('roles')->orderBy('nombre')->get(); // Carga los roles para mostrar el nombre del rol en la vista y ordena por nombre

        return view('empleados.index', compact('empleados')); // Pasa la lista de empleados a la vista para mostrarla en una tabla
    }

    public function create(): View
    {
        $roles = Rol::where('nombre', '!=', Rol::CLIENTE)->orderBy('nombre')->get(); // Obtenemos los roles disponibles para asignar a un nuevo empleado, excluyendo el rol de Cliente
        return view('empleados.create', compact('roles')); // Pasamos los roles disponibles (excepto Cliente) a la vista para el dropdown de selección de rol al crear un nuevo empleado
    }

    public function store(Request $request): RedirectResponse // Valida y guarda un nuevo empleado en la base de datos
    {
        $request->validate([
            'nombre'   => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'rol_id'   => ['required', 'exists:roles,id_rol'],
        ], [
            'nombre.required'   => 'El nombre es obligatorio.',
            'password.min'      => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'rol_id.required'   => 'Debes seleccionar un rol.',
        ]);

        if (Usuario::where('nombre', $request->nombre)->exists()) { // Verificamos si ya existe un usuario con el mismo nombre para evitar duplicados
            return back()->withInput()
                ->withErrors(['nombre' => 'Ese nombre ya está registrado.']); // Si el nombre ya existe, volvemos al formulario con un mensaje de error específico para el campo 'nombre'
        }

        DB::transaction(function () use ($request) { // Usamos una transacción para asegurar que ambas operaciones (inserción en usuarios y actuacion) se realicen correctamente o se deshagan en caso de error
            $resultado = DB::selectOne(
                "INSERT INTO usuarios (nombre, clave, fecha_clave) 
                 VALUES (?, sha256((?::text)::bytea), NOW())
                 RETURNING id_usuario",
                [$request->nombre, $request->password] // Insertamos el nuevo usuario en la tabla de usuarios y obtenemos su ID para asignarle el rol seleccionado
            );

            DB::table('actuaciones')->insert([
                'rol_id'     => $request->rol_id,
                'usuario_id' => $resultado->id_usuario,
            ]); // Insertamos el ID del nuevo usuario en la tabla de actuacion para asignarle el rol seleccionado
        });

        return redirect()->route('empleados.index')
            ->with('success', 'Empleado creado correctamente.'); // Redirigimos a la lista de empleados con un mensaje de éxito después de crear el nuevo empleado
    }

    public function edit(Usuario $usuario): View
    {
        $roles      = Rol::where('nombre', '!=', Rol::CLIENTE)->orderBy('nombre')->get(); // Obtenemos los roles disponibles para asignar a un empleado, excluyendo el rol de Cliente
        $rolActual  = $usuario->roles()->value('id_rol'); // Obtenemos el ID del rol actual del usuario para mostrarlo seleccionado en el dropdown de edición
        return view('empleados.edit', compact('usuario', 'roles', 'rolActual'));
    }

    public function update(Request $request, Usuario $usuario): RedirectResponse
    {
        $request->validate([
            'rol_id' => ['required', 'exists:roles,id_rol'],
        ]); // Validamos que se haya seleccionado un rol válido para actualizar el rol del empleado

        DB::table('actuaciones')
            ->where('usuario_id', $usuario->id_usuario) // Buscamos la actuación del usuario para actualizar su rol
            ->update(['rol_id' => $request->rol_id]);  // Actualizamos el rol del empleado en la tabla de actuacion con el nuevo rol seleccionado

        return redirect()->route('empleados.index')
            ->with('success', "Rol de {$usuario->nombre} actualizado."); // Redirigimos a la lista de empleados con un mensaje de éxito después de actualizar el rol del empleado
    }

    public function destroy(Usuario $usuario): RedirectResponse // Elimina un empleado de la base de datos, eliminando primero su actuación para evitar problemas de integridad referencial
    {
        $nombre = $usuario->nombre;
        DB::table('actuaciones')->where('usuario_id', $usuario->id_usuario)->delete(); // Eliminamos la actuación del usuario para evitar problemas de integridad referencial al eliminar el usuario
        $usuario->delete();

        return redirect()->route('empleados.index')
            ->with('success', "Empleado '{$nombre}' eliminado."); // Redirigimos a la lista de empleados con un mensaje de éxito después de eliminar el empleado
    }
}