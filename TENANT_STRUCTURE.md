# Tenant Structure

## View Organization

Views are organized by tenant and code:

```
resources/views/tenants/
├── lapp/
│   ├── default/          # Default view (lapp.test)
│   │   ├── home.blade.php
│   │   └── dashboard.blade.php
│   └── admin/            # Admin view (admin.lapp.test)
│       ├── home.blade.php
│       └── dashboard.blade.php
└── tenant2/
    ├── default/
    └── admin/
```

## View Path Format

Views use the format: `tenants.{tenant_id}.{code}.{view_name}`

**Examples:**
- `tenants.lapp.default.home` → `resources/views/tenants/lapp/default/home.blade.php`
- `tenants.lapp.admin.dashboard` → `resources/views/tenants/lapp/admin/dashboard.blade.php`

## Using Views

### Recommended: Use TenancyHelper

```php
use App\Helpers\TenancyHelper;

// Render tenant-specific view (throws exception if not found)
// IMPORTANT: Pass only the view name, NOT the full path!
// The helper automatically constructs: tenants.{tenant_id}.{code}.{view_name}
return TenancyHelper::view('home', $data);        // ✅ Correct
return TenancyHelper::view('login', $data);       // ✅ Correct
return TenancyHelper::view('admin.login', $data); // ❌ WRONG! Creates duplicate path

// Get view path without rendering
$path = TenancyHelper::getViewPath('home');
return view($path, $data);
```

### ⚠️ Common Mistake: Duplicate View Code in Path

**DO NOT** include the view code in the view name when using `TenancyHelper::view()`:

```php
// ❌ WRONG - This creates: tenants.lapp.admin.admin.login
return TenancyHelper::view('admin.login', $data);

// ✅ CORRECT - This creates: tenants.lapp.admin.login
return TenancyHelper::view('login', $data);
```

The `TenancyHelper::view()` method automatically prepends `tenants.{tenant_id}.{code}.` to your view name, so you only need to provide the view file name.

### Error Handling

The `TenancyHelper::view()` method throws exceptions if:
- Tenant or view context is not available
- The view file does not exist

```php
try {
    return TenancyHelper::view('home', $data);
} catch (\Exception $e) {
    // Handle error: view not found or context missing
    return view('errors.view-not-found', ['error' => $e->getMessage()]);
}
```

## Creating Views

Views are automatically created when using `tenant:create` or `tenant:view` commands. To manually add:

1. Create folder: `resources/views/tenants/{tenant_id}/{code}/`
2. Add view file: `{view_name}.blade.php`
3. Use in routes: `TenancyHelper::view('view_name', $data)`
