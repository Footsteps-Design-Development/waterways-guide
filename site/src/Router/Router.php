<?php

namespace Joomla\Component\WaterWaysGuide\Site\Router;

use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;

defined('_JEXEC') or die;

class Router extends RouterView
{
    public function __construct($app, $menu)
    {
        $this->registerRules();
        parent::__construct($app, $menu);
    }

    private function registerRules()
    {
        $this->attachRule(new MenuRules($this));
        $this->attachRule(new StandardRules($this));
        $this->attachRule(new NomenuRules($this));
    }
}
