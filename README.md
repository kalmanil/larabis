# Larabis - Two-Level Multi-Tenancy

Larabis extends Laravel with a two-level tenancy system using `stancl/tenancy`:

- **Level 1: Tenants** - Separate databases per tenant
- **Level 2: Views** - Multiple views per tenant (e.g., `default`, `admin`, `api`)

## Quick Start

```bash
# Create tenant with views
php artisan tenant:create lapp --domains=lapp.test --domains=admin.lapp.test

# Start dev servers
cd lapp.test && bash run-dev.sh
cd admin.lapp.test && bash run-dev.sh
```

## Documentation

- [Quick Start](QUICK_START.md) - Get up and running
- [Tenant Structure](TENANT_STRUCTURE.md) - View organization
- [View Management](VIEW_MANAGEMENT.md) - Managing tenant views
- [Tenancy Setup](TENANCY_SETUP.md) - Architecture and configuration
- [View Structure](VIEW_STRUCTURE.md) - View organization and usage
- [Code Architecture](ARCHITECTURE.md) - Hybrid structure with traits
- [Tenant View Logic](TENANT_VIEW_LOGIC.md) - Tenant-specific view logic patterns

## Key Features

- Domain-based routing with automatic tenant/view detection
- Tenant-specific views: `tenants/{tenant_id}/resources/views/tenants/{tenant_id}/{code}/`
- Tenant-specific code: `tenants/{tenant_id}/app/Features/Pages/Tenants/{tenant_id}/`
- Automatic folder creation when adding views
- Complete helper class with context checking methods
- Support for multiple views per tenant (all equal, no hierarchy)
- **Three-level trait hierarchy**: Base → View → Tenant → Tenant-View
- **Tenant-specific view logic**: Admin panels can vary by tenant while sharing base logic

## Helper Methods

The `TenancyHelper` class provides the following methods:

```php
use App\Helpers\TenancyHelper;

// Get current context
TenancyHelper::currentTenant()      // Get current tenant
TenancyHelper::currentView()        // Get current view

// Check context
TenancyHelper::isTenantContext()    // Check if in tenant context
TenancyHelper::isViewCode('admin')  // Check if current view matches code
TenancyHelper::isAdminView()        // Check if current view is admin

// View operations
TenancyHelper::view('home', $data)  // Render tenant-specific view
TenancyHelper::getViewPath('home')  // Get view path string

// ⚠️ IMPORTANT: Only pass the view name (e.g., 'home', 'login')
// The helper automatically constructs: tenants.{tenant_id}.{code}.{view_name}
// DO NOT include view code in the name (e.g., 'admin.login' is wrong)
```

## Requirements

- PHP ^8.2
- Laravel ^12.0
- stancl/tenancy ^3.9
