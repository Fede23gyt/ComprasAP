# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel-based procurement management system (ComprasAP) for municipal operations. The application manages purchasing workflows including supply items, purchase requests, budgets, and organizational structure.

**Tech Stack:**
- Backend: Laravel 12 (PHP 8.2+), SQLite database
- Frontend: Vue.js 3 + Inertia.js, Tailwind CSS
- Build Tools: Vite, Laravel Pint (code style)
- Testing: PHPUnit
- Key Packages: Spatie Laravel Permission, Laravel Adjacency List, DomPDF

## Development Commands

### Development Server
```bash
# Start full development environment (server + queue + logs + vite)
composer dev

# Individual services
php artisan serve                # Laravel server
npm run dev                     # Vite dev server
php artisan queue:listen        # Queue worker
php artisan pail               # Log viewer
```

### Build and Assets
```bash
npm run build                  # Build frontend assets (includes SSR)
npm run dev                   # Development build with hot reload
```

### Testing and Quality
```bash
composer test                 # Run PHPUnit tests
php artisan test             # Alternative test command
./vendor/bin/pint           # Laravel Pint code formatting
```

### Database
```bash
php artisan migrate          # Run migrations
php artisan db:seed         # Run seeders
php artisan migrate:fresh --seed  # Fresh install with data
```

## Architecture Overview

### Core Models & Relationships

**User Management:**
- `User` model with role-based permissions via `Role` model
- Many-to-many relationship with `Oficina` (offices) via pivot table
- Custom permission system with role-based and office-specific authorization

**Office Structure:**
- `Oficina` model uses Laravel Adjacency List for hierarchical organization
- Self-referential parent-child relationships for organizational tree
- Complex authorization: users can have different permissions per office

**Supply Management:**
- `Insumo` (supplies) with pricing, classification, and status tracking
- Relationship with `ClasifEconomica` for economic classification
- Support for CSV imports via `ImportInsumosCsv` command

**Workflow Models:**
- `Memo`, `TipoNota`, `TipoCompra`, `TipoInsumo` for various document types
- Complex purchasing workflow (requests → budgets → purchase orders)

### Authentication & Authorization

The system implements a sophisticated authorization model:

1. **Role-based permissions** via `Role` model with methods like `isAdmin()`, `isSupervisor()`
2. **Office-specific permissions** where users can have different access levels per office
3. **Middleware stack**: `auth:sanctum`, `admin`, `role:`, and custom permission middleware
4. **Route protection** with granular access control for different user roles

### Frontend Architecture

- **Inertia.js** bridges Laravel backend with Vue.js frontend
- **Component structure**: Layouts (App, Authenticated, Guest), shared Components, and page-specific Views
- **State management** through Vue composables (`useDarkMode`, `useConfirm`)
- **Modal system** for CRUD operations with reusable form components
- **Tree components** for hierarchical office management

### Key Business Logic

The application handles municipal procurement with:
- Hierarchical office structure with inheritance of permissions
- Multi-stage approval workflows
- Supply catalog with pricing and availability tracking
- Role-based access to different system functions
- Document generation (PDF exports via DomPDF)

## Important Notes

- Database uses SQLite for development
- SSR (Server-Side Rendering) is configured with Vite
- Dark mode support implemented via Tailwind CSS classes
- Custom middleware for logging office changes (`LogOficinaChanges`)
- Complex authorization logic in User model methods for office-specific permissions