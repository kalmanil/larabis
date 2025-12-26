# Tenant Submodule Setup Guide

This guide explains how to set up new tenants as separate git repositories using git submodules, while keeping sample apps (like `lapp`) in the main Larabis repository.

## Architecture

- **Sample Apps** (e.g., `lapp`): Stored directly in the Larabis repository
- **Production Tenants**: Stored as git submodules in separate repositories
- **Domain Folders**: Entry points outside `larabis/` directory

## Quick Setup

### 1. Update Configuration

Edit `setup-tenant-submodule.sh` and update:
- `TENANT_ID`: Your tenant identifier (e.g., "hebrew")
- `REPO_URL`: Your tenant repository URL

### 2. Run Setup Script

```bash
./setup-tenant-submodule.sh
```

This script will:
1. Create tenant repository structure
2. Initialize git repo and push to remote
3. Add tenant as submodule to Larabis
4. Create tenant in Larabis database
5. Create domain folders outside larabis/

## Daily Workflow

### Work on Tenant Code

```bash
cd larabis/tenants/HEBREW_TENANT_ID
# Make changes
git add .
git commit -m "Update tenant code"
git push
```

### Update Submodule in Larabis

```bash
cd larabis
git submodule update --remote tenants/HEBREW_TENANT_ID
git add tenants/HEBREW_TENANT_ID
git commit -m "Update HEBREW_TENANT_ID submodule"
```

### Update All Submodules

```bash
cd larabis
git submodule update --remote --merge
git add tenants/
git commit -m "Update all tenant submodules"
```

## Cloning Larabis with Submodules

When cloning the Larabis repository, include submodules:

```bash
git clone --recursive https://github.com/yourusername/larabis.git
```

Or if already cloned:

```bash
git submodule update --init --recursive
```

## Adding More Sample Apps

To add another sample app directly in the Larabis repo:

1. Create the tenant:
   ```bash
   php artisan tenant:create sample2 --domains=sample2.test --domains=admin.sample2.test
   ```

2. Update `.gitignore` to include it:
   ```gitignore
   tenants/*
   !tenants/lapp
   !tenants/sample2  # Add this line
   !tenants/.gitmodules
   !tenants/.git
   ```

3. Commit normally:
   ```bash
   git add tenants/sample2
   git commit -m "Add sample2 tenant"
   ```

## File Structure

```
workspace/
├── larabis/                    # Main Larabis repository
│   ├── tenants/
│   │   ├── lapp/              # Sample app (in main repo)
│   │   └── hebrew/            # Production tenant (submodule)
│   └── ...
├── hebrew.test/               # Domain entry point
│   ├── config.php
│   ├── index.php
│   └── router.php
└── admin.hebrew.test/         # Admin domain entry point
    ├── config.php
    ├── index.php
    └── router.php
```

## Notes

- Tenant repos are separate from Larabis repo
- Tenant code lives in `larabis/tenants/{TENANT_ID}/`
- Domain folders are entry points outside `larabis/`
- Use submodule commands to sync tenant updates
- Each tenant can have different versioning/release cycles
- Sample apps stay in main repo for easy reference

