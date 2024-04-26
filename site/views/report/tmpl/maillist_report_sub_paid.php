<?php
$login_email= $_SESSION["login_email"];
$login_name= $_SESSION["login_name"];
$wheresql = stripslashes($_SESSION["wheresql"]);
$sort = $_SESSION["sort"];
require_once("../../../commonV3.php");
getpost_ifset(array('login_email','login_name','table','wheresql','maillist','subject','message','send','sort'));
?>
<html>
<head>
<title>Mark subscription paid</title>
<SCRIPT LANGUAGE="JavaScript">
function closeme() {
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
<form name="form" method="post" action="">
<div class="pop_page_title"><h2>Mark subscription paid</h2></div>
<div class="pop_page_buttons"><a href="javascript:closeme()"><img src="../../../images/close.gif" width="18" height="18" alt="Close this window and return to search entry" border="0"><br>
        </a><a href="javascript:printWindow()"><img src="../../../images/print.gif" width="18" height="18" alt="Print this page" border="0"></a></div>
<table border="0" cellpadding="2" width="100%">
  <tr>
    <td colspan='2'> 
        
<h2>Note message</h2></td>
    </tr>
<tr><td colspan="2"> 
<?
//check if we have a list
if($maillist=="_"){
	//assume direct from wheresql bypassing select list so create $maillist
	$query = $db->getQuery(true)
		->select($db->qn('ID'))
		->from($db->qn($memtable))
		->where($wheresql)
		->order($sort);
	$result = $db->setQuery($query)->loadAssocList();
		if (!$result) {
			echo("<P>Error finding members $sql</P>");
			//exit();
		}
	$num_rows = count($result);
	$maillist="";
	foreach($result as $row) {
		$maillist.="_".$row["ID"];
	}
	$maillist.="_";
}
$query = $db->getQuery(true)
	->select($db->qn('ID'))
	->from($db->qn('tblMembers'));
$records = $db->setQuery($query)->loadAssocList();
if (!$records) {
	echo("<P>Error finding members</P>");
	exit();
}
$num_records = count($records);
$thisdate=date("d M Y");
$Title="Target listing - $thisdate" ;
$maxrecords = 10000;
$members = explode ("_", $maillist);
$maxmembers=sizeof ($members)-2;
echo("<b>" . $Title . "</b><br><br>".$maxmembers. " selected for marking as paid from $num_records members. <br><br>- The renewal date will be 12 months from the last one or the joining date if a new member first payment<br>- Status will be 'Paid up'<br><br>\n");
//save variables for later
echo("<input type='hidden' name='wheresql' value=\"".$wheresql."\">\n");
echo("<input type='hidden' name='contactid' value=\"".(isset($contactid) ? $contactid : '')."\">\n");
echo("<input type='hidden' name='sort' value=\"".$sort."\">\n");
echo("<input type='hidden' name='criteria' value=\"".(isset($criteria) ? $criteria : '')."\">\n");
echo("<input type='hidden' name='maillist' value=\"".$maillist."\">\n");
//records found so ok to do email
if(!$note){
	echo("<font color=#ff0000><b>Please enter a note to append to the member record notes field</b></font><br>"); 
}else{
	//do update
	$changedate=date("Y-m-d H:i:s");
	$members = explode ("_", $maillist);
	$thismemberid=0;
	$maxmembers=sizeof ($members);
	$update_no=1;
	$thismessage=stripslashes($sendmessage);
	$thissubject=stripslashes($subject);
	while($update_no<$maxmembers){
		$thismemberid=$members[$update_no];
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('tblMembers'))
			->where($db->qn('ID').' = '.$db->q($thismemberid));
		$memberinfo = $db->setQuery($query)->loadAssocList();
		if (!$memberinfo) {
			break;
		}
		$row = reset($memberinfo);
		$memberid=$row["ID"];
		$MembershipNo=$row["MembershipNo"];
		$NotesAdmin=$row["NotesAdmin"];
		$DatePaid=$row["DatePaid"];
		$DateJoined=$row["DateJoined"];
		$PaymentMethod=$row["PaymentMethod"];
		$MemStatus=$row["MemStatus"];
		switch ($MemStatus) {
			case "":
				$statusdesc="Unknown";
				break;
			case "1":
				$statusdesc="Applied pending payment";
				$NewMemStatus=2;
				break;
			case "2":
				$statusdesc="Paid up";
				$NewMemStatus=2;
				break;
			case "3":
				$statusdesc="Renewal overdue";
				$NewMemStatus=2;
				break;
			case "4":
				$statusdesc="Gone away";
				$NewMemStatus=4;
				break;			
			case "5":
				$statusdesc="Terminated";
				$NewMemStatus=2;
				break;
			case "6":
				$statusdesc="Complimentary";
				$NewMemStatus=6;
				break;
		}
		$contactname="";
		$contactname=$row["FirstName"];
		if($row["FirstName"] && $contactname){
			$contactname.=" ".$row["LastName"];
		}else{
			$contactname=$row["LastName"];
		}
		if($DatePaid!="0000-00-00 00:00:00"){
			list ($myDate, $myTime) = explode (' ', $row["DatePaid"]);
			list ($myyear, $mymonth, $myday) = explode ('-', $myDate);
			list ($myhour, $mymin, $mysec) = explode ('-', $myTime);
			if($myyear){
				$datelastpaid="$myyear-$mymonth-$myday";
			}else{
				$datelastpaid="blank - joined $DateJoined";
			}
			$renewyear=$myyear+1;
			$RenewalDate=$renewyear."-".$mymonth."-".$myday." ".$myTime;
			$expireyear=$renewyear+1;
			$ExpiryDate=$myday."-".$mymonth."-".$expireyear;
		
		}else{
			//new member first payment
			$RenewalDate=$DateJoined;
		}	
	
		//add the note
		$newnote=$NotesAdmin."\n".$note;
		$update = new \stdClass();
		$update->MemStatus = $NewMemStatus;
		$update->NotesAdmin = addslashes($newnote);
		$update->DatePaid = $RenewalDate;
		$update->LastUpdate = $changedate;
		$update->ID = $memberid;
		$db->updateObject('tblMembers', $update, 'ID') or die ("Couldn't update record ".print_r($update, true));		
	
		//enter action into member change log (for the benefit of admin or traceability)
		$subject="Subscription";
		$changelogtext="Payment confirmed renewed until ".$ExpiryDate;
		$insert = new \stdClass();
		$insert->MemberID = $memberid;
		$insert->Subject = $subject;
		$insert->ChangeDesc = $changelogtext;
		$insert->ChangeDate = $changedate;
		$db->insertObject('tblChangeLog', $insert) or die ("Couldn't update change log");	
		$donelist.=$MembershipNo."  ".$PaymentMethod." DatePaid:".$DatePaid." -> ".$RenewalDate." Status:".$MemStatus ." -> ".$NewMemStatus."  ".$contactname."<br>\n";
		$update_no+=1;
	} 
	echo("<br>Summary: The following have been marked as payment received: <br><br>$donelist");
}
if(!$note){
	//default note
	//get initials of admin
	// $initials = $login_FirstName[0].$login_LastName[0];
	$initials = preg_match_all('/(?<=\s|^)\w/iu', $login_name, $matches);
	$note="Payment received ".date("d/m/Y")." ".strtoupper(implode('', $matches[0]));
	echo("<table border='0' cellspacing='1' cellpadding='4'>");
	echo("<tr><td><b>Action</b></td>");
	echo("<td><input type='submit' name='send' value='Update'></td></tr>");
	
	echo("<tr><td>Note:</td>");
	echo("<td><input type='text' name='note' size='60' value=\"".stripslashes($note)."\"></td></tr>");
	echo("</table>");
}
?> 
      </td>
    </tr>
  </table>
</form>
</body>
</html>
