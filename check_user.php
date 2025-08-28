<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::first();

if ($user) {
    echo "Usuario: " . $user->name . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Role ID: " . $user->role_id . "\n";
    
    // Verificar rol tradicional manualmente
    if ($user->role_id) {
        $role = App\Models\Role::find($user->role_id);
        if ($role) {
            echo "Rol tradicional encontrado: " . $role->name . "\n";
        } else {
            echo "Rol tradicional no encontrado con ID: " . $user->role_id . "\n";
        }
    } else {
        echo "No tiene role_id\n";
    }
    
    // Verificar roles Spatie
    $spatieRoles = $user->roles;
    if ($spatieRoles && $spatieRoles->count() > 0) {
        echo "Roles Spatie: " . $spatieRoles->pluck('name')->join(', ') . "\n";
    } else {
        echo "No tiene roles Spatie\n";
    }
    
    // Verificar si necesitamos sincronizar
    if ($user->role_id && (!$spatieRoles || $spatieRoles->count() === 0)) {
        echo "¡NECESITA SINCRONIZACIÓN!\n";
        $role = App\Models\Role::find($user->role_id);
        if ($role) {
            echo "Asignando rol Spatie: " . $role->name . "\n";
            $user->assignRole($role->name);
            echo "Rol asignado correctamente\n";
        }
    }
} else {
    echo "No hay usuarios en la base de datos\n";
}