<?php

namespace Joomla\Component\WaterWaysGuide\Site\Router;

use Joomla\CMS\Router\RouterView;
use Joomla\CMS\Router\RouterViewConfiguration;

defined('_JEXEC') or die;

class Router extends RouterView
{
    public function __construct($app, $menu)
    {
        $routes = new RouterViewConfiguration('waterways_guide');
        $this->registerView($routes);
        parent::__construct($app, $menu);
    }
}
