<?php

namespace Joomla\Component\WaterWaysGuide\Administrator\View\Config;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;

/**
 * @package     Joomla.Administrator
 * @subpackage  com_usersync
 *
 * @copyright   Copyright (C) 2020 John Smith. All rights reserved.
 * @license     GNU General Public License version 3; see LICENSE
 */

/**
 * Main "Member Mojo" Admin View
 */
class HtmlView extends BaseHtmlView {

    /**
     * Display the main "Member Mojo" view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     * @return  void
     */
    function display($tpl = null) {


        parent::display($tpl);

        $this->addToolbar();

    }
    protected function addToolbar()
    {

        $toolbar   = Toolbar::getInstance();

        $toolbar->apply('config.save');

    }

}
