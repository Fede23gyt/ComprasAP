<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoNota extends Model
{
  use HasFactory;

  /**
   * La tabla asociada con el modelo.
   *
   * @var string
   */
  protected $table = 'tipo_nota';

  /**
   * Los atributos que son asignables en masa.
   *
   * @var array
   */
  protected $fillable = [
    'descripcion',
    'estado',
  ];

  /**
   * Los atributos que deben convertirse.
   *
   * @var array
   */
  protected $casts = [
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
  ];

  /**
   * Obtener los tipos de nota habilitados.
   *
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public static function habilitados()
  {
    return self::where('estado', 'Habilitado')
      ->orderBy('descripcion')
      ->get();
  }

  /**
   * Verificar si el tipo de nota está habilitado.
   *
   * @return bool
   */
  public function estaHabilitado()
  {
    return $this->estado === 'Habilitado';
  }

  /**
   * Cambiar el estado del tipo de nota.
   *
   * @return bool
   */
  public function toggleEstado()
  {
    $this->estado = $this->estaHabilitado() ? 'No habilitado' : 'Habilitado';
    return $this->save();
  }
}
