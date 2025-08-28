<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TipoNotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiposNota = [
            'Construcciones Rentas Generales',
            'Construcciones Resto',
            'Servicio de Impresion',
            'Fotocopias',
            'Servicio de Sonido',
            'Actuacion',
            'Capacitacion-Arbitraje-Honorarios',
            'Seguros',
            'Almuerzo-Cena-Cofee Breaks',
            'Recarga de Cartuchos-Toner',
            'Repuestos',
            'Servicio de Reparacion Vehiculos',
            'Servicio de reparacion PC/Impresoras',
            'Indumentaria',
            'Adquisicion de Cartuchos-Toner',
            'Mobiliario',
            'Equipos Informaticos',
            'Autos/Motos/Maquinarias',
            'Desmalezados',
            'Alquileres Inmuebles',
            'Alquileres Camiones-Gruas',
            'Elementos de Libreria',
            'Articulos de Limpieza-Electricidad',
            'Contratacion de Servicios',
            'DEPORTIVO',
            'Tansporte - Serv.Turisticos',
            'Adquisicion de Bienes de Consumo',
            'Otros Bienes de Capital'
        ];

        // Limpiar la tabla antes de insertar nuevos datos
        DB::table('tipo_nota')->truncate();

        // Insertar los tipos de nota
        foreach ($tiposNota as $index => $descripcion) {
            DB::table('tipo_nota')->insert([
                'id' => $index + 1,
                'descripcion' => $descripcion,
                'estado' => 'Habilitado',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }

        $this->command->info('✓ Se insertaron ' . count($tiposNota) . ' tipos de nota correctamente.');
    }
}