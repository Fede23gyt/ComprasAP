<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleOferta extends Model
{
    protected $table = 'detalle_ofertas';

    protected $fillable = [
        'oferta_id',
        'det_presupuesto_id',
        'renglon',
        'insumo_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'marca',
        'modelo',
        'caracteristicas_tecnicas',
        'descripcion_ofertada',
        'tiempo_entrega',
        'garantia',
        'observaciones',
        'seleccionado',
        'motivo_seleccion',
        'fecha_seleccion'
    ];

    protected $casts = [
        'cantidad' => 'decimal:4',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tiempo_entrega' => 'integer',
        'seleccionado' => 'boolean',
        'fecha_seleccion' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relación con la oferta
     */
    public function oferta(): BelongsTo
    {
        return $this->belongsTo(Oferta::class);
    }

    /**
     * Relación con el insumo
     */
    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class);
    }

    /**
     * Relación con el detalle del presupuesto
     */
    public function detallePresupuesto(): BelongsTo
    {
        return $this->belongsTo(DetPresupuesto::class, 'det_presupuesto_id');
    }

    /**
     * Scopes
     */
    public function scopePorRenglon($query, $renglon)
    {
        return $query->where('renglon', $renglon);
    }

    public function scopePorInsumo($query, $insumoId)
    {
        return $query->where('insumo_id', $insumoId);
    }

    public function scopeConPrecio($query)
    {
        return $query->where('precio_unitario', '>', 0);
    }

    /**
     * Métodos de utilidad
     */
    public function calcularSubtotal(): void
    {
        $this->subtotal = $this->cantidad * $this->precio_unitario;
    }

    /**
     * Obtener el ahorro respecto al precio testigo del insumo
     */
    public function getAhorroAttribute(): float
    {
        if (!$this->insumo || !$this->insumo->precio_testigo) {
            return 0;
        }
        
        $precioTestigo = $this->insumo->precio_testigo;
        $diferencia = $precioTestigo - $this->precio_unitario;
        
        return max(0, $diferencia * $this->cantidad);
    }

    /**
     * Obtener el porcentaje de ahorro
     */
    public function getPorcentajeAhorroAttribute(): float
    {
        if (!$this->insumo || !$this->insumo->precio_testigo || $this->insumo->precio_testigo <= 0) {
            return 0;
        }
        
        $precioTestigo = $this->insumo->precio_testigo;
        $diferencia = $precioTestigo - $this->precio_unitario;
        
        if ($diferencia <= 0) {
            return 0;
        }
        
        return ($diferencia / $precioTestigo) * 100;
    }

    /**
     * Verificar si el precio es mejor que el testigo
     */
    public function isMejorPrecio(): bool
    {
        if (!$this->insumo || !$this->insumo->precio_testigo) {
            return false;
        }
        
        return $this->precio_unitario < $this->insumo->precio_testigo;
    }

    /**
     * Obtener información completa del detalle
     */
    public function getInfoCompletaAttribute(): array
    {
        return [
            'renglon' => $this->renglon,
            'insumo_codigo' => $this->insumo->codigo ?? '',
            'insumo_descripcion' => $this->insumo->descripcion ?? '',
            'cantidad' => $this->cantidad,
            'unidad_medida' => $this->insumo->unidad_medida ?? 'u',
            'precio_unitario' => $this->precio_unitario,
            'subtotal' => $this->subtotal,
            'precio_testigo' => $this->insumo->precio_testigo ?? 0,
            'ahorro' => $this->ahorro,
            'porcentaje_ahorro' => $this->porcentaje_ahorro,
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'caracteristicas' => $this->caracteristicas_tecnicas
        ];
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($detalle) {
            $detalle->calcularSubtotal();
        });
    }
}