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
        Schema::create('det_notapedido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nota_pedido_id')->constrained('nota_pedido')->onDelete('cascade');
            $table->integer('renglon'); // Número de renglón en la nota
            $table->foreignId('insumo_id')->constrained('insumos')->onDelete('restrict');
            $table->decimal('cantidad', 10, 4); // Cantidad solicitada
            $table->decimal('precio', 15, 2); // Precio unitario propuesto
            $table->decimal('total_renglon', 15, 2); // Cantidad * Precio
            $table->text('comentario')->nullable(); // Descripción ampliada y detallada del insumo
            $table->timestamps();
            
            // Índices
            $table->index(['nota_pedido_id', 'renglon']);
            $table->index(['insumo_id']);
            
            // Constraint único para renglón por nota
            $table->unique(['nota_pedido_id', 'renglon']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('det_notapedido');
    }
};
