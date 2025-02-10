<?php

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\ComponentFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Service\Provider\MVCFactory;
use Joomla\CMS\Router\Service\Provider\RouterFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Component\WaterWaysGuide\Site\Extension\WaterWaysGuideComponent;

/**
 * The provider class for com_waterways_guide
 */
return new class implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->registerServiceProvider(new ComponentFactory(WaterWaysGuideComponent::class));
        $container->registerServiceProvider(new MVCFactory());
        $container->registerServiceProvider(new ComponentDispatcherFactory());
        $container->registerServiceProvider(new RouterFactory());

        $container->set(
            ComponentInterface::class . '.com_waterways_guide',
            function (Container $container) {
                return new WaterWaysGuideComponent($container->get(ComponentDispatcherFactoryInterface::class));
            }
        );
    }
};
