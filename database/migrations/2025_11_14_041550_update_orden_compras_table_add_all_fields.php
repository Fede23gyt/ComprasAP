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
        Schema::table('orden_compras', function (Blueprint $table) {
            // Hacer adjudicacion_id nullable ya que ahora usamos ofertas directamente
            if (Schema::hasColumn('orden_compras', 'adjudicacion_id')) {
                $table->foreignId('adjudicacion_id')->nullable()->change();
            }

            // Agregar columnas que faltan
            $table->string('numero_orden')->unique()->after('id');
            $table->date('fecha_orden')->after('numero_orden');
            $table->date('fecha_entrega_estimada')->nullable()->after('fecha_orden');
            $table->decimal('monto_total', 15, 2)->default(0)->after('fecha_entrega_estimada');
            $table->string('estado')->default('aprobada')->after('monto_total');
            $table->text('observaciones')->nullable()->after('estado');
            $table->string('forma_pago')->nullable()->after('observaciones');
            $table->integer('plazo_entrega')->nullable()->after('forma_pago');
            $table->string('lugar_entrega')->nullable()->after('plazo_entrega');
            $table->text('condiciones_pago')->nullable()->after('lugar_entrega');
            $table->text('garantias')->nullable()->after('condiciones_pago');
            $table->foreignId('usuario_generador_id')->nullable()->after('garantias')->constrained('users')->onDelete('set null');
            $table->date('fecha_aprobacion')->nullable()->after('usuario_generador_id');
            $table->foreignId('usuario_aprobador_id')->nullable()->after('fecha_aprobacion')->constrained('users')->onDelete('set null');
            $table->date('fecha_recepcion')->nullable()->after('usuario_aprobador_id');
            $table->string('numero_remito')->nullable()->after('fecha_recepcion');
            $table->string('numero_factura')->nullable()->after('numero_remito');
            $table->date('fecha_factura')->nullable()->after('numero_factura');
            $table->decimal('monto_facturado', 15, 2)->nullable()->after('fecha_factura');
            $table->string('estado_pago')->default('pendiente')->after('monto_facturado');
            $table->date('fecha_pago')->nullable()->after('estado_pago');

            // Eliminar columnas viejas que no se usan
            if (Schema::hasColumn('orden_compras', 'order_date')) {
                $table->dropColumn('order_date');
            }
            if (Schema::hasColumn('orden_compras', 'status')) {
                $table->dropColumn('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orden_compras', function (Blueprint $table) {
            $table->dropForeign(['usuario_generador_id']);
            $table->dropForeign(['usuario_aprobador_id']);

            $table->dropColumn([
                'numero_orden',
                'fecha_orden',
                'fecha_entrega_estimada',
                'monto_total',
                'estado',
                'observaciones',
                'forma_pago',
                'plazo_entrega',
                'lugar_entrega',
                'condiciones_pago',
                'garantias',
                'usuario_generador_id',
                'fecha_aprobacion',
                'usuario_aprobador_id',
                'fecha_recepcion',
                'numero_remito',
                'numero_factura',
                'fecha_factura',
                'monto_facturado',
                'estado_pago',
                'fecha_pago'
            ]);

            $table->date('order_date')->after('adjudicacion_id');
            $table->string('status')->default('pendiente')->after('order_date');
        });
    }
};
