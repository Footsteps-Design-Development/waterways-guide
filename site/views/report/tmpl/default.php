<?php
/**
 * @version     1.0.0
 * @package     com_membership
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Chris Grant www.productif.co.uk
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

$app = Factory::getApplication('com_membership');

//check access level of user
$user = Factory::getUser();
$userGroups = $user->getAuthorisedGroups();
if (in_array("8", $userGroups)) {
    $membershipadmin=true;
}else{
    $membershipadmin=false;	
}

$report  =  $app->input->get('report');
$template = $app->input->get('template');

require_once("/components/com_membership/commonV3.php");
include($report);

?>
