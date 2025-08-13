<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Oficina extends Model
{
  use HasRecursiveRelationships;

  protected $fillable = [
    'codigo_oficina',
    'nombre',
    'abreviacion',
    'estado',
    'parent_id',
    'descripcion',
    'telefono',
    'email',
    'direccion',
    'responsable',
  ];

  protected $casts = [
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
  ];

  /**
   * Relación padre
   */
  public function parent()
  {
    return $this->belongsTo(Oficina::class, 'parent_id');
  }

  /**
   * Relación hijos
   */
  public function children()
  {
    return $this->hasMany(Oficina::class, 'parent_id');
  }

  /**
   * Obtener todos los ancestros de la oficina
   */
  public function ancestors()
  {
    return $this->ancestorsAndSelf()->where('id', '!=', $this->id);
  }

  /**
   * Obtener todos los descendientes de la oficina
   */
  public function descendants()
  {
    return $this->descendantsAndSelf()->where('id', '!=', $this->id);
  }

  /**
   * Scope para mostrar solo habilitadas
   */
  public function scopeHabilitadas($query)
  {
    return $query->where('estado', 'Habilitada');
  }

  /**
   * Scope para mostrar solo deshabilitadas
   */
  public function scopeDeshabilitadas($query)
  {
    return $query->where('estado', 'No habilitada');
  }

  /**
   * Scope para oficinas raíz (sin padre)
   */
  public function scopeRaiz($query)
  {
    return $query->whereNull('parent_id');
  }

  /**
   * Scope para oficinas hoja (sin hijos)
   */
  public function scopeHoja($query)
  {
    return $query->whereDoesntHave('children');
  }

  /**
   * Scope para buscar por múltiples campos
   */
  public function scopeBuscar($query, $termino)
  {
    return $query->where(function ($q) use ($termino) {
      $q->where('nombre', 'like', "%{$termino}%")
        ->orWhere('codigo_oficina', 'like', "%{$termino}%")
        ->orWhere('abreviacion', 'like', "%{$termino}%")
        ->orWhere('descripcion', 'like', "%{$termino}%");
    });
  }

  /**
   * Accessor para el nombre completo con jerarquía
   */
  public function getNombreCompletoAttribute()
  {
    $nombres = $this->ancestorsAndSelf()->pluck('nombre')->toArray();
    return implode(' > ', $nombres);
  }

  /**
   * Accessor para verificar si es oficina raíz
   */
  public function getEsRaizAttribute()
  {
    return is_null($this->parent_id);
  }

  /**
   * Accessor para verificar si es oficina hoja
   */
  public function getEsHojaAttribute()
  {
    return $this->children()->count() === 0;
  }

  /**
   * Accessor para el nivel en la jerarquía (0 = raíz)
   */
  public function getNivelAttribute()
  {
    return $this->ancestors()->count();
  }

  /**
   * Accessor para verificar si está habilitada
   */
  public function getEstaHabilitadaAttribute()
  {
    return $this->estado === 'Habilitada';
  }

  /**
   * Mutator para convertir código a mayúsculas
   */
  public function setCodigoOficinaAttribute($value)
  {
    $this->attributes['codigo_oficina'] = strtoupper($value);
  }

  /**
   * Mutator para convertir abreviación a mayúsculas
   */
  public function setAbreviacionAttribute($value)
  {
    $this->attributes['abreviacion'] = strtoupper($value);
  }

  /**
   * Verificar si puede cambiar de estado (no tiene hijos habilitados)
   */
  public function puedeSerDeshabilitada()
  {
    return $this->children()->where('estado', 'Habilitada')->count() === 0;
  }

  /**
   * Verificar si puede ser padre de otra oficina
   */
  public function puedeSerPadre(Oficina $oficina)
  {
    // No puede ser padre de sí misma
    if ($this->id === $oficina->id) {
      return false;
    }

    // No puede ser padre de sus ancestros (evitar dependencia circular)
    return !$this->descendants()->where('id', $oficina->id)->exists();
  }

  /**
   * Obtener la ruta de breadcrumbs
   */
  public function getBreadcrumbs()
  {
    return $this->ancestorsAndSelf()
      ->get()
      ->map(function ($oficina) {
        return [
          'id' => $oficina->id,
          'nombre' => $oficina->nombre,
          'codigo' => $oficina->codigo_oficina,
        ];
      });
  }

  /**
   * Obtener estadísticas de la oficina
   */
  public function getEstadisticas()
  {
    return [
      'total_descendientes' => $this->descendants()->count(),
      'descendientes_habilitados' => $this->descendants()->habilitadas()->count(),
      'descendientes_deshabilitados' => $this->descendants()->deshabilitadas()->count(),
      'nivel_jerarquia' => $this->nivel,
      'es_raiz' => $this->es_raiz,
      'es_hoja' => $this->es_hoja,
    ];
  }

  /**
   * Evento para mantener consistencia en la jerarquía
   */
  protected static function booted()
  {
    static::updating(function ($oficina) {
      // Al deshabilitar una oficina, deshabilitar sus hijos
      if ($oficina->isDirty('estado') && $oficina->estado === 'No habilitada') {
        $oficina->descendants()->update(['estado' => 'No habilitada']);
      }
    });

    static::saved(function ($oficina) {
      // Recalcular el árbol si cambió el parent_id
      if ($oficina->wasChanged('parent_id')) {
        static::fixTree();
      }
    });
  }

  /**
   * Configuración específica para adjacency list
   */
  public function getDepthColumnName()
  {
    return 'oficinas.depth';
  }

  /**
   * Scope para obtener el árbol ordenado (PostgreSQL compatible)
   */
  public function scopeArbolOrdenado($query)
  {
    return $query->select('oficinas.*')->tree()->orderBy('oficinas.nombre');
  }

  /**
   * Override para manejar mejor las consultas recursivas en PostgreSQL
   */
  public function scopeTree($query)
  {
    if (config('database.default') === 'pgsql') {
      // Consulta específica para PostgreSQL
      return $query->select('oficinas.*')
        ->selectRaw('0 as tree_depth')
        ->whereNull('parent_id')
        ->with(['descendants' => function($query) {
          $query->orderBy('nombre');
        }]);
    }

    return parent::scopeTree($query);
  }

  /**
   * Obtener oficinas por nivel específico
   */
  public static function porNivel($nivel)
  {
    return static::whereHas('ancestors', function ($query) use ($nivel) {
      $query->havingRaw('COUNT(*) = ?', [$nivel]);
    });
  }
}
