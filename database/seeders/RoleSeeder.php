<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $roles = [
      [
        'name' => 'administrador',
        'display_name' => 'Administrador',
        'description' => 'Acceso completo al sistema, gestión de usuarios y configuración general',
        'permissions' => json_encode([
          'manage_users',
          'manage_roles',
          'manage_offices',
          'manage_config',
          'create_any_nota',
          'authorize_notas',
          'view_all_reports',
          'manage_budgets',
          'manage_orders',
        ]),
        'is_active' => true,
      ],
      [
        'name' => 'secretario',
        'display_name' => 'Secretario',
        'description' => 'Gestión de notas de pedido, presupuestos y órdenes de compra para todas las oficinas',
        'permissions' => json_encode([
          'create_any_nota',
          'authorize_notas',
          'view_reports',
          'manage_budgets',
          'manage_orders',
        ]),
        'is_active' => true,
      ],
      [
        'name' => 'director',
        'display_name' => 'Director',
        'description' => 'Autorización de notas de pedido y gestión de presupuestos para todas las oficinas',
        'permissions' => json_encode([
          'create_any_nota',
          'authorize_notas',
          'view_reports',
          'manage_budgets',
        ]),
        'is_active' => true,
      ],
      [
        'name' => 'operador',
        'display_name' => 'Operador',
        'description' => 'Creación de notas de pedido únicamente para las oficinas asignadas',
        'permissions' => json_encode([
          'create_own_notas',
          'view_own_reports',
        ]),
        'is_active' => true,
      ],
      [
        'name' => 'supervisor',
        'display_name' => 'Supervisor',
        'description' => 'Supervisión de notas de pedido y gestión de oficinas específicas',
        'permissions' => json_encode([
          'create_any_nota',
          'authorize_notas',
          'view_reports',
          'reopen_notas',
        ]),
        'is_active' => true,
      ],
      [
        'name' => 'invitado',
        'display_name' => 'Invitado',
        'description' => 'Acceso de solo lectura a nomencladores y consultas básicas',
        'permissions' => json_encode([
          'view_nomenclators',
        ]),
        'is_active' => true,
      ],
    ];

    foreach ($roles as $roleData) {
      Role::updateOrCreate(
        ['name' => $roleData['name']],
        $roleData
      );
    }

    $this->command->info('✅ Roles creados correctamente:');
    $this->command->info('   - Administrador: Acceso total');
    $this->command->info('   - Secretario: Gestión completa de notas y órdenes');
    $this->command->info('   - Director: Autorización y presupuestos');
    $this->command->info('   - Operador: Notas para oficinas asignadas');
    $this->command->info('   - Invitado: Solo consultas');
  }
}
