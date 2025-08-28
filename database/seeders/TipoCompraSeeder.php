<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TipoCompraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiposCompra = [
            [
                'descripcion' => 'Contratación Directa',
                'estado' => 'Habilitado'
            ],
            [
                'descripcion' => 'Licitación Privada',
                'estado' => 'Habilitado'
            ],
            [
                'descripcion' => 'Licitación Pública',
                'estado' => 'Habilitado'
            ],
            [
                'descripcion' => 'Concurso de Precios',
                'estado' => 'Habilitado'
            ],
            [
                'descripcion' => 'Emergencia',
                'estado' => 'Habilitado'
            ]
        ];

        // Limpiar la tabla antes de insertar nuevos datos
        DB::table('tipo_compra')->truncate();

        // Insertar los tipos de compra
        foreach ($tiposCompra as $index => $tipo) {
            DB::table('tipo_compra')->insert([
                'id' => $index + 1,
                'descripcion' => $tipo['descripcion'],
                'estado' => $tipo['estado'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }

        $this->command->info('✓ Se insertaron ' . count($tiposCompra) . ' tipos de compra correctamente.');
    }
}
