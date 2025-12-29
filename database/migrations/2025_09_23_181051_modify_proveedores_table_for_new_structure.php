<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero agregar columnas como nullable
        Schema::table('proveedores', function (Blueprint $table) {
            if (!Schema::hasColumn('proveedores', 'nombre_proveedor')) {
                $table->string('nombre_proveedor')->nullable()->after('cuit');
            }
            if (!Schema::hasColumn('proveedores', 'tipo')) {
                $table->string('tipo')->nullable()->after('razon_social');
            }
            if (!Schema::hasColumn('proveedores', 'domicilio')) {
                $table->text('domicilio')->nullable()->after('tipo');
            }
            if (!Schema::hasColumn('proveedores', 'telefono_fijo')) {
                $table->string('telefono_fijo')->nullable()->after('domicilio');
            }
            if (!Schema::hasColumn('proveedores', 'telefono_celular')) {
                $table->string('telefono_celular')->nullable()->after('telefono_fijo');
            }
        });

        // Mapear datos existentes
        DB::statement("
            UPDATE proveedores
            SET
                nombre_proveedor = COALESCE(nombre_fantasia, razon_social, 'Sin nombre'),
                tipo = CASE
                    WHEN condicion_iva = 'responsable_inscripto' THEN 'Responsable Inscripto'
                    WHEN condicion_iva = 'monotributo' THEN 'Monotributista'
                    WHEN condicion_iva = 'exento' THEN 'Exento'
                    ELSE 'Responsable Inscripto'
                END,
                domicilio = COALESCE(direccion, 'Sin especificar'),
                telefono_fijo = telefono
        ");

        // Modificar estado
        if (Schema::hasColumn('proveedores', 'estado')) {
            DB::statement("
                UPDATE proveedores
                SET estado = CASE
                    WHEN estado = 'activo' THEN 1
                    ELSE 0
                END
                WHERE estado IN ('activo', 'inactivo', 'suspendido')
            ");
        }

        // Ahora hacer las columnas not null donde corresponde
        Schema::table('proveedores', function (Blueprint $table) {
            $table->string('nombre_proveedor')->nullable(false)->change();
            $table->string('tipo')->nullable(false)->change();
            $table->text('domicilio')->nullable(false)->change();

            // Convertir estado manualmente usando SQL directo
        });

        // Convertir estado a boolean usando SQL directo
        if (Schema::hasColumn('proveedores', 'estado')) {
            DB::statement("ALTER TABLE proveedores ALTER COLUMN estado DROP DEFAULT");
            DB::statement("ALTER TABLE proveedores ALTER COLUMN estado TYPE boolean USING estado::boolean");
            DB::statement("ALTER TABLE proveedores ALTER COLUMN estado SET DEFAULT true");
        } else {
            Schema::table('proveedores', function (Blueprint $table) {
                $table->boolean('estado')->default(true)->after('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            if (Schema::hasColumn('proveedores', 'nombre_proveedor')) {
                $table->dropColumn('nombre_proveedor');
            }
            if (Schema::hasColumn('proveedores', 'tipo')) {
                $table->dropColumn('tipo');
            }
            if (Schema::hasColumn('proveedores', 'domicilio')) {
                $table->dropColumn('domicilio');
            }
            if (Schema::hasColumn('proveedores', 'telefono_fijo')) {
                $table->dropColumn('telefono_fijo');
            }
            if (Schema::hasColumn('proveedores', 'telefono_celular')) {
                $table->dropColumn('telefono_celular');
            }
        });
    }
};
