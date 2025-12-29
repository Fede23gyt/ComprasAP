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
        Schema::create('det_presupuesto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presupuesto_id')->constrained('presupuestos')->onDelete('cascade');
            $table->foreignId('insumo_id')->constrained('insumos')->onDelete('restrict');
            $table->integer('renglon')->comment('Número de renglón en el presupuesto');
            $table->decimal('cantidad', 15, 4)->default(0);
            $table->decimal('precio_unitario', 15, 2)->default(0);
            $table->decimal('total_renglon', 15, 2)->default(0);
            $table->string('unidad_medida', 50)->nullable();
            $table->text('especificaciones_tecnicas')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->timestamps();
            
            // Índices
            $table->index(['presupuesto_id', 'renglon']);
            $table->index(['insumo_id']);
            $table->index(['presupuesto_id', 'insumo_id']);
            
            // Constraint único para renglón por presupuesto
            $table->unique(['presupuesto_id', 'renglon']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('det_presupuesto');
    }
};
