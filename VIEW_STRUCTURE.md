# View Structure

## Location

Views are organized by tenant and code in the consolidated structure:

```
tenants/{tenant_id}/resources/views/{code}/{view_name}.blade.php
```

**Example:**
- `tenants/lapp/resources/views/default/home.blade.php` → accessed as `tenants.lapp.default.home`
- `tenants/lapp/resources/views/admin/dashboard.blade.php` → accessed as `tenants.lapp.admin.dashboard`

**Note:** All tenant-specific code (classes, views, etc.) is consolidated in the `tenants/{tenant_id}/` directory with simplified paths (no redundant tenant-name nesting).

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

## Error Handling

The `TenancyHelper::view()` method throws exceptions if:
- Tenant or view context is not available
- The view file does not exist

**Logging:**
- Tenant resolution failures are logged at debug level
- Initialization failures are logged at error level with full context

```php
try {
    return TenancyHelper::view('home', $data);
} catch (\Exception $e) {
    // Handle error: view not found or context missing
    // Check logs for detailed error information
    return view('errors.view-not-found', ['error' => $e->getMessage()]);
}
```

## Shared Layout

All views can extend the shared layout:
```blade
@extends('layouts.app')
```
