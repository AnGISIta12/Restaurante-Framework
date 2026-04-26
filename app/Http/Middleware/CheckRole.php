<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @brief Middleware que verifica el rol del usuario autenticado.
 *
 * Uso en rutas:
 *   Route::middleware(['auth', 'role:Administrador'])->group(...)
 *   Route::middleware(['auth', 'role:Maitre,Administrador'])->group(...)
 *
 * @pre  El usuario debe estar autenticado (middleware 'auth' primero).
 * @post Si el rol no coincide, redirige al dashboard con error 403.
 */
class CheckRole
{
    /**
     * @brief Maneja la solicitud entrante.
     * @param  Request  $request
     * @param  Closure  $next
     * @param  string   ...$roles  Uno o más roles permitidos separados por coma
     * @return Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $rolUsuario = session('rol');   // se guarda en AuthController al hacer login

        // Si no hay sesión de rol, desloguear
        if (!$rolUsuario) {
            return redirect()->route('login')
                ->withErrors(['auth' => 'Sesión expirada. Inicia sesión nuevamente.']);
        }

        // Verificar si el rol de la sesión está en la lista de roles permitidos
        $permitido = collect($roles)->contains(function ($rol) use ($rolUsuario) {
            return strtolower($rol) === strtolower($rolUsuario);
        });

        if (!$permitido) {
            return redirect()->route('dashboard')
                ->withErrors(['auth' => 'No tienes permiso para acceder a esa sección.']);
        }

        return $next($request);
    }
}