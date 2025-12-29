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
        // Eliminar tabla existente si existe y crear nueva estructura
        Schema::dropIfExists('ofertas');

        Schema::create('ofertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presupuesto_id')->constrained('presupuestos')->onDelete('cascade');
            $table->foreignId('proveedor_id')->constrained('proveedores')->onDelete('cascade');
            $table->string('numero_oferta')->unique();
            $table->date('fecha_oferta');
            $table->decimal('monto_total', 15, 2)->default(0);
            $table->string('estado')->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->integer('plazo_entrega')->nullable(); // días
            $table->string('forma_pago')->nullable();
            $table->integer('validez_oferta')->nullable(); // días
            $table->decimal('descuento_porcentaje', 5, 2)->default(0);
            $table->decimal('descuento_monto', 15, 2)->default(0);
            $table->decimal('impuestos', 15, 2)->default(0);
            $table->decimal('total_final', 15, 2)->default(0);
            $table->boolean('evaluada')->default(false);
            $table->boolean('seleccionada')->default(false);
            $table->text('motivo_seleccion')->nullable();
            $table->text('motivo_rechazo')->nullable();
            $table->timestamp('fecha_evaluacion')->nullable();
            $table->foreignId('evaluado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Índices
            $table->index(['presupuesto_id', 'proveedor_id']);
            $table->index(['estado', 'fecha_oferta']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ofertas');

        // Restaurar estructura anterior si es necesario
        Schema::create('ofertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pre_compra_insumo_id')->constrained('pre_compra_insumos')->onDelete('cascade');
            $table->foreignId('proveedor_id')->constrained('proveedores')->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->string('status')->default('pendiente');
            $table->timestamps();
        });
    }
};
