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
     * 
     * @deprecated Use TenancyHelper::currentView() or inject CurrentTenantView contract
     */
    public static function current()
    {
        // Try contract first, fall back to string key for backward compatibility
        if (app()->bound(\App\Contracts\CurrentTenantView::class)) {
            return app(\App\Contracts\CurrentTenantView::class)->getView();
        }
        if (app()->bound('currentTenantView')) {
            return app('currentTenantView');
        }
        return null;
    }
}

