<?php

/**
 * @version     3.0.0
 * @package     com_membership waterwaysguide
 * @copyright   Copyright (C) 2020. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Chris Grant www.productif.co.uk
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$config = Factory::getConfig();
$mailOn = Factory::getConfig()->get('mailonline') == '1';

$app = Factory::getApplication('com_membership');
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

echo ("<h2>Waterways Guide</h2>");
?>

<style type="text/css" media="screen,projection">
	.table_admin_profile {
		background-color: #FFFFCC;
		color: #333333;
		font-size: 95%;

	}

	.formcontrol {
		font-family: Arial, Helvetica, sans-serif;
		font-size: 95%;
		font-weight: lighter;
	}

	.formtextarea {
		font-family: Arial, Helvetica, sans-serif;
		font-size: 95%;
		font-weight: lighter;
		height: 120px;
		width: 100%;
	}

	.table_stripe_odd {
		background-color: #DDDDDD;
		color: #333333;
		font-size: 95%;
	}

	.table_stripe_odd a:link {
		color: #333333;
	}

	.table_stripe_odd a:visited {
		color: #333333;
	}

	.table_stripe_odd a:hover {
		color: #FAA637;
	}

	.table_stripe_even {
		background-color: #CCEEF7;
		color: #333333;
		font-size: 95%;
	}

	.table_stripe_even a:link {
		color: #333333;
	}

	.table_stripe_even a:visited {
		color: #333333;
	}

	.table_stripe_even a:hover {
		color: #FAA637;
	}

	.trailer {
		color: #333333;
		font-size: 95%;
		font-weight: normal;
		margin-left: 6px;
		border-bottom: 8px solid #ffffff;
		border-top: 0px solid #cccccc;
		border-left: 0px solid #06A8D9;
		border-right: 0px solid #06A8D9;
	}

	.trailer a:link {
		color: #333333;
		text-decoration: none
	}

	.trailer a:visited {
		color: #333333;
		text-decoration: none
	}

	.trailer a:hover {
		color: #FAA637;
		text-decoration: none
	}

	.guidechange {
		margin-top: 6px;
		padding: 3px;
		background: #ffff00;
		font-size: 95%;
	}

	.button_action {
		width: 150px !important;
		margin-right: 4px;
		margin-bottom: 4px;
</style>

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
			$guidetable = "tblGuides";
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

			$updates = 0;
			$GuideUpdate = date("Y-m-d H:i:s");
			if (!$GuidePostingDate) {
				$GuidePostingDate = $GuideUpdate;
			}
			$updatetext = "";

			//Concatinate codes


			if ($guideaction == "approvesubmission") {
				//make this version live
				$GuideStatus = 1; //live
				$GuideWaterway = str_replace(chr(146), chr(39), $GuideWaterway); //`'
				$GuideWaterway = str_replace(chr(34), chr(39), $GuideWaterway); //"'
				$update = new \stdClass();
				$update->GuideCountry = $GuideCountry;
				$update->GuideNo = $GuideNo;
				$update->GuideVer = $GuideVer;
				$update->GuideWaterway = addslashes($GuideWaterway);
				$update->GuideSummary = addslashes($GuideSummary);
				$update->GuideName = addslashes($GuideName);
				$update->GuideRef = addslashes($GuideRef);
				$update->GuideOrder = addslashes($GuideOrder);
				$update->GuideLatLong = addslashes($GuideLatLong);
				$update->GuideLocation = addslashes($GuideLocation);
				$update->GuideMooring = addslashes($GuideMooring);
				$update->GuideFacilities = addslashes($GuideFacilities);
				$update->GuideCodes = addslashes($GuideCodes);
				$update->GuideCosts = addslashes($GuideCosts);
				//RUSSELL ADDED THE FOLLOWING
				$update->GuideRating = $GuideRating;
				//ENDS
				$update->GuideAmenities = addslashes($GuideAmenities);
				$update->GuideContributors = addslashes($GuideContributors);
				$update->GuideRemarks = addslashes($GuideRemarks);
				$update->GuideLat = $GuideLat;
				$update->GuideLong = $GuideLong;
				$update->GuideDocs = $GuideDocs;
				$update->GuideCategory = addslashes($GuideCategory);
				$update->GuidePostingDate = $GuidePostingDate;
				$update->GuideStatus = $GuideStatus;
				$update->GuideUpdate = $GuideUpdate;
				$update->GuideID = $infoid;
				$update = $db->updateObject($guidetable, $update, 'GuideID');
				if (!$update) {
					echo ("Couldn't update guide ");
				} else {
					$updates = 1;
					$changelogtext = "Guide " . $GuideNo . " - '" . $GuideName . "' Version " . $GuideVer . " (" . $GuideWaterway . ")";
					$linkinfoid = $infoid;
				}
				$updates = 1;
				if ($GuideVer > 1) {
					//archive previous
					$GuideVer -= 1;
					$GuideStatus = 2; //archive
					$query = $db->getQuery(true)
						->update($db->qn($guidetable))
						->set($db->qn('GuideStatus') . ' = ' . $db->q($GuideStatus))
						->where($db->qn('GuideNo') . ' = ' . $db->q($GuideNo))
						->where($db->qn('GuideVer') . ' = ' . $db->q($GuideVer));
					$update = $db->setQuery($query)->execute();
					if (!$update) {
						echo ("Couldn't update guide ");
					} else {
						//$changelogtext.="\nGuide - '".$GuideName."' (".$GuideWaterway.") Version ".$GuideVer." archived";
						$updates = 1;
					}
				}
			} elseif ($guideaction == "rejectsubmission") {

				//...........................................................
			}
			if ($updates > 0) {

				//send to submitter if message
				if ($GuideMessage) {
					$guidesurl = $menu_url . "?guideaction=detail&infoid=" . $linkinfoid;
					$thissubject = "DBA waterways guide update";

					$thismessage = stripslashes($GuideMessage) . "\nLog in and then click this link " . $guidesurl . " to see the updated entry.";
					//$to=$submitteremail;
					$from = $guidesemail;
					$fromname = "DBA Waterways Guides Admin Administration";
					$recipient = $submitteremail;
					$subject = $thissubject;
					$body = $thismessage;

					if ($mailOn) {
						$mailer = Factory::getMailer();
						$mailer->setSender([$config->get('mailfrom'), $config->get('fromname')]);
						$mailer->addRecipient($recipient);
						$mailer->addReplyTo($from, $fromname);
						$mailer->setSubject($subject);
						$mailer->setBody(nl2br($body));
						$mailer->isHtml(true);
						$mailer->Send();
					} else echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";
				}
				//update admin log
				$subject = "Guides";
				$thischangelogtext = $changelogtext . " from member " . $GuideEditorMemNo . " approved";

				$updatetext .= stripslashes($changelogtext) . "<br>";
				$insert = new \stdClass();
				$insert->MemberID = $login_memberid;
				$insert->Subject = $subject;
				$insert->ChangeDesc = $thischangelogtext;
				$insert->ChangeDate = $GuideUpdate;
				$update = $db->insertObject('tblChangeLog', $insert);
				//update submitters log
				$thischangelogtext = $changelogtext . " approved";
				$insert = new \stdClass();
				$insert->MemberID = $submitterid;
				$insert->Subject = $subject;
				$insert->ChangeDesc = $thischangelogtext;
				$insert->ChangeDate = $GuideUpdate;
				$update = $db->insertObject('tblChangeLog', $insert);
				if (!$update) {
					echo ("Couldn't update changelog");
				}

				$message = "<font color=ff0000><b>The change history log has been updated with the following details:</b></font><br>\n";
				if ($GuideMessage) {
					$message .= $updatetext . "<br>An email has been sent to the submitter " . $submitteremail . " with the following message.<br><i>\n";
					$message .= nl2br($thismessage) . "</i>";
				} else {
					$message .= $updatetext . "<br>The submitter " . $submitteremail . " has NOT been emailed as you did not add a message,..;.<br>\n";
				}
			}

			//exit();	
			$guideaction = "list";
			//$country=$GuideCountry;
			//$waterway=$GuideWaterway;
		}

		//---------------------------------------guide member save---------------------------------------------

		if ($guideaction == "membersave") {

			$errmsg = "";

			if ($errmsg) {
				$errmsg = "Please check " . $errmsg;
				$guideaction = "edit";
			} else {
				//entry OK so update
				$updates = 0;
				$GuideUpdate = date("Y-m-d H:i:s");

				$updatetext = "";
				$subject = "Guides";
				//get info for this submitter
				if (empty($login_MembershipNo)) {
					$query = $db->getQuery(true)
						->select('*')
						->from($db->qn('tblMembers'))
						->where($db->qn('ID') . ' = ' . $db->q($login_memberid));
					$memberrow = $db->setQuery($query)->loadAssoc();
					$login_MembershipNo = $memberrow["MembershipNo"];
					$contact = $memberrow["FirstName"] . " " . $memberrow["LastName"] . ", " . $memberrow["Email"] . ", Membership No. " . $login_MembershipNo . "";
					$submitteremail = $memberrow["Email"];
					$submitterid = $memberrow["ID"];
				} else {
					$login_MembershipNo = "Unknown";
					$contact = "Unknown";
				}

				$GuideEditorMemNo = $login_MembershipNo;
				if ($infoid == "new") {
					//add new
					if (!$GuidePostingDate) {
						$GuidePostingDate = $GuideUpdate;
					}
					if ($GuideLat == "51.67256") {
						//default mid channel still there so make blank for 'unknown'
						$GuideLat = "";
						$GuideLong = "";
					}
					$GuideStatus = 0; //pending
					$GuideVer = 1; //start at v1
					$newby = 1;

					//$mytext_utf = iconv('windows-1251', 'utf-8', $mytext);
					$GuideWaterway = str_replace(chr(146), chr(39), $GuideWaterway); //`'
					$GuideWaterway = str_replace(chr(34), chr(39), $GuideWaterway); //"'
					$insert = new \stdClass();
					$insert->GuideNo = $GuideNo;
					$insert->GuideVer = $GuideVer;
					$insert->GuideCountry = addslashes($GuideCountry);
					//			$insert->GuideWaterway = addslashes($GuideWaterway);
					$insert->GuideWaterway = $GuideWaterway;
					$insert->GuideSummary = addslashes($GuideSummary);
					$insert->GuideName = addslashes($GuideName);
					$insert->GuideRef = addslashes($GuideRef);

					// Check if $GuideOrder is an empty string
					if ($GuideOrder === '') {
						// If it is, set it to NULL
						$GuideOrder = NULL;
					} else {
						// Otherwise, escape it with addslashes
						$GuideOrder = addslashes($GuideOrder);
					}

					$insert->GuideOrder = $GuideOrder;


					// REPLACED WITH THE ABOVE $insert->GuideOrder = addslashes($GuideOrder);

					$insert->GuideLatLong = addslashes($GuideLatLong);
					$insert->GuideLocation = addslashes($GuideLocation);
					$insert->GuideMooring = addslashes($GuideMooring);
					$insert->GuideFacilities = addslashes($GuideFacilities);
					$insert->GuideCodes = addslashes($GuideCodes);
					$insert->GuideCosts = addslashes($GuideCosts);
					$insert->GuideRating = addslashes($GuideRating);
					$insert->GuideAmenities = addslashes($GuideAmenities);
					$insert->GuideContributors = addslashes($GuideContributors);
					$insert->GuideRemarks = addslashes($GuideRemarks);
					$insert->GuideLat = $GuideLat;
					$insert->GuideLong = $GuideLong;
					$insert->GuideDocs = $GuideDocs;
					$insert->GuidePostingDate = $GuidePostingDate;
					$insert->GuideCategory = addslashes($GuideCategory);
					$insert->GuideUpdate = $GuideUpdate;
					$insert->GuideStatus = $GuideStatus;
					$insert->GuideEditorMemNo = $GuideEditorMemNo;
					$result = $db->insertObject($guidetable, $insert, 'GuideID');
					if (!$result) {
						die("Couldn't update database");
					}
					//get ID and update GuideNo GuideVer
					$GuideVer = 1;
					$GuideNo = $insert->GuideID;
					$update = new \stdClass();
					$update->GuideNo = $GuideNo;
					$update->GuideVer = $GuideVer;
					$update->GuideID = $GuideNo;
					$result = $db->updateObject($guidetable, $update, 'GuideID');
					if (!$result) {
						echo ("Couldn't update guide ");
					} else {
						$changelogtext = "Guide " . $GuideNo . " - '" . $GuideName . "' Version " . $GuideVer . " (" . $GuideWaterway . ") submitted for approval";
						$updates = 1;
						$linkinfoid = $GuideNo;
					}
				} elseif ($infoid > 0) {

					$GuideStatus = 0; //pending
					//$GuideNo=$infoid; //from previous version
					$GuideVer += 1; //up the version

					$GuideWaterway = str_replace(chr(146), chr(39), $GuideWaterway); //`'
					$GuideWaterway = str_replace(chr(34), chr(39), $GuideWaterway); //"'
					$insert = new \stdClass();
					$insert->GuideNo = $GuideNo;
					$insert->GuideVer = $GuideVer;
					$insert->GuideCountry = addslashes($GuideCountry);
					$insert->GuideWaterway = addslashes($GuideWaterway);
					$insert->GuideSummary = addslashes($GuideSummary);
					$insert->GuideName = addslashes($GuideName);
					$insert->GuideRef = addslashes($GuideRef);
					$insert->GuideOrder = addslashes($GuideOrder);
					$insert->GuideLatLong = addslashes($GuideLatLong);
					$insert->GuideLocation = addslashes($GuideLocation);
					$insert->GuideMooring = addslashes($GuideMooring);
					$insert->GuideFacilities = addslashes($GuideFacilities);
					$insert->GuideCodes = addslashes($GuideCodes);
					$insert->GuideCosts = addslashes($GuideCosts);
					// $insert->GuideRating = addslashes($GuideRating);

					// Debugging before setting GuideRating
					error_log("Debug: GuideRating before setting: " . var_export($GuideRating, true));
					error_log("Debug: Full insert object: " . var_export($insert, true));


					// Explicitly handle NULL and empty strings
					if ($GuideRating === NULL || $GuideRating === '') {
						$GuideRating = 0;
					}

					$insert->GuideRating = (int) $GuideRating;

					// Debugging after setting GuideRating
					error_log("Debug: GuideRating after setting: " . var_export($insert->GuideRating, true));
					error_log("Debug: Type of GuideRating: " . gettype($GuideRating));


					$insert->GuideAmenities = addslashes($GuideAmenities);
					$insert->GuideContributors = addslashes($GuideContributors);
					$insert->GuideRemarks = addslashes($GuideRemarks);
					$insert->GuideLat = $GuideLat;
					$insert->GuideLong = $GuideLong;
					$insert->GuideDocs = $GuideDocs;
					$insert->GuidePostingDate = $GuidePostingDate;
					$insert->GuideCategory = addslashes($GuideCategory);
					$insert->GuideUpdate = $GuideUpdate;
					$insert->GuideStatus = $GuideStatus;
					$insert->GuideEditorMemNo = $GuideEditorMemNo;
					$result = $db->insertObject($guidetable, $insert, 'GuideID');
					if (!$result) {
						die("Couldn't update database");
					}
					$linkinfoid = $insert->GuideID;
					$changelogtext = "Guide " . $GuideNo . " - '" . $GuideName . "' Version " . $GuideVer . " (" . $GuideWaterway . ") submitted for approval";
					$updates = 1;
				}
				if ($updates > 0) {

					//send to editor for approval

					$guidesurl = $menu_url . "?guideaction=edit&infoid=" . $linkinfoid;
					$thissubject = "DBA waterways guide member update";
					$thismessage = "A waterways guide update, '" . stripslashes($changelogtext) . "', has been made on the " . $sitename . " website.
			\n\nSubmitter: " . $contact . "\n\nLog in as waterways guide administrator and then click this link " . $guidesurl . " to check and approve.";

					$to = $guidesemail;
					$from = $guidesemail;
					$fromname = "DBA Waterways Guides Administration";
					$recipient = $guidesemail;
					$subject = $thissubject;
					$body = $thismessage;

					if ($mailOn) {
						$mailer = Factory::getMailer();
						$mailer->setSender([$config->get('mailfrom'), $config->get('fromname')]);
						$mailer->addRecipient($recipient);
						$mailer->addReplyTo($from, $fromname);
						$mailer->setSubject($subject);
						$mailer->setBody(nl2br($body));
						$mailer->isHtml(true);
						$mailer->Send();
					} else echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";
					//update log
					$subject = "Guides";
					$updatetext .= stripslashes($changelogtext) . "<br>";
					$insert = new \stdClass();
					$insert->MemberID = $login_memberid;
					$insert->Subject = $subject;
					$insert->ChangeDesc = $changelogtext;
					$insert->ChangeDate = $GuideUpdate;
					$update = $db->insertObject('tblChangeLog', $insert);
					if (!$update) {
						echo ("Couldn't update changelog");
					} else {
						$message = "<font color=ff0000><b>The change history log has been updated with the following details:<br></font>\n";
						$message .= $updatetext . "<br>Many thanks for your submission which is now pending approval</b>. You will receive an email as soon as your update has been checked by the editor and added into the live guide.\n";
					}
				}

				//exit();	
				if ($lastguideaction == "list") {
					$guideaction = "list";
				} elseif ($lastguideaction == "map") {
					$guideaction = "map";
				} else {
					$guideaction = "list";
				}
				$country = $GuideCountry;
				$waterway = $GuideWaterway;
			}
		}



		//---------------------------------------guide save admin only---------------------------------------------

		if ($guideaction == "save") {

			$errmsg = "";
			if (!$GuideCountry) {
				if ($errmsg) {
					$errmsg .= ", ";
				}
				$errmsg .= " Guide Country";
			}
			if (!$GuideWaterway) {
				if ($errmsg) {
					$errmsg .= ", ";
				}

				$errmsg .= " Guide Waterway";
			}
			if (!$GuideName) {
				if ($errmsg) {
					$errmsg .= ", ";
				}
				$errmsg .= " Guide Name";
			}

			if ($errmsg) {
				$errmsg = "Please check " . $errmsg;
				$guideaction = "edit";
			} else {
				//entry OK so update
				$updates = 0;
				$GuideUpdate = date("Y-m-d H:i:s");
				if (!$GuidePostingDate) {
					$GuidePostingDate = $GuideUpdate;
				}
				$updatetext = "";
				$subject = "Guides";


				if ($infoid == "newmooring" || $infoid == "newhazard") {
					//add new
					$GuideStatus = 1; //live
					$GuideVer = 1; //start at v1
					if ($GuideLat == "51.67256") {
						//default mid channel still there so make blank for 'unknown'
						$GuideLat = "";
						$GuideLong = "";
					}
					$newby = 1;
					$GuideEditorMemNo = $login_MembershipNo;

					$GuideWaterway = str_replace(chr(146), chr(39), $GuideWaterway); //`'
					$GuideWaterway = str_replace(chr(34), chr(39), $GuideWaterway); //"'
					$insert = new \stdClass();
					$insert->GuideCountry = addslashes($GuideCountry);
					$insert->GuideWaterway = addslashes($GuideWaterway);
					$insert->GuideSummary = addslashes($GuideSummary);
					$insert->GuideName = addslashes($GuideName);
					$insert->GuideRef = addslashes($GuideRef);
					$insert->GuideOrder = addslashes($GuideOrder);
					$insert->GuideLatLong = addslashes($GuideLatLong);
					$insert->GuideLocation = addslashes($GuideLocation);
					$insert->GuideMooring = addslashes($GuideMooring);
					$insert->GuideFacilities = addslashes($GuideFacilities);
					$insert->GuideCodes = addslashes($GuideCodes);
					$insert->GuideCosts = addslashes($GuideCosts);
					$insert->GuideRating = addslashes($GuideRating);
					$insert->GuideAmenities = addslashes($GuideAmenities);
					$insert->GuideContributors = addslashes($GuideContributors);
					$insert->GuideRemarks = addslashes($GuideRemarks);
					$insert->GuideLat = $GuideLat;
					$insert->GuideLong = $GuideLong;
					$insert->GuideDocs = $GuideDocs;
					$insert->GuidePostingDate = $GuidePostingDate;
					$insert->GuideCategory = addslashes($GuideCategory);
					$insert->GuideUpdate = $GuideUpdate;
					$insert->GuideStatus = $GuideStatus;
					$insert->GuideEditorMemNo = $GuideEditorMemNo;
					//$result = $db->insertObject($guidetable, $insert, 'GuideID');

					// Check if GuideOrder is empty or not a numeric value
					if (empty($insert->GuideOrder) || !is_numeric($insert->GuideOrder)) {
						// Set a default value
						$insert->GuideOrder = 1.00;
					}

					$result = $db->insertObject($guidetable, $insert, 'GuideID');

					if (!$result) {
						die("Couldn't update database");
					}
					//get ID and update GuideNo GuideVer
					$GuideNo = $insert->GuideID;
					$update = new \stdClass();
					$update->GuideNo = $GuideNo;
					$update->GuideVer = $GuideVer;
					$update->GuideID = $GuideNo;
					$result = $db->updateObject($guidetable, $update, 'GuideID');
					if (!$result) {
						echo ("Couldn't update guide ");
					}
					if ($GuideCategory == 2) {
						$changelogtext = "Guide hazard - '" . $GuideName . "'(" . $GuideWaterway . ") added";
					} else {
						$changelogtext = "Guide mooring - '" . $GuideName . "'(" . $GuideWaterway . ") added";
					}
					$updates = 1;
				} elseif ($infoid > 0) {
					//admin update so leave GuideVer, GuideStatus, GuideEditorMemNo as is

					//$target_encoding="UTF-8";
					//$GuideWaterway=convert_to ($GuideWaterway, $target_encoding);
					$GuideWaterway = str_replace(chr(146), chr(39), $GuideWaterway); //`'
					$GuideWaterway = str_replace(chr(34), chr(39), $GuideWaterway); //"'
					$update = new \stdClass();
					$update->GuideCountry = addslashes($GuideCountry);
					$update->GuideWaterway = addslashes($GuideWaterway);
					$update->GuideSummary = addslashes($GuideSummary);
					$update->GuideName = addslashes($GuideName);
					$update->GuideRef = addslashes($GuideRef);
					$update->GuideOrder = addslashes($GuideOrder);
					$update->GuideLatLong = addslashes($GuideLatLong);
					$update->GuideLocation = addslashes($GuideLocation);
					$update->GuideMooring = addslashes($GuideMooring);
					$update->GuideFacilities = addslashes($GuideFacilities);
					$update->GuideCodes = addslashes($GuideCodes);
					$update->GuideCosts = addslashes($GuideCosts);
					$update->GuideRating = $GuideRating;
					$update->GuideAmenities = addslashes($GuideAmenities);
					$update->GuideContributors = addslashes($GuideContributors);
					$update->GuideRemarks = addslashes($GuideRemarks);
					$update->GuideLat = $GuideLat;
					$update->GuideLong = $GuideLong;
					$update->GuideDocs = $GuideDocs;
					$update->GuideCategory = addslashes($GuideCategory);
					$update->GuidePostingDate = $GuidePostingDate;
					$update->GuideUpdate = $GuideUpdate;
					$update->GuideID = $infoid;
					$result = $db->updateObject($guidetable, $update, 'GuideID');
					if (!$result) {
						echo ("Couldn't update guide ");
					} else {
						$updates += 1;
						if ($GuideCategory == 2) {
							$changelogtext = "Guide hazard - '" . $GuideName . "'(" . $GuideWaterway . ") updated";
						} else {
							$changelogtext = "Guide mooring - '" . $GuideName . "'(" . $GuideWaterway . ") updated";
						}
					}
					$updates = 1;
				}
				if ($updates > 0) {


					//update log
					$updatetext .= $changelogtext . "<br>";
					$insert = new \stdClass();
					$insert->MemberID = $login_memberid;
					$insert->Subject = $subject;
					$insert->ChangeDesc = $changelogtext;
					$insert->ChangeDate = $GuideUpdate;
					$update = $db->insertObject('tblChangeLog', $insert);
					if (!$update) {
						echo ("Couldn't update changelog");
					} else {
						$message = "The change history log and site have been updated with the following details:<br>\n";
						$message .= $updatetext . "<br>\n";
					}
				}

				//exit();	
				if ($lastguideaction == "list") {
					$guideaction = "list";
				} elseif ($lastguideaction == "map") {
					$guideaction = "map";
				} else {
					$guideaction = "list";
				}
				$country = $GuideCountry;
				$waterway = $GuideWaterway;
			}
		}

		//---------------------------------------guide remove---------------------------------------------

		if ($guideaction == "remove") {
			echo ("<input name=\"cat_guides\" type=\"hidden\" value=\"$cat_guides\">");
			echo ("<input name=\"showclosed\" type=\"hidden\" value=\"$showclosed\">");

			//get details for log before deleting
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn($guidetable))
				->where($db->qn('GuideID') . ' = ' . $db->q($infoid));
			$result = $db->setQuery($query)->loadAssocList();
			$num_rows = count($result);
			if (!$num_rows) {
				echo ("<tr><td>Sorry - no details available for this guide</td></tr>");
				exit();
			}
			$row = reset($result);
			$GuideName = stripslashes($row["GuideName"]);
			$GuideWaterway = stripslashes($row["GuideWaterway"]);
			$GuideVer = stripslashes($row["GuideVer"]);
			$updates = 0;
			$changedate = date("Y-m-d H:i:s");
			$updatetext = "";
			$subject = "Guides";
			$query = $db->getQuery(true)
				->delete($db->qn($guidetable))
				->where($db->qn('GuideID') . ' = ' . $db->q($infoid));
			$update = $db->setQuery($query)->execute();
			if (!$update) {
				echo ("Couldn't delete entry");
			} else {
				$changelogtext = "Guide - '" . $GuideName . "'(" . $GuideWaterway . ") version " . $GuideVer . " removed";
			}

			$updates = 1;
			if ($updates > 0) {
				//update log
				$updatetext .= $changelogtext . "<br>";
				$insert = new \stdClass();
				$insert->MemberID = $login_memberid;
				$insert->Subject = $subject;
				$insert->ChangeDesc = $changelogtext;
				$insert->ChangeDate = $changedate;
				$update = $db->insertObject('tblChangeLog', $insert);
				if (!$update) {
					echo ("Couldn't update changelog");
				} else {
					$message = "The change history log and site have been updated with the following details:<br>\n";
					$message .= stripslashes($updatetext) . "<br>\n";
				}
			}
			if ($lastguideaction == "list") {
				$guideaction = "list";
			} elseif ($lastguideaction == "map") {
				$guideaction = "map";
			} else {
				$guideaction = "list";
			}


			$country = $GuideCountry;
			$waterway = $GuideWaterway;
		}






		//---------------------------------------List countries---------------------------------------------

		//if ($guideaction=="" || $guideaction=="waterways" || ($guideaction=="tick_filter") || $guideaction=="list" || $guideaction=="savepdflist" || $guideaction=="map" || $guideaction=="detail"  || $guideaction=="adminreports") {
		if ($guideaction == "" || $guideaction == "waterways" || ($guideaction == "tick_filter") || $guideaction == "savepdflist" || $guideaction == "adminreports") {

			if (isset($message)) {
				echo ("<tr><td colspan=4>$message<br></td></tr>\n");
			}



			//get current countries
			$query = $db->getQuery(true)
				->select('DISTINCTROW ' . $db->qn('gt.GuideCountry') . ', ' . $db->qn('c.printable_name'))
				->from($db->qn($guidetable) . ' AS ' . $db->qn('gt'))
				->innerJoin($db->qn('tblCountry', 'c') . ' ON ' . $db->qn('gt.GuideCountry') . ' = ' . $db->qn('c.iso'))
				->order($db->qn('printable_name'));
			$countries = $db->setQuery($query)->loadAssocList();
			print "<tr><td colspan=4><b>Country</b><br><select name=\"country\" class=\"formcontrol\" onChange=\"document.form.guideaction.value='waterways';document.form.submit()\">\n";
			print "<option value='All'>All</option>\n";
			foreach ($countries as $row) {
				if ($row["GuideCountry"] == $country) {
					print "<option value=\"" . $row["GuideCountry"] . "\" selected>" . $row["printable_name"] . "</option>\n";
				} else {
					print "<option  value=\"" . $row["GuideCountry"] . "\">" . $row["printable_name"] . "</option>\n";
				}
			}
			print "	</select>  <a href=\"#\" onClick=\"document.form.guideaction.value='waterways';document.form.submit()\"> <img src=\"Image/common/preview.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Find guides in this country\" alt=\"Find guides in this country\"></a></td></tr>\n";

			if (!$guideaction) {
				echo ("<tr><td colspan=4>" . $mooringsguidesectionintrotext . "</td></tr>\n");
			}
		}


		//---------------------------------------List waterways---------------------------------------------
		if ($guideaction == "waterways" || ($guideaction == "tick_filter") || $guideaction == "savepdflist" || $guideaction == "adminreports") {
			//if ($guideaction=="waterways" || ($guideaction=="tick_filter") || $guideaction=="list" || $guideaction=="savepdflist" || $guideaction=="map" || $guideaction=="detail" || $guideaction=="adminreports") {


			//get current waterways
			$query = $db->getQuery(true)
				->select('DISTINCTROW ' . $db->qn('GuideWaterway'))
				->from($db->qn($guidetable))
				->where($db->qn('GuideStatus') . ' = 1')
				->order($db->qn('GuideWaterway'));
			if ($country && $country != 'All') $query->where($db->qn('GuideCountry') . ' = ' . $db->q($country));
			$waterways = $db->setQuery($query)->loadAssocList();
			$rows = count($waterways);

			# If the search was unsuccessful then Display Message try again.
			if ($rows == 0) {
				print "<tr><td colspan=4>Sorry - there are no waterways in that country listed at the moment.</td></tr>\n";
			} else {



				print "<tr><td colspan=4><b>Waterway</b><br><select name=\"waterway\" class=\"formcontrol\" onChange=\"document.form.guideaction.value='tick_filter';document.form.submit()\">\n";

				print "<option value='All'>All</option>\n";
				foreach ($waterways as $row) {
					$GuideWaterway = $row["GuideWaterway"];
					//$debug.=",".$GuideWaterway;
					if (addslashes($GuideWaterway) == addslashes($waterway)) {
						print "<option value=\"" . ($GuideWaterway) . "\" selected>" . stripslashes($GuideWaterway) . "</option>\n";
					} else {
						print "<option value=\"" . ($GuideWaterway) . "\">" . stripslashes($GuideWaterway) . "</option>\n";
					}
				}
				print "	</select>  <a href=\"#\" onClick=\"document.form.guideaction.value='tick_filter';document.form.submit()\"> <img src=\"Image/common/preview.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Find guides on this waterway\" alt=\"Find guides on this waterway\"></a></td> </tr>\n";
			}
		}

		//---------------------------------------Show facility tick box filter---------------------------------------------
		if ($guideaction == "waterways" || ($guideaction == "tick_filter") || $guideaction == "savepdflist" || $guideaction == "adminreports") {

			//if ($guideaction=="waterways" || ($guideaction=="tick_filter") || $guideaction=="list" || $guideaction=="savepdflist" || $guideaction=="map" || $guideaction=="detail" || $guideaction=="adminreports") {


		?>
			<SCRIPT LANGUAGE="JavaScript">
				function showfilter() {
					var x = document.form.filteroption.selectedIndex;
					if (document.form.filteroption.options[x].value == "ALL") {
						document.getElementById("mooringfilter").style.display = 'block';
						document.getElementById("hazardsfilter").style.display = 'block';
					}
					if (document.form.filteroption.options[x].value == "M") {
						document.getElementById("mooringfilter").style.display = 'block';
						document.getElementById("hazardsfilter").style.display = 'none';
					}
					if (document.form.filteroption.options[x].value == "H") {
						document.getElementById("mooringfilter").style.display = 'none';
						document.getElementById("hazardsfilter").style.display = 'block';
					}

				}


				function changemooringcode(cbname, code) {

					var cur_str = document.form.GuideMooringCodes.value;
					var state = cbname.checked;
					var str_search = "|" + code + "|";
					if (state == 0) {
						//remove it
						if (str_search == cur_str) {
							//only one so make blank
							var new_str = cur_str.replace(str_search, '');
						} else {
							var new_str = cur_str.replace(str_search, '|');
						}
					} else {
						//add it
						if (cur_str) {
							//already some data so add on end
							var new_str = cur_str + code + "|";
						} else {
							var new_str = "|" + code + "|";
						}
					}

					//alert(cur_str+" - "+new_str);
					document.form.GuideMooringCodes.value = new_str;
				}

				function changehazardcode(cbname, code) {

					var cur_str = document.form.GuideHazardCodes.value;
					var state = cbname.checked;
					var str_search = "|" + code + "|";
					if (state == 0) {
						//remove it
						if (str_search == cur_str) {
							//only one so make blank
							var new_str = cur_str.replace(str_search, '');
						} else {
							var new_str = cur_str.replace(str_search, '|');
						}
					} else {
						//add it
						if (cur_str) {
							//already some data so add on end
							var new_str = cur_str + code + "|";
						} else {
							var new_str = "|" + code + "|";
						}
					}

					//alert(cur_str+" - "+new_str);
					document.form.GuideHazardCodes.value = new_str;
				}
			</script>
			<?php
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('tblServices'))
				->where($db->qn('ServiceCategory') . " = 'mooringsguides'")
				->order($db->qn('ServiceSortOrder'));
			$boxes = $db->setQuery($query)->loadAssocList();
			$boxhtml = "";
			foreach ($boxes as $boxrow) {
				$boxid = $boxrow["ServiceID"];
				$boxdesc = $boxrow["ServiceDescGB"];
				$found = strstr($GuideMooringCodes, "|" . $boxid . "|");
				if (!$found) {
					$boxhtml .= "<input type=\"checkbox\" name=\"facility" . $boxid . "\" value=\"" . $boxid . "\" onClick=\"changemooringcode(this,'" . $boxid . "')\">" . $boxdesc . "&nbsp;\n";
				} else {
					$boxhtml .= "<input type=\"checkbox\" name=\"facility" . $boxid . "\" value=\"" . $boxid . "\" checked onClick=\"changemooringcode(this,'" . $boxid . "')\">" . $boxdesc . "&nbsp;\n";
				}
			}
			//$filter.="<div id=\"mooringfilter\" style=\"margin-left:6px; background-color:dddddd; padding:6px;\"><b>Moorings</b> ".$boxhtml."</div>\n";
			$filter .= "<div id=\"mooringfilter\" style=\"margin-left:6px; background-color:dddddd; padding:6px;\">" . $boxhtml . "</div>\n";

			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('tblServices'))
				->where($db->qn('ServiceCategory') . " = 'hazardguides'")
				->order($db->qn('ServiceSortOrder'));
			$boxes = $db->setQuery($query)->loadAssocList();
			$boxhtml = "";
			foreach ($boxes as $boxrow) {
				$boxid = $boxrow["ServiceID"];
				$boxdesc = $boxrow["ServiceDescGB"];
				$found = strstr($GuideHazardCodes, "|" . $boxid . "|");
				if (!$found) {
					$boxhtml .= "<input type=\"checkbox\" name=\"facility" . $boxid . "\" value=\"" . $boxid . "\" onClick=\"changehazardcode(this,'" . $boxid . "')\">" . $boxdesc . "&nbsp;\n";
				} else {
					$boxhtml .= "<input type=\"checkbox\" name=\"facility" . $boxid . "\" value=\"" . $boxid . "\" checked onClick=\"changehazardcode(this,'" . $boxid . "')\">" . $boxdesc . "&nbsp;\n";
				}
			}
			//$filter.="<div id=\"hazardsfilter\" style=\"margin-left:6px;margin-top:2px; background-color:CCEEF7; padding:6px;\"><b>Hazards</b> ".$boxhtml."</div>\n";
			$filter .= "</td></tr>\n";
			echo ("<input name=\"filteroption\" type=\"hidden\" value=\"M\">\n");
			/*echo("<tr><td colspan=4><b>Include</b> <select name=\"filteroption\" class=\"formcontrol\" onChange=\"showfilter()\"> \n"); 
	echo("<option value='ALL'>Moorings and Hazards</option>\n");
	if($filteroption=="M"){
		echo("<option value='M' selected>Moorings</option>\n"); 
	}else{
		echo("<option value='M'>Moorings</option>\n");
	}
	if($filteroption=="H"){
		echo("<option value='H' selected>Hazards</option>\n"); 
	}else{
		echo("<option value='H'>Hazards</option>\n");
	}
	echo("</select></td> </tr>\n"); */
			echo ("<tr><td colspan=4><b>Filter:</b> - tick boxes to refine filter or leave for everything, then click List or Map to display or refresh results<br>" . $filter . "</td> </tr>\n");

			?>
			<SCRIPT LANGUAGE="JavaScript">
				showfilter();
			</script>
		<?php

			//---------------------------------------Display and Admin options---------------------------------------------


			if ($admin == "open") {
				//allow filter by status	
				if (!$Status0 && !$Status1 && !$Status2) {
					$Status1 = 1; //live default
				}
				if ($Status0 == 1) {
					$status0clicked = " checked";
				} else {
					$status0clicked = "";
				}
				if ($Status1 == 1) {
					$status1clicked = " checked";
				} else {
					$status1clicked = "";
				}
				if ($Status2 == 1) {
					$status2clicked = " checked";
				} else {
					$status2clicked = "";
				}
				print "<tr><td class=table_stripe_even colspan=4><b>Admin status filter:</b> <input type=\"checkbox\" name=\"Status0\" value=\"1\"" . $status0clicked . "> Pending <input type=\"checkbox\" name=\"Status1\" value=\"1\"" . $status1clicked . "> Live <input type=\"checkbox\" name=\"Status2\" value=\"1\"" . $status2clicked . "> Archive</td></tr>\n";
			}
			print "<tr><td colspan=4>\n";
			echo ("<input type=\"button\" class=\"btn btn-primary button_action\" name=\"list\" value=\"List\" onClick=\"document.form.guideaction.value='list';document.form.lastguideaction.value='list';document.form.submit()\">");

			//print"<a href=\"#\" onClick=\"document.form.guideaction.value='list';document.form.submit()\"><img src=\"Image/common/preview.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Find guides on this waterway\" alt=\"Find guides on this waterway\"> List </a> ";
			echo ("<input type=\"button\" class=\"btn btn-primary button_action\" name=\"map\" value=\"Map\" onClick=\"document.form.guideaction.value='map';document.form.lastguideaction.value='map';document.form.submit()\">");
			//print"<br><a href=\"#\" onClick=\"document.form.guideaction.value='map';document.form.submit()\"><img src=\"Image/common/compass.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"View guides on a map\" alt=\"View guides on a map\"> Map </a>";

			if (($country == "FR" && $waterway == "All") || ($country == "FR" && $waterway == "")) {
				echo ("<input type=\"button\" class=\"btn btn-primary button_action\" name=\"PDFfile1\" value=\"PDF text file A-L\" onClick=\"javascript:listtopdf(1)\">");
				echo ("<input type=\"button\" class=\"btn btn-primary button_action\" name=\"PDFfile2\" value=\"PDF text file M-Z\" onClick=\"javascript:listtopdf(2)\">");
				//print"<br><img src=\"Image/common/pdf.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Download PDF text file\" alt=\"Download PDF text file\"> Download PDF text file <a href=\"#\" onClick=\"javascript:listtopdf(1)\">Aa to Lys</a>&nbsp;&nbsp;<a href=\"#\" onClick=\"javascript:listtopdf(2)\">Marne to Yonne</a></a> ";

				//print"&nbsp;&nbsp;&nbsp;Save to kml <a href=\"#\" onClick=\"javascript:listtokml(1)\">Aa to Lys</a>&nbsp;&nbsp;<a href=\"#\" onClick=\"javascript:listtokml(2)\">Marne to Yonne</a><img src=\"Image/common/xml.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Save to a .kmz google maps file\" alt=\"Save to a .kmz google maps file\"></a> ";

			} else {
				//print"<br><a href=\"#\" onClick=\"javascript:listtopdf()\"><img src=\"Image/common/pdf.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Download PDF text file\" alt=\"Download PDF text file\"> Download PDF text file </a> ";
				echo ("<input type=\"button\" class=\"btn btn-primary button_action\" name=\"PDFfile0\" value=\"PDF text file\" onClick=\"javascript:listtopdf()\">");
			}
			//print"</td><td colspan=2>\n";
			//print"<br><a href=\"#\" onClick=\"javascript:listtokml()\"><img src=\"Image/common/xml.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Download KML map file\" alt=\"Download KML map file\"> Download KML map file </a> ";
			echo ("<input type=\"button\" class=\"btn btn-primary button_action\" name=\"btnlisttokml\" value=\"KML map file\" onClick=\"javascript:listtokml()\">");

			echo ("<input type=\"button\" class=\"btn btn-primary button_action\" name=\"map\" value=\"Add a new mooring\" onClick=\"document.form.guideaction.value='memberedit';document.form.infoid.value='new';document.form.submit()\">");
			//print"<br><a href=\"#\" onClick=\"document.form.guideaction.value='memberedit';document.form.infoid.value='new';document.form.submit()\"><img src=\"Image/common/new.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Add a new mooring\" alt=\"Add a new mooring\"> Add a new mooring </a>";
			echo ("<input type=\"button\" class=\"btn btn-primary button_action\" name=\"help\" value=\"Help\" onClick=\"document.form.guideaction.value='';document.form.infoid.value='new';document.form.submit()\">");
			//echo("<a href=\"http://barges.org/component/content/article/113-faq/312-help-wg\" class=\"btn btn-primary button_action\" role=\"button\">Help</a>");
			//<input type=\"button\" class=\"btn btn-primary button_action\" name=\"help\" value=\"Help\" onClick=\"document.form.guideaction.value='memberedit';document.form.infoid.value='new';document.form.submit()\">");
			//print"<br><a href=\"http://barges.org/component/content/article/113-faq/312-help-wg\"><img src=\"Image/common/help.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"help\" alt=\"help\"> Help </a>";



			print "</td></tr>\n";

			if ($admin == "open") {
				print "</td></tr><td colspan=4>\n";
				print "<br><b>ADMIN:</b> <a href=\"#\" onClick=\"document.form.guideaction.value='edit';document.form.infoid.value='newmooring';document.form.submit()\">New mooring <img src=\"Image/common/new.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Add a new mooring entry\" alt=\"Add a new mooring entry\"></a>";
				print "&nbsp;&nbsp;&nbsp;<a href=\"#\" onClick=\"document.form.guideaction.value='edit';document.form.infoid.value='newhazard';document.form.submit()\">New hazard <img src=\"Image/common/new.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Add a new hazard entry\" alt=\"Add a new hazard entry\"></a>";
				print "&nbsp;&nbsp;&nbsp;<a href=\"#\" onClick=\"document.form.guideaction.value='adminreports';document.form.submit()\">Reports (admin) <img src=\"Image/common/txt.gif\" title=\"Run reports\" alt=\"Run reports\" width=\"18\" height=\"18\" border=\"0\"></a>";
				print "</td></tr>\n";
			}
		}

		?>
		<SCRIPT LANGUAGE="JavaScript">
			function listtopdf(option) {
				var form = 'form';
				dml = document.forms[form];
				var country = dml.country.value;
				if (country == "All") {
					alert("Please select a single country for this option.");
				} else {

					var waterway = dml.waterway.value;
					var filteroption = dml.filteroption.value;
					var GuideMooringCodes = dml.GuideMooringCodes.value;
					var GuideHazardCodes = dml.GuideHazardCodes.value;
					var reportname = "/components/com_membership/views/wwg/tmpl/guides_list_to_pdf.php";
					//var reportname = "/components/com_membership/views/wwg/tmpl/guides_list_to_pdf.php";
					var msid = "<?php echo ($login_memberid); ?>";
					var menu_url = "<?php echo ($menu_url); ?>";
					//check

					if (option > 0) {

						if (option == 1) {
							//alert("Please select another country, unfortunately France is not yet available as pdf");
							var waterway1 = "Aa";
							var waterway2 = "Lys (Fr)";
						}
						if (option == 2) {
							var waterway1 = "Marne";
							var waterway2 = "Yonne";
						}
						var mypage = reportname + "?country=" + country + "&waterway=" + waterway + "&filteroption=" + filteroption + "&GuideMooringCodes=" + GuideMooringCodes + "&GuideHazardCodes=" + GuideHazardCodes + "&waterway1=" + waterway1 + "&waterway2=" + waterway2 + "&msid=" + msid + "&menu_url=" + menu_url;

					} else {
						var mypage = reportname + "?country=" + country + "&waterway=" + waterway + "&filteroption=" + filteroption + "&GuideMooringCodes=" + GuideMooringCodes + "&GuideHazardCodes=" + GuideHazardCodes + "&msid=" + msid + "&menu_url=" + menu_url;
					}

					//alert(mypage);
					var myname = "WaterwaysGuide";
					var w = 1000;
					var h = 500;
					var scroll = "yes";
					var winl = (screen.width - w) / 2;
					var wint = (screen.height - h) / 2;
					winprops = 'height=' + h + ',width=' + w + ',top=' + wint + ',left=' + winl + ',scrollbars=' + scroll + ',resizable'
					mypage += (mypage.indexOf('?') != -1 ? '&' : '?') + 'nocache=' + Date.now();
					win = window.open(mypage, myname, winprops)
					if (parseInt(navigator.appVersion) >= 4) {
						win.window.focus();
					}
				}
			}

			function listtokml(option) {
				var form = 'form';
				dml = document.forms[form];
				var country = dml.country.value;
				if (country == "All") {
					alert("Please select a single country for this option.");
				} else {

					var waterway = dml.waterway.value;
					var filteroption = dml.filteroption.value;
					var GuideMooringCodes = dml.GuideMooringCodes.value;
					var GuideHazardCodes = dml.GuideHazardCodes.value;
					var reportname = "/components/com_membership/views/wwg/tmpl/guides_list_to_kml.php";
					//var reportname = "components/com_membership/views/wwg/tmpl/guides_list_to_kml.php";
					var msid = "<?php echo ($login_memberid); ?>";
					var menu_url = "<?php echo ($menu_url); ?>";
					if (option > 0) {

						if (option == 1) {
							//alert("Please select another country, unfortunately France is not yet available as pdf");
							var waterway1 = "Aa";
							var waterway2 = "Lys (Fr)";
						}
						if (option == 2) {
							var waterway1 = "Marne";
							var waterway2 = "Yonne";
						}
						var mypage = reportname + "?country=" + country + "&waterway=" + waterway + "&filteroption=" + filteroption + "&GuideMooringCodes=" + GuideMooringCodes + "&GuideHazardCodes=" + GuideHazardCodes + "&waterway1=" + waterway1 + "&waterway2=" + waterway2 + "&msid=" + msid + "&menu_url=" + menu_url;

					} else {
						var mypage = reportname + "?country=" + country + "&waterway=" + waterway + "&filteroption=" + filteroption + "&GuideMooringCodes=" + GuideMooringCodes + "&GuideHazardCodes=" + GuideHazardCodes + "&msid=" + msid + "&menu_url=" + menu_url;
					}

					//alert(mypage);
					var myname = "WaterwaysGuide";
					var w = 400;
					var h = 200;
					var scroll = "no";
					var winl = (screen.width - w) / 2;
					var wint = (screen.height - h) / 2;
					winprops = 'height=' + h + ',width=' + w + ',top=' + wint + ',left=' + winl + ',scrollbars=' + scroll + ',resizable'
					mypage += (mypage.indexOf('?') != -1 ? '&' : '?') + 'nocache=' + Date.now();
					win = window.open(mypage, myname, winprops)
					if (parseInt(navigator.appVersion) >= 4) {
						win.window.focus();
					}

				}
			}
		</script>

		<?php

		//---------------------------------------List guides---------------------------------------------

		if ($guideaction == "list") {
			//link http://barges.org/members/waterwaysguide/?guideaction=list&country=FR&filteroption=M&GuideMooringCodes=|16|15|
			echo ("<input name=\"thisid\" type=\"hidden\" value=\"" . $thisid . "\">\n");
			echo ("<input name=\"country_tmp\" type=\"hidden\" value=\"$country\">\n");
			echo ("<input name=\"waterway_tmp\" type=\"hidden\" value=\"$waterway\">\n");
			if (isset($message)) {
				echo ("<tr><td colspan=4>$message<br></td></tr>\n");
			}
			echo ("<tr><td colspan=4><input type=\"button\" class=\"btn btn-primary\" name=\"filterback\" value=\"Back to the filter\" onClick=\"document.form.guideaction.value='waterways';document.form.submit()\"></td></tr>\n");
			//echo("<tr><td colspan=4><a href=\"#\" onClick=\"document.form.guideaction.value='waterways';document.form.submit()\">Back to the filter <img src=\"Image/common/back1.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Back to the filter\" alt=\"Back to the filter\"></a></td></tr>\n");
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn($guidetable))
				->order($db->qn(['GuideCountry', 'GuideWaterway', 'GuideOrder']));
			if ($country && $country != 'All') $query->where($db->qn('GuideCountry') . ' = ' . $db->q($country));
			if ($waterway && $waterway != 'All') $query->where($db->qn('GuideWaterway') . ' = ' . $db->q(stripslashes($waterway)));
			//filter options
			if ($filteroption == "ALL" || $filteroption == "M") {
				//add any ticks in $GuideMooringCodes and compare to $GuideCodes
				$filterwhere = '(' . $db->qn('GuideCategory') . ' = 1';
				//explode to array
				if ($GuideMooringCodes) {
					$codes = explode("|", $GuideMooringCodes);
					$maxcodes = sizeof($codes) - 2;
					$codeno = 1;
					while ($codeno <= $maxcodes) {
						$thiscode = "|" . $codes[$codeno] . "|";
						$filterwhere .= ' AND ' . $db->qn('GuideCodes') . " LIKE '%" . $thiscode . "%'";
						$codeno += 1;
					}
				}
				$filterwhere .= ")";
			}
			if ($filteroption == "ALL" || $filteroption == "H") {
				//add any ticks in $GuideHazardCodes and compare to $GuideCodes
				if ($filterwhere) $filterwhere .= ' OR ';
				else $filterwhere = '';
				$filterwhere .= '(' . $db->qn('GuideCategory') . ' = 2';
				//explode to array
				if ($GuideHazardCodes) {
					$codes = explode("|", $GuideHazardCodes);
					$maxcodes = sizeof($codes) - 2;
					$codeno = 1;
					while ($codeno <= $maxcodes) {
						$thiscode = "|" . $codes[$codeno] . "|";
						$filterwhere .= ' AND ' . $db->qn('GuideCodes') . " LIKE '%" . $thiscode . "%'";
						$codeno += 1;
					}
				}
				$filterwhere .= ")";
			}
			if ($filterwhere) $query->where('(' . $filterwhere . ')');
			elseif ($filteroption != "All") {
				//filter on Moorings and/or hazards without filter
				if ($filteroption == "M") $query->where($db->qn('GuideCategory') . ' = 1');
				else if ($filteroption == "H") $query->where($db->qn('GuideCategory') . ' = 2');
			}
			if (!$Status0 && !$Status1 && !$Status2) {
				$query->where($db->qn('GuideStatus') . ' = 1'); //live
			} else {
				$GuideStatusWhere = [];
				if ($Status0 == 1) $GuideStatusWhere[] = $db->qn('GuideStatus') . ' = 0'; //pending
				if ($Status1 == 1) $GuideStatusWhere[] = $db->qn('GuideStatus') . ' = 1'; //live
				if ($Status2 == 1) $GuideStatusWhere[] = $db->qn('GuideStatus') . ' = 2'; //archive
				$query->where('(' . implode(' OR ', $GuideStatusWhere) . ')');
			}
			$guides = $db->setQuery($query)->loadAssocList();
			$rows = count($guides);
			# If the search was unsuccessful then Display Message try again.
			if ($rows == 0) {
				print "<tr><td colspan=4>Sorry - there are no guides meeting your selection choices at the moment.</td></tr>\n";
			} else {
				$GuideMooringNo = 0;
				$guidematch = 0;
				$ThisGuideWaterway = "";
				$listresults = "";
				$thisrow = "odd";
				$mapping = 0;
				foreach ($guides as $row) {
					$GuideID = stripslashes($row["GuideID"]);
					$GuideCountry = stripslashes($row["GuideCountry"]);
					$GuideWaterway = stripslashes($row["GuideWaterway"]);
					$GuideSummary = nl2br(stripslashes($row["GuideSummary"]));
					$GuideName = stripslashes($row["GuideName"]);
					$GuideRemarks = stripslashes($row["GuideRemarks"]);
					$GuideLocation = nl2br(stripslashes($row["GuideLocation"]));
					$GuideRating = $row["GuideRating"];
					$GuideStatus = $row["GuideStatus"];
					$GuideNo = stripslashes($row["GuideNo"]);
					$GuideOrder = $row["GuideOrder"];
					$GuideVer = stripslashes($row["GuideVer"]);
					$GuideCategory = $row["GuideCategory"];
					$GuideRef = $row["GuideRef"];

					if ($GuideStatus != 1) {
						if ($GuideStatus == 0) {
							$GuideName .= " (V. " . $GuideVer . " Pending)";
						}
						if ($GuideStatus == 2) {
							$GuideName .= " (V. " . $GuideVer . " Archived)";
						}
					}
					$GuideUpdate = stripslashes($row["GuideUpdate"]);
					$GuideUpdatedisplay = (empty($GuideUpdate) ? 'Pre 2009' : date('dmy', strtotime($GuideUpdate))) . " - Mooring Index: " . $GuideNo . " - Version: " . $GuideVer;
					if ($GuideCategory == 2) {
						//add hazard icon in front of name
						switch ($GuideRating) {
							case "1":
								$ratingtitle = "Hazard rating Low";
								break;
							case "2":
								$ratingtitle = "Hazard rating Medium";
								break;
							case "3":
								$ratingtitle = "Hazard rating High";
						}
						$GuideCategoryIcon = "<img src=\"Image/common/hazard_small.gif\" title=\"" . $ratingtitle . "\" alt=\"" . $ratingtitle . "\" width=\"16\" height=\"16\" border=\"0\"> <b>HAZARD</b> ";
						//convert rating into stars
						$i = 1;
						$GuideRatingIcon = "";


						while ($i <= $GuideRating) {
							$GuideRatingIcon .= "<img src=\"Image/common/hazard_small.gif\" title=\"" . $ratingtitle . "\" alt=\"" . $ratingtitle . "\" width=\"16\" height=\"16\" border=\"0\">";
							$i++;
						}
					} else {

						switch ($GuideRating) {
							case "":
								$ratingtitle = "Mooring rating Unknown";
								break;
							case "0":
								$ratingtitle = "Mooring rating Doubtful";
								break;
							case "1":
								$ratingtitle = "Mooring rating Adequate";
								break;
							case "2":
								$ratingtitle = "Mooring rating Good";
								break;
							case "3":
								$ratingtitle = "Mooring rating Very Good";
						}
						$GuideCategoryIcon = "";
						//convert rating into stars
						$i = 1;
						$GuideRatingIcon = "";


						while ($i <= $GuideRating) {
							$GuideRatingIcon .= "<img src=\"Image/common/star.gif\" title=\"" . $ratingtitle . "\" alt=\"" . $ratingtitle . "\" width=\"16\" height=\"16\" border=\"0\">";
							$i++;
						}
					}





					if ($row["GuideRemarks"]) {
						$msgtrail = substr($row["GuideRemarks"], 0, 120) . " . . . . . . . <br>Last update: " . $GuideUpdatedisplay;
					} else {
						$msgtrail = "Last update: " . $GuideUpdatedisplay;
					}

					$GuideLatLong = $row["GuideLatLong"];
					$GuideLat = $row["GuideLat"];
					$GuideLong = $row["GuideLong"];

					//create map latlong
					if ($GuideLat && $GuideLong) {
						//$GuideLatLong=decimal2degree($GuideLat,'LAT') ." , " . decimal2degree($GuideLong,'LON');
						//$declatlng="Available on map option ".$GuideLatLong;
						//$mapicon="<img src=\"Image/common/compass.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"".$declatlng."\" alt=\"".$declatlng."\">";			
						$mapicon = "<a href=\"#\" onClick=\"document.form.guideaction.value='map';document.form.lastguideaction.value='map';document.form.thisid.value=" . $GuideID . ";document.form.submit()\"><img src=\"Image/common/compass.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"View guides on a map\" alt=\"View guides on a map\"></a>";
						$mapping = 1;
					} else {
						$mapicon = "";
					}
					$guidematch = 1;

					if ($country != $thiscountry) {
						//lookup country name
						$query = $db->getQuery(true)
							->select($db->qn('printable_name'))
							->from($db->qn('tblCountry'))
							->where($db->qn('iso') . ' = ' . $db->q(strtoupper($GuideCountry)));
						$countryrow = $db->setQuery($query)->loadAssoc();
						$CountryName = stripslashes($countryrow["printable_name"]);
						$outputlistresults .= "<tr><td colspan=4><h2>$CountryName</h2></td></tr>\n";
						$thiscountry = $country;
					}
					if ($GuideWaterway != $ThisGuideWaterway && $listresults) {
						//new waterway so output last one details
						if ($GuideMooringNo == 1) {
							$GuideMooringNoSummary = $GuideMooringNo . " location listed";
						} else {
							$GuideMooringNoSummary = $GuideMooringNo . " locations listed";
						}

						$outputlistresults .= "<tr valign='top'><td colspan=4 class='table_admin_profile'>" . $DisplayGuideSummary . $GuideMooringNoSummary . "</td></tr>\n";
						$outputlistresults .= "<tr><td><b>Name</b></td><td><b>Location</b></td><td><b>Rating</b></td><td><b>Map</b></td></tr>\n";
						$outputlistresults .= $listresults;
						$outputlistresults .= "<tr valign='top'><td colspan=4><hr></td></tr>\n";
						$listresults = "";
						$DisplayGuideSummary = "";
						$thisrow = "odd";
						$GuideMooringNo = 0;
					}
					if ($GuideWaterway != $ThisGuideWaterway && !$listresults) {
						//new waterway
						$outputlistresults .= "<tr valign='top'><td colspan=4><h3>$GuideWaterway</h3></td></tr>\n";
						$DisplayGuideSummary = "";
						$ThisGuideWaterway = $GuideWaterway;
					}
					$GuideMooringNo += 1;

					//get summmary and concatinate
					if ($GuideSummary) {
						$DisplayGuideSummary .= $GuideSummary . "<br><br>";
					}


					if ($thisrow == "odd") {
						$rowclass = "table_stripe_even";
						$thisrow = "even";
					} else {
						$rowclass = "table_stripe_odd";
						$thisrow = "odd";
					}
					if ($GuideCategory == 2) {
						//hazard
						//$rowclass="table_stripe_hazard";	
					}
					if ($admin == "open") {
						if ($GuideRef == "") {
							$GuideRef = "?";
						}
						$adminlink = " - Ref: " . $GuideRef . " - Sequence: " . $GuideOrder . "";
						//option if you want to edit from list rather than detail
						//$adminlink="<a href=\"#\" onClick=\"document.form.guideaction.value='edit';document.form.infoid.value='$GuideID';document.form.submit()\"><img src=\"Image/common/open.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Update this entry\"></a>";
					} else {
						$adminlink = "";
					}


					$listresults .= "<tr><td class=$rowclass><a href=\"#\" onClick=\"document.form.guideaction.value='detail';document.form.guideid.value='$GuideID';document.form.submit()\">" . $GuideName . "</a></td><td class=$rowclass>" . $GuideCategoryIcon . $GuideLocation . "</td><td class=$rowclass>$GuideRatingIcon</td><td class=$rowclass>$mapicon</td></tr>\n";
					if ($msgtrail) {
						$listresults .= "<tr><td class=trailer colspan=4><a href=\"#\" onClick=\"document.form.guideaction.value='detail';document.form.guideid.value='$GuideID';document.form.submit()\">" . $msgtrail . "</a>" . $adminlink . "</td></tr>\n";
					}
				}
				if ($GuideMooringNo > 0) {
					//add on last one
					if ($GuideMooringNo == 1) {
						$GuideMooringNoSummary = $GuideMooringNo . " location listed";
					} else {
						$GuideMooringNoSummary = $GuideMooringNo . " locations listed";
					}
					$outputlistresults .= "<tr valign='top'><td colspan=4 class='table_admin_profile'>" . $DisplayGuideSummary . $GuideMooringNoSummary . "</td></tr>\n";
					$outputlistresults .= "<tr><td><b>Name</b></td><td><b>Location</b></td><td><b>Rating</b></td><td><b>Map</b></td></tr>\n";
					$outputlistresults .= $listresults;
					$outputlistresults .= "<tr valign='top'><td colspan=4><hr></td></tr>\n";
					$DisplayGuideSummary = "";
				}

				$listresults = $outputlistresults;

				$listresults .= "<tr><td colspan=4>" . $copyright_guides . "</td></tr>";

				if ($guidematch == 1) {
					if ($rows == 1) {
						print "<tr><td colspan=4>$rows location listed - click the Name column for details \n";
					} else {
						print "<tr><td colspan=4>$rows locations listed - click the Name column for details \n";
					}
					//print or email
					//PRINT "<tr><td colspan=4><a href=\"#\" onClick=\"document.form.guideaction.value='print';document.form.guideid.value='$GuideID';document.form.submit()\">Print this listing <img src=\"Image/common/print.gif\" alt=\"Print this listing\" width=\"18\" height=\"18\" border=\"0\"></a> with details or <a href=\"#\" onClick=\"document.form.guideaction.value='emailme';document.form.guideid.value='$GuideID';document.form.submit()\">email it to me <img src=\"Image/common/email.gif\" alt=\"email it to me\" width=\"18\" height=\"18\" border=\"0\"></a></td></tr>\n";
					//PRINT " or <a href=\"#\" onClick=\"document.form.guideaction.value='printlist';document.form.submit()\">here to view, save or email the full details <img src=\"Image/common/txt.gif\" title=\"View, save or email the full details\" alt=\"View, save or email the full details\" width=\"18\" height=\"18\" border=\"0\"></a>\n";
					print "</td></tr>";
					print $listresults . "\n";
					//PRINT "<tr><td colspan=4>".$GuideMooringCodes."</td></tr>\n";

				} else {
					print "<tr><td colspan=4>Sorry - there are no guides meeting your selection choices at the moment.</td></tr>\n";
				}
			}
			//PRINT "<tr><td colspan=4>". $where." Filter option=".$filteroption."</td></tr>";
			echo ("<input name=\"mapping\" type=\"hidden\" value=\"" . (isset($mapping) ? $mapping : '') . "\">\n");


			//exit();
		}



		//---------------------------------------View map of guides---------------------------------------------

		if ($guideaction == "map") {
			echo ("<input name=\"country_tmp\" type=\"hidden\" value=\"$country\">\n");
			echo ("<input name=\"waterway_tmp\" type=\"hidden\" value=\"$waterway\">\n");
			if (isset($message)) {
				echo ("<tr><td colspan=4>$message<br></td></tr>\n");
			}
			echo ("<tr><td colspan=4><input type=\"button\" class=\"btn btn-primary button_action\" name=\"mapback\" value=\"Back to the filter\" onClick=\"document.form.guideaction.value='waterways';document.form.submit()\"><input type=\"button\" class=\"btn btn-primary\" name=\"listback\" value=\"Back to the list\" onClick=\"document.form.guideaction.value='list';document.form.lastguideaction.value='list';document.form.submit()\"></td></tr>\n");

			echo ("<tr><td colspan=4>");
			//if($mapping==1 || $waterway=="All"){
			echo ("<input name=\"thisid\" type=\"hidden\" value=\"" . $thisid . "\">\n");
			include("guides_map.php");
			//}else{
			//echo("<br>Sorry - there are currently no mooring waypoints on that waterway.<br>Check for the map icon <img src=\"Image/common/compass.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"View guides on a map\" alt=\"View guides on a map\"> in the right hand 'Map' column of the <a href=\"#\" onClick=\"document.form.guideaction.value='list';document.form.submit()\">List</a> and click the icon to open the map at that mooring. Any others, already mapped for that waterway will appear in the left hand list.\n");		
			//echo("<br><br><b>If you are familiar with the position of any of these moorings</b> then go back to the <a href=\"#\" onClick=\"document.form.guideaction.value='list';document.form.submit()\">List</a>, click on the 'Name' link and use the 'Submit an update to this mooring' facility to pinpoint the position and help us to get them all mapped. Your input is vital to the future of the guides.");		

			//}

			echo ("</td></tr>\n");
		}



		//---------------------------------------guide details---------------------------------------------

		if ($guideaction == "detail") {
			echo ("<input name=\"country_tmp\" type=\"hidden\" value=\"$country\">\n");
			echo ("<input name=\"waterway_tmp\" type=\"hidden\" value=\"$waterway\">\n");

			if (!$guideid && $infoid) {
				$guideid = $infoid;
			}
			echo ("<input name=\"thisid\" type=\"hidden\" value=\"" . $thisid . "\">\n");
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn($guidetable));
			if ($guideno) {
				$query->where($db->qn('GuideNo') . ' = ' . $db->q($guideno))
					->order($db->qn('GuideVer') . ' DESC')
					->setLimit(1);
			} else $query->where($db->qn('GuideID') . ' = ' . $db->q($guideid));
			$result = $db->setQuery($query)->loadAssocList();
			$num_rows = count($result);

			# If the search was unsuccessful then Display Message try again.
			if (!$num_rows) {
				echo ("<tr><td colspan=4>Sorry - no details available for this guide<br><hr></td></tr>");
				exit();
			}

			$datenow = time();
			$row = reset($result);
			$GuideID = stripslashes($row["GuideID"]);
			$GuideNo = stripslashes($row["GuideNo"]);
			$GuideVer = stripslashes($row["GuideVer"]);
			$GuideCountry = stripslashes($row["GuideCountry"]);
			$GuideWaterway = stripslashes($row["GuideWaterway"]);
			$GuideSummary = nl2br(stripslashes($row["GuideSummary"]));
			$GuideName = stripslashes($row["GuideName"]);
			$GuideRef = stripslashes($row["GuideRef"]);
			$GuideOrder = stripslashes($row["GuideOrder"]);
			$GuideLatLong = stripslashes($row["GuideLatLong"]);
			$GuideLocation = nl2br(stripslashes($row["GuideLocation"]));
			$GuideMooring = nl2br(stripslashes($row["GuideMooring"]));
			$GuideFacilities = nl2br(stripslashes($row["GuideFacilities"]));
			$GuideCodes = stripslashes($row["GuideCodes"]);
			$GuideCosts = nl2br(stripslashes($row["GuideCosts"]));
			$GuideRating = stripslashes($row["GuideRating"]);
			$GuideAmenities = nl2br(stripslashes($row["GuideAmenities"]));
			$GuideContributors = nl2br(stripslashes($row["GuideContributors"]));
			$GuideRemarks = nl2br(stripslashes($row["GuideRemarks"]));
			$GuideLat = stripslashes($row["GuideLat"]);
			$GuideLong = stripslashes($row["GuideLong"]);
			//convert dec to lat long
			if ($GuideLat && $GuideLong) {
				$GuideLatLong = decimal2degree($GuideLat, 'LAT') . " , " . decimal2degree($GuideLong, 'LON');
				//$mapicon=" View on a map <a href='javascript:Map(\"/components/com_membership/views/wwg/tmpl/guides_map_pop.php?thisid=".$GuideID."\")'><img src=\"Image/common/compass.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"View on a map\" alt=\"View on a map\"></a>";

				$mapicon = " View on a map <a href=\"#\" onClick=\"document.form.guideaction.value='map';document.form.lastguideaction.value='map';document.form.thisid.value=" . $GuideID . ";document.form.submit()\"><img src=\"Image/common/compass.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"View on a map\" alt=\"View on a map\"></a>";
				$mapping = 1;
			} else {
				$GuideLatLong = "Not known";
				$mapicon = "";
			}

			$GuideDocs = stripslashes($row["GuideDocs"]);
			$GuidePostingDate = stripslashes($row["GuidePostingDate"]);
			$GuideCategory = stripslashes($row["GuideCategory"]);
			$GuideUpdate = stripslashes($row["GuideUpdate"]);
			$GuideStatus = stripslashes($row["GuideStatus"]);
			$GuideUpdatedisplay = (empty($GuideUpdate) ? 'Date unknown' : date('Y-m-d', strtotime($GuideUpdate))) . " - Mooring Index: " . $GuideNo . " - Version: " . $GuideVer;
			$i = 1;
			while ($i <= $GuideRating) {
				$GuideRatingIcon .= "<img src=\"Image/common/star.gif\" title=\"rating\" alt=\"rating\" width=\"16\" height=\"16\" border=\"0\">";
				$i++;
			}
			if ($admin == "open") {
				//$adminlink="";
				$adminlink = "<input type=\"button\" class=\"btn btn-primary\" name=\"edit\" value=\"Update this entry (admin)\" onClick=\"document.form.guideaction.value='edit';document.form.infoid.value='$GuideID';document.form.submit()\">";
				//$adminlink="<a href=\"#\" onClick=\"document.form.guideaction.value='edit';document.form.infoid.value='$GuideID';document.form.submit()\">Update this entry (admin) <img src=\"Image/common/open.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Update this entry\" alt=\"Update this entry\"></a> <a href=\"#\" onClick=\"document.form.guideaction.value='memberedit';document.form.infoid.value='$GuideID';document.form.submit()\">Update this entry <img src=\"Image/common/open.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Submit an update to this mooring\" alt=\"Submit an update to this mooring\"></a>";
			} else {
				$adminlink = "<input type=\"button\" class=\"btn btn-primary\" name=\"memberedit\" value=\"Submit an update to this entry\" onClick=\"document.form.guideaction.value='memberedit';document.form.infoid.value='$GuideID';document.form.submit()\">";
				//$adminlink="<a href=\"#\" onClick=\"document.form.guideaction.value='memberedit';document.form.infoid.value='$GuideID';document.form.submit()\">Submit an update to this entry <img src=\"Image/common/open.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Submit an update to this entry\" alt=\"Submit an update to this entry\"></a>";
			}

			//echo("<tr><td colspan=4><a href=\"#\" onClick=\"document.form.guideaction.value='list';document.form.submit()\">Back to the list <img src=\"Image/common/back1.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Back to the list\" alt=\"Back to the list\"></a> ".$adminlink."</td></tr>\n");
			echo ("<tr><td colspan=4><input type=\"button\" class=\"btn btn-primary\" name=\"listback\" value=\"Back to the list\" onClick=\"document.form.guideaction.value='list';document.form.lastguideaction.value='list';document.form.submit()\"><input type=\"button\" class=\"btn btn-primary\" name=\"mapback\" value=\"Back to the map\" onClick=\"document.form.guideaction.value='map';document.form.lastguideaction.value='map';document.form.submit()\"> " . $adminlink . "</td></tr>\n");

			$listresults = "<table border=\"0\" cellpadding=\"1\" cellspacing=\"2\">\n";
			//$listresults.="<tr valign='top'><td colspan=2><b>".$thisguideenquiry."</b></td></tr>";
			$listresults .= "<tr valign='top'><td><b>Waterway:</b></td><td>" . $GuideWaterway . "</td></tr>\n";

			$listresults .= "<tr valign='top'><td><b>Name:</b></td><td>" . $GuideName . "</td></tr>\n";
			if ($GuideRating) {
				$listresults .= "<tr valign='top'><td><b>Rating:</b></td><td>" . $GuideRatingIcon . "</td></tr>\n";
			}

			if ($GuideLatLong) {
				$listresults .= "<tr valign='top'><td><b>Position:</b></td><td>" . $GuideLatLong . " " . $mapicon . "</td></tr>\n";
			}
			if ($GuideRef) {
				$listresults .= "<tr valign='top'><td><b>Reference:</b></td ><td>" . $GuideRef . "</td></tr>\n";
			}
			if ($GuideLocation) {
				$listresults .= "<tr valign='top'><td><b>Location:</b></td ><td>" . $GuideLocation . "</td></tr>\n";
			}
			switch ($GuideCategory) {
				case "1":
					$GuideCategoryDesc = "Mooring";
					break;
				case "2":
					$GuideCategoryDesc = "<img src=\"Image/common/hazard_small.gif\" title=\"hazard\" alt=\"hazard\" width=\"16\" height=\"16\" border=\"0\"> Hazard";
					break;
			}
			$listresults .= "<tr valign='top'><td><b>Category:</b></td><td>" . $GuideCategoryDesc . "</td></tr>\n";

			if ($GuideMooring) {
				$listresults .= "<tr valign='top'><td><b>Mooring:</b></td><td>" . $GuideMooring . "</td></tr>\n";
			}

			if ($GuideCodes) {
				//add tick boxes here
				switch ($GuideCategory) {
					case "1":
						$query = $db->getQuery(true)
							->select('*')
							->from($db->qn('tblServices'))
							->where($db->qn('ServiceCategory') . " = 'mooringsguides'")
							->order($db->qn('ServiceSortOrder'));
						$boxes = $db->setQuery($query)->loadAssocList();
						$boxestitle = "Essentials";
						break;
					case "2":
						$query = $db->getQuery(true)
							->select('*')
							->from($db->qn('tblServices'))
							->where($db->qn('ServiceCategory') . " = 'hazardguides'")
							->order($db->qn('ServiceSortOrder'));
						$boxes = $db->setQuery($query)->loadAssocList();
						$boxestitle = "Hazard category";
						break;
				}

				$boxhtml = "";
				foreach ($boxes as $boxrow) {
					$boxid = $boxrow["ServiceID"];
					$boxdesc = $boxrow["ServiceDescGB"];
					$found = strstr($GuideCodes, "|" . $boxid . "|");
					if ($found) {
						if ($boxhtml) {
							$boxhtml .= ", ";
						}

						$boxhtml .= $boxdesc;
					}
				}


				$listresults .= "<tr valign='top'><td><b>" . $boxestitle . "</b></td><td>" . $boxhtml . "</td></tr>\n";
			}


			if ($GuideFacilities) {
				$listresults .= "<tr valign='top'><td><b>Facilities:</b></td><td>" . $GuideFacilities . "</td></tr>\n";
			}

			if ($GuideCosts) {
				$listresults .= "<tr valign='top'><td><b>Costs:</b></td><td>" . $GuideCosts . "</td></tr>\n";
			}
			if ($GuideAmenities) {
				$listresults .= "<tr valign='top'><td><b>Amenities:</b></td ><td>" . $GuideAmenities . "</td></tr>\n";
			}


			if ($GuideContributors) {
				$listresults .= "<tr valign='top'><td><b>Contributors:</b></td ><td>" . $GuideContributors . "</td></tr>\n";
			}
			if ($GuideRemarks) {
				$listresults .= "<tr valign='top'><td><b>Remarks:</b></td><td>" . $GuideRemarks . "</td></tr>\n";
			}

			if ($GuideUpdate) {
				$listresults .= "<tr valign='top'><td><b>Last Update:</b></td><td>" . $GuideUpdatedisplay . "</td></tr>\n";
			}

			$listresults .= "<tr><td colspan=2>" . $copyright_guides . "</td></tr>\n";
			$listresults .= "</table>\n";
			echo ("<tr><td colspan=4>" . $listresults . "</td></tr>\n");

			//exit();
		?>

			<script type="text/javascript">
				function Map(path) {
					var mypage = path;
					var myname = "map";
					//var w = (screen.width - 100);
					//var h = (screen.height - 100);
					var w = 810;
					var h = 500;
					var scroll = "yes";
					var winl = (screen.width - w) / 2;
					var wint = (screen.height - h) / 2;
					winprops = 'height=' + h + ',width=' + w + ',top=' + wint + ',left=' + winl + ',scrollbars=' + scroll + ',resizable'
					mypage += (mypage.indexOf('?') != -1 ? '&' : '?') + 'nocache=' + Date.now();
					win = window.open(mypage, myname, winprops)
					if (parseInt(navigator.appVersion) >= 4) {
						win.window.focus();
					}
				}
			</script>
		<?php
		}

		//--------------------------------------- admin edit or add new---------------------------------------------

		if ($guideaction == "edit") {

			function diff($old, $new)
			{
				$maxlen = 0;
				foreach ($old as $oindex => $ovalue) {
					$nkeys = array_keys($new, $ovalue);
					foreach ($nkeys as $nindex) {
						$matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
							$matrix[$oindex - 1][$nindex - 1] + 1 : 1;
						if ($matrix[$oindex][$nindex] > $maxlen) {
							$maxlen = $matrix[$oindex][$nindex];
							$omax = $oindex + 1 - $maxlen;
							$nmax = $nindex + 1 - $maxlen;
						}
					}
				}
				if ($maxlen == 0) return array(array('d' => $old, 'i' => $new));
				return array_merge(
					diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
					array_slice($new, $nmax, $maxlen),
					diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen))
				);
			}

			function htmlDiff($old, $new)
			{
				if ($old != $new) {
					$diff = diff(explode(' ', $old), explode(' ', $new));
					$ret = '';
					foreach ($diff as $k) {
						if (is_array($k)) {
							$ret .= (!empty($k['d']) ? "<del>" . implode(' ', $k['d']) . "</del> " : '') . (!empty($k['i']) ? "<ins>" . implode(' ', $k['i']) . "</ins> " : '');
						} else $ret .= $k . ' ';
					}

					return "<b>Changes</b><br><div class=guidechange>$ret</div>";
				} else {
					return;
				}
			}


			//echo("<tr><td colspan=4>");
			//echo("<iframe id=\"map\" src=\"guides_map.php\" width=550 height=550 marginwidth=0 marginheight=0 hspace=0 vspace=0 frameborder=0 scrolling=no></iframe>\n");

			//include("guides_edit.php");
			//echo("</td></tr>\n");


			echo ("<tr><td class=content_introduction><b>Edit entry by Administrator</b></td></tr>\n");
			echo ("<tr><td><input type=\"button\" class=\"btn btn-primary\" name=\"listback\" value=\"Back to the list\" onClick=\"document.form.guideaction.value='list';document.form.lastguideaction.value='list';document.form.submit()\"><input type=\"button\" class=\"btn btn-primary\" name=\"mapback\" value=\"Back to the map\" onClick=\"document.form.guideaction.value='map';document.form.lastguideaction.value='map';document.form.submit()\"> " . (isset($adminlink) ? $adminlink : '') . "</td></tr>\n");


			if ($errmsg) {
				echo ("<tr><td><font color=ff0000><b>$errmsg</b></font></td></tr>\n");
			}
			$GuideUpdate = date("Y-m-d H:i:s");;

			if ($infoid == "newmooring") {
				echo ("<tr><td>Enter the mooring details below and click 
		<a href=\"#\" onClick=\"document.form.guideaction.value='save';document.form.submit()\"> here <img src=\"Image/common/save.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Save this entry\" alt=\"Save this entry\"></a>
		to save.</td></tr>\n");
				$GuidePostingDate = $GuideUpdate;
				$GuideCategory = 1;
			} elseif ($infoid == "newhazard") {
				echo ("<tr><td>Enter the hazard details below and click 
		<a href=\"#\" onClick=\"document.form.guideaction.value='save';document.form.submit()\"> here <img src=\"Image/common/save.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Save this entry\" alt=\"Save this entry\"></a>
		to save.</td></tr>\n");
				$GuidePostingDate = $GuideUpdate;
				$GuideCategory = 2;
			} else {
				if (!$errmsg) {
					$query = $db->getQuery(true)
						->select('*')
						->from($db->qn($guidetable))
						->where($db->qn('GuideID') . ' = ' . $db->q($infoid));
					$result = $db->setQuery($query)->loadAssocList();
					foreach ($result as $row) {
						$GuideID = stripslashes($row["GuideID"]);
						$GuideNo = stripslashes($row["GuideNo"]);
						$GuideVer = stripslashes($row["GuideVer"]);
						$GuideCountry = stripslashes($row["GuideCountry"]);
						$GuideWaterway = stripslashes($row["GuideWaterway"]);
						$GuideSummary = stripslashes($row["GuideSummary"]);
						$GuideOrder = $row["GuideOrder"];
						$GuideName = stripslashes($row["GuideName"]);
						$GuideRef = stripslashes($row["GuideRef"]);
						$GuideLatLong = stripslashes($row["GuideLatLong"]);
						$GuideLocation = stripslashes($row["GuideLocation"]);
						$GuideMooring = stripslashes($row["GuideMooring"]);
						$GuideFacilities = stripslashes($row["GuideFacilities"]);
						$GuideCodes = stripslashes($row["GuideCodes"]);
						$GuideCosts = stripslashes($row["GuideCosts"]);
						// $GuideRating = stripslashes($row["GuideRating"]);
						$GuideRating = $row["GuideRating"];
						$GuideAmenities = stripslashes($row["GuideAmenities"]);
						$GuideContributors = stripslashes($row["GuideContributors"]);
						$GuideRemarks = stripslashes($row["GuideRemarks"]);
						$GuideLat = stripslashes($row["GuideLat"]);
						$GuideLong = stripslashes($row["GuideLong"]);
						$GuideDocs = stripslashes($row["GuideDocs"]);
						$GuidePostingDate = $row["GuidePostingDate"];
						$GuideCategory = stripslashes($row["GuideCategory"]);
						$GuideUpdate = $row["GuideUpdate"];
						$GuideStatus = stripslashes($row["GuideStatus"]);
						$GuideEditorMemNo = stripslashes($row["GuideEditorMemNo"]);
						if ($status == 1) {
							$statustext = "posted on site";
						} elseif ($status == 0) {
							$statustext = "pending";
						} elseif ($status == 3) {
							$statustext = "archived";
						}
						$GuideUpdatedisplay = (empty($GuideUpdate) ? 'Date unknown' : date('Y-m-d', strtotime($GuideUpdate))) . " - Mooring Index: " . $GuideNo . " - Version: " . $GuideVer;
					}
				}
				//check for previous version to compare
				if ($GuideVer > 1) {
					$OldGuideVer = $GuideVer - 1;
					$query = $db->getQuery(true)
						->select('*')
						->from($db->qn('tblGuides'))
						->where($db->qn('GuideNo') . ' = ' . $db->q($GuideNo))
						->where($db->qn('GuideVer') . ' = ' . $db->q($OldGuideVer));
					$oldguide = $db->setQuery($query)->loadAssocList();
					$rows = count($oldguide);
					if ($rows == 0) {
						echo ("<div>Sorry - can't find version " . $OldGuideVer . " of this guide</div>\n");
					} else {
						$row = reset($oldguide);
						$OldGuideCountry = stripslashes($row["GuideCountry"]);
						$ChangeGuideCountry = htmlDiff($OldGuideCountry, $GuideCountry);
						$OldGuideWaterway = stripslashes($row["GuideWaterway"]);
						$ChangeGuideWaterway = htmlDiff($OldGuideWaterway, $GuideWaterway);
						$OldGuideSummary = stripslashes($row["GuideSummary"]);
						$ChangeGuideSummary = htmlDiff($OldGuideSummary, $GuideSummary);
						$OldGuideOrder = $row["GuideOrder"];
						$ChangeGuideOrder = htmlDiff($OldGuideOrder, $GuideOrder);
						$OldGuideName = stripslashes($row["GuideName"]);
						$ChangeGuideName = htmlDiff($OldGuideName, $GuideName);
						$OldGuideRef = stripslashes($row["GuideRef"]);
						$ChangeGuideRef = htmlDiff($OldGuideRef, $GuideRef);
						$OldGuideLatLong = stripslashes($row["GuideLatLong"]);
						$ChangeGuideLatLong = htmlDiff($OldGuideLatLong, $GuideLatLong);
						$OldGuideLocation = stripslashes($row["GuideLocation"]);
						$ChangeGuideLocation = htmlDiff($OldGuideLocation, $GuideLocation);
						$OldGuideMooring = stripslashes($row["GuideMooring"]);
						$ChangeGuideMooring = htmlDiff($OldGuideMooring, $GuideMooring);
						$OldGuideFacilities = stripslashes($row["GuideFacilities"]);
						$ChangeGuideFacilities = htmlDiff($OldGuideFacilities, $GuideFacilities);
						$OldGuideCodes = stripslashes($row["GuideCodes"]);
						$ChangeGuideCodes = htmlDiff($OldGuideCodes, $GuideCodes);
						$OldGuideCosts = stripslashes($row["GuideCosts"]);
						$ChangeGuideCosts = htmlDiff($OldGuideCosts, $GuideCosts);
						$OldGuideRating = stripslashes($row["GuideRating"]);
						$ChangeGuideRating = htmlDiff($OldGuideRating, $GuideRating);
						$OldGuideAmenities = stripslashes($row["GuideAmenities"]);
						$ChangeGuideAmenities = htmlDiff($OldGuideAmenities, $GuideAmenities);
						$OldGuideContributors = stripslashes($row["GuideContributors"]);
						$ChangeGuideContributors = htmlDiff($OldGuideContributors, $GuideContributors);
						$OldGuideRemarks = stripslashes($row["GuideRemarks"]);
						$ChangeGuideRemarks = htmlDiff($OldGuideRemarks, $GuideRemarks);
						$OldGuideLat = stripslashes($row["GuideLat"]);
						$ChangeGuideLat = htmlDiff($OldGuideLat, $GuideLat);
						$OldGuideLong = stripslashes($row["GuideLong"]);
						$ChangeGuideLong = htmlDiff($OldGuideLong, $GuideLong);
					}
				}
				echo ("<tr><td>Change the details below and <input type=\"button\" class=\"btn btn-primary\" value=\"Save this entry\" onClick=\"document.form.guideaction.value='save';document.form.submit()\"> or
		<input type=\"button\" class=\"btn btn-primary\" value=\"Remove this entry\" onClick=\"document.form.guideaction.value='remove';document.form.submit()\">.</td></tr>\n");

				//echo("<tr><td>Change the details below and click 
				//<a href=\"#\" onClick=\"document.form.guideaction.value='save';document.form.submit()\"> here <img src=\"Image/common/save.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Save this entry\" alt=\"Save this entry\"></a>
				//to save or <a href=\"#\" onClick=\"document.form.guideaction.value='remove';document.form.submit()\"> here <img src=\"Image/common/clear.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Remove this entry\" alt=\"Remove this entry\"></a> to remove</td></tr>\n");
			}
			//get current countries
			$query = $db->getQuery(true)
				->select($db->qn(['iso', 'printable_name']))
				->from($db->qn('tblCountry'))
				->where($db->qn('postzone') . " IN ('EU', 'UK')")
				->order($db->qn('printable_name'));
			$countries = $db->setQuery($query)->loadAssocList();
			$olist = "<select class=\"formcontrol\" name=\"olist_GuideCountry\" id=\"olist_GuideCountry\" onChange=\"insertcountry(this.form.olist_GuideCountry.options[this.form.olist_GuideCountry.selectedIndex].value)\">\n";
			$olist .= "<option value=\"0\">Choose a country</option>\n";
			foreach ($countries as $row) {
				if ($row["iso"] == $GuideCountry) {
					$olist .= "<option value=\"" . $row["iso"] . "\" selected>" . $row["printable_name"] . "</option>\n";
				} else {
					$olist .= "<option  value=\"" . $row["iso"] . "\">" . $row["printable_name"] . "</option>\n";
				}
			}
			$olist .= "</select> \n";

			//GuideCountry
			print "<tr><td><b>Country</b> (choose from the drop-down)<br> \n";


			if ($olist) {
				echo ($olist);
			}
			if ($CatHelp) {
				echo ("<br><img src=\"../Image/common/info.gif\" title=\"Help\" alt=\"Help\" /> $CatHelp\n");
			}

			print "<br><input type=\"text\" name=\"GuideCountry\" class=\"formcontrol\" readonly=\"true\" size=\"10\" value=\"" . $GuideCountry . "\"></td></tr>\n";

			//print"<tr><td><br></td></tr>\n";

			//get current waterways
			$query = $db->getQuery(true)
				->select('DISTINCTROW ' . $db->qn('GuideWaterway'))
				->from($db->qn($guidetable))
				->order($db->qn('GuideWaterway'));
			$waterways = $db->setQuery($query)->loadAssocList();
			$rows = count($waterways);
			# If the search was unsuccessful then Display Message try again.
			if ($rows == 0) {
				$olist = "Enter the name of the waterway below.\n";
			} else {
				$olist = "<select name=\"olist_GuideWaterway\" class=\"formcontrol\" onChange=\"insertwaterway(this.form.olist_GuideWaterway.options[this.form.olist_GuideWaterway.selectedIndex].value)\">\n";
				$olist .= "<option value=\"0\">Waterways aready on-file</option>\n";
				foreach ($waterways as $row) {
					//$guideiso=" (".strtoupper($row["GuideCountry"].")");
					$ThisGuideWaterway = stripslashes($row["GuideWaterway"]);
					if ($ThisGuideWaterway == $GuideWaterway) {
						//$olist.="<option value=\"".$ThisGuideWaterway."\" selected>".$ThisGuideWaterway.$guideiso."</option>\n";
						$olist .= "<option value=\"" . $ThisGuideWaterway . "\" selected>" . $ThisGuideWaterway . "</option>\n";
					} else {
						$olist .= "<option  value=\"" . $ThisGuideWaterway . "\">" . $ThisGuideWaterway . "</option>\n";
					}
				}
				$olist .= "	</select>\n";
			}

			//GuideWaterway
			print "<tr><td><b>Waterway</b>(choose from the drop-down or enter a new waterway below)<br>\n";


			if ($olist) {
				echo ($olist);
			}
			if ($CatHelp) {
				echo ("<br><img src=\"../Image/common/info.gif\" title=\"Help\" alt=\"Help\" /> $CatHelp\n");
			}

			print "<br><input type=\"text\" id=\"GuideWaterway\" name=\"GuideWaterway\" class=\"formcontrol\" size=\"40\" value=\"" . $GuideWaterway . "\"></td></tr>\n";

			if ($GuideStatus == 0 && $infoid != "new") {
				//lookup submitter
				//GuideEditorMemNo
				if ($GuideEditorMemNo) {
					$query = $db->getQuery(true)
						->select('*')
						->from($db->qn('tblMembers'))
						->where($db->qn('MembershipNo') . ' = ' . $db->q($GuideEditorMemNo));
					$memberrow = $db->setQuery($query)->loadAssoc();
					$login_MembershipNo = $memberrow["MembershipNo"];
					$contact = $memberrow["FirstName"] . " " . $memberrow["LastName"] . ", " . $memberrow["Email"] . ", Membership No. " . $login_MembershipNo . "";
					$submitteremail = $memberrow["Email"];
					$submitterid = $memberrow["ID"];
				} else {
					$contact = "Unknown";
				}
				$listresults .= "<tr><td><table class='submit'></td></tr>\n";
				$listresults .= "<tr><td><b>SUBMISSION APPROVAL REQUIRED</b> Check and adjust if necessary the entry below, sequence order may need to be added, edit the email message in the box that will be sent to the submitter and click the appropriate button</td></tr>\n";
				$listresults .= "<tr><td><b>Submitter: </b>" . $contact . "</td></tr>\n";
				$listresults .= "<tr valign='top'><td><b>Version:</b> " . $GuideVer . " (version 1 will be a new submission)</td></tr>\n";
				$GuideMessage = "Many thanks for your update to the guide, '" . $GuideName . "' (" . $GuideWaterway . ") version " . $GuideVer . " made on " . date_to_format($GuideUpdate, 'd') . ". ";
				$GuideMessage .= "It has now been incorporated into the guides as the current version.\n\n";
				$GuideMessage .= "Guide Editor.\n";
				$listresults .= "<tr valign='top'><td><b>Message:</b> <i>Edit the <b>default</b> message which will be emailed to the submitter or delete it completely if you don't want to send an email</i><br><textarea cols=\"90\" rows=\"10\" name=\"GuideMessage\" class=\"formtextarea\">" . $GuideMessage . "</textarea></td></tr>\n";
				$listresults .= "<tr valign='top'><td><b>Status:</b> Pending - <input type=\"button\" class=\"formcontrol\" name=\"Approve\" value=\"Approve\" onClick=\"document.form.guideaction.value='approvesubmission';document.form.submit()\"> \n";

				//$listresults.="<input type=\"button\" class=\"formcontrol\" name=\"Reject\" value=\"Reject\" onClick=\"document.form.guideaction.value='rejectsubmission';document.form.submit()\"> \n"; 


				$listresults .= "</td></tr></table></td></tr>\n";
			}
			$listresults .= "<tr><td><table>";
			$listresults .= "<tr valign='top'><td><b>Name:</b><br><input type=\"text\" name=\"GuideName\" class=\"formcontrol\" size=\"50\" value=\"" . $GuideName . "\"></td><td>" . $ChangeGuideName . "</td></tr>\n";
			switch ($GuideCategory) {
				case "1":
					$GuideCategoryDesc = "Mooring";
					break;
				case "2":
					$GuideCategoryDesc = "<img src=\"Image/common/hazard_small.gif\" title=\"hazard\" alt=\"hazard\" width=\"16\" height=\"16\" border=\"0\"> Hazard";

					break;
			}
			$listresults .= "<tr valign='top'><td><b>Category:</b> " . $GuideCategoryDesc . "</td><td>" . $ChangeGuideCategoryDesc . "</td></tr>\n";


			$listresults .= "<tr valign='top'><td><b>Order:</b> <i>Ascending number along waterway</i><br><input type=\"text\" name=\"GuideOrder\" class=\"formcontrol\" size=\"4\" placeholder=\"1.00\" step=\"0.01\" min=\"0\" max=\"1000\" value=\"" . $GuideOrder . "\"></td><td>" . $ChangeGuideOrder . "</td></tr>\n";


			$listresults .= "<tr valign='top'>
								<td><b>Is this edit? Rating:</b><br>
									<select name=\"GuideRating\" class=\"formcontrol\">
										<option value=\"0\"" . ($GuideRating == 0 ? ' selected' : '') . ">Doubtful</option>
										<option value=\"1\"" . ($GuideRating == 1 ? ' selected' : '') . ">Adequate</option>
										<option value=\"2\"" . ($GuideRating == 2 ? ' selected' : '') . ">Good</option>
										<option value=\"3\"" . ($GuideRating == 3 ? ' selected' : '') . ">Very Good</option>
		  							</select>
								</td>
								<td>" . $ChangeGuideRating . "</td></tr>\n";



			$listresults .= "<tr valign='top'>
  <td><b>Map Marker: </b><i>To mark the location on the map, type the name of a nearby place in the 'Search Box' below and click on a place in the list that appears. Then drag the marker to the right spot. You can zoom in to make the location more accurate. France has many places with the same name so you may have to use another nearby place in the search.</i><br> 
      <input type=\"hidden\" id=\"latlng\" name=\"latlng\" value=\"\" /><br>
   </td>
</tr><tr valign='top'><td class=mooring_edit_underline>
   <b>Decimal Latitude:</b> <input type=\"text\" id=\"GuideLat\" name=\"GuideLat\" class=\"formcontrol\" size=\"8\" value=\"$GuideLat\" readonly/> 
  <b>Decimal Longitude:</b>
  <input type=\"text\" id=\"GuideLong\" name=\"GuideLong\" class=\"formcontrol\" size=\"8\" value=\"$GuideLong\" readonly/> <br />
  <input id=\"pac-input\" class=\"controls\" type=\"text\" placeholder=\"Search Box\"/>
  <div align=\"center\" id=\"map\" style=\"width: 100%; height: 400px\"><br/></div>
</td></tr>\n";


			$listresults .= "<tr valign='top'><td><b>Reference(PK?):</b><br><input type=\"text\" name=\"GuideRef\" class=\"formcontrol\"size=\"50\" value=\"" . $GuideRef . "\"></td><td>" . $ChangeGuideRef . "</td></tr>\n";
			$listresults .= "<tr valign='top'><td><b>Location:</b><br><textarea cols=\"90\" rows=\"10\" name=\"GuideLocation\" class=\"formtextarea\">" . $GuideLocation . "</textarea></td><td>" . $ChangeGuideLocation . "</td></tr>\n";

			//add tick boxes here

			switch ($GuideCategory) {
				case "1":
					$listresults .= "<tr valign='top'><td><b>Mooring:</b><br><textarea cols=\"90\" rows=\"10\" name=\"GuideMooring\" class=\"formtextarea\">" . $GuideMooring . "</textarea></td><td>" . $ChangeGuideMooring . "</td></tr>\n";
					$query = $db->getQuery(true)
						->select('*')
						->from($db->qn('tblServices'))
						->where($db->qn('ServiceCategory') . " = 'mooringsguides'")
						->order($db->qn('ServiceSortOrder'));
					$boxes = $db->setQuery($query)->loadAssocList();
					$boxestitle = "Tick the boxes for standard facilities available";
					break;
				case "2":
					$listresults .= "<tr valign='top'><td><b>Hazard:</b><br><textarea cols=\"90\" rows=\"10\" name=\"GuideMooring\" class=\"formtextarea\">" . $GuideMooring . "</textarea></td><td>" . $ChangeGuideMooring . "</td></tr>\n";
					$query = $db->getQuery(true)
						->select('*')
						->from($db->qn('tblServices'))
						->where($db->qn('ServiceCategory') . " = 'hazardguides'")
						->order($db->qn('ServiceSortOrder'));
					$boxes = $db->setQuery($query)->loadAssocList();
					$boxestitle = "Tick the boxes for the type of hazard found";
					break;
			}

			$boxhtml = "";
			foreach ($boxes as $boxrow) {
				$boxid = $boxrow["ServiceID"];
				$boxdesc = $boxrow["ServiceDescGB"];
				$found = strstr($GuideCodes, "|" . $boxid . "|");
				if (!$found) {
					$boxhtml .= "<input type=\"checkbox\" name=\"facility" . $boxid . "\" value=\"" . $boxid . "\" onClick=\"changecode(this,'" . $boxid . "')\"> " . $boxdesc . "<br />\n";
				} else {
					$boxhtml .= "<input type=\"checkbox\" name=\"facility" . $boxid . "\" value=\"" . $boxid . "\" checked onClick=\"changecode(this,'" . $boxid . "')\"> " . $boxdesc . "<br />\n";
				}
			}
			$listresults .= "<tr valign='top'><td><b>" . $boxestitle . "</b><br>" . $boxhtml . "</td><td></td></tr>\n";

			if ($GuideCategory == 1) {
				$listresults .= "<tr valign='top'><td><b>Facilities:</b><br><textarea cols=\"90\" rows=\"10\" name=\"GuideFacilities\" class=\"formtextarea\">" . $GuideFacilities . "</textarea></td><td>" . $ChangeGuideFacilities . "</td></tr>\n";
				//$listresults.="<tr valign='top'><td><b>Facilities Codes:</b> <i>S = shipyard, F = fuel, C = Chandlery, R = repairs W = wintering possible. WF = WiFi Enter with comma seperator no spaces e.g F,C,R</i><br><input type=\"text\" name=\"GuideCodes\" class=\"formcontrol\" size=\"50\" value=\"".$GuideCodes."\"></td></tr>\n";
				$listresults .= "<tr valign='top'><td><b>Costs:</b><br><textarea cols=\"90\" rows=\"10\" name=\"GuideCosts\" class=\"formtextarea\">" . $GuideCosts . "</textarea></td><td>" . $ChangeGuideCosts . "</td></tr>\n";
				$listresults .= "<tr valign='top'><td><b>Amenities:</b><br><textarea cols=\"90\" rows=\"10\" name=\"GuideAmenities\" class=\"formtextarea\">" . $GuideAmenities . "</textarea></td><td>" . $ChangeGuideAmenities . "</td></tr>\n";
			}
			$listresults .= "<tr valign='top'><td><b>Contributors:</b><br><textarea cols=\"90\" rows=\"10\" name=\"GuideContributors\" class=\"formtextarea\">" . $GuideContributors . "</textarea><td>" . $ChangeGuideContributors . "</td></td></tr>\n";
			$listresults .= "<tr valign='top'><td><b>Summary:</b> <i>Enter for first mooring only</i><br><textarea cols=\"90\" rows=\"10\" name=\"GuideSummary\" class=\"formtextarea\">" . $GuideSummary . "</textarea><td>" . $ChangeGuideSummary . "</td></td></tr>\n";
			$listresults .= "<tr valign='top'><td><b>Remarks:</b><br><textarea cols=\"90\" rows=\"10\" name=\"GuideRemarks\" class=\"formtextarea\">" . $GuideRemarks . "</textarea></td><td>" . $ChangeGuideRemarks . "</td></tr>\n";
			$listresults .= "<tr valign='top'><td><b>Posting Date:</b><br><input type=\"textbox\" name=\"GuidePostingDate\" id=\"GuidePostingDate\" class=\"formcontrol\" size=\"25\" readonly=\"true\" value=\"$GuidePostingDate\"></td><td></td></tr>\n";
			$listresults .= "<tr valign='top'><td><b>Last Update: </b>" . $GuideUpdatedisplay . "</td><td></td></tr>\n";
			$listresults .= "</table>\n";
			$listresults .= "</td></tr></table>\n";



			echo ($listresults);
			echo ("<input name=\"GuideCategory\" type=\"hidden\" value=\"$GuideCategory\">\n");
			echo ("<input name=\"country\" type=\"hidden\" value=\"$GuideCountry\">\n");
			echo ("<input name=\"waterway\" type=\"hidden\" value=\"$GuideWaterway\">\n");
			echo ("<input name=\"country_tmp\" type=\"hidden\" value=\"$GuideCountry\">\n");
			echo ("<input name=\"waterway_tmp\" type=\"hidden\" value=\"$GuideWaterway\">\n");
			echo ("<input name=\"GuideCodes\" type=\"hidden\" value=\"$GuideCodes\">\n");
			echo ("<input name=\"GuideNo\" type=\"hidden\" value=\"$GuideNo\">\n");
			echo ("<input name=\"GuideVer\" type=\"hidden\" value=\"$GuideVer\">\n");
			echo ("<input name=\"GuideEditorMemNo\" type=\"hidden\" value=\"$GuideEditorMemNo\">\n");
			echo ("<input name=\"submitteremail\" type=\"hidden\" value=\"$submitteremail\">\n");
			echo ("<input name=\"submitterid\" type=\"hidden\" value=\"$submitterid\">\n");
			echo ("<input name=\"Status0\" type=\"hidden\" value=\"$Status0\">\n");
			echo ("<input name=\"Status1\" type=\"hidden\" value=\"$Status1\">\n");
			echo ("<input name=\"Status2\" type=\"hidden\" value=\"$Status2\">\n");


		?>

			<SCRIPT LANGUAGE="JavaScript">
				function fix(num) {
					string = "" + num;
					numberofdigits = string.length;
					if (numberofdigits < 2) {
						return '0' + string;
					} else {
						return string;
					}
				}

				function catcalc2(cal) {
					if (cal.dateClicked) {
						// OK, a date was clicked

						var y = cal.date.getFullYear();
						var m = cal.date.getMonth(); // integer, 0..11
						var d = cal.date.getDate(); // integer, 1..31

						var date = new Date(y, m, d);
						var now = new Date();
						var diff = date.getTime() - now.getTime();
						var days = Math.floor(diff / (1000 * 60 * 60 * 24));
						var dbdate = y + "-" + fix((m + 1)) + "-" + fix(d);
						var field = document.getElementById("GuidePostingDate");
						field.value = dbdate;
						//"%A, %B %e, %Y",			
					}
				}

				Calendar.setup({
					inputField: "GuidePostingDate",
					ifFormat: "%Y-%m-%d",
					showsTime: true,
					timeFormat: "24",
					onUpdate: catcalc2
				});



				function Help(Subject) {
					var mypage = Subject;
					var myname = "help";
					//var w = (screen.width - 100);
					//var h = (screen.height - 100);
					var w = 530;
					var h = 300;
					var scroll = "yes";
					var winl = (screen.width - w) / 2;
					var wint = (screen.height - h) / 2;
					winprops = 'height=' + h + ',width=' + w + ',top=' + wint + ',left=' + winl + ',scrollbars=' + scroll + ',resizable'
					mypage += (mypage.indexOf('?') != -1 ? '&' : '?') + 'nocache=' + Date.now();
					win = window.open(mypage, myname, winprops)
					if (parseInt(navigator.appVersion) >= 4) {
						win.window.focus();
					}
				}

				function changecode(cbname, code) {


					var cur_str = document.form.GuideCodes.value;
					var state = cbname.checked;
					var str_search = "|" + code + "|";
					if (state == 0) {
						//remove it
						if (str_search == cur_str) {
							//only one so make blank
							var new_str = cur_str.replace(str_search, '');
						} else {
							var new_str = cur_str.replace(str_search, '|');
						}
					} else {
						//add it
						if (cur_str) {
							//already some data so add on end
							var new_str = cur_str + code + "|";
						} else {
							var new_str = "|" + code + "|";
						}
					}

					//alert(cur_str+" - "+new_str);
					document.form.GuideCodes.value = new_str;
				}

				function insertcountry(text) {
					var txtarea = document.form.GuideCountry;
					//text = ' ' + text + ' ';
					txtarea.value = text;
					document.form.keywords.options["0"].selected = true;
					txtarea.focus();
				}

				function insertwaterway(text) {
					var txtarea = document.form.GuideWaterway;
					//text = ' ' + text + ' ';
					txtarea.value = text;
					document.form.keywords.options["0"].selected = true;
					txtarea.focus();
				}

				function storeCaret(textEl) {
					if (textEl.createTextRange) textEl.caretPos = document.selection.createRange().duplicate();
				}

				function SubmitContent() {
					document.form.upload.src = "Images/common/livinga22.gif";
					document.form.save.value = 'Please Wait . . . . Updating . .';
					document.form.submit();
				}

				function DeleteContent() {
					if (confirm("Confirm deletion by clicking OK")) {
						document.form.submit();
					} else {
						document.form.assetaction.value = 'detail';
					}
				}
			</script>
			<?php




		}
		//---------------------------------------member edit or add new---------------------------------------------

		if ($guideaction == "memberedit") {

			//Find GuideNo from GuideID
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn($guidetable))
				->where($db->qn('GuideID') . ' = ' . $db->q($infoid));
			$result = $db->setQuery($query)->loadAssocList();
			foreach ($result as $row) {
				$GuideID = stripslashes($row["GuideID"]);
				$GuideNo = stripslashes($row["GuideNo"]);
				$GuideVer = stripslashes($row["GuideVer"]);
				$GuideCountry = stripslashes($row["GuideCountry"]);
				$GuideWaterway = stripslashes($row["GuideWaterway"]);
				$GuideSummary = stripslashes($row["GuideSummary"]);
				$GuideOrder = $row["GuideOrder"];
				$GuideName = stripslashes($row["GuideName"]);
				$GuideRef = stripslashes($row["GuideRef"]);
				$GuideLatLong = stripslashes($row["GuideLatLong"]);
				$GuideLocation = stripslashes($row["GuideLocation"]);
				$GuideMooring = stripslashes($row["GuideMooring"]);
				$GuideFacilities = stripslashes($row["GuideFacilities"]);
				$GuideCodes = stripslashes($row["GuideCodes"]);
				$GuideCosts = stripslashes($row["GuideCosts"]);
				$GuideRating = stripslashes($row["GuideRating"]);
				$GuideAmenities = stripslashes($row["GuideAmenities"]);
				$GuideContributors = stripslashes($row["GuideContributors"]);
				$GuideRemarks = stripslashes($row["GuideRemarks"]);
				$GuideLat = stripslashes($row["GuideLat"]);
				$GuideLong = stripslashes($row["GuideLong"]);
				$GuideDocs = stripslashes($row["GuideDocs"]);
				$GuidePostingDate = $row["GuidePostingDate"];
				$GuideCategory = stripslashes($row["GuideCategory"]);
				$GuideUpdate = $row["GuideUpdate"];
				$GuideStatus = stripslashes($row["GuideStatus"]);
				if ($GuideStatus == 1) {
					$statustext = "posted on site";
				} else {
					$statustext = "pending";
				}

				$GuideUpdatedisplay = (empty($row['GuideUpdate']) ? 'Date unknown' : date('Y-m-d', strtotime($row['GuideUpdate']))) . " - Mooring Index: " . $GuideNo . " - Version: " . $GuideVer;
			}


			//echo("<tr><td colspan=4> update $guidetable $GuideNo $GuideEditorMemNo $submitteremail");
			echo ("<tr><td colspan=4>");
			//Check if this GuideNo has any outstanding pending already and if so reject another edit and inform 'still pending'
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn($guidetable))
				->where($db->qn('GuideNo') . ' = ' . $db->q($GuideNo))
				->where($db->qn('GuideStatus') . ' = 0');
			$dup_check = $db->setQuery($query)->loadAssocList();
			$rows = count($dup_check);
			if ($rows) {
				//already pending
				$row = reset($dup_check);
				$GuideUpdate = $row["GuideUpdate"];

				echo ("<tr><td class=content_introduction><b>Member update</b><br />Thank you for helping to keep the guide up to date. However, we have found an update to this entry submitted for approval on $GuideUpdate and still pending. If this was from you, please wait until you have received confirmation about it from the editor before submitting further changes. If not from you, please try the update in a day or two. If it still doesn't work please email guideeditor@barges.org who will look at the problem.</td></tr>");

			?>
				<tr>
					<td class='bodytext'><input type="button" class="btn btn-primary" value="Back to the list" onClick="document.form.guideaction.value='list';document.form.submit()"><input type="button" class="btn btn-primary" value="Back to the map" onClick="document.form.guideaction.value='map';document.form.submit()"></td>
				</tr>

		<?php

			} else {

				include("guides_edit.php");
			}

			//store non member fields to pass through to new version
			echo ("<input name=\"country\" type=\"hidden\" value=\"$GuideCountry\">\n");
			echo ("<input name=\"waterway\" type=\"hidden\" value=\"" . stripslashes($GuideWaterway) . "\">\n");
			echo ("<input name=\"country_tmp\" type=\"hidden\" value=\"$GuideCountry\">\n");
			echo ("<input name=\"waterway_tmp\" type=\"hidden\" value=\"$GuideWaterway\">\n");

			echo ("<input name=\"GuideOrder\" type=\"hidden\" value=\"$GuideOrder\">\n");
			echo ("<input name=\"GuideDocs\" type=\"hidden\" value=\"$GuideDocs\">\n");
			echo ("<input name=\"GuideSummary\" type=\"hidden\" value=\"$GuideSummary\">\n");
			echo ("<input name=\"GuidePostingDate\" type=\"hidden\" value=\"$GuidePostingDate\">\n");
			echo ("<input name=\"GuideCategory\" type=\"hidden\" value=\"$GuideCategory\">\n");
			echo ("<input name=\"GuideCodes\" type=\"hidden\" value=\"$GuideCodes\">\n");
			echo ("<input name=\"GuideNo\" type=\"hidden\" value=\"$GuideNo\">\n");
			echo ("<input name=\"GuideVer\" type=\"hidden\" value=\"$GuideVer\">\n");
			echo ("<input name=\"guidesemail\" type=\"hidden\" value=\"$guidesemail\">\n");
			echo ("</td></tr>\n");
		}


		if ($guideaction == "adminreports") {

			echo ("<tr><td colspan=4>");
			//echo("<input name=\"country\" type=\"hidden\" value=\"$country\">");
			//echo("<input name=\"waterway\" type=\"hidden\" value=\"$waterway\">");

			include("guides_adminreports.php");

			//store non member fields to pass through to new version

			echo ("</td></tr>\n");
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
