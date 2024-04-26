<?php


/*

Updates ................................
20140302 Cron split to seperate classified and avoid ocasional crashing of membership cron
*/

//cron command   /usr/local/bin/php -q /home/bargesor/public_html/components/com_waterways_guide/cron/cron_classified.php

error_reporting(E_ALL);

ini_set('display_errors', 1);

ini_set('log_errors', 1);
ini_set('error_log', '/home/customer/www/barges.org/public_html/logs/classified_cron.log'); // Replace '/path/to/error.log' with the actual path and filename where you want to store the error log.

error_log('Execution started.'); // Example log statement

//load Joomla helpers for emailsending
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', strstr(__DIR__, 'public_html', true) . 'public_html');
define('JPATH_COMPONENT', JPATH_BASE . DS . 'components' . DS . 'com_waterways_guide');
require_once(JPATH_BASE . DS . 'includes' . DS . 'defines.php');
require_once(JPATH_BASE . DS . 'includes' . DS . 'framework.php');
require_once(JPATH_COMPONENT . DS . 'commonV3.php');

use Joomla\CMS\Factory;

$config = Factory::getConfig();
$mailOn = Factory::getConfig()->get('mailonline') == '1';

$db = Factory::getDBO();


//simulate or live
$live = 1;
$livemail = 1;
$livemailadmin = 1;

$htmlheader .= "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n<html>\n<head>\n";

$htmlheader .= "<style type=\"text/css\"><!--\n.content {\n	font-family: Arial, Helvetica, sans-serif;\n	font-size: 90%;\n	font-style: normal;\n	font-weight: normal;\n}\n-->\n</style>\n";

$htmlheader .= "</head>\n";
$htmlheader .= "<body>\n";


//reset reporting values  
$classifiedpostings = 0;
$classifiedalerts = 0;
$classifiedrenewalreminder = 0;
$classifiedexpired = 0;

$datenow = date("Y-m-d");


//check classified new posting and inform members opting in ---------------------------------------------------------------
$pagesection = "adverts/search";

//find all status 1 records starting today, e.g. made live today
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblClassified'))
	->where($db->qn('ClassifiedStatus') . ' = 1')
	->where($db->qn('ClassifiedStartDate') . ' >= CURDATE()')
	->order($db->qn(['ClassifiedSection', 'ClassifiedTitle']));
$rows = 0;
$classifieds = $db->setQuery($query)->loadAssocList();
$num_rows = count($classifieds);
if ($num_rows > 0) {
	$classifiedpostings = count($classifieds);
	foreach ($classifieds as $row) {
		$ClassifiedID = stripslashes($row["ClassifiedID"]);
		$ClassifiedSection = stripslashes($row["ClassifiedSection"]);
		$ClassifiedRef = stripslashes($row["ClassifiedRef"]);
		$ClassifiedTitle = trim(stripslashes($row["ClassifiedTitle"]));
		$ClassifiedDescription = trim(stripslashes($row["ClassifiedDescription"]));
		$ClassifiedLocation = stripslashes($row["ClassifiedLocation"]);
		$ClassifiedSeekOffer = $row["ClassifiedSeekOffer"];
		if ($ClassifiedSeekOffer == "o") {
			$ClassifiedSeekOfferDesc = "Offered";
		}
		if ($ClassifiedSeekOffer == "w") {
			$ClassifiedSeekOfferDesc = "Wanted";
		}
		$classifiedmatch = 1;
		if ($thissection != $ClassifiedSection) {
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('tblClassifiedSections'))
				->where($db->qn('ClassifiedSectionID') . ' = ' . $db->q($ClassifiedSection));
			$sectionrow = $db->setQuery($query)->loadAssoc();
			$ClassifiedSectionDesc = $sectionrow["ClassifiedSectionName"];
			$listresults[$ClassifiedSection] = strtoupper($ClassifiedSectionDesc);
			$thissection = $ClassifiedSection;
		}

		$sectionlist[$ClassifiedSection] .= "$ClassifiedTitle ($ClassifiedLocation) - $ClassifiedSeekOfferDesc\n\n";
		$sectionlist[$ClassifiedSection] .= $ClassifiedDescription . "\n";
		$sectionlist[$ClassifiedSection] .= "Direct link: www.barges.org/" . $pagesection . "?classifiedaction=detail&classifiedid=" . $ClassifiedID . "\n";
		$sectionlist[$ClassifiedSection] .= "****************************************************\n\n";
	}

	//lookup all members status 2 paidup or 6 complimentary who have services
	$query = $db->getQuery(true)
		//->select('*')
		->select('Email, LastName, FirstName, Login, ID, Services')
		->from($db->qn('tblMembers'))
		->where($db->qn('Services') . " <> ''")
		->where("(" . $db->qn('MemStatus') . " = '2' OR " . $db->qn('MemStatus') . " = '6')");
	$mymembers = $db->setQuery($query)->loadAssocList();
	$num_rows = count($mymembers);
	if ($num_rows > 0) {
		$num_members = count($mymembers);

		//$maxsections=sizeof($sectionlist);
		$maxsections = 9;
		$memberEmailsSent = array(); // Track member emails that have been sent
		
		foreach ($mymembers as $row) {

			$memberemail = $row["Email"];
			$LastName = $row["LastName"];
			if ($row["FirstName"] && $row["LastName"]) {
				$FullName = $row["FirstName"] . " " . $row["LastName"];
			} else {
				$FullName = "DBA Member";
			}
			$membername = $row["Login"];
			$userid = $row["ID"];
			$Services = $row["Services"];
			$membermessage = "";
			$section = 1;
			//echo("$login<br>");

			//	Let's ensure each member only receives one email using the following line
			if (!in_array($memberemail, $memberEmailsSent)) {

				//go through any sections and assemble a message for this member
				while ($section <= $maxsections) {
					//while($section<$maxsections){
					if ($sectionlist[$section]) {
						//echo($sectionlist[$section]."$section<br>");
						$found = strstr($Services, "|" . $section . "|");
						if ($found) {
							//they want this alert
							$membermessage .= "" . $listresults[$section] . "\n\n";
							$membermessage .= "" . $sectionlist[$section] . "\n\n";
						}
					}
					$section += 1;
				}
				if ($membermessage) {
					// We have a message, so send the email
					$classifiedalerts += 1;
					$subject = "Classified alert from the $sitename";
					$message = "The following Classified posting(s) match alerts that you have requested in your $sitename membership profile\n\n";
					$message .= "To view all classified ads go to " . $siteurl . "/adverts/search\n\n";
					$message .= $membermessage;
					$message .= "" . $emailfooter;

					if ($livemail == 1) {
						if ($memberemail) {
							if ($mailOn) {
								$mailer = Factory::getMailer();
								$mailer->setSender([$config->get('mailfrom'), 'Classified submissions']);
								$mailer->addRecipient($memberemail);
								$mailer->addReplyTo($classifiedemail);
								$mailer->setSubject($subject);
								$mailer->setBody(nl2br($message));
								$mailer->isHtml(true);
								$mailer->Send();
							} else {
								echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";
							}
							// Add the member's email to the sent list
							$memberEmailsSent[] = $memberemail;
						}
					}
				}
			}
		}
	} else {
		$classifiedalerts = 0;
	}
}

//check classified renewals and notify posters ---------------------------------------------------------------
$pagesection = "adverts/search";
$datenow = date("Y-m-d");
//find all status 1 records within 14 days of expiry
$query = $db->getQuery(true)
	//->select('*')
	->select('ClassifiedID, ClassifiedSection, ClassifiedRef, ClassifiedTitle, ClassifiedDescription, ClassifiedLocation, ClassifiedSeekOffer')
	->from($db->qn('tblClassified'))
	->where($db->qn('ClassifiedStatus') . ' = 1')
	//->where($db->qn('ClassifiedEndDate') . ' > CURDATE() + INTERVAL 12 DAY')
	//->where($db->qn('ClassifiedEndDate') . ' < CURDATE() + INTERVAL 13 DAY')
    ->where($db->qn('ClassifiedStartDate').' >= CURDATE()')
	->order($db->qn(['ClassifiedSection', 'ClassifiedTitle']));



$rows = 0;
$classifieds = $db->setQuery($query)->loadAssocList();
$num_rows = count($classifieds);
if ($num_rows > 0) {
	$classifiedrenewals = count($classifieds);
	foreach ($classifieds as $row) {
		$ClassifiedID = stripslashes($row["ClassifiedID"]);
		$ClassifiedMemberID = $row["ClassifiedMemberID"];
		$ClassifiedSection = stripslashes($row["ClassifiedSection"]);
		$ClassifiedRef = stripslashes($row["ClassifiedRef"]);
		$ClassifiedTitle = trim(stripslashes($row["ClassifiedTitle"]));
		$ClassifiedDescription = trim(stripslashes($row["ClassifiedDescription"]));
		$ClassifiedLocation = stripslashes($row["ClassifiedLocation"]);
		$ClassifiedSeekOffer = $row["ClassifiedSeekOffer"];
		$ClassifiedContactEmail = stripslashes($row["ClassifiedContactEmail"]);
		$ClassifiedStartDate = date("d M Y", strtotime($row["ClassifiedStartDate"]));
		$ClassifiedEndDate = date("d M Y", strtotime($row["ClassifiedEndDate"]));
		$classifiedrenewalreminder += 1;
		$subject = "Classified renewal offer from the $sitename";
		$message = "The following Classified ad that you posted on " . $ClassifiedStartDate . " will expire in 14 days.\n";
		$message .= "$ClassifiedTitle ($ClassifiedLocation) - $ClassifiedSeekOfferDesc $ClassifiedEndDate\n\n";
		$message .= $ClassifiedDescription . "\n\n";
		$message .= "Direct link: " . $siteurl . "/adverts/search?classifiedaction=edit&classifiedid=" . $ClassifiedID . "\n\n";
		$message .= "If you DO NOT wish to extend it, do nothing and it will be removed within the next 14 days.\n";
		$message .= "If you DO wish to extend it, go to www.barges.org/login-form log in and edit your classified entry where you can select an extension option near the bottom of the edit page. This is also an opportunity to review the details.\n\n";
		$message .= "To view all classified ads go to " . $siteurl . "/adverts/search\n\n";
		$message .= "We hope that you have found the Member free classified ads section useful and simple to use. Any feedback is always welcome to classified@barges.org\n\n";
		$message .= "" . $emailfooter;
		//echo(nl2br("$ClassifiedContactEmail<br>$message<br><br>"));
		if ($livemail == 1) {
			if ($ClassifiedContactEmail) {
				if ($mailOn) {
					$mailer = Factory::getMailer();
					$mailer->setSender([$config->get('mailfrom'), 'Classified submissions']);
					$mailer->addRecipient($ClassifiedContactEmail);
					$mailer->addReplyTo($classifiedemail);
					$mailer->setSubject($subject);
					$mailer->setBody(nl2br($message));
					$mailer->isHtml(true);
					$mailer->Send();
				} else echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";
			}
		}
		//update change log
		$changelogtext = addslashes($ClassifiedRef . " '" . $ClassifiedTitle . "' renewal email sent");
		$datenow = date("Y-m-d H:i:s");
		$subject = "Classified";
		$insert = new \stdClass();
		$insert->MemberID = $ClassifiedMemberID;
		$insert->Subject = $subject;
		$insert->ChangeDesc = $changelogtext;
		$insert->ChangeDate = $datenow;
		$db->insertObject('tblChangeLog', $insert) or die("Couldn't update change log");
	}
}

//check classified expiries and delete ---------------------------------------------------------------
$pagesection = "adverts/search";
//find all status 1 records 0 days over expiry
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblClassified'))
	->where($db->qn('ClassifiedStatus') . ' = 1')
	->where($db->qn('ClassifiedEndDate') . ' < CURDATE()')
	->order($db->qn(['ClassifiedSection', 'ClassifiedTitle']));
$rows = 0;
$classifieds = $db->setQuery($query)->loadAssocList();
$num_rows = count($classifieds);
if ($num_rows > 0) {
	$classifiedexpirers = count($classifieds);
	foreach ($classifieds as $row) {
		$ClassifiedID = stripslashes($row["ClassifiedID"]);
		$ClassifiedMemberID = $row["ClassifiedMemberID"];
		$ClassifiedSection = stripslashes($row["ClassifiedSection"]);
		$ClassifiedRef = stripslashes($row["ClassifiedRef"]);
		$ClassifiedTitle = trim(stripslashes($row["ClassifiedTitle"]));
		$ClassifiedDescription = trim(stripslashes($row["ClassifiedDescription"]));
		$ClassifiedLocation = stripslashes($row["ClassifiedLocation"]);
		$ClassifiedSeekOffer = $row["ClassifiedSeekOffer"];
		$ClassifiedContactEmail = stripslashes($row["ClassifiedContactEmail"]);
		$ClassifiedStartDate = date("d M Y", strtotime($row["ClassifiedStartDate"]));
		$ClassifiedEndDate = date("d M Y", strtotime($row["ClassifiedEndDate"]));
		//$ClassifiedEndDate="2009-05-07 00:00:00";
		$Status = 4;
		//set the archive status
		$update = new \stdClass();
		$update->ClassifiedStatus = $Status;
		$update->ClassifiedID = $ClassifiedID;
		$db->updateObject('tblClassified', $update, 'ClassifiedID') or die("Couldn't update status");

		$subject = "Classified expiry on the $sitename";
		$headers = "From: Classified submissions <$classifiedemail>\n";
		$message = "The following Classified ad that you posted on " . $ClassifiedStartDate . " has now been removed following your decision not to extend it.\n";

		$message .= "$ClassifiedTitle ($ClassifiedLocation) - $ClassifiedSeekOfferDesc $ClassifiedEndDate\n\n";
		$message .= $ClassifiedDescription . "\n\n";
		$message .= "Direct link: " . $siteurl . "/adverts/search?classifiedaction=edit&classifiedid=" . $ClassifiedID . "\n\n";

		$message .= "To view all classified ads go to " . $siteurl . "/adverts/search\n\n";
		$message .= "We hope that you have found the Member free classified ads section useful and simple to use. Any feedback is always welcome to classified@barges.org\n\n";
		$classifiedexpired += 1;

		$message .= "" . $emailfooter;
		//echo(nl2br("$ClassifiedContactEmail<br>$message<br><br>"));
		if ($livemail == 1) {
			if ($memberemail) {
				if ($mailOn) {
					$mailer = Factory::getMailer();
					$mailer->setSender([$config->get('mailfrom'), 'Classified submissions']);
					$mailer->addRecipient($ClassifiedContactEmail);
					$mailer->addReplyTo($classifiedemail);
					$mailer->setSubject($subject);
					$mailer->setBody(nl2br($message));
					$mailer->isHtml(true);
					$mailer->Send();
				} else echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";
			}
		}
		//update change log
		$changelogtext = addslashes($ClassifiedRef . " '" . $ClassifiedTitle . "' removed");
		$datenow = date("Y-m-d H:i:s");
		$subject = "Classified";
		$insert = new \stdClass();
		$insert->MemberID = $ClassifiedMemberID;
		$insert->Subject = $subject;
		$insert->ChangeDesc = $changelogtext;
		$insert->ChangeDate = $datenow;
		$db->insertObject('tblChangeLog', $insert) or die("Couldn't update change log");
	}
}

//update stats

$ClassifiedPosts = $classifiedpostings;
$ClassifiedAlerts = $classifiedalerts;

$StatDate = date("Y-m-d H:i:s");

$today_start = date("Y-m-d 00:00:00");
$today_end = date("Y-m-d 23:59:59");
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblStats'))
	->where($db->qn('StatDate') . ' BETWEEN ' . $db->q($today_start) . ' AND ' . $db->q($today_end));
$result = $db->setQuery($query)->loadAssocList() or die("Couldn't find existing entry");
$num_rows = count($result);
if ($num_rows > 0) {
	//udate existing entry with classified
	$row = reset($result);
	$StatDate = $row["StatDate"];
	$update = new \stdClass();
	$update->ClassifiedPosts = $ClassifiedPosts;
	$update->ClassifiedAlerts = $ClassifiedAlerts;
	$update->StatDate = $StatDate;
	$db->updateObject('tblStats', $update, 'StatDate') or die("Couldn't update status");
	echo (print_r($update, true) . " update tblStats - $ClassifiedPosts - $ClassifiedAlerts");
} else {
	//add new entry
	$insert = new \stdClass();
	$insert->MemTot = '0';
	$insert->MemLive = '0';
	$insert->MemDD = '0';
	$insert->MemBO = '0';
	$insert->MemCH = '0';
	$insert->MemCC = '0';
	$insert->MemFOC = '0';
	$insert->MemOrdinary = '0';
	$insert->MemFamily = '0';
	$insert->MemUK = '0';
	$insert->MemEU = '0';
	$insert->MemZ1 = '0';
	$insert->MemZ2 = '0';
	$insert->MemOwner = '0';
	$insert->MemDreamer = '0';
	$insert->MemCommercial = '0';
	$insert->MemTerminated = '0';
	$insert->MemNew = '0';
	$insert->GuideRequests = '0';
	$insert->ClassifiedPosts = $ClassifiedPosts;
	$insert->ClassifiedAlerts = $ClassifiedAlerts;
	$insert->StatDate = $StatDate;
	echo (print_r($insert, true) . " insert into tblStats - $ClassifiedPosts - $ClassifiedAlerts");
	$update = $db->insertObject('tblStats', $insert);
	if (!$update) {
		echo ("Couldn't insert latest stats");
	}
}
