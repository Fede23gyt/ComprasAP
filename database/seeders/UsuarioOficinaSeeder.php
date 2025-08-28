<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Oficina;

class UsuarioOficinaSeeder extends Seeder
{
  public function run()
  {
    // Obtener todas las oficinas
    $oficinas = Oficina::all();

    if ($oficinas->isEmpty()) {
      $this->command->info('No hay oficinas para asignar.');
      return;
    }

    // 1️⃣ Administrador y Supervisor -> TODAS las oficinas
    $usuariosFullAccess = User::whereHas('roles', function ($query) {
      $query->whereIn('name', ['administrador', 'supervisor']);
    })->get();

    foreach ($usuariosFullAccess as $usuario) {
      $syncData = [];
      foreach ($oficinas as $oficina) {
        $syncData[$oficina->id] = [
          'es_principal' => false,
          'puede_autorizar' => true,
        ];
      }

      // Asignar todas las oficinas
      $usuario->oficinas()->sync($syncData);
    }

    // 2️⃣ Otros usuarios -> asignación personalizada (ejemplo)
    $otrosUsuarios = User::whereDoesntHave('roles', function ($query) {
      $query->whereIn('name', ['administrador', 'supervisor']);
    })->get();

    foreach ($otrosUsuarios as $usuario) {
      // Por ejemplo, asignamos solo la primera oficina como principal
      if ($oficinas->first()) {
        $usuario->oficinas()->sync([
          $oficinas->first()->id => [
            'es_principal' => true,
            'puede_autorizar' => false,
          ],
        ]);
      }
    }

    $this->command->info('Usuarios asignados a oficinas correctamente.');
  }
}
