<?php
// database/seeders/OficinaSeeder.php
namespace Database\Seeders;

use App\Models\Oficina;
use Illuminate\Database\Seeder;

class OficinaSeeder extends Seeder
{
    public function run(): void
    {
        $municipio = Oficina::create([
            'codigo_oficina' => 'MUN001',
            'nombre' => 'Municipalidad de Salta',
            'abreviacion' => 'MUN',
            'estado' => 'Habilitada',
            'parent_id' => null,
        ]);

        $compras = Oficina::create([
            'codigo_oficina' => 'COM001',
            'nombre' => 'Área de Compras',
            'abreviacion' => 'COM',
            'estado' => 'Habilitada',
            'parent_id' => $municipio->id,
        ]);

        $tesoreria = Oficina::create([
            'codigo_oficina' => 'TES001',
            'nombre' => 'Tesorería',
            'abreviacion' => 'TES',
            'estado' => 'Habilitada',
            'parent_id' => $municipio->id,
        ]);
        $modernizacion = Oficina::create([
            'codigo_oficina' => 'MOD001',
            'nombre' => 'SubSecreatria Modernizacion',
            'abreviacion' => 'MOD',
            'estado' => 'Habilitada',
            'parent_id' => $municipio->id,
        ]);


        // Ejemplo de sub-oficina dentro de compras
        Oficina::create([
            'codigo_oficina' => 'COM002',
            'nombre' => 'Subárea Adquisiciones',
            'abreviacion' => 'SA',
            'estado' => 'Habilitada',
            'parent_id' => $compras->id,
        ]);
    }
}
