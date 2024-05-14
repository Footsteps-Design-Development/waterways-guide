<?php

/**
 * @version     1.0.0
 * @package     com_waterways_guide
 * @copyright   Copyright (C) 2024. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Russell English
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$config = Factory::getConfig();
$doc = Factory::getDocument();
$mailOn = Factory::getConfig()->get('mailonline') == '1';

$app = Factory::getApplication('com_waterways_guide');
$db = Factory::getDBO();
require_once(JPATH_COMPONENT_SITE . "/commonV3.php");
$user = Factory::getUser();
$login_memberid = $user->id;
$login_email = $user->email;

//get menu parameters
$currentMenuItem = $app->getMenu()->getActive();
$view = $currentMenuItem->getParams()->get('wwg_view');

$menu_url = substr(strstr(Uri::current(), '//'), 2);
$parentlink = Route::_('index.php?Itemid=' . Factory::getApplication()->getMenu()->getActive()->parent_id);


$doc->addStyleSheet('media/com_waterways_guide/css/wwg.css');
$doc->addScript('media/com_waterways_guide/js/wwg.js', 'text/javascript');


echo ("<h2>Waterways Guide</h2>");
?>

<form name="form" enctype="multipart/form-data" method="post">
	<table width="100%" border="0" cellpadding="3" cellspacing="1">
		<?php

		$test_vars = (array(
			'country',
			'country_tmp',
			'waterway',
			'waterway_tmp',
			'guidetable',
			'guideid',
			'guideno',
			'guideaction',
			'lastguideaction',
			'infoid',
			'GuideMooringCodes',
			'GuideHazardCodes',
			'filteroption',
			'editpage',
			'message',
			'filter',
			'positionaction',
			'thisid',
			'Status0',
			'Status1',
			'Status2',
			'thiscountry',
			'outputlistresults',
			'section',
			'num_rows',
			'GuideRatingIcon',
			'sections',
			'where',
			'filterwhere',
			'CatHelp',
			'GuideName',
			'GuideRating0',
			'GuideRating1',
			'GuideRating2',
			'GuideRating3',
			'GuideRating4',
			'GuideRef',
			'GuideLocation',
			'info',
			'errmsg',
			'status',
			'listresults',
			'ChangeGuideName',
			'ChangeGuideCategoryDesc',
			'ChangeGuideOrder',
			'ChangeGuideRating',
			'ChangeGuideLat',
			'ChangeGuideLong',
			'ChangeGuideRef',
			'ChangeGuideLocation',
			'ChangeGuideMooring',
			'ChangeGuideFacilities',
			'ChangeGuideCosts',
			'ChangeGuideAmenities',
			'ChangeGuideContributors',
			'ChangeGuideSummary',
			'ChangeGuideRemarks',
			'submitteremail',
			'submitterid',
			'GuideCountry',
			'GuideWaterway',
			'GuideVer',
			'GuideStatus',
			'GuideEditorMemNo',
			'GuideCategory',
			'GuideCategoryDesc',
			'GuideOrder',
			'GuideRating',
			'GuideLatLong',
			'GuideLat',
			'GuideLong',
			'boxes',
			'boxestitle',
			'GuideContributors',
			'GuideSummary',
			'GuideRemarks',
			'GuidePostingDate',
			'GuideUpdatedisplay',
			'GuideCodes',
			'GuideNo',
			'GuideMooring',
			'GuideFacilities',
			'GuideCosts',
			'GuideAmenities',
			'GuideDocs',
			'footermooringsguide',
			'guiderequestmethod',
			'GuideMessage',
			'menu',
			'admin'
		));
		foreach ($test_vars as $test_var) {
			if (!$$test_var =  $app->input->getString($test_var)) {
				$$test_var = "";
			}
		}

		if (!$guidetable) {
			$guidetable = "#__waterways_guide";
		}


		if ($country_tmp) {
			$country = $country_tmp;
		}
		if ($waterway_tmp) {
			$waterway = $waterway_tmp;
		}
		if ($guideaction == "map" || $guideaction == "map_edit" || $guideaction == "memberedit" || $guideaction == "edit" || $positionaction == "map") {
		?>

		<?php
		}

		if ($view == "admin_edit") {

			$admin = "open";
		}

		//---------------------------------------guide submission approve / reject---------------------------------------------
		if ($guideaction == "approvesubmission" || $guideaction == "rejectsubmission") {
			include("guide-submission-approve-reject.php");
		}

		//---------------------------------------guide member save---------------------------------------------
		if ($guideaction == "membersave") {
			include("member-guide-save.php");
		}

		//---------------------------------------guide save admin only---------------------------------------------
		if ($guideaction == "save") {
			include("admin-guide-save.php");
		}

		//---------------------------------------guide remove---------------------------------------------
		if ($guideaction == "remove") {
			include("guide-remove.php");
		}

		//---------------------------------------List countries---------------------------------------------
		if ($guideaction == "" || $guideaction == "waterways" || ($guideaction == "tick_filter") || $guideaction == "savepdflist" || $guideaction == "adminreports") {
			include("list-countries.php");
		}


		//---------------------------------------List waterways---------------------------------------------
		if ($guideaction == "waterways" || ($guideaction == "tick_filter") || $guideaction == "savepdflist" || $guideaction == "adminreports") {
			include("list-waterways.php");
		}

		//---------------------------------------Show facility tick box filter---------------------------------------------
		if ($guideaction == "waterways" || ($guideaction == "tick_filter") || $guideaction == "savepdflist" || $guideaction == "adminreports") {
			include("show-facility-tick-box-filter.php");
		}

		//---------------------------------------List guides---------------------------------------------
		if ($guideaction == "list") {
			include("list-guides.php");
		}

		//---------------------------------------View map of guides---------------------------------------------
		if ($guideaction == "map") {
			include("view-map-of-guides.php");
		}

		//---------------------------------------guide details---------------------------------------------
		if ($guideaction == "detail") {
			include("guide-details.php");
		}

		//--------------------------------------- admin edit or add new---------------------------------------------
		if ($guideaction == "edit") {
			include("admin-edit-or-add-new.php");
		}

		//---------------------------------------member edit or add new---------------------------------------------
		if ($guideaction == "memberedit") {
			include("member-edit-or-add-new.php");
		}

		//---------------------------------------admin reports---------------------------------------------
		if ($guideaction == "adminreports") {
			include("guides_adminreports.php");
		}


		?>
	</table>
	<?php
	echo ("<input name=\"guideid\" type=\"hidden\" value=\"\">\n");
	echo ("<input name=\"guideaction\" type=\"hidden\" value=\"$guideaction\">\n");
	echo ("<input name=\"lastguideaction\" type=\"hidden\" value=\"$lastguideaction\">\n");
	echo ("<input name=\"infoid\" type=\"hidden\" value=\"$infoid\">\n");
	echo ("<input name=\"GuideMooringCodes\" type=\"hidden\" value=\"$GuideMooringCodes\">\n");
	echo ("<input name=\"GuideHazardCodes\" type=\"hidden\" value=\"$GuideHazardCodes\">\n");
	echo ("<input name=\"login_memberid\" type=\"hidden\" value=\"$login_memberid\">\n");
	?>
</form>
<?php
if ($guideaction == "map") {
	//google map javascript code has to be here at end of body as it will not work inside <td> No problem with FFox
	//only load it if a map is called for
	include("guides_map.js");
}
if ($guideaction == "map_edit") {
	//google map javascript code has to be here at end of body as it will not work inside <td> No problem with FFox
	//only load it if a map is called for
	include("guides_edit.js");
}
if ($guideaction == "memberedit" || $guideaction == "edit") {
	//google map javascript code has to be here at end of body as it will not work inside <td> No problem with FFox
	//only load it if a map is called for

	include("guides_memberedit.js");
}
if ($positionaction == "map") {
	//google map javascript code has to be here at end of body as it will not work inside <td> No problem with FFox
	//only load it if a map is called for
	include("../../wwg/tmpl/position_map.js");
}
