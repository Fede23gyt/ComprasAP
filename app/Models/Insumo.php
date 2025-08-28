<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    use HasFactory;
    protected $fillable = [
        'parent_id',
        'descripcion',
        'codigo',
        'clasificacion',
        'presentacion',
        'unidad_solicitud',
        'precio_insumo',
        'precio_testigo',
        'inventariable',
        'registrable',
        'rend_tribunal',
        'conversion',
        'fecha_ultima',
        'tipo',
        'codigo_grupo',
    ];

  /**
   * The attributes that should be cast.
   */
  protected $casts = [
    'registrable' => 'boolean',
    'precio_testigo' => 'boolean',
    'inventariable' => 'boolean',
    'rend_tribunal' => 'boolean',
    'precio_insumo' => 'decimal:2',
    'conversion' => 'decimal:4',
    'fecha_ultima' => 'date',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
  ];

  /**
   * Scope para obtener solo los registrables
   */
  public function scopeRegistrables($query)
  {
    return $query->where('registrable', true);
  }

  /**
   * Scope para obtener solo los inventariables
   */
  public function scopeInventariables($query)
  {
    return $query->where('inventariable', true);
  }

  /**
   * Scope para obtener precios testigo
   */
  public function scopePreciosTestigo($query)
  {
    return $query->where('precio_testigo', true);
  }

  /**
   * Scope para buscar por código o descripción
   */
  public function scopeBuscar($query, $termino)
  {
    if (!$termino) {
      return $query;
    }

    return $query->where(function ($q) use ($termino) {
      $q->where('codigo', 'LIKE', "%{$termino}%")
        ->orWhere('descripcion', 'LIKE', "%{$termino}%");
    });
  }

  /**
   * Accessor para el precio formateado
   */
  public function getPrecioFormateadoAttribute()
  {
    return $this->precio_insumo ? '$' . number_format($this->precio_insumo, 2) : '$0.00';
  }

  /**
   * Accessor para la fecha última formateada
   */
  public function getFechaUltimaFormateadaAttribute()
  {
    return $this->fecha_ultima ? $this->fecha_ultima->format('d/m/Y') : null;
  }

  /**
   * Mutator para el precio del insumo
   */
  public function setPrecioInsumoAttribute($value)
  {
    $this->attributes['precio_insumo'] = $value !== '' ? (float) $value : null;
  }

  /**
   * Mutator para la conversión
   */
  public function setConversionAttribute($value)
  {
    $this->attributes['conversion'] = $value !== '' ? (float) $value : 0.00;
  }

  /**
   * Método para verificar si tiene precio actualizado
   */
  public function tienePrecioActualizado($dias = 30)
  {
    if (!$this->fecha_ultima) {
      return false;
    }

    return $this->fecha_ultima->diffInDays(now()) <= $dias;
  }

  /**
   * Método para obtener el estado del precio
   */
  public function getEstadoPrecio()
  {
    if (!$this->precio_insumo || $this->precio_insumo <= 0) {
      return 'sin_precio';
    }

    if (!$this->fecha_ultima) {
      return 'sin_fecha';
    }

    $diasDesdeActualizacion = $this->fecha_ultima->diffInDays(now());

    if ($diasDesdeActualizacion <= 30) {
      return 'actualizado';
    } elseif ($diasDesdeActualizacion <= 90) {
      return 'desactualizado';
    } else {
      return 'muy_desactualizado';
    }
  }

  /**
   * Relación con clasificación económica (si existe)
   */
  public function clasificacionEconomica()
  {
    return $this->belongsTo(ClasifEconomica::class, 'clasificacion', 'codigo');
  }
}
