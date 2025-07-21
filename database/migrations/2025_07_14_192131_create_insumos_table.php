<?php

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
        Schema::create('insumos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('insumos')->onDelete('cascade');
            $table->string('descripcion');
            $table->string('codigo', 20)->unique();
            $table->string('clasificacion')->nullable();
            $table->string('presentacion', 50)->nullable();
            $table->string('unidad_solicitud', 50)->nullable();
            $table->decimal('precio_insumo', 12, 2)->nullable();
            $table->boolean('precio_testigo')->default(false);
            $table->boolean('inventariable')->default(false);
            $table->boolean('registrable')->default(true);
            $table->boolean('rend_tribunal')->default(false);
            $table->decimal('conversion', 12, 4)->default(0.00);
            $table->date('fecha_ultima')->nullable();
            $table->string('tipo')->nullable();
            $table->string('codigo_grupo', 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insumos');
    }
};
