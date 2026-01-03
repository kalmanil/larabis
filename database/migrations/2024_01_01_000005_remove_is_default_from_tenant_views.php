<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only drop column if it exists (for backward compatibility)
        // The is_default column was never in the initial tenant_views table creation,
        // but this migration is kept for systems that may have added it
        if (Schema::hasColumn('tenant_views', 'is_default')) {
            Schema::table('tenant_views', function (Blueprint $table) {
                $table->dropColumn('is_default');
            });
        }
    }

    public function down(): void
    {
        // Only add column if it doesn't exist
        if (!Schema::hasColumn('tenant_views', 'is_default')) {
            Schema::table('tenant_views', function (Blueprint $table) {
                $table->boolean('is_default')->default(false)->after('code');
            });
        }
    }
};

