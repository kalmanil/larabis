<?php

namespace App\Shared\Traits\Base;

use App\Helpers\TenancyHelper;

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
        return TenancyHelper::currentTenant();
    }
    
    public function getView(): ?\App\Models\TenantView
    {
        return TenancyHelper::currentView();
    }
}

