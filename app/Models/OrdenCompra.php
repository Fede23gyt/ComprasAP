<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrdenCompra extends Model
{
    protected $table = 'orden_compras';

    protected $fillable = [
        'adjudicacion_id',
        'presupuesto_id',
        'oferta_id',
        'proveedor_id',
        'numero_orden',
        'fecha_orden',
        'fecha_entrega_estimada',
        'monto_total',
        'estado',
        'observaciones',
        'forma_pago',
        'plazo_entrega',
        'lugar_entrega',
        'condiciones_pago',
        'garantias',
        'usuario_generador_id',
        'fecha_aprobacion',
        'usuario_aprobador_id',
        'fecha_recepcion',
        'numero_remito',
        'numero_factura',
        'fecha_factura',
        'monto_facturado',
        'estado_pago',
        'fecha_pago'
    ];

    protected $casts = [
        'fecha_orden' => 'date',
        'fecha_entrega_estimada' => 'date',
        'monto_total' => 'decimal:2',
        'fecha_aprobacion' => 'date',
        'fecha_recepcion' => 'date',
        'fecha_factura' => 'date',
        'monto_facturado' => 'decimal:2',
        'fecha_pago' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Estados de la orden de compra
    const ESTADO_BORRADOR = 'borrador';
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_APROBADA = 'aprobada';
    const ESTADO_RECHAZADA = 'rechazada';
    const ESTADO_ENVIADA = 'enviada';
    const ESTADO_RECIBIDA = 'recibida';
    const ESTADO_PARCIAL = 'parcial';
    const ESTADO_COMPLETADA = 'completada';
    const ESTADO_ANULADA = 'anulada';

    // Estados de pago
    const PAGO_PENDIENTE = 'pendiente';
    const PAGO_PARCIAL = 'parcial';
    const PAGO_COMPLETADO = 'completado';
    const PAGO_ANULADO = 'anulado';

    /**
     * Relación con la adjudicación
     */
    public function adjudicacion(): BelongsTo
    {
        return $this->belongsTo(Adjudicacion::class);
    }

    /**
     * Relación con el presupuesto
     */
    public function presupuesto(): BelongsTo
    {
        return $this->belongsTo(Presupuesto::class);
    }

    /**
     * Relación con la oferta
     */
    public function oferta(): BelongsTo
    {
        return $this->belongsTo(Oferta::class);
    }

    /**
     * Relación con el proveedor
     */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    /**
     * Relación con el usuario generador
     */
    public function usuarioGenerador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_generador_id');
    }

    /**
     * Relación con el usuario aprobador
     */
    public function usuarioAprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_aprobador_id');
    }

    /**
     * Relación con los detalles de la orden
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(OrdenCompraDetalle::class);
    }

    /**
     * Relación con las recepciones
     */
    public function recepciones(): HasMany
    {
        return $this->hasMany(RecepcionOrden::class);
    }

    /**
     * Scopes
     */
    public function scopeBorradores($query)
    {
        return $query->where('estado', self::ESTADO_BORRADOR);
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    public function scopeAprobadas($query)
    {
        return $query->where('estado', self::ESTADO_APROBADA);
    }

    public function scopeEnviadas($query)
    {
        return $query->where('estado', self::ESTADO_ENVIADA);
    }

    public function scopeRecibidas($query)
    {
        return $query->where('estado', self::ESTADO_RECIBIDA);
    }

    public function scopeCompletadas($query)
    {
        return $query->where('estado', self::ESTADO_COMPLETADA);
    }

    public function scopePorAdjudicacion($query, $adjudicacionId)
    {
        return $query->where('adjudicacion_id', $adjudicacionId);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePorEstadoPago($query, $estadoPago)
    {
        return $query->where('estado_pago', $estadoPago);
    }

    public function scopeConFacturaPendiente($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('numero_factura')
              ->orWhere('estado_pago', '!=', self::PAGO_COMPLETADO);
        });
    }

    /**
     * Métodos de utilidad
     */
    public function getEstadoTextoAttribute(): string
    {
        return match ($this->estado) {
            self::ESTADO_BORRADOR => 'Borrador',
            self::ESTADO_PENDIENTE => 'Pendiente Aprobación',
            self::ESTADO_APROBADA => 'Aprobada',
            self::ESTADO_RECHAZADA => 'Rechazada',
            self::ESTADO_ENVIADA => 'Enviada al Proveedor',
            self::ESTADO_RECIBIDA => 'Recibida Parcialmente',
            self::ESTADO_PARCIAL => 'Parcialmente Recibida',
            self::ESTADO_COMPLETADA => 'Completada',
            self::ESTADO_ANULADA => 'Anulada',
            default => 'Desconocido'
        };
    }

    public function getEstadoColorAttribute(): string
    {
        return match ($this->estado) {
            self::ESTADO_BORRADOR => 'gray',
            self::ESTADO_PENDIENTE => 'yellow',
            self::ESTADO_APROBADA => 'blue',
            self::ESTADO_RECHAZADA => 'red',
            self::ESTADO_ENVIADA => 'purple',
            self::ESTADO_RECIBIDA => 'green',
            self::ESTADO_PARCIAL => 'orange',
            self::ESTADO_COMPLETADA => 'green',
            self::ESTADO_ANULADA => 'red',
            default => 'gray'
        };
    }

    public function getEstadoPagoTextoAttribute(): string
    {
        return match ($this->estado_pago) {
            self::PAGO_PENDIENTE => 'Pendiente',
            self::PAGO_PARCIAL => 'Pago Parcial',
            self::PAGO_COMPLETADO => 'Pagado',
            self::PAGO_ANULADO => 'Anulado',
            default => $this->estado_pago
        };
    }

    public function getEstadoPagoColorAttribute(): string
    {
        return match ($this->estado_pago) {
            self::PAGO_PENDIENTE => 'yellow',
            self::PAGO_PARCIAL => 'orange',
            self::PAGO_COMPLETADO => 'green',
            self::PAGO_ANULADO => 'red',
            default => 'gray'
        };
    }

    public function isBorrador(): bool
    {
        return $this->estado === self::ESTADO_BORRADOR;
    }

    public function isPendiente(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }

    public function isAprobada(): bool
    {
        return $this->estado === self::ESTADO_APROBADA;
    }

    public function isEnviada(): bool
    {
        return $this->estado === self::ESTADO_ENVIADA;
    }

    public function isRecibida(): bool
    {
        return $this->estado === self::ESTADO_RECIBIDA;
    }

    public function isCompletada(): bool
    {
        return $this->estado === self::ESTADO_COMPLETADA;
    }

    public function isAnulada(): bool
    {
        return $this->estado === self::ESTADO_ANULADA;
    }

    /**
     * Calcular el monto pendiente de pago
     */
    public function getMontoPendienteAttribute(): float
    {
        return $this->monto_total - $this->monto_facturado;
    }

    /**
     * Calcular el porcentaje de pago
     */
    public function getPorcentajePagoAttribute(): float
    {
        if ($this->monto_total <= 0) {
            return 0;
        }
        
        return ($this->monto_facturado / $this->monto_total) * 100;
    }

    /**
     * Calcular el porcentaje de recepción
     */
    public function getPorcentajeRecepcionAttribute(): float
    {
        $totalItems = $this->detalles()->sum('cantidad');
        $recibido = $this->detalles()->sum('cantidad_recibida');
        
        if ($totalItems <= 0) {
            return 0;
        }
        
        return ($recibido / $totalItems) * 100;
    }

    /**
     * Generar número de orden automático
     */
    public static function generarNumeroOrden(): string
    {
        $year = date('Y');
        $ultimaOrden = self::where('numero_orden', 'like', "OC-{$year}-%")->orderBy('id', 'desc')->first();
        
        if ($ultimaOrden) {
            $ultimoNumero = (int) substr($ultimaOrden->numero_orden, -4);
            $nuevoNumero = str_pad($ultimoNumero + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nuevoNumero = '0001';
        }
        
        return "OC-{$year}-{$nuevoNumero}";
    }

    /**
     * Validar si la orden puede ser aprobada
     */
    public function puedeAprobar(): bool
    {
        return $this->isPendiente();
    }

    /**
     * Validar si la orden puede ser enviada
     */
    public function puedeEnviar(): bool
    {
        return $this->isAprobada();
    }

    /**
     * Validar si la orden puede recibir items
     */
    public function puedeRecibir(): bool
    {
        return $this->isEnviada() || $this->isRecibida();
    }

    /**
     * Validar si la orden puede ser facturada
     */
    public function puedeFacturar(): bool
    {
        return $this->porcentaje_recepcion > 0;
    }

    /**
     * Obtener array de estados disponibles
     */
    public static function getEstados(): array
    {
        return [
            self::ESTADO_BORRADOR => 'Borrador',
            self::ESTADO_PENDIENTE => 'Pendiente Aprobación',
            self::ESTADO_APROBADA => 'Aprobada',
            self::ESTADO_RECHAZADA => 'Rechazada',
            self::ESTADO_ENVIADA => 'Enviada al Proveedor',
            self::ESTADO_RECIBIDA => 'Recibida Parcialmente',
            self::ESTADO_PARCIAL => 'Parcialmente Recibida',
            self::ESTADO_COMPLETADA => 'Completada',
            self::ESTADO_ANULADA => 'Anulada'
        ];
    }

    /**
     * Cambiar estado de la orden
     */
    public function cambiarEstado(string $nuevoEstado, string $observaciones = null): void
    {
        $this->update([
            'estado' => $nuevoEstado,
            'observaciones' => $observaciones
        ]);

        // Si se aprueba, registrar fecha de aprobación
        if ($nuevoEstado === self::ESTADO_APROBADA) {
            $this->update(['fecha_aprobacion' => now()]);
        }
    }

    /**
     * Registrar recepción de items
     */
    public function registrarRecepcion(array $detalles): void
    {
        foreach ($detalles as $detalleData) {
            $detalle = $this->detalles()->find($detalleData['id']);
            
            if ($detalle) {
                $detalle->update([
                    'cantidad_recibida' => $detalleData['cantidad_recibida'],
                    'fecha_recepcion' => now()
                ]);
            }
        }

        // Actualizar estado según porcentaje de recepción
        $porcentaje = $this->porcentaje_recepcion;
        
        if ($porcentaje >= 100) {
            $this->cambiarEstado(self::ESTADO_COMPLETADA);
        } elseif ($porcentaje > 0) {
            $this->cambiarEstado(self::ESTADO_RECIBIDA);
        }
    }

    /**
     * Registrar factura
     */
    public function registrarFactura(string $numeroFactura, float $monto, \DateTime $fechaFactura): void
    {
        $this->update([
            'numero_factura' => $numeroFactura,
            'monto_facturado' => $monto,
            'fecha_factura' => $fechaFactura,
            'estado_pago' => $monto >= $this->monto_total ? self::PAGO_COMPLETADO : self::PAGO_PARCIAL
        ]);
    }

    /**
     * Registrar pago
     */
    public function registrarPago(float $monto, \DateTime $fechaPago): void
    {
        $nuevoMontoFacturado = $this->monto_facturado + $monto;
        
        $this->update([
            'monto_facturado' => $nuevoMontoFacturado,
            'fecha_pago' => $fechaPago,
            'estado_pago' => $nuevoMontoFacturado >= $this->monto_total ? self::PAGO_COMPLETADO : self::PAGO_PARCIAL
        ]);
    }

    /**
     * Obtener estadísticas de la orden
     */
    public function getEstadisticasAttribute(): array
    {
        return [
            'total_items' => $this->detalles()->count(),
            'items_pendientes' => $this->detalles()->where('cantidad_recibida', '<', 'cantidad')->count(),
            'items_completos' => $this->detalles()->where('cantidad_recibida', '>=', 'cantidad')->count(),
            'porcentaje_recepcion' => $this->porcentaje_recepcion,
            'porcentaje_pago' => $this->porcentaje_pago,
            'monto_pendiente' => $this->monto_pendiente,
            'dias_entrega_restantes' => $this->fecha_entrega_estimada ? 
                now()->diffInDays($this->fecha_entrega_estimada, false) : null
        ];
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($orden) {
            if (!$orden->numero_orden) {
                $orden->numero_orden = self::generarNumeroOrden();
            }
            
            if (!$orden->fecha_orden) {
                $orden->fecha_orden = now();
            }
            
            if (!$orden->usuario_generador_id && auth()->check()) {
                $orden->usuario_generador_id = auth()->id();
            }
        });
    }
}