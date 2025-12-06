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

```php
// Recommended: Use TenancyHelper
return TenancyHelper::view('home', $data);

// Or get path manually
$path = TenancyHelper::getViewPath('home');
return view($path, $data);
```

## Creating Views

Views are automatically created when using `tenant:create` or `tenant:view` commands. To manually add:

1. Create folder: `resources/views/tenants/{tenant_id}/{code}/`
2. Add view file: `{view_name}.blade.php`
3. Use in routes: `TenancyHelper::view('view_name', $data)`
