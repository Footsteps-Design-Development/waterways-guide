<?php



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
