# Sistema de Gestión Administrativa - ComprasAP

Este documento describe el sistema completo de gestión de usuarios, roles y reportes administrativos implementado.

## 🏗️ Arquitectura del Sistema

### Modelos Principales

#### User Model (`app/Models/User.php`)
- **Funcionalidades principales:**
  - Relación many-to-many con `Oficina` mediante tabla pivot
  - Relación con `Role` para manejo de permisos
  - Métodos de autorización: `canManageConfig()`, `isSupervisor()`, `isAdmin()`
  - Gestión de oficinas: `oficinaPrincipal()`, `oficinasQueAutoriza()`
  - Scopes: `active()`, `byRole()`

#### Role Model (`app/Models/Role.php`)  
- **Funcionalidades principales:**
  - Sistema de permisos basado en array JSON
  - Métodos de verificación: `hasPermission()`, `hasAnyPermission()`, `isSystemRole()`
  - Categorización de roles: `isAdmin()`, `isSupervisor()`, `canManageUsers()`
  - Atributos calculados: `getPermissionsWithDetailsAttribute()`, `getAccessLevelAttribute()`

### Controladores

#### UserController (`app/Http/Controllers/UserController.php`)
- CRUD completo de usuarios con validaciones
- Gestión de oficinas asignadas y oficina principal
- Toggle de estado y autorización por oficinas
- Filtros avanzados y paginación
- API endpoints para selects

#### RoleController (`app/Http/Controllers/RoleController.php`)
- CRUD de roles con sistema de permisos
- Protección de roles del sistema
- Validaciones de integridad (usuarios asignados)
- Gestión de permisos agrupados

#### Admin/ReportController (`app/Http/Controllers/Admin/ReportController.php`)
- Dashboard administrativo con métricas clave
- Reportes detallados por usuarios, roles y oficinas
- Análisis de actividad y crecimiento
- Exportación de datos en CSV
- Estadísticas en tiempo real

## 🚀 Rutas del Sistema

### Rutas de Administración (`/admin`)
Protegidas por middleware `admin` - Solo usuarios administradores y supervisores

#### Gestión de Usuarios
```
GET    /admin/usuarios                    # Lista de usuarios
GET    /admin/usuarios/create             # Formulario nuevo usuario  
POST   /admin/usuarios                    # Crear usuario
GET    /admin/usuarios/{id}               # Ver detalles usuario
GET    /admin/usuarios/{id}/edit          # Editar usuario
PUT    /admin/usuarios/{id}               # Actualizar usuario
DELETE /admin/usuarios/{id}               # Eliminar usuario
PATCH  /admin/usuarios/{id}/toggle-status # Cambiar estado
```

#### Gestión de Roles
```
GET    /admin/roles                       # Lista de roles
GET    /admin/roles/create                # Formulario nuevo rol
POST   /admin/roles                       # Crear rol
GET    /admin/roles/{id}                  # Ver detalles rol
GET    /admin/roles/{id}/edit             # Editar rol
PUT    /admin/roles/{id}                  # Actualizar rol
DELETE /admin/roles/{id}                  # Eliminar rol
PATCH  /admin/roles/{id}/toggle-status    # Cambiar estado
```

#### Reportes Administrativos
```
GET    /admin/reports                     # Dashboard principal
GET    /admin/reports/users               # Reporte usuarios
GET    /admin/reports/roles               # Reporte roles  
GET    /admin/reports/offices             # Reporte oficinas
GET    /admin/reports/activity            # Reporte actividad
GET    /admin/reports/export/users        # Exportar usuarios
```

## 🎨 Interfaces Vue.js

### Páginas Principales

#### `/resources/js/Pages/Admin/Users/`
- **Index.vue**: Lista de usuarios con filtros, búsqueda y paginación
- **Create.vue**: Formulario de creación con validaciones en tiempo real
- **Edit.vue**: Formulario de edición con gestión de contraseña opcional
- **Show.vue**: Vista detallada con información completa del usuario

#### `/resources/js/Pages/Admin/Roles/`
- **Index.vue**: Grid de roles con estadísticas y acciones rápidas
- (Pendientes: Create.vue, Edit.vue, Show.vue)

#### `/resources/js/Pages/Admin/Reports/`
- **Index.vue**: Dashboard con métricas, gráficos y actividad reciente

### Componentes Reutilizables

#### `/resources/js/Components/Admin/`
- **UserRoleModal.vue**: Modal para cambio rápido de rol
- **PermissionCheckbox.vue**: Selector de permisos agrupados
- **StatsCard.vue**: Tarjetas de estadísticas con gráficos

## 🔐 Sistema de Permisos

### Roles del Sistema
- **administrador**: Acceso completo al sistema
- **secretario**: Gestión administrativa avanzada  
- **director**: Supervisión y reportes
- **operador**: Operaciones básicas
- **invitado**: Solo lectura

### Permisos Disponibles
```php
// Sistema
'manage_users' => 'Gestionar usuarios'
'manage_roles' => 'Gestionar roles'  
'manage_offices' => 'Gestionar oficinas'
'manage_config' => 'Gestionar configuración'

// Notas de Pedido
'create_any_nota' => 'Crear notas para cualquier oficina'
'authorize_notas' => 'Autorizar notas de pedido'
'view_all_notas' => 'Ver todas las notas'

// Reportes  
'view_all_reports' => 'Ver todos los reportes'
'export_reports' => 'Exportar reportes'

// Y más...
```

## 📊 Características del Dashboard

### Métricas Principales
- Total de usuarios activos/inactivos
- Distribución por roles con colores
- Estadísticas de oficinas y jerarquías
- Actividad reciente del sistema
- Tasas de crecimiento

### Filtros Avanzados
- Búsqueda por nombre, email, DNI
- Filtros por rol, estado, oficina
- Ordenamiento personalizable
- Paginación optimizada

### Reportes y Exportación
- Datos en tiempo real
- Exportación CSV con filtros aplicados
- Gráficos de distribución
- Análisis de tendencias

## 🛡️ Seguridad Implementada

### Middleware de Autorización
- **AdminMiddleware**: Verifica permisos administrativos
- **RoleMiddleware**: Control basado en roles
- Verificación de usuarios activos
- Protección contra auto-eliminación

### Validaciones
- Validación de datos en backend y frontend
- Protección de roles del sistema
- Integridad referencial (usuarios con roles asignados)
- Sanitización de inputs

### Auditoría
- Logs de cambios en usuarios y roles
- Tracking de actividad administrativa
- Historial de modificaciones

## 🚀 Comandos de Desarrollo

### Testing
```bash
composer test                # Ejecutar tests
php artisan test            # Tests de Laravel
```

### Migraciones y Seeders
```bash
php artisan migrate         # Ejecutar migraciones
php artisan db:seed         # Sembrar datos base
php artisan migrate:fresh --seed  # Reset completo
```

### Assets
```bash
npm run dev                 # Desarrollo con HMR
npm run build              # Build de producción
```

## 📝 Próximas Mejoras

1. **Vistas faltantes de Roles**
   - Create.vue, Edit.vue, Show.vue para roles

2. **Funcionalidades adicionales**
   - Importación masiva de usuarios
   - Notificaciones por email
   - Logs de auditoría más detallados
   - API REST completa

3. **UI/UX**
   - Dark mode completo
   - Gráficos interactivos con Chart.js
   - Drag & drop para asignación de permisos

4. **Performance**  
   - Cache de roles y permisos
   - Búsqueda con Elasticsearch
   - Lazy loading de componentes

Este sistema proporciona una base sólida para la gestión administrativa del sistema ComprasAP, con énfasis en seguridad, usabilidad y escalabilidad.