<?php
/**
 * @version     1.0.0
 * @package     com_waterways_guide
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by com_combuilder - http://www.notwebdesign.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

// Include dependancies
jimport('joomla.application.component.controller');

// Execute the task.
$controller	= BaseController::getInstance('Membership');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();