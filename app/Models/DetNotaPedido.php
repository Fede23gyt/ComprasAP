<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetNotaPedido extends Model
{
    protected $table = 'det_notapedido';

    protected $fillable = [
        'nota_pedido_id',
        'renglon',
        'insumo_id',
        'cantidad',
        'precio',
        'total_renglon',
        'comentario',
        'presupuestado'
    ];

    protected $casts = [
        'cantidad' => 'decimal:4',
        'precio' => 'decimal:2',
        'total_renglon' => 'decimal:2',
        'renglon' => 'integer',
        'presupuestado' => 'boolean'
    ];

    /**
     * Relación con la nota de pedido
     */
    public function notaPedido(): BelongsTo
    {
        return $this->belongsTo(NotaPedido::class);
    }

    /**
     * Relación con el insumo
     */
    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class);
    }

    /**
     * Relación con los presupuestos que incluyen este insumo
     * A través de la tabla de detalles de presupuesto
     */
    public function presupuestosAsociados()
    {
        return $this->hasManyThrough(
            Presupuesto::class,
            DetPresupuesto::class,
            'insumo_id', // Foreign key en det_presupuesto
            'id', // Foreign key en presupuestos
            'insumo_id', // Local key en det_notapedido
            'presupuesto_id' // Local key en det_presupuesto
        )->where('presupuestos.estado', '!=', Presupuesto::ESTADO_RECHAZADO);
    }

    /**
     * Calcular automáticamente el total del renglón
     */
    public function calcularTotal()
    {
        $this->total_renglon = $this->cantidad * $this->precio;
        return $this->total_renglon;
    }

    /**
     * Boot method para calcular automáticamente el total al guardar
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($detalle) {
            $detalle->calcularTotal();
        });

        // Actualizar el total de la nota padre después de guardar/eliminar
        static::saved(function ($detalle) {
            $detalle->notaPedido->calcularTotal();
        });

        static::deleted(function ($detalle) {
            $detalle->notaPedido->calcularTotal();
        });
    }

    /**
     * Scopes
     */
    public function scopeDeNota($query, $notaPedidoId)
    {
        return $query->where('nota_pedido_id', $notaPedidoId);
    }

    public function scopeOrdenadoPorRenglon($query)
    {
        return $query->orderBy('renglon');
    }

    public function scopeSinPresupuestar($query)
    {
        return $query->where('presupuestado', false);
    }

    public function scopePresupuestados($query)
    {
        return $query->where('presupuestado', true);
    }

    /**
     * Obtener el precio testigo sugerido del insumo
     */
    public function getPrecioSugeridoAttribute()
    {
        return $this->insumo?->precio_insumo ?? 0;
    }

    /**
     * Verificar si el precio es diferente al precio testigo
     */
    public function isPrecioDiferente(): bool
    {
        $precioTestigo = $this->insumo?->precio_insumo ?? 0;
        return abs($this->precio - $precioTestigo) > 0.01; // Tolerancia de 1 centavo
    }

    /**
     * Obtener el porcentaje de diferencia con el precio testigo
     */
    public function getPorcentajeDiferencia(): float
    {
        $precioTestigo = $this->insumo?->precio_insumo ?? 0;
        
        if ($precioTestigo == 0) {
            return 0;
        }
        
        return (($this->precio - $precioTestigo) / $precioTestigo) * 100;
    }

    /**
     * Formatear la cantidad con la unidad de solicitud
     */
    public function getCantidadFormateadaAttribute(): string
    {
        $unidad = $this->insumo?->unidad_solicitud ?? '';
        return $this->cantidad . ($unidad ? " {$unidad}" : '');
    }

    /**
     * Generar el próximo número de renglón para una nota
     */
    public static function generarProximoRenglon($notaPedidoId): int
    {
        $ultimoRenglon = static::where('nota_pedido_id', $notaPedidoId)->max('renglon');
        return ($ultimoRenglon ?? 0) + 1;
    }
}
