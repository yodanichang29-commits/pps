<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidarRol
{
    /**
     * Maneja una solicitud entrante.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles - Roles permitidos (admin, estudiante, supervisor)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Verificar que el usuario esté autenticado
        if (!auth()->check()) {
            abort(403, 'No autenticado.');
        }

        $user = auth()->user();

        // Verificar que el usuario tenga un rol asignado
        if (empty($user->rol)) {
            abort(403, 'Usuario sin rol asignado.');
        }

        // Verificar que el rol del usuario esté en los roles permitidos
        if (!in_array($user->rol, $roles, true)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
