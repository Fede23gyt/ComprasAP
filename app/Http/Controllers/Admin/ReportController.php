<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Oficina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    /**
     * Dashboard de reportes administrativos
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Reports/Index', [
            'stats' => $this->getGeneralStats(),
            'usersByRole' => $this->getUsersByRole(),
            'usersActivity' => $this->getUsersActivity(),
            'officeStats' => $this->getOfficeStats(),
            'recentActivity' => $this->getRecentActivity(),
        ]);
    }

    /**
     * Reporte detallado de usuarios
     */
    public function users(Request $request): Response
    {
        $query = User::with(['role', 'oficinas'])
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->role) {
            $query->where('role_id', $request->role);
        }

        if ($request->status !== null) {
            $query->where('is_active', $request->boolean('status'));
        }

        if ($request->has_offices !== null) {
            if ($request->boolean('has_offices')) {
                $query->has('oficinas');
            } else {
                $query->doesntHave('oficinas');
            }
        }

        $users = $query->paginate(20);

        return Inertia::render('Admin/Reports/Users', [
            'users' => $users,
            'filters' => $request->only(['role', 'status', 'has_offices']),
            'roles' => Role::active()->get(['id', 'display_name']),
            'stats' => $this->getUsersReportStats($request),
        ]);
    }

    /**
     * Reporte detallado de roles
     */
    public function roles(): Response
    {
        $roles = Role::withCount(['users', 'users as active_users_count' => function ($q) {
            $q->where('is_active', true);
        }])->orderBy('access_level', 'desc')->get();

        return Inertia::render('Admin/Reports/Roles', [
            'roles' => $roles,
            'permissionGroups' => $this->getPermissionGroups(),
            'stats' => $this->getRolesReportStats(),
        ]);
    }

    /**
     * Reporte de oficinas y usuarios
     */
    public function offices(): Response
    {
        $offices = Oficina::withCount(['users' => function ($q) {
            $q->where('is_active', true);
        }])
        ->with(['parent:id,nombre', 'children:id,nombre,parent_id'])
        ->habilitadas()
        ->get();

        return Inertia::render('Admin/Reports/Offices', [
            'offices' => $offices,
            'stats' => $this->getOfficesReportStats(),
            'hierarchyData' => $this->getOfficeHierarchy(),
        ]);
    }

    /**
     * Reporte de actividad del sistema
     */
    public function activity(Request $request): Response
    {
        $days = $request->get('days', 30);
        
        return Inertia::render('Admin/Reports/Activity', [
            'userRegistrations' => $this->getUserRegistrationsByPeriod($days),
            'roleChanges' => $this->getRoleChangesByPeriod($days),
            'loginStats' => $this->getLoginStatsByPeriod($days),
            'filters' => ['days' => $days],
        ]);
    }

    /**
     * Exportar reporte de usuarios
     */
    public function exportUsers(Request $request)
    {
        $query = User::with(['role', 'oficinas']);

        // Aplicar filtros
        if ($request->role) {
            $query->where('role_id', $request->role);
        }

        if ($request->status !== null) {
            $query->where('is_active', $request->boolean('status'));
        }

        $users = $query->get();

        $data = $users->map(function ($user) {
            return [
                'ID' => $user->id,
                'Nombre' => $user->name,
                'Email' => $user->email,
                'DNI' => $user->dni,
                'Teléfono' => $user->telefono,
                'Rol' => $user->role->display_name,
                'Estado' => $user->is_active ? 'Activo' : 'Inactivo',
                'Oficinas' => $user->oficinas->pluck('nombre')->join(', '),
                'Fecha de creación' => $user->created_at->format('d/m/Y H:i'),
                'Observaciones' => $user->observaciones,
            ];
        });

        return response()->json([
            'data' => $data,
            'filename' => 'usuarios_' . now()->format('Y-m-d_H-i') . '.csv'
        ]);
    }

    /**
     * Estadísticas generales del sistema
     */
    private function getGeneralStats(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'total_roles' => Role::count(),
            'active_roles' => Role::where('is_active', true)->count(),
            'total_offices' => Oficina::count(),
            'active_offices' => Oficina::habilitadas()->count(),
            'users_with_offices' => User::has('oficinas')->count(),
            'users_without_offices' => User::doesntHave('oficinas')->count(),
        ];
    }

    /**
     * Usuarios agrupados por rol
     */
    private function getUsersByRole(): array
    {
        return Role::withCount('users')
            ->orderBy('access_level', 'desc')
            ->get()
            ->map(function ($role) {
                return [
                    'role' => $role->display_name,
                    'count' => $role->users_count,
                    'color' => $this->getRoleColor($role->name),
                ];
            })->toArray();
    }

    /**
     * Actividad de usuarios
     */
    private function getUsersActivity(): array
    {
        $recentUsers = User::where('created_at', '>=', now()->subDays(30))->count();
        $activeLastMonth = User::where('is_active', true)
            ->where('updated_at', '>=', now()->subDays(30))
            ->count();

        return [
            'recent_registrations' => $recentUsers,
            'active_last_month' => $activeLastMonth,
            'growth_rate' => $this->calculateGrowthRate(),
        ];
    }

    /**
     * Estadísticas de oficinas
     */
    private function getOfficeStats(): array
    {
        return [
            'total_offices' => Oficina::count(),
            'root_offices' => Oficina::whereNull('parent_id')->count(),
            'leaf_offices' => Oficina::hoja()->count(),
            'avg_users_per_office' => round(
                User::has('oficinas')->count() / max(Oficina::has('users')->count(), 1),
                2
            ),
        ];
    }

    /**
     * Actividad reciente del sistema
     */
    private function getRecentActivity(): array
    {
        return [
            'recent_users' => User::with('role')
                ->latest()
                ->take(5)
                ->get(['id', 'name', 'email', 'role_id', 'created_at']),
            'recent_role_changes' => User::with(['role'])
                ->where('updated_at', '>=', now()->subDays(7))
                ->where('updated_at', '!=', DB::raw('created_at'))
                ->latest('updated_at')
                ->take(5)
                ->get(['id', 'name', 'role_id', 'updated_at']),
        ];
    }

    /**
     * Estadísticas para el reporte de usuarios
     */
    private function getUsersReportStats(Request $request): array
    {
        $query = User::query();

        if ($request->role) {
            $query->where('role_id', $request->role);
        }

        if ($request->status !== null) {
            $query->where('is_active', $request->boolean('status'));
        }

        if ($request->has_offices !== null) {
            if ($request->boolean('has_offices')) {
                $query->has('oficinas');
            } else {
                $query->doesntHave('oficinas');
            }
        }

        $total = $query->count();
        $active = $query->where('is_active', true)->count();

        return [
            'filtered_total' => $total,
            'filtered_active' => $active,
            'filtered_inactive' => $total - $active,
        ];
    }

    /**
     * Estadísticas para el reporte de roles
     */
    private function getRolesReportStats(): array
    {
        return [
            'total_roles' => Role::count(),
            'system_roles' => Role::whereIn('name', ['administrador', 'secretario', 'director', 'operador', 'invitado'])->count(),
            'custom_roles' => Role::whereNotIn('name', ['administrador', 'secretario', 'director', 'operador', 'invitado'])->count(),
            'roles_with_users' => Role::has('users')->count(),
        ];
    }

    /**
     * Estadísticas para el reporte de oficinas
     */
    private function getOfficesReportStats(): array
    {
        return [
            'total_offices' => Oficina::count(),
            'enabled_offices' => Oficina::habilitadas()->count(),
            'disabled_offices' => Oficina::deshabilitadas()->count(),
            'offices_with_users' => Oficina::has('users')->count(),
            'max_depth' => $this->getMaxOfficeDepth(),
        ];
    }

    /**
     * Jerarquía de oficinas para visualización
     */
    private function getOfficeHierarchy(): array
    {
        return Oficina::whereNull('parent_id')
            ->with(['descendants'])
            ->get()
            ->map(function ($office) {
                return $this->buildOfficeTree($office);
            })->toArray();
    }

    /**
     * Construir árbol de oficinas
     */
    private function buildOfficeTree(Oficina $office): array
    {
        return [
            'id' => $office->id,
            'name' => $office->nombre,
            'code' => $office->codigo_oficina,
            'users_count' => $office->users()->count(),
            'children' => $office->children->map(function ($child) {
                return $this->buildOfficeTree($child);
            })->toArray(),
        ];
    }

    /**
     * Grupos de permisos
     */
    private function getPermissionGroups(): array
    {
        return [
            'system' => [
                'name' => 'Sistema',
                'permissions' => ['manage_users', 'manage_roles', 'manage_offices', 'manage_config']
            ],
            'notes' => [
                'name' => 'Notas de Pedido',
                'permissions' => ['create_any_nota', 'create_own_notas', 'authorize_notas', 'view_all_notas', 'view_own_notas']
            ],
            'procurement' => [
                'name' => 'Compras',
                'permissions' => ['manage_budgets', 'manage_orders', 'view_budgets', 'view_orders']
            ],
            'reports' => [
                'name' => 'Reportes',
                'permissions' => ['view_all_reports', 'view_own_reports', 'export_reports']
            ],
            'catalog' => [
                'name' => 'Catálogo',
                'permissions' => ['manage_nomenclators', 'view_nomenclators']
            ],
        ];
    }

    /**
     * Registros de usuarios por período
     */
    private function getUserRegistrationsByPeriod(int $days): array
    {
        return User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    /**
     * Cambios de roles por período
     */
    private function getRoleChangesByPeriod(int $days): array
    {
        return User::selectRaw('DATE(updated_at) as date, COUNT(*) as count')
            ->where('updated_at', '>=', now()->subDays($days))
            ->where('updated_at', '!=', DB::raw('created_at'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    /**
     * Estadísticas de login por período (placeholder)
     */
    private function getLoginStatsByPeriod(int $days): array
    {
        // Esto requeriría un sistema de logging de sesiones
        // Por ahora retorna datos de ejemplo
        return [
            'unique_users' => User::where('is_active', true)->count(),
            'total_sessions' => 0, // Implementar con session logging
        ];
    }

    /**
     * Color por rol para gráficos
     */
    private function getRoleColor(string $roleName): string
    {
        $colors = [
            'administrador' => '#dc2626',
            'secretario' => '#ea580c',
            'director' => '#ca8a04',
            'operador' => '#16a34a',
            'invitado' => '#6b7280',
        ];

        return $colors[$roleName] ?? '#3b82f6';
    }

    /**
     * Calcular tasa de crecimiento
     */
    private function calculateGrowthRate(): float
    {
        $currentMonth = User::whereMonth('created_at', now()->month)->count();
        $lastMonth = User::whereMonth('created_at', now()->subMonth()->month)->count();

        if ($lastMonth === 0) {
            return $currentMonth > 0 ? 100 : 0;
        }

        return round((($currentMonth - $lastMonth) / $lastMonth) * 100, 2);
    }

    /**
     * Obtener profundidad máxima de oficinas
     */
    private function getMaxOfficeDepth(): int
    {
        $maxDepth = 0;
        $rootOffices = Oficina::whereNull('parent_id')->get();

        foreach ($rootOffices as $office) {
            $depth = $this->calculateOfficeDepth($office, 0);
            $maxDepth = max($maxDepth, $depth);
        }

        return $maxDepth;
    }

    /**
     * Calcular profundidad de una oficina
     */
    private function calculateOfficeDepth(Oficina $office, int $currentDepth): int
    {
        $maxChildDepth = $currentDepth;

        foreach ($office->children as $child) {
            $childDepth = $this->calculateOfficeDepth($child, $currentDepth + 1);
            $maxChildDepth = max($maxChildDepth, $childDepth);
        }

        return $maxChildDepth;
    }
}