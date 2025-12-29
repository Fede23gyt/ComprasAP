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
        Schema::table('memos', function (Blueprint $table) {
            $table->string('nombre')->nullable()->after('id');
        });
        
        // Actualizar registros existentes con un valor por defecto
        \DB::table('memos')->whereNull('nombre')->update(['nombre' => 'Memo sin nombre']);
        
        // Hacer el campo requerido después de actualizar
        Schema::table('memos', function (Blueprint $table) {
            $table->string('nombre')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('memos', function (Blueprint $table) {
            $table->dropColumn('nombre');
        });
    }
};
