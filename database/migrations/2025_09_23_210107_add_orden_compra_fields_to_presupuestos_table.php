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
            // Campos para órdenes de compra
            $table->string('numero_orden_compra')->nullable()->after('nro_llamado');
            $table->integer('ejercicio_orden_compra')->nullable()->after('numero_orden_compra');
            $table->date('fecha_adjudicacion')->nullable()->after('ejercicio_orden_compra');
            $table->text('motivo_desierto')->nullable()->after('fecha_adjudicacion');
            $table->text('motivo_cierre')->nullable()->after('motivo_desierto');
            $table->integer('user_aprobador_id')->nullable()->after('user_id');
            $table->timestamp('fecha_aprobacion')->nullable()->after('user_aprobador_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presupuestos', function (Blueprint $table) {
            $table->dropColumn([
                'numero_orden_compra',
                'ejercicio_orden_compra',
                'fecha_adjudicacion',
                'motivo_desierto',
                'motivo_cierre',
                'user_aprobador_id',
                'fecha_aprobacion'
            ]);
        });
    }
};
