<?php


/*
To do ................................

*/


/*
Updates ................................
20140407 Email no longer directly sent from this script but added to mail queue table for bact sending on minute cron
20140302 Classified section removed to seperate cron in order to limit crash to member cron
20101209 Renewal reminder emails password removed 
20100405 Monthly and year to date stats on new and terminated members added, email to treasurer
20090206 Stats tblStats created to store daily stats for reporting.
Daily member totals will be added here to track member trends

20090402 $live=1 added and email senders prior to going live
*/

//cron command   /usr/local/bin/php -q /home/bargesor/public_html/components/com_membership/cron/cron.php


//load Joomla helpers for emailsending
define( '_JEXEC', 1 );
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', "/home/customer/www/barges.org/public_html");
define('JPATH_COMPONENT', JPATH_BASE .DS.'components'.DS.'com_membership');
require_once(JPATH_BASE .DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE .DS.'includes'.DS.'framework.php');
require_once(JPATH_BASE .DS.'libraries/joomla/user/helper.php');
require_once(JPATH_BASE .DS.'libraries/joomla/factory.php' );
require_once(JPATH_COMPONENT .DS.'common.php');

$db =JFactory::getDBO();



//simulate or live
$live=1;
$livemail=1;
$livemailadmin=1;

$htmlheader.="<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n<html>\n<head>\n";

$htmlheader.="<style type=\"text/css\"><!--\n.content {\n	font-family: Arial, Helvetica, sans-serif;\n	font-size: 90%;\n	font-style: normal;\n	font-weight: normal;\n}\n-->\n</style>\n";

$htmlheader.="</head>\n";
$htmlheader.="<body>\n";

function get_parameter($ParameterName){
   $result = mysql_query("SELECT ParameterValue FROM tblParameters WHERE ParameterName='$ParameterName'");
	$row = mysql_fetch_array($result);
	$parameter=$row["ParameterValue"];
	return $parameter;
}
 

$datenow = date("Y-m-d");


//check membership renewals ---------------------------------------------------------------

$listing.="<table>";
$message="";
$listing.="<tr><td class=content><b>Last Name</b></td><td class=content><b>Mem No</b></td><td class=content><b>Current Status</b></td><td class=content><b>Sub</b></td><td class=content><b>Date Paid</b></td><td class=content><b>Method</b></td><td class=content><b>Days Overdue</b></td></tr>\n";
$secs_now = time();
//$secs_now = time()-(2*60*60);
$secsinayear=31536000;
$changedate=date("Y-m-d H:i:s");		
$thedate=date("d M Y");	
//check current 'live' members
//2=Paid Up 6=Complimentary

//check subs renewal and action as required
$mymembers = mysql_query("SELECT * FROM tblMembers where ID=199");
$num_rows=mysql_num_rows($mymembers);
if($num_rows==0){
	$screenmessage="Can't find members." ;
}else{
	$num_members=mysql_num_rows($mymembers);
	$key = "dba";
	$done=0;
	$thisid=0;
	$AdminName=get_parameter("AdminName");
	while($row = mysql_fetch_array($mymembers)){
			
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
		if($Title && $FullName){
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
		//subpayfirst_notificationtext_final_cc_so_dd_ch
		//$etext=get_parameter("subrenew_notificationtext_final_cc_so_dd_ch");
		$etext=get_parameter("subrenew_notificationtext_ch");
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
				$esubject="This doesnt";
				$emessage="Dodgy - ".addslashes($etext);
				//save message to log tblMailHistoryMessageLog
				$query = "INSERT INTO tblMailHistoryMessageLog (Subject, Message, SenderEmail, SenderName, Queued)
				values(\"$esubject\", \"$emessage\", \"$membershipemail\", \"DBA Membership auto administration\", \"$changedate\")";
				$db->setQuery($query);
				$db->query();
				$messageid = $db->insertid();
				
				echo("<br><br>Message: ".$emessage."<br><br>Query: ".$query."<br><br>MID: ".$messageid);	
				//add record to email pending table
				$query = "INSERT INTO  tblMailHistoryRecipientLog (MessageID, MemberID, MemberName, MemberEmail, Queued)
				values(\"$messageid\", \"$userid\", \"$FullName\", \"$memberemail\", \"$changedate\")";
				$db->setQuery($query);
				$db->query();
				//JUtility::sendMail ($membershipemail, "DBA Membership", $memberemail, $esubject, $emessage);
			}
		}else{
			//no email address
			$listing.="<tr><td class=content colspan=7><b>NO EMAIL ADDRESS, LETTER REQUIRED</b></td></tr>\n";		
			$listing.="<tr><td class=content colspan=7>".nl2br($address)."<br><br></td></tr>\n";
		}
		
		
		
		
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

		
		if (strpos($memberemail, "@") !== false) {
			if($livemail==1){
				//send the email
				$esubject="This works";
				$emessage="OK - ".addslashes($etext);
				//save message to log tblMailHistoryMessageLog
				$query = "INSERT INTO tblMailHistoryMessageLog (Subject, Message, SenderEmail, SenderName, Queued)
				values(\"$esubject\", \"$emessage\", \"$membershipemail\", \"DBA Membership auto administration\", \"$changedate\")";
				$db->setQuery($query);
				$db->query();
				$messageid = $db->insertid();	
				echo("<br><br><br>Message: ".$emessage."<br><br>Query: ".$query."<br><br>MID: ".$messageid);

				//add record to email pending table
				$query = "INSERT INTO  tblMailHistoryRecipientLog (MessageID, MemberID, MemberName, MemberEmail, Queued)
				values(\"$messageid\", \"$userid\", \"$FullName\", \"$memberemail\", \"$changedate\")";
				$db->setQuery($query);
				$db->query();
				//JUtility::sendMail ($membershipemail, "DBA Membership", $memberemail, $esubject, $emessage);
			}
		}else{
			//no email address
			$listing.="<tr><td class=content colspan=7><b>NO EMAIL ADDRESS, LETTER REQUIRED</b></td></tr>\n";		
			//$listing.="<tr><td class=content colspan=7>".nl2br($address)."<br><br></td></tr>\n";
		}						
	}
}
		
		
		





echo("<br>".$listing);

?>
