<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClasifEconomica extends Model
{
  protected $table = 'clasif_economica';
  protected $fillable = ['codigo','descripcion'];
}
