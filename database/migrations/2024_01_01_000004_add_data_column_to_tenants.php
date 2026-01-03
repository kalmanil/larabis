<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Column already exists in create_tenants_table migration (2024_01_01_000001)
        // This migration is kept for backward compatibility but is effectively a no-op
        // The data column is created in the initial tenants table creation
        if (!Schema::hasColumn('tenants', 'data')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->json('data')->nullable()->after('id');
            });
        }
    }

    public function down(): void
    {
        // Only drop if column exists and we're not removing the table entirely
        if (Schema::hasColumn('tenants', 'data')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropColumn('data');
            });
        }
    }
};

