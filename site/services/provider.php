<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Component\WaterWaysGuide\Site\Extension\WaterWaysGuideComponent;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->registerServiceProvider(new MVCFactory('\\Joomla\\Component\\WaterWaysGuide'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Joomla\\Component\\WaterWaysGuide'));
        $container->registerServiceProvider(new RouterFactory('\\Joomla\\Component\\WaterWaysGuide'));

        $container->set(
            ComponentInterface::class,
            function (Container $container): WaterWaysGuideComponent {
                $component = new WaterWaysGuideComponent($container->get(ComponentDispatcherFactoryInterface::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                $component->setRouterFactory($container->get(RouterFactoryInterface::class));

                return $component;
            }
        );
    }
};
