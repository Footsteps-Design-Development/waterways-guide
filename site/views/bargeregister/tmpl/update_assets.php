<?php

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

$app = Factory::getApplication('com_waterways_guide');

?><table border="0" cellspacing="2" cellpadding="3" width="100%">
<?php
/* CHANGE LOG
20100202 check to avoid 'Unknown' new barge by use of vesselname entry box in $assetaction="list";



*/




$test_vars1=(array(
'statusmessage',
'assetinfo',
'login_MembershipNo',
'vesselname',
'vessellength',
'vesselbeam',
'vesselclass',
'Detachee',
'asset_login_membershipno',
'asset_login_memberid',
'detail',
'selectit',
'olist',
'imagedetail',
'AssetTitle',
'error',
'AssetCategory',
'AssetCategoryDesc',
'ContactPrivacyPhone',
'ContactPrivacyEmail',
'AssetDescription',
'ContactPrivacyMail',
'AssetOptions',
'AssetPrivacy',
'thefile'
));


foreach($test_vars1 as $test_var) { 
	if(!$$test_var =  $app->input->getString($test_var)){
		$$test_var = "";
	}
}




if($admin=="open"){
	echo("<tr><td colspan=3 class=table_stripe_blue>Editing member record by administrator ".$contact." - vessed ID ".$vesselid."</td></tr>\n");	
}

$editlisthelp="You may add additional barges to your profile by entering the name in the box and clicking the 'Add a new barge' link.<br><br>

Once you have a barge in your profile, click on the 'Barge Name' link for detail and then you may add, edit or delete each 'feature' or 'detail'.<br><br>
If you are no longer the keeper of the barge, you can detach it from your profile by clicking the 'Detach' icon below.<br><br>
Responsibility for the barge entry will then be handed over to the DBA Administrator until a new owner is identified. Meanwhile, the barge details will remain available for members to search and view but you will no longer be known as the keeper and all contact options will be removed.<br><br>
If you wish to delete a barge from the register, click on the 'Delete' icon below.<br><br>
Only delete if you have made an error and wish to start again.<br><br>
Otherwise, if you are no longer the keeper, use the 'Detach' icon.";


$edithelp="To add new detail, select the type of detail in the drop-down below and click on 'Go'.<br>
Information shown may be edited or deleted by clicking the 'Edit' pencil icon on the right of each section. Remember to click 'Save' for each detail.<br>
Please make sure to add at least the details:<br>
Year Built<br>
Class<br>
Length<br>
Beam<br>
as separate details in the dropdown box, selecting 'Class' from the options provided , if possible.";

/*$edithelp="Information shown can be edited or deleted by clicking the 'Edit' icon in the right hand margin for each one. 
<br>To add a new detail, select the type in the drop-down below and click on the icon. <font color=#ff0000><b>Please make sure to add at least the year built, class, length and beam as seperate details from the dropdown</b></font>";
*/

/*$editlisthelp="Click on the barge name for the detail where you can then edit each one. <br>If you are <b>no longer the keeper of this barge</b>, you can 'detach' it from this profile by clicking the icon below.
Responsibility for the barge entries will then be handed over to the DBA administrator until a new owner is identified.
Meanwhile, the barge details will still be available for members to search and view in the register but you will no-longer be known as the keeper and all contact options will be removed.
<br>If you wish to delete a barge from the register, click on the 'delete' icon below. <b><font color=#ff0000>Only do this if you have made an error and wish to start again.</b></font>
Otherwise, use the detach' icon if you are no longer the keeper.
<br>You can add additional barges to this profile by entering the name in the box and clicking 'Add a new barge'";

*/




//---------------------------------------Add new vessel------------------------------------------

if ($assetaction=="addvessel") {
	if($vesselid=="new" && $vesselname){
		//add a blank entry
		$datenow = date("Y-m-d H:i:s");
		$Status=1;
		if(!$MembershipNo){
			//no membership number so must be not logged in?
			echo("Please re-login");
			exit();
		}	
		$ItemStatus=1;
		$insert = new \stdClass();
		$insert->MembershipNo = $MembershipNo;
		$insert->Ship = $vesselname;
		$insert->Status = $Status;
		$insert->Date = $datenow;
		$insert->LastUpdate = $datenow;
		$result = $db->insertObject('tblAssetsMembers', $insert, 'ID');
		if(!$result){die ("Couldn't update database ".print_r($insert, true));}
		//get the new vesselid
		$vesselid = $insert->ID;
		
		//add the name to the asset table
		$AssetDate=$datenow;
		$AssetStatus=1;
		$AssetCategory=1;
		$AssetCategoryDesc="Barge Name";
		$AssetTitle=$vesselname;
		$AssetDescription="";
		$insert = new \stdClass();
		$insert->VesselID = $vesselid;
		$insert->AssetCategory = $AssetCategory;
		$insert->AssetCategoryDesc = $AssetCategoryDesc;
		$insert->AssetTitle = $AssetTitle;
		$insert->AssetDate = $AssetDate;
		$insert->AssetStatus = $AssetStatus;
		$result = $db->insertObject('tblAssets', $insert, 'AssetID');
		if(!$result){die ("Couldn't update database ".print_r($insert, true));}
		$assetid = $insert->AssetID;
		$statusmessage=$statusmessage."New barge '".$vesselname."' added - please add and save more details as you can";
		//look up existing keeper membershipnumber and id and any previous ones
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('tblAssetsMembers'))
			->where($db->qn('ID').' = '.$db->q($vesselid));
		$vrow = $db->setQuery($query)->loadAssoc();
		$CurrentMembershipNo = stripslashes($vrow["MembershipNo"]);
		$keepers = $vrow["Keeper"];
		if($keepers){
			//add the current keeper MembershipNo to any others to provide archive history
			$keepers.=",".$CurrentMembershipNo;
		}else{
			$keepers=$CurrentMembershipNo;
		}
		//now lookup member id
		$query = $db->getQuery(true)
			->select($db->qn('ID'))
			->from($db->qn('tblMembers'))
			->where($db->qn('MembershipNo').' = '.$db->q($CurrentMembershipNo));
		$asset_login_memberid = $db->setQuery($query)->loadResult();

		
		$changelogtext="New barge '".$vesselname."' added.";
		$subject="Register";
		$insert = new \stdClass();
		$insert->MemberID = $asset_login_memberid;
		$insert->Subject = $subject;
		$insert->ChangeDesc = $changelogtext;
		$insert->ChangeDate = $datenow;
		$db->insertObject('tblChangeLog', $insert) or die ("Couldn't update change log");	
		$screenmessage.="Register update, new barge '".$vesselname."' added.";
		$assetaction="edit";
		
		
		//update details in membership table for quick reference and filter searching
		$Ship=$vesselname;
		//lookup member keyword details to add ship name, class and year
		$oldkeywords="";
		$newkeywords="";
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('tblMembers'))
			->where($db->qn('MembershipNo').' = '.$db->q($CurrentMembershipNo));
		$mykeywords = $db->setQuery($query)->loadAssocList();
		$num_keywords= count($mykeywords);
		if (empty($num_keywords)) {	
			$screenmessage="Can't find keywords." ;
		}else{
			$keywordrow = reset($mykeywords);
			$oldkeywords=$keywordrow["Keywords"];
			$newkeywords=$oldkeywords;
			if ($Ship && false === strpos($oldkeywords, $Ship)) {
				$newkeywords = $newkeywords . " ".$Ship;
			}
			
			//add ship details to member table
			$update = new \stdClass();
			$update->Shipname = $Ship;
			$update->Keywords = $newkeywords;
			$update->MembershipNo = $Detachee;
			$db->updateObject('tblMembers', $update, 'MembershipNo') or die ("Couldn't update membership ship data");
		}
		
		
	}else{
		$statusmessage="Please enter the barge name in the box";

		$assetaction="list";
	}
}

//---------------------------------------Variable stores------------------------------------------

//echo("<input name=\"assetid\" type=\"hidden\" value=\"$assetid\">\n");
//echo("<input name=\"assetsort\" type=\"hidden\" value=\"$assetsort\">\n");
//echo("<input name=\"assetaction\" type=\"hidden\" value=\"$assetaction\">\n");
//echo("<input name=\"vesselid\" type=\"hidden\" value=\"$vesselid\">\n");
//echo("<input name=\"asset_login_memberid\" type=\"hidden\" value=\"$asset_login_memberid\">\n");

//---------------------------------------Detach vessel------------------------------------------

if ($assetaction=="detachvessel") {
	$datenow = date("Y-m-d H:i:s");
	//lookup assets to find name
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblAssets'))
		->where($db->qn('VesselID').' = '.$db->q($vesselid))
		->order($db->qn('AssetCategory'));
	try {
		$result = $db->setQuery($query)->loadAssocList();
	} catch(Exception $e) {
		echo("Can't find assets");
		exit();
	}
	
	$num_assets = count($result);
	$vesselname="";
	foreach($result as $row) {

		# Display asset Results, l
		$AssetID = stripslashes($row["AssetID"]);
		$AssetCategory = stripslashes($row["AssetCategory"]);
		$AssetCategoryDesc = stripslashes($row["AssetCategoryDesc"]);				
		$AssetTitle = stripslashes($row["AssetTitle"]);
		
		if($AssetCategory==1){
			$vesselname=$AssetTitle;
		}
	}

	//now transfer vessel id to admin or new owner	
 	//look up existing keeper membershipnumber and id and any previous ones
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblAssetsMembers'))
		->where($db->qn('ID').' = '.$db->q($vesselid));
	$vrow = $db->setQuery($query)->loadAssoc();
	$CurrentMembershipNo = stripslashes($vrow["MembershipNo"]);
	$keepers = $vrow["Keeper"];
	if($keepers){
		//add the current keeper MembershipNo to any others to provide archive history
		$keepers.=",".$CurrentMembershipNo;
	}else{
		$keepers=$CurrentMembershipNo;
	}
	//now lookup member id
	$query = $db->getQuery(true)
		->select($db->qn('ID'))
		->from($db->qn('tblMembers'))
		->where($db->qn('MembershipNo').' = '.$db->q($CurrentMembershipNo));
	$asset_login_memberid = $db->setQuery($query)->loadResult();
	if($Detachee){
		//detach to new member
		$update = new \stdClass();
		$update->MembershipNo = $Detachee;
		$update->LastUpdate = $datenow;
		$update->Keeper = $keepers;
		$update->Status = '1';
		$update->ID = $vesselid;
		$result = $db->updateObject('tblAssetsMembers', $update, 'ID');
		if(!$result){die ("Couldn't update database");}
		$statusmessage.="Barge '$vesselname' detached from this profile to member ".$Detachee;
		
		//update details of new keeper in membership table for quick reference and filter searching
		$Ship="";
		$Class="";
		$Year="";
		$Length="";
		$Beam="";

		//lookup vessel basic info
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('tblAssets'))
			->where($db->qn('VesselID').' = '.$db->q($vesselid));
		$mybarge = $db->setQuery($query)->loadAssocList();
		$num_barge= count($mybarge);
		if (empty($num_barge)) {	
			$screenmessage="Can't find barges." ;
		}else{

			foreach($mybarge as $bargerow) {
				if($bargerow["AssetCategory"]==1){
					$Ship=$bargerow["AssetTitle"];
				}
				if($bargerow["AssetCategory"]==4){
					$Length=$bargerow["AssetTitle"];
				}
				if($bargerow["AssetCategory"]==5){
					$Beam=$bargerow["AssetTitle"];
				}
				if($bargerow["AssetCategory"]==7){
					$Year=$bargerow["AssetTitle"];
				}
				if($bargerow["AssetCategory"]==3){
					$Class=$bargerow["AssetTitle"];
				}

				//lookup new keeper member keyword details to add ship name, class and year
				$oldkeywords="";
				$newkeywords="";
				$query = $db->getQuery(true)
					->select('*')
					->from($db->qn('tblMembers'))
					->where($db->qn('MembershipNo').' = '.$db->q($Detachee));
				$mykeywords = $db->setQuery($query)->loadAssocList();
				$num_keywords= count($mykeywords);
				if (empty($num_keywords)) {	
					$screenmessage="Can't find keywords." ;
				}else{
					$keywordrow = reset($mykeywords);
					$oldkeywords=$keywordrow["Keywords"];
					$newkeywords=$oldkeywords;
					if ($Ship && false === strpos($oldkeywords, $Ship)) {
						$newkeywords = $newkeywords . " ".$Ship;
					}
					if ($Year && false === strpos($oldkeywords, $Year)) {
						$newkeywords = $newkeywords . " ".$Year;
					}
					if ($Class && false === strpos($oldkeywords, $Class)) {
						$newkeywords = $newkeywords . " ".$Class;
					}
					//add ship details to member table
					$update = new \stdClass();
					if(!empty($Ship)) $update->Shipname = $Ship;
					if(!empty($Class)) $update->ShipClass = $Class;
					if(!empty($Year)) $update->ShipYear = $Year;
					if(!empty($Length)) $update->ShipLength = $Length;
					if(!empty($Beam)) $update->ShipBeam = $Beam;
					$update->Keywords = $newkeywords;
					$update->MembershipNo = $Detachee;
					$db->updateObject('tblMembers', $update, 'MembershipNo') or die ("Couldn't update membership ship data");
				}
			}

		}
		
		
		//remove previous keeper details in membership table for quick reference and filter searching
		//lookup member keyword details to compare and remove ship name, class and year
		$oldkeywords="";
		$newkeywords="";
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('tblMembers'))
			->where($db->qn('MembershipNo').' = '.$db->q($CurrentMembershipNo));
		$mykeywords = $db->setQuery($query)->loadAssocList();
		$num_keywords= count($mykeywords);
		if (empty($num_keywords)) {	
			$screenmessage="Can't find keywords." ;
		}else{
			$keywordrow = reset($mykeywords);
			$oldkeywords=$keywordrow["Keywords"];
			$newkeywords=$oldkeywords;
			
			$newkeywords = str_replace($Ship,"",$newkeywords);
			$newkeywords = str_replace($Year,"",$newkeywords);
			$newkeywords = str_replace($Class,"",$newkeywords);
			
			//make blank ship details in member table and reset [situation] to 2 'I am just interested in barges'
			$update = new \stdClass();
			$update->Situation = '2';
			$update->Shipname = '';
			$update->ShipClass = '';
			$update->ShipYear = '';
			$update->ShipLength = '';
			$update->ShipBeam = '';
			$update->Keywords = $newkeywords;
			$update->MembershipNo = $CurrentMembershipNo;
			$db->updateObject('tblMembers', $update, 'MembershipNo') or die ("Couldn't update membership ship data");
		}
		
		
		//update change log for current keeper
		$changelogtext="Barge '$vesselname' detached from this profile.";
		$subject="Register";
		$insert = new \stdClass();
		$insert->MemberID = $asset_login_memberid;
		$insert->Subject = $subject;
		$insert->ChangeDesc = $changelogtext;
		$insert->ChangeDate = $datenow;
		$db->insertObject('tblChangeLog', $insert) or die ("Couldn't update change log");	
		
		//update change log for new keeper
		//lookup new keeper member id
		$query = $db->getQuery(true)
			->select($db->qn('ID'))
			->from($db->qn('tblMembers'))
			->where($db->qn('MembershipNo').' = '.$db->q($Detachee));
		$new_memberid = $db->setQuery($query)->loadResult();
		$changelogtext="Barge '$vesselname' attached to this profile.";
		$subject="Register";
		$insert = new \stdClass();
		$insert->MemberID = $asset_login_memberid;
		$insert->Subject = $subject;
		$insert->ChangeDesc = $changelogtext;
		$insert->ChangeDate = $datenow;
		$db->insertObject('tblChangeLog', $insert) or die ("Couldn't update change log");	
	
	}else{
		//detach to default admin keeper by changing status to 2
		$update = new \stdClass();
		$update->Status = '2';
		$update->LastUpdate = '';
		$update->Keeper = '';
		$update->ID = $vesselid;		
		$result = $db->updateObject('tblAssets', $update, 'ID');
		if(!$result){die ("Couldn't update database");}

		//Change the privacy contact settings to blank = no contact
		$update = new \stdClass();
		$update->AssetPrivacy = '';
		$update->VesselID = $vesselid;		
		$result = $db->updateObject('tblAssets', $update, 'VesselID');
		if(!$result){die ("Couldn't update database");}
	
		
		//remove previous keeper details in membership table for quick reference and filter searching
		$Ship="";
		$Class="";
		$Year="";
		$Length="";
		$Beam="";

		//lookup vessel basic info to remove from previous keeper keywords
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('tblAssets'))
			->where($db->qn('VesselID').' = '.$db->q($vesselid));
		$mybarge = $db->setQuery($query)->loadAssocList();
		$num_barge= count($mybarge);
		if (empty($num_barge)) {	
			$screenmessage="Can't find barges." ;
		}else{

			foreach($mybarge as $bargerow) {
				if($bargerow["AssetCategory"]==1){
					$Ship=$bargerow["AssetTitle"];
				}
				if($bargerow["AssetCategory"]==7){
					$Year=$bargerow["AssetTitle"];
				}
				if($bargerow["AssetCategory"]==3){
					$Class=$bargerow["AssetTitle"];
				}
			 }
		}

		//lookup member keyword details to remove ship name, class and year
		$oldkeywords="";
		$newkeywords="";
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('tblMembers'))
			->where($db->qn('MembershipNo').' = '.$db->q($CurrentMembershipNo));
		$mykeywords = $db->setQuery($query)->loadAssocList();
		$num_keywords= count($mykeywords);
		if (empty($num_keywords)) {	
			$screenmessage="Can't find keywords." ;
		}else{
			$keywordrow = reset($mykeywords);
			$oldkeywords=$keywordrow["Keywords"];
			$newkeywords=$oldkeywords;
			
			$newkeywords = str_replace($Ship,"",$newkeywords);
			$newkeywords = str_replace($Year,"",$newkeywords);
			$newkeywords = str_replace($Class,"",$newkeywords);
			
			//make blank ship details in member table and reset [situation] to 2 'I am just interested in barges'
			$update = new \stdClass();
			$update->Situation = '2';
			$update->Shipname = '';
			$update->ShipClass = '';
			// $update->ShipYear = '';
			$update->ShipYear = NULL; // Set to NULL or a default integer value like 0
			//$update->ShipLength = '';
			if(!empty($Length)) $update->ShipLength = $Length;
			//$update->ShipBeam = '';
			if(!empty($Beam)) $update->ShipBeam = $Beam;
			$update->Keywords = $newkeywords;
			$update->MembershipNo = $CurrentMembershipNo;
			$db->updateObject('tblMembers', $update, 'MembershipNo') or die ("Couldn't update membership ship data");
		}
		
		$statusmessage.="Barge '$vesselname' detached from this profile to the register administration";
		//update change log
		$changelogtext="Barge '$vesselname' detached from this profile to the register administration";
		$subject="Register";
		$insert = new \stdClass();
		$insert->MemberID = $asset_login_memberid;
		$insert->Subject = $subject;
		$insert->ChangeDesc = $changelogtext;
		$insert->ChangeDate = $datenow;
		$db->insertObject('tblChangeLog', $insert) or die ("Couldn't update change log");	
	}
	
	$assetaction="list";
}

//---------------------------------------Delete vessel------------------------------------------

if ($assetaction=="deletevessel") {
	getpost_ifset(array('assetid','AssetCategoryDesc'));
	
 	//look up existing keeper membershipnumber and id and any previous ones
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblAssetsMembers'))
		->where($db->qn('ID').' = '.$db->q($vesselid));
	$vrow = $db->setQuery($query)->loadAssoc();
	$CurrentMembershipNo = stripslashes($vrow["MembershipNo"]);
	$keepers = $vrow["Keeper"];
	if($keepers){
		//add the current keeper MembershipNo to any others to provide archive history
		$keepers.=",".$CurrentMembershipNo;
	}else{
		$keepers=$CurrentMembershipNo;
	}
	//now lookup member id
	$query = $db->getQuery(true)
		->select($db->qn('ID'))
		->from($db->qn('tblMembers'))
		->where($db->qn('MembershipNo').' = '.$db->q($CurrentMembershipNo));
	$asset_login_memberid = $db->setQuery($query)->loadResult();
	
	
	//lookup assets to see if any images
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblAssets'))
		->where($db->qn('VesselID').' = '.$db->q($vesselid))
		->order($db->qn('AssetCategory'));
	try {
		$result = $db->setQuery($query)->loadAssocList();
	} catch(Exception $e) {
		echo("Can't find assets");
		exit();
	}
	
	$num_assets = count($result);
	$vesselname="";
	foreach($result as $row) {
		
		# Display asset Results, l
		$AssetID = stripslashes($row["AssetID"]);
		$AssetCategory = stripslashes($row["AssetCategory"]);
		$AssetCategoryDesc = stripslashes($row["AssetCategoryDesc"]);				
		$AssetTitle = stripslashes($row["AssetTitle"]);
		
		if($AssetCategory==1){
			$vesselname=$AssetTitle;
		}

		$imagepath="Image/register/".$AssetID.".jpg";
		//add any images underneath 
 
		if (file_exists($imagepath)) {
			//delete image
			unlink ($imagepath);
			$images+=1;
		}
	}

	//now remove assets	
	$query = $db->getQuery(true)
		->delete($db->qn('tblAssets'))
		->where($db->qn('VesselID').' = '.$db->q($vesselid));
	$update = $db->setQuery($query)->execute();
	if(!$update){
		echo("Couldn't remove entry from database");
	}

	//now remove vessel	
	$query = $db->getQuery(true)
		->delete($db->qn('tblAssetsMembers'))
		->where($db->qn('ID').' = '.$db->q($vesselid));
	$update = $db->setQuery($query)->execute();
	if(!$update){
		echo("Couldn't remove entry from database");
	}
	
	
	//remove keeper barge details in membership table for quick reference and filter searching
	$Ship="";
	$Class="";
	$Year="";
	$Length="";
	$Beam="";

	//lookup vessel basic info to remove from previous keeper keywords
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblAssets'))
		->where($db->qn('VesselID').' = '.$db->q($vesselid));
	$mybarge = $db->setQuery($query)->loadAssocList();
	$num_barge= count($mybarge);
	if (empty($num_barge)) {	
		$screenmessage="Can't find barges." ;
	}else{

		foreach($mybarge as $bargerow) {
			if($bargerow["AssetCategory"]==1){
				$Ship=$bargerow["AssetTitle"];
			}
			if($bargerow["AssetCategory"]==7){
				$Year=$bargerow["AssetTitle"];
			}
			if($bargerow["AssetCategory"]==3){
				$Class=$bargerow["AssetTitle"];
			}
		 }
	}

	//lookup member keyword details to remove ship name, class and year
	$oldkeywords="";
	$newkeywords="";
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblMembers'))
		->where($db->qn('MembershipNo').' = '.$db->q($CurrentMembershipNo));
	$mykeywords = $db->setQuery($query)->loadAssocList();
	$num_keywords= count($mykeywords);
	if (empty($num_keywords)) {	
		$screenmessage="Can't find keywords." ;
	}else{
		$keywordrow = reset($mykeywords);
		$oldkeywords=$keywordrow["Keywords"];
		$newkeywords=$oldkeywords;

		$newkeywords = str_replace($Ship,"",$newkeywords);
		$newkeywords = str_replace($Year,"",$newkeywords);
		$newkeywords = str_replace($Class,"",$newkeywords);

		//make blank ship details in member table and reset [situation] to 2 'I am just interested in barges'
		$update = new \stdClass();
		$update->Situation = '2';
		$update->Shipname = '';
		$update->ShipClass = '';
		$update->ShipYear = '';
		$update->ShipLength = '';
		$update->ShipBeam = '';
		$update->Keywords = $newkeywords;
		$update->MembershipNo = $CurrentMembershipNo;
		$db->updateObject('tblMembers', $update, 'MembershipNo') or die ("Couldn't update membership ship data");
	}

	//update change log
	$datenow = date("Y-m-d H:i:s");
	$changelogtext="Barge '$vesselname' deleted.";
	$subject="Register";
	$insert = new \stdClass();
	$insert->MemberID = $asset_login_memberid;
	$insert->Subject = $subject;
	$insert->ChangeDesc = $changelogtext;
	$insert->ChangeDate = $datenow;
	$db->insertObject('tblChangeLog', $insert) or die ("Couldn't update change log");	
	
	$statusmessage.="Barge '$vesselname' deleted";
	if($images>0){
		$statusmessage.="<br>$images image(s) deleted\n";
	}
	$assetaction="list";
}



//---------------------------------------Delete set of details------------------------------------------

if ($assetaction=="delete") {
	
	$query = $db->getQuery(true)
		->delete($db->qn('tblAssets'))
		->where($db->qn('AssetID').' = '.$db->q($assetid));
	$update = $db->setQuery($query)->execute();
	if(!$update){
		echo("Couldn't remove details from database");
	}
	$statusmessage.="'".str_replace("_", " ", $AssetCategoryDesc)."' has been deleted";
	$imagepath="Image/register/".$assetid.".jpg";
	//check and delete any image file 

	if (file_exists($imagepath)) {
		unlink ($imagepath);
		$statusmessage.="<br>$imagepath has been deleted\n";
	}

	//update details in membership table for quick reference and filter searching
	$Ship="";
	$Class="";
	$Year="";
	$Length="";
	$Beam="";

	//lookup vessel basic info
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblAssets'))
		->where($db->qn('VesselID').' = '.$db->q($vesselid));
	$mybarge = $db->setQuery($query)->loadAssocList();
	$num_barge= count($mybarge);
	if (empty($num_barge)) {	
		$screenmessage="Can't find barges." ;
	}else{

		foreach($mybarge as $bargerow) {
			if($bargerow["AssetCategory"]==1){
				$Ship=$bargerow["AssetTitle"];
			}
			if($bargerow["AssetCategory"]==4){
				$Length=$bargerow["AssetTitle"];
			}
			if($bargerow["AssetCategory"]==5){
				$Beam=$bargerow["AssetTitle"];
			}
			if($bargerow["AssetCategory"]==7){
				$Year=$bargerow["AssetTitle"];
			}
			if($bargerow["AssetCategory"]==3){
				$Class=$bargerow["AssetTitle"];
			}
	
			//add ship details to asset table
			$update = new \stdClass();
			if(!empty($Ship)) $update->Ship = $Ship;
			if(!empty($Class)) $update->Class = $Class;
			if(!empty($Year)) $update->Year = $Year;
			if(!empty($Length)) $update->Length = $Length;
			if(!empty($Beam)) $update->Beam = $Beam;
			$update->ID = $vesselid;
			$db->updateObject('tblAssetsMembers', $update, 'ID') or die ("Couldn't update ship");

	
			//lookup member keyword details to add ship name, class and year
			$oldkeywords="";
			$newkeywords="";
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('tblMembers'))
				->where($db->qn('MembershipNo').' = '.$db->q($CurrentMembershipNo));
			$mykeywords = $db->setQuery($query)->loadAssocList();
			$num_keywords= count($mykeywords);
			if (empty($num_keywords)) {	
				$screenmessage="Can't find keywords." ;
			}else{
				$keywordrow = reset($mykeywords);
				$oldkeywords=$keywordrow["Keywords"];
				$newkeywords=$oldkeywords;
				if ($Ship && false === strpos($oldkeywords, $Ship)) {
					$newkeywords = $newkeywords . " ".$Ship;
				}
				if ($Year && false === strpos($oldkeywords, $Year)) {
					$newkeywords = $newkeywords . " ".$Year;
				}
				if ($Class && false === strpos($oldkeywords, $Class)) {
					$newkeywords = $newkeywords . " ".$Class;
				}
				//update ship details to member table
				$update = new \stdClass();
				if(!empty($Ship)) $update->Shipname = $Ship;
				if(!empty($Class)) $update->ShipClass = $Class;
				if(!empty($Year)) $update->ShipYear = $Year;
				if(!empty($Length)) $update->ShipLength = $Length;
				if(!empty($Beam)) $update->ShipBeam = $Beam;
				$update->Keywords = $newkeywords;
				$update->MembershipNo = $CurrentMembershipNo;
				$db->updateObject('tblMembers', $update, 'MembershipNo') or die ("Couldn't update membership ship data");
			}
		}

	}
	
	
	
	
	$assetaction="editdetail";
} 
//---------------------------------------Save or new set of details------------------------------------------

if ($assetaction=="save") {

//getpost_ifset(array('AssetCategory','AssetCategoryDesc','AssetTitle','AssetDescription','AssetPrivacy'));
	
	if($AssetTitle!="Unknown" && $AssetTitle!=""){
	
		$datenow = date("Y-m-d H:i:s");
		//get current owner details
		$query = $db->getQuery(true)
			->select($db->qn('MembershipNo'))
			->from($db->qn('tblAssetsMembers'))
			->where($db->qn('ID').' = '.$vesselid);
		$CurrentMembershipNo = stripslashes($db->setQuery($query)->loadResult());
		//now lookup member id
		$query = $db->getQuery(true)
			->select($db->qn('ID'))
			->from($db->qn('tblMembers'))
			->where($db->qn('MembershipNo').' = '.$db->q($CurrentMembershipNo));
		$asset_login_memberid = $db->setQuery($query)->loadResult();
		

		if($assetid=="new"){
			//add a blank details
			
			$AssetDate=$datenow;
			$AssetStatus=1;
			$insert = new \stdClass();
			$insert->VesselID = $vesselid;
			$insert->AssetCategory = $AssetCategory;
			$insert->AssetCategoryDesc = $AssetCategoryDesc;
			$insert->AssetDate = $AssetDate;
			$insert->AssetStatus = $AssetStatus;
			$result = $db->insertObject('tblAssets', $insert, 'AssetID');
			if(!$result){die ("Couldn't update database");}
			$assetid = $insert->AssetID;
			$thisassetaction="list";
			
		}
	
		//update existing record
	
		//$AssetPrivacy=$ContactPrivacyEmail.$ContactPrivacyPhone.$ContactPrivacyMail;
		$AssetPrivacy="";
		$update = new \stdClass();
		$update->AssetTitle = addslashes($AssetTitle);
		$update->AssetDescription = addslashes($AssetDescription);
		$update->AssetLastUpdate = $datenow;
		$update->AssetPrivacy = $AssetPrivacy;
		$update->AssetID = $assetid;
		$result = $db->updateObject('tblAssets', $update, 'AssetID');
		
		if(!$result){
				die ("Couldn't update database <br>$statusmessage<br>$query");
		}else{
			//update change log
			
			
			if($assetid=="new"){
				$statusmessage=$statusmessage."'".str_replace("_", " ", $AssetCategoryDesc)."' - '".$AssetTitle."' details have been added";
				$changelogtext="New item '".str_replace("_", " ", $AssetCategoryDesc)."' - '".addslashes($AssetTitle)."' added.";
			}else{
				$statusmessage=$statusmessage."'".str_replace("_", " ", $AssetCategoryDesc)."' - '".$AssetTitle."' details have been updated";
				$changelogtext="Change to item '".str_replace("_", " ", $AssetCategoryDesc)."' - '".addslashes($AssetTitle)."'";
			}
			$subject="Register";
			$insert = new \stdClass();
			$insert->MemberID = $asset_login_memberid;
			$insert->Subject = $subject;
			$insert->ChangeDesc = $changelogtext;
			$insert->ChangeDate = $datenow;
			$db->insertObject('tblChangeLog', $insert) or die ("Couldn't update change log");	
		}
	
		//image upload
		function file_upload_error_message($error_code) {
			switch ($error_code) { 
				case UPLOAD_ERR_INI_SIZE: 
					return 'The uploaded file exceeds the upload_max_filesize directive in php.ini'; 
				case UPLOAD_ERR_FORM_SIZE: 
					return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'; 
				case UPLOAD_ERR_PARTIAL: 
					return 'The uploaded file was only partially uploaded'; 
				case UPLOAD_ERR_NO_FILE: 
					return 'No file was uploaded'; 
				case UPLOAD_ERR_NO_TMP_DIR: 
					return 'Missing a temporary folder'; 
				case UPLOAD_ERR_CANT_WRITE: 
					return 'Failed to write file to disk'; 
				case UPLOAD_ERR_EXTENSION: 
					return 'File upload stopped by extension'; 
				default: 
					return 'Unknown upload error'; 
			} 
		} 


		if ($_FILES['thefile']['error'] === UPLOAD_ERR_OK){
   			// upload ok
			//variables . . . . . . . . . . . . . . 
			$the_imagepath="Image/register";
			$image_resize_width=700;
			$imagepercent="";
			$the_clippath=""; 
			$clip_width="";
			$error="";
			$allowedExts = array("jpg", "jpeg");
			//the file name will be the asset id
			$myfilename=$assetid.".jpg";

					
			# validate the file $the_file		
			$thefiletmp =$_FILES["thefile"]["tmp_name"];
			$thefilename =$_FILES["thefile"]["name"];
  			$thefiletype =$_FILES["thefile"]["type"];
			$thefilesize =$_FILES["thefile"]["size"];

			# check if we are allowed to upload this file_type	
		
			$extension = strtolower(end(explode(".", $thefilename)));
			if ((($thefiletype == "image/jpeg")
				|| ($thefiletype == "image/pjpeg"))
				&& in_array($extension, $allowedExts))   {

				//its an allowed image
				$size = GetImageSize($thefiletmp);
				list($foo,$width,$bar,$height) = explode("\"",$size[3]);		
				if (file_exists($the_imagepath . "/" . $myfilename)) {
					//delete old version if valid
					unlink ($the_imagepath . "/" . $myfilename);
					//$statusmessage.="<br>$the_imagepath / $the_file_name has been deleted\n";
				}
				if (!@copy($thefiletmp, $the_imagepath . "/" . $myfilename)) {
					$error.="A problem arose during the upload to (" . $the_imagepath . "/" . $myfilename. ")";
				}else{
					//Upload OK so calculate file size and make clip if image			
					$mediafilesize=filesize ($the_imagepath . "/" . $myfilename);		
					$statusmessage.="<br>File $myfilename ($mediafilesize Bytes) has been uploaded successfully"; 
					#-+ Check if the file exists 
					if (!file_exists($the_imagepath . "/" . $myfilename)){ 
						die ("Error: File not found..."); 
					} 
					$filename = $the_imagepath . "/" . $myfilename;
					
					// Get dimensions
					list($org_w, $org_h) = getimagesize($filename);
					$src_img = imagecreatefromjpeg($filename);	
					if($the_clippath){
						// Directory where the thumbnails will be saved 
						// Calculate thumbnail height
						$new_width = $clip_width;
						$new_height = floor($clip_width * $org_h / $org_w);					
						$clip_height=$new_height;
						// Resample
						$image_p = imagecreatetruecolor($new_width, $new_height);

						imagecopyresampled($image_p, $src_img, 0, 0, 0, 0, $new_width, $new_height, $org_w, $org_h);
						// Save it!
						imagejpeg($image_p, $the_clippath . "/" . $myfilename, 100);
						$statusmessage.="<br>Image clip has been created\n"; 
						$statusmessage.="<br>Original dimensions: $org_w x $org_h\n"; 
						$statusmessage.="<br>Clip dimensions: $clip_width x $clip_height\n";
					}
					//resize original if too big
					if($image_resize_width || $imagepercent){
						if($imagepercent){
							$new_width = floor($org_w * $imagepercent/100);
							if($new_width<$org_w){
								//no need to resize
								$new_width=$org_w;
								$new_height = $org_h;
							}else{
								$new_height = floor($org_h * $imagepercent/100);
							}
							
						}elseif($image_resize_width){
							$new_width= floor($image_resize_width);
							if($new_width<$org_w){
								//need to resize
								$new_height = floor($org_h * ($new_width / $org_w));
							}else{
								$new_width=$org_w;
								$new_height = $org_h;
							}
						}else{
							//no change
							$new_width=$org_w;
							$new_height = $org_h;
						}
						//resample to reduce file size and width if necessary
						//20090309 CJG uploads <500 were not being resampled so could end up big files
						//now all are resampled even if less than 500 to optimise the file
						//if($new_width!=$org_w){
						//needs resizing
						$new_w=$new_width;
						$new_h=$new_height;
						// Resample
						$image_p = imagecreatetruecolor($new_width, $new_height);
						//$src_img = imagecreatefromjpeg($filename);
						imagecopyresampled($image_p, $src_img, 0, 0, 0, 0, $new_width, $new_height, $org_w, $org_h);
						//delete old file ????

						//unlink ("../".$oldmedialocationurl);
						
						// Save it!
						imagejpeg($image_p, $the_imagepath . "/" . $myfilename, 100);
						
						$oldmediafilesize=$mediafilesize;
						$mediafilesize=filesize ($the_imagepath . "/" . $myfilename);		
						$statusmessage.="<br>The image has been resized"; 
						$statusmessage.=" from $org_w x $org_h\n"; 
						$statusmessage.="to $new_w x $new_h\n"; 
						imagedestroy($src_img);
					}
				}
			}else{
				$statusmessage.="<br>The image $thefiletype was not of an allowed type\n"; 
			}
		}else{
			if($_FILES['thefile']['error'] === UPLOAD_ERR_NO_FILE){
				$statusmessage.="<br>No image uploaded"; 
			}else{
				$statusmessage.="<br>There was an error uploading the image: " . file_upload_error_message($_FILES['thefile']['error']); 
			}
		}
		//end of image upload
		
	}else{
		//blank entry
		$statusmessage="A title is required\n"; 
	}
	$statusmessage.="<br>".$error;
	$assetaction="editdetail";
	
	//update details in membership table for quick reference and filter searching added CJG 20180120
	$Ship="";
	$Class="";
	$Year="";
	$Length="";
	$Beam="";

	//lookup vessel basic info
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblAssets'))
		->where($db->qn('VesselID').' = '.$db->q($vesselid));
	$mybarge = $db->setQuery($query)->loadAssocList();
	$num_barge= count($mybarge);
	if (empty($num_barge)) {	
		$screenmessage="Can't find barges." ;
	}else{

		foreach($mybarge as $bargerow) {
			if($bargerow["AssetCategory"]==1){
				$Ship=$bargerow["AssetTitle"];
			}
			if($bargerow["AssetCategory"]==4){
				$Length=$bargerow["AssetTitle"];
			}
			if($bargerow["AssetCategory"]==5){
				$Beam=$bargerow["AssetTitle"];
			}
			if($bargerow["AssetCategory"]==7){
				$Year=$bargerow["AssetTitle"];
			}
			if($bargerow["AssetCategory"]==3){
				$Class=$bargerow["AssetTitle"];
			}
	
			//add ship details to asset table
			$update = new \stdClass();
			if(!empty($Ship)) $update->Ship = $Ship;
			if(!empty($Class)) $update->Class = $Class;
			if(!empty($Year)) $update->Year = $Year;
			if(!empty($Length)) $update->Length = $Length;
			if(!empty($Beam)) $update->Beam = $Beam;
			$update->ID = $vesselid;
			$db->updateObject('tblAssetsMembers', $update, 'ID') or die ("Couldn't update ship");

	
			//lookup member keyword details to add ship name, class and year
			$oldkeywords="";
			$newkeywords="";
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('tblMembers'))
				->where($db->qn('MembershipNo').' = '.$db->q($CurrentMembershipNo));
			$mykeywords = $db->setQuery($query)->loadAssocList();
			$num_keywords= count($mykeywords);
			if (empty($num_keywords)) {	
				$screenmessage="Can't find keywords." ;
			}else{
				$keywordrow = reset($mykeywords);
				$oldkeywords=$keywordrow["Keywords"];
				$newkeywords=$oldkeywords;
				if ($Ship && false === strpos($oldkeywords, $Ship)) {
					$newkeywords = $newkeywords . " ".$Ship;
				}
				if ($Year && false === strpos($oldkeywords, $Year)) {
					$newkeywords = $newkeywords . " ".$Year;
				}
				if ($Class && false === strpos($oldkeywords, $Class)) {
					$newkeywords = $newkeywords . " ".$Class;
				}
				//add ship details to member table
				$update = new \stdClass();
				if(!empty($Ship)) $update->Shipname = $Ship;
				if(!empty($Class)) $update->ShipClass = $Class;
				if(!empty($Year)) $update->ShipYear = $Year;
				if(!empty($Length)) $update->ShipLength = $Length;
				if(!empty($Beam)) $update->ShipBeam = $Beam;
				$update->Keywords = $newkeywords;
				$update->MembershipNo = $CurrentMembershipNo;
				$db->updateObject('tblMembers', $update, 'MembershipNo') or die ("Couldn't update membership ship data");
			}
		}

	}
	
}


//---------------------------------------List vessels---------------------------------------------

if ($assetaction=="" || $assetaction=="list") {
	
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblAssetsMembers'))
		->where($db->qn('MembershipNo').' = '.$db->q($MembershipNo))
		->where($db->qn('Status')." = '1'")
		->order($db->qn('Date'));

	//$statusmessage=$sql;
	$vessels = $db->setQuery($query)->loadAssocList();
	$vrows = count($vessels);
	# If the search was unsuccessful then Display Message try again.
	$rowclass="table_links";
	If ($vrows == 0){
		$assetinfo.="<td class=list_small>There are no barges attached to this profile</td>\n";
	}else{
		
		$listresults.="<tr><td class=table_links><b>Barge Name</b></a></td><td class=table_links><b>Detach</b></a></td><td class=table_links><b>Delete</b></a></td></tr>\n";
		foreach($vessels as $row) {

			# Display asset Results, l
			$VesselID = stripslashes($row["ID"]);
			$VesselDate = stripslashes($row["Date"]);
			$MembershipNo = stripslashes($row["MembershipNo"]);
			if($VesselDate){
				$VesselDateDisplay=" - added ".date_to_format($VesselDate,"d") ;
			}else{
				$VesselDateDisplay="";
			}

			
			//lookup a name
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('tblAssets'))
				->where($db->qn('VesselID').' = '.$db->q($VesselID))
				->order($db->qn('AssetCategory'));
			try {
				$result = $db->setQuery($query)->loadAssocList();
			} catch(Exception $e) {
				echo("Can't find assets");
				exit();
			}
			
			$num_rows = count($result);
			
			# If the search was unsuccessful then Display Message try again.
			if ($num_rows) {
				foreach($result as $arow) {
		
					# Display asset Results, l
					$AssetID = stripslashes($arow["AssetID"]);
					$AssetCategory = stripslashes($arow["AssetCategory"]);
					$AssetCategoryDesc = stripslashes($arow["AssetCategoryDesc"]);				
					$AssetTitle = stripslashes($arow["AssetTitle"]);
					if($AssetCategory==1){
						$vesselname=$AssetTitle;
					}
				}
				
			}
			//$editlink="<a href=\"#\" onClick=\"document.form.assetaction.value='edit';document.form.assetid.value='$AssetID';document.form.submit()\"><img src=\"Image/common/group_edit.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Edit this entry\" title=\"Edit this entry\"></a>";
			if(!$vesselname){
				$vesselname="Unknown ".$MembershipNo.", ".$VesselDate;
			}

			
			$listresults.="<tr><td class=$rowclass><a href=\"#\" onClick=\"document.form.assetaction.value='editdetail';document.form.vesselid.value='".$VesselID."';document.form.submit()\">".$vesselname."</a>$VesselDateDisplay</td>\n";
			if($admin=="open"){
				//editor is admin editor so store changes against owner, not admin
				$listresults.="<td class=$rowclass><a href=\"#\" onClick=\"document.form.assetaction.value='detachvessel';document.form.vesselid.value='".$VesselID."';DetachVessel(this)\"><img src=\"Image/common/cut.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Transfer barge '".$vesselname."' from this profile to the membership no entered in the box above\" title=\"Transfer barge '".$vesselname."' from this profile to the membership no entered in the box above\"></a></td>\n";
			}else{
				//editor is owner
				$listresults.="<td class=$rowclass><a href=\"#\" onClick=\"document.form.assetaction.value='detachvessel';document.form.vesselid.value='".$VesselID."';DetachVessel(this)\"><img src=\"Image/common/cut.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Detach barge '".$vesselname."' from this profile\" title=\"Detach barge '".$vesselname."' from this profile\"></a> </td>\n";
			}
			$listresults.="<td class=$rowclass><a href=\"#\" onClick=\"document.form.assetaction.value='deletevessel';document.form.vesselid.value='".$VesselID."';DeleteVessel(this);\"><img src=\"Image/common/clear.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Delete barge '".$vesselname."' from this profile\" title=\"Delete barge '".$vesselname."' from this profile\"></a> </td>\n";
			$listresults.="</tr>\n";
			//$listresults.="<tr><td colspan=3 class=$rowclass><hr></td></tr>\n";			
			
			$vesselname="";
			//debug row $listresults.="<tr><td colspan=6 class=$rowclass>".$a[$listrow][15]."</td></tr>\n";	
		}
	}		
	$listresults.="<tr><td colspan=3 class=$rowclass><a href=\"#\" onClick=\"document.form.assetaction.value='addvessel';document.form.vesselid.value='new';document.form.submit()\">Add a new barge</a>
	 Name: <input name=\"vesselname\" type=\"text\" class=\"formcontrol\" id=\"vesselname\" value=\"\" size=\"35\" maxlength=\"50\"></td></tr>\n";


	//TO DO add length, beam, class as well mandatory

	//echo("<tr><td colspan=6 class=list_small>How to search the register . . . . . . . </td></tr>\n");
	//echo("<div>Search text <input class=\"formcontrol\" name=\"assetsearchtext\" type=\"text\" value=\"$assetsearchtext\" size=\"30\">\n");
	//echo("&nbsp;<a href=\"#\" onClick=\"document.form.assetaction.value='list';document.form.submit()\"><img src=\"Image/common/search_go.gif\" alt=\"Start search\" width=\"30\" height=\"19\" border=\"0\"></a>\n");

?>

	<script language="JavaScript">
  
    function DetachVessel() {	
		var detachee=document.form.Detachee.value;
		if(detachee){
			var confirmtext="Detachment of this barge will pass responsibility to member "+detachee+" and cannot be reversed. \n\nConfirm detachment and transfer by clicking OK";
		}else{
			var confirmtext="Detachment of this barge will pass responsibility to the register administration and cannot be reversed. \n\nConfirm detachment and transfer by clicking OK";		
		}
		if (confirm(confirmtext)) {
			document.form.submit();	
		}else{
			document.form.assetaction.value='list';
		}
	}

	function DeleteVessel() {
		if (confirm("Deleting this barge from the register cannot be undone. All entries and images will be removed. \n\nConfirm deletion by clicking OK")) {
			document.form.submit();	
		}else{
			document.form.assetaction.value='list';
		}
	}	
   
    </script>
<?php



	echo("<tr><td colspan=3 class=list_small_member>$editlisthelp</td></tr>\n");	
	if($statusmessage){
		echo("<tr><td colspan=3 class=list_small><font color=ff0000><b>".$statusmessage."</b></font></td></tr>\n");
	}
	if($vrows==0){
		echo("<tr><td colspan=3 class=list_small><b>There are currently no barges attached to this profile</b><br>Click on 'Add a new barge' to enter details</td></tr>\n");
	}elseif($vrows==1){
		echo("<tr><td colspan=3 class=list_small><b>There is currently $vrows barge attached to this profile</b><br>Click on a barge name to view or edit details<input type='hidden' name='Detachee'value=''></td></tr>\n");
	}else{
		echo("<tr><td colspan=3 class=list_small><b>There are currently $vrows barges attached to this profile</b><br>Click on a barge name to view or edit details<input type='hidden' name='Detachee'value=''></td></tr>\n");
	}
	
	echo($listresults);
}

//---------------------------------------Entry details---------------------------------------------

if ($assetaction=="editdetail") {
	//echo("<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"1\">");
	echo("<input name=\"assetsearchtext\" type=\"hidden\" value=\"$assetsearchtext\">\n");

	$num_rows = 0;
	
	$query = $db->getQuery(true)
		->select('a.*')
		->select($db->qn('ac.CatSort'))
		->from($db->qn('tblAssets', 'a'))
		->innerJoin($db->qn('tblAssetsCategories', 'ac').' ON '.$db->qn('a.AssetCategory').' = '.$db->qn('ac.CatID'))
		->where($db->qn('a.VesselID').' = '.$db->q($vesselid))
		->order($db->qn(['ac.CatSort', 'a.AssetTitle']));
	try {
		$result = $db->setQuery($query)->loadAssocList();
	} catch(Exception $e) {
		echo("Can't find assets");
		exit();
	}
	$num_assets = count($result);
	
	$datenow = time();
	$currentassets="|";
	$vesselname="";
	foreach($result as $row)	{

		# Display asset Results, l
		$AssetID = stripslashes($row["AssetID"]);
		$VesselID = stripslashes($row["VesselID"]);
		$AssetCategory = stripslashes($row["AssetCategory"]);
		$AssetCategoryDesc = stripslashes($row["AssetCategoryDesc"]);				
		$AssetTitle = stripslashes($row["AssetTitle"]);
		$AssetDescription = stripslashes($row["AssetDescription"]);
		$AssetDate = stripslashes($row["AssetDate"]);
		$AssetLastUpdate = stripslashes($row["AssetLastUpdate"]);
		$AssetPrivacy = stripslashes($row["AssetPrivacy"]);
		$editlink="<a href=\"#\" onClick=\"document.form.assetaction.value='edit';document.form.assetid.value='$AssetID';document.form.submit()\"><img src=\"Image/common/group_edit.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Edit this detail\" title=\"Edit this detail\"></a>";
		
		$currentassets.="$AssetCategory|";

		$AssetPrivacyBlock="";
		//add privacy image scrapped, now use PM in Forum link 20130410 CJG


		if($AssetCategory==1){
			$vesselname=$AssetTitle;
		}


		//$data[$AssetCategory] = stripslashes($row["AssetTitle"]);
		$rowclass="list_small";
		$detail.="<tr><td class='table_stripe_even' width='18%'><b>".str_replace("_", " ", $AssetCategoryDesc)."</b>\n";
		$detail.="<td class='table_stripe_even' width='72%'>".nl2br($AssetTitle)."</td>\n";
		$detail.="<td class='table_stripe_even' width='5%'>".$AssetPrivacyBlock."</td>";		
		$detail.="<td class='table_stripe_even' width='5%'>".$editlink."</td></tr>";		

		if($AssetDescription){
			$detail.="<tr><td colspan=4 class=$rowclass  valign=top>".nl2br($AssetDescription)."</td></tr>\n";
		}
		//echo("- $rowcount, $AssetCategory $VesselID ".$row["AssetTitle"]." <br>");

		$imagepath="Image/register/".$AssetID.".jpg";
		//add any images underneath 
 
		if (file_exists($imagepath)) {
			$imageInfo = getimagesize($imagepath);
			$imwidth = $imageInfo[0];
			$imheight = $imageInfo[1]; 
			$mediatitle=$AssetTitle;
			$detail.="<tr><td colspan=4><img src=\"".$imagepath."\" width=".$imwidth." height=".$imheight." border=0 title=\"".$mediatitle."\" alt=\"".$mediatitle."\"></td></tr>\n";  
		}

		$detail.="<tr><td colspan=4 ></td></tr>\n";

	}
	if($currentassets=="|"){
		$currentassets.="|";
	}
	if(!$vesselname){
		$vesselname="Unknown";
	}
	echo("<tr><td colspan=4 class=list_small_member>$edithelp</td></tr>\n");
	if($statusmessage){
		echo("<tr><td colspan=4 class=list_small><font color=ff0000><b>".$statusmessage."</b></font></td></tr>\n");
	}
	echo("<tr><td colspan=4 class='list_small'><a href=\"#\" onClick=\"document.form.assetaction.value='list';document.form.submit()\">Back to the list <img src=\"Image/common/back1.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Back to the list\" title=\"Back to the list\"></a></td></tr>\n");
	echo("<tr><td colspan=4 class='list_small_underline'><b>Barge:</b> ".$vesselname."</td></tr>\n");


	//allow adding of new asset
	//get all the possible category options and build dropdown excluding already taken
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblAssetsCategories'))
		->order($db->qn('CatSort'));
	$result = $db->setQuery($query)->loadAssocList();
	if (empty($result)) {
		echo("<P>Error finding data</P>");
		exit();
	}
	$num_rows = count($result);
	if (empty($num_rows)) {
		$message="No information available at this time";
		exit();
	}
	$catlist="<select class=\"formcontrol\" name=\"AssetOptions\" id=\"AssetOptions\">\n";
	$debug=$currentassets;
	$pos=true;
	foreach($result as $row){
		$CatID=$row["CatID"];
		$CatDesc=$row["CatDesc"];
		$CatSort=$row["CatSort"];
		$CatHelp=$row["CatHelp"];
		$CatPreferedOptions=$row["CatPreferedOptions"];
		$pos = strpos ($currentassets, "|$CatID|");
		$debug.="+$CatID";
		if($pos>-1 && $CatID!=13){ //13='feature' which can be multiple
			//already gotit
		}else{
			$catlist.="<option value=\"".$CatID."\"".$selectit.">".str_replace("_", " ", $CatDesc)."</option>\n";
		
		}
	}
	$catlist.="</select>";

	
	if($admin=="open"){
		//allow admin edit
		//lookup current owner 
		if($vesselid){
			$fullname="";
			$showname="";
			$query = $db->getQuery(true)
				->select('m.*')
				->select($db->qn(['am.Status', 'am.Keeper']))
				->from($db->qn('tblMembers', 'm'))
				->innerJoin($db->qn('tblAssetsMembers', 'am').' ON '.$db->qn('m.MembershipNo').' = '.$db->qn('am.MembershipNo'))
				->where($db->qn('am.ID').' = '.$db->q($vesselid));
			$memberrow = $db->setQuery($query)->loadAssoc();
			if (!empty($memberrow)) {	
				$MembershipNo=$memberrow["MembershipNo"];
				$memid=$memberrow["ID"];
				$Status=$memberrow["Status"];
				$DateJoined=$memberrow["DateJoined"];
				$DatePaid=$memberrow["DatePaid"];
				$Country=$memberrow["Country"];
				$Telephone1=$memberrow["Telephone1"];
				$Telephone2=$memberrow["Telephone2"];
				$Email=$memberrow["Email"];
				$Keepers=$memberrow["Keeper"];
				$LastUpdate=$memberrow["LastUpdate"];
				
				if($MembershipNo){
					$asset_login_memberid=$memberrow["ID"];
					$Services=$memberrow["Services"];
					$MemStatus=$memberrow["MemStatus"];

					//OK to show name if $Services includes |1|
					$pos = strpos ($Services, "|1|");
					//$debug="Services: $Services pos:$pos ms:$MemStatus $MembershipNo";
					if($pos>-1){
						$anon=0;
						$showname="Keeper allowing name in register";
					}else{
						//keeper wants to remain annonymouse
						$showname="Keeper wishes to remain anonymous";
						$anon=1;
					}	
					
					//Get details and place first member surname first for sort order
					$fullname="";
					if($memberrow["FirstName"]){
						$fullname=$memberrow["FirstName"];
					}
					if($memberrow["LastName2"] && ($memberrow["LastName2"] != $memberrow["LastName"])){
						//different surnames
						$fullname.=" ".$memberrow["LastName"]." & ".$memberrow["FirstName2"]." ".$memberrow["LastName2"];
					}elseif($memberrow["LastName2"] && ($memberrow["LastName2"] == $memberrow["LastName"])){
						//same surname
						$fullname.=" & ".$memberrow["FirstName2"]." ".$memberrow["LastName"];
					}else{
						//no second member
						$fullname.=" ".$memberrow["LastName"];
					}
					
					if(!$fullname){
						$fullname="Unknown";
					}
				}
			}
		}else{
			$fullname="Unknown - can't find barge in database";
		}
		switch ($Status) {
			case "1":
			$Barge_Status_text="Member is keeper";
			break;
			case "2":
			$Barge_Status_text="Detached to Admin";
			break;
		}	

		switch ($MemStatus) {
			case "1":
			$Mem_Status_text="Applied awaiting payment";
			break;
			case "2":
			$Mem_Status_text="Paid up";
			break;
			case "3":
			$Mem_Status_text="Renewal overdue";
			break;
			case "4":
			$Mem_Status_text="Gone away";
			break;
			case "5":
			$Mem_Status_text="Terminated";
			break;
			case "6":
			$Mem_Status_text="Complimentary";
			break;
			case "7":
			$Mem_Status_text="Set to terminate";
			break;
		}
		//email incomplete entry
		$mailsubject="DBA Barge register update";
		$mailmessage="Dear Member
		%0A%0A
		The barge register is currently undergoing some changes and we are reviewing register entries including your barge $vesselname. The more complete the register is the more interesting and useful it will be for both Members and Club officials.";
		$missing="";
		if(!$length){
			if($missing){
				$missing.=", ";
			}
			$missing.="Length";
		}
		if(!$beam){
			if($missing){
				$missing.=", ";
			}
			$missing.="Beam";
		}
		if(!$type){
			if($missing){
				$missing.=", ";
			}
			$missing.="Type";
		}
		if(!$propulsion){
			if($missing){
				$missing.=", ";
			}
			$missing.="Propulsion - sail or power";
		}
		if(!$yearbuilt){
			if($missing){
				$missing.=", ";
			}
			$missing.="Year built";
		}
		
		if($missing){
			$mailmessage.="%0A%0AThe entry for your barge is currently missing some important detail and it would be really helpful if you could either visit the register online and insert the missing detail [ ".$missing." ] or email the missing information to bargeregister@barges.org."; 
		}
		if($anon==1){
			$mailmessage.="%0A%0ACould I also ask that you visit the website Members section and check the details in 'My Details'.  Below the 'Situation' field, where you have entered [I own a barge] there is a tick-box which must be ticked to allow your name to appear as the keeper of your barge in the register. Unless you have compelling reasons to remain anonymous, please tick the box.";
		}
		$mailmessage.="%0A%0AThank you and Best wishes";
		$mailmessage.="%0A%0A".$user->name;
		$mailmessage.="%0A%0ABarge Register administration";		
		
		$emaillink1="<a href=\"mailto:".$Email."?Subject=".$mailsubject."&Body=".$mailmessage."\">".$Email."</a>";
		
		//Keeper(s): ".$fullname." <a href='javascript:MoreInfo(" . $memid . ")'><img height=16 src='/components/com_waterways_guide/images/info.gif' width=16 border=0 alt='Click to view details' title='Click to view details'></a> <a href='javascript:Changelog(" . $memid . ")'><img height=16 src='/components/com_waterways_guide/images/txt.gif' width=16 border=0 alt='Click to view change log' title='Click to view change log'></a><br>

		//email anon owner
		$mailsubject="DBA Barge register update";
		$mailmessage="Dear Member
		%0A%0A
		As part of our on-going review of the Barge Register, I am contacting you because your register entry for $vesselname does not show your name as owner. The register entry shows the owner as 'Unknown', and  the 'Whose Barge' list shows the owner as 'Wishes to Remain Anonymous'. The reason for this is that you have not ticked the box in your 'My Details' page which allows your name to appear.";
		$mailmessage.="%0A%0AIf you have deliberately unticked the box because you wish to withhold your name from fellow members, that is your right and you need do nothing. If, however, you would like your name to appear as the owner of $vesselname you should log on to the website members section and select 'My Details'. Scroll down to the 'Situation' field, where you should have entered 'I own a barge' or 'I have an ownership share in a barge'. The tick-box which allows your name to be shown is immediately below this field. Tick the box and then click on the [update] button at the foot of the page. Alternatively, reply to this email giving me your authority and I can tick the box for you.  Your name will then appear on the Register and 'Whose Barge ?' listing.";
		$mailmessage.="%0A%0APlease note, only your name will appear - your contact details and other personal data will not be shared. I hope that you will choose to let your name be shown.";
		$mailmessage.="%0A%0AThank you and Best wishes";
		$mailmessage.="%0A%0A".$user->name;
		$mailmessage.="%0A%0ABarge Register administration";

		$emaillink2="<a href=\"mailto:".$Email."?Subject=".$mailsubject."&Body=".$mailmessage."\">".$Email."</a>";
		//Keeper(s): ".$fullname." <a href='javascript:MoreInfo(" . $memid . ")'><img height=16 src='/components/com_waterways_guide/images/info.gif' width=16 border=0 alt='Click to view details' title='Click to view details'></a> <a href='javascript:Changelog(" . $memid . ")'><img height=16 src='/components/com_waterways_guide/images/txt.gif' width=16 border=0 alt='Click to view change log' title='Click to view change log'></a><br>
		
		
		//allow name link to PM discussion-forum/private-messaging?task=new&recip=7591
    	$pm_modal_params = array(); 
	 	$pm_modal_params['title'] = 'Private message';
		$pm_modal_params['backdrop'] = "true";
		$pm_modal_params['tabindex'] = "99";
		//$pm_modal_params['footer'] = '<p>Footer</p>'; 
		
		$pm_modal_params['height'] = "400px";
    	$pm_modal_params['width'] = "800px";	
    	$pm_modal_params['url'] = 'http://www.barges.org/index.php?option=com_uddeim&task=new&recip='.$asset_login_memberid;
    	
		$pm_body = ""; 	
		//$mypm_url="<a href=\"/index.php?option=com_uddeim&task=new&recip=".$asset_login_memberid."\"><img src=\"Image/common/email.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Send a private message to Keeper\" title=\"Send a private message to Keeper\"> ".$fullname."</a>";
		
		// echo("<tr><td colspan=4 class='list_small'><a href=\"#pmModal\" class=\"btn\" data-toggle=\"modal\"> Contact the keeper by Private Message</a> </td></tr>\n");
		// echo HTMLHelper::_('bootstrap.renderModal', 'pmModal', $pm_modal_params, $pm_body); 
		echo '<tr><td colspan="4" class="list_small">To contact the Keeper, use <a href="/members/member-finder">Member Finder</a></td></tr>';
		
			
		
		echo("<tr><td colspan=4 class=list_small><b>ADMIN</b></td></tr>\n");
		echo("<tr><td colspan=4 class=list_small>
		Barge: ".$vesselname."<br>
		Barge status: ".$Barge_Status_text."<br>
		Keeper(s): ".$fullname." <a href='javascript:MoreInfo(" . $memid . ")'><img height=16 src='/components/com_waterways_guide/images/info.gif' width=16 border=0 alt='Click to view details' title='Click to view details'></a> <a href='javascript:Changelog(" . $memid . ")'><img height=16 src='/components/com_waterways_guide/images/txt.gif' width=16 border=0 alt='Click to view change log' title='Click to view change log'></a><br>
		Ex Keeper(s): ".$Keepers."<br>
		View name in profile: ".$showname."<br>
		Membership No: ".$MembershipNo."<br>
		Country: ".$Country."<br>
		Membership Status: ".$Mem_Status_text."<br>
		DateJoined: ".$DateJoined."<br>
		Date Last Paid: ".$DatePaid."<br>
		Telephone: ".$Telephone1."<br>
		Mobile: ".$Telephone2."<br>
		Email: ".$emaillink1." - incomplete entry<br>
		Email: ".$emaillink2." - anonymous owner<br>
		Last update: ".$LastUpdate."</td></tr>\n");
		$admintransferlink.="<a href=\"#\" onClick=\"document.form.assetaction.value='detachvessel';document.form.vesselid.value='".$vesselid."';DetachVessel(this)\">Detach <img src=\"Image/common/cut.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Transfer barge '".$vesselname."' from this profile to the membership no entered in the box\" title=\"Transfer barge '".$vesselname."' from this profile to the membership no entered in the box\"></a>\n";
		echo("<tr><td colspan=4 class=list_small>To transfer, enter the new owner Membership no here (or blank for Admin) and click 'Detach' <input class=formcontrol type='text' name='Detachee' size='10' value=\"\"> ".$admintransferlink."</td></tr>\n");
		echo("<tr><td colspan=4 class=list_small>Delete this barge from the register <a href=\"#\" onClick=\"document.form.assetaction.value='deletevessel';document.form.vesselid.value='".$VesselID."';DeleteVessel(this);\"><img src=\"Image/common/clear.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Delete barge '".$vesselname."' from this profile\" title=\"Delete barge '".$vesselname."' from this profile\"></a></td></tr>\n");


		//echo("<tr><td colspan=4 class=list_small><b>View the full register change log <a href=\"#\" onClick=\"document.form.assetaction.value='adminchangelog';document.form.submit();\">here</a></td></tr>\n");


		echo("<tr><td colspan=4 class=list_small></td></tr>\n");
		//$admineditlink="&VesselID=".$VesselID."\"> click here to edit <img src=\"Image/common/icon_accdetail.gif\" width=16 height=17 border=0 alt=\"Add or edit barge register\"></a><br>\n";
		//
		//$admineditlink="<a href="#" onClick="document.form.assetaction.value='detail';document.form.vesselid.value='568';document.form.submit()">Esme</a>
		//$admineditlink="<a href=\"".$registerurl."&MyAccountaction=assets_update&asset_login_membershipno=".$MembershipNo."&assetaction=list&editpage=1&asset_login_memberid=".$asset_login_memberid."\"> edit member entry here <img src=\"Image/common/icon_accdetail.gif\" width=16 height=17 border=0 alt=\"Add or edit barge register\"></a><br>\n";
		//echo("<tr><td colspan=3 class='list_small'>Admin:&nbsp;".$admineditlink."</td></tr>\n");
	}
	$newlink="<input type=\"button\" class=\"btn btn-primary btn-sm\" name=\"go\" value=\"Go\" onClick=\"document.form.assetaction.value='edit';document.form.assetid.value='new';document.form.submit()\">";
	
	//$newlink="<a href=\"#\" onClick=\"document.form.assetaction.value='edit';document.form.assetid.value='new';document.form.submit()\"><img src=\"Image/common/go.gif\" width=\"27\" height=\"18\" border=\"0\" alt=\"Add a new detail\" title=\"Add a new detail\"></a>";
	echo("<tr><td colspan=4 class='list_small_underline'><b>Add a new detail</b> ".$catlist." ".$newlink."</td></tr>\n");

	if($num_assets){
		echo("<tr><td class=list_small><b>Detail</b></td><td class=list_small><b>Info</b></td><td class=list_small></td><td class=list_small><b>Edit</b></td></tr>\n");
		echo("<tr><td colspan=4 ></td></tr>\n");
		echo($detail);
	}
	
	if($admin=="open"){
	?>
	
	<SCRIPT LANGUAGE="JavaScript">
	<!--
	function MoreInfo(memberid){
		var mypage = "<?php 
		echo("../../../../../index.php?option=com_waterways_guide&tmpl=component&view=profile&userid=");
		?>"+memberid;
		//alert("mypage "+mypage);
		var myname = "Member_Profile";
		var w = 800;
		var h = 600;
		var scroll = "yes";
		var winl = (screen.width - w) / 2;
		var wint = (screen.height - h) / 2;
		winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scroll+',resizable'
		mypage += (mypage.indexOf('?') != -1 ? '&' : '?') + 'nocache=' + Date.now();
		win = window.open(mypage, myname, winprops)
		if (parseInt(navigator.appVersion) >= 4) { win.window.focus(); }
	}
	
	function Changelog(memberid){
		var mypage = "/components/com_waterways_guide/views/search/tmpl/view_change_log.php?memberid="+memberid;
		//alert("mypage "+mypage);
		var myname = "Member_Changelog";
		var w = 800;
		var h = 600;
		var scroll = "yes";
		var winl = (screen.width - w) / 2;
		var wint = (screen.height - h) / 2;
		winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scroll+',resizable'
		mypage += (mypage.indexOf('?') != -1 ? '&' : '?') + 'nocache=' + Date.now();
		win = window.open(mypage, myname, winprops)
		if (parseInt(navigator.appVersion) >= 4) { win.window.focus(); }
	}
	

	function DetachVessel() {	
		var detachee=document.form.Detachee.value;
		if(detachee){
			var confirmtext="Detachment of this barge will pass responsibility to member "+detachee+". \n\nConfirm detachment and transfer by clicking OK";
		}else{
			var confirmtext="Detachment of this barge will pass responsibility to the register administration. \n\nConfirm detachment and transfer by clicking OK";		
		}
		if (confirm(confirmtext)) {
			document.form.submit();	
		}else{
			document.form.assetaction.value='list';
		}
	}

	function DeleteVessel() {
		if (confirm("Deleting this barge from the register cannot be undone. All entries and images will be removed. \n\nConfirm deletion by clicking OK")) {
			document.form.submit();	
		}else{
			document.form.assetaction.value='list';
		}
	}	
   

	//-->
	</script>


	<?php
	}
	
	//$directlink="http://www.barges.org/main.php?section=".$section."&assetaction=detail&vesselid=".$vesselid;
	//echo("<tr><td colspan=4 class='list_small'>Use this link to send to a friend to go directly to this barge<br><a href=\"".$directlink."\">".$directlink."</a></td></tr>\n");
	//echo("</table>");
}

//---------------------------------------asset edit---------------------------------------------

if ($assetaction=="edit") {
	//lookup this asset detail
	$datenow = date("Y-m-d H:i:s");

	if($assetid){
		if($assetid=="new"){
			//new detail
			getpost_ifset(array('AssetOptions'));
			$CatID=$AssetOptions;
			$AssetCategory=$AssetOptions;
		}else{
			//existing detail
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('tblAssets'))
				->where($db->qn('AssetID').' = '.$db->q($assetid));
			try {
				$result = $db->setQuery($query)->loadAssocList();
			} catch(Exception $e) {
				echo("Can't find asset detail to edit");
				exit();
			}
			
			$num_rows = count($result);
			
			# If the search was unsuccessful then Display Message try again.
			if (empty($num_rows)) {
				echo("<tr><td class=list_small>Sorry - no details available for this asset</td></tr>"); 
				exit();
			}
		
			$datenow = time();
			$row = reset($result);
			$AssetID = $row["AssetID"];
			$VesselID = $row["VesselID"];
			$AssetCategory = stripslashes($row["AssetCategory"]);
			$AssetCategoryDesc = stripslashes($row["AssetCategoryDesc"]);				
			$AssetTitle = stripslashes($row["AssetTitle"]);
			$AssetDescription = stripslashes($row["AssetDescription"]);
			$AssetDate = $row["AssetDate"];
			$AssetLastUpdate = $row["AssetLastUpdate"];
			$AssetPrivacy = $row["AssetPrivacy"];
			
		}

		//check if prefered options required and lookup for edit box
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('tblAssetsCategories'))
			->where($db->qn('CatID').' = '.$db->q($AssetCategory));
		$result = $db->setQuery($query)->loadAssocList();
		if (empty($result)) {
			echo("<P>Error finding data</P>");
			exit();
		}
		$num_rows = count($result);
		if (empty($num_rows)) {
			$message="No information available at this time";
			exit();
		}

		$row = reset($result);
		//CatID CatDesc CatSort CatHelp CatPreferedOptions
		$CatPreferedOptions=$row["CatPreferedOptions"];
		$CatHelp=$row["CatHelp"];
		$CatID=$row["CatID"];
		if(!$AssetCategoryDesc){
			$AssetCategoryDesc=$row["CatDesc"];
		}
		if($CatPreferedOptions){
			//lookup current used option from assets		
			$query = $db->getQuery(true)
				->select('DISTINCTROW '.$db->qn('AssetTitle'))
				->from($db->qn('tblAssets'))
				->where($db->qn('AssetCategory').' = '.$db->q($CatID))
				->where($db->qn('AssetTitle')." <> ''")
				->order($db->qn('AssetTitle'));
			$PreferedOptions = $db->setQuery($query)->loadAssocList();
			if (empty($PreferedOptions)) {
				echo("<P>Error finding data</P>");
				//exit();
			}
			$num_rows = count($PreferedOptions);
			if (empty($num_rows)) {
				$message="No information available at this time";
				exit();
			}
			$olist="";
			$optionno=0;
	
			$olist="<select class=\"formcontrol\" name=\"keywords\" id=\"keywords\" onChange=\"inserttext(this.form.keywords.options[this.form.keywords.selectedIndex].value)\">\n";
			$olist.="<option value=\"0\">Choose an option if possible</option>\n";
			foreach($PreferedOptions as $row){
				$OptionAssetTitle=$row["AssetTitle"];
				//create an options dropdown		
	
				$olist.="<option value=\"".$OptionAssetTitle."\"".$selectit.">".$OptionAssetTitle."</option>\n";
				$optionno+=1;
			}
			$olist.="</select>\n";

		}
		
	}
	$thisassetaction="editdetail";

?>
	<script language="JavaScript">
  
    function Help(Subject){
    var mypage = Subject;
    var myname = "help";
    //var w = (screen.width - 100);
    //var h = (screen.height - 100);
    var w = 530;
    var h = 300;
    var scroll = "yes";
    var winl = (screen.width - w) / 2;
    var wint = (screen.height - h) / 2;
    winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scroll+',resizable'
	mypage += (mypage.indexOf('?') != -1 ? '&' : '?') + 'nocache=' + Date.now();
    win = window.open(mypage, myname, winprops)
    if (parseInt(navigator.appVersion) >= 4) { win.window.focus(); }
    }
    
    function inserttext(text) {
        var txtarea = document.form.AssetTitle;
        //text = ' ' + text + ' ';
        txtarea.value  = text;
		document.form.keywords.options["0"].selected=true;
        txtarea.focus();
    }
    
    function storeCaret(textEl) {
        if (textEl.createTextRange) textEl.caretPos = document.selection.createRange().duplicate();
    }

    function SubmitContent() {	
		var strValidChars = "0123456789.-";
  		var errors="";
		var AssetTitle = document.form.AssetTitle.value;
		var AssetDescription = document.form.AssetDescription.value;
		var AssetCategoryDesc = document.form.AssetCategoryDesc.value;
	

		if (AssetCategoryDesc == "Barge Name"){
			if (AssetTitle == ""){
				errors+='- Enter a Barge Name\n';
				document.form.AssetTitle.style.backgroundColor ="#ffff00";
			}else{
				document.form.AssetTitle.style.backgroundColor ="#ffffff";
			}
		}
		if (AssetCategoryDesc == "Year Built"){
			if (AssetTitle == "" || !AssetTitle.toString().match(/^[-]?\d*\.?\d*$/)){
				errors+='- Numeric year built only\n';
				document.form.AssetTitle.style.backgroundColor ="#ffff00";
			}else{
				document.form.AssetTitle.style.backgroundColor ="#ffffff";
			}
		}
		if (AssetCategoryDesc == "Barge Class"){
			if (AssetTitle == ""){
				errors+='- Choose from the list or if you really can`t find it, type it in the `Entry` box\n';
				document.form.AssetTitle.style.backgroundColor ="#ffff00";
			}else{
				document.form.AssetTitle.style.backgroundColor ="#ffffff";
			}
		}
		if (AssetCategoryDesc == "Length M"){
			if (AssetTitle == "" || !AssetTitle.toString().match(/^[-]?\d*\.?\d*$/)){
				errors+='- Numeric length in metres only\n';
				document.form.AssetTitle.style.backgroundColor ="#ffff00";
			}else{
				document.form.AssetTitle.style.backgroundColor ="#ffffff";
			}
		}
		if (AssetCategoryDesc == "Beam M"){
			if (AssetTitle == "" || !AssetTitle.toString().match(/^[-]?\d*\.?\d*$/)){
				errors+='- Numeric beam in metres only\n';
				document.form.AssetTitle.style.backgroundColor ="#ffff00";
			}else{
				document.form.AssetTitle.style.backgroundColor ="#ffffff";
			}
		}
		if (AssetCategoryDesc == "Registration"){
			if (AssetTitle == ""){
				errors+='- Put the registration in the `Entry` box\n';
				document.form.AssetTitle.style.backgroundColor ="#ffff00";
			}else{
				document.form.AssetTitle.style.backgroundColor ="#ffffff";
			}
		}
		if (AssetCategoryDesc == "Propulsion"){
			if (AssetTitle == "" || (AssetTitle!="Sail / Power" && AssetTitle!="Sail" && AssetTitle!="Power" && AssetTitle!="Unpowered")){
				errors+='- Select Sail, Sail / Power or Power, there is another detail for engine type\n';
				document.form.AssetTitle.style.backgroundColor ="#ffff00";
			}else{
				document.form.AssetTitle.style.backgroundColor ="#ffffff";
			}
		}
		if (AssetCategoryDesc == "Location"){
			if (AssetTitle == ""){
				errors+='- Put the Location in the `Entry` box\n';
				document.form.AssetTitle.style.backgroundColor ="#ffff00";
			}else{
				document.form.AssetTitle.style.backgroundColor ="#ffffff";
			}
		}
		if (AssetCategoryDesc == "Use Configuration"){
			if (AssetTitle == ""){
				errors+='- Choose an option from the list unless you are sure that you need to add an alternative.\n';
				document.form.AssetTitle.style.backgroundColor ="#ffff00";
			}else{
				document.form.AssetTitle.style.backgroundColor ="#ffffff";
			}
		}
		if (AssetCategoryDesc == "History"){
			if (AssetTitle == ""){
				document.form.AssetTitle.value="-";
			}
			if (AssetDescription == ""){
				errors+='- Put some history in the `Additional info` box\n';
				document.form.AssetDescription.style.backgroundColor ="#ffff00";
			}else{
				document.form.AssetDescription.style.backgroundColor ="#ffffff";
			}
		}
		if (AssetCategoryDesc == "Engine"){
			if (AssetTitle == ""){
				errors+='- Choose an option from the list unless you are sure that you need to add an alternative.\n';
				document.form.AssetTitle.style.backgroundColor ="#ffff00";
			}else{
				document.form.AssetTitle.style.backgroundColor ="#ffffff";
			}
		}
		if (AssetCategoryDesc == "Gearbox"){
			if (AssetTitle == ""){
				errors+='- Choose an option from the list unless you are sure that you need to add an alternative.\n';
				document.form.AssetTitle.style.backgroundColor ="#ffff00";
			}else{
				document.form.AssetTitle.style.backgroundColor ="#ffffff";
			}
		}
	
		if (AssetCategoryDesc == "Feature"){
			if (AssetTitle == ""){
				errors+='- Put a title in the `Entry` box\n';
				document.form.AssetTitle.style.backgroundColor ="#ffff00";
			}else{
				document.form.AssetTitle.style.backgroundColor ="#ffffff";
			}
			if (AssetDescription == ""){
				errors+='- Put a description in the `Additional info` box\n';
				document.form.AssetDescription.style.backgroundColor ="#ffff00";
			}else{
				document.form.AssetDescription.style.backgroundColor ="#ffffff";
			}
		}
		if (AssetCategoryDesc == "MMSI"){
			if (AssetTitle == "" || !AssetTitle.toString().match(/^[-]?\d*\.?\d*$/)){
				errors+='- Put the MMSI number (only) in the `Entry` box\n';
				document.form.AssetTitle.style.backgroundColor ="#ffff00";
			}else{
				document.form.AssetTitle.style.backgroundColor ="#ffffff";
			}
		}

		if (errors) {
			alert('Please check the highlighted entries and try again:\n'+errors);
		}else{
			//document.form.upload.src="Images/common/livinga22.gif";
			document.form.save.value='Please Wait . . . . Updating . .';
			document.form.submit();
		}	

	}

	function DeleteContent() {
		if (confirm("Confirm deletion by clicking OK")) {
			document.form.submit();	
		}else{
			document.form.assetaction.value='editdetail';
		}
	}	
   
    </script>
 <tr><td colspan=2 class='list_small'><a href="#" onClick="document.form.assetaction.value='<?php echo($thisassetaction); ?>';document.form.submit()">Back to the details <img src="Image/common/back1.gif" width="18" height="18" border="0" alt="Back to the details"></a>
 </td></tr>
    <tr><td colspan=2 class='list_small'>
      
    <strong>Edit detail -</strong>
    <input name="AssetCategoryDesc" type="hidden" value="<?php echo(str_replace("_", " ", $AssetCategoryDesc)); ?>">
    <input name="AssetCategory" type="hidden" value="<?php echo($AssetCategory); ?>">

    <?php echo(str_replace("_", " ", $AssetCategoryDesc)); ?>
    &nbsp;&nbsp;<input type="button" name="save" class="formcontrol" value="Save &raquo;" onclick="document.form.assetaction.value='save';SubmitContent(this);" />
    
		
    <?php if($AssetCategory>1 && $assetid!="new"){ ?>
       &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="button" name="delete" class="formcontrol" value="Delete" onClick="document.form.assetaction.value='delete';DeleteContent(this);">
    <?php
    }
	?>
    
    
    </td></tr>

	<tr>
	  <td valign="top" class='table_stripe_even'>Entry</td>
	<td class='table_stripe_even'><input name="AssetTitle" type="text" class="formcontrol" id="AssetTitle" value="<?php echo($AssetTitle); ?>" size="35" maxlength="50" /> <?php 
	if($olist){
		echo($olist);
	}
	if($CatHelp){
		echo("<br><img src=\"Image/common/info.gif\" alt=\"Help\" /> $CatHelp\n");
	}
		 ?></td>
  </tr>
  <tr>
<td valign="top" class='table_stripe_even'> Picture</td>
<td class='table_stripe_even'><?php
	 //check if there is an image
	 $imagepath="Image/register/".$assetid.".jpg";
	//add any images underneath 
 
	if (file_exists($imagepath)) {
		$imageInfo = getimagesize($imagepath);
		//$imwidth = $imageInfo[0];
		//$imheight = $imageInfo[1]; 
		$imwidth = 100;
		$imheight = $imageInfo[1]/($imageInfo[0]/100); 
		$mediatitle=$AssetTitle;
		$imagedetail.="<img src=\"".$imagepath."\" width=".$imwidth." height=".$imheight." border=0 title=\"".$mediatitle."\" alt=\"".$mediatitle."\">\n";  
		echo($imagedetail);
	}
	?>
     
     
     <input type="FILE" name="thefile" id="thefile" class="formcontrol" size="70" />
     
     <input type="hidden" name="MAX_FILE_SIZE" value="1000000">
     <a href='javascript:Help("/components/com_waterways_guide/views/bargeregister/tmpl/help_upload_file.php")'><img src="Image/common/help.gif" width="20" height="20" alt="Help on uploading an image" border="0"></a></td>
    </tr><tr>
<td valign="top" class='table_stripe_even'>
Additional info</td>
<td class='table_stripe_even'><textarea name="AssetDescription" class="formtextarea" cols="86" rows="20"><?php echo($AssetDescription); ?></textarea></td></tr>
 

<?php
}

if($assetaction=="adminchangelog"){
	?>

    <div class="pop_page_title"><h2>Change Log</h2></div>

    <table border="0" cellpadding="2" bgcolor="#FFFFFF" width="100%">
    <?php
	$query = $db->getQuery(true)
		->select('m.*')
		->select('cl.*')
		->from($db->qn('tblMembers', 'm'))
		->innerJoin($db->qn('tblChangeLog', 'cl').' ON '.$db->qn('m.MembershipNo').' = '.$db->qn('cl.MemberID'))
		->where($db->qn('cl.Subject')." = 'register'")
		->order($db->qn('cl.LogID').' DESC');
    
    $result = $db->setQuery($query)->loadAssocList();
    if (!empty($result)) {
		$num_rows=count($result);
		echo("<tr><td><b>Member</b></td>\n");
		echo("<td><b>Change details</b></td>\n");
		echo("<td><b>Date</b></td></tr>\n");
		//echo("<tr><td bgcolor=\"".$bgc."\" colspan=4><hr></td></tr>\n");
		$thisrow = "odd";
		foreach($result as $row) {
			$changedatedisplay = date("d M Y", strtotime($row['ChangeDate']));
			if($thisrow=="odd"){
				$rowclass="table_stripe_even";
				$thisrow="even";
			}else{
				$rowclass="table_stripe_odd";		
				$thisrow="odd";
			}	
			if($row["MembershipNo"]){
				$thismember=$row["MembershipNo"]." ".$row["LastName"];
			}else{
				$thismember="Archived ID:".$row["MemberID"];
			}
			
			echo("<tr><td width=90 valign=top class=".$rowclass.">".$thismember."</td>\n");
			echo("<td valign=top class=".$rowclass.">".$row["ChangeDesc"]."</td>\n");
			echo("<td valign=top width=90 class=".$rowclass.">".$changedatedisplay."</td></tr>\n");
		}
	}else{
		echo("There are no recorded changes\n");
	}
    
    ?>
    
    </table>
	<?php
}
?>
</table>
