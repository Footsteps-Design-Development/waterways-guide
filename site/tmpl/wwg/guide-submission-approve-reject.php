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

$guideaction = "list";
