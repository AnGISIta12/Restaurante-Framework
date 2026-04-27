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
        $roles = Rol::orderBy('nombre')->get();
        return view('registro', compact('roles'));
    }

    public function login(Request $request): RedirectResponse|\Illuminate\Http\Response
    {
        $request->validate([
            'nombre'   => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // 🔥 FIX REAL (HEX en vez de binario)
        $hashHex = hash('sha256', $request->password);

        $usuario = Usuario::where('nombre', $request->nombre)
            ->whereRaw("encode(clave, 'hex') = ?", [$hashHex])
            ->first();

        if (!$usuario) {
            return back()
                ->withInput(['nombre' => $request->nombre])
                ->withErrors(['auth' => 'Usuario o contraseña incorrectos.']);
        }

        // Obtener rol
        $rol = $usuario->roles()->value('nombre') ?? 'Cliente';

        // Guardar sesión
        Session::put('usuario_id',     $usuario->id);
        Session::put('usuario_nombre', $usuario->nombre);
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

        if (Usuario::where('nombre', $request->nombre)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['nombre' => 'Ese nombre ya existe.']);
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

        return redirect()->route('login')
            ->with('success', 'Cuenta creada correctamente.');
    }

    public function logout(): RedirectResponse
    {
        Session::flush();
        return redirect()->route('login');
    }
}
