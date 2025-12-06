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

```php
use App\Helpers\TenancyHelper;

$tenant = TenancyHelper::currentTenant();        // Current tenant
$view = TenancyHelper::currentView();            // Current view
$isAdmin = TenancyHelper::isViewCode('admin');   // Check view code
TenancyHelper::view('home', $data);              // Render tenant view
```

## Database

- **Central DB** (`database.sqlite`): Stores `tenants`, `tenant_views`, `domains`
- **Tenant DBs**: Separate database per tenant (managed by stancl/tenancy)
