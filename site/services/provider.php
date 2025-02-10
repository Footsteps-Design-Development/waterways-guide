<?php

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\ComponentFactory;
use Joomla\CMS\MVC\Service\Provider\MVCFactory;
use Joomla\CMS\Router\Service\Provider\RouterFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Component\WaterWaysGuide\Site\Extension\WaterWaysGuideComponent;

return new class implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->registerServiceProvider(new ComponentFactory(WaterWaysGuideComponent::class));
        $container->registerServiceProvider(new MVCFactory());
        $container->registerServiceProvider(new ComponentDispatcherFactory());
        $container->registerServiceProvider(new RouterFactory());

        // Load Helper
        require_once JPATH_SITE . '/components/com_waterways_guide/src/Helper/WaterwaysHelper.php';

        $container->set(
            ComponentInterface::class . '.com_waterways_guide',
            function (Container $container) {
                return new WaterWaysGuideComponent($container->get(ComponentDispatcherFactoryInterface::class));
            }
        );
    }
};
