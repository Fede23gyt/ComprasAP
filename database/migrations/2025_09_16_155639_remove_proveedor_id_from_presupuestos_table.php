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
            // Eliminar clave foránea primero
            $table->dropForeign(['proveedor_id']);
            // Eliminar columna
            $table->dropColumn('proveedor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presupuestos', function (Blueprint $table) {
            // Restaurar columna
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->onDelete('cascade');
        });
    }
};
