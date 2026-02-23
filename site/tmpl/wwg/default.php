<?php

/**
 * @version     5.0.0
 * @package     com_waterways_guide waterwaysguide
 * @copyright   Copyright (C) 2020. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Chris Grant www.productif.co.uk
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\WaterWaysGuide\Site\Helper\WaterwaysHelper;

$app = Factory::getApplication();
$config = $app->getConfig();
$doc = Factory::getApplication()->getDocument();
$mailOn = $config->get('mailonline') == '1';

$db = Factory::getContainer()->get('DatabaseDriver');
$user = $app->getIdentity();
$login_memberid = $user->id;
$login_email = $user->email;

// Component parameters (previously from commonV3.php)
$cParams = WaterwaysHelper::getParams();

// Load configuration variables used by sub-templates
$mooringsguidesectionintrotext = $cParams->get('mooringsguidesectionintrotext', '');
$mooringsguidedocintrotext = $cParams->get('mooringsguidedocintrotext', '');
$copyright_guides = $cParams->get('copyright_guides', '');
$message_guides = $cParams->get('message_guides', '');
$footerguide = $cParams->get('footerguide', '');
$footermooringsguide = $cParams->get('footermooringsguide', '');

//get menu parameters
$currentMenuItem = $app->getMenu()->getActive();
$view = $currentMenuItem->getParams()->get('wwg_view');

$menu_url = substr(strstr(Uri::current(), '//'), 2);
$parentlink = Route::_('index.php?Itemid=' . Factory::getApplication()->getMenu()->getActive()->parent_id);


$doc->addStyleSheet('media/com_waterways_guide/css/wwg.css');
$doc->addStyleSheet('media/com_waterways_guide/css/guides_map.min.css');
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
			$guidetable = $db->getPrefix() . "waterways_guide";
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
			echo "<div id='guide-submission-approve-reject'>";
			include("guide-submission-approve-reject.php");
			echo '</div>';
		}

		//---------------------------------------guide member save---------------------------------------------
		if ($guideaction == "membersave") {
			echo "<div id='member-guide-save'>";
			include("member-guide-save.php");
			echo '</div>';
		}

		//---------------------------------------guide save admin only---------------------------------------------
		if ($guideaction == "save") {
			echo "<div id='admin-guide-save'>";
			include("admin-guide-save.php");
			echo '</div>';
		}

		//---------------------------------------guide remove---------------------------------------------
		if ($guideaction == "remove") {
			echo "<div id='guide-remove'>";
			include("guide-remove.php");
			echo '</div>';
		}

		//---------------------------------------List countries---------------------------------------------
		if ($guideaction == "" || $guideaction == "waterways" || ($guideaction == "tick_filter") || $guideaction == "savepdflist" || $guideaction == "adminreports") {
			echo "<div id='list-countries'>";
			include("list-countries.php");
			echo '</div>';
		}


		//---------------------------------------List waterways---------------------------------------------
		if ($guideaction == "waterways" || ($guideaction == "tick_filter") || $guideaction == "savepdflist" || $guideaction == "adminreports") {
			echo "<div id='list-waterways'>";
			include("list-waterways.php");
			echo '</div>';
		}

		//---------------------------------------Show facility tick box filter---------------------------------------------
		if ($guideaction == "waterways" || ($guideaction == "tick_filter") || $guideaction == "savepdflist" || $guideaction == "adminreports") {
			echo "<div id='show-facility-tick-box-filter'>";
			include("show-facility-tick-box-filter.php");
			echo '</div>';
		}

		//---------------------------------------List guides---------------------------------------------
		if ($guideaction == "list") {
			echo "<div id='list-guides'>";
			include("list-guides.php");
			echo '</div>';
		}

		//---------------------------------------View map of guides---------------------------------------------
		if ($guideaction == "map") {
			echo "<div id='view-map-of-guides'>";
			include("view-map-of-guides.php");
			echo '</div>';
		}

		//---------------------------------------guide details---------------------------------------------
		if ($guideaction == "detail") {
			echo "<div id='guide-details'>";
			include("guide-details.php");
			echo '</div>';
		}

		//--------------------------------------- admin edit or add new---------------------------------------------
		if ($guideaction == "edit") {
			echo "<div id='admin-edit-or-add-new'>";
			include("admin-edit-or-add-new.php");
			echo '</div>';
		}

		//---------------------------------------member edit or add new---------------------------------------------
		if ($guideaction == "memberedit") {
			echo "<div id='member-edit-or-add-new'>";
			include("member-edit-or-add-new.php");
			echo '</div>';
		}

		//---------------------------------------admin reports---------------------------------------------
		if ($guideaction == "adminreports") {
			echo "<div id='guides_adminreports'>";
			include("guides_adminreports.php");
			echo '</div>';
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
