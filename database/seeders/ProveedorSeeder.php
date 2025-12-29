<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProveedorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $proveedores = [];
        
        // Provincias argentinas
        $provincias = [
            'Buenos Aires', 'CABA', 'Catamarca', 'Chaco', 'Chubut',
            'Córdoba', 'Corrientes', 'Entre Ríos', 'Formosa', 'Jujuy',
            'La Pampa', 'La Rioja', 'Mendoza', 'Misiones', 'Neuquén',
            'Río Negro', 'Salta', 'San Juan', 'San Luis', 'Santa Cruz',
            'Santa Fe', 'Santiago del Estero', 'Tierra del Fuego', 'Tucumán'
        ];
        
        // Rubros principales
        $rubros = [
            'Tecnología', 'Construcción', 'Alimentos', 'Limpieza', 'Oficina',
            'Seguridad', 'Transporte', 'Salud', 'Educación', 'Textil',
            'Automotor', 'Metalmecánica', 'Química', 'Agropecuario', 'Servicios'
        ];
        
        for ($i = 1; $i <= 50; $i++) {
            $cuit = $this->generarCuitValido();
            $razonSocial = $this->generarRazonSocial($i);
            
            $proveedores[] = [
                'cuit' => $cuit,
                'razon_social' => $razonSocial,
                'nombre_fantasia' => $this->generarNombreFantasia($razonSocial),
                'direccion' => $this->generarDireccion(),
                'localidad' => $this->generarLocalidad(),
                'provincia' => $provincias[array_rand($provincias)],
                'codigo_postal' => rand(1000, 9999),
                'telefono' => $this->generarTelefono(),
                'email' => $this->generarEmail($razonSocial),
                'contacto' => $this->generarNombreCompleto(),
                'cargo_contacto' => $this->generarCargo(),
                'telefono_contacto' => $this->generarTelefono(),
                'email_contacto' => $this->generarEmailContacto($razonSocial),
                'condicion_iva' => $this->generarCondicionIva(),
                'rubro_principal' => $rubros[array_rand($rubros)],
                'sitio_web' => $this->generarSitioWeb($razonSocial),
                'observaciones' => $this->generarObservaciones(),
                'estado' => $this->generarEstado(),
                'fecha_alta' => $this->generarFechaAlta(),
                'fecha_baja' => $this->generarFechaBaja(),
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        
        // Insertar en lotes para mejor performance
        $chunks = array_chunk($proveedores, 10);
        foreach ($chunks as $chunk) {
            DB::table('proveedores')->insert($chunk);
        }
    }
    
    private function generarCuitValido(): string
    {
        $prefijos = ['20', '23', '24', '27', '30', '33', '34'];
        $prefijo = $prefijos[array_rand($prefijos)];
        $numero = str_pad(rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        
        $cuitSinVerificador = $prefijo . $numero;
        $coeficientes = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        $suma = 0;
        
        for ($i = 0; $i < 10; $i++) {
            $suma += (int)$cuitSinVerificador[$i] * $coeficientes[$i];
        }
        
        $resto = $suma % 11;
        $digitoVerificador = 11 - $resto;
        
        if ($digitoVerificador === 11) $digitoVerificador = 0;
        if ($digitoVerificador === 10) $digitoVerificador = 9;
        
        return $cuitSinVerificador . $digitoVerificador;
    }
    
    private function generarRazonSocial(int $index): string
    {
        $tipos = ['S.A.', 'S.R.L.', 'S.H.', 'S.C.', 'S.A.C.I.F.I.A.', 'Coop.'];
        $nombres = [
            'Distribuidora', 'Comercial', 'Industrial', 'Servicios', 'Importadora',
            'Exportadora', 'Constructora', 'Tecnología', 'Alimentos', 'Logística',
            'Seguridad', 'Transporte', 'Salud', 'Educación', 'Agropecuaria'
        ];
        
        $tipo = $tipos[array_rand($tipos)];
        $nombre = $nombres[array_rand($nombres)];
        
        return "{$nombre} del Sur {$tipo}";
    }
    
    private function generarNombreFantasia(string $razonSocial): string
    {
        $fantasias = [
            'Soluciones Integrales', 'Innovación Total', 'Calidad Premium',
            'Servicio Excelente', 'Futuro Seguro', 'Proyectos Exitosos',
            'Tecnología Avanzada', 'Distribución Eficiente', 'Logística Perfecta'
        ];
        
        return $fantasias[array_rand($fantasias)];
    }
    
    private function generarDireccion(): string
    {
        $calles = ['Av. San Martín', 'Calle Belgrano', 'Av. 9 de Julio', 'Calle Mitre',
                  'Av. Corrientes', 'Calle Sarmiento', 'Av. Rivadavia', 'Calle Alvear'];
        $numeros = rand(100, 5000);
        
        return $calles[array_rand($calles)] . ' ' . $numeros;
    }
    
    private function generarLocalidad(): string
    {
        $localidades = [
            'Capital Federal', 'La Plata', 'Córdoba', 'Rosario', 'Mendoza',
            'San Miguel de Tucumán', 'Mar del Plata', 'Salta', 'Santa Fe',
            'San Juan', 'Resistencia', 'Neuquén', 'Bahía Blanca', 'Posadas',
            'Paraná', 'Formosa', 'San Salvador de Jujuy', 'La Rioja',
            'Santa Rosa', 'Rawson', 'Viedma', 'Ushuaia'
        ];
        
        return $localidades[array_rand($localidades)];
    }
    
    private function generarTelefono(): string
    {
        return '11 ' . rand(3000, 9999) . ' ' . rand(1000, 9999);
    }
    
    private function generarEmail(string $razonSocial): string
    {
        $nombre = strtolower(preg_replace('/[^a-zA-Z]/', '', $razonSocial));
        $dominios = ['gmail.com', 'hotmail.com', 'yahoo.com', 'outlook.com', 'empresa.com.ar'];
        $dominio = $dominios[array_rand($dominios)];
        
        // Add random number to ensure uniqueness
        $random = rand(1000, 9999);
        return $nombre . $random . '@' . $dominio;
    }
    
    private function generarNombreCompleto(): string
    {
        $nombres = ['Juan', 'María', 'Carlos', 'Ana', 'Luis', 'Laura', 'Pedro', 'Sofía'];
        $apellidos = ['González', 'Rodríguez', 'Gómez', 'Fernández', 'López', 'Díaz', 'Martínez', 'Pérez'];
        
        return $nombres[array_rand($nombres)] . ' ' . $apellidos[array_rand($apellidos)];
    }
    
    private function generarCargo(): string
    {
        $cargos = ['Gerente', 'Director', 'Coordinador', 'Supervisor', 'Ejecutivo', 'Representante'];
        $areas = ['Ventas', 'Comercial', 'Administración', 'Operaciones', 'Logística', 'Tecnología'];
        
        return $cargos[array_rand($cargos)] . ' de ' . $areas[array_rand($areas)];
    }
    
    private function generarEmailContacto(string $razonSocial): string
    {
        $nombre = strtolower(preg_replace('/[^a-zA-Z]/', '', $this->generarNombreCompleto()));
        $dominios = ['gmail.com', 'hotmail.com', 'yahoo.com', 'outlook.com', 'empresa.com.ar'];
        $dominio = $dominios[array_rand($dominios)];
        
        // Add random number to ensure uniqueness
        $random = rand(1000, 9999);
        return $nombre . $random . '@' . $dominio;
    }
    
    private function generarCondicionIva(): string
    {
        $condiciones = [
            Proveedor::IVA_RESPONSABLE_INSCRIPTO,
            Proveedor::IVA_MONOTRIBUTO,
            Proveedor::IVA_EXENTO,
            Proveedor::IVA_NO_RESPONSABLE
        ];
        
        return $condiciones[array_rand($condiciones)];
    }
    
    private function generarSitioWeb(string $razonSocial): string
    {
        $nombre = strtolower(preg_replace('/[^a-zA-Z]/', '', $razonSocial));
        return 'https://www.' . $nombre . '.com.ar';
    }
    
    private function generarObservaciones(): ?string
    {
        $observaciones = [
            null,
            'Proveedor confiable con más de 5 años en el mercado',
            'Especializado en productos de alta calidad',
            'Entrega rápida y eficiente',
            'Atención personalizada',
            'Precios competitivos'
        ];
        
        return $observaciones[array_rand($observaciones)];
    }
    
    private function generarEstado(): string
    {
        $estados = [
            Proveedor::ESTADO_ACTIVO,
            Proveedor::ESTADO_ACTIVO,
            Proveedor::ESTADO_ACTIVO,
            Proveedor::ESTADO_SUSPENDIDO,
            Proveedor::ESTADO_INACTIVO
        ];
        
        return $estados[array_rand($estados)];
    }
    
    private function generarFechaAlta(): string
    {
        return now()->subYears(rand(0, 10))->subMonths(rand(0, 11))->format('Y-m-d');
    }
    
    private function generarFechaBaja(): ?string
    {
        // Solo algunos proveedores inactivos tendrán fecha de baja
        if (rand(1, 10) <= 2) {
            return now()->subMonths(rand(1, 6))->format('Y-m-d');
        }
        
        return null;
    }
}
