<?php

// administrator/src/Extension/Waterways_guideComponent.php
namespace Waterwaysguide\Component\Waterways_guide\Administrator\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Extension\BaseComponent;

class Waterways_guideComponent extends BaseComponent
{
    protected function createController(string $name)
    {
        $name = ucfirst($name);
        $class = "Waterwaysguide\\Component\\Waterways_guide\\Administrator\\Controller\\{$name}Controller";
        if (class_exists($class)) {
            return new $class();
        }
        return null;
    }
}
