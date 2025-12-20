# View Management

All views are equal - 'default' is just a naming convention for main domains.

## Creating Tenants

```bash
# Single view (backward compatibility - uses --domain)
php artisan tenant:create mytenant --domain=mytenant.test

# Multiple views (all equal) - recommended
php artisan tenant:create mytenant \
    --domains=mytenant.test \
    --domains=admin.mytenant.test \
    --domains=api.mytenant.test

# Note: --domain creates a single view named "default"
#       --domains creates multiple views with inferred names/codes
```

## Managing Views

### Add View

```bash
php artisan tenant:view mytenant api.mytenant.test --name=api --code=api
```

### Update View

```bash
# Change name and code
php artisan tenant:view mytenant mytenant.test --update --name=main --code=main

# Change only code
php artisan tenant:view mytenant mytenant.test --update --code=public
```

## View Properties

- **name**: Display name (e.g., 'default', 'admin')
- **code**: Used in view paths (e.g., `tenants.lapp.{code}.home`)
- **domain**: Domain this view handles

View name/code are inferred from domain:
- `mytenant.test` → name='default', code='default'
- `admin.mytenant.test` → name='admin', code='admin'

## View Files

Views are automatically created at:
```
tenants/{tenant_id}/resources/views/tenants/{tenant_id}/{code}/{view_name}.blade.php
```

Folders are automatically created/renamed when views are added/updated.
