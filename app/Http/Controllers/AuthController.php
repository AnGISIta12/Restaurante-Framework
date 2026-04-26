<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

/**
 * @brief Controlador de autenticación del sistema.
 *
 * Maneja login, logout y registro de usuarios.
 * La contraseña se almacena y compara usando SHA-256 (compatible
 * con la BD existente del proyecto anterior).
 */
class AuthController extends Controller
{
    /*------------------------------------------------------------------
     | Formularios
     ------------------------------------------------------------------*/

    /**
     * @brief Muestra el formulario de login.
     * @return View|RedirectResponse
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Session::has('usuario_id')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * @brief Muestra el formulario de registro.
     * @return View|RedirectResponse
     */
    public function showRegistro(): View|RedirectResponse
    {
        if (Session::has('usuario_id')) {
            return redirect()->route('dashboard');
        }
        $roles = Rol::orderBy('nombre')->get();
        return view('auth.registro', compact('roles'));
    }

    /*------------------------------------------------------------------
     | Acciones
     ------------------------------------------------------------------*/

    /**
     * @brief Procesa el intento de login.
     *
     * Compara la contraseña usando SHA-256 puro (compatible con
     * sha256(password::bytea) de PostgreSQL).
     *
     * @param  Request $request  Campos: nombre, password
     * @return RedirectResponse
     * @pre  Los campos nombre y password deben estar presentes.
     * @post Si las credenciales son válidas, se guarda la sesión y
     *       se redirige al dashboard.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre'   => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'nombre.required'   => 'El nombre de usuario es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        // Hash SHA-256 en binario — idéntico al de PostgreSQL sha256()
        $hashBinario = hash('sha256', $request->password, true);

        $usuario = Usuario::where('nombre', $request->nombre)
            ->whereRaw('clave = ?', [$hashBinario])
            ->first();

        if (!$usuario) {
            return back()
                ->withInput(['nombre' => $request->nombre])
                ->withErrors(['auth' => 'Usuario o contraseña incorrectos.']);
        }

        // Obtener el rol (primer rol asignado al usuario)
        $rol = $usuario->roles()->value('nombre') ?? 'Cliente';

        // Guardar sesión
        Session::put('usuario_id',     $usuario->id_usuario);
        Session::put('usuario_nombre', $usuario->nombre);
        Session::put('rol',            $rol);

        return redirect()->route('dashboard');
    }

    /**
     * @brief Procesa el registro de un nuevo usuario.
     *
     * Inserta el usuario con sha256() en PostgreSQL para garantizar
     * compatibilidad exacta con el formato bytea de la BD.
     *
     * @param  Request $request  Campos: nombre, password, password_confirmation, rol_id
     * @return RedirectResponse
     * @pre  El nombre no debe existir ya en la BD.
     * @post Se crea el usuario y se asigna el rol en la tabla actuaciones.
     */
    public function registro(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre'                => ['required', 'string', 'max:100'],
            'password'              => ['required', 'string', 'min:6', 'confirmed'],
            'rol_id'                => ['required', 'exists:roles,id_rol'],
        ], [
            'nombre.required'            => 'El nombre es obligatorio.',
            'nombre.max'                 => 'El nombre no puede superar 100 caracteres.',
            'password.required'          => 'La contraseña es obligatoria.',
            'password.min'               => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed'         => 'Las contraseñas no coinciden.',
            'rol_id.required'            => 'Debes seleccionar un rol.',
            'rol_id.exists'              => 'El rol seleccionado no es válido.',
        ]);

        // Verificar nombre único
        if (Usuario::where('nombre', $request->nombre)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['nombre' => 'Ese nombre de usuario ya está registrado.']);
        }

        DB::transaction(function () use ($request) {
            // Insertar con SHA-256 en PostgreSQL (bytea)
            $resultado = DB::selectOne(
                "INSERT INTO usuarios (nombre, clave, fecha_clave)
                 VALUES (?, sha256((?::text)::bytea), NOW())
                 RETURNING id_usuario",
                [$request->nombre, $request->password]
            );

            $nuevoId = $resultado->id_usuario;

            // Asignar rol en tabla pivot actuaciones
            DB::table('actuaciones')->insert([
                'rol_id'     => $request->rol_id,
                'usuario_id' => $nuevoId,
            ]);
        });

        return redirect()->route('login')
            ->with('success', 'Cuenta creada correctamente. Ahora puedes iniciar sesión.');
    }

    /**
     * @brief Cierra la sesión del usuario actual.
     * @return RedirectResponse
     * @pre  Debe existir una sesión activa.
     * @post La sesión queda destruida y el usuario es redirigido al login.
     */
    public function logout(): RedirectResponse
    {
        Session::flush();
        return redirect()->route('login')
            ->with('success', 'Sesión cerrada correctamente.');
    }
}