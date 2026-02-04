<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Site\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\MVCComponent;

class WaterWaysGuideComponent extends MVCComponent implements RouterServiceInterface
{
    use RouterServiceTrait;
}
