<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'activity_type',
        'description',
        'browser',
        'platform',
        'device',
        'country',
        'city',
        'login_at',
        'logout_at',
        'session_duration'
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
        'session_duration' => 'integer'
    ];

    // Tipos de actividad predefinidos
    const TYPE_LOGIN = 'login';
    const TYPE_LOGOUT = 'logout';
    const TYPE_FAILED_LOGIN = 'failed_login';
    const TYPE_PASSWORD_RESET = 'password_reset';
    const TYPE_ACCOUNT_LOCKED = 'account_locked';

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para filtrar por tipo de actividad
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * Scope para filtrar por usuario
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para filtrar por rango de fechas
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('login_at', [$startDate, $endDate]);
    }

    /**
     * Obtener estadísticas de login por período
     */
    public static function getLoginStats($startDate, $endDate)
    {
        return self::ofType(self::TYPE_LOGIN)
            ->betweenDates($startDate, $endDate)
            ->selectRaw('COUNT(*) as total_logins')
            ->selectRaw('COUNT(DISTINCT user_id) as unique_users')
            ->selectRaw('AVG(session_duration) as avg_session_duration')
            ->first();
    }

    /**
     * Obtener logins por día para gráficos
     */
    public static function getLoginsByDay($startDate, $endDate)
    {
        return self::ofType(self::TYPE_LOGIN)
            ->betweenDates($startDate, $endDate)
            ->selectRaw('DATE(login_at) as date')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Obtener dispositivos más comunes
     */
    public static function getTopDevices($startDate, $endDate)
    {
        return self::ofType(self::TYPE_LOGIN)
            ->betweenDates($startDate, $endDate)
            ->selectRaw('device, COUNT(*) as count')
            ->whereNotNull('device')
            ->groupBy('device')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
    }
}
