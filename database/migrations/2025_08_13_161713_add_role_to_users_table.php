<?php

// database/migrations/xxxx_xx_xx_add_role_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::table('users', function (Blueprint $table) {
      $table->unsignedBigInteger('role_id')->nullable()->after('email');
      $table->boolean('is_active')->default(true)->after('role_id');
      $table->string('dni', 20)->nullable()->after('name');
      $table->string('telefono', 20)->nullable()->after('dni');
      $table->text('observaciones')->nullable()->after('telefono');

      // Índices y claves foráneas
      $table->index('role_id');
      $table->index('is_active');
      $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('users', function (Blueprint $table) {
      $table->dropForeign(['role_id']);
      $table->dropIndex(['role_id']);
      $table->dropIndex(['is_active']);
      $table->dropColumn(['role_id', 'is_active', 'dni', 'telefono', 'observaciones']);
    });
  }
};
