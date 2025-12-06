<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_views', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('name'); // e.g., 'default', 'admin', 'api'
            $table->string('domain')->unique(); // e.g., 'lapp.test', 'admin.lapp.test'
            $table->string('code')->default('default'); // 'default', 'admin', 'api', etc.
            $table->json('config')->nullable(); // View-specific configuration
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index(['tenant_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_views');
    }
};

