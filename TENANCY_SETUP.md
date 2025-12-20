# Tenancy Setup

## Architecture

- **Tenants**: Separate databases per tenant (managed by `stancl/tenancy`)
- **Views**: Multiple views per tenant (stored in `tenant_views` table)
- **Domains**: Each view handles a specific domain

## Domain Configuration

Each domain folder (`lapp.test/`, `admin.lapp.test/`) contains:

```php
// config.php
return [
    'tenant_id' => 'lapp',
    'code' => 'default', // or 'admin', 'api', etc.
];
```

## Request Flow

1. Request hits domain (e.g., `admin.lapp.test:8001`)
2. Domain folder's `index.php` loads config
3. `TenantViewMiddleware` initializes tenant and view context
4. Routes use `TenancyHelper` to access current tenant/view
5. Views resolve to: `tenants.{tenant_id}.{code}.{view_name}`

## Helper Methods

The `TenancyHelper` class provides comprehensive methods for working with tenants and views:

```php
use App\Helpers\TenancyHelper;

// Get current context
$tenant = TenancyHelper::currentTenant();        // Current tenant (Tenant model)
$view = TenancyHelper::currentView();            // Current view (TenantView model)

// Check context
$inContext = TenancyHelper::isTenantContext();   // Check if in tenant context
$isAdmin = TenancyHelper::isAdminView();        // Check if current view is admin
$isApi = TenancyHelper::isViewCode('api');       // Check if current view matches code

// View operations
TenancyHelper::view('home', $data);              // Render tenant-specific view
$path = TenancyHelper::getViewPath('home');      // Get view path string

// ⚠️ IMPORTANT: Only pass the view name (e.g., 'home', 'login', 'dashboard')
// The helper automatically constructs: tenants.{tenant_id}.{code}.{view_name}
// Example: For admin view 'login.blade.php', use:
//   TenancyHelper::view('login', $data);  // ✅ Correct
//   NOT: TenancyHelper::view('admin.login', $data);  // ❌ Wrong - creates duplicate path
```

### Helper Method Details

- **`currentTenant()`**: Returns the current `Tenant` model or `null`
- **`currentView()`**: Returns the current `TenantView` model or `null`
- **`isTenantContext()`**: Returns `true` if tenancy is initialized
- **`isViewCode($code)`**: Returns `true` if current view code matches
- **`isAdminView()`**: Shorthand for `isViewCode('admin')`
- **`view($viewName, $data)`**: Renders tenant-specific view, throws exception if not found
- **`getViewPath($viewName)`**: Returns view path string without rendering

## Database

- **Central DB** (`database.sqlite`): Stores `tenants`, `tenant_views`, `domains`
- **Tenant DBs**: Separate database per tenant (managed by stancl/tenancy)
