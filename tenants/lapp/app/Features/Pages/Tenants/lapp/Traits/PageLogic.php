<?php

namespace App\Features\Pages\Tenants\lapp\Traits;

use App\Shared\Traits\Base\PageLogic as BasePageLogic;
use App\Helpers\TenancyHelper;

trait PageLogic
{
    use BasePageLogic;
    
    protected $lappConfig;
    protected $lappBranding;
    
    public function traitConstructPageLogic()
    {
        $this->lappConfig = [
            'name' => 'Lapp',
            'tagline' => 'Your Business Solution',
            'theme_color' => '#3b82f6',
            'logo' => '/images/lapp-logo.png',
        ];
        
        $this->lappBranding = [
            'primary_color' => '#3b82f6',
            'secondary_color' => '#8b5cf6',
            'accent_color' => '#10b981',
        ];
        
        $this->setPageData([
            'tenant' => TenancyHelper::currentTenant(),
            'config' => $this->lappConfig,
            'branding' => $this->lappBranding,
        ]);
    }
    
    public function getLappConfig(): array
    {
        return $this->lappConfig;
    }
    
    public function getLappBranding(): array
    {
        return $this->lappBranding;
    }
}

