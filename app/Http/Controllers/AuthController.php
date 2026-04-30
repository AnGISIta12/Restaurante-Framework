<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Session::has('usuario_id')) { // Ya autenticado el usuario 
            return redirect()->route('dashboard'); // Redirige al dashboard o a la página principal después de iniciar sesión
        }
        return view('login');
    }

    public function showRegistro(): View|RedirectResponse
    {
        if (Session::has('usuario_id')) { // Ya autenticado el usuario 
            return redirect()->route('dashboard'); // Redirige al dashboard o a la página principal después de iniciar sesión
        }
        $roles = Rol::orderBy('nombre')->get(); // Obtener roles ordenados alfabéticamente
        return view('registro', compact('roles')); // Pasar los roles a la vista para el dropdown
    }

    public function login(Request $request): RedirectResponse|\Illuminate\Http\Response
    {
        $request->validate([
            'nombre'   => ['required', 'string'], // Validación básica para el nombre de usuario
            'password' => ['required', 'string'], // Validación básica para la contraseña
        ]);

        // Hash de la contraseña usando SHA-256 ya que en la BD esta encriptada con esa función
        $hashHex = hash('sha256', $request->password);

        $usuario = Usuario::where('nombre', $request->nombre)  // Usamos whereRaw para comparar el hash hexadecimal con el valor almacenado en la base de datos
            ->whereRaw("encode(clave, 'hex') = ?", [$hashHex]) // La función encode(clave, 'hex') convierte el valor almacenado en la base de datos a formato hexadecimal para compararlo con el hash generado a partir de la contraseña ingresada por el usuario
            ->first();

        if (!$usuario) {
            return back()
                ->withInput(['nombre' => $request->nombre]) // Mantener el nombre de usuario ingresado en el formulario
                ->withErrors(['auth' => 'Usuario o contraseña incorrectos.']);
        }

        // Obtener rol
        $rol = $usuario->roles()->value('nombre') ?? 'Cliente';

        // Guardar sesión
        Session::put('usuario_id',     $usuario->id_usuario);
        Session::put('usuario_nombre', $usuario->nombre); // Guardamos el nombre del usuario en la sesión para mostrarlo en el dashboard
        Session::put('rol',            $rol);

        return redirect()->route('dashboard');
    }

    public function registro(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre'   => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:6', 'confirmed'], // La regla 'confirmed' requiere que haya un campo 'password_confirmation' con el mismo valor
            'rol_id'   => ['required', 'exists:roles,id_rol'], // Validación para asegurarse de que el rol seleccionado existe en la tabla de roles
        ]);

        if (Usuario::where('nombre', $request->nombre)->exists()) {
            return back()
                ->withInput() // Mantener los datos ingresados en el formulario
                ->withErrors(['nombre' => 'Ese nombre ya existe.']);
        }

        DB::transaction(function () use ($request) { // Usamos una transacción para asegurar que ambas operaciones (inserción en usuarios y actuacion) se realicen correctamente o se deshagan en caso de error
            $resultado = DB::selectOne( // Usamos DB::selectOne para ejecutar la consulta SQL directamente y obtener el ID del nuevo usuario insertado
                "INSERT INTO usuarios (nombre, clave, fecha_clave) 
                 VALUES (?, sha256((?::text)::bytea), NOW()) 
                 RETURNING id_usuario",
                [$request->nombre, $request->password]
            );

            DB::table('actuaciones')->insert([
                'rol_id'     => $request->rol_id,
                'usuario_id' => $resultado->id_usuario, // Insertamos el ID del nuevo usuario en la tabla de actuacion para asignarle el rol seleccionado
            ]);
        });

        return redirect()->route('login')
            ->with('success', 'Cuenta creada correctamente.');
    }

    public function logout(): RedirectResponse // Método para cerrar sesión
    {
        Session::flush();
        return redirect()->route('login');
    }
}
