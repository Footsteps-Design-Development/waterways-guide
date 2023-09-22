<?php
require_once("../commonV3.php");
$loginstate=check_login("admin");
if($loginstate===false){
	echo("You are not authorised to view this page");
	exit();
}
if(!$login_email){
	//cant do as no admin email to send from
	echo("An administrator email address was not found. Please check that you are logged in as an administrator and that your profile email address is valid");
	exit();	
}else{
	$from=$login_email;
}
?>
<html>
<head>
<title>Restore member archive</title>
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
<link href="../style.css" rel="stylesheet" type="text/css">
<body>
<form name="form" method="post" action="">
<table border="0" cellpadding="2" width="660">
  <tr> 
    
<td valign="middle"><h1>Restore member archive</h1></td>
  <td valign="middle" align="right"> 
      <div align="right"><a href="javascript:closeme()"><img src="../Image/common/close.gif" width="18" height="18" alt="Close this window and return to search entry" border="0"><br>
        </a><a href="javascript:printWindow()"><img src="../Image/common/print.gif" width="18" height="18" alt="Print this page" border="0"></a></div>
    </td>
  </tr>
  <tr>
    <td colspan='2'> 
        
<h2>Note message
</h2></td>
    </tr>
<tr><td colspan="2"> 
<?php
//check if we have a list
if(!$maillist){
	//assume direct from wheresql bypassing select list so create $maillist
	$nwheresql = str_replace("\\","",$wheresql);
	$query = $db->getQuery(true)
		->select($db->qn('ID'))
		->from($db->qn('tblMembers_archive_archive'))
		->where($nwheresql)
		->order($sort);
	$result = $db->setQuery($query)->loadAssocList();
  		if (!$result) {
    		echo("<P>Error finding members</P>");
	    	exit();
		}
	$num_rows = count($result);
	foreach($result as $row) {
		$maillist.="_".$row["ID"];
	}
	$maillist.="_";
}
$query = $db->getQuery(true)
	->select($db->qn('ID'))
	->from($db->qn('tblMembers_archive'));
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
echo("<b>" . $Title . "</b><br><br>".$maxmembers. " selected for restoring from $num_records members on the archive<br>\n");
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
	//do restore
	$changedate=date("Y-m-d H:i:s");
	$members = explode ("_", $maillist);
	$thismemberid=0;
	$maxmembers=sizeof ($members);
	$restore_no=1;
	$thismessage=stripslashes($sendmessage);
	$thissubject=stripslashes($subject);
	while($restore_no<$maxmembers){
		$thismemberid=$members[$restore_no];
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('tblMembers_archive'))
			->where($db->qn('ID').' = '.$db->q($thismemberid));
		$memberinfo = $db->setQuery($query)->loadAssocList();
		if (!$memberinfo) {
			break;
		}
		$row = reset($memberinfo);
		$memberid=$row["ID"];
		$MembershipNo=$row["MembershipNo"];
		$NotesAdmin=$row["NotesAdmin"];
		$contactname="";
		$contactname=$row["FirstName"];
		if($row["FirstName"] && $contactname){
			$contactname.=" ".$row["LastName"];
		}else{
			$contactname=$row["LastName"];
		}
		
		
		
		//add to the members table
		$query="INSERT INTO tblMembers SELECT * FROM tblMembers_archive WHERE ID=\"".$memberid."\"";
		$db->setQuery($query)->execute() or die ("Couldn't insert archive");		
		
		//delete archive record	
		$query = $db->getQuery(true)
			->delete($db->qn('tblMembers_archive'))
			->where($db->qn('ID').' = '.$db->q($memberid));
		$db->setQuery($query)->execute() or die ("Couldn't remove archive");				
	
		//add the note
		$newnote=$NotesAdmin."\n".$note;
		$update = new \stdClass();
		$update->NotesAdmin = addslashes($newnote);
		$update->ID = $memberid;
		$db->updateObject('tblMembers', $update, 'ID') or die ("Couldn't update archive date");		
		
		//enter action into member change log (for the benefit of admin or traceability)
		$subject="Profile restored";
		$changelogtext="Profile restored from archive by administrator";
		$insert = new \stdClass();
		$insert->MemberID = $memberid;
		$insert->Subject = $subject;
		$insert->ChangeDesc = $changelogtext;
		$insert->ChangeDate = $changedate;
		$db->insertObject('tblChangeLog', $insert) or die ("Couldn't update change log");	
		$donelist.=$MembershipNo."  ".$contactname."<br>\n";
		$restore_no+=1;
	} 
	echo("<br>Summary: The following have been restored: <br><br>$donelist");
}
if(!$note){
	//default note
	$note=date("d/m/Y")." restored from archive ";
	echo("<table border='0' cellspacing='1' cellpadding='4'>");
	echo("<tr><td><b>Action</b></td>");
	echo("<td><input type='submit' name='send' value='Restore'></td></tr>");
	
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
