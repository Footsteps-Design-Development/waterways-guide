<?php
/**
 * @version     3.0.0 20210713
 * @package     com_membership search
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Chris Grant www.productif.co.uk
 */
echo("run 1");
//$componentpath="/components/com_membership/views/search/tmpl/";
echo(" run 1.5");
//load Joomla helpers
defined('_JEXEC') or die;
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', strstr(__DIR__, 'public_html', true).'public_html');
define('JPATH_COMPONENT', JPATH_BASE .DS.'components'.DS.'com_membership');
require_once(JPATH_BASE .DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE .DS.'includes'.DS.'framework.php');
require_once(JPATH_COMPONENT .DS.'commonV3.php');
use Joomla\CMS\Factory;
echo(" run 2");
$db = Factory::getDBO();
$user = Factory::getUser();
echo(" run 3");
//check access level of user
$userGroups = $user->getAuthorisedGroups();
if (in_array("8", $userGroups) || in_array("22", $userGroups)) {
    $membershipadmin=true; //superuser or membershipadmin
	$level=1;
}else{
	$membershipadmin=false;
	$level=0;
}
if (in_array("24", $userGroups)) {
    $membershipview=true; //board members view only
}else{
	$membershipview=false;
}
$login_email=$user->email;
$login_username=$user->username;
$login_name=$user->name;
$clist="";
$GroupID=0;
$maxlength=50;
getpost_ifset(array('level','login_email','login_name','table','wheresql','maillist','sort','status','criteria','membershipadmin','register'));
/*
$_SESSION["level"]=$level;
$_SESSION["login_email"]=$login_email;
$_SESSION["login_name"]=$login_name;
$_SESSION["this_wheresql"]=$wheresql;
$_SESSION["this_sort"]=$sort;
$_SESSION["wheresql"]=$wheresql;
$_SESSION["sort"]=$sort;
$_SESSION["table"]=$table;
$_SESSION["membershipadmin"]=$membershipadmin;
*/
//echo("email: ".$login_email." name: ".$login_name." level: ".$level." membershipadmin: ".$membershipadmin. " wheresql: ".$wheresql);
$componentpath="../../../views/search/tmpl/";
$reportcomponentpath="../../../views/report/tmpl/";
?>
<html><HEAD>
<SCRIPT LANGUAGE="JavaScript">
<!--
function closeme() {
window.close(self);
}
function printWindow(){
   bV = parseInt(navigator.appVersion)
   if (bV >= 4) window.print()
}
function MoreInfo(memberid){
	var mypage = "../../../../../index.php?option=com_membership&tmpl=component&view=profile&userid="+memberid<?php if(isset($table) && $table=="archive"){echo("+\"&table=archive\"");	} ?>;
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
	var mypage = "view_change_log.php?memberid="+memberid<?php if(isset($table) && $table=="archive"){echo("+\"&table=archive\"");	} ?>;
	
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
function doreport(n){
	var form='form'; 
	var valid=0; 
	dml=document.forms[form];
	len = dml.elements.length;
	var i=0;
	var maillist = "_";
	//Check that a selection has been made and if so add to member string
	if(n<1){
		//filter
		var selections=0;
		for( i=0 ; i<len ; i++) {
			if ((dml.elements[i].name=='select') && (dml.elements[i].checked==1)){
				maillist+=dml.elements[i].value+"_";
				selections+=1;
				valid=1;
			}
		}
		if(valid==0){
			alert("Please select at least one member from the list");
		}else if(selections>300){
			alert("You have selected "+selections+" and the maximum number of individual selections allowed is 300. Please reduce your selections or continue with a report using the 'GO ALL' button.");
		}
		
	}else{
		valid=1;
	}
	if(valid==1){
		var	wheresql = (document.form.wheresql.value);
		//alert(wheresql);
		var criteria = (document.form.criteria.value);
		var sort = (document.form.sort.value);
		//var docname=document.form[0].reportname.value;
		var docname=dml.reportname.value;
		//var docname="maillist_report_1.php";
		var mypage = docname+"&maillist="+maillist+"&criteria="+criteria;			
		mypage+="&wheresql=<? echo(addslashes($wheresql)); ?>";
		//mypage+= encodeURI(wheresql);
		//mypage+="&criteria\=";
		//mypage+= encodeURI(criteria);
		mypage+="&sort=";
		mypage+= sort;
		mypage+="&login_email=<? echo($login_email); ?>";
		//mypage+= encodeURI(<? echo($login_email); ?>);
		mypage+="&login_name=<? echo($login_name); ?>";
		
		mypage+="&level=<? echo($level); ?>";
		
		
		alert(mypage);
		//var mypage = docname+"&maillist="+maillist+"&wheresql="+wheresql+"&criteria="+criteria;			
	
		//var mypage = docname+"&criteria="+criteria+"&maillist="+maillist;
		
		window.addEvent('domready', function() {
			SqueezeBox.open(mypage, {
			handler: 'iframe',
			size: { x: 800, y: 600 }
			 });
		});
		
		/*var myname = "Reports";
		var w = 800;
		var h = 600;
		var scroll = "yes";
		var winl = (screen.width - w) / 2;
		var wint = (screen.height - h) / 2;
		winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scroll+',resizable'
		mypage += (mypage.indexOf('?') != -1 ? '&' : '?') + 'nocache=' + Date.now();
		win = window.open(mypage, myname, winprops)
		if (parseInt(navigator.appVersion) >= 4) { win.window.focus(); }
		*/
	}
}
function groups(id){
	var mypage = "edit_groups.php?id="+id+"&gkid=1";
	var myname = "groups";
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
function message(actn){
	var form='form'; 
	var valid=0; 
	dml=document.forms[form];
	len = dml.elements.length;
	var i=0;
	if(actn==1 || actn==0){
		//alert("email manager "+val);
		for( i=0 ; i<len ; i++) {
			if (dml.elements[i].name=='select') {
				dml.elements[i].checked=actn;
			}
		}
	}
}
function restore_archive(){
	var form='form'; 
	var valid=0; 
	dml=document.forms[form];
	len = dml.elements.length;
	var i=0;
	//Check that a selection has been made and if so add to member string
	var maillist = "_";
	var selections=0;
	for( i=0 ; i<len ; i++) {
		if ((dml.elements[i].name=='select') && (dml.elements[i].checked==1)){
			maillist+=dml.elements[i].value+"_";
			selections+=1;
			valid=1;
		}
	}
	if(valid==0){
		alert("Please select at least one member from the list");
	}else if(selections>300){
			alert("You have selected "+selections+" and the maximum number of individual selections allowed is 300. Please reduce your selections and try again.");
	}else{
		var criteria = encodeURI(document.form.criteria.value);
		//var docname=document.form[0].reportname.value;
		var docname="restore_archive.php";
		var table=document.form.table.value;
		var mypage = docname+"?criteria="+criteria+"&maillist="+maillist;
		var myname = "Restore";
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
}
//-->
</script>
<link href="../../../style.css" rel="stylesheet" type="text/css">
<title>Search results</title></HEAD>
<body>
<form name="form">
<input name="wheresql" type="hidden" value="<?php echo(addslashes($wheresql)); ?>">
<input name="criteria" type="hidden" value="<?php if(isset($criteria)) echo(addslashes($criteria)); ?>">
<input name="sort" type="hidden" value="<?php echo($sort); ?>">
<a name="top"></a>
<div class="pop_page_title"><h2>Search results</h2></div>
 <table border="0" cellspacing="2" cellpadding="3"  width="100%">
        <?php
$nwheresql = str_replace("\\","",$wheresql);
$query = $db->getQuery(true)
	->select($db->qn('ID'))
	->from($db->qn($table != 'archive' ? 'tblMembers' : 'tblMembers_archive'));
$records = $db->setQuery($query)->loadAssocList();
if (!$records) {
    echo("<P>Error performing query: ".$query->__toString()."</P>");
	exit();
}
$num_records = count($records);
$query = $db->getQuery(true)
	->select('DISTINCTROW *')
	->from($db->qn($table != 'archive' ? 'tblMembers' : 'tblMembers_archive'))
	->where($nwheresql)
	->order($sort);
$result = $db->setQuery($query)->loadAssocList();
if (!$result) {
	echo("<P>Error performing query: ".$query->__toString()."</P>");
	exit();
}
$num_rows = count($result);
$thisdate=date("d M Y");
$Title="Target listing - $thisdate" ;
$maxrecords = 10000;
if(isset($table) && $table=="archive"){
	echo("<tr><td colspan=5><b>" . $Title . "</b><br><br>".$num_rows." match(es) found from $num_records members in the archive</td></tr>");
}else{
	echo("<tr><td colspan=5><b>" . $Title . "</b><br><br>".$num_rows." match(es) found from $num_records members on the database</td></tr>");
}
echo("<tr><td colspan=5>Search criteria: ".(isset($criteria) ? $criteria : '')."</td></tr>"); 
if (!$num_rows) {
    echo("<tr><td colspan=5><b>Sorry - nothing found - Please select some different criteria and try again</b></td></tr>");
	exit();
}
if ($num_rows > $maxrecords) {
    echo("<tr><td colspan=5>Maximum $maxrecords records has been reached.<br>Please select different or closer criteria and try again</td></tr>");
	exit();
}else{
   echo("<tr><td colspan=5>Click on the <img height=16 src='../../../images/info.gif' width=16 border=0 alt='Click to view details' title='Click to view details'> 'Info' icon for more details and editing or <img height=16 src='../../../images/txt.gif' width=16 border=0 alt='Click to view change log' title='Click to view change log'> 'log' to view changes <br>or select members to transfer to the Report, Email or Mailmerge message facility.</td></tr>\n");	
	echo("<tr><td colspan=5>Reports & Actions ");
	echo("<select name='reportname'>\n");
	echo("<option value='".$reportcomponentpath."maillist_email.php?var=1'>Email </option>\n");
	echo("<option value='".$reportcomponentpath."maillist_report_1.php?var=1'>Name and address details</option>\n");
	echo("<option value='".$reportcomponentpath."mergedoc.php?template=DBA_letter_Blank.rtf'>Merge to blank letter</option>\n");             
	echo("<option value='".$reportcomponentpath."maillist_merge.php?var=1'>Create mailmerge full list</option>\n");
	echo("<option value='".$reportcomponentpath."maillist_merge_labels.php?var=1'>Create mailmerge labels</option>\n");	
	echo("<option value='".$reportcomponentpath."maillist_merge_survey.php?var=1'>Create mailmerge for DBA Survey</option>\n");	
	echo("<option value='".$reportcomponentpath."maillist_merge_survey_anon.php?var=1'>Create mailmerge anonymised for DBA Survey</option>\n");	
	
	if($level==1){
		//only allow these by super or membership admin
		echo("<option value='".$reportcomponentpath."maillist_report_marksubpaid.php?var=1'>Mark subscription paid</option>\n");
		echo("<option value='".$reportcomponentpath."maillist_member_delete.php?var=1'>Delete member records (BEWARE, no going back)</option>\n");
		echo("<option value='".$reportcomponentpath."mergedoc.php?template=DBA_letter_Welcome.rtf'>Merge to DBA welcome letter</option>\n");
		echo("<option value='".$reportcomponentpath."mergedoc.php?template=DBA_letter_Sub_cc_Reminder.rtf'>Merge to cc sub reminder letter</option>\n");
		echo("<option value='".$reportcomponentpath."mergedoc.php?template=DBA_letter_Sub_ch_Reminder.rtf'>Merge to ch sub reminder letter</option>\n");
		echo("<option value='".$reportcomponentpath."mergedoc.php?template=DBA_letter_Sub_so_Reminder.rtf'>Merge to so sub notice letter</option>\n");
	
		echo("<option value='".$reportcomponentpath."mergedoc.php?template=DBA_letter_Sub_dd_Notice.rtf'>Merge to dd sub notice letter</option>\n");
	}
        
//<option value="message_edit.php">Prepare email message</option>
//components/com_membership/views/profile/tmpl/mergedoc.php?template="+template+"&memberid="+mid;
	//echo("<option value='maillist_report_sub_paid.php'>Mark Sub paid</option>\n");
 
	echo("</select><input type=button value='Go filter' onClick='javascript:doreport(0)' name='button'>\n");
	
	 
    echo("<input type='button' name='emailsend' value='Go ALL' onClick='javascript:doreport(1)'>\n");
	echo("</td></tr>\n");
}
$datenow = time();
if($register=="1"){
	//include barge regsiter columns
	$header = "<tr><td><b>Select</b><br><input type='button' name='selectall' value='+' onClick='javascript:message(1)'><input type='button' name='clearall' value='-' onClick='javascript:message(0)'></td><td><b>Member</b></td><td><b>Last Paid</b></td><td><b>email / login</b></td>
	<td><b>Barge</b></td><td><b>Class</b></td><td><b>Year</b></td><td><b>Length</b></td><td><b>Beam</b></td><td><b>Details</b></td></tr>";
}else{
	$header = "<tr><td><b>Select</b><br><input type='button' name='selectall' value='+' onClick='javascript:message(1)'><input type='button' name='clearall' value='-' onClick='javascript:message(0)'></td><td><b>Member</b></td><td><b>Last Paid</b></td><td><b>email / login</b></td><td><b>Details</b></td></tr>";
}
echo($header);
$line="even";
foreach($result as $row) {
	list ($myDate, $myTime) = explode (' ', $row["DatePaid"]);
	//list ($myyear, $myyear, $myday) = explode ('-', $myDate);
	
	if($row["FirstName"]){
		$col1=$row["MembershipNo"]." ".stripslashes($row["FirstName"])." ".stripslashes($row["LastName"]);
	}else{
		$col1=$row["MembershipNo"]." ".stripslashes($row["LastName"]);
	}
	$col2=stripslashes($row["PaymentMethod"])." ".$myDate;
	
	if ($line == "even"){
		$lineformat="<td class='table_stripe_odd'>";
		$line="odd";
	}else{
		$lineformat="<td class='table_stripe_even'>";
		$line="even";
	}
	if($register=="1"){
		echo("<tr>" . $lineformat . "<input type='checkbox' name='select' value='".$row["ID"]."'></td>" . $lineformat . $col1 . "</td>" . $lineformat . $col2 . "</td>" . $lineformat .$row["Email"]. "</td>". "</td>" . $lineformat .$row["ShipName"]. "</td>"."</td>" . $lineformat .$row["ShipClass"]. "</td>"."</td>" . $lineformat .$row["ShipYear"]. "</td>"."</td>" . $lineformat .$row["ShipLength"]. "</td>"."</td>" . $lineformat .$row["ShipBeam"]. "</td>".$lineformat ."<a href='javascript:MoreInfo(" . $row["ID"] . ")'><img height=16 src='../../../images/info.gif' width=16 border=0 alt='Click to view details' title='Click to view details'></a> <a href='javascript:Changelog(" . $row["ID"] . ")'><img height=16 src='../../../images/txt.gif' width=16 border=0 alt='Click to view change log' title='Click to view change log'></a> <a href='javascript:groups(" . $row["ID"] . ")'><img height=16 src='../../../images/group.gif' width=13 border=0 alt='Click to view or edit groups' title='Click to view or edit groups'></a></td></tr>");	
	}else{
		echo("<tr>" . $lineformat . "<input type='checkbox' name='select' value='".$row["ID"]."'></td>" . $lineformat . $col1 . "</td>" . $lineformat . $col2 . "</td>" . $lineformat .$row["Email"]. "</td>". $lineformat ."<a href='javascript:MoreInfo(" . $row["ID"] . ")'><img height=16 src='../../../images/info.gif' width=16 border=0 alt='Click to view details' title='Click to view details'></a> <a href='javascript:Changelog(" . $row["ID"] . ")'><img height=16 src='../../../images/txt.gif' width=16 border=0 alt='Click to view change log' title='Click to view change log'></a> <a href='javascript:groups(" . $row["ID"] . ")'><img height=16 src='../../../images/group.gif' width=13 border=0 alt='Click to view or edit groups' title='Click to view or edit groups'></a></td></tr>");	
	}
}
if ($num_rows > 8) {
    echo("<tr><td colspan='5'><a href='#top'>Back to the top</a><br></td></tr>");
}
//echo("<tr><td colspan='5'><br>$footer1</td></tr>");
?>
</table>
  
</form>
</body></html>
