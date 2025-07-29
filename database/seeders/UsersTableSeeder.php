<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate([
            'email' => 'demo@municipalidad.gob.ar',
        ], [
            'name'  => 'Usuario Demo',
            'password' => bcrypt('12345678'),
        ]);

        $user->assignRole('Usuario');
    }
}
