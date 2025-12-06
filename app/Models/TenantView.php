<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantView extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'domain',
        'code', // 'default', 'admin', 'api', etc.
        'config', // JSON field for view-specific configuration
    ];

    protected $casts = [
        'config' => 'array',
    ];

    /**
     * Get the tenant that owns this view
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    /**
     * Get the current view context
     */
    public static function current()
    {
        return app('currentTenantView');
    }
}

