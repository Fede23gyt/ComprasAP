<?php

namespace App\Http\Controllers;

use App\Models\Oficina;
use App\Http\Requests\StoreOficinaRequest;
use App\Http\Requests\UpdateOficinaRequest;
use App\Services\OficinaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OficinaController extends Controller
{
  protected OficinaService $oficinaService;

  public function __construct(OficinaService $oficinaService)
  {
    $this->oficinaService = $oficinaService;
  }

  /**
   * Mostrar listado de oficinas en estructura de árbol
   */
  public function index(): Response
  {
    $oficinas = Oficina::tree()->get()->toTree();
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
   * Guardar nueva oficina
   */
  public function store(StoreOficinaRequest $request): RedirectResponse
  {
    try {
      $this->oficinaService->create($request->validated());

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
   * Mostrar oficina específica
   */
  public function show(Oficina $oficina): Response
  {
    $oficina->load(['parent', 'children']);

    return inertia('Oficinas/Show', [
      'oficina' => $oficina,
      'breadcrumbs' => $oficina->getBreadcrumbs(),
      'stats' => $oficina->getEstadisticas()
    ]);
  }

  /**
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
   * Actualizar oficina existente
   */
  public function update(UpdateOficinaRequest $request, Oficina $oficina): RedirectResponse
  {
    try {
      $this->oficinaService->update($oficina, $request->validated());

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
   * Alternar estado de una oficina
   */
  public function toggle(Oficina $oficina): RedirectResponse
  {
    try {
      $result = $this->oficinaService->toggleEstado($oficina);

      if (!$result['success']) {
        return redirect()
          ->back()
          ->withErrors(['error' => $result['message']]);
      }

      return redirect()
        ->back()
        ->with('success', $result['message']);
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->with('error', 'Error al cambiar el estado de la oficina.');
    }
  }

  /**
   * API: Obtener oficinas para selects dinámicos
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
   * API: Obtener jerarquía completa de una oficina
   */
  public function getHierarchy(Oficina $oficina): JsonResponse
  {
    try {
      $hierarchy = $this->oficinaService->getHierarchy($oficina);

      return response()->json([
        'success' => true,
        'data' => [
          'hierarchy' => $hierarchy,
          'stats' => [
            'total_ancestors' => $oficina->ancestors()->count(),
            'total_descendants' => $oficina->descendants()->count(),
            'level' => $oficina->nivel
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
   * API: Obtener árbol completo de oficinas
   */
  public function getTree(Request $request): JsonResponse
  {
    $request->validate([
      'include_disabled' => 'nullable|boolean'
    ]);

    try {
      $query = $request->boolean('include_disabled')
        ? Oficina::query()
        : Oficina::habilitadas();

      $tree = $query->tree()->get()->toTree();

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
   * API: Validar código de oficina
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

  /**
   * Obtener estadísticas generales
   */
  private function getStats(): array
  {
    return [
      'total' => Oficina::count(),
      'habilitadas' => Oficina::habilitadas()->count(),
      'deshabilitadas' => Oficina::deshabilitadas()->count(),
      'raiz' => Oficina::raiz()->count(),
      'hojas' => Oficina::hoja()->count(),
      'niveles' => Oficina::max('depth') + 1
    ];
  }
}
