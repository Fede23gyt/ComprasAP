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
        Schema::create('presupuestos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nota_pedido_id')->constrained('nota_pedido')->onDelete('cascade');
            $table->integer('ejercicio');
            $table->integer('numero_presupuesto');
            $table->date('fecha_presupuesto');
            $table->foreignId('proveedor_id')->constrained('proveedores')->onDelete('restrict');
            $table->enum('estado', [
                'borrador',
                'enviado', 
                'aprobado',
                'rechazado',
                'adjudicado'
            ])->default('borrador');
            $table->decimal('total_presupuesto', 15, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->integer('plazo_entrega')->nullable()->comment('Días para entrega');
            $table->string('forma_pago')->nullable();
            $table->integer('validez_oferta')->nullable()->comment('Días de validez');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            
            // Índices
            $table->index(['ejercicio', 'numero_presupuesto']);
            $table->index(['nota_pedido_id', 'estado']);
            $table->index(['proveedor_id', 'estado']);
            $table->index(['fecha_presupuesto']);
            $table->index(['estado']);
            
            // Constraint único para número de presupuesto por ejercicio
            $table->unique(['numero_presupuesto', 'ejercicio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuestos');
    }
};
