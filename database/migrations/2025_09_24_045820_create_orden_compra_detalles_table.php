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
        Schema::create('orden_compra_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_compra_id')->constrained('orden_compras')->onDelete('cascade');
            $table->foreignId('det_presupuesto_id')->nullable()->constrained('det_presupuesto')->onDelete('set null');
            $table->foreignId('insumo_id')->constrained('insumos')->onDelete('cascade');
            $table->integer('renglon');
            $table->decimal('cantidad', 10, 4);
            $table->decimal('precio_unitario', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->text('descripcion')->nullable();
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->text('garantia')->nullable();
            $table->decimal('cantidad_recibida', 10, 4)->default(0);
            $table->timestamp('fecha_recepcion')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Índices
            $table->index(['orden_compra_id', 'renglon']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_compra_detalles');
    }
};
