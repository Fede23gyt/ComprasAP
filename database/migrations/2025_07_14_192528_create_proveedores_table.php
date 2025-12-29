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
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('cuit', 11)->unique();
            $table->string('razon_social');
            $table->string('nombre_fantasia')->nullable();
            $table->string('direccion');
            $table->string('localidad');
            $table->string('provincia');
            $table->string('codigo_postal', 8);
            $table->string('telefono', 20)->nullable();
            $table->string('email')->unique();
            $table->string('contacto')->nullable();
            $table->string('cargo_contacto')->nullable();
            $table->string('telefono_contacto', 20)->nullable();
            $table->string('email_contacto')->nullable();
            $table->string('condicion_iva');
            $table->string('rubro_principal');
            $table->string('sitio_web')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('estado')->default('activo');
            $table->date('fecha_alta');
            $table->date('fecha_baja')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
