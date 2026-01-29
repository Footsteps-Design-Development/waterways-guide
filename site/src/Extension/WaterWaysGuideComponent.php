<?php

namespace Joomla\Component\WaterWaysGuide\Site\Extension;

use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Component\WaterWaysGuide\Site\Router\Router;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

class WaterWaysGuideComponent implements ComponentInterface, RouterServiceInterface
{
    private $dispatcherFactory;

    public function __construct(ComponentDispatcherFactoryInterface $dispatcherFactory)
    {
        $this->dispatcherFactory = $dispatcherFactory;
    }

    public function getDispatcher(): ComponentDispatcherFactoryInterface
    {
        return $this->dispatcherFactory;
    }

    public function getRouter()
    {
        return new Router();
    }

    // public function getRoutes()
    // {
    //     return [
    //         'generatePdf' => [
    //             'type' => 'component',
    //             'controller' => 'display',
    //             'task' => 'generatePdf'
    //         ],
    //         'generateKml' => [
    //             'type' => 'component',
    //             'controller' => 'display',
    //             'task' => 'generateKml'
    //         ]
    //     ];
    // }
    public function getRoutes()
    {
        return [
            'generatePdf' => [
                'type' => 'component',
                'controller' => 'wwg',
                'task' => 'generatepdf'
            ],
            'generateKml' => [
                'type' => 'component',
                'controller' => 'wwg',
                'task' => 'generatekml'
            ]
        ];
    }
}
