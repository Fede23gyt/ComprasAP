<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the dashboard based on user role
     */
    public function index(): Response
    {
        $user = auth()->user();
        
        // Get role name - handle both Spatie roles and traditional role relationship
        $roleName = null;
        
        try {
            // First try Spatie roles
            if ($user->roles && $user->roles->isNotEmpty()) {
                $roleName = $user->roles->first()->name;
            }
            // Then try traditional role relationship
            elseif ($user->role && is_object($user->role)) {
                $roleName = $user->role->name;
            }
            // Try role_id if exists
            elseif ($user->role_id) {
                $role = \App\Models\Role::find($user->role_id);
                $roleName = $role ? $role->name : null;
            }
        } catch (\Exception $e) {
            \Log::warning('Error getting user role: ' . $e->getMessage());
        }
        
        // Fallback to default if still null
        if (!$roleName) {
            $roleName = 'invitado';
        }
        
        // Redirect to specific dashboard based on role
        switch ($roleName) {
            case 'administrador':
            case 'secretario':
            case 'director':
                return $this->adminDashboard();
                
            case 'operador':
                return $this->operatorDashboard();
                
            case 'invitado':
                return $this->guestDashboard();
                
            default:
                return $this->defaultDashboard();
        }
    }
    
    /**
     * Admin/Supervisor Dashboard
     */
    private function adminDashboard(): Response
    {
        try {
            $stats = [
                'total_users' => \App\Models\User::count(),
                'active_users' => \App\Models\User::where('is_active', true)->count(),
                'users_growth' => $this->calculateUserGrowth(),
                'pending_notes' => $this->getPendingNotesCount(),
                'notes_growth' => $this->calculateNotesGrowth(),
                'active_offices' => $this->getActiveOfficesCount(),
                'monthly_budget' => $this->getMonthlyBudget(),
                'budget_variance' => $this->getBudgetVariance(),
            ];
            
            $recentActivity = $this->getRecentActivity();
            $usersByRole = $this->getUsersByRole();
            $systemAlerts = $this->getSystemAlerts();
            $metrics = $this->getSystemMetrics();
            
            return Inertia::render('Dashboard/AdminDashboard', [
                'stats' => $stats,
                'recentActivity' => $recentActivity,
                'usersByRole' => $usersByRole,
                'systemAlerts' => $systemAlerts,
                'metrics' => $metrics,
            ]);
        } catch (\Exception $e) {
            // Fallback en caso de error
            return Inertia::render('Dashboard/AdminDashboard', [
                'stats' => [
                    'total_users' => 0,
                    'active_users' => 0,
                    'users_growth' => 0,
                    'pending_notes' => 0,
                    'notes_growth' => 0,
                    'active_offices' => 0,
                    'monthly_budget' => 0,
                    'budget_variance' => 0,
                ],
                'recentActivity' => [],
                'usersByRole' => [],
                'systemAlerts' => [],
                'metrics' => [
                    'response_time' => 200,
                    'uptime' => 99,
                    'memory_usage' => 45,
                ],
            ]);
        }
    }
    
    /**
     * Operator Dashboard
     */
    private function operatorDashboard(): Response
    {
        $user = auth()->user();
        
        $stats = [
            'my_notes' => $this->getUserNotesCount($user->id),
            'processing_notes' => $this->getUserNotesCount($user->id, 'pending'),
            'approved_notes' => $this->getUserNotesCount($user->id, 'approved'),
            'monthly_notes' => $this->getUserMonthlyNotes($user->id),
            'approval_rate' => $this->getUserApprovalRate($user->id),
            'monthly_growth' => $this->getUserMonthlyGrowth($user->id),
        ];
        
        $recentNotes = $this->getUserRecentNotes($user->id);
        $notifications = $this->getUserNotifications($user->id);
        
        return Inertia::render('Dashboard/OperatorDashboard', [
            'user' => $user,
            'stats' => $stats,
            'recentNotes' => $recentNotes,
            'notifications' => $notifications,
        ]);
    }
    
    /**
     * Guest Dashboard
     */
    private function guestDashboard(): Response
    {
        $user = auth()->user();
        
        return Inertia::render('Dashboard/GuestDashboard', [
            'user' => $user,
            'canViewNotes' => false, // Guests typically can't view notes
            'availableReports' => $this->getAvailableReports($user),
        ]);
    }
    
    /**
     * Default Dashboard
     */
    private function defaultDashboard(): Response
    {
        return $this->operatorDashboard();
    }
    
    /**
     * Calculate user growth percentage
     */
    private function calculateUserGrowth(): float
    {
        $currentMonth = \App\Models\User::whereMonth('created_at', now()->month)->count();
        $lastMonth = \App\Models\User::whereMonth('created_at', now()->subMonth()->month)->count();
        
        if ($lastMonth === 0) {
            return $currentMonth > 0 ? 100 : 0;
        }
        
        return round((($currentMonth - $lastMonth) / $lastMonth) * 100, 1);
    }
    
    /**
     * Get pending notes count (placeholder)
     */
    private function getPendingNotesCount(): int
    {
        // This would query the actual notes table when implemented
        return rand(5, 25);
    }
    
    /**
     * Calculate notes growth (placeholder)
     */
    private function calculateNotesGrowth(): float
    {
        // Placeholder calculation
        return rand(-10, 25) / 10;
    }
    
    /**
     * Get monthly budget (placeholder)
     */
    private function getMonthlyBudget(): float
    {
        // This would come from actual budget calculations
        return 750000.50;
    }
    
    /**
     * Get budget variance (placeholder)
     */
    private function getBudgetVariance(): float
    {
        return rand(-15, 20) / 10;
    }
    
    /**
     * Get active offices count (safe method)
     */
    private function getActiveOfficesCount(): int
    {
        try {
            return \App\Models\Oficina::where('estado', 'Habilitado')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get recent system activity
     */
    private function getRecentActivity(): array
    {
        // This would query actual activity logs
        return [
            [
                'id' => 1,
                'type' => 'user_created',
                'description' => 'Nuevo usuario creado: María González',
                'user' => 'Admin',
                'created_at' => now()->subHours(2),
            ],
            [
                'id' => 2,
                'type' => 'note_approved',
                'description' => 'Nota de pedido #2024-001 aprobada',
                'user' => 'Director',
                'created_at' => now()->subHours(4),
            ],
            [
                'id' => 3,
                'type' => 'role_assigned',
                'description' => 'Rol "Operador" asignado a Juan Pérez',
                'user' => 'Admin',
                'created_at' => now()->subHours(6),
            ],
        ];
    }
    
    /**
     * Get users grouped by role
     */
    private function getUsersByRole(): array
    {
        try {
            // Método más seguro sin usar withCount que puede causar problemas
            $roles = \App\Models\Role::all();
            
            return $roles->map(function ($role) {
                // Contar usuarios usando consultas separadas para evitar problemas de relación
                $spatieUsersCount = 0;
                $traditionalUsersCount = 0;
                
                try {
                    // Intentar contar usuarios de Spatie
                    if (method_exists($role, 'users')) {
                        $spatieUsersCount = $role->users()->count();
                    }
                } catch (\Exception $e) {
                    // Silenciar error si la relación no existe
                }
                
                try {
                    // Intentar contar usuarios tradicionales
                    if (method_exists($role, 'oldUsers')) {
                        $traditionalUsersCount = $role->oldUsers()->count();
                    }
                } catch (\Exception $e) {
                    // Silenciar error si la relación no existe
                }
                
                $totalUsers = $spatieUsersCount + $traditionalUsersCount;
                
                return [
                    'name' => $role->display_name ?? $role->name,
                    'count' => $totalUsers,
                    'color' => $this->getRoleColor($role->name),
                ];
            })->toArray();
            
        } catch (\Exception $e) {
            // Fallback en caso de error
            return [
                [
                    'name' => 'Sin datos',
                    'count' => 0,
                    'color' => '#6b7280',
                ]
            ];
        }
    }
    
    /**
     * Get system alerts
     */
    private function getSystemAlerts(): array
    {
        $alerts = [];
        
        try {
            // Check for users without offices - método más seguro
            $usersWithoutOffices = \App\Models\User::where('is_active', true)
                ->whereDoesntHave('oficinas')
                ->count();
            
            if ($usersWithoutOffices > 0) {
                $alerts[] = [
                    'id' => 'users_no_offices',
                    'type' => 'warning',
                    'title' => 'Usuarios sin oficinas',
                    'description' => "{$usersWithoutOffices} usuarios activos no tienen oficinas asignadas",
                ];
            }
        } catch (\Exception $e) {
            // Silenciar error si hay problemas con la relación
        }
        
        try {
            // Check for inactive roles with users - método más seguro
            $inactiveRoles = \App\Models\Role::where('is_active', false)->get();
            $rolesWithUsers = 0;
            
            foreach ($inactiveRoles as $role) {
                $hasUsers = false;
                
                try {
                    if (method_exists($role, 'users') && $role->users()->exists()) {
                        $hasUsers = true;
                    }
                } catch (\Exception $e) {
                    // Continuar si hay error
                }
                
                try {
                    if (!$hasUsers && method_exists($role, 'oldUsers') && $role->oldUsers()->exists()) {
                        $hasUsers = true;
                    }
                } catch (\Exception $e) {
                    // Continuar si hay error
                }
                
                if ($hasUsers) {
                    $rolesWithUsers++;
                }
            }
            
            if ($rolesWithUsers > 0) {
                $alerts[] = [
                    'id' => 'inactive_roles_users',
                    'type' => 'info',
                    'title' => 'Roles inactivos con usuarios',
                    'description' => "{$rolesWithUsers} roles inactivos tienen usuarios asignados",
                ];
            }
        } catch (\Exception $e) {
            // Silenciar error si hay problemas con las relaciones
        }
        
        return $alerts;
    }
    
    /**
     * Get system performance metrics
     */
    private function getSystemMetrics(): array
    {
        return [
            'response_time' => rand(150, 300), // milliseconds
            'uptime' => rand(95, 100), // percentage
            'memory_usage' => rand(30, 70), // percentage
        ];
    }
    
    /**
     * Get user notes count
     */
    private function getUserNotesCount(int $userId, string $status = null): int
    {
        // Placeholder - would query actual notes table
        $base = rand(10, 50);
        
        if ($status === 'pending') return intval($base * 0.3);
        if ($status === 'approved') return intval($base * 0.6);
        
        return $base;
    }
    
    /**
     * Get user monthly notes
     */
    private function getUserMonthlyNotes(int $userId): int
    {
        return rand(5, 20);
    }
    
    /**
     * Get user approval rate
     */
    private function getUserApprovalRate(int $userId): float
    {
        return rand(70, 95) / 10;
    }
    
    /**
     * Get user monthly growth
     */
    private function getUserMonthlyGrowth(int $userId): float
    {
        return rand(-20, 30) / 10;
    }
    
    /**
     * Get user recent notes
     */
    private function getUserRecentNotes(int $userId): array
    {
        // Placeholder data - would query actual notes
        return [
            [
                'id' => 1,
                'numero' => '2025-001',
                'status' => 'pending',
                'oficina_destino' => ['nombre' => 'Secretaría General'],
                'items_count' => 5,
                'created_at' => now()->subDays(1),
            ],
            [
                'id' => 2,
                'numero' => '2025-002',
                'status' => 'approved',
                'oficina_destino' => ['nombre' => 'Dirección de Obras'],
                'items_count' => 3,
                'created_at' => now()->subDays(3),
            ],
        ];
    }
    
    /**
     * Get user notifications
     */
    private function getUserNotifications(int $userId): array
    {
        return [
            [
                'id' => 1,
                'type' => 'success',
                'title' => 'Nota Aprobada',
                'message' => 'Tu nota de pedido #2025-001 ha sido aprobada',
                'created_at' => now()->subHours(2),
            ],
            [
                'id' => 2,
                'type' => 'info',
                'title' => 'Recordatorio',
                'message' => 'Tienes una nota pendiente de completar',
                'created_at' => now()->subHours(6),
            ],
        ];
    }
    
    /**
     * Get available reports for user
     */
    private function getAvailableReports($user): array
    {
        // Return limited reports for guests
        return [
            'nomenclators' => true,
            'public_reports' => true,
            'detailed_reports' => false,
        ];
    }
    
    /**
     * Get role color for charts
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
}