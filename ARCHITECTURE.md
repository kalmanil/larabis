# Larabis Architecture

## Overview

Larabis uses a **hybrid feature-based architecture** with trait composition for organizing tenant-specific and view-specific code. This structure provides maximum flexibility while maintaining clean separation of concerns.

## Directory Structure

```
app/
├── Core/                              # Framework core (shared)
│   ├── Helpers/
│   │   └── TenancyHelper.php
│   ├── Models/
│   │   ├── Tenant.php
│   │   └── TenantView.php
│   └── Traits/
│       └── ConstructableTrait.php     # Enables constructors in traits
│
├── Features/                          # Feature-based organization
│   └── Pages/                        # Example: Pages feature
│       ├── Controllers/
│       │   └── PageController.php
│       └── Views/                    # View-specific code (shared)
│           ├── default/
│           │   └── Traits/
│           │       └── PageLogic.php
│           └── admin/
│               └── Traits/
│                   └── PageLogic.php  # Base admin logic (all tenants)
│
└── Shared/                            # Shared across all features
    └── Traits/
        └── Base/
            └── PageLogic.php         # Base trait with shared logic

tenants/                               # Consolidated tenant-specific code
└── {tenant_id}/
    ├── app/                          # Tenant-specific classes (simplified paths)
    │   └── Features/
    │       └── Pages/
    │           ├── Traits/
    │           │   └── PageLogic.php  # Tenant-specific (all views)
    │           └── Views/             # Tenant-specific view logic
    │               └── admin/
    │                   └── Traits/
    │                       └── PageLogic.php  # Tenant's admin-specific logic
    └── resources/
        └── views/
            ├── default/
            │   └── *.blade.php
            └── admin/
                └── *.blade.php
```

**Note:** All tenant-specific code (classes, views, etc.) is consolidated in `tenants/{tenant_id}/` for better organization and easier deployment.

## Namespace Structure

### Core Components
```php
App\Core\Helpers\TenancyHelper
App\Core\Models\Tenant
App\Core\Traits\ConstructableTrait
```

### Feature Components (Shared)
```php
App\Features\Pages\Controllers\PageController
```

### Tenant-Specific (Consolidated)
```php
// Namespace: App\Features\Pages\Tenants\{tenant_id}\...
// File path: tenants/{tenant_id}/app/Features/Pages/... (simplified, no redundant nesting)
App\Features\Pages\Tenants\lapp\Traits\PageLogic  # Tenant logic (all views)
// → tenants/lapp/app/Features/Pages/Traits/PageLogic.php

App\Features\Pages\Tenants\lapp\Views\admin\Traits\PageLogic  # Lapp's admin-specific logic
// → tenants/lapp/app/Features/Pages/Views/admin/Traits/PageLogic.php
```

### View-Specific (Shared)
```php
// Located in: app/Features/Pages/Views/
App\Features\Pages\Views\default\Traits\PageLogic
App\Features\Pages\Views\admin\Traits\PageLogic  # Base admin logic (all tenants)
```

### Shared Base
```php
App\Shared\Traits\Base\PageLogic
```

## Trait Composition Pattern

### How It Works

1. **Base Trait** - Shared logic for all tenants/views
2. **Tenant Trait** - Extends base with tenant-specific logic
3. **View Trait** - Extends base with view-specific logic
4. **Controller** - Uses both traits with conflict resolution

### Example: Page Logic

#### Base Trait
```php
// app/Shared/Traits/Base/PageLogic.php
namespace App\Shared\Traits\Base;

trait PageLogic
{
    protected $pageData = [];
    
    public function getPageData(): array
    {
        return $this->pageData;
    }
}
```

#### Tenant-Specific Trait
```php
// tenants/lapp/app/Features/Pages/Traits/PageLogic.php (simplified path)
// Namespace still includes tenant name for uniqueness
namespace App\Features\Pages\Tenants\lapp\Traits;

use App\Core\Traits\ConstructableTrait;
use App\Shared\Traits\Base\PageLogic as BasePageLogic;

trait PageLogic
{
    use ConstructableTrait, BasePageLogic {
        BasePageLogic::getPageData as getBasePageData;
    }
    
    protected $lappConfig;
    
    public function traitConstructPageLogic()
    {
        $this->lappConfig = [
            'name' => 'Lapp',
            'tagline' => 'Your Business Solution',
        ];
        
        $this->setPageData(['config' => $this->lappConfig]);
    }
}
```

#### View-Specific Trait
```php
// app/Features/Pages/Views/admin/Traits/PageLogic.php
namespace App\Features\Pages\Views\admin\Traits;

use App\Core\Traits\ConstructableTrait;
use App\Shared\Traits\Base\PageLogic as BasePageLogic;

trait PageLogic
{
    use ConstructableTrait, BasePageLogic {
        BasePageLogic::getPageData as getBasePageData;
    }
    
    protected $adminConfig;
    
    public function traitConstructPageLogic()
    {
        $this->adminConfig = [
            'type' => 'cms',
            'requires_auth' => true,
        ];
        
        $this->setPageData(['view_config' => $this->adminConfig]);
    }
}
```

#### Controller Using Both
```php
// app/Features/Pages/Controllers/PageController.php
namespace App\Features\Pages\Controllers;

use App\Features\Pages\Tenants\lapp\Traits\PageLogic as LappPageLogic;
use App\Features\Pages\Views\admin\Traits\PageLogic as AdminPageLogic;

class PageController extends Controller
{
    use LappPageLogic, AdminPageLogic {
        // View takes priority
        AdminPageLogic::getPageData insteadof LappPageLogic;
    }
    
    // Both traitConstructPageLogic() methods are called automatically
    // via ConstructableTrait
}
```

## ConstructableTrait: Constructor Emulation

Since PHP traits cannot have constructors, `ConstructableTrait` provides a workaround:

```php
trait ConstructableTrait
{
    public function __construct()
    {
        parent::__construct();
        $this->initializeAllTraits();
    }
    
    protected function initializeAllTraits()
    {
        // Automatically calls traitConstruct{TraitName}() for all traits
    }
}
```

### Trait Constructor Naming

Trait constructors must follow this pattern:
- Trait name: `PageLogic`
- Constructor method: `traitConstructPageLogic()`

## Priority Resolution

When multiple traits have the same method:

1. **Tenant-specific view** (highest priority) - e.g., `Tenants/lapp/Views/admin/Traits/PageLogic`
2. **Base view** (high priority) - e.g., `Views/admin/Traits/PageLogic`
3. **Tenant-specific** (medium priority) - e.g., `Tenants/lapp/Traits/PageLogic`
4. **Base/Shared** (lowest priority) - e.g., `Shared/Traits/Base/PageLogic`

Use `insteadof` to explicitly set priority:
```php
use LappPageLogic, BaseAdminPageLogic, LappAdminPageLogic {
    // Tenant-specific view takes highest priority
    LappAdminPageLogic::getPageData insteadof LappPageLogic, BaseAdminPageLogic;
    LappAdminPageLogic::getAdminDashboardData insteadof BaseAdminPageLogic;
}
```

## Tenant-Specific View Logic

When view logic needs to vary by tenant (e.g., admin panel differs slightly between tenants):

### Structure
```
app/Features/Pages/
├── Views/admin/Traits/PageLogic.php              # Base admin (shared)
└── Tenants/
    └── lapp/
        └── Views/admin/Traits/PageLogic.php      # Lapp's admin override
```

### Example: Tenant-Specific Admin Logic

#### Base Admin Trait (Shared)
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

#### Tenant-Specific Admin Trait
```php
// tenants/lapp/app/Features/Pages/Views/admin/Traits/PageLogic.php (simplified path)
// Namespace still includes tenant name for uniqueness
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
        $base = parent::getAdminDashboardData();
        return array_merge($base, [
            'stats' => ['lapp_metric' => 100],
        ]);
    }
}
```

#### Controller Using All Three Levels
```php
use LappPageLogic, BaseAdminPageLogic, LappAdminPageLogic {
    // Priority: Tenant-specific view > Base view > Tenant
    LappAdminPageLogic::getPageData insteadof LappPageLogic, BaseAdminPageLogic;
}
```

## Current Implementation: Lapp Tenant

### Default View (Landing Page)
- **Domain**: `lapp.test:8000`
- **View Code**: `default`
- **Purpose**: Public landing page
- **Location**: `tenants/lapp/resources/views/default/home.blade.php`
- **Usage**: `TenancyHelper::view('home', $data)`

### Admin View (CMS Login)
- **Domain**: `admin.lapp.test:8001`
- **View Code**: `admin`
- **Purpose**: Admin CMS with mock login
- **Location**: `tenants/lapp/resources/views/admin/login.blade.php`
- **Usage**: `TenancyHelper::view('login', $data)` ⚠️ Note: Use 'login', not 'admin.login'

### ⚠️ Important: View Path Construction

When using `TenancyHelper::view()`, only pass the view file name (without the view code prefix):

```php
// ✅ CORRECT
TenancyHelper::view('home', $data);     // → tenants.lapp.default.home
TenancyHelper::view('login', $data);    // → tenants.lapp.admin.login

// ❌ WRONG - Creates duplicate path
TenancyHelper::view('admin.login', $data);  // → tenants.lapp.admin.admin.login (ERROR!)
```

The helper automatically constructs the full path: `tenants.{tenant_id}.{code}.{view_name}`

## Adding New Features

### Step 1: Create Base Trait
```php
// app/Shared/Traits/Base/ProductLogic.php
namespace App\Shared\Traits\Base;

trait ProductLogic
{
    public function getProducts()
    {
        return Product::all();
    }
}
```

### Step 2: Create Tenant Trait
```php
// app/Features/Products/Tenants/lapp/Traits/ProductLogic.php
namespace App\Features\Products\Tenants\lapp\Traits;

use App\Core\Traits\ConstructableTrait;
use App\Shared\Traits\Base\ProductLogic as BaseProductLogic;

trait ProductLogic
{
    use ConstructableTrait, BaseProductLogic;
    
    public function traitConstructProductLogic()
    {
        // Lapp-specific initialization
    }
    
    public function getProducts()
    {
        return parent::getProducts()->where('lapp', true);
    }
}
```

### Step 3: Create View Trait
```php
// app/Features/Products/Views/admin/Traits/ProductLogic.php
namespace App\Features\Products\Views\admin\Traits;

use App\Core\Traits\ConstructableTrait;
use App\Shared\Traits\Base\ProductLogic as BaseProductLogic;

trait ProductLogic
{
    use ConstructableTrait, BaseProductLogic;
    
    public function traitConstructProductLogic()
    {
        // Admin-specific initialization
    }
    
    public function getProducts()
    {
        return parent::getProducts()->withTrashed();
    }
}
```

### Step 4: Use in Controller
```php
// app/Features/Products/Controllers/ProductController.php
use App\Features\Products\Tenants\lapp\Traits\ProductLogic as LappProductLogic;
use App\Features\Products\Views\admin\Traits\ProductLogic as AdminProductLogic;

class ProductController extends Controller
{
    use LappProductLogic, AdminProductLogic {
        AdminProductLogic::getProducts insteadof LappProductLogic;
    }
}
```

## Benefits

1. **Clean Separation**: Tenant and view code are isolated
2. **Reusability**: Base traits shared across tenants/views
3. **Flexibility**: Mix and match traits as needed
4. **Maintainability**: Easy to find and update code
5. **Scalability**: Add new tenants/views without affecting existing code
6. **Type Safety**: Full IDE support and autocomplete

## Best Practices

1. **Use descriptive names**: `ProductLogic` not `Logic`
2. **Keep traits focused**: One trait per concern
3. **Resolve conflicts explicitly**: Use `insteadof` and `as`
4. **Document priority**: Comment why one trait wins
5. **Test combinations**: Ensure all trait combinations work
6. **Follow naming**: `traitConstruct{TraitName}()` for constructors

## Three-Level Trait Hierarchy

The architecture supports three levels of trait composition:

1. **Base Traits** (`Shared/Traits/Base/`) - Shared across all tenants/views
2. **View Traits** (`Views/{code}/Traits/`) - Shared view logic across all tenants
3. **Tenant Traits** (`Tenants/{id}/Traits/`) - Tenant-specific logic for all views
4. **Tenant-View Traits** (`Tenants/{id}/Views/{code}/Traits/`) - Tenant-specific view logic

### Priority Order
```
Tenant-View > Base-View > Tenant > Base
```

This allows:
- ✅ Shared admin logic (in `Views/admin`)
- ✅ Tenant-specific admin overrides (in `Tenants/{id}/Views/admin`)
- ✅ Mostly same, slightly different pattern
- ✅ Easy to maintain and extend

## Summary

The hybrid architecture provides:
- ✅ Feature-based organization
- ✅ Tenant-specific code isolation
- ✅ View-specific code isolation
- ✅ **Tenant-specific view logic** (three-level hierarchy)
- ✅ Trait composition with constructors
- ✅ Clean namespace structure
- ✅ Maximum code reuse

