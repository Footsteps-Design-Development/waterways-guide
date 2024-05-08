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
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model;

jimport('joomla.application.component.model');
jimport('joomla.application.component.helper');

Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_waterways_guide/tables');

/**
 * Model
 */
class MembershipModelMembership extends BaseModel
{
	protected $_item;

	/**
	 * Get the data for a banner
	 */
	function &getItem()
	{
		if (!isset($this->_item))
		{
			$cache = Factory::getCache('com_waterways_guide', '');

			$id = $this->getState('membership.id');

			$this->_item =  $cache->get($id);

			if ($this->_item === false) {
				
                // redirect to banner url
				$db		= $this->getDbo();
				$query	= $db->getQuery(true);
				$query->select(
					'a.*'
					);
				$query->from('#__users as a');
				$query->where('a.id = ' . (int) $id);

				$db->setQuery((string)$query);
				if (!$db->query()) {
					throw new \Exception($db->getErrorMsg(), 500);
				}

				$this->_item = $db->loadObject();
				$cache->store($this->_item, $id);
			}
		}
		return $this->_item;
	}

}

