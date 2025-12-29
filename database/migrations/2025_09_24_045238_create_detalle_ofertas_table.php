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
        Schema::create('detalle_ofertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oferta_id')->constrained('ofertas')->onDelete('cascade');
            $table->foreignId('det_presupuesto_id')->constrained('det_presupuesto')->onDelete('cascade');
            $table->integer('renglon');
            $table->foreignId('insumo_id')->constrained('insumos')->onDelete('cascade');
            $table->decimal('cantidad', 10, 4); // Puede ser menor que la del presupuesto
            $table->decimal('precio_unitario', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->text('caracteristicas_tecnicas')->nullable();
            $table->text('descripcion_ofertada')->nullable(); // Descripción específica del proveedor
            $table->integer('tiempo_entrega')->nullable(); // días específicos para este ítem
            $table->string('garantia')->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('seleccionado')->default(false);
            $table->text('motivo_seleccion')->nullable();
            $table->timestamp('fecha_seleccion')->nullable();
            $table->timestamps();

            // Índices
            $table->index(['oferta_id', 'renglon']);
            $table->index(['insumo_id', 'precio_unitario']);
            $table->unique(['oferta_id', 'det_presupuesto_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_ofertas');
    }
};
