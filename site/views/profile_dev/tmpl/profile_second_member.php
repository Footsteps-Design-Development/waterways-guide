<?php
/**
 * @version     1.0.0
 * @package     com_membership
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Chris Grant www.productif.co.uk
 * include file for second family member profile update
 */
 
// no direct access
	

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$introtext="Hello . . . instructions";
$errormessage="";
$screenmessage="";
$mainmember="";
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\User\UserHelper;

//include(dirname(__FILE__)."/form_filters.php")

//get form type params from menu item to decide new or existing user
$app = Factory::getApplication('com_membership');

//check access level of user
$user = Factory::getUser();
$componentpath="/components/com_membership/views/profile/tmpl/";


$userGroups = $user->getAuthorisedGroups();

if (in_array("8", $userGroups) || in_array("22", $userGroups)) {
    $membershipadmin=true; //superuser or membershipadmin
}else{
	$membershipadmin=false;
}





	
$myparams = $app->getTemplate(true)->params;
$id=$myparams->get('id');



function is_matched($text){
   return preg_match("/\b(\w+)\s+(\\1)\b/i", $text);
}

function replace($text){
   return preg_replace("/\b(\w+)\s+(\\1)\b/i", "\\1", $text);
}

function createRandomPassword() {    
	$chars = "abcdefghijkmnopqrstuvwxyz23456789";
	srand((double)microtime()*1000000);
	$i = 0;    $pass = '' ;
	while ($i <= 7) {
		$num = rand() % 33;
		$tmp = substr($chars, $num, 1);
		$pass = $pass . $tmp;
		$i++;
	}    
	return $pass;
}

	
$db = Factory::getDBO();
require_once(JPATH_COMPONENT_SITE."/commonV3.php");

$test_vars=(array(
'ID',
'Title2',
'$errormessage',
'FirstName2', 
'LastName2', 
'Email',
'Email2',
'ID2',
'Telephone2',
'Mobile2',
'Services', 
'userid',
'Login',
'Login2',
'PW2',
'table',
'subaction',
'Initials2',
'num_services'
));
foreach($test_vars as $test_var) { 
	if(!$$test_var =  $app->input->getString($test_var)){
		$$test_var = "";
	}
}
	
	
if(!$userid){
	//logged in user is using this form, not admin
	$userid = $user->id;
}

if(isset($table) && $table=="archive"){
	$memtable="tblMembers_archive";
}else{
	$memtable="tblMembers";
}
	

if ($subaction=="subscribe2" || $subaction=="Update" || $subaction=="Approve") {


	//validate
	$screenmessage="";
	//check if this is the right member id
	if (!$userid){
		$errormessage="<br> - with updating your member information. Please go to the <a href=\"".$memberloginurl."\">main login page</a> to try again or retrieve your password";
		$subaction="back";
	}else{

		//check username is unique
		if($Login2){
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__users'))
				->where($db->qn('username').' = '.$db->q($Login2));
			$result = $db->getQuery($query)->loadAssocList();
			$num_rows = count($result);
			if($num_rows > 0){
				//already exists

				//check if ID is same
				$row = reset($result);
				$id=$row["id"];
				if($id!=$ID2){
					//trying to use username of another profile
					//Is it main member email or another member?
					if($Login==$Login2){
						$errormessage.="<br> - the login user name '".$Login2."' is the same as the main member.";	
						$subaction="back";	
					}else{
						$errormessage.="<br> - the login user name '".$Login2."' is already being used on another account.";	
						$subaction="back";
					}
				}

			}else{
				//$errormessage="email unique";
			}
		}
		
		//check email is unique
		if(!empty($ID2) && !empty($Email2)){
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__users'))
				->where($db->qn('email').' = '.$db->q($Email2));
			$result = $db->setQuery($query)->loadAssocList();
			$num_rows = count($result);
			if($num_rows>0){
				//already exists

				//check if ID is same
				$row = reset($result);
				$id=$row["id"];
				if($id!=$ID2){
					//trying to use emailaddress of another profile
					//Is it main member email or another member?
					if($Email==$Email2){
						$errormessage="<br> - the email address '".$Email2."' is the same as the main member.";	
						$subaction="back";	
					}else{
						$errormessage="<br> - the email address '".$Email2."' is already being used on another account.";	
						$subaction="back";
					}
				}

			}else{
				//$errormessage="email unique";
			}
		}
	}

	
	if($subaction!="back"){


		

		//$message.=$changes;
		//check account exists
		if (!$userid){
			$errormessage.="<br> - with updating your member information. Please go to the <a href=\"".$memberloginurl."\">main login page</a> to try again or retrieve your password";
			$subaction="back";
		}else{
			// lookup main member id to use when storing changes
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn($memtable))
				->where($db->q('ID2').' = '.$db->q($userid));
			$row = $db->setQuery($query)->loadObject();
			if($row) {
				//update keywords
				$keywords="";
				$keywords.=" ".$row->FirstName;
				$keywords.=" ".$row->LastName;
				$keywords.=" ".$row->Email;
				$keywords.=" ".$row->Address1;
				$keywords.=" ".$row->Address2;
				$keywords.=" ".$row->Address3;
				$keywords.=" ".$row->Address4;
				$keywords.=" ".$row->PostCode;
				$keywords.=" ".$row->Country;
				$keywords.=" ".$row->CountryCode;
				$keywords.=" ".$row->MemNo;
				$keywords.=" ".$row->MembershipNo;
				$keywords.=" ".$row->Login;
				//add in member2
				$keywords.=" ".$FirstName2;
				$keywords.=" ".$LastName2;
				$keywords.=" ".$Email2;
				$keywords.=" ".$Login2;
				//change to lower case
				$keywords=strtolower($keywords);
				//remove dup words
				$text=$keywords;
				while(is_matched($text)){
					$text = replace($text);
				}		
				$changes="";
				
				//update member record
				$changedate=date("Y-m-d H:i:s");
				$datenowdisplay=date("d/m/Y");
				$LastUpdate = "$changedate";
				$update = new \stdClass();
				$update->Title2 = addslashes($Title2);
				$update->FirstName2 = addslashes($FirstName2);
				$update->LastName2 = addslashes($LastName2);
				$update->Email2 = addslashes($Email2);
				$update->Mobile2 = addslashes($Mobile2);
				$update->Services = $Services;
				$update->LastUpdate = $changedate;
				$update->Keywords = addslashes($keywords);
				$update->ID2 = $userid;
				$db->updateObject($memtable, $update, 'ID2');
				$screenmessage.="\n\nYour profile changes have been saved.";
				
				//update #__users table
				if($FirstName2){
					$fullname2=$FirstName2;
				}else{
					$fullname2="";
				}
				if($LastName2){
					if($fullname2){
						$fullname2.=" ".$LastName2;
					}else{
						$fullname2.=$LastName2;
					}
				}
				
				//generate encrypted pw if changed
				
				$update = new \stdClass();
				$update->name = addslashes($fullname2);
				$update->username = addslashes($Login2);
				$update->email = addslashes($Email2);
				if($PW2!=""){
					//pw changed
					// $salt = UserHelper::genRandomPassword(32);
					// $crypt = UserHelper::getCryptedPassword($PW2, $salt);
					// $password2 = $crypt . ':' . $salt;
					$password2 = UserHelper::hashPassword($PW2);
					$update->password = $password2;
				}
				$update->id = $userid;
				$db->updateObject('#__users', $update, 'id');

				$subject="Profile update";
				$changelogtext="";
				//$screenmessage.="\n\nProfile changes have been saved in jos)users.".$query;

				if($changes){
					$changelogtext.=$changes;
					//update change log if not admin change
					$insert = new \stdClass();
					$insert->MemberID = $ID;
					$insert->Subject = $subject;
					$insert->ChangeDesc = $changelogtext;
					$insert->ChangeDate = $changedate;
					// $db->insertObject('tblChangeLog', $insert) or die ("Couldn't update change log");
				}
			}
		}

	}
}

?>

<style type="text/css" media="screen,projection">


.formtextarea {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 95%;
	font-weight: lighter;
	height:120px;
	width:100%;
}

</style>
<h2>My Details - second family member</h2>
	<?php
	
	if($errormessage){
		echo("<br><font color=#ff0000><b>There was a problem:".nl2br($errormessage)."</b></font>");
	}	
	if(!$screenmessage){
		$screenmessage="To save any changes, click the 'Update' button at the end of the form.";
	}
	//echo($introtext);
	echo("<br>".nl2br($screenmessage));
	?>

<form name="form" enctype="multipart/form-data" method="post">
<?php

//Form display *******************************************************************************
if(!$subaction || $subaction=="back"){


	
	// lookup main member id to use when storing changes
	if (!$user->guest && $userid!="new") {
		//existing user so get details
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn($memtable))
			->where($db->qn('ID2').' = '.$db->q($userid));
		$row = $db->setQuery($query)->loadObject();
		if(count($row)) {
			//$row=$db->loadRow();
			$ID = stripslashes($row->ID); //main member ID
			$Login = stripslashes($row->Login);
			$Email = stripslashes($row->Email); 
			$Title = stripslashes($row->Title); 
			$FirstName = stripslashes($row->FirstName); 
			$LastName = stripslashes($row->LastName); 
			$Title2 = stripslashes($row->Title2); 
			$FirstName2 = stripslashes($row->FirstName2); 
			$LastName2 = stripslashes($row->LastName2); 

			
			//lookup email and login in #__users
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__users'))
				->where($db->qn('id').' = '.$db->q($userid));
			$partner = $db->setQuery($query)->loadObject();
			if(count($partner)) {
				$Email2 = stripslashes($partner->email);
				$Login2 = stripslashes($partner->username);  
			}else{
				//no email
				$Email2="";
				$Login2 = "?"; 
			}
			$Mobile2 = $row->Mobile2;
			$Services = $row->Services; 
			$Keywords = stripslashes($row->Keywords); 
			$MembershipNo = $row->MembershipNo; 
			$MemNo = $row->MemNo; 
			$DateJoined = $row->DateJoined;
			if($DateJoined){
				$datejoineddisplay=date_to_format($DateJoined,"d") ;
			}
			$contact=$FirstName." ".$LastName;
			

			$mainmember.="<br>Main member is: ".$contact.", membership number ".$MembershipNo." since ".$datejoineddisplay;



		}
		//$introtext.=" substatus=$substatus memstatus=$MemStatus daystorenew=$daystorenew error=$error datelastpaid=$DatePaid";
	}else{
		$go2value="Update";
	}


	echo("<input type=\"hidden\"' name=\"userid\" value=\"".$userid."\">\n");
	echo("<input type=\"hidden\"' name=\"ID\" value=\"".$ID."\">\n");
	echo("<input type=\"hidden\"' name=\"Login\" value=\"".$Login."\">\n");
	echo("<input type=\"hidden\"' name=\"Email\" value=\"".$Email."\">\n");
	echo("<input type=\"hidden\"' name=\"ID2\" value=\"".$userid."\">\n");
	echo("<input type=\"hidden\" name=\"subaction\" value=\"".$subaction."\">\n");
	echo("<input type=\"hidden\"' name=\"Services\" value=\"".$Services."\">\n");
	echo("<input type=\"hidden\"' name=\"MemNo\" value=\"".$MemNo."\">\n");
	echo("<input type=\"hidden\"' name=\"MembershipNo\" value=\"".$MembershipNo."\">\n");

	?>

	<SCRIPT LANGUAGE="JavaScript">
	<!--


	function Help(HelpID){
		var mypage = "help.php?FormEntryID\=" + HelpID;
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

	function MM_validateForm() { //v3.0

		var errors="";
		
		var user_login2 = document.form.Login2.value;
		var user_pw2 = document.form.PW2.value;
		var user_title2 = document.form.Title2.value;
		var user_forename2 = document.form.FirstName2.value;
		var user_familyname2 = document.form.LastName2.value;
		var user_email2 = document.form.Email2.value;
		
		if(user_login2 == ""){
			errors+='- login user name\n';
			document.form.Login2.style.backgroundColor ="#ffff00";
		}else{
			cd=user_login2;
			if ((cd.indexOf("\#") != -1) || 
				(cd.indexOf("\%") != -1) || 
				(cd.indexOf("\&") != -1) || 
				(cd.indexOf("\=") != -1) || 
				(cd.indexOf("\,") != -1) || 
				(cd.indexOf("\?") != -1) || 
				(cd.indexOf("\:") != -1) || 
				(cd.indexOf("\;") != -1) || 
				(cd.indexOf("\'") != -1) || 
				(cd.indexOf("\"") != -1) || 
				(cd.indexOf("\[") != -1) || 
				(cd.indexOf("\]") != -1) || 
				(cd.indexOf("\{") != -1) ||
				(cd.indexOf(" ") != -1) ||  
				(cd.indexOf("\}") != -1)){
				errors+='- your login user name must only contain standard characters or numbers\n';
				document.form.Login2.style.backgroundColor ="#ffff00";
			}else{
				document.form.Login2.style.backgroundColor ="#ffffff";
			}
		}
		if (user_pw2!="" && user_pw2.length < 6){
			errors+='- password should be at least 6 characters\n';
			document.form.PW2.style.backgroundColor ="#ffff00";
		}else if (user_pw2!=""){
			cd=user_pw2;
			if ((cd.indexOf("\#") != -1) || 
				(cd.indexOf("\%") != -1) || 
				(cd.indexOf("\&") != -1) || 
				(cd.indexOf("\=") != -1) || 
				(cd.indexOf("\,") != -1) || 
				(cd.indexOf("\?") != -1) || 
				(cd.indexOf("\:") != -1) || 
				(cd.indexOf("\;") != -1) || 
				(cd.indexOf("\'") != -1) || 
				(cd.indexOf("\"") != -1) || 
				(cd.indexOf("\[") != -1) || 
				(cd.indexOf("\]") != -1) || 
				(cd.indexOf("\{") != -1) || 
				(cd.indexOf(" ") != -1) || 
				(cd.indexOf("\}") != -1)){
				errors+='- the password must only contain standard characters or numbers and no spaces\n';
				document.form.PW2.style.backgroundColor ="#ffff00";
			}else{
				document.form.PW2.style.backgroundColor ="#ffffff";
			}
		}
		if (/^\s/.test(user_title2) || user_title2 == ""){
			errors+='- second member title Mr, Mrs, Ms etc\n';
			document.form.Title2.style.backgroundColor ="#ffff00";
		}else{
			document.form.Title2.style.backgroundColor ="#ffffff";
		}
		if (/^\s/.test(user_forename2) || user_forename2 == ""){
			errors+='- second member forename\n';
			document.form.FirstName2.style.backgroundColor ="#ffff00";
		}else{
			document.form.FirstName2.style.backgroundColor ="#ffffff";
		}
		if (/^\s/.test(user_familyname2) || user_familyname2 == ""){
			errors+='- second member family name\n';
			document.form.LastName2.style.backgroundColor ="#ffff00";
		}else{
			document.form.LastName2.style.backgroundColor ="#ffffff";
		}
		if (/^\s/.test(user_email2) || user_email2 == ""){
			errors+='- email address\n';
			document.form.Email2.style.backgroundColor ="#ffff00";
		}else{
			if(!/^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/.test(user_email2)) {
				errors+='- email does not seem to be a valid e-mail address.\n';
				document.form.Email2.style.backgroundColor ="#ffff00";
			} else {
				document.form.Email2.style.backgroundColor ="#ffffff";
			}
			// p=user_email2.indexOf('@');
			// if (p<1 || p==(user_email2.length-1)) {
				// errors+='- email does not seem to be a valid e-mail address.\n';
				// document.form.Email2.style.backgroundColor ="#ffff00";
			// }else{
				// cd=user_email2;
				// if ((cd.indexOf("\#") != -1) || 
					// (cd.indexOf("\%") != -1) || 
					// (cd.indexOf("\&") != -1) || 
					// (cd.indexOf("\=") != -1) || 
					// (cd.indexOf("\,") != -1) || 
					// (cd.indexOf("\?") != -1) || 
					// (cd.indexOf("\:") != -1) || 
					// (cd.indexOf("\;") != -1) || 
					// (cd.indexOf("\'") != -1) || 
					// (cd.indexOf("\"") != -1) || 
					// (cd.indexOf("\[") != -1) || 
					// (cd.indexOf("\]") != -1) || 
					// (cd.indexOf("\{") != -1) || 
					// (cd.indexOf(" ") != -1) || 
					// (cd.indexOf("\}") != -1)){
					// errors+='- your email must only contain standard characters or numbers\n';
					// document.form.Email2.style.backgroundColor ="#ffff00";
				// }else{
					// document.form.Email2.style.backgroundColor ="#ffffff";
				// }
			// }
		}

		if (errors) {
			alert('Please check the highlighted entries and try again:\n'+errors);
		}else{
			document.form.subaction.value='subscribe2';
			document.form.submit()
			//alert("submit");
		}		
	}


	//-->
	</script>

	<style type="text/css" media="screen,projection">
		td.profile_label{
			width:300px;
			background-color:#CCEEF7;
			border: 1px;
			padding-top: 2px;
			padding-bottom: 2px;
			padding-right: 2px;
			padding-left: 4px;
			vertical-align:top
		}
		td.profile_gdpr{
			width:300px;
			background-color:#FFFF00;
			border: 1px;
			padding-top: 2px;
			padding-bottom: 2px;
			padding-right: 2px;
			padding-left: 4px;
			vertical-align:top
		}
		td.profile_field{
			background-color:#CCEEF7;
			padding-top: 2px;
			padding-bottom: 2px;
			padding-right: 2px;
			padding-left: 2px;
		}

	</style>
	<?php echo($mainmember); ?>
	<table border="0" cellpadding="3" cellspacing="2" width="100%">



	<tr> 			  
		<td colspan="2">&nbsp;</td>
	</tr>			
		<td width="30%" class=profile_label>Login user name <em>* </em></td>
		<td width="70%" class=profile_field> <input class=formcontrol type='text' name='Login2' size='30' value="<?php echo($Login2); ?>"> 
		  <em>excluding &lt;&gt;\&quot;'%;()&amp;</em></td>
	</tr>

	<tr> 

		<td class=profile_label>Password <em>* (single word 
						alphanumeric) </em></td>
		<td class=profile_field> <input class=formcontrol type='password' name='PW2' placeholder='Leave blank to keep existing' size='30' value="">                 </td>
	</tr>
	<tr id="title2">                  
		<td class=profile_label>
		Title <em>* (Mr, Mrs, Ms etc) </em></td>
		<td class=profile_field> <input class=formcontrol type='text' name='Title2' size='30' value="<?php echo($Title2); ?>">

		</td>
	</tr>
	<tr id="forename2">                 
		<td class=profile_label>Forename *</td>
		<td class=profile_field> <input class=formcontrol type='text' name='FirstName2' size='30' value="<?php echo($FirstName2); ?>"></td>
	</tr>
		<tr id="familyname2">                 
		<td class=profile_label>Family name <em>*</em></td>
		<td class=profile_field> <input class=formcontrol type='text' name='LastName2' size='30' value="<?php echo($LastName2); ?>"></td>
	</tr><tr id="email2">                 
		<td class=profile_label>Email address <em>*</em></td>
		<td class=profile_field><input class=formcontrol type='text' name='Email2' size='30' value="<?php echo($Email2); ?>" />

		<?php
		//if(!empty($ID2)){
			echo("<em>must be different from main member</em>");
		//}
		?>

		</td>
	</tr>
 	<tr id="mobile2"> 			  
		<td class=profile_label>Tel mobile:</td>
		<td class=profile_field> <input class=formcontrol type='text' name='Mobile2' size='30' value="<?php echo($Mobile2); ?>">         </td>
	</tr>
	<tr> 			  
		<td colspan="2"><strong>Options</strong> Tick  boxes for the  following or untick to cancel.</td>
		</tr>

	<?php
	//contact privacy settings


	//GDPR settings second member (DBA)
	$servicetitle="To give you more control and to meet General Data Protection Regulations, choose emails you would like from us:";
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblServices'))
		->where($db->qn('ServiceCategory').' = '.$db->q('GDPR2'))
		->order($db->qn('ServiceSortOrder'));
	$sections = $db->setQuery($query)->loadObjectList(); 
	$boxes="";
	$num_services+= count($sections);
	foreach($sections as $sectionrow) {
		$serviceid=$sectionrow->ServiceID;
		$servicedesc=$sectionrow->ServiceDescGB." ".$sectionrow->ServiceHelpGB;

		$found = strstr ($Services, "|".$serviceid."|");
		if(!$found){
			$boxes.="<input type=\"checkbox\" name=\"service".$serviceid."\" value=\"".$serviceid."\" onClick=\"servicetype(this,".$serviceid.",".$num_services.")\"> ".$servicedesc."<br />\n";
		}else{
			$boxes.="<input type=\"checkbox\" name=\"service".$serviceid."\" value=\"".$serviceid."\" checked onClick=\"servicetype(this,".$serviceid.",".$num_services.")\"> ".$servicedesc."<br />\n";
		}
	}
	echo("<tr><td colspan=2>".$servicetitle."</td></tr><tr><td class=profile_gdpr valign=\"top\"></td><td class=profile_gdpr valign=\"top\">".$boxes."</td></tr>\n");
	//GDPR settings second member (other members)
	$servicetitle="Would you like to allow contact from other members through the Member Finder facility:";
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblServices'))
		->where($db->qn('ServiceCategory').' = '.$db->q('GDPR2_mem'))
		->order($db->qn('ServiceSortOrder'));
	$sections = $db->setQuery($query)->loadObjectList(); 
	$boxes="";
	$num_services+= count($sections);
	foreach($sections as $sectionrow) {
		$serviceid=$sectionrow->ServiceID;
		$servicedesc=$sectionrow->ServiceDescGB." ".$sectionrow->ServiceHelpGB;

		$found = strstr ($Services, "|".$serviceid."|");
		if(!$found){
			$boxes.="<input type=\"checkbox\" name=\"service".$serviceid."\" value=\"".$serviceid."\" onClick=\"servicetype(this,".$serviceid.",".$num_services.")\"> ".$servicedesc."<br />\n";
		}else{
			$boxes.="<input type=\"checkbox\" name=\"service".$serviceid."\" value=\"".$serviceid."\" checked onClick=\"servicetype(this,".$serviceid.",".$num_services.")\"> ".$servicedesc."<br />\n";
		}
	}
	echo("<tr><td colspan=2>".$servicetitle."</td></tr><tr><td class=profile_gdpr valign=\"top\"></td><td class=profile_gdpr valign=\"top\">".$boxes."</td></tr>\n");



	if(!$subaction || $subaction=="back"){
		?>
		<tr>
		
			<td colspan="2" class=bodytext><input type="button" class="btn btn-primary button_action" name="go2" value="Update" onClick="MM_validateForm();"></td>
			
			
		</tr>	
		<?php
	}

	?>
	</table>


	<SCRIPT LANGUAGE="JavaScript">
		//hide option entry fields on startup or if current selection requires
		
	function servicetype(cbname,servicecode,num_services){
		//override num_services
		var num_services=70;
		var form='form'; 
		var dml=document.forms[form];
		var s=0;
		var onload_services='<?php echo($Services); ?>';
		var cur_service_sel=document.form.Services.value;
		var new_service_sel="|";
		var state = cbname.checked;
		var userid = document.form.userid.value;;
		var oktoupdate=1;
		var admin=<?php if($membershipadmin==true){echo("1");}else{echo("0");} ?>;
				
		if(oktoupdate==1){
			for(s=0; s<=num_services; s++){
				if((cur_service_sel.indexOf("|"+s+"|")==-1) && (servicecode != s)){
					//ignore it
				}else{
					if((cur_service_sel.indexOf("|"+s+"|")!=-1) && (servicecode != s)){
						//keep it
						new_service_sel+=s+"|";
					}else{
						if((cur_service_sel.indexOf("|"+s+"|")==-1) && (servicecode == s)){
							if(state==1){
								//add it
								new_service_sel+=s+"|";
							}
						}
					}
				}
			}
			//alert(cur_service_sel+" "+new_service_sel);
			//var myDiv = document.getElementById("debug");
  			//myDiv.innerHTML = "Debug"+" - Old service:"+cur_service_sel+" New service:"+new_service_sel;
			
			cur_service_sel=new_service_sel;
			document.form.Services.value=cur_service_sel;
		}
	
	}
	

	</script>
	</form>
    
<?php
}
//echo("s".$subaction);
?>
<div id="debug"></div>
