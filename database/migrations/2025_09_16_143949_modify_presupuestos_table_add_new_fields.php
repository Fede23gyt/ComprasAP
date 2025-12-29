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
        Schema::table('presupuestos', function (Blueprint $table) {
            // Agregar referencia al tipo de compra
            $table->foreignId('tipo_compra_id')->nullable()->after('proveedor_id')->constrained('tipo_compra');
            
            // Campos adicionales de entrega y datos administrativos
            $table->string('lugar_entrega')->nullable()->after('validez_oferta');
            $table->string('domicilio_entrega')->nullable()->after('lugar_entrega');
            $table->string('dependencia')->nullable()->after('domicilio_entrega');
            $table->integer('nro_llamado')->default(1)->after('dependencia');
            $table->string('plazo_pago')->nullable()->after('nro_llamado');
            $table->text('comentario')->nullable()->after('plazo_pago');
            
            // Campo para indicar el orden de los renglones cuando hay múltiples notas
            $table->enum('orden_renglones', ['codigo_insumo', 'nota_pedido_insumo'])->nullable()->after('comentario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presupuestos', function (Blueprint $table) {
            $table->dropForeign(['tipo_compra_id']);
            $table->dropColumn([
                'tipo_compra_id',
                'lugar_entrega',
                'domicilio_entrega', 
                'dependencia',
                'nro_llamado',
                'plazo_pago',
                'comentario',
                'orden_renglones'
            ]);
        });
    }
};
