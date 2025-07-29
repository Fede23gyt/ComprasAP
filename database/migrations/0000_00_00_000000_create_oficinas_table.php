<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oficinas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_oficina')->unique();   // Código único
            $table->string('nombre');
            $table->string('abreviacion', 20);
            $table->enum('estado', ['Habilitada', 'No habilitada'])->default('Habilitada');
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('oficinas')
                ->cascadeOnDelete();   // Jerarquía
            $table->timestamps();
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
