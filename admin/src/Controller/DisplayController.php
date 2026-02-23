<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
    protected $option = 'com_waterways_guide';
    protected $default_view = 'guides';

    public function display($cachable = false, $urlparams = []): static
    {
        return parent::display($cachable, $urlparams);
    }
}
