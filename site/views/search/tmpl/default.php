<?php
/**
 * @version     1.0.0
 * @package     com_waterways_guide search
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Chris Grant www.productif.co.uk
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

$componentpath="/components/com_waterways_guide/views/search/tmpl/";

$maxlength=50;

$db = Factory::getDBO();
$user = Factory::getUser();



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

$_SESSION["login_email"] = $login_email;
$_SESSION["login_username"] = $login_name;
$_SESSION["login_name"] = $login_name;
$_SESSION["membershipadmin"] = $membershipadmin;
$_SESSION["level"] = $level;
// $_SESSION["table"] = $table;

?>
<html>
<head>
<title>Search and Select</title>
<link rel="stylesheet" href="../style.css" type="text/css">


<style type="text/css">


</style>
</head>

<script language="JavaScript">

sortitems = 1;  // Automatically sort items within lists? (1 or 0) 

function move_choice(fbox,tbox) {
for(var i=0; i<fbox.options.length; i++) {
	if(fbox.options[i].selected && fbox.options[i].value != "") {
		var no = new Option();
		no.value = fbox.options[i].value;
		var thisvalue = fbox.options[i].value;
		no.text = fbox.options[i].text;
	}
}
var l=tbox.options.length;
if (l>0){
	for(var j=0; j<l; j++) {
		if(tbox.options[j].value == thisvalue){
			var dup=1;
		}
	}
}else{
	var dup=0;
}
if(dup!=1){
	tbox.options[tbox.options.length] = no;
	if (sortitems) SortD(tbox);
}

}

function drop(tbox) {
	var j=tbox.options.length;
	for(var i=0; i<j; i++) {
		if(tbox.options[i].selected) {
			tbox.options[i].value = "";
			tbox.options[i].text = "";
		}
	
	}
	
	BumpUp(tbox);
	if (sortitems) SortD(tbox);
}

function BumpUp(box)  {
	for(var i=0; i<box.options.length; i++) {
		if(box.options[i].value == "")  {
			for(var j=i; j<box.options.length-1; j++)  {
				box.options[j].value = box.options[j+1].value;
				box.options[j].text = box.options[j+1].text;
			}
			var ln = i;
			break;
	   }
	}
	if(ln < box.options.length)  {
		box.options.length -= 1;
		BumpUp(box);
	}
}

function SortD(box)  {

	var temp_opts = new Array();
	var temp = new Object();
	var tempval = new Object();
		for(var i=0; i<box.options.length; i++)  {
		temp_opts[i] = box.options[i];
	}
	for(var x=0; x<temp_opts.length-1; x++)  {
		for(var y=(x+1); y<temp_opts.length; y++)  {
			if(temp_opts[x].text > temp_opts[y].text)  {
				temp = temp_opts[x].text;
				tempval = temp_opts[x].value;
				temp_opts[x].text = temp_opts[y].text;
				temp_opts[x].value = temp_opts[y].value;
				temp_opts[y].text = temp;
				temp_opts[y].value = tempval;
			}
		}
	}
	for(var i=0; i<box.options.length; i++)  {
		box.options[i].value = temp_opts[i].value;
		box.options[i].text = temp_opts[i].text;
	}
}

function gofind() {

	sqltext="";
	criteria="";
	invers="";
	if(document.form.Criteria0c.options.length > 0){
		if (sqltext){sqltext+= " AND ("}else{sqltext="("}
		criteria+="Situation ("; 
		for(var i=0; i<document.form.Criteria0c.options.length; i++) {
			sqltext+= "(Situation=";
			sqltext+=  document.form.Criteria0c.options[i].value;
			criteria+= document.form.Criteria0c.options[i].text; 
			sqltext+= ")";
			if (i<document.form.Criteria0c.options.length-1){
				sqltext+=" OR ";
				criteria+=" / ";
				}
		}
		sqltext+= ")";
		criteria+=") ";
	}
	
	
	if(document.form.Criteria1c.options.length > 0){
		if (sqltext){sqltext+= " AND ("}else{sqltext="("}
		criteria+="Post Zone ("; 
		for(var i=0; i<document.form.Criteria1c.options.length; i++) {
			sqltext+= "(PostZone=\"";
			sqltext+=  document.form.Criteria1c.options[i].value;
			criteria+= document.form.Criteria1c.options[i].text; 
			sqltext+= "\")";
			if (i<document.form.Criteria1c.options.length-1){
				sqltext+=" OR ";
				criteria+=" / ";
			}
		}
		sqltext+= ")";
		criteria+=") ";
	}
	
	if(document.form.Criteria2c.options.length > 0){
		if (sqltext){sqltext+= " AND ("}else{sqltext="("}
		criteria+="Status ("; 
		for(var i=0; i<document.form.Criteria2c.options.length; i++) {
			sqltext+= "(MemStatus=\"";
			sqltext+=  document.form.Criteria2c.options[i].value;
			criteria+= document.form.Criteria2c.options[i].text; 
			sqltext+= "\")";
			if (i<document.form.Criteria2c.options.length-1){
				sqltext+=" OR ";
				criteria+=" / ";
			}
		}
		sqltext+= ")";
		criteria+=") ";
	}
	
	
	if(document.form.Criteria4c.options.length > 0){
		if (sqltext){sqltext+= " AND ("}else{sqltext="("}
		criteria+="Location ("; 
		for(var i=0; i<document.form.Criteria4c.options.length; i++) {
			sqltext+= "(CountryCode=\"";
			sqltext+=  document.form.Criteria4c.options[i].value;
			criteria+= document.form.Criteria4c.options[i].text; 
			sqltext+= "\")";
			if (i<document.form.Criteria4c.options.length-1){
				sqltext+=" OR ";
				criteria+=" / ";
			}
		}
		sqltext+= ")";
		criteria+=") ";
	}
	
	if(document.form.Criteria44c.options.length > 0){
		if (sqltext){sqltext+= " AND ("}else{sqltext="("}
		criteria+="Barge Location ("; 
		for(var i=0; i<document.form.Criteria44c.options.length; i++) {
			sqltext+= "(CountryCodeCruising=\"";
			sqltext+=  document.form.Criteria44c.options[i].value;
			criteria+= document.form.Criteria44c.options[i].text; 
			sqltext+= "\")";
			if (i<document.form.Criteria44c.options.length-1){
				sqltext+=" OR ";
				criteria+=" / ";
			}
		}
		sqltext+= ")";
		criteria+=") ";
	}
	
	if(document.form.Criteria5c.options.length > 0){
		if (sqltext){sqltext+= " AND ("}else{sqltext="("}
		criteria+="Member type ("; 
		for(var i=0; i<document.form.Criteria5c.options.length; i++) {
			sqltext+= "(MemTypeCode=";
			sqltext+=  document.form.Criteria5c.options[i].value;
			criteria+= document.form.Criteria5c.options[i].text; 
			sqltext+= ")";
			if (i<document.form.Criteria5c.options.length-1){
				sqltext+=" OR ";
				criteria+=" / ";
			}
		}
		sqltext+= ")";
		criteria+=") ";
	}
	if(document.form.Criteria6c.options.length > 0){
		if (sqltext){sqltext+= " AND ("}else{sqltext="("}
		criteria+="Payment Method ("; 
		for(var i=0; i<document.form.Criteria6c.options.length; i++) {
			sqltext+= "(PaymentMethod=\"";
			sqltext+=  document.form.Criteria6c.options[i].value;
			criteria+= document.form.Criteria6c.options[i].text; 
			sqltext+= "\")";
			if (i<document.form.Criteria6c.options.length-1){
				sqltext+=" OR ";
				criteria+=" / ";
			}
		}
		sqltext+= ")";
		criteria+=") ";
	}
	
	if(document.form.Criteria7c.options.length > 0){
		if (sqltext){sqltext+= " AND ("}else{sqltext="("}
		criteria+="Group ("; 
		for(var i=0; i<document.form.Criteria7c.options.length; i++) {
			sqltext+= "(Groups LIKE \"%|";
			sqltext+=  document.form.Criteria7c.options[i].value;
			criteria+= document.form.Criteria7c.options[i].text; 
			sqltext+= "|%\")";
			if (i<document.form.Criteria7c.options.length-1){
				sqltext+=" OR ";
				criteria+=" / ";
			}
		}
		sqltext+= ")";
		criteria+=") ";
	}

	if(document.form.Criteria8c.options.length > 0){
		if (sqltext){sqltext+= " AND ("}else{sqltext="("}
			criteria+="Profile preferences ("; 
			if(document.form.Criteria8cInv.checked){
				for(var i=0; i<document.form.Criteria8c.options.length; i++) {
					sqltext+= "(Services NOT LIKE \"%|";
					sqltext+=  document.form.Criteria8c.options[i].value;
					criteria+= document.form.Criteria8c.options[i].text; 
					sqltext+= "|%\")";
					if (i<document.form.Criteria8c.options.length-1){
						sqltext+=" OR ";
						criteria+=" / ";
					}
				}
			}else{
				for(var i=0; i<document.form.Criteria8c.options.length; i++) {
				sqltext+= "(Services LIKE \"%|";
				sqltext+=  document.form.Criteria8c.options[i].value;
				criteria+= document.form.Criteria8c.options[i].text; 
				sqltext+= "|%\")";
				if (i<document.form.Criteria8c.options.length-1){
					sqltext+=" OR ";
					criteria+=" / ";
				}
			}
		}
		sqltext+= ")";
		criteria+=") ";
	}
		//Joining date
	
	var startday="01";
	var join_month=document.form.joinmonth.value;
	var join_year=document.form.joinyear.value;
	var searchtxt="";
	if(join_year!="Any year"){
		searchtxt="%"+join_year;
	}
	
	if(join_month!="Any month"){
		if(join_month<10){
			join_month="0"+join_month;
		}
		if(join_year!="Any year"){
			//year and month
			searchtxt+="-"+join_month;
		}else{
			//just month
			searchtxt="%-"+join_month;
		}
	}
	if(searchtxt!=""){
		searchtxt+="-%";
	}
	
	
	if(join_month=="Any month" && join_year=="Any year"){
		//not set
	}else{
		//add join date to sql
		if (sqltext){sqltext+= " AND (("}else{sqltext="(("}
		criteria+="Joining date ("+join_month+"-"+join_year+") "; 
		sqltext+= "DateJoined LIKE \""+searchtxt+"\"))";
	}
	
	
	//Membership renewal date
	var startday="01";
	var join_month=document.form.renewmonth.value;
	var join_year=document.form.renewyear.value;
	var searchtxt="";
	if(join_year!="Any year"){
		searchtxt="%"+join_year;
	}
	
	if(join_month!="Any month"){
		if(join_month<10){
			join_month="0"+join_month;
		}
		if(join_year!="Any year"){
			//year and month
			searchtxt+="-"+join_month;
		}else{
			//just month
			searchtxt="%-"+join_month;
		}
	}
	if(searchtxt!=""){
		searchtxt+="-%";
	}
	
	
	if(join_month=="Any month" && join_year=="Any year"){
		//not set
	}else{
		//add renewal to sql
		if (sqltext){sqltext+= " AND (("}else{sqltext="(("}
		criteria+="Sub renewal due ("+join_month+"-"+join_year+") "; 
		sqltext+= "DatePaid LIKE \""+searchtxt+"\"))";
	}
	
	//Membership exit date
	var startday="01";
	var join_month=document.form.exitmonth.value;
	var join_year=document.form.exityear.value;
	var searchtxt="";
	if(join_year!="Any year"){
		searchtxt="%"+join_year;
	}
	
	if(join_month!="Any month"){
		if(join_month<10){
			join_month="0"+join_month;
		}
		if(join_year!="Any year"){
			//year and month
			searchtxt+="-"+join_month;
		}else{
			//just month
			searchtxt="%-"+join_month;
		}
	}
	if(searchtxt!=""){
		searchtxt+="-%";
	}
	
	
	if(join_month=="Any month" && join_year=="Any year"){
		//not set
	}else{
		//add exit date to sql
		if (sqltext){sqltext+= " AND (("}else{sqltext="(("}
		criteria+="Leaving date ("+join_month+"-"+join_year+") "; 
		sqltext+= "DateCeased LIKE \""+searchtxt+"\"))";
	}
	
	
	
	if(document.form.txtsearch.value){
		//Free text search keywords
		ssqltext="";
		validwords=0;
		searchtext=document.form.txtsearch.value;
		//strip whitespace on outside
		searchtext= searchtext.replace(/^\s+|\s+$/g, '');
		if (sqltext){
			ssqltext+= " AND (";
		}else{
			ssqltext="(";
		}
		criteria+="Keyword Search text contains ("+searchtext+")";
		searcharray = searchtext.split(" ");
		words=searcharray.length;
		for(var word=0; word<words; word++) {
			if ((searcharray[word] !="") && (searcharray[word] !=" ") && (searcharray[word].length>1)){
				if (word>0){ssqltext+= " OR "}
				validwords=1;
				//Notes admin added to free text search 211009 CJG
				ssqltext+= "(Keywords LIKE \"%"+searcharray[word]+"%\") OR (NotesAdmin LIKE \"%"+searcharray[word]+"%\")";
			}
		}
		//ssqltext+= ")";
		if (validwords==1){
			//ssqltext+= ")";
			sqltext+=ssqltext+")";
			
		}
	}
	

	//Database filter live / term / all

	len = document.form.dbfilter.length
	
	for (i = 0; i <len; i++) {
		if (document.form.dbfilter[i].checked) {
			var dbsql = document.form.dbfilter[i].value;
		}
	}
	
	if (dbsql){
		if (sqltext){
			sqltext+=" AND "+dbsql;
		}else{
			sqltext=dbsql;
		}
	}
	criteria+="Database ("+dbsql+")";
		

	
	document.form.wheresql = sqltext;
	//opendoc="index.php?option=com_waterways_guide&tmpl=component&view=search";
	
	opendoc='<?php echo($componentpath."search_results.php"); ?>';

	sortorderval=document.form.sortorder.value;
	login_email=document.form.login_email.value;		
	login_name=document.form.login_name.value;
	level=document.form.level.value;
	if(document.form.register.checked){
		register=1;
	}else{
		register=0;
	}
	if (!sqltext){
		sqltext="ID>0";
	}
	opendoc+="?wheresql\=";
	opendoc+= encodeURI(sqltext);
	opendoc+="&criteria\=";
	opendoc+= encodeURI(criteria);
	opendoc+="&sort\=";
	opendoc+= encodeURI(sortorderval);
	opendoc+="&login_email\=";
	opendoc+= encodeURI(login_email);
	opendoc+="&login_name\=";
	opendoc+= encodeURI(login_name);
	opendoc+="&level\=";
	opendoc+= encodeURI(level);
	opendoc+="&register\=";
	opendoc+= encodeURI(register);
	if(document.form.table.checked){
		//opendoc+="&table\="+document.form.table.value;
	}
	//alert(opendoc);	
	
	/*window.addEvent('domready', function() {
		SqueezeBox.open(opendoc, {
		handler: 'iframe',
		size: { x: 900, y: 600 }
		 });
	});
	*/
	
	
	var mypage = opendoc;
	var myname = "filter";
	var w = 800;
	var h = 600;
	var scroll = "yes";
	var winl = (screen.width - w) / 2;
	var wint = (screen.height - h) / 2;
	winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scroll+',resizable';
	mypage += (mypage.indexOf('?') != -1 ? '&' : '?') + 'nocache=' + Date.now();
	win = window.open(mypage, myname, winprops);
	if (parseInt(navigator.appVersion) >= 4) { win.window.focus(); }
	

}
function cleardates(type){
	if(type==1){
		document.form.joinmonth.selectedIndex = 0;
		document.form.joinyear.selectedIndex = 0;
	}
	if(type==2){
		document.form.renewmonth.selectedIndex = 0;
		document.form.renewyear.selectedIndex = 0;
	}
	if(type==3){
		document.form.exitmonth.selectedIndex = 0;
		document.form.exityear.selectedIndex = 0;
	}
}
function cleartxtsearch3c(){
document.form.txtsearch3c.value="";
}
function cleartxtsearch(){
document.form.txtsearch.value="";
}

</script>
<script language="JavaScript">

function admin(form){
	var doc = document.form.admindoc.value;
	if(!doc){
		exit;
	}
	var mypage = doc;
	var myname = "Admin";
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

function addmember(){
	var mypage = "/index.php?option=com_waterways_guide&tmpl=component&view=profile&userid=new&ID=new";
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

function exit(){

}

function closeme() {
window.close(self);
}

function printWindow(){
   bV = parseInt(navigator.appVersion)
   if (bV >= 4) window.print()
}

function MoreInfo(ClientID){
var mypage = "moreinfo.php?ClientID\=" + ClientID;
var myname = "moreinfo";
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
<body bgcolor="#FFFFFF">
<form name="form">
<input type="hidden" name="qv" value="">
<input type="hidden" name="criteria" value="">
<input type="hidden" name="sortorderval" value="">
<input type="hidden" name="login_email" value="<?php echo($login_email); ?>">
<input type="hidden" name="login_name" value="<?php echo($login_name); ?>">
<input type="hidden" name="level" value="<?php echo($level); ?>">
<input type="hidden" name="wheresql" value="">
<SCRIPT TYPE="text/javascript">
<!--

function txtsearchenter(myfield,e)
{
var keycode;
if (window.event) keycode = window.event.keyCode;
else if (e) keycode = e.which;
else return true;

if (keycode == 13)
   {
   gofind();
   //document.form.action.value='go';
   //document.form.assetaction=="xx";
   //myfield.form.submit();
   return false;
   }
else
   return true;
}
//-->
</SCRIPT>
<?php
echo("Administrator: $login_name email: $login_email");
?>
  

	
<table width="100%" border="0" cellpadding="4" cellspacing="2" class=bodytext>
  
<tr valign="bottom"> 
    
<td colspan="2"> 
      <table class=bodytext cellspacing="1" cellpadding="4" border="0">

<?php
if($membershipadmin==true){
//allow add new member
?>
<tr>
<td><div align="right"><b>New member:</b></div></td>
<td><input class='formcontrol' type='button' name='Newmember' onClick='addmember()' value='Add'></td>


</tr>
<tr> 
<?php
}

?>        
<tr> 
          
<td width="109"> 
            <div align="right"><b>Search</b></div></td>
<td> 
           </td>
<td valign="middle"><div align="right"><b>Sorted 
  by:</b></div></td>
<td valign="middle"><select class='form_control' name="sortorder">
	<option value="LastName">Contact Surname</option>
	<option value="MembershipNo">Membership number</option>
	<option value="Login">login email address</option>
	<option value="Country">Country</option>
	<option value="CountryCodeCruising">Country Cruising</option>
	<option value="PostZone">Postal Zone</option>
	<option value="PaymentMethod">Payment method</option>
	<option value="MemTypeCode">Member type</option>
	<option value="DateJoined">Date of Joining</option>
	<option value="ShipClass">Ship Class</option>
	<option value="ShipName">Ship Name</option>
	<option value="ShipYear">Ship Year</option>
	<option value="ShipLength">Ship Length</option>
	<option value="ShipBeam">Ship Beam</option>
</select>
  <input class='form_control' type='button' name='Go2' onClick='gofind()' value='Go'>
  <label>
  <input type='checkbox' name='table' id='table' value='archive'>
  Search Archive</label>
  
  </td>
</tr>
      </table>      </td>
      
</tr>
 
<tr valign="bottom">
<td colspan="2">  
<fieldset>
<legend>Member Status</legend>
<input name="dbfilter" type="radio"  value="" checked> All
<input name="dbfilter" type="radio" value="MemStatus!=5 AND MemStatus!=1"> Live 
<input name="dbfilter" type="radio" value="MemStatus=5"> Terminated<br>
<input name="register" type="checkbox" value="1"> Include Situation & barge details
</fieldset>
</td>

</tr>

<tr valign="bottom">
<td colspan="2">  
<fieldset>
<legend>Text search in keywords</legend>
<input type='button' class='form_control' name='Go3' onClick='cleartxtsearch()' value='Clear'> 
<input class='form_control' type="text" name="txtsearch" size="50" onKeyPress="return txtsearchenter(this,event)">
</fieldset></td>

</tr>
<tr>
<td colspan=2><fieldset>
<legend>Date joined</legend>
<input class='form_control' type='button' name='Go4' onClick='cleardates(1)' value='Clear'> 
<?php
$datenow=date("Y-m-d",time());
list ($thisyear, $thismonth, $thisday) = explode ('-', $datenow);
$datenowdisplay=date ("d M Y", time());
echo("<select class='form_control' name=joinmonth><option selected value=\"Any month\">Any month</option>\n"); 
$list=12;
$first=1;
while($list>=$first){
	$listM=date ("M", mktime(0,0,0,$list,1,2000));
	echo ("<option value=\"".$list."\">".$listM."</option>\n");
	$list-=1; 
}		
 echo("</select><select class='form_control' name=joinyear><option value=\"Any year\">Any year</option>\n");
$listyear=$thisyear;
//$firstyear=$thisyear;
$firstyear="2001";
while($listyear>=$firstyear){
	echo ("<option value=\"".$listyear."\">".$listyear."</option>\n");
$listyear-=1; 
}
echo("</select>");




?>
&nbsp;
</fieldset></td>

</tr>
<tr>
<td colspan=2><fieldset>
<legend>Date renewal</legend>
<input class='form_control' type='button' name='Go' onClick='cleardates(2)' value='Clear'> 
<?php
$datenow=date("Y-m-d",time());
list ($thisyear, $thismonth, $thisday) = explode ('-', $datenow);
$datenowdisplay=date ("d M Y", time());
echo("<select class='form_control' name=renewmonth><option selected value=\"Any month\">Any month</option>\n"); 
$list=12;
$first=1;
while($list>=$first){
	$listM=date ("M", mktime(0,0,0,$list,1,2000));
	echo ("<option value=\"".$list."\">".$listM."</option>\n");
	$list-=1; 
}		
 echo("</select><select class='form_control' name=renewyear><option value=\"Any year\">Any year</option>\n");
$listyear=$thisyear+1;
//$firstyear=$thisyear;
$firstyear="2001";
while($listyear>=$firstyear){
	echo ("<option value=\"".$listyear."\">".$listyear."</option>\n");
$listyear-=1; 
}
echo("</select>");




?>
&nbsp;
</fieldset></td>

</tr>
	
</tr>
<tr>
<td colspan=2><fieldset>
<legend>Date ceased</legend>
<input class='form_control' type='button' name='Go' onClick='cleardates(3)' value='Clear'> 
<?php
$datenow=date("Y-m-d",time());
list ($thisyear, $thismonth, $thisday) = explode ('-', $datenow);
$datenowdisplay=date ("d M Y", time());
echo("<select class='form_control' name=exitmonth><option selected value=\"Any month\">Any month</option>\n"); 
$list=12;
$first=1;
while($list>=$first){
	$listM=date ("M", mktime(0,0,0,$list,1,2000));
	echo ("<option value=\"".$list."\">".$listM."</option>\n");
	$list-=1; 
}		
 echo("</select><select class='form_control' name=exityear><option value=\"Any year\">Any year</option>\n");
$listyear=$thisyear+1;
//$firstyear=$thisyear;
$firstyear="2012";
while($listyear>=$firstyear){
	echo ("<option value=\"".$listyear."\">".$listyear."</option>\n");
$listyear-=1; 
}
echo("</select>");




?>
&nbsp;
</fieldset></td>

</tr>
<tr bgcolor="#FFFF00"> 
    
<td colspan='2'><b>SELECT PROFILE PREFERENCE 'adminprivacy' in the last box below for ALL searches other than contact relating to membership
 </b>

</tr>
<tr valign="bottom"> 
    
<td><b>Click to add Targetting Choices</b></td>
<td><b>Your 
      Choices - click to remove</b></td>
      
</tr>
<tr valign="bottom"> 
    
<td valign="top">
<fieldset>
<legend>Member Type</legend>
      
<select style="width: 90% !important; min-width: 90%; max-width: 90%;" multiple size="4" name="Criteria5" onClick="move_choice(this.form.Criteria5,this.form.Criteria5c)">
         
<option value='1'>Single within Europe</option>
<option value='2'>Family within Europe</option>
<option value='3'>Single outside Europe</option>
<option value='4'>Family outside Europe</option>
<option value='5'>Honarary</option>
<option value='6'>Press</option>
<option value='7'>Voucher</option>
</select>
    </fieldset></td>
<td valign="top">
<fieldset>
<legend>Member Type</legend>
      
<select style="width: 90% !important; min-width: 90%; max-width: 90%;" multiple size="4" name="Criteria5c" onClick="drop(this.form.Criteria5c)">
</select>
</fieldset></td>

</tr>
<tr valign="bottom"> 
    
<td valign="top">
<fieldset>
<legend>Situation</legend>
      
<select style="width: 90% !important; min-width: 90%; max-width: 90%;" multiple size="4" name="Criteria0" onClick="move_choice(this.form.Criteria0,this.form.Criteria0c)">
         
<option value='0'>Unknown</option>
<option value='1'>Looking for a barge</option>
<option value='2'>Interest only</option>
<option value='3'>Commercial</option>
<option value='4'>Owner</option>
<option value='5'>Owner shared</option>
</select>
    </fieldset></td>
<td valign="top">
<fieldset>
<legend>Situation</legend>
      
<select style="width: 90% !important; min-width: 90%; max-width: 90%;" multiple size="4" name="Criteria0c" onClick="drop(this.form.Criteria0c)">
</select>
</fieldset></td>

</tr>
  
<tr valign="bottom"> 
    
<td>
<fieldset>
<legend>Location</legend>
      
<select style="width: 90% !important; min-width: 90%; max-width: 90%;" multiple size="5" name="Criteria4" onClick="move_choice(this.form.Criteria4,this.form.Criteria4c)">
<option value=' '>Blank - unknown</option>
<?php
$query = $db->getQuery(true)
	->select($db->qn(['c.iso', 'c.printable_name']))
	->from($db->qn('tblCountry', 'c'))
	->innerJoin($db->qn('tblMembers', 'm').' ON '.$db->qn('c.iso').' = '.$db->qn('m.CountryCodeCruising'))
	->group($db->qn(['c.iso', 'c.printable_name']))
	->order($db->qn('c.printable_name'));
$results = $db->setQuery($query)->loadObjectList(); 

if(count($results)) {
	$clist.="<option value=\"0_0_0\">Select a country</option>\n";
	//$clist.="<option value=\"GB_UK_United Kingdom\">United Kingdom</option>\n";
	foreach($results as $row) {
		echo("<option value='" . $row->iso . "'>" . $row->printable_name . "</option>\n");
	}
}

?>
</select>
</fieldset></td>
<td>
<fieldset>
<legend>Location</legend>
      
<select style="width: 90% !important; min-width: 90%; max-width: 90%;" multiple size="5" name="Criteria4c" onClick="drop(this.form.Criteria4c)">
</select>
</fieldset></td>

</tr>
 
 

<tr valign="bottom"> 
<td>
<fieldset>
<legend>Barge Location (main cruising area)</legend>
      
<select style="width: 90% !important; min-width: 90%; max-width: 90%;" multiple size="5" name="Criteria44" onClick="move_choice(this.form.Criteria44,this.form.Criteria44c)">
<option value=' '>Blank - unknown</option>
<?php
$query = $db->getQuery(true)
	->select($db->qn(['c.iso', 'c.printable_name']))
	->from($db->qn('tblCountry', 'c'))
	->innerJoin($db->qn('tblMembers', 'm').' ON '.$db->qn('c.iso').' = '.$db->qn('m.CountryCodeCruising'))
	->group($db->qn(['c.iso', 'c.printable_name']))
	->order($db->qn('c.printable_name'));
$results = $db->setQuery($query)->loadObjectList(); 
if(count($results)) {
	$clist.="<option value=\"0_0_0\">Select a country</option>\n";
	foreach($results as $row) {
		echo("<option value='" . $row->iso . "'>" . $row->printable_name . "</option>\n");
	}
}

?>
</select>
</fieldset></td>
<td>
<fieldset>
<legend>Barge Location</legend>
      
<select style="width: 90% !important; min-width: 90%; max-width: 90%;" multiple size="5" name="Criteria44c" onClick="drop(this.form.Criteria44c)">
</select>
</fieldset></td>
</tr>


<tr valign="bottom"> 
    
<td>
<fieldset>
<legend>Post Zone</legend>
<select style="width: 90% !important; min-width: 90%; max-width: 90%;" multiple size="4" name="Criteria1" onClick="move_choice(this.form.Criteria1,this.form.Criteria1c)">
  <option value='UK'>UK</option>
  <option value='EU'>EU (excl UK)</option>
  <option value='Z1'>Outside EU Z1</option>
  <option value='Z2'>Outside EU Z2</option>
</select>
</fieldset></td>
<td>
<fieldset>
<legend>Post Zone</legend>
<select style="width: 90% !important; min-width: 90%; max-width: 90%;" multiple size="4" name="Criteria1c" onClick="drop(this.form.Criteria1c)">
</select>
</fieldset></td>

</tr>
  
<tr valign="bottom"> 
    
<td>
<fieldset>
<legend>Status</legend>
      
<select style="width: 90% !important; min-width: 90%; max-width: 90%;" multiple size="5" name="Criteria2" onClick="move_choice(this.form.Criteria2,this.form.Criteria2c)">
<option value='1'>Applied pending payment</option>
<option value='2'>Paid up</option>
<option value='3'>Renewal overdue</option>
<option value='4'>Gone away</option>
<option value='7'>Set to terminate</option>
<option value='5'>Terminated</option>
<option value='6'>Complimentary</option>
</select>
</fieldset></td>
<td>
<fieldset>
<legend>Status</legend>
      
<select style="width: 90% !important; min-width: 90%; max-width: 90%;" multiple size="5" name="Criteria2c" onClick="drop(this.form.Criteria2c)">
</select>
</fieldset></td>

</tr>
<tr valign="bottom"> 
    
<td>
<fieldset>
<legend>Payment Method</legend>
      
<select style="width: 90% !important; min-width: 90%; max-width: 90%;" name="Criteria6" size="5" multiple id="Criteria6" onClick="move_choice(this.form.Criteria6,this.form.Criteria6c)">
<option value='ch'>Cheque / Cash</option>
<option value='so'>Standing Order</option>
<option value='cc'>Credit / debit card</option>
<option value='dd'>Direct Debit</option>
<option value='foc'>Free of Charge</option>
</select>
</fieldset></td>
<td>
<fieldset>
<legend>Payment Method</legend>
      
<select style="width: 90% !important; min-width: 90%; max-width: 90%;" name="Criteria6c" size="5" multiple id="Criteria6c" onClick="drop(this.form.Criteria6c)">
</select>
</fieldset></td>

</tr>
<tr valign="bottom"> 
    
<td>
<fieldset>
<legend>Groups</legend>
      
<select style="width: 90% !important; min-width: 90%; max-width: 90%;" name="Criteria7" size="5" multiple id="Criteria7" onClick="move_choice(this.form.Criteria7,this.form.Criteria7c)">
<?php
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblGroupType'))
	->order($db->qn('GroupDesc'));
$results = $db->setQuery($query)->loadObjectList(); 			
if(count($results)) {
	//Make the dropdown
	foreach($results as $row) {
		$thiscode=$row->GroupID;
		$thisdesc=$row->GroupDesc;
		if($GroupID==$thiscode){$sel="selected ";}else{$sel="";}
		echo("<option $sel value='$thiscode'>$thisdesc</option>\n");
	}
}


			?>
</select>
</fieldset></td>
<td>
<fieldset>
<legend>Groups</legend>
      
<select style="width: 90% !important; min-width: 90%; max-width: 90%;" name="Criteria7c" size="5" multiple id="Criteria7c" onClick="drop(this.form.Criteria7c)">
</select>
</fieldset></td>

</tr>


<tr valign="bottom"> 
    
<td>
<fieldset>
<legend>Profile preferences</legend>
      
<select style="width: 90% !important; min-width: 90%; max-width: 90%;" name="Criteria8" size="10" multiple id="Criteria8" onClick="move_choice(this.form.Criteria8,this.form.Criteria8c)">
<?php
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblServices'))
	->where($db->qn('ServiceCategory').' != '.$db->q('mooringsguides'))
	->where($db->qn('ServiceCategory').' != '.$db->q('warningguides'))
	->order($db->qn(['ServiceCategory', 'ServiceSortOrder']));
$results = $db->setQuery($query)->loadObjectList(); 			
			
if(count($results)) {
	//Make the dropdown
	foreach($results as $row) {
		$thiscode=$row->ServiceID;
		$thisdesc=substr($row->ServiceCategory." - ".$row->ServiceDescGB, 0, 55);
		if($GroupID==$thiscode){$sel="selected ";}else{$sel="";}
		echo("<option $sel value='$thiscode'>$thisdesc</option>\n");
	}
}

?>
</select>
</fieldset></td>
<td>
<fieldset>
<legend>Profile preferences <input name="Criteria8cInv" type="checkbox" value="1">
Invert</legend>
<select style="width: 90% !important; min-width: 90%; max-width: 90%;" name="Criteria8c" size="10" multiple id="Criteria8c" onClick="drop(this.form.Criteria8c)">
</select>
</fieldset></td></tr>
</table>
</form>
</body>
</html>