<?php

namespace App\Shared\Traits\Base;

use App\Helpers\TenancyHelper;
use App\Tenancy\TenantContext;

trait PageLogic
{
    protected $pageData = [];

    public function getPageData(): array
    {
        return $this->pageData;
    }

    public function setPageData(array $data): void
    {
        $this->pageData = array_merge($this->pageData, $data);
    }

    public function getTenant(): ?\App\Models\Tenant
    {
        if (property_exists($this, 'tenantContext') && $this->tenantContext instanceof TenantContext) {
            return $this->tenantContext->getTenant();
        }
        return TenancyHelper::currentTenant();
    }

    public function getView(): ?\App\Models\TenantView
    {
        if (property_exists($this, 'tenantContext') && $this->tenantContext instanceof TenantContext) {
            return $this->tenantContext->getView();
        }
        return TenancyHelper::currentView();
    }
}

