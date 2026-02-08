# Larabis

Two-level multi-tenancy for Laravel using `stancl/tenancy`: **tenants** (separate DB per tenant) and **views** (multiple views per tenant, e.g. `default`, `admin`, `api`).

## Requirements

- PHP ^8.2, Laravel ^12.0, stancl/tenancy ^3.9

## Quick Start

```bash
composer install && npm install
php artisan migrate
php artisan tenant:create lapp --domains=lapp.test --domains=admin.lapp.test
```

Add to hosts: `127.0.0.1 lapp.test` and `127.0.0.1 admin.lapp.test`. Run from domain folders (e.g. `lapp.test/run-dev.sh`, `admin.lapp.test/run-dev.sh`) or use a single script like `./run-lapp-dev.sh` from parent.

- Default: http://lapp.test:8000  
- Admin: http://admin.lapp.test:8001

## Structure

- **Central DB**: `tenants`, `tenant_views`, domains.
- **Tenant DBs**: One per tenant (stancl).
- **Views**: `tenants/{tenant_id}/resources/views/{code}/{name}.blade.php` → path `tenants.{tenant_id}.{code}.{view_name}`.
- **Tenant code**: `tenants/{tenant_id}/app/...` (autoloaded via `bootstrap/tenant-autoload.php`).

## TenancyHelper

```php
use App\Helpers\TenancyHelper;

TenancyHelper::currentTenant()      // current Tenant or null
TenancyHelper::currentView()        // current TenantView or null
TenancyHelper::isTenantContext()    // tenancy initialized
TenancyHelper::isViewCode('admin')  // current view code
TenancyHelper::isAdminView()        // isViewCode('admin')
TenancyHelper::view('home', $data)  // render tenant view
TenancyHelper::getViewPath('home')  // path string
```

**Important:** Pass only the view name to `TenancyHelper::view()` (e.g. `'home'`, `'login'`). The helper builds `tenants.{tenant_id}.{code}.{view_name}`. Do **not** pass `'admin.login'` (would become `tenants.lapp.admin.admin.login`).

## Commands

```bash
# Create tenant (single or multiple views)
php artisan tenant:create mytenant --domains=mytenant.test --domains=admin.mytenant.test

# Add view to existing tenant
php artisan tenant:view mytenant api.mytenant.test --name=api --code=api
```

## Page data services (feature logic)

Page and admin data are provided by **service classes** (not traits), resolved per tenant and view.

- **Contract:** `App\Features\Pages\Contracts\PageDataServiceInterface` — `getPageData()`, `getAdminDashboardData()`, `getAdminTheme()`.
- **Base:** `app/Features/Pages/Base/Default/PageDataService.php` and `Base/Admin/PageDataService.php` — shared default and admin behavior.
- **Tenant:** `tenants/{id}/app/Features/Pages/Default/PageDataService.php` and `tenants/{id}/app/Features/Pages/Admin/PageDataService.php` — tenant-specific; extend or compose the base and override as needed.

`PageDataServiceFactory` resolves the implementation from the current tenant and view (tenant-specific class if it exists, otherwise base). The controller receives `PageDataServiceInterface` via the container. Use the same class name `PageDataService` and namespaces to distinguish Base vs `Tenants\{id}\Default` or `Admin`.

## Domain / request flow

Domain folders (e.g. `lapp.test/`, `admin.lapp.test/`) contain `config.php` with `tenant_id` and `code`. `TenantViewMiddleware` runs first: `TenantResolver` resolves tenant/view from DB, then `tenancy()->initialize($tenant)`, then context is bound to `CurrentTenant` and `CurrentTenantView`.

## Tenant submodules (optional)

Use `setup-tenant-submodule.sh`: set `TENANT_ID` and `REPO_URL`, run script. It creates the tenant repo, adds it as a submodule under `tenants/{id}/`, creates domain folders, and registers the tenant. Clone Larabis with `git clone --recursive` or run `git submodule update --init --recursive`.

## Upgrades (stancl/Laravel)

- **Critical:** `TenantViewMiddleware` must stay first in web middleware; do not reorder resolve → initialize → bind in middleware. Keep `tenant-autoload.php` in composer autoload.
- Watch: `tenancy()->initialize()`, `tenancy()->initialized`, `HasDatabase`/`HasDomains`, `TenantWithDatabase`.
- After upgrades run: `php artisan test` (including `UpgradeCompatibility` and tenant/error tests). Check logs for “Tenancy initialization failed” and “Tenant resolution failed”.

## Tests & logging

```bash
php artisan test
```

Tenant init failures are logged and re-thrown; resolution failures return null and are logged at debug. Use logs to troubleshoot missing tenants/views.
