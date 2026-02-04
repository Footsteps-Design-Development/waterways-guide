<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Site\Router;

defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Menu\AbstractMenu;

class Router extends RouterView
{
    public function __construct(SiteApplication $app, AbstractMenu $menu)
    {
        $wwg = new RouterViewConfiguration('wwg');
        $this->registerView($wwg);

        parent::__construct($app, $menu);
    }
}
