<?php
/**
 * @version     1.0.0
 * @package     com_membership
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by com_combuilder - http://www.notwebdesign.com
 */
 

/**
 * Membership Component Controller
 *
 * @since  3.1
 */
 
use Joomla\CMS\Factory;
 
class MembershipController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean        $cachable   If true, the view output will be cached
	 * @param   mixed|boolean  $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController  This object to support chaining.
	 *
	 * @since   3.1
	 
	public function display($cachable = true, $urlparams = false)
	{
		$user = Factory::getUser();

		// Set the default view name and format from the Request.
		$vName = $this->input->get('view', 'tags');
		$this->input->set('view', $vName);

		if ($user->get('id') ||($this->input->getMethod() == 'POST' && $vName = 'tags'))
		{
			$cachable = false;
		}

		$safeurlparams = array(
			'id'               => 'ARRAY',
			'type'             => 'ARRAY',
			'limit'            => 'UINT',
			'limitstart'       => 'UINT',
			'filter_order'     => 'CMD',
			'filter_order_Dir' => 'CMD',
			'lang'             => 'CMD'
		);

		return parent::display($cachable, $safeurlparams);
		
	}*/
}







// No direct access (old J2.5)
/*defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

class MembershipController extends JController
{

}*/