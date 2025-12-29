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
            $table->foreignId('presupuesto_id')->nullable()->after('adjudicacion_id')->constrained('presupuestos')->onDelete('set null');
            $table->foreignId('oferta_id')->nullable()->after('presupuesto_id')->constrained('ofertas')->onDelete('set null');
            $table->foreignId('proveedor_id')->nullable()->after('oferta_id')->constrained('proveedores')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orden_compras', function (Blueprint $table) {
            $table->dropForeign(['presupuesto_id']);
            $table->dropForeign(['oferta_id']);
            $table->dropForeign(['proveedor_id']);
            $table->dropColumn(['presupuesto_id', 'oferta_id', 'proveedor_id']);
        });
    }
};
