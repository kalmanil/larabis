<?php

namespace App\Features\Pages\Views\default\Traits;

use App\Shared\Traits\Base\PageLogic as BasePageLogic;

trait PageLogic
{
    use BasePageLogic;
    
    protected $defaultViewConfig;
    
    public function traitConstructPageLogic()
    {
        $this->defaultViewConfig = [
            'type' => 'landing',
            'show_navigation' => true,
            'show_footer' => true,
            'meta_title' => 'Welcome',
        ];
        
        $this->setPageData([
            'view_config' => $this->defaultViewConfig,
            'is_landing' => true,
        ]);
    }
    
    public function getDefaultViewConfig(): array
    {
        return $this->defaultViewConfig;
    }
}

