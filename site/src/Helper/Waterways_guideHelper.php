<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Waterways_guide
 * @author     Russell English <russell@footsteps-design.co.uk>
 * @copyright  2024 Russell English
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Waterwaysguide\Component\Waterways_guide\Site\Helper;

defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Class Waterways_guideFrontendHelper
 *
 * @since  1.0.0
 */
class Waterways_guideHelper
{
	

	/**
	 * Gets the files attached to an item
	 *
	 * @param   int     $pk     The item's id
	 *
	 * @param   string  $table  The table's name
	 *
	 * @param   string  $field  The field's name
	 *
	 * @return  array  The files
	 */
	public static function getFiles($pk, $table, $field)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query
			->select($field)
			->from($table)
			->where('id = ' . (int) $pk);

		$db->setQuery($query);

		return explode(',', $db->loadResult());
	}

	/**
	 * Gets the edit permission for an user
	 *
	 * @param   mixed  $item  The item
	 *
	 * @return  bool
	 */
	public static function canUserEdit($item)
	{
		$permission = false;
		$user       = Factory::getApplication()->getIdentity();

		if ($user->authorise('core.edit', 'com_waterways_guide') || (isset($item->created_by) && $user->authorise('core.edit.own', 'com_waterways_guide') && $item->created_by == $user->id) || $user->authorise('core.create', 'com_waterways_guide'))
		{
			$permission = true;
		}

		return $permission;
	}
}
