<?php

echo ("<input name=\"cat_guides\" type=\"hidden\" value=\"$cat_guides\">");
echo ("<input name=\"showclosed\" type=\"hidden\" value=\"$showclosed\">");
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

$user       = Factory::getUser();
$login_memberid = $user->id;
if($user->guest) {
    $link  = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode(JUri::current()), "You must be logged in to view this content");
    Factory::getApplication()->redirect($link);
}
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
    $update = $db->insertObject('#__waterways_guide_changelog', $insert);
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
