# Upgrade Safety Documentation

## Custom Tenancy Architecture

This application extends stancl/tenancy with a custom "view" layer that allows multiple views per tenant (e.g., `default`, `admin`, `api`).

### Custom Components

1. **TenantViewMiddleware** (`app/Http/Middleware/TenantViewMiddleware.php`)
   - **Why custom:** We need to initialize tenancy AND set tenant view context
   - **Alternative considered:** Could not use stancl's `InitializeTenancyByDomain` because we need custom view logic
   - **Risks:** Bypasses stancl's standard initialization flow
   - **Upgrade notes:** Watch for changes to `tenancy()->initialize()` API

2. **TenantView Model** (`app/Models/TenantView.php`)
   - Custom model for managing multiple views per tenant
   - Stored in central database, not tenant databases
   - **Upgrade notes:** Independent of stancl internals - safe

3. **Custom Autoloader** (`bootstrap/tenant-autoload.php`)
   - Loads tenant-specific classes from `tenants/{id}/app/`
   - **Upgrade notes:** May need adjustment if Laravel class caching changes

4. **View Namespace Registration** (`app/Providers/AppServiceProvider.php`)
   - Registers tenant view namespaces using TenancyInitialized event
   - **Upgrade notes:** Registered after tenancy initialization for proper timing

## Sensitive Integration Points

1. **TenancyServiceProvider** (`app/Providers/TenancyServiceProvider.php`)
   - Only binds Tenant model contract
   - Does NOT register stancl middleware (handled by TenantViewMiddleware)
   - Uses contracts only - safe for upgrades

2. **TenantResolver Service** (`app/Tenancy/TenantResolver.php`)
   - **Single entry point** for tenant resolution logic
   - Reads domain configuration (config/env)
   - Performs database queries to find tenant/view
   - **Logging:** Logs debug messages when resolution fails (tenant not found, view not found)
   - **DOES NOT create tenant views** - views must exist (created via artisan commands)
   - **Upgrade notes:** Monitor for changes to model query patterns

3. **TenantViewMiddleware** (`app/Http/Middleware/TenantViewMiddleware.php`)
   - **Single entry point** for tenancy initialization
   - Uses TenantResolver to resolve tenant/view
   - Binds resolved instances via contracts (CurrentTenant, CurrentTenantView)
   - Calls `tenancy()->initialize($tenant)` - monitor this API for changes
   - **Error handling:** Wraps initialization in try-catch, logs errors, re-throws for stancl handling
   - **Does NOT perform DB writes or schema checks**

4. **Domain Configuration**
   - Domain folders use `config/domain.php` and `$_ENV` (backward compatible)
   - **Refactored:** Now uses Laravel config system as primary, `$_ENV` as fallback
   - Configuration is read by TenantResolver service

5. **Container Bindings**
   - Uses contracts: `CurrentTenant::class`, `CurrentTenantView::class`
   - Implemented by `TenantContext` class
   - Backward compatibility maintained via string key fallbacks in TenancyHelper

## Upgrade Checklist

When upgrading stancl/tenancy:

- [ ] Verify `tenancy()->initialize()` API (called in TenantViewMiddleware)
- [ ] Check `tenancy()->initialized` property/method (used in TenancyHelper)
- [ ] Review middleware registration changes
- [ ] Test tenant database connection logic
- [ ] Verify `HasDatabase` and `HasDomains` traits (used in Tenant model)
- [ ] Check contract interfaces (`TenantWithDatabase`)

## Architecture Entry Points

### Tenancy Initialization
- **Entry Point:** `TenantViewMiddleware::handle()`
- **Location:** `app/Http/Middleware/TenantViewMiddleware.php`
- **Calls:** `tenancy()->initialize($tenant)` from stancl
- **When:** Every HTTP request (if tenant resolved)

### Tenant Resolution
- **Entry Point:** `TenantResolver::resolve()`
- **Location:** `app/Tenancy/TenantResolver.php`
- **Responsibilities:**
  - Reads domain configuration
  - Queries database for tenant/view
  - Returns resolved tenant and view (or null)
- **Does NOT:** Create tenant views (must exist)

### Container Bindings
- **Contracts:** `CurrentTenant`, `CurrentTenantView`
- **Implementation:** `TenantContext`
- **Bound by:** `TenantViewMiddleware` after resolution
- **Access:** Via `TenancyHelper` or inject contracts directly

## Error Handling

### Tenant Initialization Failures
- **Location:** `TenantViewMiddleware::handle()`
- **Behavior:** Wraps `tenancy()->initialize()` in try-catch block
- **Logging:** Errors are logged with context (tenant_id, domain, error message)
- **Exception handling:** Exceptions are re-thrown so stancl can handle them appropriately
- **Common failures:**
  - Tenant database doesn't exist
  - Database connection refused
  - Invalid tenant configuration

### Tenant Resolution Failures
- **Location:** `TenantResolver::resolveTenant()` and `resolveView()`
- **Behavior:** Returns `null` when tenant/view cannot be resolved
- **Logging:** Debug-level logs when resolution fails (tenant not found, view not found)
- **Graceful degradation:** Application continues normally when tenant is null (non-tenant routes)

### Monitoring
- Check logs for `Tenancy initialization failed` entries
- Check logs for `Tenant resolution failed` entries
- Use log context to identify problematic tenants/domains

## Laravel Upgrade Notes

- [ ] Test custom autoloader with class caching
- [ ] Verify service provider boot order
- [ ] Test view namespace registration timing (uses TenancyInitialized event)
- [ ] Verify config system compatibility
- [ ] Test error handling paths after upgrade

## Critical File Locations

### Files That Must Not Be Moved/Reordered

1. **bootstrap/app.php** - Middleware registration
   - `TenantViewMiddleware` MUST be prepended to web middleware
   - Order: Tenancy initialization → Other middleware
   - ⚠️ DO NOT move TenantViewMiddleware to different middleware group
   - Location: Lines 13-17

2. **app/Http/Middleware/TenantViewMiddleware.php** - Initialization order
   - Step 1: Resolve tenant/view (line 42-45)
   - Step 2: Initialize stancl tenancy (line 54)
   - Step 3: Bind context (line 61-68)
   - ⚠️ DO NOT reorder these steps
   - Error handling wraps step 2-3 (line 49-81)

3. **bootstrap/tenant-autoload.php** - Autoloader registration
   - Loaded via composer.json "files" autoload (line 32-34)
   - Must run before Laravel fully boots
   - ⚠️ DO NOT remove from composer.json autoload
   - ⚠️ DO NOT change autoload registration order

4. **app/Tenancy/TenantResolver.php** - Single entry point
   - All tenant resolution must go through this service
   - ⚠️ DO NOT create alternative resolution paths
   - Used by TenantViewMiddleware (line 42)

## Upgrade Breakage Detection

### Automated Tests

Run these tests after every upgrade to detect breakage:

```bash
php artisan test --filter=UpgradeCompatibility
```

These tests verify that critical APIs we depend on still work:
- `tenancy()->initialize()` API
- `tenancy()->initialized` property
- `HasDatabase` and `HasDomains` traits
- `TenantWithDatabase` contract
- Middleware initialization order

### Manual Verification Checklist

After upgrading stancl/tenancy or Laravel:

- [ ] Run `php artisan test --filter=UpgradeCompatibility`
- [ ] Verify `tenancy()->initialize($tenant)` still works
- [ ] Check `tenancy()->initialized` property/method still accessible
- [ ] Test `HasDatabase` and `HasDomains` traits still work
- [ ] Verify `TenantWithDatabase` contract still exists
- [ ] Confirm middleware registration order still respected
- [ ] Test tenant autoloader still works with class caching
- [ ] Verify error handling still works (check logs)
- [ ] Test tenant resolution with invalid domains
- [ ] Verify view namespace registration timing

## Testing

Run the following tests after upgrades:

```bash
php artisan test
```

Key test files:
- `tests/Feature/TenantViewTest.php` - Tenant view resolution
- `tests/Feature/TenantViewFallbackTest.php` - Fallback behavior
- `tests/Feature/TenantErrorHandlingTest.php` - Error scenarios and logging
- `tests/Feature/UpgradeCompatibilityTest.php` - Upgrade breakage detection

