<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Insumo extends Model
{
    use HasFactory;
    use HasRecursiveRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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
     *
     * @var array<string, string>
     */
    protected $casts = [
        'precio_insumo'  => 'decimal:2',
        'precio_testigo' => 'boolean',
        'inventariable'  => 'boolean',
        'registrable'    => 'boolean',
        'rend_tribunal'  => 'boolean',
        'conversion'     => 'decimal:4',
        'fecha_ultima'   => 'date',
    ];

    /**
     * Get the parent insumo.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Insumo::class, 'parent_id');
    }

    /**
     * Get the children insumos.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Insumo::class, 'parent_id');
    }

  public function clasifEconomica()
  {
    return $this->belongsTo(ClasifEconomica::class, 'clasificacion', 'codigo');
  }
}
