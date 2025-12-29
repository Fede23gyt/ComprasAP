<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdjudicacionDetalle extends Model
{
    protected $table = 'adjudicacion_detalles';

    protected $fillable = [
        'adjudicacion_id',
        'renglon',
        'insumo_id',
        'cantidad_adjudicada',
        'precio_unitario_adjudicado',
        'subtotal_adjudicado',
        'marca',
        'modelo',
        'caracteristicas_tecnicas',
        'garantia',
        'tiempo_entrega',
        'observaciones',
        'cantidad_original',
        'precio_unitario_original',
        'variacion_precio_porcentaje',
        'variacion_precio_monto'
    ];

    protected $casts = [
        'cantidad_adjudicada' => 'decimal:2',
        'precio_unitario_adjudicado' => 'decimal:2',
        'subtotal_adjudicado' => 'decimal:2',
        'cantidad_original' => 'decimal:2',
        'precio_unitario_original' => 'decimal:2',
        'variacion_precio_porcentaje' => 'decimal:2',
        'variacion_precio_monto' => 'decimal:2',
        'tiempo_entrega' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relación con la adjudicación
     */
    public function adjudicacion(): BelongsTo
    {
        return $this->belongsTo(Adjudicacion::class);
    }

    /**
     * Relación con el insumo
     */
    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class);
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

    public function scopeConVariacionPrecio($query, $umbral = 0)
    {
        return $query->where('variacion_precio_porcentaje', '>', $umbral);
    }

    public function scopeConMejorPrecio($query)
    {
        return $query->where('variacion_precio_porcentaje', '<', 0);
    }

    /**
     * Métodos de utilidad
     */
    public function calcularSubtotal(): void
    {
        $this->subtotal_adjudicado = $this->cantidad_adjudicada * $this->precio_unitario_adjudicado;
    }

    /**
     * Calcular variación de precio respecto al original
     */
    public function calcularVariacionPrecio(): void
    {
        if ($this->precio_unitario_original > 0) {
            $variacionMonto = $this->precio_unitario_original - $this->precio_unitario_adjudicado;
            $variacionPorcentaje = ($variacionMonto / $this->precio_unitario_original) * 100;
            
            $this->variacion_precio_monto = $variacionMonto;
            $this->variacion_precio_porcentaje = $variacionPorcentaje;
        } else {
            $this->variacion_precio_monto = 0;
            $this->variacion_precio_porcentaje = 0;
        }
    }

    /**
     * Obtener el ahorro total del renglón
     */
    public function getAhorroTotalAttribute(): float
    {
        if ($this->precio_unitario_original > 0) {
            $ahorroUnitario = $this->precio_unitario_original - $this->precio_unitario_adjudicado;
            return $ahorroUnitario * $this->cantidad_adjudicada;
        }
        
        return 0;
    }

    /**
     * Verificar si el precio adjudicado es mejor que el original
     */
    public function isMejorPrecio(): bool
    {
        return $this->precio_unitario_adjudicado < $this->precio_unitario_original;
    }

    /**
     * Verificar si hay variación en la cantidad
     */
    public function tieneVariacionCantidad(): bool
    {
        return $this->cantidad_adjudicada != $this->cantidad_original;
    }

    /**
     * Obtener la variación de cantidad
     */
    public function getVariacionCantidadAttribute(): float
    {
        return $this->cantidad_adjudicada - $this->cantidad_original;
    }

    /**
     * Obtener el porcentaje de variación de cantidad
     */
    public function getVariacionCantidadPorcentajeAttribute(): float
    {
        if ($this->cantidad_original > 0) {
            return (($this->cantidad_adjudicada - $this->cantidad_original) / $this->cantidad_original) * 100;
        }
        
        return 0;
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
            'cantidad_original' => $this->cantidad_original,
            'cantidad_adjudicada' => $this->cantidad_adjudicada,
            'variacion_cantidad' => $this->variacion_cantidad,
            'variacion_cantidad_porcentaje' => $this->variacion_cantidad_porcentaje,
            'precio_unitario_original' => $this->precio_unitario_original,
            'precio_unitario_adjudicado' => $this->precio_unitario_adjudicado,
            'variacion_precio_monto' => $this->variacion_precio_monto,
            'variacion_precio_porcentaje' => $this->variacion_precio_porcentaje,
            'subtotal_adjudicado' => $this->subtotal_adjudicado,
            'ahorro_total' => $this->ahorro_total,
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'caracteristicas' => $this->caracteristicas_tecnicas,
            'garantia' => $this->garantia,
            'tiempo_entrega' => $this->tiempo_entrega
        ];
    }

    /**
     * Obtener el estado del detalle
     */
    public function getEstadoAttribute(): string
    {
        if ($this->cantidad_adjudicada == 0) {
            return 'no_adjudicado';
        }
        
        if ($this->isMejorPrecio()) {
            return 'mejor_precio';
        }
        
        if ($this->precio_unitario_adjudicado > $this->precio_unitario_original) {
            return 'precio_mayor';
        }
        
        return 'precio_igual';
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($detalle) {
            $detalle->calcularSubtotal();
            $detalle->calcularVariacionPrecio();
        });
    }
}