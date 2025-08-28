<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Los roles ya deben existir desde el RoleSeeder, no los recreamos aquí

        // Crear usuarios de prueba
        $users = [
            [
                'name' => 'Administrador Sistema',
                'email' => 'admin@compras.gov.ar',
                'dni' => '12345678',
                'telefono' => '387-1234567',
                'password' => Hash::make('admin123'),
                'is_active' => true,
                'role' => 'administrador'
            ],
            [
                'name' => 'María Secretaria',
                'email' => 'secretaria@compras.gov.ar',
                'dni' => '23456789',
                'telefono' => '387-2345678',
                'password' => Hash::make('secretaria123'),
                'is_active' => true,
                'role' => 'secretario'
            ],
            [
                'name' => 'Juan Director',
                'email' => 'director@compras.gov.ar',
                'dni' => '34567890',
                'telefono' => '387-3456789',
                'password' => Hash::make('director123'),
                'is_active' => true,
                'role' => 'director'
            ],
            [
                'name' => 'Ana Operadora',
                'email' => 'operador@compras.gov.ar',
                'dni' => '45678901',
                'telefono' => '387-4567890',
                'password' => Hash::make('operador123'),
                'is_active' => true,
                'role' => 'operador'
            ],
            [
                'name' => 'Pedro Invitado',
                'email' => 'invitado@compras.gov.ar',
                'dni' => '56789012',
                'telefono' => '387-5678901',
                'password' => Hash::make('invitado123'),
                'is_active' => true,
                'role' => 'invitado'
            ]
        ];

        foreach ($users as $userData) {
            $role = Role::where('name', $userData['role'])->first();
            
            if ($role) {
                // Crear usuario
                $user = User::firstOrCreate(
                    ['email' => $userData['email']],
                    [
                        'name' => $userData['name'],
                        'dni' => $userData['dni'],
                        'telefono' => $userData['telefono'],
                        'password' => $userData['password'],
                        'is_active' => $userData['is_active'],
                        'role_id' => $role->id, // Para compatibilidad con sistema anterior
                    ]
                );

                // Asignar rol usando Spatie
                if (!$user->hasRole($userData['role'])) {
                    $user->assignRole($userData['role']);
                }

                $this->command->info("Usuario {$userData['email']} creado/actualizado con rol {$userData['role']}");
            }
        }

        $this->command->info("Usuarios de prueba creados exitosamente!");
    }
}
