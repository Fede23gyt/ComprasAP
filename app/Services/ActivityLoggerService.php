<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jaybizzle\LaravelCrawlerDetect\Facades\LaravelCrawlerDetect;
use Stevebauman\Location\Facades\Location;

class ActivityLoggerService
{
    /**
     * Registrar actividad de login
     */
    public static function logLogin(Request $request, $user = null)
    {
        $userAgent = $request->userAgent();
        $ipAddress = $request->ip();
        
        $deviceInfo = self::parseUserAgent($userAgent);
        $locationInfo = self::getLocationInfo($ipAddress);

        return ActivityLog::create([
            'user_id' => $user ? $user->id : null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'activity_type' => ActivityLog::TYPE_LOGIN,
            'description' => $user ? 'Login exitoso' : 'Intento de login fallido',
            'browser' => $deviceInfo['browser'],
            'platform' => $deviceInfo['platform'],
            'device' => $deviceInfo['device'],
            'country' => $locationInfo['country'] ?? null,
            'city' => $locationInfo['city'] ?? null,
            'login_at' => now(),
        ]);
    }

    /**
     * Registrar actividad de logout
     */
    public static function logLogout($user)
    {
        // Buscar el último login del usuario para calcular la duración de la sesión
        $lastLogin = ActivityLog::where('user_id', $user->id)
            ->where('activity_type', ActivityLog::TYPE_LOGIN)
            ->whereNull('logout_at')
            ->latest('login_at')
            ->first();

        if ($lastLogin) {
            // Calcular duración en segundos, asegurando que sea un entero positivo
            $sessionDuration = (int) abs(now()->diffInSeconds($lastLogin->login_at));
            
            $lastLogin->update([
                'logout_at' => now(),
                'session_duration' => $sessionDuration,
                'description' => 'Logout realizado'
            ]);

            return $lastLogin;
        }

        return null;
    }

    /**
     * Registrar intento de login fallido
     */
    public static function logFailedLogin(Request $request, $credentials)
    {
        $userAgent = $request->userAgent();
        $ipAddress = $request->ip();
        
        $deviceInfo = self::parseUserAgent($userAgent);
        $locationInfo = self::getLocationInfo($ipAddress);

        return ActivityLog::create([
            'user_id' => null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'activity_type' => ActivityLog::TYPE_FAILED_LOGIN,
            'description' => 'Intento de login fallido con email: ' . ($credentials['email'] ?? 'desconocido'),
            'browser' => $deviceInfo['browser'],
            'platform' => $deviceInfo['platform'],
            'device' => $deviceInfo['device'],
            'country' => $locationInfo['country'] ?? null,
            'city' => $locationInfo['city'] ?? null,
            'login_at' => now(),
        ]);
    }

    /**
     * Parsear user agent para obtener información del dispositivo
     */
    protected static function parseUserAgent($userAgent)
    {
        $browser = 'Desconocido';
        $platform = 'Desconocido';
        $device = 'Desconocido';

        // Detectar si es un crawler/bot
        if (LaravelCrawlerDetect::isCrawler($userAgent)) {
            $device = 'Bot/Crawler (' . LaravelCrawlerDetect::getMatches() . ')';
            return compact('browser', 'platform', 'device');
        }

        // Parsear browser y platform básico
        if (preg_match('/(chrome|firefox|safari|opera|edge|msie|trident)/i', $userAgent, $browserMatch)) {
            $browser = ucfirst(strtolower($browserMatch[1]));
        }

        if (preg_match('/(windows|macintosh|linux|android|ios|iphone|ipad)/i', $userAgent, $platformMatch)) {
            $platform = ucfirst(strtolower($platformMatch[1]));
        }

        // Detectar dispositivo
        if (preg_match('/(mobile|tablet|android|iphone|ipad)/i', $userAgent)) {
            $device = 'Móvil';
            if (preg_match('/(tablet|ipad)/i', $userAgent)) {
                $device = 'Tablet';
            }
        } else {
            $device = 'Escritorio';
        }

        return compact('browser', 'platform', 'device');
    }

    /**
     * Obtener información de ubicación por IP
     */
    protected static function getLocationInfo($ipAddress)
    {
        // Excluir IPs locales y de desarrollo
        if ($ipAddress === '127.0.0.1' || $ipAddress === '::1' || strpos($ipAddress, '192.168.') === 0) {
            return ['country' => 'Local', 'city' => 'Red Local'];
        }

        try {
            $location = Location::get($ipAddress);
            
            if ($location && $location->countryName) {
                return [
                    'country' => $location->countryName,
                    'city' => $location->cityName,
                ];
            }
        } catch (\Exception $e) {
            // Silenciar errores de geolocalización
        }

        return ['country' => null, 'city' => null];
    }

    /**
     * Obtener estadísticas de actividad
     */
    public static function getActivityStats($startDate, $endDate)
    {
        $loginStats = ActivityLog::getLoginStats($startDate, $endDate);
        $failedLogins = ActivityLog::ofType(ActivityLog::TYPE_FAILED_LOGIN)
            ->betweenDates($startDate, $endDate)
            ->count();

        $loginsByDay = ActivityLog::getLoginsByDay($startDate, $endDate);
        $topDevices = ActivityLog::getTopDevices($startDate, $endDate);

        return [
            'total_logins' => $loginStats->total_logins ?? 0,
            'unique_users' => $loginStats->unique_users ?? 0,
            'failed_logins' => $failedLogins,
            'avg_session_duration' => round($loginStats->avg_session_duration ?? 0),
            'logins_by_day' => $loginsByDay,
            'top_devices' => $topDevices,
            'success_rate' => $loginStats->total_logins > 0 ? 
                round(($loginStats->total_logins / ($loginStats->total_logins + $failedLogins)) * 100, 2) : 0
        ];
    }

    /**
     * Limpiar logs antiguos (más de 90 días)
     */
    public static function cleanupOldLogs($days = 90)
    {
        $cutoffDate = now()->subDays($days);
        
        return ActivityLog::where('login_at', '<', $cutoffDate)->delete();
    }
}