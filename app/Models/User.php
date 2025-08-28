<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
  use HasFactory, Notifiable, HasRoles;

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
   * Obtener el rol principal del usuario (compatibilidad con sistema anterior)
   */
  public function getMainRole()
  {
    return $this->roles->first();
  }

  /**
   * Obtener el nombre del rol principal
   */
  public function getRoleNameAttribute(): ?string
  {
    $role = $this->getMainRole();
    return $role ? $role->display_name ?? $role->name : null;
  }

  /**
   * Verificar si es administrador
   */
  public function isAdmin(): bool
  {
    return $this->hasRole('administrador');
  }

  /**
   * Verificar si es supervisor (admin, secretario, director)
   */
  public function isSupervisor(): bool
  {
    return $this->hasAnyRole(['administrador', 'secretario', 'director']);
  }

  /**
   * Verificar si puede administrar configuración
   */
  public function canManageConfig(): bool
  {
    return $this->can('manage_users') || $this->isSupervisor();
  }

  /**
   * Verificar si puede crear notas para cualquier oficina
   */
  public function canCreateForAnyOffice(): bool
  {
    return $this->can('create_notes') || $this->isSupervisor();
  }

  /**
   * Verificar si puede autorizar notas
   */
  public function canAuthorize(): bool
  {
    return $this->can('authorize_notes') || $this->isSupervisor();
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
