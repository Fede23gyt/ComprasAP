<?php

namespace App\Http\Controllers;

use App\Models\Oficina;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OficinaController extends Controller
{
  /**
   * Display a listing of the resource.
   * Mostrar listado de oficinas en estructura de árbol
   */
  public function index(): Response
  {
    // Obtener todas las oficinas ordenadas
    $todasLasOficinas = Oficina::orderBy('nombre')->get();

    // Construir el árbol manualmente
    $oficinas = $this->buildTree($todasLasOficinas);

    $padres = Oficina::habilitadas()
      ->orderBy('nombre')
      ->get(['id', 'nombre']);

    return inertia('Oficinas/Index', [
      'oficinas' => $oficinas,
      'padres' => $padres,
      'stats' => $this->getStats()
    ]);
  }

  /**
   * Show the form for creating a new resource.
   * Mostrar formulario de creación
   */
  public function create(): Response
  {
    return inertia('Oficinas/Create', [
      'padres' => Oficina::habilitadas()
        ->orderBy('nombre')
        ->get(['id', 'nombre']),
    ]);
  }

  /**
   * Store a newly created resource in storage.
   * Guardar nueva oficina
   */
  public function store(Request $request): RedirectResponse
  {
    $validated = $request->validate([
      'nombre' => [
        'required',
        'string',
        'max:255',
        'unique:oficinas,nombre'
      ],
      'codigo_oficina' => [
        'required',
        'string',
        'max:20',
        'unique:oficinas,codigo_oficina',
        'regex:/^[A-Z0-9\-_]+$/'
      ],
      'abreviacion' => [
        'required',
        'string',
        'max:20',
        'unique:oficinas,abreviacion'
      ],
      'parent_id' => [
        'nullable',
        'exists:oficinas,id'
      ],
      'estado' => [
        'required',
        'in:Habilitada,No habilitada'
      ],
      'descripcion' => [
        'nullable',
        'string',
        'max:500'
      ],
      'telefono' => [
        'nullable',
        'string',
        'max:20'
      ],
      'email' => [
        'nullable',
        'email',
        'max:255'
      ],
      'direccion' => [
        'nullable',
        'string',
        'max:300'
      ],
      'responsable' => [
        'nullable',
        'string',
        'max:255'
      ],
    ], [
      'nombre.required' => 'El nombre es obligatorio.',
      'nombre.unique' => 'Ya existe una oficina con este nombre.',
      'codigo_oficina.required' => 'El código de oficina es obligatorio.',
      'codigo_oficina.unique' => 'Ya existe una oficina con este código.',
      'codigo_oficina.regex' => 'El código debe contener solo letras mayúsculas, números y guiones.',
      'abreviacion.required' => 'La abreviación es obligatoria.',
      'abreviacion.unique' => 'Ya existe una oficina con esta abreviación.',
      'parent_id.exists' => 'La oficina padre seleccionada no existe.',
      'estado.required' => 'El estado es obligatorio.',
      'estado.in' => 'El estado debe ser Habilitada o No habilitada.',
    ]);

    try {
      // Validar que no se cree un bucle de dependencia
      if ($validated['parent_id']) {
        $this->validateNoCircularDependency(null, $validated['parent_id']);
      }

      Oficina::create($validated);

      return redirect()
        ->route('oficinas.index')
        ->with('success', 'Oficina creada correctamente.');
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->withInput()
        ->withErrors(['error' => 'Error al crear la oficina: ' . $e->getMessage()]);
    }
  }

  /**
   * Display the specified resource.
   * Mostrar oficina específica
   */
  public function show(Oficina $oficina): Response
  {
    $oficina->load(['parent', 'children']);

    return inertia('Oficinas/Show', [
      'oficina' => $oficina,
      'breadcrumbs' => $this->getBreadcrumbs($oficina),
      'stats' => $this->getOficinaStats($oficina)
    ]);
  }

  /**
   * Show the form for editing the specified resource.
   * Mostrar formulario de edición
   */
  public function edit(Oficina $oficina): Response
  {
    return inertia('Oficinas/Edit', [
      'oficina' => $oficina,
      'padres' => Oficina::habilitadas()
        ->where('id', '!=', $oficina->id)
        ->orderBy('nombre')
        ->get(['id', 'nombre']),
    ]);
  }

  /**
   * Update the specified resource in storage.
   * Actualizar oficina existente
   */
  public function update(Request $request, Oficina $oficina): RedirectResponse
  {
    $validated = $request->validate([
      'nombre' => [
        'required',
        'string',
        'max:255',
        Rule::unique('oficinas')->ignore($oficina->id)
      ],
      'codigo_oficina' => [
        'required',
        'string',
        'max:20',
        Rule::unique('oficinas')->ignore($oficina->id),
        'regex:/^[A-Z0-9\-_]+$/'
      ],
      'abreviacion' => [
        'required',
        'string',
        'max:20',
        Rule::unique('oficinas')->ignore($oficina->id)
      ],
      'parent_id' => [
        'nullable',
        'exists:oficinas,id',
        'not_in:' . $oficina->id
      ],
      'estado' => [
        'required',
        'in:Habilitada,No habilitada'
      ],
      'descripcion' => [
        'nullable',
        'string',
        'max:500'
      ],
      'telefono' => [
        'nullable',
        'string',
        'max:20'
      ],
      'email' => [
        'nullable',
        'email',
        'max:255'
      ],
      'direccion' => [
        'nullable',
        'string',
        'max:300'
      ],
      'responsable' => [
        'nullable',
        'string',
        'max:255'
      ],
    ], [
      'nombre.required' => 'El nombre es obligatorio.',
      'nombre.unique' => 'Ya existe una oficina con este nombre.',
      'codigo_oficina.required' => 'El código de oficina es obligatorio.',
      'codigo_oficina.unique' => 'Ya existe una oficina con este código.',
      'codigo_oficina.regex' => 'El código debe contener solo letras mayúsculas, números y guiones.',
      'abreviacion.required' => 'La abreviación es obligatoria.',
      'abreviacion.unique' => 'Ya existe una oficina con esta abreviación.',
      'parent_id.exists' => 'La oficina padre seleccionada no existe.',
      'parent_id.not_in' => 'Una oficina no puede ser padre de sí misma.',
      'estado.required' => 'El estado es obligatorio.',
      'estado.in' => 'El estado debe ser Habilitada o No habilitada.',
    ]);

    try {
      // Validar que no se cree un bucle de dependencia
      if ($validated['parent_id'] && $validated['parent_id'] != $oficina->parent_id) {
        $this->validateNoCircularDependency($oficina->id, $validated['parent_id']);
      }

      $oficina->update($validated);

      return redirect()
        ->route('oficinas.index')
        ->with('success', 'Oficina actualizada correctamente.');
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->withInput()
        ->withErrors(['error' => 'Error al actualizar la oficina: ' . $e->getMessage()]);
    }
  }

  /**
   * Toggle status of the specified resource.
   * Alternar estado de una oficina
   */
  public function toggle(Oficina $oficina): RedirectResponse
  {
    try {
      // Verificar si se puede deshabilitar
      if ($oficina->estado === 'Habilitada' && !$oficina->puedeSerDeshabilitada()) {
        return redirect()
          ->back()
          ->withErrors(['error' => 'No se puede deshabilitar una oficina que tiene oficinas hijas habilitadas.']);
      }

      $nuevoEstado = $oficina->estado === 'Habilitada' ? 'No habilitada' : 'Habilitada';
      $oficina->estado = $nuevoEstado;
      $oficina->save();

      // Si se deshabilita, deshabilitar descendientes
      if ($nuevoEstado === 'No habilitada') {
        $this->deshabilitarDescendientes($oficina);
      }

      $mensaje = $nuevoEstado === 'Habilitada'
        ? 'Oficina habilitada correctamente.'
        : 'Oficina deshabilitada correctamente.';

      return redirect()
        ->back()
        ->with('success', $mensaje);
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->with('error', 'Error al cambiar el estado de la oficina.');
    }
  }

  // ========================================
  // API ENDPOINTS
  // ========================================

  /**
   * API: Get offices for selects/dropdowns
   * Obtener oficinas para selects dinámicos
   */
  public function getOficinas(Request $request): JsonResponse
  {
    $request->validate([
      'search' => 'nullable|string|max:50',
      'exclude' => 'nullable|integer|exists:oficinas,id',
      'limit' => 'nullable|integer|min:1|max:100'
    ]);

    $query = Oficina::habilitadas();

    // Búsqueda
    if ($request->filled('search')) {
      $search = $request->get('search');
      $query->where(function ($q) use ($search) {
        $q->where('nombre', 'ilike', "%{$search}%")
          ->orWhere('codigo_oficina', 'ilike', "%{$search}%")
          ->orWhere('abreviacion', 'ilike', "%{$search}%");
      });
    }

    // Excluir oficina específica
    if ($request->filled('exclude')) {
      $query->where('id', '!=', $request->get('exclude'));
    }

    $oficinas = $query
      ->orderBy('nombre')
      ->limit($request->get('limit', 50))
      ->get(['id', 'nombre', 'codigo_oficina', 'abreviacion']);

    return response()->json([
      'success' => true,
      'data' => $oficinas,
      'total' => $oficinas->count()
    ]);
  }

  /**
   * API: Get complete hierarchy of an office
   * Obtener jerarquía completa de una oficina
   */
  public function getHierarchy(Oficina $oficina): JsonResponse
  {
    try {
      $hierarchy = $this->getOficinaHierarchy($oficina);

      return response()->json([
        'success' => true,
        'data' => [
          'hierarchy' => $hierarchy,
          'stats' => [
            'total_ancestors' => $this->countAncestors($oficina),
            'total_descendants' => $this->countDescendants($oficina),
            'level' => $this->getOficinaLevel($oficina)
          ]
        ]
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al obtener la jerarquía'
      ], 500);
    }
  }

  /**
   * API: Get complete tree of offices
   * Obtener árbol completo de oficinas
   */
  public function getTree(Request $request): JsonResponse
  {
    $request->validate([
      'include_disabled' => 'nullable|boolean'
    ]);

    try {
      $todasLasOficinas = $request->boolean('include_disabled')
        ? Oficina::orderBy('nombre')->get()
        : Oficina::habilitadas()->orderBy('nombre')->get();

      $tree = $this->buildTree($todasLasOficinas);

      return response()->json([
        'success' => true,
        'data' => $tree
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al obtener el árbol de oficinas'
      ], 500);
    }
  }

  /**
   * API: Validate office code
   * Validar código de oficina
   */
  public function validateCode(Request $request): JsonResponse
  {
    $request->validate([
      'codigo' => 'required|string|max:20',
      'exclude_id' => 'nullable|integer'
    ]);

    $exists = Oficina::where('codigo_oficina', $request->codigo)
      ->when($request->exclude_id, function ($query, $excludeId) {
        return $query->where('id', '!=', $excludeId);
      })
      ->exists();

    return response()->json([
      'available' => !$exists,
      'message' => $exists ? 'El código ya está en uso' : 'Código disponible'
    ]);
  }

  // ========================================
  // PRIVATE HELPER METHODS
  // ========================================

  /**
   * Build tree structure manually
   * Construir árbol de oficinas manualmente
   */
  private function buildTree($oficinas, $parentId = null): array
  {
    $tree = [];

    foreach ($oficinas as $oficina) {
      if ($oficina->parent_id == $parentId) {
        $node = $oficina->toArray();
        $node['children'] = $this->buildTree($oficinas, $oficina->id);
        $tree[] = $node;
      }
    }

    return $tree;
  }

  /**
   * Validate no circular dependency
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
        throw new \Exception('No se puede crear una dependencia circular entre oficinas.');
      }

      if (in_array($current, $visited)) {
        throw new \Exception('Se detectó una dependencia circular en la jerarquía de oficinas.');
      }

      $visited[] = $current;
      $parent = Oficina::find($current);
      $current = $parent?->parent_id;
    }
  }

  /**
   * Disable all descendants of an office
   * Deshabilitar todos los descendientes de una oficina
   */
  private function deshabilitarDescendientes(Oficina $oficina): void
  {
    $hijos = Oficina::where('parent_id', $oficina->id)->get();

    foreach ($hijos as $hijo) {
      if ($hijo->estado === 'Habilitada') {
        $hijo->update(['estado' => 'No habilitada']);
        $this->deshabilitarDescendientes($hijo); // Recursivo
      }
    }
  }

  /**
   * Get office hierarchy
   * Obtener jerarquía de una oficina
   */
  private function getOficinaHierarchy(Oficina $oficina): array
  {
    $hierarchy = [];

    // Obtener ancestros
    $current = $oficina->parent;
    $ancestors = [];
    while ($current) {
      array_unshift($ancestors, $current->toArray());
      $current = $current->parent;
    }
    $hierarchy = array_merge($hierarchy, $ancestors);

    // Agregar la oficina actual
    $hierarchy[] = $oficina->toArray();

    // Obtener descendientes
    $descendants = $this->getDescendants($oficina);
    $hierarchy = array_merge($hierarchy, $descendants);

    return $hierarchy;
  }

  /**
   * Get all descendants of an office
   * Obtener todos los descendientes de una oficina
   */
  private function getDescendants(Oficina $oficina): array
  {
    $descendants = [];
    $hijos = Oficina::where('parent_id', $oficina->id)->get();

    foreach ($hijos as $hijo) {
      $descendants[] = $hijo->toArray();
      $descendants = array_merge($descendants, $this->getDescendants($hijo));
    }

    return $descendants;
  }

  /**
   * Count ancestors of an office
   * Contar ancestros de una oficina
   */
  private function countAncestors(Oficina $oficina): int
  {
    $count = 0;
    $current = $oficina->parent;

    while ($current) {
      $count++;
      $current = $current->parent;
    }

    return $count;
  }

  /**
   * Count descendants of an office
   * Contar descendientes de una oficina
   */
  private function countDescendants(Oficina $oficina): int
  {
    $count = 0;
    $hijos = Oficina::where('parent_id', $oficina->id)->get();

    foreach ($hijos as $hijo) {
      $count++;
      $count += $this->countDescendants($hijo);
    }

    return $count;
  }

  /**
   * Get office level in hierarchy
   * Obtener nivel de la oficina en la jerarquía
   */
  private function getOficinaLevel(Oficina $oficina): int
  {
    return $this->countAncestors($oficina);
  }

  /**
   * Get breadcrumbs for an office
   * Obtener breadcrumbs de una oficina
   */
  private function getBreadcrumbs(Oficina $oficina): array
  {
    $breadcrumbs = [];
    $current = $oficina;

    while ($current) {
      array_unshift($breadcrumbs, [
        'id' => $current->id,
        'nombre' => $current->nombre,
        'codigo' => $current->codigo_oficina,
      ]);
      $current = $current->parent;
    }

    return $breadcrumbs;
  }

  /**
   * Get general statistics
   * Obtener estadísticas generales
   */
  private function getStats(): array
  {
    return [
      'total' => Oficina::count(),
      'habilitadas' => Oficina::habilitadas()->count(),
      'deshabilitadas' => Oficina::deshabilitadas()->count(),
      'raiz' => Oficina::whereNull('parent_id')->count(),
    ];
  }

  /**
   * Get specific office statistics
   * Obtener estadísticas específicas de una oficina
   */
  private function getOficinaStats(Oficina $oficina): array
  {
    return [
      'nivel' => $this->getOficinaLevel($oficina),
      'ancestros' => $this->countAncestors($oficina),
      'descendientes' => $this->countDescendants($oficina),
      'hijos_directos' => Oficina::where('parent_id', $oficina->id)->count(),
      'hijos_habilitados' => Oficina::where('parent_id', $oficina->id)
        ->where('estado', 'Habilitada')->count(),
      'puede_deshabilitar' => $oficina->puedeSerDeshabilitada(),
      'es_raiz' => is_null($oficina->parent_id),
      'es_hoja' => Oficina::where('parent_id', $oficina->id)->count() === 0,
    ];
  }
}
