<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifica que exista una sesión activa (usuario_id en session).
 * Reemplaza el middleware 'auth' estándar de Laravel porque
 * usamos SHA-256 y no bcrypt, por lo que no usamos Auth::login().
 */
class AuthSessionCustom
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('usuario_id')) {
            return redirect()->route('login')
                ->withErrors(['auth' => 'Debes iniciar sesión para continuar.']);
        }

        return $next($request);
    }
}