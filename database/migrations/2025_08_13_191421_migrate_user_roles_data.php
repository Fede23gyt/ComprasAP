<?php

// database/migrations/xxxx_xx_xx_migrate_user_roles_data.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\User;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    // Solo ejecutar si existen usuarios y roles
    if (Schema::hasTable('users') && Schema::hasTable('roles')) {

      // Mapeo de roles antiguos a nuevos
      $roleMapping = [
        'Usuario' => 'operador',
        'Administrador' => 'administrador',
        'Admin' => 'administrador',
        'Supervisor' => 'secretario',
        'Secretario' => 'secretario',
        'Director' => 'director',
        'Invitado' => 'invitado',
      ];

      // Migrar cada usuario
      $users = DB::table('users')->get();

      foreach ($users as $user) {
        $oldRole = $user->role ?? 'Usuario';
        $newRoleName = $roleMapping[$oldRole] ?? 'operador';

        // Buscar el nuevo rol
        $newRole = Role::where('name', $newRoleName)->first();

        if ($newRole) {
          DB::table('users')
            ->where('id', $user->id)
            ->update([
              'role_id' => $newRole->id,
              'is_active' => true,
              'updated_at' => now(),
            ]);
        }
      }

      // Log de la migración
      $migratedCount = DB::table('users')->whereNotNull('role_id')->count();
      \Log::info("Migrados {$migratedCount} usuarios al nuevo sistema de roles");
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    // Si necesitamos revertir, podemos mapear de vuelta
    if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {

      $reverseMapping = [
        'administrador' => 'Administrador',
        'secretario' => 'Supervisor',
        'director' => 'Director',
        'operador' => 'Usuario',
        'invitado' => 'Invitado',
      ];

      $users = DB::table('users')
        ->join('roles', 'users.role_id', '=', 'roles.id')
        ->select('users.id', 'roles.name as role_name')
        ->get();

      foreach ($users as $user) {
        $oldRole = $reverseMapping[$user->role_name] ?? 'Usuario';

        DB::table('users')
          ->where('id', $user->id)
          ->update(['role' => $oldRole]);
      }
    }
  }
};
