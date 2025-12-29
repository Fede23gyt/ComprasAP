<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    protected $table = 'proveedores';

    protected $fillable = [
        'cuit',
        'nombre_proveedor',
        'razon_social',
        'tipo',
        'domicilio',
        'telefono_fijo',
        'telefono_celular',
        'email',
        'estado'
    ];

    protected $casts = [
        'estado' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Tipos de IVA
    const TIPO_RESPONSABLE_INSCRIPTO = 'Responsable Inscripto';
    const TIPO_EXENTO = 'Exento';
    const TIPO_MONOTRIBUTISTA = 'Monotributista';

    public static function getTipos(): array
    {
        return [
            self::TIPO_RESPONSABLE_INSCRIPTO,
            self::TIPO_EXENTO,
            self::TIPO_MONOTRIBUTISTA,
        ];
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', true);
    }

    public function scopeInactivos($query)
    {
        return $query->where('estado', false);
    }

    public function getEstadoTextoAttribute(): string
    {
        return $this->estado ? 'Activo' : 'Inactivo';
    }

    public function getEstadoColorAttribute(): string
    {
        return $this->estado ? 'green' : 'red';
    }

    public function isActivo(): bool
    {
        return $this->estado;
    }

    public function getNombreDisplayAttribute(): string
    {
        return $this->nombre_proveedor ?: $this->razon_social;
    }

    public static function validarCuit(string $cuit): bool
    {
        $cuit = preg_replace('/[^0-9]/', '', $cuit);

        if (strlen($cuit) !== 11) {
            return false;
        }

        $prefijo = substr($cuit, 0, 2);
        if (!in_array($prefijo, ['20', '23', '24', '27', '30', '33', '34'])) {
            return false;
        }

        $coeficientes = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        $suma = 0;

        for ($i = 0; $i < 10; $i++) {
            $suma += (int)$cuit[$i] * $coeficientes[$i];
        }

        $resto = $suma % 11;
        $digitoVerificador = 11 - $resto;

        if ($digitoVerificador === 11) {
            $digitoVerificador = 0;
        } elseif ($digitoVerificador === 10) {
            $digitoVerificador = 9;
        }

        return $digitoVerificador === (int)$cuit[10];
    }

    public static function formatearCuit(string $cuit): string
    {
        $cuit = preg_replace('/[^0-9]/', '', $cuit);
        if (strlen($cuit) === 11) {
            return substr($cuit, 0, 2) . '-' . substr($cuit, 2, 8) . '-' . substr($cuit, 10, 1);
        }
        return $cuit;
    }
}