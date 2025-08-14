<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
  /**
   * Display a listing of roles
   */
  public function index(Request $request): Response
  {
    $query = Role::query()->orderBy('name');

    // Filtro por búsqueda si existe
    if ($request->has('search') && !empty($request->search)) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->where('name', 'ILIKE', "%{$search}%")
          ->orWhere('display_name', 'ILIKE', "%{$search}%")
          ->orWhere('description', 'ILIKE', "%{$search}%");
      });
    }

    // Filtro por estado
    if ($request->has('status') && $request->status !== '') {
      $query->where('is_active', $request->boolean('status'));
    }

    $roles = $query->withCount('users')->paginate(15)->withQueryString();

    return Inertia::render('Admin/Roles/Index', [
      'roles' => $roles,
      'filters' => $request->only(['search', 'status']),
      'stats' => $this->getRoleStats(),
    ]);
  }

  /**
   * Show the form for creating a new role
   */
  public function create(): Response
  {
    return Inertia::render('Admin/Roles/Create', [
      'availablePermissions' => $this->getAvailablePermissions(),
    ]);
  }

  /**
   * Store a newly created role
   */
  public function store(Request $request): RedirectResponse
  {
    $validated = $request->validate([
      'name' => [
        'required',
        'string',
        'max:50',
        'unique:roles,name',
        'regex:/^[a-z_]+$/',
      ],
      'display_name' => [
        'required',
        'string',
        'max:100',
        'unique:roles,display_name',
      ],
      'description' => [
        'nullable',
        'string',
        'max:500',
      ],
      'permissions' => [
        'nullable',
        'array',
      ],
      'permissions.*' => [
        'string',
        'in:' . implode(',', array_keys($this->getAvailablePermissions())),
      ],
      'is_active' => [
        'required',
        'boolean',
      ],
    ], [
      'name.required' => 'El nombre del rol es obligatorio.',
      'name.unique' => 'Ya existe un rol con este nombre.',
      'name.regex' => 'El nombre solo puede contener letras minúsculas y guiones bajos.',
      'display_name.required' => 'El nombre para mostrar es obligatorio.',
      'display_name.unique' => 'Ya existe un rol con este nombre para mostrar.',
      'permissions.*.in' => 'Uno o más permisos seleccionados no son válidos.',
    ]);

    try {
      Role::create($validated);

      return redirect()
        ->route('admin.roles.index')
        ->with('success', 'Rol creado correctamente.');
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->withInput()
        ->with('error', 'Error al crear el rol: ' . $e->getMessage());
    }
  }

  /**
   * Display the specified role
   */
  public function show(Role $role): Response
  {
    $role->load(['users' => function ($query) {
      $query->select('id', 'name', 'email', 'is_active', 'role_id')
        ->with('oficinas:id,nombre,abreviacion');
    }]);

    return Inertia::render('Admin/Roles/Show', [
      'role' => $role,
      'permissions' => $this->getAvailablePermissions(),
    ]);
  }

  /**
   * Show the form for editing the specified role
   */
  public function edit(Role $role): Response
  {
    return Inertia::render('Admin/Roles/Edit', [
      'role' => $role,
      'availablePermissions' => $this->getAvailablePermissions(),
    ]);
  }

  /**
   * Update the specified role
   */
  public function update(Request $request, Role $role): RedirectResponse
  {
    $validated = $request->validate([
      'name' => [
        'required',
        'string',
        'max:50',
        Rule::unique('roles')->ignore($role->id),
        'regex:/^[a-z_]+$/',
      ],
      'display_name' => [
        'required',
        'string',
        'max:100',
        Rule::unique('roles')->ignore($role->id),
      ],
      'description' => [
        'nullable',
        'string',
        'max:500',
      ],
      'permissions' => [
        'nullable',
        'array',
      ],
      'permissions.*' => [
        'string',
        'in:' . implode(',', array_keys($this->getAvailablePermissions())),
      ],
      'is_active' => [
        'required',
        'boolean',
      ],
    ], [
      'name.required' => 'El nombre del rol es obligatorio.',
      'name.unique' => 'Ya existe un rol con este nombre.',
      'name.regex' => 'El nombre solo puede contener letras minúsculas y guiones bajos.',
      'display_name.required' => 'El nombre para mostrar es obligatorio.',
      'display_name.unique' => 'Ya existe un rol con este nombre para mostrar.',
      'permissions.*.in' => 'Uno o más permisos seleccionados no son válidos.',
    ]);

    try {
      $role->update($validated);

      return redirect()
        ->route('admin.roles.index')
        ->with('success', 'Rol actualizado correctamente.');
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->withInput()
        ->with('error', 'Error al actualizar el rol: ' . $e->getMessage());
    }
  }

  /**
   * Remove the specified role
   */
  public function destroy(Role $role): RedirectResponse
  {
    try {
      // Verificar si el rol tiene usuarios asignados
      if ($role->users()->count() > 0) {
        return redirect()
          ->back()
          ->with('error', 'No se puede eliminar un rol que tiene usuarios asignados.');
      }

      // Verificar si es un rol del sistema
      $systemRoles = ['administrador', 'secretario', 'director', 'operador', 'invitado'];
      if (in_array($role->name, $systemRoles)) {
        return redirect()
          ->back()
          ->with('error', 'No se puede eliminar un rol del sistema.');
      }

      $roleName = $role->display_name;
      $role->delete();

      return redirect()
        ->route('admin.roles.index')
        ->with('success', "Rol '{$roleName}' eliminado correctamente.");
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->with('error', 'Error al eliminar el rol: ' . $e->getMessage());
    }
  }

  /**
   * Toggle role status
   */
  public function toggleStatus(Role $role): RedirectResponse
  {
    try {
      $role->is_active = !$role->is_active;
      $role->save();

      $status = $role->is_active ? 'activado' : 'desactivado';

      return redirect()
        ->back()
        ->with('success', "Rol '{$role->display_name}' {$status} correctamente.");
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->with('error', 'Error al cambiar el estado del rol.');
    }
  }

  /**
   * Get available permissions
   */
  private function getAvailablePermissions(): array
  {
    return [
      // Gestión de usuarios y sistema
      'manage_users' => 'Gestionar usuarios',
      'manage_roles' => 'Gestionar roles',
      'manage_offices' => 'Gestionar oficinas',
      'manage_config' => 'Gestionar configuración',

      // Notas de pedido
      'create_any_nota' => 'Crear notas para cualquier oficina',
      'create_own_notas' => 'Crear notas para oficinas asignadas',
      'authorize_notas' => 'Autorizar notas de pedido',
      'view_all_notas' => 'Ver todas las notas',
      'view_own_notas' => 'Ver propias notas',

      // Presupuestos y órdenes
      'manage_budgets' => 'Gestionar presupuestos',
      'manage_orders' => 'Gestionar órdenes de compra',
      'view_budgets' => 'Ver presupuestos',
      'view_orders' => 'Ver órdenes de compra',

      // Reportes
      'view_all_reports' => 'Ver todos los reportes',
      'view_own_reports' => 'Ver reportes propios',
      'export_reports' => 'Exportar reportes',

      // Nomencladores
      'manage_nomenclators' => 'Gestionar nomencladores',
      'view_nomenclators' => 'Ver nomencladores',
    ];
  }

  /**
   * Get role statistics
   */
  private function getRoleStats(): array
  {
    return [
      'total' => Role::count(),
      'active' => Role::where('is_active', true)->count(),
      'inactive' => Role::where('is_active', false)->count(),
      'with_users' => Role::has('users')->count(),
      'system_roles' => Role::whereIn('name', ['administrador', 'secretario', 'director', 'operador', 'invitado'])->count(),
    ];
  }

  /**
   * Get roles for API (for selects)
   */
  public function getRolesForSelect(): \Illuminate\Http\JsonResponse
  {
    $roles = Role::active()
      ->orderBy('display_name')
      ->get(['id', 'name', 'display_name']);

    return response()->json($roles);
  }
}
