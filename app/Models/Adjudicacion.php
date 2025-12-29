<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Adjudicacion extends Model
{
    protected $table = 'adjudicaciones';

    protected $fillable = [
        'presupuesto_id',
        'oferta_id',
        'proveedor_id',
        'numero_adjudicacion',
        'fecha_adjudicacion',
        'monto_adjudicado',
        'estado',
        'motivo_adjudicacion',
        'observaciones',
        'plazo_entrega_definitivo',
        'forma_pago_definitiva',
        'garantia_contrato',
        'fecha_vencimiento_garantia',
        'usuario_adjudicador_id',
        'fecha_aprobacion',
        'numero_expediente',
        'numero_resolucion'
    ];

    protected $casts = [
        'fecha_adjudicacion' => 'date',
        'monto_adjudicado' => 'decimal:2',
        'fecha_vencimiento_garantia' => 'date',
        'fecha_aprobacion' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Estados de la adjudicación
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_APROBADA = 'aprobada';
    const ESTADO_RECHAZADA = 'rechazada';
    const ESTADO_FIRMADA = 'firmada';
    const ESTADO_EJECUTADA = 'ejecutada';
    const ESTADO_FINALIZADA = 'finalizada';
    const ESTADO_ANULADA = 'anulada';

    // Motivos de adjudicación
    const MOTIVO_MEJOR_PRECIO = 'mejor_precio';
    const MOTIVO_MEJOR_TECNICA = 'mejor_tecnica';
    const MOTIVO_UNICO_OFERENTE = 'unico_oferente';
    const MOTIVO_EMERGENCIA = 'emergencia';
    const MOTIVO_CONVENIO = 'convenio';
    const MOTIVO_OTRO = 'otro';

    /**
     * Relación con el presupuesto
     */
    public function presupuesto(): BelongsTo
    {
        return $this->belongsTo(Presupuesto::class);
    }

    /**
     * Relación con la oferta adjudicada
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
     * Relación con el usuario adjudicador
     */
    public function usuarioAdjudicador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_adjudicador_id');
    }

    /**
     * Relación con los detalles de la adjudicación
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(AdjudicacionDetalle::class);
    }

    /**
     * Scopes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    public function scopeAprobadas($query)
    {
        return $query->where('estado', self::ESTADO_APROBADA);
    }

    public function scopeFirmadas($query)
    {
        return $query->where('estado', self::ESTADO_FIRMADA);
    }

    public function scopeEjecutadas($query)
    {
        return $query->where('estado', self::ESTADO_EJECUTADA);
    }

    public function scopeFinalizadas($query)
    {
        return $query->where('estado', self::ESTADO_FINALIZADA);
    }

    public function scopePorPresupuesto($query, $presupuestoId)
    {
        return $query->where('presupuesto_id', $presupuestoId);
    }

    public function scopePorProveedor($query, $proveedorId)
    {
        return $query->where('proveedor_id', $proveedorId);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
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
            self::ESTADO_APROBADA => 'Aprobada',
            self::ESTADO_RECHAZADA => 'Rechazada',
            self::ESTADO_FIRMADA => 'Firmada',
            self::ESTADO_EJECUTADA => 'En Ejecución',
            self::ESTADO_FINALIZADA => 'Finalizada',
            self::ESTADO_ANULADA => 'Anulada',
            default => 'Desconocido'
        };
    }

    public function getEstadoColorAttribute(): string
    {
        return match ($this->estado) {
            self::ESTADO_PENDIENTE => 'yellow',
            self::ESTADO_APROBADA => 'blue',
            self::ESTADO_RECHAZADA => 'red',
            self::ESTADO_FIRMADA => 'green',
            self::ESTADO_EJECUTADA => 'purple',
            self::ESTADO_FINALIZADA => 'gray',
            self::ESTADO_ANULADA => 'red',
            default => 'gray'
        };
    }

    public function getMotivoAdjudicacionTextoAttribute(): string
    {
        return match ($this->motivo_adjudicacion) {
            self::MOTIVO_MEJOR_PRECIO => 'Mejor Precio',
            self::MOTIVO_MEJOR_TECNICA => 'Mejor Propuesta Técnica',
            self::MOTIVO_UNICO_OFERENTE => 'Único Oferente',
            self::MOTIVO_EMERGENCIA => 'Emergencia',
            self::MOTIVO_CONVENIO => 'Convenio Marco',
            self::MOTIVO_OTRO => 'Otro Motivo',
            default => $this->motivo_adjudicacion
        };
    }

    public function isPendiente(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }

    public function isAprobada(): bool
    {
        return $this->estado === self::ESTADO_APROBADA;
    }

    public function isFirmada(): bool
    {
        return $this->estado === self::ESTADO_FIRMADA;
    }

    public function isEjecutada(): bool
    {
        return $this->estado === self::ESTADO_EJECUTADA;
    }

    public function isFinalizada(): bool
    {
        return $this->estado === self::ESTADO_FINALIZADA;
    }

    public function isAnulada(): bool
    {
        return $this->estado === self::ESTADO_ANULADA;
    }

    /**
     * Obtener el ahorro respecto al presupuesto de referencia
     */
    public function getAhorroAttribute(): float
    {
        if (!$this->presupuesto || !$this->presupuesto->total_estimado) {
            return 0;
        }
        
        return $this->presupuesto->total_estimado - $this->monto_adjudicado;
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
     * Generar número de adjudicación automático
     */
    public static function generarNumeroAdjudicacion(): string
    {
        $year = date('Y');
        $ultimaAdjudicacion = self::where('numero_adjudicacion', 'like', "ADJ-{$year}-%")->orderBy('id', 'desc')->first();
        
        if ($ultimaAdjudicacion) {
            $ultimoNumero = (int) substr($ultimaAdjudicacion->numero_adjudicacion, -3);
            $nuevoNumero = str_pad($ultimoNumero + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nuevoNumero = '001';
        }
        
        return "ADJ-{$year}-{$nuevoNumero}";
    }

    /**
     * Validar si la adjudicación puede ser aprobada
     */
    public function puedeAprobar(): bool
    {
        return $this->isPendiente();
    }

    /**
     * Validar si la adjudicación puede ser firmada
     */
    public function puedeFirmar(): bool
    {
        return $this->isAprobada();
    }

    /**
     * Validar si la adjudicación puede ser ejecutada
     */
    public function puedeEjecutar(): bool
    {
        return $this->isFirmada();
    }

    /**
     * Validar si la adjudicación puede ser finalizada
     */
    public function puedeFinalizar(): bool
    {
        return $this->isEjecutada();
    }

    /**
     * Cambiar estado de la adjudicación
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
     * Obtener estadísticas de la adjudicación
     */
    public function getEstadisticasAttribute(): array
    {
        return [
            'total_detalles' => $this->detalles()->count(),
            'total_items' => $this->detalles()->sum('cantidad'),
            'monto_promedio_item' => $this->detalles()->avg('precio_unitario'),
            'items_sin_stock' => $this->detalles()->whereHas('insumo', function ($q) {
                $q->where('stock_actual', '<=', 0);
            })->count(),
        ];
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($adjudicacion) {
            if (!$adjudicacion->numero_adjudicacion) {
                $adjudicacion->numero_adjudicacion = self::generarNumeroAdjudicacion();
            }
            
            if (!$adjudicacion->fecha_adjudicacion) {
                $adjudicacion->fecha_adjudicacion = now();
            }
        });
    }
}