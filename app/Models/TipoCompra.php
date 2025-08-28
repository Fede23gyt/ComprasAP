<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoCompra extends Model
{
  use HasFactory;

  /**
   * The table associated with the model.
   */
  protected $table = 'tipo_compra';

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = [
    'descripcion',
    'estado',
  ];

  /**
   * The attributes that should be cast.
   */
  protected $casts = [
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
  ];

  /**
   * Boot the model.
   */
  protected static function boot()
  {
    parent::boot();

    // Evento para formatear la descripción antes de guardar
    static::saving(function ($tipoCompra) {
      $tipoCompra->descripcion = ucfirst(strtolower(trim($tipoCompra->descripcion)));
    });
  }

  /**
   * Scope para obtener solo los habilitados
   */
  public function scopeHabilitados($query)
  {
    return $query->where('estado', 'Habilitado');
  }

  /**
   * Scope para obtener solo los deshabilitados
   */
  public function scopeDeshabilitados($query)
  {
    return $query->where('estado', 'No habilitado');
  }

  /**
   * Scope para búsqueda por descripción
   */
  public function scopeBuscar($query, $termino)
  {
    if (!$termino) {
      return $query;
    }

    return $query->where('descripcion', 'LIKE', "%{$termino}%");
  }

  /**
   * Accessor para el estado con formato
   */
  public function getEstadoFormateadoAttribute()
  {
    return $this->estado === 'Habilitado' ? 'Activo' : 'Inactivo';
  }

  /**
   * Accessor para verificar si está habilitado
   */
  public function getEsHabilitadoAttribute()
  {
    return $this->estado === 'Habilitado';
  }

  /**
   * Mutator para la descripción
   */
  public function setDescripcionAttribute($value)
  {
    $this->attributes['descripcion'] = ucfirst(strtolower(trim($value)));
  }

  /**
   * Relación con Compras (si existe la tabla compras)
   * Descomenta cuando tengas la tabla compras
   */
  /*
  public function compras()
  {
      return $this->hasMany(Compra::class, 'tipo_compra_id');
  }
  */

  /**
   * Get route key name for implicit model binding
   */
  public function getRouteKeyName()
  {
    return 'id';
  }

  /**
   * Método para verificar si se puede eliminar
   */
  public function puedeEliminarse()
  {
    // Verificar relaciones antes de eliminar
    // return !$this->compras()->exists();
    return true; // Por ahora siempre permitir
  }

  /**
   * Método estático para obtener los estados disponibles
   */
  public static function getEstadosDisponibles()
  {
    return [
      'Habilitado' => 'Habilitado',
      'No habilitado' => 'No habilitado',
    ];
  }

  /**
   * Método para cambiar el estado
   */
  public function toggleEstado()
  {
    $this->estado = $this->estado === 'Habilitado' ? 'No habilitado' : 'Habilitado';
    return $this->save();
  }

  /**
   * Método para obtener estadísticas
   */
  public static function getEstadisticas()
  {
    return [
      'total' => self::count(),
      'habilitados' => self::habilitados()->count(),
      'deshabilitados' => self::deshabilitados()->count(),
    ];
  }
}
