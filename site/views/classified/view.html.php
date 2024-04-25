<?php
/**
 * @version     1.0.0
 * @package     com_waterways_guide
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by com_combuilder - http://www.notwebdesign.com
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\Registry\Registry;

/**
 * HTML View class for the Tags component
 *
 * @since  3.1
 */
class MembershipViewClassified extends HtmlView
{
	protected $state;
	protected $item;

	function display($tpl = null)
	{
		$app		= Factory::getApplication();
		$params		= $app->getParams();

		// Get some data from the models
		$state		= $this->get('State');
		$item		= $this->get('Item');

        parent::display($tpl);

	}
}