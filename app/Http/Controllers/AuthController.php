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
        if (Session::has('usuario_id')) {
            return redirect()->route('dashboard');
        }
        return view('login');
    }

    public function showRegistro(): View|RedirectResponse
    {
        if (Session::has('usuario_id')) {
            return redirect()->route('dashboard');
        }

        // Garantiza que los roles operativos existan para el selector de registro.
        foreach ([
            Rol::ADMINISTRADOR,
            Rol::MAITRE,
            Rol::MESERO,
            Rol::COCINERO,
            Rol::CLIENTE,
        ] as $nombreRol) {
            Rol::firstOrCreate(['nombre' => $nombreRol]);
        }

        $roles = Rol::orderBy('nombre')->get();
        return view('registro', compact('roles'));
    }

    public function login(Request $request): RedirectResponse|\Illuminate\Http\Response
    {
        $request->validate([
            'nombre'   => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $usuario = \App\Models\User::where('name', $request->nombre)->first();

        if (!$usuario || !\Illuminate\Support\Facades\Hash::check($request->password, $usuario->password)) {
            return back()
                ->withInput(['nombre' => $request->nombre])
                ->withErrors(['auth' => 'Usuario o contraseña incorrectos.']);
        }

        // Obtener rol
        $rolModel = Rol::find($usuario->rol_id);
        $rol = $rolModel ? $rolModel->nombre : 'Cliente';

        // Guardar sesión
        Session::put('usuario_id',     $usuario->id);
        Session::put('usuario_nombre', $usuario->name);
        Session::put('rol',            ucfirst($rol));

        // 👇 PRUEBA
        return redirect()->route('dashboard');
    }

    public function registro(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre'   => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'rol_id'   => ['required', 'exists:roles,id'],
        ]);

        if (\App\Models\User::where('name', $request->nombre)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['nombre' => 'Ese nombre ya existe.']);
        }

        DB::transaction(function () use ($request) {
            \App\Models\User::create([
                'name'     => $request->nombre,
                'email'    => uniqid() . '@restaurante.com', // generamos un correo por defecto
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                'rol_id'   => $request->rol_id,
            ]);
        });

        return redirect()->route('login')
            ->with('success', 'Cuenta creada correctamente.');
    }

    public function logout(): RedirectResponse
    {
        Session::flush();
        return redirect()->route('login');
    }
}
