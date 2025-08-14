<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
  use HasFactory, Notifiable;

  protected $fillable = [
    'name',
    'email',
    'password',
    'dni',
    'telefono',
    'role_id',
    'is_active',
    'observaciones',
  ];

  protected $hidden = [
    'password',
    'remember_token',
  ];

  protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
    'is_active' => 'boolean',
  ];

  /**
   * Relación con rol
   */
  public function role()
  {
    return $this->belongsTo(Role::class);
  }

  /**
   * Relación con oficinas (muchas a muchas)
   */
  public function oficinas()
  {
    return $this->belongsToMany(Oficina::class, 'user_oficinas')
      ->withPivot(['es_principal', 'puede_autorizar'])
      ->withTimestamps();
  }

  /**
   * Obtener la oficina principal del usuario
   */
  public function oficinaPrincipal()
  {
    return $this->oficinas()->wherePivot('es_principal', true)->first();
  }

  /**
   * Obtener oficinas donde puede autorizar
   */
  public function oficinasQueAutoriza()
  {
    return $this->oficinas()->wherePivot('puede_autorizar', true);
  }

  /**
   * Scope para usuarios activos
   */
  public function scopeActive($query)
  {
    return $query->where('is_active', true);
  }

  /**
   * Scope para usuarios por rol
   */
  public function scopeByRole($query, string $roleName)
  {
    return $query->whereHas('role', function ($q) use ($roleName) {
      $q->where('name', $roleName);
    });
  }

  /**
   * Verificar si el usuario tiene un rol específico
   */
  public function hasRole(string $roleName): bool
  {
    return $this->role && $this->role->name === $roleName;
  }

  /**
   * Verificar si el usuario tiene alguno de los roles especificados
   */
  public function hasAnyRole(array $roles): bool
  {
    return $this->role && in_array($this->role->name, $roles);
  }

  /**
   * Verificar si el usuario tiene un permiso específico
   */
  public function hasPermission(string $permission): bool
  {
    return $this->role && $this->role->hasPermission($permission);
  }

  /**
   * Verificar si es administrador
   */
  public function isAdmin(): bool
  {
    return $this->role && $this->role->isAdmin();
  }

  /**
   * Verificar si es supervisor (admin, secretario, director)
   */
  public function isSupervisor(): bool
  {
    return $this->role && $this->role->isSupervisor();
  }

  /**
   * Verificar si puede administrar configuración
   */
  public function canManageConfig(): bool
  {
    return $this->role && $this->role->canManageConfig();
  }

  /**
   * Verificar si puede crear notas para cualquier oficina
   */
  public function canCreateForAnyOffice(): bool
  {
    return $this->role && $this->role->canCreateForAnyOffice();
  }

  /**
   * Verificar si puede autorizar notas
   */
  public function canAuthorize(): bool
  {
    return $this->role && $this->role->canAuthorize();
  }

  /**
   * Verificar si puede crear notas para una oficina específica
   */
  public function canCreateForOffice(int $oficinaId): bool
  {
    // Si puede crear para cualquier oficina, retorna true
    if ($this->canCreateForAnyOffice()) {
      return true;
    }

    // Verificar si tiene asignada esa oficina
    return $this->oficinas()->where('oficina_id', $oficinaId)->exists();
  }

  /**
   * Verificar si puede autorizar notas de una oficina específica
   */
  public function canAuthorizeForOffice(int $oficinaId): bool
  {
    // Si puede autorizar cualquier nota, retorna true
    if ($this->canAuthorize()) {
      return true;
    }

    // Verificar si puede autorizar en esa oficina específica
    return $this->oficinas()->where('oficina_id', $oficinaId)
      ->wherePivot('puede_autorizar', true)
      ->exists();
  }

  /**
   * Obtener IDs de oficinas asignadas
   */
  public function getOficinasIdsAttribute(): array
  {
    return $this->oficinas->pluck('id')->toArray();
  }

  /**
   * Obtener nombre completo del rol
   */
  public function getRoleNameAttribute(): ?string
  {
    return $this->role?->display_name;
  }

  /**
   * Verificar si el usuario está activo
   */
  public function isActive(): bool
  {
    return $this->is_active && ($this->role?->is_active ?? false);
  }

  /**
   * Asignar oficina principal
   */
  public function setOficinaPrincipal(int $oficinaId): void
  {
    // Quitar principal de todas las oficinas
    $this->oficinas()->updateExistingPivot(
      $this->oficinas->pluck('id')->toArray(),
      ['es_principal' => false]
    );

    // Asignar como principal
    if ($this->oficinas()->where('oficina_id', $oficinaId)->exists()) {
      $this->oficinas()->updateExistingPivot($oficinaId, ['es_principal' => true]);
    } else {
      $this->oficinas()->attach($oficinaId, ['es_principal' => true]);
    }
  }

  /**
   * Asignar múltiples oficinas
   */
  public function syncOficinas(array $oficinaIds, int $principalId = null): void
  {
    $syncData = [];

    foreach ($oficinaIds as $oficinaId) {
      $syncData[$oficinaId] = [
        'es_principal' => $oficinaId == $principalId,
        'puede_autorizar' => false, // Por defecto false, se puede cambiar después
      ];
    }

    $this->oficinas()->sync($syncData);
  }
}
