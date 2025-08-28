<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotaPedido extends Model
{
    protected $table = 'nota_pedido';

    protected $fillable = [
        'numero_nota',
        'ejercicio_nota',
        'fecha_nota',
        'oficina_id',
        'tipo_nota_id',
        'descripcion',
        'expediente',
        'estado',
        'total_nota',
        'user_id',
        'fecha_confirmacion',
        'confirmado_por'
    ];

    protected $casts = [
        'fecha_nota' => 'date',
        'fecha_confirmacion' => 'datetime',
        'total_nota' => 'decimal:2',
        'estado' => 'integer',
        'numero_nota' => 'integer',
        'ejercicio_nota' => 'integer'
    ];

    // Estados
    const ESTADO_ABIERTA = 0;
    const ESTADO_CONFIRMADA = 1;
    const ESTADO_RECHAZADA = 2;

    /**
     * Relación con la oficina solicitante
     */
    public function oficina(): BelongsTo
    {
        return $this->belongsTo(Oficina::class);
    }

    /**
     * Relación con el tipo de nota
     */
    public function tipoNota(): BelongsTo
    {
        return $this->belongsTo(TipoNota::class, 'tipo_nota_id');
    }

    /**
     * Relación con el usuario que creó la nota
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación con el usuario que confirmó la nota
     */
    public function confirmadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmado_por');
    }

    /**
     * Relación con los detalles de la nota
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetNotaPedido::class)->orderBy('renglon');
    }

    /**
     * Scopes
     */
    public function scopeAbiertas($query)
    {
        return $query->where('estado', self::ESTADO_ABIERTA);
    }

    public function scopeConfirmadas($query)
    {
        return $query->where('estado', self::ESTADO_CONFIRMADA);
    }

    public function scopeRechazadas($query)
    {
        return $query->where('estado', self::ESTADO_RECHAZADA);
    }

    public function scopeDelEjercicio($query, $ejercicio)
    {
        return $query->where('ejercicio_nota', $ejercicio);
    }

    public function scopeDeOficina($query, $oficinaId)
    {
        return $query->where('oficina_id', $oficinaId);
    }

    /**
     * Métodos de utilidad
     */
    public function getEstadoTextoAttribute()
    {
        return match ($this->estado) {
            self::ESTADO_ABIERTA => 'Abierta',
            self::ESTADO_CONFIRMADA => 'Confirmada',
            self::ESTADO_RECHAZADA => 'Rechazada',
            default => 'Desconocido'
        };
    }

    public function getEstadoColorAttribute()
    {
        return match ($this->estado) {
            self::ESTADO_ABIERTA => 'yellow',
            self::ESTADO_CONFIRMADA => 'green',
            self::ESTADO_RECHAZADA => 'red',
            default => 'gray'
        };
    }

    public function isAbierta(): bool
    {
        return $this->estado === self::ESTADO_ABIERTA;
    }

    public function isConfirmada(): bool
    {
        return $this->estado === self::ESTADO_CONFIRMADA;
    }

    public function isRechazada(): bool
    {
        return $this->estado === self::ESTADO_RECHAZADA;
    }

    public function puedeEditar(): bool
    {
        return $this->isAbierta();
    }

    public function puedeConfirmar(): bool
    {
        return $this->isAbierta();
    }

    /**
     * Calcular el total de la nota basado en los detalles
     */
    public function calcularTotal()
    {
        $this->total_nota = $this->detalles()->sum('total_renglon');
        $this->save();
        return $this->total_nota;
    }

    /**
     * Generar el próximo número de nota para el ejercicio actual
     */
    public static function generarProximoNumero($ejercicio = null)
    {
        $ejercicio = $ejercicio ?? date('Y');
        $ultimaNota = static::where('ejercicio_nota', $ejercicio)->max('numero_nota');
        return ($ultimaNota ?? 0) + 1;
    }

    /**
     * Confirmar la nota de pedido
     */
    public function confirmar($usuarioId = null)
    {
        $this->update([
            'estado' => self::ESTADO_CONFIRMADA,
            'fecha_confirmacion' => now(),
            'confirmado_por' => $usuarioId ?? auth()->id()
        ]);
    }

    /**
     * Rechazar la nota de pedido
     */
    public function rechazar()
    {
        $this->update([
            'estado' => self::ESTADO_RECHAZADA
        ]);
    }

    /**
     * Reabrir una nota confirmada (solo para administradores)
     */
    public function reabrir()
    {
        $this->update([
            'estado' => self::ESTADO_ABIERTA,
            'fecha_confirmacion' => null,
            'confirmado_por' => null
        ]);
    }
}
