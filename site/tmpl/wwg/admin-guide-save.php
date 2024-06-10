<?php
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

$user = Factory::getUser();
$login_memberid = $user->id;
if ($user->guest) {
    $link  = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode(JUri::current()), "You must be logged in to view this content");
    Factory::getApplication()->redirect($link);
}

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
    // Entry OK, so update
    $updates = 0;
    $GuideUpdate = date("Y-m-d H:i:s");
    if (!$GuidePostingDate) {
        $GuidePostingDate = $GuideUpdate;
    }
    $updatetext = "";
    $subject = "Guides";

    if ($infoid == "newmooring" || $infoid == "newhazard") {
        // Add new
        $GuideStatus = 1; // live
        $GuideVer = 1; // start at v1
        if ($GuideLat == "51.67256") {
            // Default mid-channel still there, so make blank for 'unknown'
            $GuideLat = "";
            $GuideLong = "";
        }
        $newby = 1;
        $GuideEditorMemNo = $login_memberid;

        $GuideWaterway = str_replace(chr(146), chr(39), $GuideWaterway); // `'
        $GuideWaterway = str_replace(chr(34), chr(39), $GuideWaterway); // "'
        $insert = new stdClass();
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
        $insert->GuideRating = $GuideRating !== '' ? (int)$GuideRating : null;
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

        // Check if GuideOrder is empty or not a numeric value
        if (empty($insert->GuideOrder) || !is_numeric($insert->GuideOrder)) {
            // Set a default value
            $insert->GuideOrder = 1.00;
        }

        $result = $db->insertObject($guidetable, $insert, 'GuideID');
        if (!$result) {
            die("Couldn't update database");
        }

        // Get ID and update GuideNo and GuideVer
        $GuideNo = $insert->GuideID;
        $update = new stdClass();
        $update->GuideNo = $GuideNo;
        $update->GuideVer = $GuideVer;
        $update->GuideID = $GuideNo;
        $update->GuideRating = (int) $GuideRating;

        try {
            $result = $db->updateObject($guidetable, $update, 'GuideID');
            if (!$result) {
                echo ("Couldn't update guide ");
            }
        } catch (Exception $e) {
            error_log("Database error: " . $e->getMessage());
        }

        if ($GuideCategory == 2) {
            $changelogtext = "Guide hazard - '" . $GuideName . "'(" . $GuideWaterway . ") added";
        } else {
            $changelogtext = "Guide mooring - '" . $GuideName . "'(" . $GuideWaterway . ") added";
        }
        $updates = 1;

    } elseif ($infoid > 0) {
        // Admin update
        $GuideWaterway = str_replace(chr(146), chr(39), $GuideWaterway); // `'
        $GuideWaterway = str_replace(chr(34), chr(39), $GuideWaterway); // "'
        $update = new stdClass();
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
        $update->GuideRating = $GuideRating !== '' ? (int)$GuideRating : null;
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
        // Update log
        $updatetext .= $changelogtext . "<br>";
        $insert = new stdClass();
        $insert->MemberID = $login_memberid;
        $insert->Subject = $subject;
        $insert->ChangeDesc = $changelogtext;
        $insert->ChangeDate = $GuideUpdate;
        $update = $db->insertObject('#__waterways_guide_changelog', $insert);
        if (!$update) {
            echo ("Couldn't update changelog");
        } else {
            $message = "The change history log and site have been updated with the following details:<br>\n";
            $message .= $updatetext . "<br>\n";
        }
    }

    // exit();    
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
