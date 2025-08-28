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
        Schema::create('nota_pedido', function (Blueprint $table) {
            $table->id();
            $table->integer('numero_nota');
            $table->integer('ejercicio_nota');
            $table->date('fecha_nota');
            $table->foreignId('oficina_id')->constrained('oficinas')->onDelete('restrict');
            $table->foreignId('tipo_nota_id')->constrained('tipo_nota')->onDelete('restrict');
            $table->text('descripcion');
            $table->string('expediente')->nullable(); // Se agrega después del alta
            $table->integer('estado')->default(0); // 0=Abierta, 1=Confirmada, 2=Rechazada
            $table->decimal('total_nota', 15, 2)->default(0); // Total calculado de todos los renglones
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict'); // Usuario que creó la nota
            $table->timestamp('fecha_confirmacion')->nullable(); // Fecha cuando se confirmó
            $table->foreignId('confirmado_por')->nullable()->constrained('users')->onDelete('set null'); // Usuario que confirmó
            $table->timestamps();
            
            // Índices
            $table->index(['ejercicio_nota', 'numero_nota']);
            $table->index(['oficina_id', 'estado']);
            $table->index(['fecha_nota']);
            $table->index(['estado']);
            
            // Constraint único para número de nota por ejercicio
            $table->unique(['numero_nota', 'ejercicio_nota']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_pedido');
    }
};
