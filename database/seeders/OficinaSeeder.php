<?php

namespace Database\Seeders;

use App\Models\Oficina;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OficinaSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Limpiar tabla de forma segura para PostgreSQL
    $this->command->info('🧹 Limpiando datos existentes...');

    // Deshabilitar triggers temporalmente en PostgreSQL
    if (config('database.default') === 'pgsql') {
      DB::statement('TRUNCATE TABLE oficinas RESTART IDENTITY CASCADE;');
    } else {
      // Para MySQL
      DB::statement('SET FOREIGN_KEY_CHECKS=0;');
      Oficina::truncate();
      DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    $this->command->info('🏛️  Creando estructura de la Municipalidad de Salta...');

    // 🏛️ NIVEL 0 - MUNICIPALIDAD (RAÍZ)
    $municipalidad = Oficina::create([
      'codigo_oficina' => 'MUN-001',
      'nombre' => 'Municipalidad de Salta',
      'abreviacion' => 'MUN',
      'estado' => 'Habilitada',
      'parent_id' => null,
      'descripcion' => 'Municipalidad de Salta - Gobierno Local',
      'telefono' => '387-4372800',
      'email' => 'contacto@municipalidaddesalta.gov.ar',
      'direccion' => 'España 366, Salta Capital',
      'responsable' => 'Intendente Municipal',
    ]);

    // 📋 NIVEL 1 - SECRETARÍAS
    $secretarias = [
      [
        'codigo' => 'SG-001',
        'nombre' => 'Secretaría General',
        'abrev' => 'SG',
        'desc' => 'Coordinación general y administrativa del municipio',
        'tel' => '387-4372801',
        'email' => 'secretariageneral@municipalidaddesalta.gov.ar'
      ],
      [
        'codigo' => 'SH-001',
        'nombre' => 'Secretaría de Hacienda',
        'abrev' => 'SH',
        'desc' => 'Gestión financiera y tributaria municipal',
        'tel' => '387-4372802',
        'email' => 'hacienda@municipalidaddesalta.gov.ar'
      ],
      [
        'codigo' => 'SO-001',
        'nombre' => 'Secretaría de Obras Públicas',
        'abrev' => 'SO',
        'desc' => 'Planificación y ejecución de obras de infraestructura',
        'tel' => '387-4372803',
        'email' => 'obraspublicas@municipalidaddesalta.gov.ar'
      ],
      [
        'codigo' => 'SA-001',
        'nombre' => 'Secretaría de Ambiente',
        'abrev' => 'SA',
        'desc' => 'Protección ambiental y desarrollo sustentable',
        'tel' => '387-4372804',
        'email' => 'ambiente@municipalidaddesalta.gov.ar'
      ],
      [
        'codigo' => 'SS-001',
        'nombre' => 'Secretaría de Servicios Públicos',
        'abrev' => 'SS',
        'desc' => 'Gestión de servicios públicos municipales',
        'tel' => '387-4372805',
        'email' => 'servicios@municipalidaddesalta.gov.ar'
      ],
      [
        'codigo' => 'SC-001',
        'nombre' => 'Secretaría de Cultura',
        'abrev' => 'SC',
        'desc' => 'Promoción cultural y patrimonio histórico',
        'tel' => '387-4372806',
        'email' => 'cultura@municipalidaddesalta.gov.ar'
      ]
    ];

    $secretariasCreadas = [];
    foreach ($secretarias as $sec) {
      $secretariasCreadas[$sec['abrev']] = Oficina::create([
        'codigo_oficina' => $sec['codigo'],
        'nombre' => $sec['nombre'],
        'abreviacion' => $sec['abrev'],
        'estado' => 'Habilitada',
        'parent_id' => $municipalidad->id,
        'descripcion' => $sec['desc'],
        'telefono' => $sec['tel'],
        'email' => $sec['email'],
      ]);
    }

    $this->command->info('✅ Secretarías creadas: ' . count($secretariasCreadas));

    // 🏢 NIVEL 2 - DIRECCIONES DE SECRETARÍA GENERAL
    $direccionesSG = [
      [
        'codigo' => 'DRRHH-001',
        'nombre' => 'Dirección de Recursos Humanos',
        'abrev' => 'DRRHH',
        'desc' => 'Gestión del personal municipal'
      ],
      [
        'codigo' => 'DIT-001',
        'nombre' => 'Dirección de Sistemas',
        'abrev' => 'DIT',
        'desc' => 'Tecnologías de la información y comunicación'
      ],
      [
        'codigo' => 'DLEG-001',
        'nombre' => 'Dirección Legal',
        'abrev' => 'DLEG',
        'desc' => 'Asesoramiento jurídico y legal'
      ],
      [
        'codigo' => 'DCOM-001',
        'nombre' => 'Dirección de Comunicación',
        'abrev' => 'DCOM',
        'desc' => 'Comunicación institucional y prensa'
      ]
    ];

    $direccionesSGCreadas = [];
    foreach ($direccionesSG as $dir) {
      $direccionesSGCreadas[$dir['abrev']] = Oficina::create([
        'codigo_oficina' => $dir['codigo'],
        'nombre' => $dir['nombre'],
        'abreviacion' => $dir['abrev'],
        'estado' => 'Habilitada',
        'parent_id' => $secretariasCreadas['SG']->id,
        'descripcion' => $dir['desc'],
      ]);
    }

    // 💰 NIVEL 2 - DIRECCIONES DE SECRETARÍA DE HACIENDA
    $direccionesSH = [
      [
        'codigo' => 'DCOMP-001',
        'nombre' => 'Dirección de Compras',
        'abrev' => 'DCOMP',
        'desc' => 'Compras y contrataciones públicas'
      ],
      [
        'codigo' => 'DCONT-001',
        'nombre' => 'Dirección Contable',
        'abrev' => 'DCONT',
        'desc' => 'Contabilidad y registración patrimonial'
      ],
      [
        'codigo' => 'DTES-001',
        'nombre' => 'Dirección de Tesorería',
        'abrev' => 'DTES',
        'desc' => 'Gestión de fondos y pagos'
      ],
      [
        'codigo' => 'DTRIB-001',
        'nombre' => 'Dirección Tributaria',
        'abrev' => 'DTRIB',
        'desc' => 'Recaudación y fiscalización tributaria'
      ],
      [
        'codigo' => 'DPRES-001',
        'nombre' => 'Dirección de Presupuesto',
        'abrev' => 'DPRES',
        'desc' => 'Planificación y control presupuestario'
      ]
    ];

    $direccionesSHCreadas = [];
    foreach ($direccionesSH as $dir) {
      $direccionesSHCreadas[$dir['abrev']] = Oficina::create([
        'codigo_oficina' => $dir['codigo'],
        'nombre' => $dir['nombre'],
        'abreviacion' => $dir['abrev'],
        'estado' => 'Habilitada',
        'parent_id' => $secretariasCreadas['SH']->id,
        'descripcion' => $dir['desc'],
      ]);
    }

    // 🚧 NIVEL 2 - DIRECCIONES DE OBRAS PÚBLICAS
    $direccionesSO = [
      [
        'codigo' => 'DPROJ-001',
        'nombre' => 'Dirección de Proyectos',
        'abrev' => 'DPROJ',
        'desc' => 'Diseño y planificación de proyectos'
      ],
      [
        'codigo' => 'DEJEC-001',
        'nombre' => 'Dirección de Ejecución',
        'abrev' => 'DEJEC',
        'desc' => 'Ejecución y supervisión de obras'
      ],
      [
        'codigo' => 'DMANT-001',
        'nombre' => 'Dirección de Mantenimiento',
        'abrev' => 'DMANT',
        'desc' => 'Mantenimiento de infraestructura'
      ]
    ];

    $direccionesSOCreadas = [];
    foreach ($direccionesSO as $dir) {
      $direccionesSOCreadas[$dir['abrev']] = Oficina::create([
        'codigo_oficina' => $dir['codigo'],
        'nombre' => $dir['nombre'],
        'abreviacion' => $dir['abrev'],
        'estado' => 'Habilitada',
        'parent_id' => $secretariasCreadas['SO']->id,
        'descripcion' => $dir['desc'],
      ]);
    }

    $this->command->info('✅ Direcciones creadas');

    // 🔧 NIVEL 3 - ÁREAS DE DIRECCIÓN DE COMPRAS
    $areasCompras = [
      [
        'codigo' => 'DCOMP-LIC-001',
        'nombre' => 'Área de Licitaciones',
        'abrev' => 'LIC',
        'desc' => 'Gestión de licitaciones públicas y privadas'
      ],
      [
        'codigo' => 'DCOMP-COT-001',
        'nombre' => 'Área de Cotizaciones',
        'abrev' => 'COT',
        'desc' => 'Compras por cotización y contratación directa'
      ],
      [
        'codigo' => 'DCOMP-ALM-001',
        'nombre' => 'Almacén Municipal',
        'abrev' => 'ALM',
        'desc' => 'Gestión de inventario y distribución'
      ],
      [
        'codigo' => 'DCOMP-PATR-001',
        'nombre' => 'Patrimonio',
        'abrev' => 'PATR',
        'desc' => 'Control patrimonial y bienes municipales'
      ],
      [
        'codigo' => 'DCOMP-PROV-001',
        'nombre' => 'Registro de Proveedores',
        'abrev' => 'PROV',
        'desc' => 'Registro y habilitación de proveedores'
      ]
    ];

    foreach ($areasCompras as $area) {
      Oficina::create([
        'codigo_oficina' => $area['codigo'],
        'nombre' => $area['nombre'],
        'abreviacion' => $area['abrev'],
        'estado' => 'Habilitada',
        'parent_id' => $direccionesSHCreadas['DCOMP']->id,
        'descripcion' => $area['desc'],
      ]);
    }

    // 💻 NIVEL 3 - ÁREAS DE DIRECCIÓN DE SISTEMAS
    $areasSistemas = [
      [
        'codigo' => 'DIT-DEV-001',
        'nombre' => 'Desarrollo de Sistemas',
        'abrev' => 'DEV',
        'desc' => 'Desarrollo y mantenimiento de software'
      ],
      [
        'codigo' => 'DIT-SUP-001',
        'nombre' => 'Soporte Técnico',
        'abrev' => 'SUP',
        'desc' => 'Mesa de ayuda y soporte a usuarios'
      ],
      [
        'codigo' => 'DIT-RED-001',
        'nombre' => 'Redes y Comunicaciones',
        'abrev' => 'RED',
        'desc' => 'Gestión de redes e infraestructura IT'
      ],
      [
        'codigo' => 'DIT-SEG-001',
        'nombre' => 'Seguridad Informática',
        'abrev' => 'SEG',
        'desc' => 'Seguridad de la información y ciberseguridad'
      ]
    ];

    foreach ($areasSistemas as $area) {
      Oficina::create([
        'codigo_oficina' => $area['codigo'],
        'nombre' => $area['nombre'],
        'abreviacion' => $area['abrev'],
        'estado' => 'Habilitada',
        'parent_id' => $direccionesSGCreadas['DIT']->id,
        'descripcion' => $area['desc'],
      ]);
    }

    // 👥 NIVEL 3 - ÁREAS DE RECURSOS HUMANOS
    $areasRRHH = [
      [
        'codigo' => 'DRRHH-LEG-001',
        'nombre' => 'Legajos',
        'abrev' => 'LEG',
        'desc' => 'Gestión de legajos del personal'
      ],
      [
        'codigo' => 'DRRHH-LIQ-001',
        'nombre' => 'Liquidaciones',
        'abrev' => 'LIQ',
        'desc' => 'Liquidación de haberes y conceptos'
      ],
      [
        'codigo' => 'DRRHH-CAP-001',
        'nombre' => 'Capacitación',
        'abrev' => 'CAP',
        'desc' => 'Formación y desarrollo del personal'
      ]
    ];

    foreach ($areasRRHH as $area) {
      Oficina::create([
        'codigo_oficina' => $area['codigo'],
        'nombre' => $area['nombre'],
        'abreviacion' => $area['abrev'],
        'estado' => 'Habilitada',
        'parent_id' => $direccionesSGCreadas['DRRHH']->id,
        'descripcion' => $area['desc'],
      ]);
    }

    // 🏪 NIVEL 2 - DIRECCIONES OPERATIVAS
    $direccionesOperativas = [
      [
        'codigo' => 'DINSP-001',
        'nombre' => 'Dirección de Inspección',
        'abrev' => 'DINSP',
        'desc' => 'Control y fiscalización municipal',
        'parent' => $secretariasCreadas['SG']->id
      ],
      [
        'codigo' => 'DTRAN-001',
        'nombre' => 'Dirección de Tránsito',
        'abrev' => 'DTRAN',
        'desc' => 'Control de tránsito y transporte',
        'parent' => $secretariasCreadas['SS']->id
      ],
      [
        'codigo' => 'DLIMP-001',
        'nombre' => 'Dirección de Limpieza',
        'abrev' => 'DLIMP',
        'desc' => 'Recolección y limpieza urbana',
        'parent' => $secretariasCreadas['SS']->id
      ],
      [
        'codigo' => 'DALUM-001',
        'nombre' => 'Dirección de Alumbrado',
        'abrev' => 'DALUM',
        'desc' => 'Mantenimiento del alumbrado público',
        'parent' => $secretariasCreadas['SS']->id
      ]
    ];

    foreach ($direccionesOperativas as $dir) {
      Oficina::create([
        'codigo_oficina' => $dir['codigo'],
        'nombre' => $dir['nombre'],
        'abreviacion' => $dir['abrev'],
        'estado' => 'Habilitada',
        'parent_id' => $dir['parent'],
        'descripcion' => $dir['desc'],
      ]);
    }

    // 🚫 OFICINAS DESHABILITADAS (para testing)
    $oficinasDeshabilitadas = [
      [
        'codigo' => 'TEMP-COVID-001',
        'nombre' => 'Oficina Temporal COVID',
        'abrev' => 'TCOVID',
        'desc' => 'Oficina creada durante la pandemia - Ya no utilizada'
      ],
      [
        'codigo' => 'TEMP-CENSO-001',
        'nombre' => 'Oficina Temporal Censo',
        'abrev' => 'TCENSO',
        'desc' => 'Oficina temporal para el censo 2022'
      ]
    ];

    foreach ($oficinasDeshabilitadas as $oficina) {
      Oficina::create([
        'codigo_oficina' => $oficina['codigo'],
        'nombre' => $oficina['nombre'],
        'abreviacion' => $oficina['abrev'],
        'estado' => 'No habilitada',
        'parent_id' => $municipalidad->id,
        'descripcion' => $oficina['desc'],
      ]);
    }

    // Actualizar las relaciones de adjacency list
    if (method_exists(Oficina::class, 'fixTree')) {
      Oficina::fixTree();
    }

    // Estadísticas finales
    $total = Oficina::count();
    $habilitadas = Oficina::habilitadas()->count();
    $deshabilitadas = Oficina::deshabilitadas()->count();
    $niveles = Oficina::max('depth') + 1;

    $this->command->info('');
    $this->command->info('🎉 ¡Seeder ejecutado exitosamente!');
    $this->command->info('📊 ESTADÍSTICAS:');
    $this->command->info("   📋 Total de oficinas: {$total}");
    $this->command->info("   ✅ Oficinas habilitadas: {$habilitadas}");
    $this->command->info("   ❌ Oficinas deshabilitadas: {$deshabilitadas}");
    $this->command->info("   🏗️  Niveles jerárquicos: {$niveles}");
    $this->command->info('');
    $this->command->info('🏛️  Estructura creada:');
    $this->command->info('   └── Municipalidad de Salta');
    $this->command->info('       ├── 6 Secretarías');
    $this->command->info('       ├── 12 Direcciones');
    $this->command->info('       └── 12 Áreas específicas');
  }
}
