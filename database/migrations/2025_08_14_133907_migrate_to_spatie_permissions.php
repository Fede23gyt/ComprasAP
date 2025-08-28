<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add guard_name column to existing roles table if it doesn't exist
        if (!Schema::hasColumn('roles', 'guard_name')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->string('guard_name')->default('web')->after('name');
            });
            
            // Update all existing roles to have web guard
            DB::table('roles')->update(['guard_name' => 'web']);
        }

        // Create permissions table
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();

                $table->unique(['name', 'guard_name']);
            });
        }

        // Create model_has_permissions table
        if (!Schema::hasTable('model_has_permissions')) {
            Schema::create('model_has_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('permission_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');

                $table->foreign('permission_id')
                    ->references('id')
                    ->on('permissions')
                    ->onDelete('cascade');

                $table->primary(['permission_id', 'model_id', 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            });
        }

        // Create model_has_roles table
        if (!Schema::hasTable('model_has_roles')) {
            Schema::create('model_has_roles', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');

                $table->foreign('role_id')
                    ->references('id')
                    ->on('roles')
                    ->onDelete('cascade');

                $table->primary(['role_id', 'model_id', 'model_type'],
                    'model_has_roles_role_model_type_primary');
            });
        }

        // Create role_has_permissions table
        if (!Schema::hasTable('role_has_permissions')) {
            Schema::create('role_has_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('role_id');

                $table->foreign('permission_id')
                    ->references('id')
                    ->on('permissions')
                    ->onDelete('cascade');

                $table->foreign('role_id')
                    ->references('id')
                    ->on('roles')
                    ->onDelete('cascade');

                $table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
            });
        }

        // Migrate existing user-role relationships
        if (Schema::hasColumn('users', 'role_id') && Schema::hasTable('model_has_roles')) {
            // Get all users with roles
            $users = DB::table('users')->whereNotNull('role_id')->get();
            
            foreach ($users as $user) {
                // Insert into model_has_roles if not exists
                DB::table('model_has_roles')->insertOrIgnore([
                    'role_id' => $user->role_id,
                    'model_type' => 'App\Models\User',
                    'model_id' => $user->id,
                ]);
            }
        }

        // Create basic permissions
        $this->createBasicPermissions();
        $this->assignPermissionsToRoles();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('permissions');
        
        if (Schema::hasColumn('roles', 'guard_name')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn('guard_name');
            });
        }
    }

    private function createBasicPermissions(): void
    {
        $permissions = [
            'manage_users',
            'manage_roles',
            'manage_permissions',
            'manage_offices',
            'view_admin_reports',
            'manage_purchase_types',
            'manage_note_types',
            'create_notes',
            'edit_notes',
            'delete_notes',
            'authorize_notes',
            'view_all_notes',
            'manage_budgets',
            'manage_purchase_orders',
            'view_nomenclators',
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insertOrIgnore([
                'name' => $permission,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function assignPermissionsToRoles(): void
    {
        $rolePermissions = [
            'administrador' => [
                'manage_users', 'manage_roles', 'manage_permissions', 'manage_offices',
                'view_admin_reports', 'manage_purchase_types', 'manage_note_types',
                'create_notes', 'edit_notes', 'delete_notes', 'authorize_notes',
                'view_all_notes', 'manage_budgets', 'manage_purchase_orders',
                'view_nomenclators'
            ],
            'secretario' => [
                'manage_users', 'manage_offices', 'view_admin_reports',
                'manage_purchase_types', 'manage_note_types', 'create_notes',
                'edit_notes', 'authorize_notes', 'view_all_notes',
                'manage_budgets', 'manage_purchase_orders', 'view_nomenclators'
            ],
            'director' => [
                'view_admin_reports', 'create_notes', 'edit_notes', 'authorize_notes',
                'view_all_notes', 'manage_budgets', 'manage_purchase_orders',
                'view_nomenclators'
            ],
            'operador' => [
                'create_notes', 'edit_notes', 'view_nomenclators'
            ],
            'invitado' => [
                'view_nomenclators'
            ]
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = DB::table('roles')->where('name', $roleName)->first();
            if ($role) {
                foreach ($permissions as $permissionName) {
                    $permission = DB::table('permissions')->where('name', $permissionName)->first();
                    if ($permission) {
                        DB::table('role_has_permissions')->insertOrIgnore([
                            'role_id' => $role->id,
                            'permission_id' => $permission->id,
                        ]);
                    }
                }
            }
        }
    }
};
