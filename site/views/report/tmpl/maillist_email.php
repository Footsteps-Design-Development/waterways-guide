<?php

//load Joomla helpers
define( '_JEXEC', 1 );
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', strstr(__DIR__, 'public_html', true).'public_html');
define('JPATH_COMPONENT', JPATH_BASE .DS.'components'.DS.'com_waterways_guide');
require_once(JPATH_BASE .DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE .DS.'includes'.DS.'framework.php');
//require_once(JPATH_BASE .DS.'libraries/joomla/user/helper.php');
//require_once(JPATH_BASE .DS.'libraries/joomla/factory.php' );
require_once(JPATH_COMPONENT .DS.'commonV3.php');

use Joomla\CMS\Factory;
$config = Factory::getConfig();
$mailOn = Factory::getConfig()->get('mailonline') == '1';

getpost_ifset(array('login_email','login_name','message','subject','from','send','table','wheresql','maillist','criteria','status','sort','RecipientsSelectM','RecipientsSelectS','RecipientsSelectB','recipients','contactselected'));

$db =Factory::getDBO();

function fixMSWord($string) {
    $map = Array(
        '33' => '!', '34' => '"', '35' => '#', '36' => '$', '37' => '%', '38' => '&', '39' => "'", '40' => '(', '41' => ')', '42' => '*', 
        '43' => '+', '44' => ',', '45' => '-', '46' => '.', '47' => '/', '48' => '0', '49' => '1', '50' => '2', '51' => '3', '52' => '4', 
        '53' => '5', '54' => '6', '55' => '7', '56' => '8', '57' => '9', '58' => ':', '59' => ';', '60' => '<', '61' => '=', '62' => '>', 
        '63' => '?', '64' => '@', '65' => 'A', '66' => 'B', '67' => 'C', '68' => 'D', '69' => 'E', '70' => 'F', '71' => 'G', '72' => 'H', 
        '73' => 'I', '74' => 'J', '75' => 'K', '76' => 'L', '77' => 'M', '78' => 'N', '79' => 'O', '80' => 'P', '81' => 'Q', '82' => 'R', 
        '83' => 'S', '84' => 'T', '85' => 'U', '86' => 'V', '87' => 'W', '88' => 'X', '89' => 'Y', '90' => 'Z', '91' => '[', '92' => '\\', 
        '93' => ']', '94' => '^', '95' => '_', '96' => '`', '97' => 'a', '98' => 'b', '99' => 'c', '100'=> 'd', '101'=> 'e', '102'=> 'f', 
        '103'=> 'g', '104'=> 'h', '105'=> 'i', '106'=> 'j', '107'=> 'k', '108'=> 'l', '109'=> 'm', '110'=> 'n', '111'=> 'o', '112'=> 'p', 
        '113'=> 'q', '114'=> 'r', '115'=> 's', '116'=> 't', '117'=> 'u', '118'=> 'v', '119'=> 'w', '120'=> 'x', '121'=> 'y', '122'=> 'z', 
        '123'=> '{', '124'=> '|', '125'=> '}', '126'=> '~', '127'=> ' ', '128'=> '&#8364;', '129'=> ' ', '130'=> ',', '131'=> ' ', '132'=> '"', 
        '133'=> '.', '134'=> ' ', '135'=> ' ', '136'=> '^', '137'=> ' ', '138'=> ' ', '139'=> '<', '140'=> ' ', '141'=> ' ', '142'=> ' ', 
        '143'=> ' ', '144'=> ' ', '145'=> "'", '146'=> "'", '147'=> '"', '148'=> '"', '149'=> '.', '150'=> '-', '151'=> '-', '152'=> '~', 
        '153'=> ' ', '154'=> ' ', '155'=> '>', '156'=> ' ', '157'=> ' ', '158'=> ' ', '159'=> ' ', '160'=> ' ', '161'=> '¡', '162'=> '¢', 
        '163'=> '£', '164'=> '¤', '165'=> '¥', '166'=> '¦', '167'=> '§', '168'=> '¨', '169'=> '©', '170'=> 'ª', '171'=> '«', '172'=> '¬', 
        '173'=> '­', '174'=> '®', '175'=> '¯', '176'=> '°', '177'=> '±', '178'=> '²', '179'=> '³', '180'=> '´', '181'=> 'µ', '182'=> '¶', 
        '183'=> '·', '184'=> '¸', '185'=> '¹', '186'=> 'º', '187'=> '»', '188'=> '¼', '189'=> '½', '190'=> '¾', '191'=> '¿', '192'=> 'À', 
        '193'=> 'Á', '194'=> 'Â', '195'=> '£', '196'=> 'Ä', '197'=> 'Å', '198'=> 'Æ', '199'=> 'Ç', '200'=> 'È', '201'=> 'É', '202'=> 'Ê', 
        '203'=> 'Ë', '204'=> 'Ì', '205'=> 'Í', '206'=> 'Î', '207'=> 'Ï', '208'=> 'Ð', '209'=> 'Ñ', '210'=> 'Ò', '211'=> 'Ó', '212'=> 'Ô', 
        '213'=> 'Õ', '214'=> 'Ö', '215'=> '×', '216'=> 'Ø', '217'=> 'Ù', '218'=> 'Ú', '219'=> 'Û', '220'=> 'Ü', '221'=> 'Ý', '222'=> 'Þ', 
        '223'=> 'ß', '224'=> 'à', '225'=> 'á', '226'=> 'â', '227'=> 'ã', '228'=> 'ä', '229'=> 'å', '230'=> 'æ', '231'=> 'ç', '232'=> 'è', 
        '233'=> 'é', '234'=> 'ê', '235'=> 'ë', '236'=> 'ì', '237'=> 'í', '238'=> 'î', '239'=> 'ï', '240'=> 'ð', '241'=> 'ñ', '242'=> 'ò', 
        '243'=> 'ó', '244'=> 'ô', '245'=> 'õ', '246'=> 'ö', '247'=> '÷', '248'=> 'ø', '249'=> 'ù', '250'=> 'ú', '251'=> 'û', '252'=> 'ü', 
        '253'=> 'ý', '254'=> 'þ', '255'=> 'ÿ'
    );

    $search = Array();
    $replace = Array();

    foreach ($map as $s => $r) {
        $search[] = chr((int)$s);
        $replace[] = $r;
    }

    return str_replace($search, $replace, $string); 
}
	
?>
<html>
<head>
<title>Message Editor</title>
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
<form name="form" method="post" action="maillist_email.php">
<div class="pop_page_title"><h2>Contact Manager</h2></div>
<div class="pop_page_buttons"><a href="javascript:closeme()"><img src="../../../images/close.gif" width="18" height="18" alt="Close this window and return to search entry" border="0"><br>
        </a><a href="javascript:printWindow()"><img src="../../../images/print.gif" width="18" height="18" alt="Print this page" border="0"></a></div>
	
<table border="0" cellpadding="2" bgcolor="#FFFFFF" width="100%">
  <tr>
    <td colspan='2'> 
        
<h2>Message 
        editor
</h2></td>
    </tr>
<tr><td colspan="2"> 


<?php
if(!$login_email){
	//cant do as no admin email to send from
	echo("An administrator email address was not found. Please check that you are logged in as an administrator and that your profile email address is valid");
	//exit();	
}else{
	$from=$login_email;
	$DateNow=date("Y-m-d H:i:s");
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
			->where(stripslashes($wheresql))
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
	if($maxmembers==1){
		echo("<b>" . $Title . "</b><br><br>".$maxmembers. " record has been selected for messaging<br>\n");
	}else{
		echo("<b>" . $Title . "</b><br><br>".$maxmembers. " records have been selected for messaging<br>\n");	
	}
	
	//save variables for later
	echo("<input type='hidden' name='wheresql' value=\"".$wheresql."\">\n");
	echo("<input type='hidden' name='contactid' value=\"".(isset($contactid) ? $contactid : '')."\">\n");
	echo("<input type='hidden' name='sort' value=\"".$sort."\">\n");
	echo("<input type='hidden' name='criteria' value=\"".(isset($criteria) ? $criteria : '')."\">\n");
	echo("<input type='hidden' name='maillist' value=\"".$maillist."\">\n");
	echo("<input type='hidden' name='login_email' value=\"".$login_email."\">\n");
	echo("<input type='hidden' name='login_name' value=\"".$login_name."\">\n");
	//records found so ok to do email
	
	if(empty($recipients) || $recipients=="B"){
		$RecipientsSelectM="";
		$RecipientsSelectS="";
		$RecipientsSelectB=" checked";
		$recipients="B";
		$contactselected="Both members";
	}elseif($recipients=="M"){
		$RecipientsSelectM=" checked";
		$RecipientsSelectS="";
		$RecipientsSelectB="";
		$contactselected="Main member";
		$recipients="M";
	}elseif($recipients=="S"){
		$RecipientsSelectM="";
		$RecipientsSelectS=" checked";
		$RecipientsSelectB="";
		$contactselected="Second member";
		$recipients="S";
	}
	$missedlist="";
	
	if(empty($subject) || empty($message)){
		echo("<font color=#ff0000><b>Please check that you have a Subject and Message</b></font><br>"); 
	}else{
		$sendmessage=stripslashes($message."\n\n".$emailfooter);
		if (!$send || $send=="Test"){
			if($send=="Test"){
				//Test send to $emailfrom
				/*$thissubject=stripslashes(mb_convert_encoding(fixMSWord($subject), "UTF-8"));
				//Convert some bad characters e.g. â&#8364; added 20210128 CJG â&#8364; 
				
				$thissubject = str_replace("â&#8364", "'", $thismessage);
				$thissubject = str_replace("Â", "", $thismessage);
				$thissubject = str_replace("â€™", "'", $thissubject);
				$thissubject = str_replace("â€œ", '"', $thissubject);
				$thissubject = str_replace('â€“', '-', thissubject);
				$thissubject = str_replace('â€', '"', $thissubject);
								
				$thismessage=stripslashes(mb_convert_encoding(fixMSWord($sendmessage), "UTF-8"));
				//Convert some bad characters e.g. â&#8364; added 20210128 CJG
				$thismessage = str_replace("â&#8364", "'", $thismessage);
				$thismessage = str_replace("Â", "", $thismessage);
				$thismessage = str_replace("â€™", "'", $thismessage);
				$thismessage = str_replace("â€œ", '"', $thismessage);
				$thismessage = str_replace('â€“', '-', $thismessage);
				$thismessage = str_replace('â€', '"', $thismessage);
				
				*/
				$to=$from;
				$thismessage=$sendmessage;
				$content=$thismessage;
				
				if($mailOn) {
					$mailer = Factory::getMailer();		
					$mailer->setSender([$config->get('mailfrom'), $config->get('fromname')]);
					$mailer->addRecipient($to);
					$mailer->addReplyTo($from, $login_name);
					$mailer->setSubject($subject);
					$mailer->setBody(nl2br($content));
					$mailer->isHtml(true);
					$mailer->Send();
				} else echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";

				echo("<font color='ff0000'><b>Test email has been sent to ".$to."</b></font><br>");
			}
		}else{
			//dO EMAIL SHOT
			
			//set_time_limit(0);
			$addresslist="";
			$emailsent=0;
			$emaildups=0;
			$headers = "From: ".$from."\n";
			$members = explode ("_", $maillist);
			$thismemberid=0;
			$maxmembers=sizeof ($members)-2;
			$emailno=1;
			$notqueued=0;
			$queuedok=0;
			//check and replace dud apostrophe ’ and ms
			/*
			
					 
			$tryitnow = cleanuptext("sdsd “sdsdsdfdfssd” fdsds");
			echo $tryitnow;
			$ms_search = array(chr(145),chr(146),chr(147),chr(148),chr(151));
 			$ms_replace = array("'","'",'"','"','-');
			$tempmessage=str_replace($ms_search, $ms_replace, $thismessage);
			$thismessage=addslashes($tempmessage);
			$tempsubject=str_replace($ms_search, $ms_replace, $thissubject);
			$thissubject=addslashes($tempsubject);
			$thismessage=$sendmessage;
			$thissubject=stripslashes($subject);
			*/
			
			/*
			$thissubject=stripslashes(mb_convert_encoding(fixMSWord($subject), "UTF-8"));
			//Convert some bad characters e.g. â&#8364; added 20210128 CJG
			$thissubject = str_replace("â&#8364", "'", $thismessage);
			$thissubject = str_replace("Â", "", $thismessage);
			$thissubject = str_replace("â€™", "'", $thissubject);
			$thissubject = str_replace("â€œ", '"', $thissubject);
			$thissubject = str_replace('â€“', '-', thissubject);
			$thissubject = str_replace('â€', '"', $thissubject);

			$thismessage=stripslashes(mb_convert_encoding(fixMSWord($sendmessage), "UTF-8"));
			//Convert some bad characters e.g. â&#8364; added 20210128 CJG
			$thismessage = str_replace("â&#8364", "'", $thismessage);
			$thismessage = str_replace("Â", "", $thismessage);
			$thismessage = str_replace("â€™", "'", $thismessage);
			$thismessage = str_replace("â€œ", '"', $thismessage);
			$thismessage = str_replace('â€“', '-', $thismessage);
			$thismessage = str_replace('â€', '"', $thismessage);
			
			*/
			$thissubject=addslashes($subject);
			$thismessage=addslashes($sendmessage);
			
			//save message to log tblMailHistoryMessageLog
			$insert = new \stdClass();
			$insert->Subject = $thissubject;
			$insert->Message = $thismessage;
			$insert->SenderEmail = $from;
			$insert->SenderName = $login_name;
			$insert->Queued = $DateNow;
			$db->insertObject('tblMailHistoryMessageLog', $insert, 'ID');
			$messageid = $insert->ID;	
			
			while($emailno<=$maxmembers){
				$thismemberid=$members[$emailno];
								
				try{
					$query = $db->getQuery(true)
						->select('*')
						->from($db->qn($memtable))
						->where($db->qn('ID').' = '.$db->q($thismemberid));
				}catch (RuntimeException $e){
					echo $e->getMessage();
				}
				if ($this_member = $db->setQuery($query)->loadObject()) {
					$memberid=$this_member->ID;
					$memberid2=$this_member->ID2;
					$MembershipNo=$this_member->MembershipNo;
					if($recipients=="B" || $recipients=="M"){
						//send to main
						$contactname="";
						$contactname=$this_member->FirstName;
						if($this_member->FirstName && $contactname){
							$contactname.=" ".$this_member->LastName;
						}else{
							$contactname=$this_member->LastName;
						}
						$emailaddress=$this_member->Email;
						$pos = strpos ($emailaddress, "@");
						if($pos==true){
							//have email so include
							//Check if already included
							if($pos = strpos ($addresslist, $emailaddress)){
								//Ignore duplicate
								$emaildups+=1;
							}else{
								//add it
								$addresslist.="|".$emailaddress;
								$emailsent+=1;
								$to=$contactname." <".$emailaddress.">";
								$fromname="DBA Administration";
								$recipient=$emailaddress;
								$subject=$thissubject;
								$body=$thismessage;
								$contactname=addslashes($contactname);
								$emailaddress=addslashes($emailaddress);
								//add record to email pending table
								$insert = new \stdClass();
								$insert->MessageID = $messageid;
								$insert->MemberID = $memberid;
								$insert->MemberName = $contactname;
								$insert->MemberEmail = $emailaddress;
								$insert->Queued = $DateNow;
								try{
									$db->insertObject('tblMailHistoryRecipientLog', $insert);
								}catch (RuntimeException $e){
									echo $e->getMessage();
								}
								
								$sentlist = (empty($sentlist) ? '' : $sentlist."\n").$MembershipNo." ".$contactname." ".$emailaddress;
								$queuedok+=1;
							}
						}else{
							//No email address so add to missed list
							if($missedlist){
								$missedlist=$missedlist."\n";
							}
							$missedlist=$MembershipNo." ".$contactname;
							$notqueued+=1;
						}
					}
					
					if($recipients=="B" || $recipients=="S"){
						//send to second member
						$contactname="";
						$contactname=$this_member->FirstName2;
						if($this_member->FirstName2 && $contactname){
							$contactname.=" ".$this_member->LastName2;
						}else{
							$contactname=$this_member->LastName2;
						}
						$emailaddress=$this_member->Email2;
						$pos = strpos ($emailaddress, "@");
						if($pos==true){
							//have email so include
							//Check if already included
							if($pos = strpos ($addresslist, $emailaddress)){
								//Ignore duplicate
								$emaildups+=1;
							}else{
								//add it
								$addresslist.="|".$emailaddress;
								$emailsent+=1;
								$to=$contactname." <".$emailaddress.">";
								$fromname="DBA Administration";
								$recipient=$emailaddress;
								$subject=$thissubject;
								$body=$thismessage;
								
								$contactname=addslashes($contactname);
								$emailaddress=addslashes($emailaddress);
								//add record to email pending table
								$insert = new \stdClass();
								$insert->MessageID = $messageid;
								$insert->MemberID = $memberid2;
								$insert->MemberName = $contactname;
								$insert->MemberEmail = $emailaddress;
								$insert->Queued = $DateNow;
								try{
									$db->insertObject('tblMailHistoryRecipientLog', $insert);
								}catch (RuntimeException $e){
									echo $e->getMessage();
								}
															
								
								$sentlist = (empty($sentlist) ? '' : $sentlist."\n").$MembershipNo." ".$contactname." ".$emailaddress;
								$queuedok+=1;
							}
						}else{
							//No email address so add to missed list
							if($missedlist){
								$missedlist=$missedlist."\n";
							}
							$missedlist=$missedlist.$MembershipNo." ".$contactname." (second member)";
							$notqueued+=1;
						}
					}
				}
				$emailno+=1;
			} 
			echo("<b>$emailsent message(s) queued for sending ($emaildups duplicate(s)).<br>A report with recipient list has been emailed to $from");
			//email message to sender for the record
			if($missedlist==""){
				$missedlist="none";
			}
			//update recipient numbers to log tblMailHistoryMessageLog
			$update = new \stdClass();
			$update->QueuedQty = $queuedok;
			$update->NotQueuedQty = $notqueued;
			$update->ID = $messageid;
			try{
				$db->updateObject('tblMailHistoryMessageLog', $update, 'ID');
			}catch (RuntimeException $e){
    			echo $e->getMessage();
			}
			//$thissubject=stripslashes(mb_convert_encoding(fixMSWord($subject), "UTF-8"));
			//$thismessage=stripslashes(mb_convert_encoding(fixMSWord($sendmessage), "UTF-8"));
			
			$thissubject=stripslashes($subject);
			$thismessage=stripslashes($sendmessage);
			
			$adminmessage="This is a copy for your records of a message queued for sending via Contact Manager\n\nSender: ".$from."\n\nSubject: ".$thissubject."\n\nMessage: ".$thismessage."\n\nSelection criteria: ".(isset($criteria) ? $criteria : '')."\n\nContacts selected: ".$contactselected."\n\nRecipient(s):\n".$sentlist."\n\nMissing email addresses:\n".$missedlist;

			$fromname="DBA Administration";
			$recipient=$from;
			$subject=$thissubject;
			$body=$adminmessage;
			if($mailOn) {
				$mailer = Factory::getMailer();		
				$mailer->setSender([$config->get('mailfrom'), $config->get('fromname')]);
				$mailer->addRecipient($recipient);
				$mailer->addReplyTo($from, $fromname);
				$mailer->setSubject($thissubject);
				$mailer->setBody(nl2br($body));
				$mailer->isHtml(true);
				$mailer->Send();
			} else echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";

			$message="";
			$subject="";
		}
	}
	if(empty($subject)){
		$subject="Message...";
	}
	

	echo("<table border='0' cellspacing='1' cellpadding='4'>");
	echo("<tr><td><b>Email</b></td>");
	echo("<td><input type='submit' name='send' value='Test'>");
	echo("<input type='submit' name='send' value='Send'></td><td>&nbsp;</td></tr>");
	//choose main, second or both	
	echo("<tr><td>To:</td>");
	echo("<td><input name='recipients' type='radio' value='M' ".$RecipientsSelectM."> Main member <input name='recipients' type='radio' value='S' ".$RecipientsSelectS."> Second member<input name='recipients' type='radio' value='B' ".$RecipientsSelectB."> Both</td>");
	echo("<td>&nbsp;</td></tr>");
	//From	
	echo("<tr><td>From:</td>");
	echo("<td><input type='text' name='from' size='60' value='".$from."' readonly ></td>");
	echo("<td>&nbsp;</td></tr>");
	//Subject	
	echo("<tr><td>Subject:</td>");
	echo("<td><input type='text' name='subject' size='60' value=".stripslashes($subject)."></td>");
	echo("<td></td></tr>");
	//Message box
	echo("<tr><td>Message:</td>");
	echo("<td><textarea name='message' rows='10' cols='60'>".(isset($message) ? $message : '')."</textarea></td>");
	echo("<td></td></tr>");
	echo("<tr><td>Help:</td>");
	echo("<td>You can embed various codes within the text which will be replaced by unique details when the email is processed:<br>[name] to insert the member full name<br>[quicklogin] for an auto login link that will then go directly to member profile page (<i>put this on a seperate line so as not to break the link</i>).</td>");
	echo("<td></td></tr></table>");
}
?> 

      </td>
    </tr>
  </table>
</form>
</body>

</html>
