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
        Schema::create('presupuesto_nota_pedido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presupuesto_id')->constrained('presupuestos')->onDelete('cascade');
            $table->foreignId('nota_pedido_id')->constrained('nota_pedido')->onDelete('cascade');
            $table->integer('orden')->default(1); // Para ordenar las notas de pedido en el presupuesto
            $table->timestamps();
            
            // Evitar duplicados
            $table->unique(['presupuesto_id', 'nota_pedido_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuesto_nota_pedido');
    }
};
