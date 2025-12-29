<?php

namespace App\Console\Commands;

use App\Models\Insumo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportInsumosCsv extends Command
{
  protected $signature   = 'import:insumos';
  protected $description = 'Importa / actualiza insumos desde archivo CSV con separador ;';

  public function handle()
  {
    $path = storage_path('app/insumos.csv');
    if (!file_exists($path)) {
      $this->error('Archivo insumos.csv no encontrado en storage/app');
      return;
    }

    $handle = fopen($path, 'r');
    // Convertir de Windows-1252 a UTF-8 para manejar caracteres especiales
    stream_filter_append($handle, 'convert.iconv.WINDOWS-1252/UTF-8');

    $map = []; // [codigo => id]
    $contador = 0;
    $actualizados = 0;
    $creados = 0;

    $this->info('Iniciando importación de insumos...');

    // Leer línea por línea y dividir por el separador ;
    while (($linea = fgets($handle)) !== false) {
        // Eliminar saltos de línea y dividir por el separador ;
        $linea = trim($linea);

        // Debug: mostrar primera línea para verificar formato
        if ($contador === 0) {
            $this->info("Primera línea: " . substr($linea, 0, 100) . "...");
            $this->info("Número de separadores: " . substr_count($linea, ';'));
        }

        $line = explode(';', $linea);

        // Saltar líneas vacías
        if (count($line) < 11) {
            if (!empty(trim($linea))) {
                $this->warn("Línea con menos de 11 campos: " . substr($linea, 0, 50) . "...");
            }
            continue;
        }

      // Mapeo de campos según la estructura del archivo CSV (11 campos, sin nivel)
      $codigo = trim($line[0] ?? '');
      $descripcion = trim($line[1] ?? '');
      $clasificacion = trim($line[2] ?? '');
      $presentacion = trim($line[3] ?? '');
      $unidad_solicitud = trim($line[4] ?? '');
      $precio_insumo = $this->parsePrecio(trim($line[5] ?? '0.00'));
      $inventariable = $this->parseBoolean(trim($line[7] ?? 'No'));
      $registrable = $this->parseBoolean(trim($line[8] ?? 'No'));
      $rend_tribunal = $this->parseBoolean(trim($line[9] ?? 'No'));
      $precio_testigo = $this->parseBoolean(trim($line[6] ?? 'No'));
      $tipo = trim($line[10] ?? '');
      $conversion = 0.00; // Campo no presente en el archivo, valor por defecto

      // Validar campos requeridos
      if (empty($codigo) || empty($descripcion)) {
        $this->warn("Saltando línea - Código o descripción vacíos: {$codigo}");
        continue;
      }

      try {
        // Buscar si el insumo ya existe
        $insumoExistente = Insumo::where('codigo', $codigo)->first();

        $datosInsumo = [
          'descripcion' => $descripcion,
          'clasificacion' => $clasificacion,
          'presentacion' => $presentacion,
          'unidad_solicitud' => $unidad_solicitud,
          'precio_insumo' => $precio_insumo,
          'inventariable' => $inventariable,
          'registrable' => $registrable,
          'rend_tribunal' => $rend_tribunal,
          'precio_testigo' => $precio_testigo,
          'conversion' => $conversion,
          'tipo' => $tipo,
          'fecha_ultima' => now(),
        ];

        if ($insumoExistente) {
          // Actualizar insumo existente
          $insumoExistente->update($datosInsumo);
          $actualizados++;
          $this->info("Actualizado: {$codigo} - {$descripcion}");
        } else {
          // Crear nuevo insumo
          Insumo::create(array_merge(['codigo' => $codigo], $datosInsumo));
          $creados++;
          $this->info("Creado: {$codigo} - {$descripcion}");
        }

        $contador++;
        $map[$codigo] = $codigo; // Guardar código para posible jerarquía

        // Mostrar progreso cada 100 registros
        if ($contador % 100 === 0) {
          $this->line("Procesados: {$contador} registros...");
        }

      } catch (\Exception $e) {
        $this->error("Error procesando código {$codigo}: " . $e->getMessage());
        continue;
      }
    }

    fclose($handle);

    $this->info("\nImportación completada:");
    $this->info("Total procesados: {$contador}");
    $this->info("Nuevos insumos: {$creados}");
    $this->info("Insumos actualizados: {$actualizados}");

    // TODO: Implementar lógica de jerarquía si es necesaria
    // $this->procesarJerarquia($map);
  }

  /**
   * Parsear valor booleano desde texto
   */
  private function parseBoolean($value)
  {
    $value = strtolower(trim($value));
    return in_array($value, ['si', 'sí', 'yes', 'true', '1', 'verdadero']);
  }

  /**
   * Parsear precio desde formato string
   */
  private function parsePrecio($precio)
  {
    // Remover puntos de miles y convertir coma decimal a punto
    $precio = str_replace('.', '', $precio);
    $precio = str_replace(',', '.', $precio);

    return floatval($precio);
  }

  /**
   * Procesar jerarquía de insumos (si es necesaria)
   */
  private function procesarJerarquia($map)
  {
    $this->info('Procesando jerarquía de insumos...');
    // Implementar lógica de jerarquía si los códigos tienen estructura padre-hijo
  }
}
