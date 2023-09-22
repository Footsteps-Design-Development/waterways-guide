<?php
if(!defined( '_JEXEC')) {
	define( '_JEXEC', 1 );
	define('JPATH_BASE', strstr(__DIR__, 'public_html', true).'public_html');
	define('JPATH_COMPONENT', JPATH_BASE .'/components/com_membership');
	require_once(JPATH_BASE .'/includes/defines.php');
	require_once(JPATH_BASE .'/includes/framework.php');
}

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;
use Joomla\CMS\Session\Session;

if(Version::MAJOR_VERSION > 3) {
	$container = Factory::getContainer();
	$container->alias('session.web', 'session.web.site')
		->alias('session', 'session.web.site')
		->alias('JSession', 'session.web.site')
		->alias(Session::class, 'session.web.site')
		->alias(\Joomla\Session\Session::class, 'session.web.site')
		->alias(\Joomla\Session\SessionInterface::class, 'session.web.site');
	$app = $container->get(\Joomla\CMS\Application\SiteApplication::class);
	Factory::$application = $app;
}

$cParams = ComponentHelper::getParams('com_membership');

foreach([
	'adminemail',
	'adobehelp',
	'baseorderno',
	'bulletinemail',
	'channel',
	'classifiedemail',
	'classifiedsectionintrotext',
	'contactpage',
	'contactus',
	'copyright_guides',
	'DBABankDetails',
	'ddformpath',
	'emailfooter',
	'eventsemail',
	'eventsurl',
	'feedbackemail',
	'footer1',
	'footer2',
	'footer3',
	'footerassetcontactemail',
	'footerguide',
	'forumemail',
	'guidesemail',
	'header2',
	'linksemail',
	'memberloginurl',
	'membership_address',
	'membership_signature',
	'membershipemail',
	'membershipmailaddress',
	'message_guides',
	'message_index',
	'mooringsguidedocintrotext',
	'mooringsguidesectionintrotext',
	'paypalcancel',
	'paypalreturn',
	'paypalthanks',
	'registerurl',
	'registrationemail',
	'Services_newmember',
	'shopintro',
	'supportemail',
	'thanksforpayment_cc',
	'thanksforrenewing_cc',
	'thanksforrenewing_ch',
	'thanksforrenewing_dd',
	'thanksforrenewing_so',
	'thanksforrenewingpayment_cc',
	'thanksforsubscribing_cc',
	'thanksforsubscribing_ch',
	'thanksforsubscribing_dd',
	'thanksforsubscribing_foc',
	'thanksforsubscribing_so',
	'VesselNoKeeperMID',
	'webmasteremail'
	] as $k) $$k = $cParams->get($k);

foreach($cParams->get('subamount') as $k=>$v) {
	foreach($v as $k2=>$v2) {
		if($k2 == 'n') ${$k} = $v2;
		else ${$k.$k2} = $v2;
	}
}

//Quicklogin secret string to MD5 encrypt
$secretstring="gdprsecretstring"; 
$currentdirectory=getcwd();

$sitename = Factory::getConfig()->get('sitename');
$siteurl = Uri::getInstance()->getHost();
 
function decimal2degree($decimal_coord="",$latorlon="")	{
	//accepts a coordinate in decimal format and returns the equivalent degree
	//as a string
	$degrees = abs(intval($decimal_coord)).'&deg;'.(($decimal_coord * 60) % 60).'&apos;'.number_format(fmod($decimal_coord * 3600, 60), 2).'"';
	if($latorlon == 'LAT') return $degrees.($decimal_coord < 0 ? 'S' : 'N');
	elseif($latorlon == 'LON') return $degrees.($decimal_coord < 0 ? 'W' : 'E');
	else return 'N/A';
}


function unEscape($codedString)
{
	return preg_replace(["#", "~", "\^", "&", "£"], ["\"", ",", "<br>", "&amp;", "&pound;"], $codedString);
}

function check_login($thislevel){
	if (!isset($_SESSION['useraccess'])) return false;
	if (!isset($_SESSION['login_memberid'])) return false;
	if ($thislevel=="admin" && $_SESSION['level'] >= 55){
		return true;
	}elseif ($thislevel=="editor" && $_SESSION['level'] >= 50){
		return true;
	}elseif ($thislevel=="editor_page" && $_SESSION['level'] >= 45){
		return true;
	}
}

//register variables functions http://www.zend.com/zend/art/art-sweat4.php

function register($varname, $defval=NULL) { 
	if (array_key_exists($varname, $_SERVER)) { 
		$retval = $_SERVER[$varname]; 
	} elseif (array_key_exists($varname, $_COOKIE)) { 
		$retval = $_COOKIE[$varname]; 
	} elseif (array_key_exists($varname, $_POST)) { 
		$retval = $_POST[$varname]; 
	} elseif (array_key_exists($varname, $_GET)) { 
		$retval = $_GET[$varname]; 
	} elseif (array_key_exists($varname, $_ENV)) { 
		$retval = $_ENV[$varname]; 
	} else { 
		$retval = $defval; 
	} 
	return $retval; 
}
	
function getpost_ifset($test_vars){ 
	if (!is_array($test_vars)) { 
		$test_vars = array($test_vars); 
	} 
	 
	foreach($test_vars as $test_var) { 
		if (isset($_POST[$test_var])) { 
			global $$test_var; 
			$$test_var = $_POST[$test_var]; 
		} elseif (isset($_GET[$test_var])) { 
			global $$test_var; 
			$$test_var = $_GET[$test_var]; 
		} 
	} 
}


function split_date($dt) 
{ 
	$elements = explode(" ", $dt);
	$dateElements = explode("-", $elements[0]);
    return $dateElements; 
} 

# This function just takes the date/time and strips the time off to leave only the date

function date_to_human($dt) 
{ 
	$elements = explode(" ", $dt);
    return split_date($elements[0]);
} 

function date_to_format($dt,$format) { 
	$dateEnum = ['dt'=>'jS F, Y - h:ma', 'ymd'=>'Y-m-d', 'dmy'=>'d-m-Y'];
	if(isset($dateEnum[$format])) return date($dateEnum[$format], strtotime($dt));
	else return date("jS F, Y", strtotime($dt));
}
