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
        Schema::table('det_notapedido', function (Blueprint $table) {
            $table->boolean('presupuestado')->default(false)->after('comentario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('det_notapedido', function (Blueprint $table) {
            $table->dropColumn('presupuestado');
        });
    }
};
