<?php

namespace App\Services;

use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Stevebauman\Location\Facades\Location;

class UserTrackingService
{
    protected Agent $agent;

    public function __construct()
    {
        $this->agent = new Agent();
    }

    /**
     * Collecte toutes les informations de tracking depuis la requête
     */
    public function collectTrackingData(Request $request, array $clientData = []): array
    {
        // Vérification du consentement RGPD
        $hasConsent = $request->cookie('tracking_consent') === 'accepted';
        
        if (!$hasConsent) {
            // Collecte minimale sans consentement (conformité RGPD)
            return [
                'ip_address' => null, // Pas d'IP sans consentement
                'user_agent' => null, // Pas d'user agent sans consentement
                'country' => null,
                'country_name' => null,
                'city' => null,
                'device_type' => null,
                'browser' => null,
                'browser_version' => null,
                'operating_system' => null,
                'platform' => null,
                'is_mobile' => false,
                'is_tablet' => false,
                'is_desktop' => true,
                'language' => null,
                'timezone' => null,
                'screen_width' => null,
                'screen_height' => null,
            ];
        }
        
        // Collecte complète avec consentement
        $ipAddress = $this->getClientIpAddress($request);
        $userAgent = $request->userAgent();

        // Géolocalisation
        $locationData = $this->getLocationData($ipAddress);

        // Analyse du user agent
        $this->agent->setUserAgent($userAgent);
        $deviceData = $this->getDeviceData();
        $browserData = $this->getBrowserData();
        $platformData = $this->getPlatformData();

        // Données client (envoyées depuis le frontend)
        $clientInfo = $this->parseClientData($clientData);

        return array_merge([
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'language' => $this->getLanguage($request),
        ], $locationData, $deviceData, $browserData, $platformData, $clientInfo);
    }

    /**
     * Obtient l'adresse IP réelle du client
     */
    protected function getClientIpAddress(Request $request): string
    {
        // Vérifie les headers proxy/CDN courants
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_REAL_IP',            // Nginx
            'HTTP_X_FORWARDED_FOR',      // Standard proxy header
            'HTTP_X_FORWARDED',          // Alternative
            'HTTP_FORWARDED_FOR',        // Alternative
            'HTTP_FORWARDED',            // RFC 7239
            'REMOTE_ADDR'                // Fallback
        ];

        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) && !empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                
                // Si c'est une liste d'IPs, prend la première
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Valide l'IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $request->ip() ?? '127.0.0.1';
    }

    /**
     * Obtient les données de géolocalisation
     */
    protected function getLocationData(string $ipAddress): array
    {
        try {
            // Pour le développement local, utilise une IP publique de test
            if ($ipAddress === '127.0.0.1' || $ipAddress === '::1' || 
                filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) === false) {
                $ipAddress = '8.8.8.8'; // IP de Google pour test
            }

            $position = Location::get($ipAddress);

            if ($position) {
                return [
                    'country' => $position->countryCode,
                    'country_name' => $position->countryName,
                    'city' => $position->cityName,
                ];
            }
        } catch (\Exception $e) {
            \Log::warning('Erreur géolocalisation: ' . $e->getMessage());
        }

        return [
            'country' => null,
            'country_name' => null,
            'city' => null,
        ];
    }

    /**
     * Obtient les données sur l'appareil
     */
    protected function getDeviceData(): array
    {
        $deviceType = 'desktop';
        $isMobile = $this->agent->isMobile();
        $isTablet = $this->agent->isTablet();
        $isDesktop = $this->agent->isDesktop();

        if ($isTablet) {
            $deviceType = 'tablet';
        } elseif ($isMobile) {
            $deviceType = 'mobile';
        }

        return [
            'device_type' => $deviceType,
            'is_mobile' => $isMobile,
            'is_tablet' => $isTablet,
            'is_desktop' => $isDesktop,
        ];
    }

    /**
     * Obtient les données du navigateur
     */
    protected function getBrowserData(): array
    {
        return [
            'browser' => $this->agent->browser(),
            'browser_version' => $this->agent->version($this->agent->browser()),
        ];
    }

    /**
     * Obtient les données de la plateforme/OS
     */
    protected function getPlatformData(): array
    {
        $platform = $this->agent->platform();
        $platformVersion = $this->agent->version($platform);

        return [
            'operating_system' => $platform,
            'platform' => $this->normalizePlatform($platform),
        ];
    }

    /**
     * Normalise le nom de la plateforme
     */
    protected function normalizePlatform(string $platform): string
    {
        $platform = strtolower($platform);
        
        if (str_contains($platform, 'windows')) {
            return 'Windows';
        } elseif (str_contains($platform, 'mac') || str_contains($platform, 'os x')) {
            return 'macOS';
        } elseif (str_contains($platform, 'linux')) {
            return 'Linux';
        } elseif (str_contains($platform, 'ios')) {
            return 'iOS';
        } elseif (str_contains($platform, 'android')) {
            return 'Android';
        }
        
        return ucfirst($platform);
    }

    /**
     * Obtient la langue préférée
     */
    protected function getLanguage(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');
        
        if ($acceptLanguage) {
            // Parse le premier code de langue
            preg_match('/([a-zA-Z]{2}(-[a-zA-Z]{2})?)/', $acceptLanguage, $matches);
            return $matches[1] ?? null;
        }
        
        return null;
    }

    /**
     * Parse les données client envoyées depuis le frontend
     */
    protected function parseClientData(array $clientData): array
    {
        return [
            'timezone' => $clientData['timezone'] ?? null,
            'screen_width' => isset($clientData['screen_width']) ? (int) $clientData['screen_width'] : null,
            'screen_height' => isset($clientData['screen_height']) ? (int) $clientData['screen_height'] : null,
        ];
    }

    /**
     * Analyse les statistiques d'utilisation par pays
     */
    public function getCountryStats(\DateTime $startDate = null, \DateTime $endDate = null): array
    {
        $query = \DB::table('ai_chat_sessions')
            ->select('country', 'country_name', \DB::raw('COUNT(*) as sessions_count'))
            ->whereNotNull('country')
            ->groupBy('country', 'country_name')
            ->orderBy('sessions_count', 'desc');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query->get()->toArray();
    }

    /**
     * Analyse les statistiques d'utilisation par appareil
     */
    public function getDeviceStats(\DateTime $startDate = null, \DateTime $endDate = null): array
    {
        $query = \DB::table('ai_chat_sessions')
            ->select('device_type', \DB::raw('COUNT(*) as sessions_count'))
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->orderBy('sessions_count', 'desc');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query->get()->toArray();
    }

    /**
     * Analyse les statistiques d'utilisation par navigateur
     */
    public function getBrowserStats(\DateTime $startDate = null, \DateTime $endDate = null): array
    {
        $query = \DB::table('ai_chat_sessions')
            ->select('browser', \DB::raw('COUNT(*) as sessions_count'))
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderBy('sessions_count', 'desc')
            ->limit(10);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query->get()->toArray();
    }

    /**
     * Obtient les statistiques générales
     */
    public function getGeneralStats(\DateTime $startDate = null, \DateTime $endDate = null): array
    {
        $query = \DB::table('ai_chat_sessions');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $totalSessions = $query->count();
        $uniqueUsers = $query->distinct('user_id')->whereNotNull('user_id')->count();
        $anonymousSessions = $query->whereNull('user_id')->count();
        
        // Sessions par jour (derniers 30 jours)
        $dailyStats = \DB::table('ai_chat_sessions')
            ->select(\DB::raw('DATE(created_at) as date'), \DB::raw('COUNT(*) as sessions'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy(\DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->toArray();

        return [
            'total_sessions' => $totalSessions,
            'unique_users' => $uniqueUsers,
            'anonymous_sessions' => $anonymousSessions,
            'daily_stats' => $dailyStats,
        ];
    }
}