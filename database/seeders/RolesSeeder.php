<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        Role::create(['name' => 'Usuario']);
        Role::create(['name' => 'Responsable']);
        Role::create(['name' => 'Compras']);
        Role::create(['name' => 'Proveedor']);
    }
}
