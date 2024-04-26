<?php


/*
To do ................................

*/


/*
Updates ................................
20210620 Conversion of mysql to mysqli in preparation for upgrade from PHP 5x to 7x
20180817 GDPR deletion of ex member, email, phone, address in both tblMembers and #__users
20140407 Email no longer directly sent from this script but added to mail queue table for bact sending on minute cron
20140302 Classified section removed to seperate cron in order to limit crash to member cron
20101209 Renewal reminder emails password removed 
20100405 Monthly and year to date stats on new and terminated members added, email to treasurer
20090206 Stats tblStats created to store daily stats for reporting.
Daily member totals will be added here to track member trends

20090402 $live=1 added and email senders prior to going live
*/

//cron command   /usr/local/bin/php -q /home/bargesor/public_html/components/com_waterways_guide/cron/cron.php


//load Joomla helpers
define( '_JEXEC', 1 );
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', strstr(__DIR__, 'public_html', true).'public_html');
define('JPATH_COMPONENT', JPATH_BASE .DS.'components'.DS.'com_waterways_guide');
require_once(JPATH_BASE .DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE .DS.'includes'.DS.'framework.php');
require_once(JPATH_COMPONENT .DS.'commonV3.php');

use Joomla\CMS\Factory;
$config = Factory::getConfig();
$mailOn = Factory::getConfig()->get('mailonline') == '1';

$app = Factory::getApplication('com_waterways_guide');
$db = Factory::getDBO();
$test_vars=(array('test', 'input_surname', 'input_barge'));
		
			foreach($test_vars as $test_var) { 
				if(!$$test_var =  $app->input->getString($test_var)){
					$$test_var = "";
				}
			}

$test=0; //simulate 1 or live 0
if(!$test){
	$live=1;
	$livemail=1;
	$livemailadmin=1;	
	
}else{
	$live=0;
	$livemail=0;
	$livemailadmin=0;
}

function get_param($ParameterName, $db){
	$query = $db->getQuery(true)
		->select($db->qn('ParameterValue'))
		->from($db->qn('tblParameters'))
		->where($db->qn('ParameterName').' = '.$db->q($ParameterName));
	return $db->setQuery($query)->loadResult();
}
 
//reset reporting values  
$classifiedpostings=0;
$classifiedalerts=0;
$classifiedrenewalreminder=0;
$classifiedexpired=0;
$num_members=0;
$newjoinernotpaid=0;
$overduearchived=0;
$overduefinalreminder=0;
$reminderch =0;
$remindercc=0;
$reminderso=0;
$reminderdd=0;
$terminatedarchived=0;
$live_members=0;
$joiner_ch=0;
$joiner_cc=0;
$joiner_dd=0;

$datenow = date("Y-m-d");


//check membership renewals ---------------------------------------------------------------

$listing = "<table>";
$message="";
$listing.="<tr><td class=content><b>Last Name</b></td><td class=content><b>Mem No</b></td><td class=content><b>Current Status</b></td><td class=content><b>Sub</b></td><td class=content><b>Date Paid</b></td><td class=content><b>Method</b></td><td class=content><b>Days Overdue</b></td></tr>\n";
$secs_now = time();
//$secs_now = time()-(2*60*60);
$secsinayear=31536000;
$changedate=date("Y-m-d H:i:s");
$DateArchive=date("Y-m-d H:i:s");
$thedate=date("d M Y");	

//check subs renewal and action as required
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblMembers'))
	->order($db->qn('DatePaid').' DESC');
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows==0){
	$screenmessage="Can't find members." ;
}else{
	$num_members = count($mymembers);
	$key = "dba";
	$done=0;
	$thisid=0;
	$AdminName = get_param("AdminName", $db);
	foreach($mymembers as $row) {
		$mn=$row["ID"];
		$login=$row["Login"];
		//$memberemail=$memberemail;
		$memberemail=$row["Email"];
		//$password=$row["PW"];
		$password="(which you can request from the login page if you are unsure)";
		$userid=$row["ID"];
		$ID2=$row["ID2"];
		$MembershipNo=$row["MembershipNo"];
		$MemType=$row["MemType"];
		$MemTypeCode=$row["MemTypeCode"];
		$BasicSub=$row["BasicSub"];
		$Title=$row["Title"];
		$LastName=$row["LastName"];
		if(!empty($Title) && !empty($FullName)){
			$FullName="$Title $LastName";
		}else{
			$FullName="Member";		
		}
		$address=$FullName."\n";
		$Address1=$row["Address1"];
		if($Address1){
			$address=$Address1;
		}
		$Address2=$row["Address2"];
		if($Address2){
			$address.="\n".$Address2;
		}
		$Address3=$row["Address3"];
		if($Address3){
			$address.="\n".$Address3;
		}
		$Address4=$row["Address4"];
		if($Address4){
			$address.="\n".$Address4;
		}
		$PostCode=$row["PostCode"];
		if($PostCode){
			$address.="\n".$PostCode;
		}
		$Country=$row["Country"];
		if($Country){
			$address.="\n".$Country;
		}		
		
		
		
		
		$DateJoined=$row["DateJoined"];
		$DatePaid=$row["DatePaid"];
		$PaymentMethod=$row["PaymentMethod"];
		$MemStatus=$row["MemStatus"];
		switch ($MemStatus) {
			case "":
				$statusdesc="Unknown";
				break;
			case "1":
				$statusdesc="Applied pending payment";
				break;
			case "2":
				$statusdesc="Paid up";
				break;
			case "3":
				$statusdesc="Renewal overdue";
				break;
			case "4":
				$statusdesc="Gone away";
				break;			
			case "5":
				$statusdesc="Terminated";
				break;
			case "6":
				$statusdesc="Complimentary";
				break;
		}

		


		if($MemStatus<5 || $MemStatus==7){
			//paying member - not complimentary or set manually to terminate [7]
			//check the time between now and last paid 31536000 secs in a year
			
			$my_secs=strtotime($row["DatePaid"]);
			if($myyear = date('Y', $my_secs)) {
				$datelastpaid = implode('-', [$myyear, date('m', $my_secs), date('d', $my_secs)]);
			}else{
				$datelastpaid="blank - joined $DateJoined";
			}
			$renewyear=$myyear+1;
			//$RenewalDate=$myday."/".$mymonth." ".$renewyear;

			$RenewalDate=date("d F, Y", $my_secs+$secsinayear ); 
			$error=($secs_now-$my_secs);
			$daysoverdue=number_format (($error-$secsinayear)/86400);

			if($MemStatus==1){
				//joiner never paid
				//check the time between now and join date 31536000 secs in a year
				
				switch ($PaymentMethod) {
					case "ch":
						$joiner_ch+=1;
						break;
					case "cc":
						$joiner_cc+=1;
						break;
					case "dd":
						$joiner_dd+=1;
						break;						
				}			
				$my_secs=strtotime($row["DateJoined"]);
				$error=($secs_now-$my_secs);
				$dayssincejoined=number_format (($error)/86400);
				
				if($dayssincejoined==14){
					//remind havent paid
					/*				
					- check membership cc so dd ch payers 
					- list to admin@barges.org for ref.
					- enter action into member change log
					- email reminder from admin@barges.org:
					subpayfirst_notificationtext_final_cc_so_dd_ch (14 days over)
					*/
					$daysoverdue=13;
					$DateJoined=date("d F, Y",$my_secs); 
					$etext=get_param("subpayfirst_notificationtext_final_cc_so_dd_ch", $db);
					//Search and replace in email templates
					$DeletionDate=date("d F, Y",$secs_now+(42*60*60*24)); 
					$searchwords="thedate,address,membershipmailaddress,AdminName,DateJoined,RenewalDate,DeletionDate,BasicSub,FullName,emailfooter,MembershipNo,login,password";
					$replace = explode (",", $searchwords);
					$maxwords=sizeof ($replace);
					$wordno=0;
					$rawtext=$etext;
					while($wordno<$maxwords){
						$thisword=$replace[$wordno];
						$etext= str_replace("[$thisword]", ${$thisword}, $etext);
						$wordno+=1;
					}
	
					$listing.="<tr><td class=content>New joiner sub not paid after $dayssincejoined days: $LastName </td><td class=content>$MembershipNo</td><td class=content>$statusdesc</td><td class=content>$BasicSub</td><td class=content>$datelastpaid</td><td class=content>$PaymentMethod</td><td class=content>$daysoverdue</td></tr>\n";

					//check valid email @
					if (strpos($memberemail, "@") !== false) {
						if($livemail==1){
							//send the email
							$esubject="DBA Subscription";
							$emessage=addslashes($etext);
							//save message to log tblMailHistoryMessageLog
							$insert = new \stdClass();
							$insert->Subject = $esubject;
							$insert->Message = $emessage;
							$insert->SenderEmail = $membershipemail;
							$insert->SenderName = 'DBA Membership auto administration';
							$insert->Queued = $changedate;
							$db->insertObject('tblMailHistoryMessageLog', $insert, 'ID');
							$messageid = $insert->ID;
							//add record to email pending table
							$efullname=addslashes($FullName);
							$insert = new \stdClass();
							$insert->MessageID = $messageid;
							$insert->MemberID = $userid;
							$insert->MemberName = $efullname;
							$insert->MemberEmail = $memberemail;
							$insert->Queued = $changedate;
							$db->insertObject('tblMailHistoryRecipientLog', $insert);
						}

					}else{
						//no email address
						$listing.="<tr><td class=content colspan=7><b>NO EMAIL ADDRESS, LETTER REQUIRED</b></td></tr>\n";		
						$listing.="<tr><td class=content colspan=7>".nl2br($address)."<br><br></td></tr>\n";
					
					}						
					$listing.="<tr><td class=content colspan=7>".nl2br($etext)."</td></tr>\n";					
					if($live==1){
						//enter action into member change log (for the benefit of admin or traceability)
						$subject="Subscription";
						$changelogtext="Joining subscription overdue by 14 days, email reminder sent";
						$insert = new \stdClass();
						$insert->MemberID = $userid;
						$insert->Subject = $subject;
						$insert->ChangeDesc = $changelogtext;
						$insert->ChangeDate = $changedate;
						$db->insertObject('tblChangeLog', $insert) or die ("Couldn't update change log");
					}
					$listing.="<tr><td class=content colspan=7><hr></td></tr>\n";
					$newjoinernotpaid+=1;
				}
				if($dayssincejoined==42){
					//remind havent paid
					/*				
					- check membership cc so dd ch payers 
					- list to admin@barges.org for ref.
					- enter action into member change log
					- email reminder from admin@barges.org:
					subpayfirst_notificationtext_final_cc_so_dd_ch (42 days over)
					*/
					$daysoverdue=41;
					$DateJoined=date("d F, Y",$my_secs); 
					$etext=get_param("subpayfirst_notificationtext_final_cc_so_dd_ch", $db);
					//Search and replace in email templates
					$DeletionDate=date("d F, Y",$secs_now+(42*60*60*24)); 
					$searchwords="thedate,address,membershipmailaddress,AdminName,DateJoined,RenewalDate,DeletionDate,BasicSub,FullName,emailfooter,MembershipNo,login,password";
					$replace = explode (",", $searchwords);
					$maxwords=sizeof ($replace);
					$wordno=0;
					$rawtext=$etext;
					while($wordno<$maxwords){
						$thisword=$replace[$wordno];
						$etext= str_replace("[$thisword]", ${$thisword}, $etext);
						$wordno+=1;
					}
	
					$listing.="<tr><td class=content>New joiner sub not paid after $dayssincejoined days: $LastName </td><td class=content>$MembershipNo</td><td class=content>$statusdesc</td><td class=content>$BasicSub</td><td class=content>$datelastpaid</td><td class=content>$PaymentMethod</td><td class=content>$daysoverdue</td></tr>\n";

					//check valid email @
					if (strpos($memberemail, "@") !== false) {
						if($livemail==1){
							//send the email
							$esubject="DBA Subscription";
							$emessage=addslashes($etext);
							//save message to log tblMailHistoryMessageLog
							$insert = new \stdClass();
							$insert->Subject = $esubject;
							$insert->Message = $emessage;
							$insert->SenderEmail = $membershipemail;
							$insert->SenderName = 'DBA Membership auto administration';
							$insert->Queued = $changedate;
							$db->insertObject('tblMailHistoryMessageLog', $insert, 'ID');
							$messageid = $insert->ID;
							//add record to email pending table
							$efullname=addslashes($FullName);
							$insert = new \stdClass();
							$insert->MessageID = $messageid;
							$insert->MemberID = $userid;
							$insert->MemberName = $efullname;
							$insert->MemberEmail = $memberemail;
							$insert->Queued = $changedate;
							$db->insertObject('tblMailHistoryRecipientLog', $insert);
						}
					}else{
						//no email address
						$listing.="<tr><td class=content colspan=7><b>NO EMAIL ADDRESS, LETTER REQUIRED</b></td></tr>\n";		
												$listing.="<tr><td class=content colspan=7>".nl2br($address)."<br><br></td></tr>\n";
					
					}						
					$listing.="<tr><td class=content colspan=7>".nl2br($etext)."</td></tr>\n";					
					if($live==1){
						//enter action into member change log (for the benefit of admin or traceability)
						$subject="Subscription";
						$changelogtext="Joining subscription overdue by 42 days, final email reminder sent";
						$insert = new \stdClass();
						$insert->MemberID = $userid;
						$insert->Subject = $subject;
						$insert->ChangeDesc = $changelogtext;
						$insert->ChangeDate = $changedate;
						$db->insertObject('tblChangeLog', $insert) or die ("Couldn't update change log");
					}
					$listing.="<tr><td class=content colspan=7><hr></td></tr>\n";
					$newjoinernotpaid+=1;
				}
				if($dayssincejoined>=77){
					//have ignored reminder so archive and remove
					//make daysoverdue 46 to catch in next archive section
					$daysoverdue=46;
					//$newjoinernotpaid+=1;
				}
			}
			if($daysoverdue==46 || $MemStatus==7){
				//archive and set status terminated
				//membership cc so dd ch payers with 46 days overdue
				//or new joiners 46+35 days still not paid
				
										
				//check if exists and transfer any vessel register entry to 'unknown keeper'
				//get the vessel(s)
				$query = $db->getQuery(true)
					->select('*')
					->from($db->qn('tblAssetsMembers'))
					->where($db->qn('MembershipNo').' = '.$db->q($MembershipNo));
				$vessels = $db->setQuery($query)->loadAssocList();
				$num_rows = count($vessels);
				if($num_rows>0){
					$vrows = count($vessels);
					foreach($vessels as $vrow) {
						$VesselID = stripslashes($vrow["ID"]);
						$keepers = $vrow["Keeper"];
						if($keepers){
							//add the current keeper MembershipNo to any others to provide archive history
							$keepers.=",".$MembershipNo;
						}else{
							$keepers=$MembershipNo;
						}
						//vessel(s) found so transfer vessel id to admin	
						//$VesselNoKeeperMID in commonV3.php
						if($live==1){
							$update = new \stdClass();
							$update->Keeper = $keepers;
							$update->Status = '2';
							$update->LastUpdate = $datenow;
							$update->ID = $VesselID;
							$db->updateObject('tblAssetsMembers', $update, 'ID') or die ("Couldn't update database");
						}
						//lookup vessel name
						$query = $db->getQuery(true)
							->select('COUNT(*)')
							->from($db->qn('tblAssets'))
							->where($db->qn('VesselID').' = '.$db->q($VesselID))
							->order($db->qn('AssetCategory'));
						$vname = $db->setQuery($query)->loadAssocList();
						$num_rows = count($vname);
						if($num_rows==0){
							echo("Can't find assets $sql");
							$num_vessels=0;
							//exit();
						}else{
							$num_vessels = count($vname);
						}
						# If the search was unsuccessful then Display Message try again.
						if ($num_vessels>0) {
							foreach($vname as $arow) {
								//Go through rows to find name asset category = l
								foreach(['AssetID','AssetCategory','AssetCategoryDesc','AssetTitle'] as $ak) {
									$$ak = isset($arow[$ak]) ? stripslashes($arow[$ak]) : '';
								}
								if($AssetCategory==1){
									$vesselname=$AssetTitle;
								} else {
									$vesselname="Unknown";
								}
							}
						}else{
							$vesselname="Unknown";
						}
						if($live==1){
							//Change the privacy contact settings to blank = no contact
							$update = new \stdClass();
							$update->AssetPrivacy = '';
							$update->VesselID = $VesselID;
							$db->updateObject('tblAssets', $update, 'VesselID') or die ("Couldn't update database");

							//update change log
							$changelogtext=addslashes("Vessel '$vesselname' detached from this profile due to member profile termination.");
							$subject="Register";
							$insert = new \stdClass();
							$insert->MemberID = $userid;
							$insert->Subject = $subject;
							$insert->ChangeDesc = $changelogtext;
							$insert->ChangeDate = $changedate;
							$db->insertObject('tblChangeLog', $insert) or die ("Couldn't update change log");
						}
						$listing.="<tr><td class=content colspan=7>Vessel '$vesselname' detached from this profile</td></tr>\n";
					}
				}
				if($live==1){
					//set the termination date
					//revised query to include blanking of any vessel data from barge register
					$update = new \stdClass();
					$update->MemStatus = '5';
					$update->DateArchive = $DateArchive;
					$update->DateCeased = $DateArchive;
					$update->Login = $userid;
					$update->PW = '';
					$update->Email2 = '';
					$update->Address1 = '';
					$update->Address2 = '';
					$update->Address3 = '';
					$update->Address4 = '';
					$update->PostCode = '';
					$update->Telephone1 = '';
					$update->Telephone2 = '';
					$update->Mobile2 = '';
					$update->Email = '';
					$update->Keywords = $LastName;
					$update->ID = $userid;
					$db->updateObject('tblMembers', $update, 'ID') or die ("Couldn't update leaving date");
					//set the joomla user block user from login
					$update = new \stdClass();
					$update->block = '1';
					$update->username = $ID2;
					$update->email = '';
					$update->password = '';
					$update->id = $ID2;
					$db->updateObject('#__users', $update, 'id') or die ("Couldn't update status");
					//and a family partner?
					if($ID2){
						$update = new \stdClass();
						$update->block = '1';
						$update->username = $ID2;
						$update->email = '';
						$update->password = '';
						$update->id = $ID2;
						$db->updateObject('#__users', $update, 'id') or die ("Couldn't update status");
					}
					

				}
				if(!isset($terminations)) $terminations = '';
				$terminations.=" $userid $LastName <br>";	
				if($MemStatus==7){
					//Manual termination by admin 
						
					//enter action into member change log (for the benefit of admin or traceability)
					$subject="Profile terminated";
					$changelogtext="Profile terminated by administrator";
					if($live==1){
						$insert = new \stdClass();
						$insert->MemberID = $userid;
						$insert->Subject = $subject;
						$insert->ChangeDesc = $changelogtext;
						$insert->ChangeDate = $changedate;
						$db->insertObject('tblChangeLog', $insert) or die ("Couldn't update change log");
					}	
					$terminatedarchived+=1;

				
				}else{
					//enter action into member change log (for the benefit of admin or traceability)
					$subject="Profile terminated";
					$changelogtext="Subscription overdue final reminder has been ignored, profile terminated";
					if($live==1){
						$insert = new \stdClass();
						$insert->MemberID = $userid;
						$insert->Subject = $subject;
						$insert->ChangeDesc = $changelogtext;
						$insert->ChangeDate = $changedate;
						$db->insertObject('tblChangeLog', $insert) or die ("Couldn't update change log");
					}
					$overduearchived+=1;
				}
				//$listing.="<tr><td class=content colspan=7><hr></td></tr>\n";
				
					
			}elseif($daysoverdue==30 && ($PaymentMethod=="so" || $PaymentMethod=="dd")){
				//dd and so reminder to be decided
				
				
				
				
			}elseif($daysoverdue==7 && $PaymentMethod!="so" && $PaymentMethod!="dd"){
				//if($daysoverdue==7){				
				//if($daysoverdue>=7 && $daysoverdue<=42){	//run once			
				/*				
				2.	
				- check membership cc so dd ch payers with 7 past renewal
				- list to admin@barges.org for ref.
				- change MemberStatus to 'RenewalOverdue'
				- enter action into member change log
				- email reminder from admin@barges.org:
				subrenew_notificationtext_final_cc_so_dd_ch (7 days over)
				*/
				$etext=get_param("subrenew_notificationtext_final_cc_so_dd_ch", $db);
				//Search and replace in email templates
				$DeletionDate=date("d F, Y",$secs_now+(37*60*60*24)); 
				$searchwords="thedate,address,membershipmailaddress,AdminName,RenewalDate,DeletionDate,BasicSub,FullName,emailfooter,MembershipNo,login,password";
				$replace = explode (",", $searchwords);
				$maxwords=sizeof ($replace);
				$wordno=0;
				$rawtext=$etext;
				while($wordno<$maxwords){
					$thisword=$replace[$wordno];
					$etext= str_replace("[$thisword]", ${$thisword}, $etext);
					$wordno+=1;
				}

				$listing.="<tr><td class=content>Renewal overdue (7 days): $LastName</td><td class=content>$MembershipNo</td><td class=content>$statusdesc</td><td class=content>$BasicSub</td><td class=content>$datelastpaid</td><td class=content>$PaymentMethod</td><td class=content>$daysoverdue</td></tr>\n";
				if (strpos($memberemail, "@") !== false) {
					if($livemail==1){
						//send the email
						$esubject="DBA Subscription renewal overdue";
						$emessage=addslashes($etext);
						//save message to log tblMailHistoryMessageLog
						$insert = new \stdClass();
						$insert->Subject = $esubject;
						$insert->Message = $emessage;
						$insert->SenderEmail = $membershipemail;
						$insert->SenderName = 'DBA Membership auto administration';
						$insert->Queued = $changedate;
						$db->insertObject('tblMailHistoryMessageLog', $insert, 'ID');
						$messageid = $insert->ID;
						//add record to email pending table
						$efullname=addslashes($FullName);
						$insert = new \stdClass();
						$insert->MessageID = $messageid;
						$insert->MemberID = $userid;
						$insert->MemberName = $efullname;
						$insert->MemberEmail = $memberemail;
						$insert->Queued = $changedate;
						$db->insertObject('tblMailHistoryRecipientLog', $insert);
					}
				}else{
					//no email address
					$listing.="<tr><td class=content colspan=7><b>NO EMAIL ADDRESS, LETTER REQUIRED</b></td></tr>\n";		
					$listing.="<tr><td class=content colspan=7>".nl2br($address)."<br><br></td></tr>\n";
				}			
				$listing.="<tr><td class=content colspan=7>".nl2br($etext)."</td></tr>\n";
				if($live==1){
					//update member record status to renewal overdue	
					$update = new \stdClass();
					$update->MemStatus = '3';
					$update->ID = $userid;
					$db->updateObject('tblMembers', $update, 'ID') or die ("Couldn't remove user");				
					//enter action into member change log (for the benefit of admin or traceability)
					$subject="Subscription";
					$changelogtext="Subscription overdue by 7 days, final email reminder sent";
					$insert = new \stdClass();
					$insert->MemberID = $userid;
					$insert->Subject = $subject;
					$insert->ChangeDesc = $changelogtext;
					$insert->ChangeDate = $changedate;
					$db->insertObject('tblChangeLog', $insert) or die ("Couldn't update change log");
				}
				$listing.="<tr><td class=content colspan=7><hr></td></tr>\n";
				$overduefinalreminder+=1;
		
			}elseif($daysoverdue==-42 && $PaymentMethod=="ch"){				
				/*			
				Advance warning of renewal
				- check membership ch payers with  42 days to renewal
				- list to admin@barges.org for ref.
				- enter action into member change log 
				- email reminder from admin@barges.org:
				subrenew_notificationtext_ch (42 days)
				*/
				$listing.="<tr><td class=content>Renewal reminder (<42 days ch): $FullName</td><td class=content>$MembershipNo</td><td class=content>$statusdesc</td><td class=content>$BasicSub</td><td class=content>$datelastpaid</td><td class=content>$PaymentMethod</td><td class=content>$daysoverdue</td></tr>\n";
				if (strpos($memberemail, "@") !== false) {
					$etext=get_param("subrenew_notificationtext_ch", $db);
				}else{
					$etext=get_param("subrenew_notificationtext_ch_letter", $db);
				}
				//Search and replace in email templates
				$DeletionDate=date("d F, Y",$secs_now+(37*60*60*24)); 
				$searchwords="thedate,address,membershipmailaddress,AdminName,RenewalDate,DeletionDate,BasicSub,FullName,emailfooter,MembershipNo,login,password";
				$replace = explode (",", $searchwords);
				$maxwords=sizeof ($replace);
				$wordno=0;
				$rawtext=$etext;
				while($wordno<$maxwords){
					$thisword=$replace[$wordno];
					$etext= str_replace("[$thisword]", ${$thisword}, $etext);
					$wordno+=1;
				}

				if($live==1){
					//enter action into member change log (for the benefit of admin or traceability)
					$subject="Subscription";
					$changelogtext="Subscription due, email reminder sent";
					$insert = new \stdClass();
					$insert->MemberID = $userid;
					$insert->Subject = $subject;
					$insert->ChangeDesc = $changelogtext;
					$insert->ChangeDate = $changedate;
					$db->insertObject('tblChangeLog', $insert) or die ("Couldn't update change log");
				}
				if (strpos($memberemail, "@") !== false) {
					if($livemail==1){
						//send the email
						$esubject="DBA Subscription renewal reminder";
						$emessage=addslashes($etext);
						//save message to log tblMailHistoryMessageLog
						$insert = new \stdClass();
						$insert->Subject = $esubject;
						$insert->Message = $emessage;
						$insert->SenderEmail = $membershipemail;
						$insert->SenderName = 'DBA Membership auto administration';
						$insert->Queued = $changedate;
						$db->insertObject('tblMailHistoryMessageLog', $insert, 'ID');
						$messageid = $insert->ID;
						//add record to email pending table
						$efullname=addslashes($FullName);
						$insert = new \stdClass();
						$insert->MessageID = $messageid;
						$insert->MemberID = $userid;
						$insert->MemberName = $efullname;
						$insert->MemberEmail = $memberemail;
						$insert->Queued = $changedate;
						$db->insertObject('tblMailHistoryRecipientLog', $insert);
					}
				}else{
					//no email address
					$listing.="<tr><td class=content colspan=7><b>NO EMAIL ADDRESS, LETTER REQUIRED</b></td></tr>\n";		
					//$listing.="<tr><td class=content colspan=7>".nl2br($address)."<br><br></td></tr>\n";
				}			

				$listing.="<tr><td class=content colspan=7>".nl2br($etext)."</td></tr>\n";
				$listing.="<tr><td class=content colspan=7><hr></td></tr>\n";
				$reminderch+=1;

			}elseif($daysoverdue==-42 && $PaymentMethod=="cc"){					
				/*
				4.
				- check membership cc payers with 42 days to renewal
				- list to admin@barges.org for ref. 
				- enter action into member change log	
				- email reminder from admin@barges.org:
				subrenew_notificationtext_cc (42 days)
				*/
				$listing.="<tr><td class=content>Renewal reminder (<42 days cc): -  $FullName</td><td class=content>$MembershipNo</td><td class=content>$statusdesc</td><td class=content>$BasicSub</td><td class=content>$datelastpaid</td><td class=content>$PaymentMethod</td><td class=content>$daysoverdue</td></tr>\n";
				$etext=get_param("subrenew_notificationtext_cc", $db);
				//Search and replace in email templates
				$DeletionDate=date("d F, Y",$secs_now+(37*60*60*24)); 
				$searchwords="thedate,address,membershipmailaddress,AdminName,RenewalDate,DeletionDate,BasicSub,FullName,emailfooter,MembershipNo,login,password";
				$replace = explode (",", $searchwords);
				$maxwords=sizeof ($replace);
				$wordno=0;
				$rawtext=$etext;
				while($wordno<$maxwords){
					$thisword=$replace[$wordno];
					$etext= str_replace("[$thisword]", ${$thisword}, $etext);
					$wordno+=1;
				}


				if($live==1){
					//enter action into member change log (for the benefit of admin or traceability)
					$subject="Subscription";
					$changelogtext="Subscription due, email reminder sent";
					$insert = new \stdClass();
					$insert->MemberID = $userid;
					$insert->Subject = $subject;
					$insert->ChangeDesc = $changelogtext;
					$insert->ChangeDate = $changedate;
					$db->insertObject('tblChangeLog', $insert) or die ("Couldn't update change log");
				}
				if (strpos($memberemail, "@") !== false) {
				//if ($memberemail) {
					if($livemail==1){
						//send the email
						$esubject="DBA Subscription renewal reminder";
						$emessage=addslashes($etext);
						//save message to log tblMailHistoryMessageLog
						$insert = new \stdClass();
						$insert->Subject = $esubject;
						$insert->Message = $emessage;
						$insert->SenderEmail = $membershipemail;
						$insert->SenderName = 'DBA Membership auto administration';
						$insert->Queued = $changedate;
						$db->insertObject('tblMailHistoryMessageLog', $insert, 'ID');
						$messageid = $insert->ID;
						//add record to email pending table
						$efullname=addslashes($FullName);
						$insert = new \stdClass();
						$insert->MessageID = $messageid;
						$insert->MemberID = $userid;
						$insert->MemberName = $efullname;
						$insert->MemberEmail = $memberemail;
						$insert->Queued = $changedate;
						$db->insertObject('tblMailHistoryRecipientLog', $insert);
					}
				}else{
					//no email address
					$listing.="<tr><td class=content colspan=7><b>NO EMAIL ADDRESS, LETTER REQUIRED</b></td></tr>\n";		
					$listing.="<tr><td class=content colspan=7>".nl2br($address)."<br><br></td></tr>\n";
				}			

				$listing.="<tr><td class=content colspan=7>".nl2br($etext)."</td></tr>\n";
				$listing.="<tr><td class=content colspan=7><hr></td></tr>\n";
				$remindercc+=1;

			}elseif($daysoverdue==-30 && $PaymentMethod=="so"){					
				/*
				4.
				- check membership so payers with 30 days to renewal
				- list to admin@barges.org for ref. 
				- enter action into member change log	
				- email reminder from admin@barges.org:
				subrenew_notificationtext_so (30 days)
				*/
				$listing.="<tr><td class=content>Renewal reminder (<30 days so): -  $FullName</td><td class=content>$MembershipNo</td><td class=content>$statusdesc</td><td class=content>$BasicSub</td><td class=content>$datelastpaid</td><td class=content>$PaymentMethod</td><td class=content>$daysoverdue</td></tr>\n";
				$etext=get_param("subrenew_notificationtext_so", $db);
				//Search and replace in email templates
				$DeletionDate=date("d F, Y",$secs_now+(37*60*60*24)); 
				$searchwords="thedate,address,membershipmailaddress,AdminName,RenewalDate,DeletionDate,BasicSub,DBABankDetails,FullName,emailfooter,MembershipNo,login,password";
				$replace = explode (",", $searchwords);
				$maxwords=sizeof ($replace);
				$wordno=0;
				$rawtext=$etext;
				while($wordno<$maxwords){
					$thisword=$replace[$wordno];
					$etext= str_replace("[$thisword]", ${$thisword}, $etext);
					$wordno+=1;
				}


				if($live==1){
					//enter action into member change log (for the benefit of admin or traceability)
					$subject="Subscription";
					$changelogtext="Subscription due, email reminder sent";
					$insert = new \stdClass();
					$insert->MemberID = $userid;
					$insert->Subject = $subject;
					$insert->ChangeDesc = $changelogtext;
					$insert->ChangeDate = $changedate;
					$db->insertObject('tblChangeLog', $insert) or die ("Couldn't update change log");
				}
				if (strpos($memberemail, "@") !== false) {
				//if ($memberemail) {
					if($livemail==1){
						//send the email
						$esubject="DBA Subscription renewal reminder";
						$emessage=addslashes($etext);
						//save message to log tblMailHistoryMessageLog
						$insert = new \stdClass();
						$insert->Subject = $esubject;
						$insert->Message = $emessage;
						$insert->SenderEmail = $membershipemail;
						$insert->SenderName = 'DBA Membership auto administration';
						$insert->Queued = $changedate;
						$db->insertObject('tblMailHistoryMessageLog', $insert, 'ID');
						$messageid = $insert->ID;
						//add record to email pending table
						$efullname=addslashes($FullName);
						$insert = new \stdClass();
						$insert->MessageID = $messageid;
						$insert->MemberID = $userid;
						$insert->MemberName = $efullname;
						$insert->MemberEmail = $memberemail;
						$insert->Queued = $changedate;
						$db->insertObject('tblMailHistoryRecipientLog', $insert);
					}
				}else{
					//no email address
					$listing.="<tr><td class=content colspan=7><b>NO EMAIL ADDRESS, LETTER REQUIRED</b></td></tr>\n";		
					$listing.="<tr><td class=content colspan=7>".nl2br($address)."<br><br></td></tr>\n";
				}			

				$listing.="<tr><td class=content colspan=7>".nl2br($etext)."</td></tr>\n";
				$listing.="<tr><td class=content colspan=7><hr></td></tr>\n";
				$reminderso+=1;

			}elseif($daysoverdue==-30 && $PaymentMethod=="dd"){					
				/*No action for now.
				4.
				- check membership dd payers with 30 days to renewal
				- list to admin@barges.org for ref. 
				- enter action into member change log	
				- email reminder from admin@barges.org:
				subrenew_notificationtext_so (30 days)
				
				$listing.="<tr><td class=content>Renewal reminder (<30 days dd): -  $FullName</td><td class=content>$MembershipNo</td><td class=content>$statusdesc</td><td class=content>$BasicSub</td><td class=content>$datelastpaid</td><td class=content>$PaymentMethod</td><td class=content>$daysoverdue</td></tr>\n";
				$etext=get_param("subrenew_notificationtext_dd", $db);
				//Search and replace in email templates
				$DeletionDate=date("d F, Y",$secs_now+(37*60*60*24)); 
				$searchwords="AdminName,RenewalDate,DeletionDate,BasicSub,FullName,emailfooter,MembershipNo,login,password";
				$replace = explode (",", $searchwords);
				$maxwords=sizeof ($replace);
				$wordno=0;
				$rawtext=$etext;
				while($wordno<$maxwords){
					$thisword=$replace[$wordno];
					$etext= str_replace("[$thisword]", ${$thisword}, $etext);
					$wordno+=1;
				}


				if($live==1){
					//enter action into member change log (for the benefit of admin or traceability)
					$subject="Subscription";
					$changelogtext="Subscription due, email reminder sent";
					$insert = new \stdClass();
					$insert->MemberID = $userid;
					$insert->Subject = $subject;
					$insert->ChangeDesc = $changelogtext;
					$insert->ChangeDate = $changedate;
					$db->insertObject('tblChangeLog', $insert) or die ("Couldn't update change log");
				}
				if (strpos($memberemail, "@") !== false) {
				//if ($memberemail) {
					if($livemail==1){
						//send the email
						$esubject="DBA Subscription renewal reminder";
						$headers = "From: DBA Membership <$membershipemail>\n";
						$emessage=addslashes($etext);
						mail( $memberemail, $esubject, $emessage, "$headers" ) or print "Could not send mail";
					}

				}else{
					//no email address
					$listing.="<tr><td class=content colspan=7><b>NO EMAIL ADDRESS, LETTER REQUIRED</b></td></tr>\n";		
					$listing.="<tr><td class=content colspan=7>".nl2br($address)."<br><br></td></tr>\n";
				}			

				$listing.="<tr><td class=content colspan=7>".nl2br($etext)."</td></tr>\n";
				$listing.="<tr><td class=content colspan=7><hr></td></tr>\n";
				$reminderdd+=1;
				*/

			}
		}
	}
}
$listing.="</table>";


$htmlheader="<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n<html>\n<head>\n";

$htmlheader.="<style type=\"text/css\"><!--\n.content {\n	font-family: Arial, Helvetica, sans-serif;\n	font-size: 90%;\n	font-style: normal;\n	font-weight: normal;\n}\n-->\n</style>\n";

$htmlheader.="</head>\n";
$htmlheader.="<body>\n";



//update stats added 20161102

$MemTot=0;
$MemLive=0;
$MemNewToday=0;
$MemTermToday=0;
$MemDD=0;
$MemBO=0;
$MemCH=0;
$MemCC=0;
$MemFOC=0;
$MemOrdinary=0;
$MemFamily=0;
$MemHonorary=0;
$MemPress=0;
$MemVoucher=0;
$MemUK=0;
$MemEU=0;
$MemZ1=0;
$MemZ2=0;
$MemOwner=0;
$MemDreamer=0;
$MemCommercial=0;
$MemSitUnknown=0;
$MemTerminated=0;
$MemNew=0;
$MemCruiseGB=0;
$MemCruiseBE=0;
$MemCruiseNL=0;
$MemCruiseFR=0;
$MemCruiseOther=0;
$MonthMemNew=0;
$MonthMemTerminated=0;
$YearMemNew=0;
$YearMemTerminated=0;
$LastYearMemNew=0;
$LastYearMemTerminated=0;


//1 Applied awaiting payment
//2 Paid up
//3 Renewal overdue
//4 Gone away
//5 Terminated
//6 Complimentary

$query = $db->getQuery(true)
	->select('COUNT(*)')
	->from($db->qn('tblMembers'))
	->where($db->qn('DateJoined')." > '0000-00-00 00:00:00'")
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('DateCeased').' IS NULL');
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$MemLive=$num_rows;
}
$query = $db->getQuery(true)
	->select('COUNT(*) AS '.$db->qn('NumberOfRecords'))
	->select('SUM('.$db->qn('BasicSub').') AS '.$db->qn('TotalSubs'))
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('PaymentMethod')." = 'DD'");
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$row = reset($mymembers);
	$TotalSubsDD=$row["TotalSubs"];
	$MemDD=$row["NumberOfRecords"];
	
}
$query = $db->getQuery(true)
	->select('COUNT(*) AS '.$db->qn('NumberOfRecords'))
	->select('SUM('.$db->qn('BasicSub').') AS '.$db->qn('TotalSubs'))
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('PaymentMethod')." = 'SO'");
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$row = reset($mymembers);
	$TotalSubsBO=$row["TotalSubs"];
	$MemBO=$row["NumberOfRecords"];
}
$query = $db->getQuery(true)
	->select('COUNT(*) AS '.$db->qn('NumberOfRecords'))
	->select('SUM('.$db->qn('BasicSub').') AS '.$db->qn('TotalSubs'))
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('PaymentMethod')." = 'CH'");
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$row = reset($mymembers);
	$TotalSubsCH=$row["TotalSubs"];
	$MemCH=$row["NumberOfRecords"];
}
$query = $db->getQuery(true)
	->select('COUNT(*) AS '.$db->qn('NumberOfRecords'))
	->select('SUM('.$db->qn('BasicSub').') AS '.$db->qn('TotalSubs'))
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('PaymentMethod')." = 'CC'");
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$row = reset($mymembers);
	$TotalSubsCC=$row["TotalSubs"];
	$MemCC=$row["NumberOfRecords"];
}
$query = $db->getQuery(true)
	->select('COUNT(*) AS '.$db->qn('NumberOfRecords'))
	->select('SUM('.$db->qn('BasicSub').') AS '.$db->qn('TotalSubs'))
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('PaymentMethod')." = 'FOC'");
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$row = reset($mymembers);
	$TotalSubsFOC=$row["TotalSubs"];
	$MemFOC=$row["NumberOfRecords"];
}
$query = $db->getQuery(true)
	->select('COUNT(*)')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where('('.$db->qn('MemTypeCode').' = 1 OR '.$db->qn('MemTypeCode').' = 3)');
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$MemOrdinary=$num_rows;
}
$query = $db->getQuery(true)
	->select('COUNT(*)')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where('('.$db->qn('MemTypeCode').' = 2 OR '.$db->qn('MemTypeCode').' = 4)');
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$MemFamily=$num_rows;
}
$query = $db->getQuery(true)
	->select('COUNT('.$db->qn('ID').')')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('MemTypeCode').' = 5');
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$MemHonorary=$num_rows;
}
$query = $db->getQuery(true)
	->select('COUNT('.$db->qn('ID').')')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('MemTypeCode').' = 6');
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$MemPress=$num_rows;
}
$query = $db->getQuery(true)
	->select('COUNT('.$db->qn('ID').')')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('MemTypeCode').' = 7');
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$MemVoucher=$num_rows;
}
$query = $db->getQuery(true)
	->select('COUNT(*)')
	->from($db->qn('tblMembers'));
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$MemTot=$num_rows;
}
$query = $db->getQuery(true)
	->select('COUNT('.$db->qn('ID').')')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('PostZone')." = 'UK'");
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$MemUK=$num_rows;
}
$query = $db->getQuery(true)
	->select('COUNT('.$db->qn('ID').')')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('PostZone')." = 'EU'");
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$MemEU=$num_rows;
}
$query = $db->getQuery(true)
	->select('COUNT('.$db->qn('ID').')')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('PostZone')." = 'Z1'");
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$MemZ1=$num_rows;
}
$query = $db->getQuery(true)
	->select('COUNT('.$db->qn('ID').')')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('PostZone')." = 'Z2'");
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$MemZ2=$num_rows;
}
$query = $db->getQuery(true)
	->select('COUNT('.$db->qn('ID').')')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('PostZone')." = 'UK'");
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$MemUK=$num_rows;
}
//Cruising country
$query = $db->getQuery(true)
	->select('COUNT('.$db->qn('ID').')')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('CountryCodeCruising')." = 'GB'")
	->where('('.$db->qn('Situation').' = 4 OR '.$db->qn('Situation').' = 5)');
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$MemCruiseGB=$num_rows;
}
$query = $db->getQuery(true)
	->select('COUNT('.$db->qn('ID').')')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('CountryCodeCruising')." = 'NL'")
	->where('('.$db->qn('Situation').' = 4 OR '.$db->qn('Situation').' = 5)');
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$MemCruiseNL=$num_rows;
}
$query = $db->getQuery(true)
	->select('COUNT('.$db->qn('ID').')')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('CountryCodeCruising')." = 'BE'")
	->where('('.$db->qn('Situation').' = 4 OR '.$db->qn('Situation').' = 5)');
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$MemCruiseBE=$num_rows;
}
$query = $db->getQuery(true)
	->select('COUNT('.$db->qn('ID').')')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('CountryCodeCruising')." = 'FR'")
	->where('('.$db->qn('Situation').' = 4 OR '.$db->qn('Situation').' = 5)');
$num_rows = count($mymembers);
if($num_rows>0){
	$MemCruiseFR=$num_rows;
}

$query = $db->getQuery(true)
	->select('COUNT('.$db->qn('ID').')')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where('('.$db->qn('Situation').' = 4 OR '.$db->qn('Situation').' = 5)');
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$MemOwner=$num_rows;
}
$query = $db->getQuery(true)
	->select('COUNT('.$db->qn('ID').')')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where('('.$db->qn('Situation').' = 1 OR '.$db->qn('Situation').' = 2)');
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$MemDreamer=$num_rows;
}
$query = $db->getQuery(true)
	->select('COUNT('.$db->qn('ID').')')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('Situation').' = 3');
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$MemCommercial=$num_rows;
}
$query = $db->getQuery(true)
	->select('COUNT('.$db->qn('ID').')')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where('('.$db->qn('Situation')." = '' OR ".$db->qn('Situation').' = 0)');
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$MemSitUnknown=$num_rows;
}

//calc monthly todate figures (20161020)
$ThisMonth=date("n");
$ThisYear=date("Y");
$ThisDay=date("j");
$query = $db->getQuery(true)
	->select('COUNT('.$db->qn('ID').')')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where('DAY('.$db->qn('DateJoined').') = '.$db->q($ThisDay))
	->where('MONTH('.$db->qn('DateJoined').') = '.$db->q($ThisMonth))
	->where('YEAR('.$db->qn('DateJoined').') = '.$db->q($ThisYear));
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$MemNewToday=$num_rows;
}
$query = $db->getQuery(true)
	->select('COUNT('.$db->qn('ID').')')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' = 5')
	->where('DAY('.$db->qn('DateCeased').') = '.$db->q($ThisDay))
	->where('MONTH('.$db->qn('DateCeased').') = '.$db->q($ThisMonth))
	->where('YEAR('.$db->qn('DateCeased').') = '.$db->q($ThisYear));
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$MemTermToday=$num_rows;
}
$query = $db->getQuery(true)
	->select('COUNT('.$db->qn('ID').')')
	->from($db->qn('tblMembers'))
	->where('MONTH('.$db->qn('DateJoined').') = '.$db->q($ThisMonth))
	->where('YEAR('.$db->qn('DateJoined').') = '.$db->q($ThisYear));
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$MonthMemNew=$num_rows;
}
$query = $db->getQuery(true)
	->select('COUNT('.$db->qn('ID').')')
	->from($db->qn('tblMembers'))
	->where('MONTH('.$db->qn('DateCeased').') = '.$db->q($ThisMonth))
	->where('YEAR('.$db->qn('DateCeased').') = '.$db->q($ThisYear));
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$MonthMemTerminated=$num_rows;
}

$query = $db->getQuery(true)
	->select('COUNT('.$db->qn('ID').')')
	->from($db->qn('tblMembers'))
	->where('YEAR('.$db->qn('DateJoined').') = '.$db->q($ThisYear));
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$YearMemNew=$num_rows;
}
$query = $db->getQuery(true)
	->select('COUNT('.$db->qn('ID').')')
	->from($db->qn('tblMembers'))
	->where('YEAR('.$db->qn('DateCeased').') = '.$db->q($ThisYear));
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$YearMemTerminated=$num_rows;
}

$LastYear=date("Y")-1;

$query = $db->getQuery(true)
	->select('COUNT('.$db->qn('ID').')')
	->from($db->qn('tblMembers'))
	->where('YEAR('.$db->qn('DateJoined').') = '.$db->q($LastYear));
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$LastYearMemNew=$num_rows;
}
$query = $db->getQuery(true)
	->select('COUNT('.$db->qn('ID').')')
	->from($db->qn('tblMembers'))
	->where('YEAR('.$db->qn('DateCeased').') = '.$db->q($LastYear));
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$LastYearMemTerminated=$num_rows;
}


$today_start=date("Y-m-d 00:00:00");
$today_end=date("Y-m-d 23:59:59");	


$query = $db->getQuery(true)
	->select('COUNT(*)')
	->from($db->qn('tblMembers'))
	->where($db->qn('DateJoined').' >= CURDATE()');
$num_rows = $db->setQuery($query)->loadResult();
if($num_rows>0){
	$MemNew=$num_rows;
}
//$MemTerminated=$overduearchived+$terminatedarchived;
$MemTerminated=$overduearchived;



$StatDate=date("Y-m-d H:i:s");	

$statssummary="<b>STATS SUMMARY ".$StatDate."</b></br>";
$statssummary.="<br>\n";
$statssummary.="<b>".$ThisYear." to date</b></br>\n";
$statssummary.="&nbsp;Members Live ".$MemLive."</br>\n";

$statssummary.="<b>Today</b><br>\n";
$statssummary.="&nbsp;New ".$MemNewToday."</br>\n";
$statssummary.="&nbsp;Terminated ".$MemTermToday."</br>\n";
$statssummary.="&nbsp;Change ".($MemNewToday-$MemTermToday)."</br>\n";

$Netmonthlymemberchange=$MonthMemNew-$MonthMemTerminated;
$Netannualmemberchange=$YearMemNew-$YearMemTerminated;

$LastYearNetannualmemberchange=$LastYearMemNew-$LastYearMemTerminated;

$statssummary.="<style>
td {
	border-left:1px solid black;
	border-top:1px solid black;
	width:40px;
}
table {
	border-right:1px solid black;
	border-bottom:1px solid black;
}
</style>";

//this year
$statssummary.="<br>\n";
$statssummary.="<br><b>Member Status</b>\n";
$YearSpan=6;
$YearTo=date("Y");
$YearFrom=$YearTo-$YearSpan;

$ThisYear=$YearFrom;
$lastmonthtotal=0;
while($ThisYear <= $YearTo){

	$statssummary.="<br><table cellpadding='2' cellspacing='0'>";
	$statssummary.="<tr><td>".$ThisYear."</td><td align='right'>Jan</td><td align='right'>Feb</td><td align='right'>Mar</td><td align='right'>Apr</td><td align='right'>May</td><td align='right'>Jun</td><td align='right'>Jul</td><td align='right'>Aug</td><td align='right'>Sep</td><td align='right'>Oct</td><td align='right'>Nov</td><td align='right'>Dec</td><td>Total</td></tr>";
	$mon=1;
	$memtot_row="";
	$new_row="";
	$term_row="";
	$change_row="";
	$check_row="";
	$term_row_total=0;
	$new_row_total=0;
	
	while($mon < 13){
		$startmonth=($ThisYear)."-".str_pad($mon, 2, '0', STR_PAD_LEFT)."-01";
		$query = $db->getQuery(true)
			->select('COUNT('.$db->qn('ID').')')
			->from($db->qn('tblMembers'))
			->where($db->qn('MemStatus').' != 1')
			->where($db->qn('MemStatus')." != ''")
			->where($db->qn('DateJoined')." > '0000-00-00 00:00:00'")
			->where($db->qn('DateJoined').' <= LAST_DAY('.$db->q($startmonth).')')
			->where('('.$db->qn('DateCeased').' IS NULL OR '.$db->qn('DateCeased').' > LAST_DAY('.$db->q($startmonth).'))');
		$num_total = $db->setQuery($query)->loadResult();
		$memtot_row.="<td align='right'>".$num_total."</td>";
		
		
		$query = $db->getQuery(true)
			->select('COUNT('.$db->qn('ID').')')
			->from($db->qn('tblMembers'))
			->where($db->qn('MemStatus').' != 1')
			->where($db->qn('MemStatus')." != ''")
			->where('MONTH('.$db->qn('DateJoined').') = '.str_pad($mon, 2, '0', STR_PAD_LEFT))
			->where('YEAR('.$db->qn('DateJoined').') = '.$db->q($ThisYear));
		$num_rows_new = $db->setQuery($query)->loadResult();
		$new_row.="<td align='right'>".$num_rows_new."</td>";
		$new_row_total+=$num_rows_new;
		
		$query = $db->getQuery(true)
			->select('COUNT('.$db->qn('ID').')')
			->from($db->qn('tblMembers'))
			->where($db->qn('MemStatus').' = 5')
			->where($db->qn('DateCeased')." != ''")
			->where('MONTH('.$db->qn('DateCeased').') = '.str_pad($mon, 2, '0', STR_PAD_LEFT))
			->where('YEAR('.$db->qn('DateCeased').') = '.$db->q($ThisYear));
		$num_rows_term = $db->setQuery($query)->loadResult();
		$term_row.="<td align='right'>".$num_rows_term."</td>";
		$term_row_total+=$num_rows_term;
		$Debug_sql = $query->__toString()."<br><br>";
		
		$change_row.="<td align='right'>".($num_rows_new-$num_rows_term)."</td>";
		$change_row_total=$new_row_total-$term_row_total;
		if(($lastmonthtotal+($num_rows_new-$num_rows_term))!=$num_total){
			$check_row.="<td align='right' bgcolor='FFFF00'>".(($lastmonthtotal+($num_rows_new-$num_rows_term))-$num_total)."</td>";
		}else{
			$check_row.="<td align='right'>".($lastmonthtotal+($num_rows_new-$num_rows_term))."</td>";
		}
		$lastmonthtotal=$num_total;
		$mon+=1;
	}
	$statssummary.="<tr><td>Total</td>".$memtot_row."<td></td></tr>";
	$statssummary.="<tr><td>New</td>".$new_row."<td><b>".$new_row_total."</b></td></tr>";
	$statssummary.="<tr><td>Terminated</td>".$term_row."<td><b>".$term_row_total."</b></td></tr>";
	$statssummary.="<tr><td>Change</td>".$change_row."<td><b>".$change_row_total."</b></td></tr>";
	$statssummary.="<tr><td>Check</td>".$check_row."<td><b></b></td></tr>";
	$statssummary.="</table>";
	$ThisYear+=1;
}

//Length of membership retention
$Numyears=1;
$NumyearsAv[]=0;
$statssummary.="<br><b>Membership retention years</b> (years before leaving)";

$thisblock="<table cellpadding='2' cellspacing='0'>";
$headeryear=1;
$thisblock.="<tr><td>Leaving Year</td>";
$NumyearsTot = [];
while($headeryear <= 25){
	$thisblock.="<td>$headeryear</td>";
	$headeryear+=1;
	$NumyearsTot[$headeryear]=0;
}
$thisblock.="<td>Total</td></tr>";
$StartYear=2013;
$ThisYear=$StartYear;
$YearTotal=0;
while($ThisYear <= $YearTo){
	$thisblock.="<tr><td>$ThisYear</td>";
	$Numyears=1;
	while($Numyears <= 25){
		$query = $db->getQuery(true)
			->select('COUNT('.$db->qn('ID').')')
			->from($db->qn('tblMembers'))
			->where('Year('.$db->qn('DateCeased').') = '.$db->q($ThisYear))
			->where('Year('.$db->qn('DateCeased').') - Year('.$db->qn('DateJoined').') = '.$db->q($Numyears));
		$num_total = $db->setQuery($query)->loadResult();
		if(!isset($NumyearsTot[$Numyears])) $NumyearsTot[$Numyears] = 0;
		$NumyearsTot[$Numyears]+=$num_total;
		$thisblock.="<td>".($num_total == 0 ? '' : $num_total)."</td>";
		$Numyears+=1;
		$YearTotal+=$num_total;
	}
	$thisblock.="<td>".$YearTotal."</td></tr>";	
	$ThisYear+=1;
	$YearTotal=0;
}
/*add total row
$headeryear=1;
$thisblock.="<tr><td>Total</td>";
while($headeryear <= 25){
	$thisblock.="<td>".($NumyearsTot[$headeryear])."</td>";
	$headeryear+=1;
}
$thisblock.="<td></td></tr>";
*/

//add average row
$headeryear=1;
$thisblock.="<tr><td>Average</td>";
while($headeryear <= 25){
	if(round($NumyearsTot[$headeryear]/($YearTo-$StartYear))==0){
		$thisblock.="<td></td>";
	}else{
		$thisblock.="<td>".round($NumyearsTot[$headeryear]/($YearTo-$StartYear))."</td>";
	}
	$headeryear+=1;
}
$thisblock.="<td></td></tr>";
$thisblock.="</table>";
$statssummary.="<br>".$thisblock."\n";


$TotalSubs=$TotalSubsDD+$TotalSubsBO+$TotalSubsCH+$TotalSubsCC+$TotalSubsFOC;
$statssummary.="<br><br><b>Current membership</b><br>\n";

$statssummary.="<br><b>Payment method</b></br>\n";
$statssummary.="<table cellpadding='2' cellspacing='0'>";
$statssummary.="<tr><td>Direct Debit </td><td align='right'>".$MemDD."
</td><td align='right'>".(round($MemDD/$MemLive*100,1))."%
</td><td align='right'>&pound;".number_format($TotalSubsDD)."
</td><td align='right'>".(round($TotalSubsDD/$TotalSubs*100,1))."%</td></tr>\n";

$statssummary.="<tr><td>Bankers_Order</td><td align='right'>".$MemBO."
</td><td align='right'>".(round($MemBO/$MemLive*100,1))."% 
</td><td align='right'>&pound;".number_format($TotalSubsBO)."
</td><td align='right'>".(round($TotalSubsBO/$TotalSubs*100,1))."%</td></tr>\n";

$statssummary.="<tr><td>Cheque</td><td align='right'>".$MemCH."
</td><td align='right'>".(round($MemCH/$MemLive*100,1))."% 
</td><td align='right'>&pound;".number_format($TotalSubsCH)."
</td><td align='right'>".(round($TotalSubsCH/$TotalSubs*100,1))."%</td></tr>\n";

$statssummary.="<tr><td>Card</td><td align='right'>".$MemCC."
</td><td align='right'>".(round($MemCC/$MemLive*100,1))."% 
</td><td align='right'>&pound;".number_format($TotalSubsCC)."
</td><td align='right'>".(round($TotalSubsCC/$TotalSubs*100,1))."%</td></tr>\n";

$statssummary.="<tr><td>FOC</td><td align='right'>".$MemFOC."
</td><td align='right'>".(round($MemFOC/$MemLive*100,1))."% 
</td><td align='right'>&pound;".number_format($TotalSubsFOC)."
</td><td align='right'>".(round($TotalSubsFOC/$TotalSubs*100,1))."%</td></tr>\n";

$statssummary.="<tr><td>Total </td><td align='right'>".($MemDD+$MemBO+$MemCH+$MemCC+$MemFOC)."
</td><td align='right'></td><td>&pound;".(number_format($TotalSubs))."
</td><td></td></tr>\n";
$statssummary.="</table>\n";

//$statssummary.=$Debug_sql;
$statssummary.="<br><b>Member type</b></br>\n";
$statssummary.="<table cellpadding='2' cellspacing='0'>";
$statssummary.="<tr><td>Single </td><td align='right'>".$MemOrdinary."
</td><td align='right'>".(round($MemOrdinary/$MemLive*100,1))."%</td></tr>\n";

$statssummary.="<tr><td>Family </td><td align='right'>".$MemFamily."
</td><td align='right'>".(round($MemFamily/$MemLive*100,1))."%</td></tr>\n";

$statssummary.="<tr><td>Honorary </td><td align='right'>".$MemHonorary."
</td><td align='right'>".(round($MemHonorary/$MemLive*100,1))."%</td></tr>\n";

$statssummary.="<tr><td>Press </td><td align='right'>".$MemPress."
</td><td align='right'>".(round($MemPress/$MemLive*100,1))."%</td></tr>\n";

$statssummary.="<tr><td>Voucher </td><td align='right'>".$MemVoucher."
</td><td align='right'>".(round($MemVoucher/$MemLive*100,1))."%</td></tr>\n";

$statssummary.="<tr><td>Total</td><td align='right'>".($MemOrdinary+$MemFamily+$MemHonorary+$MemPress+$MemVoucher)."
</td><td></td></tr>\n";
$statssummary.="</table>\n";


$statssummary.="<br><b>Address location</b></br>\n";
$statssummary.="<table cellpadding='2' cellspacing='0'>";
$statssummary.="<tr><td>UK</td><td align='right'>".$MemUK."</td><td align='right'>".(round($MemUK/$MemLive*100,1))."%</td>";
$statssummary.="<tr><td>EU_not_UK </td><td align='right'>".$MemEU."</td><td align='right'>".(round($MemEU/$MemLive*100,1))."%</td>";
$statssummary.="<tr><td>Z1</td><td align='right'>".$MemZ1."</td><td align='right'>".(round($MemZ1/$MemLive*100,1))."%</td>";
$statssummary.="<tr><td>Z2</td><td align='right'>".$MemZ2."</td><td align='right'>".(round($MemZ2/$MemLive*100,1))."%</td>";
$statssummary.="<tr><td>Total</td><td align='right'>".($MemUK+$MemEU+$MemZ1+$MemZ2)."</td><td align='right'></td>";
$statssummary.="</td></tr>\n";
$statssummary.="</table>\n";

//values(\"$MemTot\", \"$MemLive\", \"$MemDD\", \"$MemBO\", \"$MemCH\", \"$MemCC\", \"$MemFOC\", \"$MemOrdinary\", \"$MemFamily\", \"$MemHonorary\", \"$MemPress\", \"$MemVoucher\", \"$MemUK\", \"$MemEU\", \"$MemZ1\","$MemZ2\",\"$MemOwner\",\"$MemDreamer\",\"$MemCommercial\",\"$MemSitUnknown\",\"$YearMemTerminated\",\"$YearMemNew\",\"$GuideRequests\",\"$ClassifiedPosts\",\"$ClassifiedAlerts\",\"$StatDate\")";
$statssummary.="<br><b>Situation</b></br>\n";
$statssummary.="<table cellpadding='2' cellspacing='0'>";
$statssummary.="<tr><td>Owner</td><td align='right'>".$MemOwner."</td><td align='right'>".(round($MemOwner/$MemLive*100,1))."%</td>";
$statssummary.="<tr><td>Dreamer</td><td align='right'>".$MemDreamer."</td><td align='right'>".(round($MemDreamer/$MemLive*100,1))."%</td>";
$statssummary.="<tr><td>Commercial</td><td align='right'>".$MemCommercial."</td><td align='right'>".(round($MemCommercial/$MemLive*100,1))."%</td>";
$statssummary.="<tr><td>Unknown</td><td align='right'>".$MemSitUnknown."</td><td align='right'>".(round($MemSitUnknown/$MemLive*100,1))."%</td>";
$statssummary.="<tr><td>Total</td><td align='right'>".($MemOwner+$MemDreamer+$MemCommercial+$MemSitUnknown)."</td><td align='right'></td>";
$statssummary.="</td></tr>\n";
$statssummary.="</table>\n";

$MemCruiseOther=$MemOwner-($MemCruiseGB+$MemCruiseBE+$MemCruiseNL+$MemCruiseFR);
$statssummary.="<br><b>Boat location</b></br>\n";
$statssummary.="<table cellpadding='2' cellspacing='0'>";
$statssummary.="<tr><td>GB</td><td align='right'>".$MemCruiseGB."</td><td align='right'>".(round($MemCruiseGB/$MemOwner*100,1))."%</td>";
$statssummary.="<tr><td>Non_GB</td><td align='right'>".($MemCruiseBE+$MemCruiseNL+$MemCruiseFR+$MemCruiseOther)."</td><td align='right'>".(round(($MemCruiseBE+$MemCruiseNL+$MemCruiseFR+$MemCruiseOther)/$MemOwner*100,1))."%</td>";
$statssummary.="<tr><td>BE</td><td align='right'>".$MemCruiseBE."</td><td align='right'>".(round($MemCruiseBE/$MemOwner*100,1))."%</td>";
$statssummary.="<tr><td>NL</td><td align='right'>".$MemCruiseNL."</td><td align='right'>".(round($MemCruiseNL/$MemOwner*100,1))."%</td>";
$statssummary.="<tr><td>FR</td><td align='right'>".$MemCruiseFR."</td><td align='right'>".(round($MemCruiseFR/$MemOwner*100,1))."%</td>";
$statssummary.="<tr><td>Other</td><td align='right'>".$MemCruiseOther."</td><td align='right'>".(round($MemCruiseOther/$MemOwner*100,1))."%</td>";
$statssummary.="<tr><td>Total</td><td align='right'>".($MemCruiseGB+$MemCruiseBE+$MemCruiseNL+$MemCruiseFR+$MemCruiseOther)."</td><td align='right'></td>";
$statssummary.="</td></tr>\n";
$statssummary.="</table>\n";




$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblStats'))
	->where($db->qn('StatDate').' BETWEEN '.$db->q($today_start).' AND '.$db->q($today_end));
$result = $db->setQuery($query)->loadAssocList();
$num_rows = count($result);
if($num_rows>0){
	//udate existing entry with members data
	$row = reset($result);
	$StatDate=$row["StatDate"];
	$update = new \stdClass();
	$update->MemTot = $MemTot;
	$update->MemLive = $MemLive;
	$update->MemDD = $MemDD;
	$update->MemBO = $MemBO;
	$update->MemCH = $MemCH;
	$update->MemCC = $MemCC;
	$update->MemFOC = $MemFOC;
	$update->MemOrdinary = $MemOrdinary;
	$update->MemFamily = $MemFamily;
	$update->MemHonorary = $MemHonorary;
	$update->MemPress = $MemPress;
	$update->MemVoucher = $MemVoucher;
	$update->MemUK = $MemUK;
	$update->MemEU = $MemEU;
	$update->MemZ = $MemZ1;
	$update->MemZ = $MemZ2;
	$update->MemOwner = $MemOwner;
	$update->MemDreamer = $MemDreamer;
	$update->MemCommercial = $MemCommercial;
	$update->MemSitUnknown = $MemSitUnknown;
	$update->MemCruiseGB = $MemCruiseGB;
	$update->MemCruiseBE = $MemCruiseBE;
	$update->MemCruiseNL = $MemCruiseNL;
	$update->MemCruiseFR = $MemCruiseFR;
	$update->MemCruiseOther = $MemCruiseOther;
	$update->MemTerminated = $MemTermToday;
	$update->MemNew = $MemNewToday;
	if(isset($GuideRequests)) $update->GuideRequests = $GuideRequests;
	$update->StatDate = $StatDate;
	$db->updateObject('tblStats', $update, 'StatDate') or die ("Couldn't update stats");			
}else{
	//add new entry
	$ClassifiedPosts=0;
	$ClassifiedAlerts=0;
	$insert = new \stdClass();
	$insert->MemTot = $MemTot;
	$insert->MemLive = $MemLive;
	$insert->MemDD = $MemDD;
	$insert->MemBO = $MemBO;
	$insert->MemCH = $MemCH;
	$insert->MemCC = $MemCC;
	$insert->MemFOC = $MemFOC;
	$insert->MemOrdinary = $MemOrdinary;
	$insert->MemFamily = $MemFamily;
	$insert->MemHonorary = $MemHonorary;
	$insert->MemPress = $MemPress;
	$insert->MemVoucher  = $MemVoucher;
	$insert->MemUK = $MemUK;
	$insert->MemEU = $MemEU;
	$insert->MemZ1 = $MemZ1;
	$insert->MemZ2 = $MemZ2;
	$insert->MemOwner = $MemOwner;
	$insert->MemDreamer = $MemDreamer;
	$insert->MemCommercial = $MemCommercial;
	$insert->MemSitUnknown = $MemSitUnknown;
	$insert->MemCruiseGB = $MemCruiseGB;
	$insert->MemCruiseBE = $MemCruiseBE;
	$insert->MemCruiseNL = $MemCruiseNL;
	$insert->MemCruiseFR = $MemCruiseFR;
	$insert->MemCruiseOther = $MemCruiseOther;
	$insert->MemTerminated = $MemTermToday;
	$insert->MemNew = $MemNewToday;
	if(isset($GuideRequests)) $insert->GuideRequests = $GuideRequests;
	$insert->ClassifiedPosts = $ClassifiedPosts;
	$insert->ClassifiedAlerts = $ClassifiedAlerts;
	$insert->StatDate = $StatDate;
	$update = $db->insertObject('tblStats', $insert);
	if(!$update){
		echo("Couldn't insert stats $query");
	}
}



$subject="Daily automated membership renewal report $sitename";
//confirm emails
//$content=$message."\n\n".$emailfooter;
$content="$htmlheader <div class=content>";
if($live==1){
	$content.="<h2>DAILY CRON (LIVE MODE)</h2><br><hr>";
}else{
	$content.="<h2>DAILY CRON (SIMULATION MODE - php".phpversion().")</h2><br><hr>";
}

$content.=$statssummary. "<br><hr>
<b>Daily membership report</b>
<br><br>Status check on $num_members records containing $MemLive 'live' members (Paid up, Renewal overdue, Gone away, Complimentary)
<br><br>EMAIL NOTIFICATION SENT TO:
<br>New joiners not paid after 42 days: $newjoinernotpaid
<br>Sub overdue 7 days final reminder ch and cc: $overduefinalreminder
<br>Sub reminder 42 days to renewal cheque payer: $reminderch 
<br>Sub reminder 42 days to renewal card payer: $remindercc
<br>Sub reminder 30 days to renewal standing order payer: $reminderso
<br>Sub reminder 30 days to renewal direct debit payer: $reminderdd
<br><br>ACTIONS ON MEMBER RECORDS:
<br>Sub overdue 42 days so archived & deleted: $overduearchived
<br>Terminated 42 days ago so archived & deleted: $terminatedarchived
<br>$terminations<br><hr>
";


$content.=$listing;

$content.="</div></body>\n";
$content.="</html>\n";

if($livemailadmin==1){
	if($mailOn) {
		$mailer = Factory::getMailer();	
		$mailer->setSender([$config->get('mailfrom'), 'DBA Membership Auto Administration']);
		$mailer->addRecipient($membershipemail);
		// $mailer->addRecipient($webmasteremail);
		$mailer->addRecipient('dbawebsite@barges.org');
		$mailer->addRecipient('treasurer@barges.org');
		$mailer->addReplyTo($registrationemail);
		$mailer->setSubject($subject);
		$mailer->setBody(nl2br($content));
		$mailer->isHtml(true);
		$mailer->Send();
	} else echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";
	//echo("email sent to admins");
}

echo($content);

// file_put_contents(__FILE__.'.lastrun', strtotime('now'));
file_put_contents(__FILE__.'.last-run.log', date('c'));