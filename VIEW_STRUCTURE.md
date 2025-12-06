# View Structure

## Location

Views are organized by tenant and code:

```
resources/views/tenants/{tenant_id}/{code}/{view_name}.blade.php
```

**Example:**
- `resources/views/tenants/lapp/default/home.blade.php` → accessed as `tenants.lapp.default.home`
- `resources/views/tenants/lapp/admin/dashboard.blade.php` → accessed as `tenants.lapp.admin.dashboard`

## Using in Routes

```php
use App\Helpers\TenancyHelper;

Route::get('/', function () {
    return TenancyHelper::view('home', [
        'tenant' => TenancyHelper::currentTenant(),
        'view' => TenancyHelper::currentView(),
    ]);
});
```

## Accessing Context

```blade
{{-- Current tenant --}}
{{ TenancyHelper::currentTenant()->id }}

{{-- Current view --}}
{{ TenancyHelper::currentView()->name }}
{{ TenancyHelper::currentView()->code }}

{{-- Check view code --}}
@if(TenancyHelper::isViewCode('admin'))
    {{-- Admin-only content --}}
@endif
```

## Shared Layout

All views can extend the shared layout:
```blade
@extends('layouts.app')
```
