<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con usuarios (compatibilidad con sistema anterior)
     */
    public function oldUsers(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Scope para roles activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para roles inactivos
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope para buscar roles
     */
    public function scopeSearch($query, $term)
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
              ->orWhere('display_name', 'LIKE', "%{$term}%")
              ->orWhere('description', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Verificar si el rol tiene un permiso específico (compatibilidad con sistema anterior)
     */
    public function hasCustomPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Verificar si tiene alguno de los permisos especificados (custom method)
     */
    public function hasAnyCustomPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasCustomPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verificar si tiene todos los permisos especificados (custom method)
     */
    public function hasAllCustomPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasCustomPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Verificar si es administrador
     */
    public function isAdmin(): bool
    {
        return $this->name === 'administrador';
    }

    /**
     * Verificar si es supervisor (admin, secretario, director)
     */
    public function isSupervisor(): bool
    {
        return in_array($this->name, ['administrador', 'secretario', 'director']);
    }

    /**
     * Verificar si puede gestionar configuración
     */
    public function canManageConfig(): bool
    {
        return $this->hasCustomPermission('manage_config') || $this->isAdmin();
    }

    /**
     * Verificar si puede gestionar usuarios
     */
    public function canManageUsers(): bool
    {
        return $this->hasCustomPermission('manage_users') || $this->isAdmin();
    }

    /**
     * Verificar si puede gestionar roles
     */
    public function canManageRoles(): bool
    {
        return $this->hasCustomPermission('manage_roles') || $this->isAdmin();
    }

    /**
     * Verificar si puede crear notas para cualquier oficina
     */
    public function canCreateForAnyOffice(): bool
    {
        return $this->hasCustomPermission('create_any_nota') || $this->isSupervisor();
    }

    /**
     * Verificar si puede autorizar notas
     */
    public function canAuthorize(): bool
    {
        return $this->hasCustomPermission('authorize_notas') || $this->isSupervisor();
    }

    /**
     * Verificar si puede ver todos los reportes
     */
    public function canViewAllReports(): bool
    {
        return $this->hasCustomPermission('view_all_reports') || $this->isSupervisor();
    }

    /**
     * Verificar si es un rol del sistema (no se puede eliminar)
     */
    public function isSystemRole(): bool
    {
        return in_array($this->name, ['administrador', 'secretario', 'director', 'operador', 'invitado']);
    }

    /**
     * Obtener los permisos como array con información adicional
     */
    public function getPermissionsWithDetailsAttribute(): array
    {
        $allPermissions = $this->getAvailablePermissions();
        $rolePermissions = $this->permissions ?? [];

        return collect($allPermissions)->map(function ($description, $permission) use ($rolePermissions) {
            return [
                'key' => $permission,
                'name' => $description,
                'has_permission' => in_array($permission, $rolePermissions),
            ];
        })->values()->toArray();
    }

    /**
     * Obtener contador de usuarios activos
     */
    public function getActiveUsersCountAttribute(): int
    {
        return $this->oldUsers()->where('is_active', true)->count();
    }

    /**
     * Obtener nivel de acceso (para ordenamiento)
     */
    public function getAccessLevelAttribute(): int
    {
        switch ($this->name) {
            case 'administrador':
                return 5;
            case 'secretario':
            case 'director':
                return 4;
            case 'operador':
                return 3;
            case 'invitado':
                return 1;
            default:
                return 2;
        }
    }

    /**
     * Obtener permisos disponibles en el sistema
     */
    private function getAvailablePermissions(): array
    {
        return [
            // Gestión de usuarios y sistema
            'manage_users' => 'Gestionar usuarios',
            'manage_roles' => 'Gestionar roles',
            'manage_offices' => 'Gestionar oficinas',
            'manage_config' => 'Gestionar configuración',

            // Notas de pedido
            'create_any_nota' => 'Crear notas para cualquier oficina',
            'create_own_notas' => 'Crear notas para oficinas asignadas',
            'authorize_notas' => 'Autorizar notas de pedido',
            'view_all_notas' => 'Ver todas las notas',
            'view_own_notas' => 'Ver propias notas',

            // Presupuestos y órdenes
            'manage_budgets' => 'Gestionar presupuestos',
            'manage_orders' => 'Gestionar órdenes de compra',
            'view_budgets' => 'Ver presupuestos',
            'view_orders' => 'Ver órdenes de compra',

            // Reportes
            'view_all_reports' => 'Ver todos los reportes',
            'view_own_reports' => 'Ver reportes propios',
            'export_reports' => 'Exportar reportes',

            // Nomencladores
            'manage_nomenclators' => 'Gestionar nomencladores',
            'view_nomenclators' => 'Ver nomencladores',
        ];
    }
}
