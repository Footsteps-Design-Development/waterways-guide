<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Administrator\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\Component\WaterWaysGuide\Administrator\Console\MigrateGuidesCommand;
use Joomla\DI\Container;
use Psr\Container\ContainerInterface;

class WaterWaysGuideComponent extends MVCComponent implements BootableExtensionInterface
{
    /**
     * Boots the extension
     *
     * @param ContainerInterface $container The DI container
     *
     * @return void
     */
    public function boot(ContainerInterface $container): void
    {
        // Register CLI commands if running in CLI mode
        if (PHP_SAPI === 'cli') {
            $this->registerConsoleCommands($container);
        }
    }

    /**
     * Register console commands
     *
     * @param ContainerInterface $container The DI container
     *
     * @return void
     */
    private function registerConsoleCommands(ContainerInterface $container): void
    {
        if ($container instanceof Container) {
            $container->share(
                MigrateGuidesCommand::class,
                function (Container $container): MigrateGuidesCommand {
                    return new MigrateGuidesCommand();
                },
                true
            );
        }
    }
}
