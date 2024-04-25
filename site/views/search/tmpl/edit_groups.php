<?php define( '_JEXEC', 1 );
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', strstr(__DIR__, 'public_html', true).'public_html');
define('JPATH_COMPONENT', JPATH_BASE .DS.'components'.DS.'com_waterways_guide');
require_once(JPATH_BASE .DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE .DS.'includes'.DS.'framework.php');
require_once(JPATH_COMPONENT .DS.'commonV3.php');
getpost_ifset(array('action','id','gkid','newgroup','author','groupid'));

$level=60;

use Joomla\CMS\Factory;

$db = Factory::getDbo();

if(isset($action) && $action=="addnewgroup"){
	//add new group
	if(!empty($gkid)){
		$changedate=date("Y-m-d H:i:s");
		$insert = new \stdClass();
		$insert->GroupDesc = $newgroup;
		if(isset($author)) $insert->GroupAuthor = $author;
		$insert->GroupLastUpdate = $changedate;
		$insert->GroupChannel = $gkid;
		$result = $db->insertObject('tblGroupType', $insert);
		if(!$result){die ("Couldn't update groups" .print_r($insert, true));}
		$message="New Group '".$newgroup."' added to groups list";
	}else{
		$gkid = '';
		$message="There is no owner for creating the new group '".$newgroup."'. Please add a new group via a gatekeeper profile.";	
	}
}
if(isset($action) && ($action=="addtogroup" || $action=="removefromgroup")){
	//echo("Update group ".$groupid);
	//lookup the group description
	
	//check if edit in group id
	$gid  =  preg_replace("/e/",'',$groupid);  
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblGroupType'))
		->where($db->qn('GroupID').' = '.$db->q($gid));
	$result = $db->setQuery($query)->loadAssocList();
	if (count($result) == 0) {	
		//Group not found
		echo("Error - Couldn't find Group $groupid to update");
	}else{
		$grouprow = reset($result);
		$GroupDesc=$grouprow["GroupDesc"];
	}
	$changedate=date("Y-m-d H:i:s");
	//remove or add group to member profile
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblMembers'))
		->where($db->qn('ID').' = '.$db->q($id));
	$result = $db->setQuery($query)->loadAssocList();
	$row = reset($result);
	$Groups=$row["Groups"];
	$num_rows = count($result);
	if(isset($action) && $action=="addtogroup"){
		if($Groups){
			//existing groups so add it on end
			$updategroups=$Groups.$groupid."|";
		}else{
			$updategroups="|".$groupid."|";
		}
		$changelogtext="Subscribed to Group '".$GroupDesc."'";
		$message=$changelogtext;

	}
	if(isset($action) && $action=="removefromgroup" && !empty($GroupDesc)){
		$updategroups=str_replace("|".$groupid."|", "|", $Groups);
		$changelogtext="Unsubscribed from Group '".$GroupDesc."'";
		$message=$changelogtext;
	}
	$subject="Profile update";
	//write back the updated group
	$update = new \stdClass();
	$update->Groups = $updategroups;
	$update->LastUpdate = $changedate;
	$update->ID = $id;
	$db->updateObject('tblMembers', $update, 'ID') or die ("Couldn't update member database");
	//update change log to show group removed
	$insert = new \stdClass();
	$insert->MemberID = $id;
	$insert->Subject = $subject;
	$insert->ChangeDesc = $changelogtext;
	$insert->ChangeDate = $changedate;
	$db->insertObject('tblChangeLog', $insert) or die ("Couldn't add to database" .print_r($insert, true));
}

if(isset($action) && $action=="deletegroup"){
	//echo("Delete group ".$groupid);
	//lookup this group description
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblGroupType'))
		->where($db->qn('GroupID').' = '.$db->q($groupid));
	$result = $db->setQuery($query)->loadAssocList();
	$num_rows = count($result);
	if (empty($num_rows)) {	
		//Group not found
		echo("Error - Couldn't find Group $groupid to delete");
	}else{
		$grouprow = reset($result);
		$GroupDesc=$grouprow["GroupDesc"];
	}
	//delete group
	$changedate=date("Y-m-d H:i:s");
	$query = $db->getQuery(true)
		->delete($db->qn('tblGroupType'))
		->where($db->qn('GroupID').' = '.$db->q($groupid));
	$update = $db->setQuery($query)->execute();
	if(!$update){
		echo("Couldn't delete Group");
	}
	//remove group from all members currently in it
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblMembers'))
		->where($db->qn('Groups')." LIKE '%|".$groupid."|%'");
	$result = $db->setQuery($query)->loadAssocList();
	if (!$result) {
		$message="Group '".$GroupDesc."'  had no Member Profiles and has been deleted";
	} else {
		$num_rows = count($result);
		$grouptoremove="|".$groupid."|";
		$changelogtext="Unsubscribed from Group '".$GroupDesc."' as the Group was closed";
		$subject="Profile update";
		$changedate=date("Y-m-d H:i:s");
		$updates=0;
		foreach($result as $row) {
			$Groups=$row["Groups"];
			$ID=$row["ID"];
			$updategroups=str_replace($grouptoremove, "|", $Groups);	
			//write back the updated group
			$update = new \stdClass();
			$update->Groups = $updategroups;
			$update->LastUpdate = $changedate;
			$update->ID = $ID;
			$db->updateObject('tblMembers', $update, 'ID') or die ("Couldn't update member database");
			//update change log to show group removed

			$updates+=1;
			$insert = new \stdClass();
			$insert->MemberID = $ID;
			$insert->Subject = $subject;
			$insert->ChangeDesc = $changelogtext;
			$insert->ChangeDate = $changedate;
			$db->insertObject('tblChangeLog', $insert) or die ("Couldn't add to database" .print_r($insert, true).' insert into tblChangeLog');

		}
		$message="Group '".$GroupDesc."' has been deleted and removed from ".$updates." Member profile(s)";
	}
}


//get groups for this member
if (empty($id) || $id != "newdirect") {
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblMembers'))
		->where($db->qn('ID').' = '.$db->q($id));
	if($row = $db->setQuery($query)->loadAssoc()) {
		$contactname=$row["FirstName"]." ".$row["LastName"];
		$membername=$contactname;
		$membergroups=$row["Groups"];
		$Level=$row["Level"];	
		if($Level==50 || $Level==60){
			$message="This member has full site editing rights which overide any Group content editing settings.";
		}else{
			$message="";
		}
	}	
	//Check number of groups available.
	$query = $db->getQuery(true)
		->select('COUNT(*)')
		->from($db->qn('tblGroupType'));
	$numgroups = $db->setQuery($query)->loadResult();
}else{
	$message="Please complete and 'Update' initial details of this New member before adding to groups";
	$newmember=1;
}
?>
<html>
<head>
<title>Edit Groups</title>
<SCRIPT LANGUAGE="JavaScript">
<!--
function closeme() {
	window.close(self);
}
function printWindow(){
   bV = parseInt(navigator.appVersion)
   if (bV >= 4) window.print()
}

function DeleteGroup(groupcode){
	if (confirm("Press 'OK' if you are sure you want to delete this Group. All members currently subscribed to this Group will be unsubscribed.\nOtherwise press 'Cancel'.")) {
	var id = document.form.id.value;
	var gkid = document.form.gkid.value;
	var url="edit_groups.php?action=deletegroup&id="+id+"&gkid="+gkid+"&groupid="+groupcode + '&nocache=' + Date.now();
	window.location.href = url;
	}  
}

function MM_validateForm() {
 	var errors="";
	var newgroup = document.form.newgroup.value;
	if (newgroup == ""){
		errors+='- enter a Group description\n';
	}
	if (errors) {
		alert('Please check your entry and try again:\n'+errors);
	}else{
	  	document.form.action.value="addnewgroup"
	  	document.MM_returnValue = (errors == '');
	}
}

function grouptype(cbname,groupcode){
	var state = cbname.checked;
	if(state==1){
		//add it
		var action="addtogroup";
	}else{
		var action="removefromgroup";
	}
	//alert(new_service_sel);
	var id = document.form.id.value;
	var gkid = document.form.gkid.value;
	var url="edit_groups.php?action="+action+"&id="+id+"&gkid="+gkid+"&groupid="+groupcode + '&nocache=' + Date.now();
	window.location.href = url;
}
function MoreInfo(groupid){
var mypage = "group_list.php?groupid\=" + groupid;
var myname = "grouplist";
//var w = (screen.width - 100);
//var h = (screen.height - 100);
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

//-->
</script>
<link rel="stylesheet" href="../../../style.css" type="text/css">
</head>

<body>
<div class="pop_page_title"><h2>Group administration</h2></div>
<div class="pop_page_buttons"><a href="javascript:closeme()"><img src="../../../images/close.gif" width="18" height="18" alt="Close this window and return to search entry" border="0"><br>
        </a><a href="javascript:printWindow()"><img src="../../../images/print.gif" width="18" height="18" alt="Print this page" border="0"></a></div>

<form action="edit_groups.php" method="post" name="form">
<input name="id" type="hidden" value="<?php echo($id); ?>">
<input name="gkid" type="hidden" value="<?php echo $gkid; ?>">
<input name="action" type="hidden" value="">
<table border='0' cellspacing='2' cellpadding='3' width='100%'>
<?php
if(isset($message)){
	echo("<tr><td colspan=4><font color='#ff0000'><b>".$message."</b></font></td></tr>\n");
}
if(empty($newmember)){
	echo("<tr><td colspan=4>Tick  boxes to include '".(isset($membername) ? $membername : '')."' in a Group.</td></tr>");
	echo("<tr><td colspan=4 class=bodytext>Group member view <img height=16 src='../../../images/group.gif' width=13 border=0 alt='Group member page'></td><td colspan=3 class=bodytext>Group content editor <img height=16 src='../../../images/group_edit.gif' width=13 border=0 alt='Group edit page'></td></tr>\n");

	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblGroupType'))
		->where($db->qn('GroupChannel').' = '.$db->q($gkid))
		->order($db->qn('GroupDesc'));
	$groups = $db->setQuery($query)->loadAssocList();
	$numgroups = count($groups);
	if (!$groups) {
		echo("<P>Error finding available groups</P>");
		exit();
	}
	$searchmembergroups=isset($membergroups) ? $membergroups : '';
	foreach($groups as $grouprow) {
		$groupid=$grouprow["GroupID"];
		$groupdesc=$grouprow["GroupDesc"];
?>	<tr>
		<td class="table_stripe_even"><input type="checkbox" name="group<?= $groupid; ?>" value="<?= $groupid; ?>" onClick="grouptype(this,'<?= $groupid; ?>')"<?php
			$found = strstr ($searchmembergroups, "|".$groupid."|");
			if($found) echo ' checked';
		?>></td>
		<td class="table_stripe_even"><?= $groupdesc; ?></td>
		<td><a href="javascript:MoreInfo('<?= $groupid; ?>')"><img height="16" src="../../../images/info.gif" width="16" border="0" title="Click to see who is currently in this group"></a></td>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td class="table_stripe_even"><input type="checkbox" name="groupe<?= $groupid; ?>" value="<?= $groupid; ?>" onClick="grouptype(this,'<?= $groupid; ?>e')"<?php
			//check edit rights
			$found = strstr($searchmembergroups, "|".$groupid."e|");
			if($found) echo ' checked';
		?>></td>
		<td></td>
		<td><a href="javascript:MoreInfo('<?= $groupid; ?>e')"><img height="16" src="../../../images/info.gif" width="16" border="0" title="Click to see who is currently in this group"></a></td>
		<td><a href='javascript:DeleteGroup(<?= $groupid; ?>)'>delete Group</a></td>
	</tr>
<?php }


   	if($level==60){
		echo("<tr><td colspan=4 class=bodytext>New Group <input name=\"newgroup\" type=\"text\" size=\"30\"><input type=\"submit\" name=\"Submit\" value=\"Add\" onClick=\"MM_validateForm();return document.MM_returnValue\"></td></tr>\n");
	}
}
?>
</table>		
</form>
  
</body>

</html>
