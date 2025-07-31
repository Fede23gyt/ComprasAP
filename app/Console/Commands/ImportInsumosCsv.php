<?php

namespace App\Console\Commands;

use App\Models\Insumo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportInsumosCsv extends Command
{
  protected $signature   = 'import:insumos';
  protected $description = 'Importa / actualiza insumos desde CSV respetando jerarquía';

  public function handle()
  {
    $path = storage_path('app/InsumosSIGA.csv');
    if (!file_exists($path)) {
      $this->error('Archivo insumos.csv no encontrado en storage/app');
      return;
    }

    $handle   = fopen($path, 'r');
    /*stream_filter_append($handle, 'convert.iconv.ISO-8859-1/UTF-8');*/
    stream_filter_append($handle, 'convert.iconv.WINDOWS-1252/UTF-8');
    $rootCode = '!!!';
    $map      = []; // [codigo => id]

    // 1. Pasada 1: insertar todos sin padre
    while (($line = fgetcsv($handle, 0, ';')) !== false) {
      $nivel        = trim($line[0]);
      $codigo       = trim($line[2]);
      $descripcion  = trim($line[3]);
      $clasificacion = trim($line[4]);
      $habilitado   = strtolower(trim($line[5])) === '1' ? 1 : 0;
      $rendTribunal = strtolower(trim($line[7])) === '1' ? 1 : 0;
      $presentacion = trim($line[9]);
      $unidad       = trim($line[10]);
      $inventariable= strtolower(trim($line[11])) === '1' ? 1 : 0;
      $precioTestigo= strtolower(trim($line[12])) === '1' ? 1 : 0;
      $tipo         = trim($line[13]);

      $insumo = Insumo::updateOrCreate(
        ['codigo' => $codigo],
        [
          'descripcion'      => $descripcion,
          'clasificacion'    => $clasificacion,
          'habilitado'       => $habilitado,
          'rend_tribunal'    => $rendTribunal,
          'presentacion'     => $presentacion,
          'unidad_solicitada'=> $unidad,
          'inventariable'    => $inventariable,
          'precio_testigo'   => $precioTestigo,
          'tipo'             => $tipo,
          'parent_id'        => null, // se calcula después
        ]
      );
      $map[$codigo] = $insumo->id;
    }
    fclose($handle);

    // 2. Pasada 2: asignar padres
    $handle = fopen($path, 'r');
    while (($line = fgetcsv($handle, 0, ';')) !== false) {
      $nivel  = trim($line[0]);
      $codigo = trim($line[2]);

      if ($nivel === $rootCode) continue; // Raíz no tiene padre

      // Extraer padre (longitud anterior)
      $parentCode = substr($codigo, 0, -3);
      if (isset($map[$parentCode])) {
        Insumo::where('codigo', $codigo)->update(['parent_id' => $map[$parentCode]]);
      }
    }
    fclose($handle);

    $this->info('Insumos importados correctamente.');
  }
}
