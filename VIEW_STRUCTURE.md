# View Structure

## Location

Views are organized by tenant and code in the consolidated structure:

```
tenants/{tenant_id}/resources/views/tenants/{tenant_id}/{code}/{view_name}.blade.php
```

**Example:**
- `tenants/lapp/resources/views/tenants/lapp/default/home.blade.php` → accessed as `tenants.lapp.default.home`
- `tenants/lapp/resources/views/tenants/lapp/admin/dashboard.blade.php` → accessed as `tenants.lapp.admin.dashboard`

**Note:** All tenant-specific code (classes, views, etc.) is now consolidated in the `tenants/{tenant_id}/` directory for better organization and easier deployment.

## Using in Routes

```php
use App\Helpers\TenancyHelper;

Route::get('/', function () {
    return TenancyHelper::view('home', [
        'tenant' => TenancyHelper::currentTenant(),
        'view' => TenancyHelper::currentView(),
    ]);
});

// ⚠️ IMPORTANT: Only pass the view name, not the full path!
// The helper constructs: tenants.{tenant_id}.{code}.{view_name}
// For admin view 'login.blade.php', use:
return TenancyHelper::view('login', $data);  // ✅ Correct
// NOT:
return TenancyHelper::view('admin.login', $data);  // ❌ Wrong - creates duplicate path
```

## Accessing Context

```blade
{{-- Current tenant --}}
{{ TenancyHelper::currentTenant()->id }}

{{-- Current view --}}
{{ TenancyHelper::currentView()->name }}
{{ TenancyHelper::currentView()->code }}
{{ TenancyHelper::currentView()->domain }}

{{-- Check view code --}}
@if(TenancyHelper::isViewCode('admin'))
    {{-- Admin-only content --}}
@endif

{{-- Check if admin view (shorthand) --}}
@if(TenancyHelper::isAdminView())
    {{-- Admin-only content --}}
@endif

{{-- Check if in tenant context --}}
@if(TenancyHelper::isTenantContext())
    {{-- Tenant-specific content --}}
@endif
```

## Safe Access Patterns

Since helper methods can return `null`, use safe access:

```blade
{{-- Safe access with null coalescing --}}
{{ TenancyHelper::currentTenant()?->id ?? 'No tenant' }}
{{ TenancyHelper::currentView()?->name ?? 'No view' }}

{{-- Or check before accessing --}}
@if(TenancyHelper::currentTenant())
    <p>Tenant: {{ TenancyHelper::currentTenant()->id }}</p>
@endif
```

## Shared Layout

All views can extend the shared layout:
```blade
@extends('layouts.app')
```
