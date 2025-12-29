<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetPresupuesto extends Model
{
    protected $table = 'det_presupuesto';

    protected $fillable = [
        'presupuesto_id',
        'insumo_id',
        'renglon',
        'cantidad',
        'precio_unitario',
        'total_renglon',
        'unidad_medida',
        'especificaciones_tecnicas',
        'observaciones',
        'marca',
        'modelo'
    ];

    protected $casts = [
        'cantidad' => 'decimal:4',
        'precio_unitario' => 'decimal:2',
        'total_renglon' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relación con el presupuesto
     */
    public function presupuesto(): BelongsTo
    {
        return $this->belongsTo(Presupuesto::class);
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
    public function scopeDelPresupuesto($query, $presupuestoId)
    {
        return $query->where('presupuesto_id', $presupuestoId);
    }

    public function scopeDelInsumo($query, $insumoId)
    {
        return $query->where('insumo_id', $insumoId);
    }

    public function scopePorRenglon($query, $renglon)
    {
        return $query->where('renglon', $renglon);
    }

    /**
     * Métodos de utilidad
     */
    public function calcularTotal(): float
    {
        $this->total_renglon = $this->cantidad * $this->precio_unitario;
        $this->save();
        return $this->total_renglon;
    }

    /**
     * Comparar con otro detalle para análisis
     */
    public function compararCon(DetPresupuesto $otroDetalle): array
    {
        $comparacion = [
            'diferencia_precio' => $this->precio_unitario - $otroDetalle->precio_unitario,
            'porcentaje_diferencia' => 0,
            'diferencia_total' => $this->total_renglon - $otroDetalle->total_renglon,
            'es_mejor' => $this->precio_unitario < $otroDetalle->precio_unitario,
            'misma_calidad' => $this->marca === $otroDetalle->marca && $this->modelo === $otroDetalle->modelo
        ];

        if ($otroDetalle->precio_unitario > 0) {
            $comparacion['porcentaje_diferencia'] = 
                ($comparacion['diferencia_precio'] / $otroDetalle->precio_unitario) * 100;
        }

        return $comparacion;
    }

    /**
     * Obtener información completa del item
     */
    public function getInformacionCompletaAttribute(): array
    {
        return [
            'insumo' => $this->insumo->descripcion,
            'codigo_insumo' => $this->insumo->codigo,
            'cantidad' => $this->cantidad,
            'unidad_medida' => $this->unidad_medida ?: $this->insumo->unidad_medida,
            'precio_unitario' => $this->precio_unitario,
            'total_renglon' => $this->total_renglon,
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'especificaciones' => $this->especificaciones_tecnicas,
            'observaciones' => $this->observaciones
        ];
    }

    /**
     * Verificar si es equivalente a otro detalle
     */
    public function esEquivalente(DetPresupuesto $otroDetalle): bool
    {
        return $this->insumo_id === $otroDetalle->insumo_id &&
               $this->marca === $otroDetalle->marca &&
               $this->modelo === $otroDetalle->modelo;
    }

    /**
     * Obtener el precio por unidad de medida estándar
     */
    public function getPrecioPorUnidadEstandar(): float
    {
        // Aquí se podría implementar conversión de unidades si es necesario
        return $this->precio_unitario;
    }

    /**
     * Validar que los datos sean consistentes
     */
    public function validarConsistencia(): bool
    {
        return $this->total_renglon === ($this->cantidad * $this->precio_unitario) &&
               $this->cantidad > 0 &&
               $this->precio_unitario >= 0;
    }

    /**
     * Actualizar el total automáticamente antes de guardar
     */
    protected static function booted()
    {
        static::saving(function ($detalle) {
            if ($detalle->isDirty(['cantidad', 'precio_unitario'])) {
                $detalle->total_renglon = $detalle->cantidad * $detalle->precio_unitario;
            }
        });
    }
}