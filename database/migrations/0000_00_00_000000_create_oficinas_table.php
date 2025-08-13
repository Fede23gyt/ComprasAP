<?php

// database/migrations/xxxx_xx_xx_create_oficinas_table.php

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
    Schema::create('oficinas', function (Blueprint $table) {
      $table->id();

      // Campos principales
      $table->string('codigo_oficina', 20)->unique();
      $table->string('nombre')->unique();
      $table->string('abreviacion', 20)->unique();
      $table->enum('estado', ['Habilitada', 'No habilitada'])->default('Habilitada');

      // Relación jerárquica
      $table->unsignedBigInteger('parent_id')->nullable();

      // Campos adicionales opcionales
      $table->text('descripcion')->nullable();
      $table->string('telefono', 20)->nullable();
      $table->string('email')->nullable();
      $table->text('direccion')->nullable();
      $table->string('responsable')->nullable();

      // Campos para el paquete de adjacency list
      $table->integer('_lft')->nullable();
      $table->integer('_rgt')->nullable();
      $table->integer('depth')->default(0);

      $table->timestamps();

      // Índices
      $table->index('parent_id');
      $table->index('estado');
      $table->index(['_lft', '_rgt']);
      $table->index('depth');
      $table->index('codigo_oficina');
      $table->index('nombre');

      // Clave foránea
      $table->foreign('parent_id')->references('id')->on('oficinas')->onDelete('set null');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('oficinas');
  }
};
