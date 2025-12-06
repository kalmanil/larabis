<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check if column exists before renaming
        if (Schema::hasColumn('tenant_views', 'view_type')) {
            Schema::table('tenant_views', function (Blueprint $table) {
                // Drop the composite index first (MySQL/SQLite compatible)
                $table->dropIndex(['tenant_id', 'view_type']);
            });
            
            // Use DB::statement for column rename (more compatible)
            DB::statement('ALTER TABLE tenant_views RENAME COLUMN view_type TO code');
            
            Schema::table('tenant_views', function (Blueprint $table) {
                // Recreate the composite index with the new column name
                $table->index(['tenant_id', 'code']);
            });
        }
    }

    public function down(): void
    {
        // Check if column exists before renaming
        if (Schema::hasColumn('tenant_views', 'code')) {
            Schema::table('tenant_views', function (Blueprint $table) {
                // Drop the composite index
                $table->dropIndex(['tenant_id', 'code']);
            });
            
            // Use DB::statement for column rename (more compatible)
            DB::statement('ALTER TABLE tenant_views RENAME COLUMN code TO view_type');
            
            Schema::table('tenant_views', function (Blueprint $table) {
                // Recreate the composite index with the old column name
                $table->index(['tenant_id', 'view_type']);
            });
        }
    }
};
