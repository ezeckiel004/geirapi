<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Vérifie que l'utilisateur possède l'un des rôles autorisés
     * Usage : middleware('role:admin') ou middleware('role:admin,technician')
     */
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, $roles) || !$user->is_active) {
            return response()->json([
                'message' => 'Accès refusé — rôle insuffisant.',
            ], 403);
        }

        return $next($request);
    }
}
