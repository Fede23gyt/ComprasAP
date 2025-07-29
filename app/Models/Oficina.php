<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Oficina extends Model
{
    use HasRecursiveRelationships;

    protected $fillable = [
        'codigo_oficina',
        'nombre',
        'abreviacion',
        'estado',
        'parent_id',
    ];

    // Relación padre
    public function parent()
    {
        return $this->belongsTo(Oficina::class, 'parent_id');
    }

    // Relación hijos
    public function children()
    {
        return $this->hasMany(Oficina::class, 'parent_id');
    }

    // Scope para mostrar solo habilitadas
    public function scopeHabilitadas($query)
    {
        return $query->where('estado', 'Habilitada');
    }
}
