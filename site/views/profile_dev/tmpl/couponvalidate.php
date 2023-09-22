<?php

// no direct access


defined('_JEXEC') or die;

use Joomla\CMS\Factory;

$config = Factory::getConfig();

$mailOn = Factory::getConfig()->get('mailonline') == '1';

use Joomla\CMS\User\UserHelper;

$db = Factory::getDBO();

require_once(JPATH_COMPONENT_SITE."/commonV3.php");

getpost_ifset(array('coupon'));
  
  //if(isset($_POST['coupon']) && !empty($_POST['coupon']) ){
    $coupon = trim($_POST['coupon']);        
    $checkcoupon = "SELECT Email FROM tblMembers WHERE ID='".$coupon."'"; 
    $results_coupon = mysqli_query($db,$checkcoupon);    
    if(mysqli_num_rows($results_coupon)) {   echo true; } 
    else { echo false; }   
  //}
echo("success");
?>