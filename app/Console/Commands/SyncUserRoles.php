<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;

class SyncUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:sync-roles {--force : Force sync even if user already has Spatie roles}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize user roles between traditional role_id and Spatie Permission roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');
        
        $this->info('Iniciando sincronización de roles de usuario...');
        
        // Obtener todos los usuarios con role_id
        $users = User::whereNotNull('role_id')->with(['role', 'roles'])->get();
        
        $this->info("Encontrados {$users->count()} usuarios para procesar.");
        
        $progressBar = $this->output->createProgressBar($users->count());
        $progressBar->start();
        
        $synced = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ($users as $user) {
            try {
                // Si el usuario ya tiene roles de Spatie y no se fuerza, saltear
                if (!$force && $user->roles->isNotEmpty()) {
                    $skipped++;
                    $progressBar->advance();
                    continue;
                }
                
                // Obtener el rol tradicional
                if (!$user->role) {
                    $this->error("\nUsuario {$user->email} no tiene rol válido");
                    $errors++;
                    $progressBar->advance();
                    continue;
                }
                
                // Sincronizar con Spatie
                $user->syncRoles([$user->role->name]);
                $synced++;
                
            } catch (\Exception $e) {
                $this->error("\nError sincronizando usuario {$user->email}: " . $e->getMessage());
                $errors++;
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        
        $this->newLine(2);
        $this->info("Sincronización completada:");
        $this->info("  • Usuarios sincronizados: {$synced}");
        $this->info("  • Usuarios omitidos: {$skipped}");
        $this->info("  • Errores: {$errors}");
        
        // Mostrar resumen de roles
        $this->newLine();
        $this->info("Resumen de roles por usuario:");
        $this->table(
            ['Usuario', 'Email', 'Rol Tradicional', 'Roles Spatie'],
            User::with(['role', 'roles'])->get()->map(function ($user) {
                return [
                    $user->name,
                    $user->email,
                    $user->role?->name ?? 'Sin rol',
                    $user->roles->pluck('name')->join(', ') ?: 'Sin roles',
                ];
            })->toArray()
        );
        
        return 0;
    }
}