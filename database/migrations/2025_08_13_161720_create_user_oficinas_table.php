<?php

// database/migrations/xxxx_xx_xx_create_user_oficinas_table.php

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
    Schema::create('user_oficinas', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('user_id');
      $table->unsignedBigInteger('oficina_id');
      $table->boolean('es_principal')->default(false); // Oficina principal del usuario
      $table->boolean('puede_autorizar')->default(false); // Si puede autorizar notas de esta oficina
      $table->timestamps();

      // Índices y claves foráneas
      $table->unique(['user_id', 'oficina_id']);
      $table->index('user_id');
      $table->index('oficina_id');
      $table->index('es_principal');
      $table->index('puede_autorizar');

      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
      $table->foreign('oficina_id')->references('id')->on('oficinas')->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('user_oficinas');
  }
};
