<?php


//cron command   /usr/local/bin/php -q /home/customer/www/barges.org/public_html/components/com_membership/cron/cron_mail.php


//load Joomla helpers
define( '_JEXEC', 1 );
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', strstr(__DIR__, 'public_html', true).'public_html');
define('JPATH_COMPONENT', JPATH_BASE .DS.'components'.DS.'com_membership');
require_once(JPATH_BASE .DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE .DS.'includes'.DS.'framework.php');
require_once(JPATH_COMPONENT .DS.'commonV3.php');

use Joomla\CMS\Factory;
$config = Factory::getConfig();
$mailOn = Factory::getConfig()->get('mailonline') == '1';

$db = Factory::getDBO();

//simulate or live
$livemail=1;
$livemailadmin=1;
	
 
$secs_now = time();
$secsinayear=31536000;
$datenow=date("Y-m-d H:i:s");		
$content="";

//tblMailHistoryMessageLog ID Subject Message SenderEmail SenderName Sent SentQty NotSentQty Status
//tblMailHistoryRecipientLog ID MessageID MemberID MemberName MemberEmail Queued Sent Status

//Status 0=queued 1=sent 2=bounced
$limit=100;
$thismessageid=0;
$messagefound=0;
$URL_path="www.barges.org/ql.php?qlc=";
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblMailHistoryRecipientLog'))
	->where($db->qn('status').' = 0')
	->order($db->qn('MessageID'))
	->setLimit($limit);
$recipients = $db->setQuery($query)->loadObjectList();
	
if ($recipients){
		
	foreach($recipients as $recipient){

		if($recipient->MessageID!=$thismessageid){
			//lookup message
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('tblMailHistoryMessageLog'))
				->where($db->qn('ID').' = '.$db->q($recipient->MessageID));
			if ($this_message = $db->setQuery($query)->loadObject ()) {
				$from=stripslashes($this_message->SenderEmail);
				$fromname=stripslashes($this_message->SenderName);
				$subject=stripslashes($this_message->Subject);
				$body=stripslashes($this_message->Message);

				$messagefound=1;
			}else{
				//message data not found
				$messagefound=0;
				$content="Failed to find message ".$recipient->MessageID." intended for ".$recipient->MemberEmail;
			}
			$thismessageid=$recipient->MessageID;
		}
		if($livemail==1 && $messagefound==1){
			//check if quicklogin URL embedded
			$new_body=$body;
			if(strpos($body, "[quicklogin]")=== false){

			}else{
				//replace with full URL
				$user_id=$recipient->MemberID;
				$insertURL=$URL_path.md5($user_id.$secretstring);
				$new_body=str_replace("[quicklogin]",$insertURL,$body);
			}
			//check if member name embedded
			if(strpos($body, "[name]")=== false){

			}else{
				//replace with full URL
				$new_body=str_replace("[name]",$recipient->MemberName,$new_body);
			}
			//echo("working 4 $from, $fromname, $recipient->MemberEmail, $subject, $new_body");			
			if($mailOn) {
				$mailer = Factory::getMailer();	
				$mailer->setSender([$config->get('mailfrom'), $config->get('fromname')]);
				$mailer->addRecipient($recipient->MemberEmail);
				$mailer->addReplyTo($from, $fromname);
				$mailer->setSubject($subject);
				$mailer->setBody(nl2br($new_body));
				$mailer->isHtml(true);
				$mailer->Send();
			} else echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";
			
			//update status
			$update = new \stdClass();
			$update->Sent = $datenow;
			$update->Status = 1;
			$update->ID = $recipient->ID;
			$db->updateObject('tblMailHistoryRecipientLog', $update, 'ID');
			echo("Message id: ".$this_message->ID." Recipient: ".$recipient->MemberEmail."\n");	
		}					
	}							
}else{
	//echo("No recipients");
}
if($livemailadmin==1 && $content){
	
	
	$to=$webmasteremail;
	//$to="chris@productif.co.uk";
	if($mailOn) {
		$mailer = Factory::getMailer();	
		$mailer->setSender([$config->get('mailfrom'), 'DBA Membership Auto Administration']);
		$mailer->addRecipient($to);
		$mailer->addReplyTo($registrationemail);
		$mailer->setSubject($subject);
		$mailer->setBody(nl2br($content));
		$mailer->isHtml(true);
		$mailer->Send();
	} else echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";

}
//echo("complete");
file_put_contents(__FILE__.'.last-run.log', date('c'));