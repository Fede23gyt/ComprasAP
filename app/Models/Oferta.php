<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Oferta extends Model
{
    protected $table = 'ofertas';

    protected $fillable = [
        'presupuesto_id',
        'proveedor_id',
        'numero_oferta',
        'fecha_oferta',
        'monto_total',
        'estado',
        'observaciones',
        'plazo_entrega',
        'forma_pago',
        'validez_oferta',
        'descuento_porcentaje',
        'descuento_monto',
        'impuestos',
        'total_final',
        'evaluada',
        'seleccionada',
        'motivo_seleccion',
        'motivo_rechazo',
        'fecha_evaluacion',
        'evaluado_por'
    ];

    protected $casts = [
        'fecha_oferta' => 'date',
        'fecha_evaluacion' => 'datetime',
        'monto_total' => 'decimal:2',
        'descuento_porcentaje' => 'decimal:2',
        'descuento_monto' => 'decimal:2',
        'impuestos' => 'decimal:2',
        'total_final' => 'decimal:2',
        'evaluada' => 'boolean',
        'seleccionada' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Estados de la oferta
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_EVALUANDO = 'evaluando';
    const ESTADO_APROBADA = 'aprobada';
    const ESTADO_RECHAZADA = 'rechazada';
    const ESTADO_ADJUDICADA = 'adjudicada';
    const ESTADO_NO_ADJUDICADA = 'no_adjudicada';

    /**
     * Relación con el presupuesto
     */
    public function presupuesto(): BelongsTo
    {
        return $this->belongsTo(Presupuesto::class);
    }

    /**
     * Relación con el proveedor
     */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    /**
     * Relación con el usuario evaluador
     */
    public function evaluador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluado_por');
    }

    /**
     * Relación con los detalles de la oferta
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleOferta::class);
    }

    /**
     * Scopes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    public function scopeEnEvaluacion($query)
    {
        return $query->where('estado', self::ESTADO_EVALUANDO);
    }

    public function scopeAprobadas($query)
    {
        return $query->where('estado', self::ESTADO_APROBADA);
    }

    public function scopeRechazadas($query)
    {
        return $query->where('estado', self::ESTADO_RECHAZADA);
    }

    public function scopeAdjudicadas($query)
    {
        return $query->where('estado', self::ESTADO_ADJUDICADA);
    }

    public function scopeNoAdjudicadas($query)
    {
        return $query->where('estado', self::ESTADO_NO_ADJUDICADA);
    }

    public function scopePorPresupuesto($query, $presupuestoId)
    {
        return $query->where('presupuesto_id', $presupuestoId);
    }

    public function scopePorProveedor($query, $proveedorId)
    {
        return $query->where('proveedor_id', $proveedorId);
    }

    public function scopeConProveedorActivo($query)
    {
        return $query->whereHas('proveedor', function ($q) {
            $q->where('estado', Proveedor::ESTADO_ACTIVO);
        });
    }

    /**
     * Métodos de utilidad
     */
    public function getEstadoTextoAttribute(): string
    {
        return match ($this->estado) {
            self::ESTADO_PENDIENTE => 'Pendiente',
            self::ESTADO_EVALUANDO => 'En Evaluación',
            self::ESTADO_APROBADA => 'Aprobada',
            self::ESTADO_RECHAZADA => 'Rechazada',
            self::ESTADO_ADJUDICADA => 'Adjudicada',
            self::ESTADO_NO_ADJUDICADA => 'No Adjudicada',
            default => 'Desconocido'
        };
    }

    public function getEstadoColorAttribute(): string
    {
        return match ($this->estado) {
            self::ESTADO_PENDIENTE => 'yellow',
            self::ESTADO_EVALUANDO => 'blue',
            self::ESTADO_APROBADA => 'green',
            self::ESTADO_RECHAZADA => 'red',
            self::ESTADO_ADJUDICADA => 'purple',
            self::ESTADO_NO_ADJUDICADA => 'orange',
            default => 'gray'
        };
    }

    public function isPendiente(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }

    public function isEnEvaluacion(): bool
    {
        return $this->estado === self::ESTADO_EVALUANDO;
    }

    public function isAprobada(): bool
    {
        return $this->estado === self::ESTADO_APROBADA;
    }

    public function isRechazada(): bool
    {
        return $this->estado === self::ESTADO_RECHAZADA;
    }

    public function isAdjudicada(): bool
    {
        return $this->estado === self::ESTADO_ADJUDICADA;
    }

    public function isNoAdjudicada(): bool
    {
        return $this->estado === self::ESTADO_NO_ADJUDICADA;
    }

    /**
     * Verificar si la oferta puede ser modificada
     * No puede modificarse si el presupuesto ya fue adjudicado o cerrado
     */
    public function puedeModificar(): bool
    {
        // Si la oferta ya tiene un estado terminal, no puede modificarse
        if (!$this->isPendiente() && !$this->isEnEvaluacion()) {
            return false;
        }

        // Verificar también el estado del presupuesto
        if ($this->presupuesto) {
            $estadosPresupuestoCerrado = [
                Presupuesto::ESTADO_ADJUDICADO,
                Presupuesto::ESTADO_ADJUDICADO_PARCIAL,
                Presupuesto::ESTADO_DESIERTO,
                Presupuesto::ESTADO_CERRADO,
                Presupuesto::ESTADO_ANULADO
            ];
            
            if (in_array($this->presupuesto->estado, $estadosPresupuestoCerrado)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtener el estado efectivo de la oferta
     * Considera el estado del presupuesto para determinar si la oferta
     * realmente sigue "pendiente" o debería mostrarse como desestimada
     */
    public function getEstadoEfectivoAttribute(): string
    {
        // Si el presupuesto está adjudicado y la oferta no fue adjudicada, está desestimada
        if ($this->presupuesto && $this->presupuesto->isAdjudicado()) {
            if (!$this->isAdjudicada()) {
                return self::ESTADO_NO_ADJUDICADA;
            }
        }
        
        return $this->estado;
    }

    /**
     * Obtener texto del estado efectivo
     */
    public function getEstadoEfectivoTextoAttribute(): string
    {
        $estado = $this->estado_efectivo;
        
        return match ($estado) {
            self::ESTADO_PENDIENTE => 'Pendiente',
            self::ESTADO_EVALUANDO => 'En Evaluación',
            self::ESTADO_APROBADA => 'Aprobada',
            self::ESTADO_RECHAZADA => 'Rechazada',
            self::ESTADO_ADJUDICADA => 'Adjudicada',
            self::ESTADO_NO_ADJUDICADA => 'No Adjudicada',
            default => 'Desconocido'
        };
    }

    /**
     * Calcular el total final
     */
    public function calcularTotalFinal(): void
    {
        $subtotal = $this->monto_total;
        
        // Aplicar descuento porcentual
        if ($this->descuento_porcentaje > 0) {
            $descuento = $subtotal * ($this->descuento_porcentaje / 100);
            $subtotal -= $descuento;
        }
        
        // Aplicar descuento monto fijo
        if ($this->descuento_monto > 0) {
            $subtotal -= $this->descuento_monto;
        }
        
        // Sumar impuestos
        $subtotal += $this->impuestos;
        
        $this->total_final = max(0, $subtotal);
    }

    /**
     * Obtener el ahorro respecto al presupuesto de referencia
     */
    public function getAhorroAttribute(): float
    {
        if (!$this->presupuesto || !$this->presupuesto->total_estimado) {
            return 0;
        }
        
        return $this->presupuesto->total_estimado - $this->total_final;
    }

    /**
     * Obtener el porcentaje de ahorro
     */
    public function getPorcentajeAhorroAttribute(): float
    {
        if (!$this->presupuesto || !$this->presupuesto->total_estimado || $this->presupuesto->total_estimado <= 0) {
            return 0;
        }
        
        return ($this->ahorro / $this->presupuesto->total_estimado) * 100;
    }

    /**
     * Generar número de oferta automático
     */
    public static function generarNumeroOferta(): string
    {
        $year = date('Y');
        $ultimaOferta = self::where('numero_oferta', 'like', "OF-{$year}-%")->orderBy('id', 'desc')->first();
        
        if ($ultimaOferta) {
            $ultimoNumero = (int) substr($ultimaOferta->numero_oferta, -3);
            $nuevoNumero = str_pad($ultimoNumero + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nuevoNumero = '001';
        }
        
        return "OF-{$year}-{$nuevoNumero}";
    }

    /**
     * Validar si la oferta puede ser evaluada
     */
    public function puedeEvaluar(): bool
    {
        return $this->isPendiente() || $this->isEnEvaluacion();
    }

    /**
     * Validar si la oferta puede ser adjudicada
     */
    public function puedeAdjudicar(): bool
    {
        return $this->isAprobada();
    }

    /**
     * Cambiar estado de la oferta
     */
    public function cambiarEstado(string $nuevoEstado, string $observaciones = null): void
    {
        $this->update([
            'estado' => $nuevoEstado,
            'observaciones' => $observaciones
        ]);
    }

    /**
     * Obtener estadísticas de la oferta
     */
    public function getEstadisticasAttribute(): array
    {
        return [
            'total_detalles' => $this->detalles()->count(),
            'detalles_con_precio' => $this->detalles()->where('precio_unitario', '>', 0)->count(),
            'mejor_precio' => $this->detalles()->min('precio_unitario'),
            'peor_precio' => $this->detalles()->max('precio_unitario'),
            'precio_promedio' => $this->detalles()->avg('precio_unitario'),
        ];
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($oferta) {
            if (!$oferta->numero_oferta) {
                $oferta->numero_oferta = self::generarNumeroOferta();
            }
            
            if (!$oferta->fecha_oferta) {
                $oferta->fecha_oferta = now();
            }
        });

        static::saving(function ($oferta) {
            $oferta->calcularTotalFinal();
        });
    }
}