<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Oficina;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
  /**
   * Display a listing of users
   */
  public function index(Request $request): Response
  {
    $query = User::with(['role', 'oficinas'])
      ->orderBy('name');

    // Filtro por búsqueda
    if ($request->has('search') && !empty($request->search)) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->where('name', 'ILIKE', "%{$search}%")
          ->orWhere('email', 'ILIKE', "%{$search}%")
          ->orWhere('dni', 'ILIKE', "%{$search}%");
      });
    }

    // Filtro por rol
    if ($request->has('role') && $request->role !== '') {
      $query->where('role_id', $request->role);
    }

    // Filtro por estado
    if ($request->has('status') && $request->status !== '') {
      $query->where('is_active', $request->boolean('status'));
    }

    // Filtro por oficina
    if ($request->has('oficina') && $request->oficina !== '') {
      $query->whereHas('oficinas', function ($q) use ($request) {
        $q->where('oficina_id', $request->oficina);
      });
    }

    $users = $query->paginate(15)->withQueryString();

    return Inertia::render('Admin/Users/Index', [
      'users' => $users,
      'filters' => $request->only(['search', 'role', 'status', 'oficina']),
      'roles' => Role::active()->get(['id', 'display_name']),
      'oficinas' => Oficina::habilitadas()->get(['id', 'nombre']),
      'stats' => $this->getUserStats(),
    ]);
  }

  /**
   * Show the form for creating a new user
   */
  public function create(): Response
  {
    return Inertia::render('Admin/Users/Create', [
      'roles' => Role::active()->get(['id', 'name', 'display_name']),
      'oficinas' => Oficina::habilitadas()->orderBy('nombre')->get(['id', 'nombre', 'codigo_oficina']),
    ]);
  }

  /**
   * Store a newly created user
   */
  public function store(Request $request): RedirectResponse
  {
    $validated = $request->validate([
      'name' => [
        'required',
        'string',
        'max:255',
      ],
      'email' => [
        'required',
        'string',
        'email',
        'max:255',
        'unique:users,email',
      ],
      'dni' => [
        'nullable',
        'string',
        'max:20',
        'unique:users,dni',
      ],
      'telefono' => [
        'nullable',
        'string',
        'max:20',
      ],
      'password' => [
        'required',
        'string',
        'min:8',
        'confirmed',
      ],
      'role_id' => [
        'required',
        'exists:roles,id',
      ],
      'is_active' => [
        'required',
        'boolean',
      ],
      'oficinas' => [
        'nullable',
        'array',
      ],
      'oficinas.*' => [
        'exists:oficinas,id',
      ],
      'oficina_principal' => [
        'nullable',
        'exists:oficinas,id',
      ],
      'observaciones' => [
        'nullable',
        'string',
        'max:500',
      ],
    ], [
      'name.required' => 'El nombre es obligatorio.',
      'email.required' => 'El email es obligatorio.',
      'email.unique' => 'Ya existe un usuario con este email.',
      'dni.unique' => 'Ya existe un usuario con este DNI.',
      'password.required' => 'La contraseña es obligatoria.',
      'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
      'password.confirmed' => 'La confirmación de contraseña no coincide.',
      'role_id.required' => 'El rol es obligatorio.',
      'role_id.exists' => 'El rol seleccionado no existe.',
      'oficinas.*.exists' => 'Una o más oficinas seleccionadas no existen.',
      'oficina_principal.exists' => 'La oficina principal seleccionada no existe.',
    ]);

    try {
      DB::beginTransaction();

      // Crear usuario
      $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'dni' => $validated['dni'],
        'telefono' => $validated['telefono'],
        'password' => Hash::make($validated['password']),
        'role_id' => $validated['role_id'],
        'is_active' => $validated['is_active'],
        'observaciones' => $validated['observaciones'],
      ]);

      // Asignar oficinas si se proporcionaron
      if (!empty($validated['oficinas'])) {
        $oficinasPrincipal = $validated['oficina_principal'] ?? null;
        $user->syncOficinas($validated['oficinas'], $oficinasPrincipal);
      }

      DB::commit();

      return redirect()
        ->route('admin.users.index')
        ->with('success', 'Usuario creado correctamente.');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()
        ->back()
        ->withInput()
        ->with('error', 'Error al crear el usuario: ' . $e->getMessage());
    }
  }

  /**
   * Display the specified user
   */
  public function show(User $user): Response
  {
    $user->load(['role', 'oficinas']);

    return Inertia::render('Admin/Users/Show', [
      'user' => $user,
      'oficinaPrincipal' => $user->oficinaPrincipal(),
      'oficinasAutoriza' => $user->oficinasQueAutoriza()->get(),
    ]);
  }

  /**
   * Show the form for editing the specified user
   */
  public function edit(User $user): Response
  {
    $user->load(['role', 'oficinas']);

    return Inertia::render('Admin/Users/Edit', [
      'user' => $user,
      'roles' => Role::active()->get(['id', 'name', 'display_name']),
      'oficinas' => Oficina::habilitadas()->orderBy('nombre')->get(['id', 'nombre', 'codigo_oficina']),
      'userOficinas' => $user->oficinas->map(function ($oficina) {
        return [
          'id' => $oficina->id,
          'nombre' => $oficina->nombre,
          'es_principal' => $oficina->pivot->es_principal,
          'puede_autorizar' => $oficina->pivot->puede_autorizar,
        ];
      }),
    ]);
  }

  /**
   * Update the specified user
   */
  public function update(Request $request, User $user): RedirectResponse
  {
    $validated = $request->validate([
      'name' => [
        'required',
        'string',
        'max:255',
      ],
      'email' => [
        'required',
        'string',
        'email',
        'max:255',
        Rule::unique('users')->ignore($user->id),
      ],
      'dni' => [
        'nullable',
        'string',
        'max:20',
        Rule::unique('users')->ignore($user->id),
      ],
      'telefono' => [
        'nullable',
        'string',
        'max:20',
      ],
      'password' => [
        'nullable',
        'string',
        'min:8',
        'confirmed',
      ],
      'role_id' => [
        'required',
        'exists:roles,id',
      ],
      'is_active' => [
        'required',
        'boolean',
      ],
      'oficinas' => [
        'nullable',
        'array',
      ],
      'oficinas.*' => [
        'exists:oficinas,id',
      ],
      'oficina_principal' => [
        'nullable',
        'exists:oficinas,id',
      ],
      'observaciones' => [
        'nullable',
        'string',
        'max:500',
      ],
    ], [
      'name.required' => 'El nombre es obligatorio.',
      'email.required' => 'El email es obligatorio.',
      'email.unique' => 'Ya existe un usuario con este email.',
      'dni.unique' => 'Ya existe un usuario con este DNI.',
      'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
      'password.confirmed' => 'La confirmación de contraseña no coincide.',
      'role_id.required' => 'El rol es obligatorio.',
      'role_id.exists' => 'El rol seleccionado no existe.',
      'oficinas.*.exists' => 'Una o más oficinas seleccionadas no existen.',
      'oficina_principal.exists' => 'La oficina principal seleccionada no existe.',
    ]);

    try {
      DB::beginTransaction();

      // Preparar datos para actualizar
      $updateData = [
        'name' => $validated['name'],
        'email' => $validated['email'],
        'dni' => $validated['dni'],
        'telefono' => $validated['telefono'],
        'role_id' => $validated['role_id'],
        'is_active' => $validated['is_active'],
        'observaciones' => $validated['observaciones'],
      ];

      // Solo actualizar contraseña si se proporcionó
      if (!empty($validated['password'])) {
        $updateData['password'] = Hash::make($validated['password']);
      }

      $user->update($updateData);

      // Actualizar oficinas si se proporcionaron
      if (isset($validated['oficinas'])) {
        $oficinasPrincipal = $validated['oficina_principal'] ?? null;
        $user->syncOficinas($validated['oficinas'], $oficinasPrincipal);
      }

      DB::commit();

      return redirect()
        ->route('admin.users.index')
        ->with('success', 'Usuario actualizado correctamente.');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()
        ->back()
        ->withInput()
        ->with('error', 'Error al actualizar el usuario: ' . $e->getMessage());
    }
  }

  /**
   * Remove the specified user
   */
  public function destroy(User $user): RedirectResponse
  {
    try {
      // No permitir eliminar el propio usuario
      if (auth()->id() === $user->id) {
        return redirect()
          ->back()
          ->with('error', 'No puedes eliminar tu propio usuario.');
      }

      $userName = $user->name;
      $user->delete();

      return redirect()
        ->route('admin.users.index')
        ->with('success', "Usuario '{$userName}' eliminado correctamente.");
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->with('error', 'Error al eliminar el usuario: ' . $e->getMessage());
    }
  }

  /**
   * Toggle user status
   */
  public function toggleStatus(User $user): RedirectResponse
  {
    try {
      // No permitir desactivar el propio usuario
      if (auth()->id() === $user->id && $user->is_active) {
        return redirect()
          ->back()
          ->with('error', 'No puedes desactivar tu propio usuario.');
      }

      $user->is_active = !$user->is_active;
      $user->save();

      $status = $user->is_active ? 'activado' : 'desactivado';

      return redirect()
        ->back()
        ->with('success', "Usuario '{$user->name}' {$status} correctamente.");
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->with('error', 'Error al cambiar el estado del usuario.');
    }
  }

  /**
   * Update user offices authorization
   */
  public function updateOfficeAuthorization(Request $request, User $user): RedirectResponse
  {
    $validated = $request->validate([
      'oficina_id' => 'required|exists:oficinas,id',
      'puede_autorizar' => 'required|boolean',
    ]);

    try {
      $user->oficinas()->updateExistingPivot(
        $validated['oficina_id'],
        ['puede_autorizar' => $validated['puede_autorizar']]
      );

      $action = $validated['puede_autorizar'] ? 'otorgado' : 'retirado';

      return redirect()
        ->back()
        ->with('success', "Permiso de autorización {$action} correctamente.");
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->with('error', 'Error al actualizar el permiso de autorización.');
    }
  }

  /**
   * Get user statistics
   */
  private function getUserStats(): array
  {
    return [
      'total' => User::count(),
      'active' => User::where('is_active', true)->count(),
      'inactive' => User::where('is_active', false)->count(),
      'with_oficinas' => User::has('oficinas')->count(),
      'admins' => User::whereHas('role', function ($q) {
        $q->where('name', 'administrador');
      })->count(),
    ];
  }

  /**
   * Get users for API (for selects)
   */
  public function getUsersForSelect(Request $request): \Illuminate\Http\JsonResponse
  {
    $query = User::with('role:id,display_name')
      ->where('is_active', true)
      ->orderBy('name');

    if ($request->has('role')) {
      $query->whereHas('role', function ($q) use ($request) {
        $q->where('name', $request->role);
      });
    }

    $users = $query->get(['id', 'name', 'email', 'role_id']);

    return response()->json($users);
  }
}
