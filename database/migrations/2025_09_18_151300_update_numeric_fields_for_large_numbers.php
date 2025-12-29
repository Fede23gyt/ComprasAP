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
        // Actualizar tabla det_notapedido para manejar números grandes
        Schema::table('det_notapedido', function (Blueprint $table) {
            $table->decimal('cantidad', 18, 4)->change(); // Permite hasta 14 dígitos enteros
            $table->decimal('precio', 18, 2)->change(); // Permite hasta 16 dígitos enteros  
            $table->decimal('total_renglon', 20, 2)->change(); // Permite hasta 18 dígitos enteros
        });

        // Actualizar tabla nota_pedido para totales grandes
        Schema::table('nota_pedido', function (Blueprint $table) {
            $table->decimal('total_nota', 20, 2)->change(); // Permite hasta 18 dígitos enteros
        });

        // Actualizar tabla det_presupuesto para números grandes
        Schema::table('det_presupuesto', function (Blueprint $table) {
            $table->decimal('cantidad', 18, 4)->change(); 
            $table->decimal('precio_unitario', 18, 2)->change();
            $table->decimal('total_renglon', 20, 2)->change();
        });

        // Actualizar tabla presupuestos para totales grandes
        Schema::table('presupuestos', function (Blueprint $table) {
            $table->decimal('total_presupuesto', 20, 2)->change();
        });

        // Actualizar tabla insumos para precios grandes
        Schema::table('insumos', function (Blueprint $table) {
            $table->decimal('precio_insumo', 18, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir cambios si es necesario
        Schema::table('det_notapedido', function (Blueprint $table) {
            $table->decimal('cantidad', 10, 4)->change();
            $table->decimal('precio', 10, 2)->change();
            $table->decimal('total_renglon', 12, 2)->change();
        });

        Schema::table('nota_pedido', function (Blueprint $table) {
            $table->decimal('total_nota', 12, 2)->change();
        });

        Schema::table('det_presupuesto', function (Blueprint $table) {
            $table->decimal('cantidad', 10, 4)->change();
            $table->decimal('precio_unitario', 10, 2)->change();
            $table->decimal('total_renglon', 12, 2)->change();
        });

        Schema::table('presupuestos', function (Blueprint $table) {
            $table->decimal('total_presupuesto', 12, 2)->change();
        });

        Schema::table('insumos', function (Blueprint $table) {
            $table->decimal('precio_insumo', 10, 2)->change();
        });
    }
};
