<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Presupuesto extends Model
{
    protected $table = 'presupuestos';

    protected $fillable = [
        'nota_pedido_id',
        'ejercicio',
        'numero_presupuesto',
        'fecha_presupuesto',
        'tipo_compra_id',
        'memo_id',
        'estado',
        'total_presupuesto',
        'observaciones',
        'plazo_entrega',
        'forma_pago',
        'validez_oferta',
        'lugar_entrega',
        'domicilio_entrega',
        'dependencia',
        'nro_llamado',
        'plazo_pago',
        'comentario',
        'orden_renglones',
        'user_id',
        'numero_orden_compra',
        'ejercicio_orden_compra',
        'fecha_adjudicacion',
        'motivo_desierto',
        'motivo_cierre',
        'user_aprobador_id',
        'fecha_aprobacion',
    ];

    protected $casts = [
        'fecha_presupuesto' => 'date',
        'fecha_adjudicacion' => 'date',
        'fecha_aprobacion' => 'datetime',
        'total_presupuesto' => 'decimal:2',
        'plazo_entrega' => 'integer',
        'validez_oferta' => 'integer',
        'ejercicio_orden_compra' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'dias_restantes'
    ];

    // Estados del presupuesto
    const ESTADO_BORRADOR = 'borrador';
    const ESTADO_ENVIADO = 'enviado';
    const ESTADO_APROBADO = 'aprobado';
    const ESTADO_RECHAZADO = 'rechazado';
    const ESTADO_DESIERTO = 'desierto';
    const ESTADO_CERRADO = 'cerrado';
    const ESTADO_ADJUDICADO = 'adjudicado';
    const ESTADO_ADJUDICADO_PARCIAL = 'adjudicado_parcial';
    const ESTADO_ANULADO = 'anulado';

    /**
     * Relación con la nota de pedido
     */
    public function notaPedido(): BelongsTo
    {
        return $this->belongsTo(NotaPedido::class);
    }


    /**
     * Relación con el usuario que creó el presupuesto
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación con el usuario que aprobó el presupuesto
     */
    public function usuarioAprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_aprobador_id');
    }

    /**
     * Relación con los detalles del presupuesto
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetPresupuesto::class)->orderBy('renglon');
    }

    /**
     * Relación con las adjudicaciones
     */
    public function adjudicaciones(): HasMany
    {
        return $this->hasMany(Adjudicacion::class);
    }

    /**
     * Relación con las ofertas recibidas
     */
    public function ofertas(): HasMany
    {
        return $this->hasMany(Oferta::class);
    }

    /**
     * Relación many-to-many con notas de pedido
     */
    public function notasPedido()
    {
        return $this->belongsToMany(NotaPedido::class, 'presupuesto_nota_pedido')
            ->withPivot('orden')
            ->orderBy('presupuesto_nota_pedido.orden');
    }

    /**
     * Relación con el tipo de compra
     */
    public function tipoCompra(): BelongsTo
    {
        return $this->belongsTo(TipoCompra::class);
    }

    /**
     * Relación con el memo
     */
    public function memo(): BelongsTo
    {
        return $this->belongsTo(Memo::class);
    }

    /**
     * Scopes
     */
    public function scopeBorrador($query)
    {
        return $query->where('estado', self::ESTADO_BORRADOR);
    }

    public function scopeEnviados($query)
    {
        return $query->where('estado', self::ESTADO_ENVIADO);
    }

    public function scopeAprobados($query)
    {
        return $query->where('estado', self::ESTADO_APROBADO);
    }

    public function scopeRechazados($query)
    {
        return $query->where('estado', self::ESTADO_RECHAZADO);
    }

    public function scopeAdjudicados($query)
    {
        return $query->where('estado', self::ESTADO_ADJUDICADO);
    }

    public function scopeAdjudicadosParciales($query)
    {
        return $query->where('estado', self::ESTADO_ADJUDICADO_PARCIAL);
    }

    public function scopeDesiertos($query)
    {
        return $query->where('estado', self::ESTADO_DESIERTO);
    }

    public function scopeCerrados($query)
    {
        return $query->where('estado', self::ESTADO_CERRADO);
    }

    public function scopeDelEjercicio($query, $ejercicio)
    {
        return $query->where('ejercicio', $ejercicio);
    }

    public function scopeDeNotaPedido($query, $notaPedidoId)
    {
        return $query->where('nota_pedido_id', $notaPedidoId);
    }

    /**
     * Métodos de utilidad
     */
    public function getEstadoTextoAttribute(): string
    {
        return match ($this->estado) {
            self::ESTADO_BORRADOR => 'Borrador',
            self::ESTADO_ENVIADO => 'Enviado',
            self::ESTADO_APROBADO => 'Aprobado',
            self::ESTADO_RECHAZADO => 'Rechazado',
            self::ESTADO_DESIERTO => 'Desierto',
            self::ESTADO_CERRADO => 'Cerrado',
            self::ESTADO_ADJUDICADO => 'Adjudicado',
            self::ESTADO_ADJUDICADO_PARCIAL => 'Adjudicado Parcial',
            self::ESTADO_ANULADO => 'Anulado',
            default => 'Desconocido'
        };
    }

    public function getEstadoColorAttribute(): string
    {
        return match ($this->estado) {
            self::ESTADO_BORRADOR => 'gray',
            self::ESTADO_ENVIADO => 'blue',
            self::ESTADO_APROBADO => 'green',
            self::ESTADO_RECHAZADO => 'red',
            self::ESTADO_DESIERTO => 'orange',
            self::ESTADO_CERRADO => 'red',
            self::ESTADO_ADJUDICADO => 'purple',
            self::ESTADO_ADJUDICADO_PARCIAL => 'blue',
            self::ESTADO_ANULADO => 'red',
            default => 'gray'
        };
    }

    public function isBorrador(): bool
    {
        return $this->estado === self::ESTADO_BORRADOR;
    }

    public function isEnviado(): bool
    {
        return $this->estado === self::ESTADO_ENVIADO;
    }

    public function isAprobado(): bool
    {
        return $this->estado === self::ESTADO_APROBADO;
    }

    public function isRechazado(): bool
    {
        return $this->estado === self::ESTADO_RECHAZADO;
    }

    public function isDesierto(): bool
    {
        return $this->estado === self::ESTADO_DESIERTO;
    }

    public function isCerrado(): bool
    {
        return $this->estado === self::ESTADO_CERRADO;
    }

    public function isAdjudicado(): bool
    {
        return $this->estado === self::ESTADO_ADJUDICADO;
    }

    public function isAdjudicadoParcial(): bool
    {
        return $this->estado === self::ESTADO_ADJUDICADO_PARCIAL;
    }

    public function puedeEditar(): bool
    {
        return $this->isBorrador();
    }

    public function puedeEnviar(): bool
    {
        return $this->isBorrador() && $this->detalles()->count() > 0;
    }

    public function puedeAprobar(User $user = null): bool
    {
        if (!$user) {
            $user = auth()->user();
        }

        return $this->isEnviado()
            && $this->detalles()->count() > 0
            && $user
            && ($user->hasRole('director') || $user->hasRole('administrador'));
    }

    public function puedeMarcarDesierto(): bool
    {
        return $this->isAprobado();
    }

    public function puedeCerrar(): bool
    {
        return $this->isAprobado();
    }

    public function puedeAdjudicar(): bool
    {
        return $this->puedeAdjudicarConOfertas();
    }

    public function puedeAdjudicarParcial(): bool
    {
        return $this->puedeAdjudicarConOfertas();
    }

    public function puedeReabrir(): bool
    {
        return $this->isDesierto() || $this->isAdjudicadoParcial();
    }

    public function tieneOfertas(): bool
    {
        return $this->ofertas()->count() > 0;
    }

    public function puedeRecibirOfertas(): bool
    {
        return $this->isAprobado() && !$this->isVencido();
    }

    public function isVencido(): bool
    {
        if (!$this->validez_oferta || !$this->fecha_aprobacion) {
            return false;
        }

        $fechaVencimiento = $this->fecha_aprobacion->copy()->addDays($this->validez_oferta);
        return now() > $fechaVencimiento;
    }

    public function getDiasRestantesAttribute(): ?int
    {
        if (!$this->validez_oferta || !$this->fecha_aprobacion) {
            return null;
        }

        $fechaVencimiento = $this->fecha_aprobacion->copy()->addDays($this->validez_oferta);
        $diasRestantes = now()->diffInDays($fechaVencimiento, false);

        return (int) ceil($diasRestantes);
    }

    public function puedeAdjudicarConOfertas(): bool
    {
        return $this->isAprobado() && $this->tieneOfertas() && $this->tieneOfertasSeleccionadas();
    }

    public function isAnulado(): bool
    {
        return $this->estado === self::ESTADO_ANULADO;
    }

    public function puedeAnular(): bool
    {
        return ($this->isBorrador() || $this->isEnviado() || $this->isAprobado()) &&
               !$this->isAdjudicado() &&
               !$this->isAdjudicadoParcial();
    }

    public function anular(): bool
    {
        if (!$this->puedeAnular()) {
            return false;
        }

        DB::transaction(function () {
            // Cambiar estado a anulado
            $this->estado = self::ESTADO_ANULADO;
            $this->save();

            // Liberar insumos asociados (marcar como no presupuestados)
            $this->liberarInsumos();
        });

        return true;
    }

    public function tieneOfertasSeleccionadas(): bool
    {
        return $this->ofertas()->whereHas('detalles', function($query) {
            $query->where('seleccionado', true);
        })->exists();
    }

    /**
     * Calcular el total del presupuesto basado en los detalles
     */
    public function calcularTotal(): float
    {
        $this->total_presupuesto = $this->detalles()->sum('total_renglon');
        $this->save();

        return $this->total_presupuesto;
    }

    /**
     * Generar el próximo número de presupuesto para el ejercicio
     */
    public static function generarProximoNumero($ejercicio = null): int
    {
        $ejercicio = $ejercicio ?? date('Y');
        $ultimoPresupuesto = static::where('ejercicio', $ejercicio)->max('numero_presupuesto');

        return ($ultimoPresupuesto ?? 0) + 1;
    }

    /**
     * Obtener el identificador completo del presupuesto
     */
    public function getIdentificadorCompletoAttribute(): string
    {
        return "{$this->numero_presupuesto}/{$this->ejercicio}";
    }

    /**
     * Comparar con otro presupuesto para análisis
     */
    public function compararCon(Presupuesto $otroPresupuesto): array
    {
        $comparacion = [
            'diferencia_total' => $this->total_presupuesto - $otroPresupuesto->total_presupuesto,
            'porcentaje_diferencia' => 0,
            'es_mejor' => $this->total_presupuesto < $otroPresupuesto->total_presupuesto,
            'detalles_comparacion' => [],
        ];

        if ($otroPresupuesto->total_presupuesto > 0) {
            $comparacion['porcentaje_diferencia'] =
                ($comparacion['diferencia_total'] / $otroPresupuesto->total_presupuesto) * 100;
        }

        return $comparacion;
    }

    /**
     * Enviar/Confirmar presupuesto (borrador → enviado)
     */
    public function enviar(): void
    {
        if (!$this->puedeEnviar()) {
            throw new \Exception('El presupuesto no puede ser enviado. Debe estar en borrador y tener al menos un detalle.');
        }

        $this->update([
            'estado' => self::ESTADO_ENVIADO,
        ]);
    }

    /**
     * Aprobar presupuesto (enviado → aprobado, solo directores)
     */
    public function aprobar(int $userAprobadorId, ?string $observaciones = null): void
    {
        if (!$this->puedeAprobar()) {
            throw new \Exception('El presupuesto no puede ser aprobado en su estado actual');
        }

        DB::transaction(function () use ($userAprobadorId, $observaciones) {
            $this->update([
                'estado' => self::ESTADO_APROBADO,
                'user_aprobador_id' => $userAprobadorId,
                'fecha_aprobacion' => now(),
                'observaciones' => $observaciones,
            ]);

            // Asegurar que todos los insumos estén marcados como presupuestados
            $this->marcarInsumosComoPresupuestados();
        });
    }

    /**
     * Rechazar presupuesto (enviado → rechazado)
     */
    public function rechazar(?string $motivo = null): void
    {
        if (!$this->isEnviado()) {
            throw new \Exception('Solo se pueden rechazar presupuestos en estado enviado');
        }

        DB::transaction(function () use ($motivo) {
            $this->update([
                'estado' => self::ESTADO_RECHAZADO,
                'observaciones' => $motivo,
            ]);

            // Liberar los insumos para que puedan ser reutilizados
            $this->liberarInsumos();
        });
    }

    /**
     * Marcar como desierto
     */
    public function marcarComoDesierto(?string $motivo = null): void
    {
        if (!$this->puedeMarcarDesierto()) {
            throw new \Exception('El presupuesto no puede ser marcado como desierto');
        }

        DB::transaction(function () use ($motivo) {
            $this->update([
                'estado' => self::ESTADO_DESIERTO,
                'motivo_desierto' => $motivo,
            ]);

            // Liberar los insumos para que puedan ser reutilizados
            $this->liberarInsumos();
        });
    }

    /**
     * Cerrar presupuesto
     */
    public function cerrar(?string $motivo = null): void
    {
        if (!$this->puedeCerrar()) {
            throw new \Exception('El presupuesto no puede ser cerrado');
        }

        DB::transaction(function () use ($motivo) {
            $this->update([
                'estado' => self::ESTADO_CERRADO,
                'motivo_cierre' => $motivo,
            ]);

            // Liberar los insumos
            $this->liberarInsumos();
        });
    }

    /**
     * Marcar como adjudicado
     */
    public function adjudicar(string $numeroOrdenCompra, int $ejercicioOrdenCompra): void
    {
        if (!$this->puedeAdjudicar()) {
            throw new \Exception('El presupuesto no puede ser adjudicado');
        }

        $this->update([
            'estado' => self::ESTADO_ADJUDICADO,
            'numero_orden_compra' => $numeroOrdenCompra,
            'ejercicio_orden_compra' => $ejercicioOrdenCompra,
            'fecha_adjudicacion' => now(),
        ]);
    }

    /**
     * Marcar como adjudicado parcial
     */
    public function adjudicarParcial(string $numeroOrdenCompra, int $ejercicioOrdenCompra): void
    {
        if (!$this->puedeAdjudicarParcial()) {
            throw new \Exception('El presupuesto no puede ser adjudicado parcialmente');
        }

        $this->update([
            'estado' => self::ESTADO_ADJUDICADO_PARCIAL,
            'numero_orden_compra' => $numeroOrdenCompra,
            'ejercicio_orden_compra' => $ejercicioOrdenCompra,
            'fecha_adjudicacion' => now(),
        ]);
    }


    /**
     * Reabrir presupuesto (desierto o adjudicado parcial)
     */
    public function reabrir(?array $renglonesNoAdjudicados = null): self
    {
        if (!$this->puedeReabrir()) {
            throw new \Exception('Este presupuesto no puede ser reabierto');
        }

        return DB::transaction(function () use ($renglonesNoAdjudicados) {
            $ejercicio = date('Y');
            $numeroPresupuesto = static::generarProximoNumero($ejercicio);

            // Crear nuevo presupuesto con los mismos datos
            $nuevoPresupuesto = static::create([
                'nota_pedido_id' => $this->nota_pedido_id,
                'tipo_compra_id' => $this->tipo_compra_id,
                'memo_id' => $this->memo_id,
                'ejercicio' => $ejercicio,
                'numero_presupuesto' => $numeroPresupuesto,
                'fecha_presupuesto' => now()->toDateString(),
                'lugar_entrega' => $this->lugar_entrega,
                'domicilio_entrega' => $this->domicilio_entrega,
                'dependencia' => $this->dependencia,
                'nro_llamado' => $this->nro_llamado + 1, // Incrementar número de llamado
                'plazo_pago' => $this->plazo_pago,
                'comentario' => $this->comentario,
                'orden_renglones' => $this->orden_renglones,
                'plazo_entrega' => $this->plazo_entrega,
                'forma_pago' => $this->forma_pago,
                'validez_oferta' => $this->validez_oferta,
                'observaciones' => 'Reapertura del presupuesto ' . $this->identificador_completo,
                'user_id' => auth()->id(),
                'estado' => self::ESTADO_BORRADOR,
            ]);

            // Copiar asociaciones con notas de pedido
            foreach ($this->notasPedido as $nota) {
                $nuevoPresupuesto->notasPedido()->attach($nota->id, [
                    'orden' => $nota->pivot->orden
                ]);
            }

            // Si es desierto, copiar todos los detalles
            // Si es adjudicado parcial, solo copiar los renglones no adjudicados
            if ($this->isDesierto()) {
                foreach ($this->detalles as $detalle) {
                    DetPresupuesto::create([
                        'presupuesto_id' => $nuevoPresupuesto->id,
                        'insumo_id' => $detalle->insumo_id,
                        'renglon' => $detalle->renglon,
                        'cantidad' => $detalle->cantidad,
                        'precio_unitario' => $detalle->precio_unitario,
                        'unidad_medida' => $detalle->unidad_medida,
                        'especificaciones_tecnicas' => $detalle->especificaciones_tecnicas,
                        'marca' => $detalle->marca,
                        'modelo' => $detalle->modelo,
                        'observaciones' => $detalle->observaciones,
                    ]);
                }
            } elseif ($this->isAdjudicadoParcial() && $renglonesNoAdjudicados) {
                foreach ($this->detalles as $detalle) {
                    if (in_array($detalle->renglon, $renglonesNoAdjudicados)) {
                        DetPresupuesto::create([
                            'presupuesto_id' => $nuevoPresupuesto->id,
                            'insumo_id' => $detalle->insumo_id,
                            'renglon' => $detalle->renglon,
                            'cantidad' => $detalle->cantidad,
                            'precio_unitario' => $detalle->precio_unitario,
                            'unidad_medida' => $detalle->unidad_medida,
                            'especificaciones_tecnicas' => $detalle->especificaciones_tecnicas,
                            'marca' => $detalle->marca,
                            'modelo' => $detalle->modelo,
                            'observaciones' => $detalle->observaciones,
                        ]);
                    }
                }
            }

            $nuevoPresupuesto->calcularTotal();

            // Marcar insumos como presupuestados en el nuevo presupuesto
            $nuevoPresupuesto->marcarInsumosComoPresupuestados();

            return $nuevoPresupuesto;
        });
    }

    /**
     * Liberar insumos asociados (marcar como no presupuestados)
     */
    public function liberarInsumos(): void
    {
        // Obtener todos los insumos de este presupuesto y liberarlos en las notas de pedido
        $insumosALiberar = $this->detalles()->with('insumo')->get();
        
        foreach ($this->notasPedido as $nota) {
            foreach ($insumosALiberar as $detalle) {
                DetNotaPedido::where('nota_pedido_id', $nota->id)
                    ->where('insumo_id', $detalle->insumo_id)
                    ->where('presupuestado', true)
                    ->update(['presupuestado' => false]);
            }
        }
    }


    /**
     * Marcar todos los insumos del presupuesto como presupuestados en las notas asociadas
     */
    public function marcarInsumosComoPresupuestados(): void
    {
        foreach ($this->detalles as $detalle) {
            foreach ($this->notasPedido as $nota) {
                DetNotaPedido::where('nota_pedido_id', $nota->id)
                    ->where('insumo_id', $detalle->insumo_id)
                    ->where('presupuestado', false)
                    ->update(['presupuestado' => true]);
            }
        }
    }
}
