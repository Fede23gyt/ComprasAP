<?php

namespace App\Services;

use App\Models\Oficina;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OficinaService
{
  /**
   * Crear nueva oficina
   */
  public function create(array $data): Oficina
  {
    DB::beginTransaction();

    try {
      // Validar dependencia circular
      if (isset($data['parent_id'])) {
        $this->validateNoCircularDependency(null, $data['parent_id']);
      }

      $oficina = Oficina::create($data);

      DB::commit();

      Log::info('Oficina creada', [
        'oficina_id' => $oficina->id,
        'codigo' => $oficina->codigo_oficina,
        'user_id' => auth()->id()
      ]);

      return $oficina;
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Error al crear oficina', [
        'error' => $e->getMessage(),
        'data' => $data,
        'user_id' => auth()->id()
      ]);
      throw $e;
    }
  }

  /**
   * Actualizar oficina existente
   */
  public function update(Oficina $oficina, array $data): Oficina
  {
    DB::beginTransaction();

    try {
      $originalData = $oficina->toArray();

      // Validar dependencia circular si cambió el parent_id
      if (isset($data['parent_id']) && $data['parent_id'] != $oficina->parent_id) {
        $this->validateNoCircularDependency($oficina->id, $data['parent_id']);
      }

      $oficina->update($data);

      DB::commit();

      Log::info('Oficina actualizada', [
        'oficina_id' => $oficina->id,
        'changes' => array_diff_assoc($data, $originalData),
        'user_id' => auth()->id()
      ]);

      return $oficina->fresh();
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Error al actualizar oficina', [
        'oficina_id' => $oficina->id,
        'error' => $e->getMessage(),
        'data' => $data,
        'user_id' => auth()->id()
      ]);
      throw $e;
    }
  }

  /**
   * Alternar estado de una oficina
   */
  public function toggleEstado(Oficina $oficina): array
  {
    DB::beginTransaction();

    try {
      $nuevoEstado = $oficina->estado === 'Habilitada' ? 'No habilitada' : 'Habilitada';

      // Verificar si se puede deshabilitar
      if ($nuevoEstado === 'No habilitada' && !$oficina->puedeSerDeshabilitada()) {
        return [
          'success' => false,
          'message' => 'No se puede deshabilitar una oficina que tiene oficinas hijas habilitadas.'
        ];
      }

      $estadoAnterior = $oficina->estado;
      $oficina->estado = $nuevoEstado;
      $oficina->save();

      // Si se deshabilita, deshabilitar descendientes
      if ($nuevoEstado === 'No habilitada') {
        $this->deshabilitarDescendientes($oficina);
      }

      DB::commit();

      $mensaje = $nuevoEstado === 'Habilitada'
        ? 'Oficina habilitada correctamente.'
        : 'Oficina deshabilitada correctamente.';

      Log::info('Estado de oficina cambiado', [
        'oficina_id' => $oficina->id,
        'estado_anterior' => $estadoAnterior,
        'estado_nuevo' => $nuevoEstado,
        'user_id' => auth()->id()
      ]);

      return [
        'success' => true,
        'message' => $mensaje
      ];
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Error al cambiar estado de oficina', [
        'oficina_id' => $oficina->id,
        'error' => $e->getMessage(),
        'user_id' => auth()->id()
      ]);
      throw $e;
    }
  }

  /**
   * Obtener jerarquía completa de una oficina
   */
  public function getHierarchy(Oficina $oficina): Collection
  {
    $hierarchy = collect();

    // Obtener ancestros
    $ancestors = $oficina->ancestors()->orderBy('depth')->get();
    $hierarchy = $hierarchy->merge($ancestors);

    // Agregar la oficina actual
    $hierarchy->push($oficina);

    // Obtener descendientes
    $descendants = $oficina->descendants()->orderBy('depth')->get();
    $hierarchy = $hierarchy->merge($descendants);

    return $hierarchy;
  }

  /**
   * Buscar oficinas con filtros avanzados
   */
  public function search(array $filters): Collection
  {
    $query = Oficina::query();

    // Filtro por estado
    if (isset($filters['estado'])) {
      $query->where('estado', $filters['estado']);
    }

    // Filtro por nivel
    if (isset($filters['nivel'])) {
      $query->where('depth', $filters['nivel']);
    }

    // Búsqueda por texto
    if (isset($filters['search']) && !empty($filters['search'])) {
      $search = $filters['search'];
      $query->where(function ($q) use ($search) {
        $q->where('nombre', 'ilike', "%{$search}%")
          ->orWhere('codigo_oficina', 'ilike', "%{$search}%")
          ->orWhere('abreviacion', 'ilike', "%{$search}%")
          ->orWhere('descripcion', 'ilike', "%{$search}%");
      });
    }

    // Filtro por oficina padre
    if (isset($filters['parent_id'])) {
      $query->where('parent_id', $filters['parent_id']);
    }

    return $query->orderBy('nombre')->get();
  }

  /**
   * Validar que no se cree una dependencia circular
   */
  private function validateNoCircularDependency(?int $oficinaId, ?int $parentId): void
  {
    if (!$parentId) {
      return;
    }

    $visited = [];
    $current = $parentId;

    while ($current) {
      if ($current === $oficinaId) {
        throw new \InvalidArgumentException('No se puede crear una dependencia circular entre oficinas.');
      }

      if (in_array($current, $visited)) {
        throw new \InvalidArgumentException('Se detectó una dependencia circular en la jerarquía de oficinas.');
      }

      $visited[] = $current;
      $parent = Oficina::find($current);
      $current = $parent?->parent_id;
    }
  }

  /**
   * Deshabilitar todos los descendientes de una oficina
   */
  private function deshabilitarDescendientes(Oficina $oficina): void
  {
    $descendientes = $oficina->descendants()->where('estado', 'Habilitada')->get();

    foreach ($descendientes as $descendiente) {
      $descendiente->update(['estado' => 'No habilitada']);

      Log::info('Descendiente deshabilitado automáticamente', [
        'oficina_padre_id' => $oficina->id,
        'descendiente_id' => $descendiente->id,
        'user_id' => auth()->id()
      ]);
    }
  }

  /**
   * Obtener estadísticas de una oficina
   */
  public function getEstadisticasDetalladas(Oficina $oficina): array
  {
    return [
      'basicas' => $oficina->getEstadisticas(),
      'jerarquia' => [
        'ancestros' => $oficina->ancestors()->count(),
        'descendientes_directos' => $oficina->children()->count(),
        'descendientes_totales' => $oficina->descendants()->count(),
        'nivel_maximo' => $oficina->descendants()->max('depth') ?? $oficina->depth
      ],
      'estados' => [
        'hijos_habilitados' => $oficina->children()->habilitadas()->count(),
        'hijos_deshabilitados' => $oficina->children()->deshabilitadas()->count(),
        'puede_deshabilitar' => $oficina->puedeSerDeshabilitada()
      ]
    ];
  }
}
