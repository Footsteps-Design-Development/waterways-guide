<?php
/**
 * @version     1.0.0
 * @package     com_membership
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by com_combuilder - http://www.notwebdesign.com
 */

/**
 * @param	array	A named array
 * @return	array
 */
function MembershipBuildRoute(&$query)
{
	$segments = array();

	if (isset($query['task'])) {
		$segments[] = $query['task'];
		unset($query['task']);
		if (isset($query['id'])) $segments[] = $query['id'];
	}
	if (isset($query['id'])) {
		unset($query['id']);
	}

    if(isset($query['form'])){
        unset($query['form']);
    }

    if(isset($query['view'])){
        unset($query['view']);
    }

	return $segments;
}

/**
 * @param	array	A named array
 * @param	array
 *
 * Formats:
 *
 * index.php?/banners/task/id/Itemid
 *
 * index.php?/banners/id/Itemid
 */
function MembershipParseRoute($segments)
{
	$vars = array();

	// view is always the first element of the array
	$count = count($segments);

	if ($count)
	{
		$count--;
		$segment = array_shift($segments);
		if (is_numeric($segment)) {
			$vars['id'] = $segment;
		} else {
			$vars['task'] = $segment;
		}
	}

	if ($count)
	{
		$count--;
		$segment = array_shift($segments) ;
		if (is_numeric($segment)) {
			$vars['id'] = $segment;
		}
	}

	return $vars;
}
