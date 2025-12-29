<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenCompraDetalle extends Model
{
    protected $table = 'orden_compra_detalles';

    protected $fillable = [
        'orden_compra_id',
        'det_presupuesto_id',
        'insumo_id',
        'renglon',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'descripcion',
        'marca',
        'modelo',
        'garantia',
        'cantidad_recibida',
        'fecha_recepcion',
        'observaciones'
    ];

    protected $casts = [
        'cantidad' => 'decimal:4',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'cantidad_recibida' => 'decimal:4',
        'fecha_recepcion' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relación con la orden de compra
     */
    public function ordenCompra(): BelongsTo
    {
        return $this->belongsTo(OrdenCompra::class);
    }

    /**
     * Relación con el detalle del presupuesto
     */
    public function detallePresupuesto(): BelongsTo
    {
        return $this->belongsTo(DetPresupuesto::class, 'det_presupuesto_id');
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

    public function scopePendientesRecepcion($query)
    {
        return $query->whereColumn('cantidad_recibida', '<', 'cantidad');
    }

    public function scopeCompletamentRecibidos($query)
    {
        return $query->whereColumn('cantidad_recibida', '>=', 'cantidad');
    }

    /**
     * Métodos de utilidad
     */
    public function calcularSubtotal(): void
    {
        $this->subtotal = $this->cantidad * $this->precio_unitario;
    }

    public function getCantidadPendienteAttribute(): float
    {
        return max(0, $this->cantidad - $this->cantidad_recibida);
    }

    public function getPorcentajeRecepcionAttribute(): float
    {
        if ($this->cantidad <= 0) {
            return 0;
        }

        return ($this->cantidad_recibida / $this->cantidad) * 100;
    }

    public function isCompletamenteRecibido(): bool
    {
        return $this->cantidad_recibida >= $this->cantidad;
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