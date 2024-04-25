<?php
$login_email= $_SESSION["login_email"];
$login_name= $_SESSION["login_name"];
$wheresql = stripslashes($_SESSION["wheresql"]);
$sort = $_SESSION["sort"];
$live=1;
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
getpost_ifset(array('login_email','login_name','message','subject','from','send','table','wheresql','maillist','criteria','status','sort','recipients','contactselected','go'));
$db = Factory::getDBO();
?>
<html>
<head>
<title>Member deletion</title>
<SCRIPT LANGUAGE="JavaScript">
function closeme() {
window.opener.location.reload();
window.close(self);
}
function printWindow(){
   bV = parseInt(navigator.appVersion)
   if (bV >= 4) window.print()
}
</script>
</head>
<link href="../../../style.css" rel="stylesheet" type="text/css">
<body bgcolor="#FFFFFF">
<form name="form" method="post" action="maillist_member_delete.php">
<div class="pop_page_title"><h2>Delete member records</h2></div>
<div class="pop_page_buttons"><a href="javascript:closeme()"><img src="../../../images/close.gif" width="18" height="18" alt="Close this window and return to search entry" border="0"><br>
        </a><a href="javascript:printWindow()"><img src="../../../images/print.gif" width="18" height="18" alt="Print this page" border="0"></a></div>
	
<table border="0" cellpadding="2" bgcolor="#FFFFFF" width="100%">
  
<tr><td colspan="2"> 
<?php
if(!$login_email){
	//cant do as no admin email to send from
	echo("An administrator email address was not found. Please check that you are logged in as an administrator and that your profile email address is valid");
	//exit();	
}else{
	$from=$login_email;
	if(isset($table) && $table=="archive"){
		$memtable="tblMembers_archive";
	}else{
		$memtable="tblMembers";
	}
	//check if we have a list
	if($maillist=="_"){
		//assume direct from wheresql bypassing select list so create $maillist
		$query = $db->getQuery(true)
			->select($db->qn('ID'))
			->from($db->qn($memtable))
			->where($wheresql)
			->order($sort);
		if ($this_maillist = $db->setQuery($query)->loadObjectList ()) {
			for ($i=0; $i<count($this_maillist); $i++) {
				$maillist.=$this_maillist[$i]->ID."_";
			}
		}
	}	
	$thisdate=date("d M Y");
	$Title="Target listing - $thisdate" ;
	$maxrecords = 10000;
	$members = explode ("_", $maillist);
	$maxmembers=sizeof ($members)-2;
	
	
	//save variables for later
	echo("<input type='hidden' name='wheresql' value=\"".$wheresql."\">\n");
	echo("<input type='hidden' name='contactid' value=\"".(isset($contactid) ? $contactid : '')."\">\n");
	echo("<input type='hidden' name='sort' value=\"".$sort."\">\n");
	echo("<input type='hidden' name='criteria' value=\"".(isset($criteria) ? $criteria : '')."\">\n");
	echo("<input type='hidden' name='maillist' value=\"".$maillist."\">\n");
	echo("<input type='hidden' name='login_email' value=\"".$login_email."\">\n");
	//records found so ok to do email
	
	$recipients="B"; //delete both members if existing
	
	if(!empty($go) && $go == "Go"){
	
		//dO delete
		
		
		//set_time_limit(0);
		$addresslist="";
		$deletions=0;
		$emaildups=0;
		$headers = "From: ".$from."\n";
		$members = explode ("_", $maillist);
		$thismemberid=0;
		$maxmembers=sizeof ($members)-2;;
		$emailno=1;
		$notqueued=0;
		$queuedok=0;
		
		
		while($emailno<=$maxmembers){
			$thismemberid=$members[$emailno];
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn($memtable))
				->where($db->qn('ID').' = '.$db->q($thismemberid));
			if ($this_member = $db->setQuery($query)->loadObject ()) {
				$memberid=$this_member->ID;
				$memberid2=$this_member->ID2;
				$MembershipNo=$this_member->MembershipNo;
				if($recipients=="B" || $recipients=="M"){
					
					
					//check and transfer any barge register to unknown
					//check if exists and transfer any vessel register entry to 'unknown keeper'
					//get the vessel(s)
					$query = $db->getQuery(true)
						->select('*')
						->from($db->qn('tblAssetsMembers'))
						->where($db->qn('MembershipNo').' = '.$db->q($MembershipNo));
					$vessels = $db->setQuery($query)->loadAssocList();
					if ($vessels){
						$vrows = count($vessels);
						foreach($vessels as $vrow) {
							$vlisting="";
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
								->select('*')
								->from($db->qn('tblAssets'))
								->where($db->qn('VesselID').' = '.$db->q($VesselID))
								->order($db->qn('AssetCategory'));
							$vname = $db->setQuery($query)->loadAssocList();
							if (!$vname) {
								echo("Can't find assets ".$query->__toString());
								$num_vessels=0;
								//exit();
							}else{
								$num_vessels = count($vname);
							}
							# If the search was unsuccessful then Display Message try again.
							if ($num_vessels) {
								foreach($vname as $arow) {
									//Go through rows to find name asset category = l
									$AssetID = stripslashes($arow["AssetID"]);
									$AssetCategory = stripslashes($arow["AssetCategory"]);
									$AssetCategoryDesc = stripslashes($arow["AssetCategoryDesc"]);				
									$AssetTitle = stripslashes($arow["AssetTitle"]);
									if($AssetCategory==1){
										$vesselname=$AssetTitle;
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
								$insert->MemberID = $thismemberid;
								$insert->Subject = $subject;
								$insert->ChangeDesc = $changelogtext;
								$insert->ChangeDate = $datenow;
								$db->insertObject('tblChangeLog', $insert) or die ("Couldn't update change log");	
							}
							$vlisting.="Vessel '$vesselname' detached from this profile";
						}
					}
					
					
					
					
					//delete main
					$contactname="";
					$contactname=$this_member->FirstName;
					if($this_member->FirstName && $contactname){
						$contactname.=" ".$this_member->LastName;
					}else{
						$contactname=$this_member->LastName;
					}
					if($live==1){			
						$query = $db->getQuery(true)
							->delete($db->qn($memtable))
							->where($db->qn('ID').' = '.$db->q($thismemberid));
						$db->setQuery($query)->execute() or die ("Couldn't delete member from tblMembers");
						$query = $db->getQuery(true)
							->delete($db->qn('#__users'))
							->where($db->qn('id').' = '.$db->q($thismemberid));
						$db->setQuery($query)->execute() or die ("Couldn't delete member from #__users");
						$query = $db->getQuery(true)
							->delete($db->qn('#__user_profiles'))
							->where($db->qn('user_id').' = '.$db->q($thismemberid));
						$db->setQuery($query)->execute() or die ("Couldn't delete member from #__user_profiles");
						$query = $db->getQuery(true)
							->delete($db->qn('#__user_usergroup_map'))
							->where($db->qn('user_id').' = '.$db->q($thismemberid));
						$db->setQuery($query)->execute() or die ("Couldn't delete member from #__user_usergroup_map");
						$query = $db->getQuery(true)
							->delete($db->qn('#__user_notes'))
							->where($db->qn('user_id').' = '.$db->q($thismemberid));
						$db->setQuery($query)->execute() or die ("Couldn't delete member from #__user_notes");
					}
					
					$addresslist.="|".$emailaddress;
					$deletions+=1;
					if($deletedlist){
						$deletedlist=$deletedlist."\n";
					}
					$deletedlist=$deletedlist.$MembershipNo." ".$contactname." ".$emailaddress;
					$queuedok+=1;
			
				}
				
				if($recipients=="B" && $memberid2){
					//delete second member
					$contactname="";
					$contactname=$this_member->FirstName2;
					if($this_member->FirstName2 && $contactname){
						$contactname.=" ".$this_member->LastName2;
					}else{
						$contactname=$this_member->LastName2;
					}
					if($live==1){			
						$query = $db->getQuery(true)
							->delete($db->qn($memtable))
							->where($db->qn('ID').' = '.$db->q($thismemberid2));
						$db->setQuery($query)->execute() or die ("Couldn't delete member from tblMembers");
						$query = $db->getQuery(true)
							->delete($db->qn('#__users'))
							->where($db->qn('id').' = '.$db->q($thismemberid2));
						$db->setQuery($query)->execute() or die ("Couldn't delete member from #__users");
						$query = $db->getQuery(true)
							->delete($db->qn('#__user_profiles'))
							->where($db->qn('user_id').' = '.$db->q($thismemberid2));
						$db->setQuery($query)->execute() or die ("Couldn't delete member from #__user_profiles");
						$query = $db->getQuery(true)
							->delete($db->qn('#__user_usergroup_map'))
							->where($db->qn('user_id').' = '.$db->q($thismemberid2));
						$db->setQuery($query)->execute() or die ("Couldn't delete member from #__user_usergroup_map");
						$query = $db->getQuery(true)
							->delete($db->qn('#__user_notes'))
							->where($db->qn('user_id').' = '.$db->q($thismemberid2));
						$db->setQuery($query)->execute() or die ("Couldn't delete member from #__user_notes");
					}
					//add it
					$addresslist.="|".$emailaddress;
					$deletions+=1;
							
					if($deletedlist){
						$deletedlist=$deletedlist."\n";
					}
					$deletedlist=$deletedlist.$MembershipNo." ".$contactname." ".$emailaddress." (second member)";
					$queuedok+=1;
				}
			}
			$emailno+=1;
		} 
		echo("<b>$deletions deletion(s) made including any second members.<br>A report with member list has been emailed to $from");
		//email message to sender for the record
		if($missedlist==""){
			$missedlist="none";
		}
		
		$adminmessage="This is a copy for your records member(s) deleted\n\nSender: ".$from."\n\nSelection criteria: ".(isset($criteria) ? $criteria : '')."\n\nDeletion(s):\n".$deletedlist."\n";
		$fromname="DBA Administration";
		$recipient=$from;
		$subject="DBA Membership deletion confirmation report";
		$body=$adminmessage;
		if($mailOn) {
			$mailer = Factory::getMailer();		
			$mailer->setSender([$config->get('mailfrom'), $config->get('fromname')]);
			$mailer->addRecipient($recipient);
			$mailer->addReplyTo($from, $fromname);
			$mailer->setSubject($subject);
			$mailer->setBody(nl2br($body));
			$mailer->isHtml(true);
			$mailer->Send();
		} else echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";
		
	}else{
	
 		
		if($maxmembers==1){
			echo("<b>" . $Title . "</b><br><br>".$maxmembers. " record has been selected for deleting<br>\n");
		}else{
			echo("<b>" . $Title . "</b><br><br>".$maxmembers. " records have been selected for deleting<br>\n");	
		}
		echo("<table border='0' cellspacing='1' cellpadding='4'>");
		echo("<tr><td><b>Click 'Go' to proceed (warning this cannot be reversed)</b></td>");
		echo("<td><input type='submit' name='go' value='Go'></td><td>&nbsp;</td></tr>");
		
		echo("</table>");
	}
}
?> 
      </td>
    </tr>
  </table>
</form>
</body>
</html>
