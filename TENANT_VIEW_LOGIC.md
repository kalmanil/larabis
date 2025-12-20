# Tenant-Specific View Logic

## Overview

Larabis supports **tenant-specific view logic** - allowing view implementations (like admin panels) to vary slightly between tenants while sharing most of the base logic.

## Problem

Sometimes you need:
- **Mostly same**: Admin panel logic is 90% the same across tenants
- **Slightly different**: Each tenant needs small customizations (theme, widgets, features)

## Solution: Three-Level Trait Hierarchy

```
Base Trait (Shared)
    ↓
View Trait (Shared across tenants)
    ↓
Tenant Trait (All views for tenant)
    ↓
Tenant-View Trait (Tenant-specific view logic)
```

## Directory Structure

```
app/Features/Pages/
├── Views/admin/Traits/PageLogic.php              # Base admin (shared)
└── Tenants/
    └── lapp/
        ├── Traits/PageLogic.php                   # Lapp (all views)
        └── Views/admin/Traits/PageLogic.php       # Lapp's admin (override)
```

## Priority Order

When multiple traits define the same method:

1. **Tenant-View Specific** (highest) - `Tenants/{id}/Views/{code}/Traits/`
2. **Base View** (high) - `Views/{code}/Traits/`
3. **Tenant Specific** (medium) - `Tenants/{id}/Traits/`
4. **Base/Shared** (lowest) - `Shared/Traits/Base/`

## Example: Admin Panel with Tenant Variations

### Base Admin Trait (Shared - All Tenants)

```php
// app/Features/Pages/Views/admin/Traits/PageLogic.php
namespace App\Features\Pages\Views\admin\Traits;

trait PageLogic
{
    protected $adminConfig = [
        'theme' => 'default',
        'show_sidebar' => true,
    ];
    
    public function getAdminDashboardData(): array
    {
        return ['stats' => []];
    }
}
```

### Lapp's Admin Trait (Tenant-Specific Override)

```php
// app/Features/Pages/Tenants/lapp/Views/admin/Traits/PageLogic.php
namespace App\Features\Pages\Tenants\lapp\Views\admin\Traits;

use App\Features\Pages\Views\admin\Traits\PageLogic as BaseAdminPageLogic;

trait PageLogic
{
    use BaseAdminPageLogic {
        BaseAdminPageLogic::getAdminDashboardData as getBaseAdminDashboardData;
    }
    
    public function traitConstructPageLogic()
    {
        // Override base admin config
        $this->adminConfig = array_merge($this->adminConfig, [
            'theme' => 'lapp-blue',  // Lapp-specific theme
            'custom_features' => true,
        ]);
    }
    
    // Override base method with Lapp-specific logic
    public function getAdminDashboardData(): array
    {
        $base = $this->getBaseAdminDashboardData();
        return array_merge($base, [
            'stats' => ['lapp_metric' => 100],
        ]);
    }
}
```

### Controller Using All Levels

```php
// app/Features/Pages/Controllers/PageController.php
use App\Features\Pages\Tenants\lapp\Traits\PageLogic as LappPageLogic;
use App\Features\Pages\Views\admin\Traits\PageLogic as BaseAdminPageLogic;
use App\Features\Pages\Tenants\lapp\Views\admin\Traits\PageLogic as LappAdminPageLogic;

class PageController extends Controller
{
    use LappPageLogic, BaseAdminPageLogic, LappAdminPageLogic {
        // Tenant-specific view takes highest priority
        LappAdminPageLogic::getAdminDashboardData insteadof BaseAdminPageLogic;
        LappAdminPageLogic::getPageData insteadof LappPageLogic, BaseAdminPageLogic;
    }
}
```

## Use Cases

### When to Use Tenant-Specific View Logic

✅ **Use when:**
- Admin panel needs tenant-specific widgets
- Different themes per tenant for same view
- Tenant-specific features in admin
- Custom branding per tenant in admin
- Different dashboard metrics per tenant

❌ **Don't use when:**
- Logic is completely different (use separate controllers)
- Only data differs (use tenant-specific data sources)
- All tenants need exact same logic (use base view trait only)

## Current Implementation: Lapp Admin

### Base Admin (`Views/admin/Traits/PageLogic.php`)
- Shared admin configuration
- Default theme and permissions
- Base dashboard data structure

### Lapp Admin (`Tenants/lapp/Views/admin/Traits/PageLogic.php`)
- Lapp-specific theme: `lapp-blue`
- Custom widgets: sales_overview, customer_metrics, revenue_chart
- Advanced features enabled
- Custom branding

### Result
- **Base admin logic**: Shared across all tenants
- **Lapp admin logic**: Overrides base with Lapp-specific customizations
- **Other tenants**: Use base admin (or create their own overrides)

## Adding Tenant-Specific View Logic

### Step 1: Create Base View Trait
```php
// app/Features/Pages/Views/admin/Traits/PageLogic.php
// Base admin logic (shared)
```

### Step 2: Create Tenant-View Trait
```php
// app/Features/Pages/Tenants/{tenant_id}/Views/admin/Traits/PageLogic.php
namespace App\Features\Pages\Tenants\{tenant_id}\Views\admin\Traits;

use App\Features\Pages\Views\admin\Traits\PageLogic as BaseAdminPageLogic;

trait PageLogic
{
    use BaseAdminPageLogic {
        BaseAdminPageLogic::getAdminDashboardData as getBaseAdminDashboardData;
    }
    
    // Override base methods with tenant-specific logic
}
```

### Step 3: Use in Controller
```php
use App\Features\Pages\Tenants\{tenant_id}\Views\admin\Traits\PageLogic as TenantAdminPageLogic;

class PageController extends Controller
{
    use BaseAdminPageLogic, TenantAdminPageLogic {
        TenantAdminPageLogic::getAdminDashboardData insteadof BaseAdminPageLogic;
    }
}
```

## Benefits

1. **Code Reuse**: Base logic shared, only differences overridden
2. **Maintainability**: Update base logic, all tenants benefit
3. **Flexibility**: Each tenant can customize as needed
4. **Scalability**: Add new tenants without affecting existing ones
5. **Clear Structure**: Easy to find tenant-specific view logic

## Summary

- ✅ Base view logic in `Views/{code}/Traits/`
- ✅ Tenant-specific view overrides in `Tenants/{id}/Views/{code}/Traits/`
- ✅ Priority: Tenant-View > Base-View > Tenant > Base
- ✅ Mostly same, slightly different pattern supported
- ✅ Easy to maintain and extend

