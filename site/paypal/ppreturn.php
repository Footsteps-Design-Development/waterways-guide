<?php
	$raw_post_data = file_get_contents('php://input');
	$raw_post_array = explode('&', $raw_post_data);
	$myPost = array();
	foreach ($raw_post_array as $keyval) {
	  $keyval = explode ('=', $keyval);
	  if (count($keyval) == 2){
		 $myPost[$keyval[0]] = urldecode($keyval[1]);
	  }
	}
	// read the post from PayPal system and add 'cmd'
	$req = 'cmd=_notify-validate';
	foreach ($myPost as $key => $value) {        
		$value = urlencode($value);
		$req .= "&$key=$value";
	}
		
	
	
	
	// post back to PayPal system to validate
	$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
	$fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);
	
	// assign posted variables to local variables
	$item_name = $_POST['itemname'];
	$business = $_POST['business'];
	$item_number = $_POST['itemnumber'];
	$payment_status = $_POST['payment_status'];
	
	$mc_gross = $_POST['mc_gross'];
	$payment_currency = $_POST['mc_currency'];
	$txn_id = $_POST['txn_id'];
	$receiver_email = $_POST['receiver_email'];
	$receiver_id = $_POST['receiver_id'];
	$quantity = $_POST['quantity'];
	$num_cart_items = $_POST['num_cart_items'];
	$payment_date = $_POST['payment_date'];
	$first_name = $_POST['first_name'];
	$last_name = $_POST['last_name'];
	$payment_type = $_POST['payment_type'];
	$payment_status = $_POST['payment_status'];
	$payment_gross = $_POST['payment_gross'];
	$payment_fee = $_POST['payment_fee'];
	$settle_amount = $_POST['settle_amount'];
	$memo = $_POST['memo'];
	$payer_email = $_POST['payer_email'];
	$txn_type = $_POST['txn_type'];
	$payer_status = $_POST['payer_status'];
	$address_street = $_POST['address_street'];
	$address_city = $_POST['address_city'];
	$address_state = $_POST['address_state'];
	$address_zip = $_POST['address_zip'];
	$address_country = $_POST['address_country'];
	$address_status = $_POST['address_status'];
	$tax = $_POST['tax'];
	$option_name1 = $_POST['option_name1'];
	$option_selection1 = $_POST['option_selection1'];
	$option_name2 = $_POST['option_name2'];
	$option_selection2 = $_POST['option_selection2'];
	$for_auction = $_POST['for_auction'];
	$invoice = $_POST['invoice'];
	$custom = $_POST['custom'];
	$notify_version = $_POST['notify_version'];
	$verify_sign = $_POST['verify_sign'];
	$payer_business_name = $_POST['payer_business_name'];
	$payer_id =$_POST['payer_id'];
	$mc_currency = $_POST['mc_currency'];
	$mc_fee = $_POST['mc_fee'];
	$exchange_rate = $_POST['exchange_rate'];
	$settle_currency  = $_POST['settle_currency'];
	$parent_txn_id  = $_POST['parent_txn_id'];
	
	
	//echo("Customer: $custom");
	// subscription specific vars
	
	$subscr_id = $_POST['subscr_id'];
	$subscr_date = $_POST['subscr_date'];
	$subscr_effective  = $_POST['subscr_effective'];
	$period1 = $_POST['period1'];
	$period2 = $_POST['period2'];
	$period3 = $_POST['period3'];
	$amount1 = $_POST['amount1'];
	$amount2 = $_POST['amount2'];
	$amount3 = $_POST['amount3'];
	$mc_amount1 = $_POST['mc_amount1'];
	$mc_amount2 = $_POST['mc_amount2'];
	$mc_amount3 = $_POST['mcamount3'];
	$recurring = $_POST['recurring'];
	$reattempt = $_POST['reattempt'];
	$retry_at = $_POST['retry_at'];
	$recur_times = $_POST['recur_times'];
	$username = $_POST['username'];
	$password = $_POST['password'];
	
	//auction specific vars
	
	$for_auction = $_POST['for_auction'];
	$auction_closing_date  = $_POST['auction_closing_date'];
	$auction_multi_item  = $_POST['auction_multi_item'];
	$auction_buyer_id  = $_POST['auction_buyer_id'];
	
	//load Joomla helpers for emailsending
	define( '_JEXEC', 1 );
	define('DS', DIRECTORY_SEPARATOR);
	define('JPATH_BASE', substr(__FILE__,0,strrpos(__FILE__, DS."components")));
	require_once JPATH_BASE.'/includes/defines.php';
	require_once JPATH_BASE.'/includes/framework.php';	
	require_once(JPATH_BASE.'/components/com_waterways_guide/commonV3.php');

	use Joomla\CMS\Factory;
	$config = Factory::getConfig();
	$mailOn = Factory::getConfig()->get('mailonline') == '1';
	
	$db = Factory::getDbo();

	//DB connect creds and email 
	$notify_email =  $webmasteremail;         //email address to which debug emails are sent to

	/*
	if (!$fp) {
		// HTTP ERROR
	} else {
		fputs ($fp, $header . $req);
		while (!feof($fp)) {
			$res = fgets ($fp, 1024);
			if (strcmp ($res, "VERIFIED") == 0) {
	*/

	$fecha = date("m")."/".date("d")."/".date("Y");
	$fecha = date("Y").date("m").date("d");	
	//check if transaction ID has been processed before
	$query = $db->getQuery(true)
		->select('COUNT('.$db->qn('txnid').')')
		->from($db->qn('paypal_payment_info'))
		->where($db->qn('txnid').' = '.$db->q($txn_id));
	try {
		$nm = $db->setQuery($query)->loadResult();
	} catch(Exception $e) {
		die("Duplicate txn id check query failed:<br>" . print_r($e, true) . "<br>" . $query->__toString());
	}
	if ($nm == 0 && $txn_id){
		//no dup
	  if ($txn_type == "cart"){
			$insert = new \stdClass();
			$insert->custom = $custom;
			$insert->itemnumber = $_POST[$itemnumber];
			$insert->itemname = $_POST[$itemname];
			$insert->quantity = $_POST[$quantity];
			$insert->paymentstatus = $payment_status;
			$insert->buyer_email = $payer_email;
			$insert->firstname = $first_name;
			$insert->lastname = $last_name;
			$insert->street = $address_street;
			$insert->city = $address_city;
			$insert->state = $address_state;
			$insert->zipcode = $address_zip;
			$insert->country = $address_country;
			$insert->mc_gross = $mc_gross;
			$insert->mc_fee = $mc_fee;
			$insert->memo = $memo;
			$insert->paymenttype = $payment_type;
			$insert->paymentdate = $payment_date;
			$insert->txnid = $txn_id;
			$insert->pendingreason = $pending_reason;
			$insert->reasoncode = $reason_code;
			$insert->tax = $tax;
			$insert->datecreation = $fecha;
			try {
				$db->insertObject('paypal_payment_info', $insert);
			} catch(Exception $e) {
				die("Cart - paypal_payment_info, Query failed:<br>" . print_r($e, true) . "<br>" . print_r($insert, true));
			}
			for ($i = 1; $i <= $num_cart_items; $i++) {
				 $itemname = "item_name".$i;
				 $itemnumber = "item_number".$i;
				 $on0 = "option_name1_".$i;
				 $os0 = "option_selection1_".$i;
				 $on1 = "option_name2_".$i;
				 $os1 = "option_selection2_".$i;
				 $quantity = "quantity".$i;
		
				$insert = new \stdClass();
				$insert->txnid = $txn_id;
				$insert->itemnumber = $_POST[$itemnumber];
				$insert->itemname = $_POST[$itemname];
				$insert->on0 = $_POST[$on0];
				$insert->os0 = $_POST[$os0];
				$insert->on1 = $_POST[$on1];
				$insert->os1 = $_POST[$os1];
				$insert->quantity = $_POST[$quantity];
				$insert->invoice = $invoice;
				$insert->custom = $custom;
				try {
					$db->insertObject('paypal_cart_info', $insert);
				} catch(Exception $e) {
					die("Cart - paypal_cart_info, Query failed:<br>" . print_r($e, true) . "<br>" . print_r($insert, true));
				}
			}
		}
		
	}
	//manual test activation unrem these lines
	//$item_number = $_GET['item_number'];
	//$custom = $_GET['custom'];
	//$payment_status = $_GET['payment_status'];
	
	//test www.barges.org/ppreturn.php?item_number=DBA-SubNew&custom=199&payment_status=Completed
	
	//http://barges.org/ppreturn.php?item_number=DBA-SubRenew&custom=199&payment_status=Completed
	
	//$custom=199;
	//$item_number="DBA-SubRenew";
	//$payment_status="Completed";
	
	
	$changedate=date("Y-m-d H:i:s");
	//$AmountPaid="35.00";
	//check and action 
	if($_POST[$itemnumber]=="DBA-SubRenew" && $payment_status=="Completed"){
		//to be added for Paypal renewals
		if($custom){
			//$custom should contain ID
			//get existing member details
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('tblMembers'))
				->where($db->qn('ID').' = '.$db->q($custom));
			$row = $db->setQuery($query)->loadAssoc();
			$found = count($row);
			if($found>0){
				$userid=$custom;
				$track=$row["Track"];
				if($track==""){
					$track=time();
				}
				//make live
				//update  account details		
				//check last date paid and add a year if today is prior to renewal date - e.g. renewing in advance of expiry
				$diff = strtotime('now') - strtotime(date('Y-m-d', strtotime($row['DatePaid'].' +1 year +1 day')));
				if($expiresecs>$nowsecs){
					//renew date = lastrenewdate + 12 months as sub has not yet expired
					$DatePaid = date('Y-m-d 23:00:00', strtotime($row['DatePaid'].' +1 year'));
					$DateExpirytext = date('d/m/Y', strtotime($row['DatePaid'].' +2 years'));
				}else{
					//renew date = now as sub has already expired
					$DatePaid = date('Y-m-d H:i:s');
					$DateExpirytext = date('d/m/Y', strtotime('+1 year'));
				}
				$MemStatus=2;
				$MembershipNo=$row["MembershipNo"];
				$LastName=$row["LastName"];
				$FullName= $row["FirstName"]." ".$row["LastName"];
				$memberlogin=$row["Login"];
				$memberpw=$row["PW"];
				$memberemail=$row["Email"];
				$ID2=$row["ID2"];
				$MemType=$row["MemType"];
				$update = new \stdClass();
				$update->MemStatus = $MemStatus;
				$update->DatePaid = $DatePaid;
				$update->AmountPaid = $AmountPaid;
				$update->Track = $track;
				$update->ID = $userid;
				$db->updateObject('tblMembers', $update, 'ID') or die ("Couldn't add members details");
				//unblock in user table in case blocked
				$update = new \stdClass();
				$update->block = '0';
				$update->id = $custom;
				$db->updateObject('#__users', $update, 'id') or die ("Couldn't update Joomla partner status");
				$adminmessage.="\n\nStatus has been unblocked";
				//also family second member?
				if($MemType=="Family"){
					$adminmessage.="\n\nPartner status has been unblocked";
					$update = new \stdClass();
					$update->block = '0';
					$update->id = $ID2;
					$db->updateObject('#__users', $update, 'id') or die ("Couldn't update Joomla partner status");
				}					
	
	
				//exit();
				$subject="Profile update";
				$changelogtext="membership subscription renewed until ".$DateExpirytext.". Credit / debit card payment of GBP ".$AmountPaid." received";
				$insert = new \stdClass();
				$insert->MemberID = $userid;
				$insert->Subject = $subject;
				$insert->ChangeDesc = $changelogtext;
				$insert->ChangeDate = $changedate;
				$db->insertObject('tblChangeLog', $insert) or die ("Couldn't update change log");
	
				$message="Thank you for your subscription renewal payment. Your membership has been renewed until ".$DateExpirytext.".";
				//put together member login details paragraph
	
				$message.="\n\nPlease use the following details for logging in to the members area:\nWebsite address: $memberloginurl\nLogin name: $memberlogin\nPassword: Click the 'reminder' button\n\n";
	
				$to=$memberemail;
				//$to=$webmasteremail;
				$from=$registrationemail;
	
				$subject="Renewal of subscription to the $sitename website ";
				//confirm emails
				$content=$message."\n\n".$emailfooter;
				if($mailOn) {
					$mailer = Factory::getMailer();	
					$mailer->setSender([$config->get('mailfrom'), 'DBA Membership Auto Administration']);
					$mailer->addRecipient($to);
					$mailer->addReplyTo($from);
					$mailer->setSubject($subject);
					$mailer->setBody(nl2br($content));
					$mailer->isHtml(true);
					$mailer->Send();
				} else echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";
	
				$screenmessage.="<br><br>An email confirmation of this entry has been sent to ".$to."\n";					
	
				$subject="Renewal of subscription ".$LastName." ".$MembershipNo." to the $sitename website ";
				$content="A member ".$LastName." ".$MembershipNo." has completed card payment for subscription renewal to the $sitename website.\n\nThe membership has been renewed until ".$DateExpirytext.". No action should be required as this is an auto renewal. Verification code:". strcmp ($res, "VERIFIED");
	
				$from=$registrationemail;
				$to=$registrationemail;
				if($mailOn) {
					$mailer = Factory::getMailer();	
					$mailer->setSender([$config->get('mailfrom'), 'DBA Membership Auto Administration']);
					$mailer->addRecipient($to);
					$mailer->addReplyTo($from);
					$mailer->setSubject($subject);
					$mailer->setBody(nl2br($content));
					$mailer->isHtml(true);
					$mailer->Send();
				} else echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";
				
				//copy mail to webmaster
				$to=$webmasteremail;
				if($mailOn) {
					$mailer = Factory::getMailer();	
					$mailer->setSender([$config->get('mailfrom'), 'DBA Membership Auto Administration']);
					$mailer->addRecipient($to);
					$mailer->addReplyTo($from);
					$mailer->setSubject($subject);
					$mailer->setBody(nl2br($content));
					$mailer->isHtml(true);
					$mailer->Send();
				} else echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";
				//echo(nl2br($changelogtext.$content));
			}
		}		
	}
	
	
	
	
	if($_POST[$itemnumber]=="DBA-SubNew" && $payment_status=="Completed"){
		//new member
		if($custom){
			//$custom should contain ID
			//get existing member details
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('tblMembers'))
				->where($db->qn('ID').' = '.$db->q($custom));
			$row = $db->setQuery($query)->loadAssoc();
			$found = count($row);
			if($found>0){
				$userid=$custom;
				//make live
				//update  account details		
				$MemStatus=2;
				$DateJoined=date("Y-m-d H:i:s");
				$DatePaid=date("Y-m-d H:i:s");
				$memberlogin=$row["Login"];
				$MembershipNo=$row["MembershipNo"];
				$LastName=$row["LastName"];
				$FullName= $row["FirstName"]." ".$row["LastName"];
				
				$memberpw=$row["PW"];
				$track=$row["Track"];
				$memberemail=$row["Email"];
				$ID2=$row["ID2"];
				$Email2=$row["Email2"];	
				$Login2=$row["Login2"];
				$MemType=$row["MemType"];			
				
				$update = new \stdClass();
				$update->MemStatus = $MemStatus;
				$update->DateJoined = $DateJoined;
				$update->DatePaid = $DatePaid;
				$update->ID = $userid;
				$db->updateObject('tblMembers', $update, 'ID') or die ("Couldn't add members details");
	
				//unblock in user table in case blocked
				$update = new \stdClass();
				$update->block = '0';
				$update->id = $custom;
				$db->updateObject('#__users', $update, 'id') or die ("Couldn't update Joomla status");
				$adminmessage.="\n\nStatus has been unblocked";
				
				$subject="Profile update";
				$changelogtext="New membership activated. Credit / debit card payment received";
				$insert = new \stdClass();
				$insert->MemberID = $userid;
				$insert->Subject = $subject;
				$insert->ChangeDesc = $changelogtext;
				$insert->ChangeDate = $changedate;
				$db->insertObject('tblChangeLog', $insert) or die ("Couldn't update change log");
	
				$message=$thanksforpayment_cc;
				//put together member login details paragraph
	
				$message.="\n\nPlease use the following details for logging in to the members area:\nWebsite address: $memberloginurl\nLogin name: $memberlogin\nPassword: click the 'reminder' button\n\n";
	
				$to=$memberemail;
				$from=$registrationemail;
				$subject="Activation of subscription to the $sitename website ";
				//confirm emails
				$content=$message."\n\n".$emailfooter;
				if($mailOn) {
					$mailer = Factory::getMailer();	
					$mailer->setSender([$config->get('mailfrom'), 'DBA Membership Auto Administration']);
					$mailer->addRecipient($to);
					$mailer->addReplyTo($from);
					$mailer->setSubject($subject);
					$mailer->setBody(nl2br($content));
					$mailer->isHtml(true);
					$mailer->Send();
				} else echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";
				$screenmessage.="<br><br>An email confirmation of this entry has been sent to ".$to."\n";					
	
				//also family second member?
				if($MemType=="Family"){
					$adminmessage.="\n\nPartner status has been unblocked";
					$update = new \stdClass();
					$update->block = '0';
					$update->id = $ID2;
					$db->updateObject('#__users', $update, 'id') or die ("Couldn't update Joomla partner status");

					//Create email messages
					$message="Your family member application has been approved for the $sitename\n\nYou can now login at any time at ".$memberloginurl." to access the members section.\n\nYour login and password are:\nLogin: ".$Login2."\nClick the 'Forgot your password?' to reset your password for the first time to one of your choice.\n\nThanks for subscribing to ".$sitename.".";
					$to=$Email2;
					$screenmessage.="<br>The Profile for the second family member is also now live and the following message has been emailed to ".$to." <br><br>".nl2br($message);
					$from=$registrationemail;
					$fromname="DBA Registration";
					$subject="Your application has been approved for the $sitename";
					$content=$message."\n\n".$emailfooter;
					if($mailOn) {
						$mailer = Factory::getMailer();	
						$mailer->setSender([$config->get('mailfrom'), $config->get('fromname')]);
						$mailer->addRecipient($to);
						$mailer->addReplyTo($from, $fromname);
						$mailer->setSubject($subject);
						$mailer->setBody(nl2br($content));
						$mailer->isHtml(true);
						$return = $mailer->Send();
						// Check for an error.
						if ($return !== true) {
							throw new \Exception($db->getErrorMsg(), 500);
						}
					} else echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";
				}			
			}		
			
			$subject="Profile update";
			$changelogtext="Application approved<br>";
			$insert = new \stdClass();
			$insert->MemberID = $userid;
			$insert->Subject = $subject;
			$insert->ChangeDesc = $changelogtext;
			$insert->ChangeDate = $changedate;
			$db->insertObject('tblChangeLog', $insert) or die ("Couldn't update change log");
		}
	
	
	
		$subject="Activation of subscription ".$LastName." ".$MembershipNo." to the ".$sitename;
		$content="A new member ".$LastName." ".$MembershipNo." has completed card payment for subscription to the ".$sitename." and is ready for a welcome pack. Verification code:". strcmp ($res, "VERIFIED");
		$content.="\n\nDirect link if you are already logged in as administrator\nbarges.org/index.php?option=com_waterways_guide&tmpl=component&view=profile&userid=".$custom;
		
		$from=$registrationemail;

		$to=$registrationemail;
		if($mailOn) {
			$mailer = Factory::getMailer();	
			$mailer->setSender([$config->get('mailfrom'), 'DBA Membership Auto Administration']);
			$mailer->addRecipient($to);
			$mailer->addReplyTo($from);
			$mailer->setSubject($subject);
			$mailer->setBody(nl2br($content));
			$mailer->isHtml(true);
			$mailer->Send();
		} else echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";

		$to=$webmasteremail;
		if($mailOn) {
			$mailer = Factory::getMailer();	
			$mailer->setSender([$config->get('mailfrom'), 'DBA Membership Auto Administration']);
			$mailer->addRecipient($to);
			$mailer->addReplyTo($from);
			$mailer->setSubject($subject);
			$mailer->setBody(nl2br($content));
			$mailer->isHtml(true);
			$mailer->Send();
		} else echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";

		echo(nl2br($changelogtext.$content));
	}		

?>