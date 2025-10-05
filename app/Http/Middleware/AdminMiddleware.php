<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            // Pour les requêtes API, retourner une erreur JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non authentifié. Veuillez vous connecter.'
                ], 401);
            }
            
            // Pour les requêtes web, rediriger vers login
            return redirect()->route('login');
        }

        if (!auth()->user()->is_admin) {
            // Pour les requêtes API, retourner une erreur JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé. Vous devez être administrateur.'
                ], 403);
            }
            
            // Pour les requêtes web, abort avec code d'erreur
            abort(403, 'Accès non autorisé. Vous devez être administrateur.');
        }

        return $next($request);
    }
}
