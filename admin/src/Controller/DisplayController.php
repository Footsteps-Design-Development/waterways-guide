<?php

namespace Joomla\Component\WaterWaysGuide\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseFactory;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\Mysqli;
use Joomla\CMS\User\User;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
/**
 * @package     Joomla.Administrator
 * @subpackage  com_membermojo
 *
 * @copyright   Copyright (C) 2020 John Smith. All rights reserved.
 * @license     GNU General Public License version 3; see LICENSE
 */

/**
 * Default Controller of MemberMojo component
 *
 * @package     Joomla.Administrator
 * @subpackage  com_membermojo
 */
class DisplayController extends BaseController {
    /**
     * The default view for the display method.
     *
     * @var string
     */
    protected $default_view = 'wwg';

    public function display($cachable = false, $urlparams = array()) {
        return parent::display($cachable, $urlparams);
    }
}
