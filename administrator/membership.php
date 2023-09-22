<?php
/**
 * @version     1.0.0
 * @package     com_membership
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by com_combuilder - http://www.notwebdesign.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

// Redirect to Joomla Administrator

Factory::getApplication()->redirect(Uri::base());