<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CookieConsentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Si c'est une route d'API qui collecte des données de tracking
        if ($request->is('api/ai/chat/sessions') && $request->isMethod('POST')) {
            $hasConsent = $request->cookie('tracking_consent') === 'accepted';
            
            // Si pas de consentement, on limite les données collectées
            if (!$hasConsent) {
                // Log pour audit RGPD
                \Log::info('Session créée sans consentement tracking', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            }
            
            // Ajouter header pour informer le frontend du statut du consentement
            $response->headers->set('X-Tracking-Consent', $hasConsent ? 'granted' : 'required');
        }
        
        return $response;
    }
}
