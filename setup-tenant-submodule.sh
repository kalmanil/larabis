#!/bin/bash
# Setup script for new tenant as git submodule
# Usage: ./setup-tenant-submodule.sh

# ============================================
# CONFIGURATION - Update these values
# ============================================
TENANT_ID="HEBREW_TENANT_ID"  # Replace with actual Hebrew tenant name
REPO_URL="https://github.com/yourusername/HEBREW_TENANT_ID.git"  # Replace with actual repo URL

# ============================================
# Step 1: Create Tenant Repo Structure
# ============================================
echo "Step 1: Creating tenant repository structure..."

# Navigate to tenants directory
cd tenants

# Create tenant directory
mkdir -p $TENANT_ID
cd $TENANT_ID

# Initialize git repo
git init

# Create tenant structure
mkdir -p app/Features
mkdir -p resources/views/default
mkdir -p resources/views/admin

# Create .gitignore
cat > .gitignore << 'EOF'
.env
.vscode/
.idea/
*.log
vendor/
node_modules/
.DS_Store
EOF

# Create README
cat > README.md << EOF
# $TENANT_ID Tenant

Tenant-specific code for $TENANT_ID.

## Structure
- \`app/\` - Tenant-specific PHP classes and traits
- \`resources/views/\` - Tenant-specific Blade templates
EOF

# Initial commit
git add .
git commit -m "Initial tenant structure for $TENANT_ID"

# Add remote and push
git remote add origin $REPO_URL
git branch -M main
git push -u origin main

echo "✓ Tenant repository created and pushed"

# ============================================
# Step 2: Add as Git Submodule in Larabis
# ============================================
echo "Step 2: Adding tenant as submodule to Larabis..."

# Go back to larabis root
cd ../..

# Remove from tracking if already tracked (shouldn't be, but just in case)
git rm -r --cached tenants/$TENANT_ID 2>/dev/null || echo "Not tracked yet"

# Add as submodule
git submodule add $REPO_URL tenants/$TENANT_ID

# Commit submodule addition
git commit -m "Add $TENANT_ID tenant as submodule"

echo "✓ Tenant added as submodule"

# ============================================
# Step 3: Create Tenant in Larabis Database
# ============================================
echo "Step 3: Creating tenant in database..."

php artisan tenant:create $TENANT_ID \
    --domains=$TENANT_ID.test \
    --domains=admin.$TENANT_ID.test

echo "✓ Tenant created in database"

# ============================================
# Step 4: Create Domain Folders (outside larabis)
# ============================================
echo "Step 4: Creating domain folders..."

# Go to workspace root (parent of larabis)
cd ..

# Create default domain folder
mkdir -p $TENANT_ID.test

cat > $TENANT_ID.test/config.php << EOF
<?php
return [
    'tenant_id' => '$TENANT_ID',
    'code' => 'default',
    'site_title' => '$TENANT_ID - Main Site',
    'theme_color' => '#3b82f6',
];
EOF

cat > $TENANT_ID.test/index.php << 'EOF'
<?php
$domainConfig = require __DIR__ . '/config.php';
foreach ($domainConfig as $key => $value) {
    if ($key === 'view_type') {
        $_ENV['DOMAIN_CODE'] = $value;
        $_ENV['DOMAIN_VIEW_TYPE'] = $value;
    } else {
        $_ENV['DOMAIN_' . strtoupper($key)] = $value;
    }
}
require __DIR__ . '/../larabis/public/index.php';
EOF

cat > $TENANT_ID.test/router.php << 'EOF'
<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}
require_once __DIR__ . '/index.php';
EOF

# Create admin domain folder
mkdir -p admin.$TENANT_ID.test

cat > admin.$TENANT_ID.test/config.php << EOF
<?php
return [
    'tenant_id' => '$TENANT_ID',
    'code' => 'admin',
    'site_title' => '$TENANT_ID - Admin CMS',
    'theme_color' => '#8b5cf6',
];
EOF

cp $TENANT_ID.test/index.php admin.$TENANT_ID.test/index.php
cp $TENANT_ID.test/router.php admin.$TENANT_ID.test/router.php

echo "✓ Domain folders created"

# ============================================
# Summary
# ============================================
echo ""
echo "============================================"
echo "Setup Complete!"
echo "============================================"
echo "Tenant ID: $TENANT_ID"
echo "Repository: $REPO_URL"
echo "Domain folders:"
echo "  - $TENANT_ID.test/"
echo "  - admin.$TENANT_ID.test/"
echo ""
echo "Next steps:"
echo "1. Update your hosts file if needed"
echo "2. Start development servers from domain folders"
echo "3. Begin developing tenant-specific code in tenants/$TENANT_ID/"

