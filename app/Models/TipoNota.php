<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoNota extends Model
{
  use HasFactory;

  /**
   * The table associated with the model.
   */
  protected $table = 'tipo_nota';

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
    static::saving(function ($tipoNota) {
      $tipoNota->descripcion = ucfirst(strtolower(trim($tipoNota->descripcion)));
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
    return $query->where('estado', 'Deshabilitado');
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
    // return !$this->notasPedido()->exists();
    return true; // Por ahora siempre permitir
  }

  /**
   * Método estático para obtener los estados disponibles
   */
  public static function getEstadosDisponibles()
  {
    return [
      'Habilitado' => 'Habilitado',
      'Deshabilitado' => 'Deshabilitado',
    ];
  }

  /**
   * Método para cambiar el estado
   */
  public function toggleEstado()
  {
    $this->estado = $this->estado === 'Habilitado' ? 'Deshabilitado' : 'Habilitado';
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

  /**
   * Relación con notas de pedido (cuando exista la tabla)
   * Descomenta cuando tengas la tabla notas_pedido
   */
  /*
  public function notasPedido()
  {
      return $this->hasMany(NotaPedido::class, 'tipo_nota_id');
  }
  */
}
