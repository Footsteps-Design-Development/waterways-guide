<?php

/**
 * @version     3.0.0 Revision for sqli and php update 20210713
 * @package     com_waterways_guide
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Chris Grant www.productif.co.uk
 */
// no direct access

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

$config = Factory::getConfig();
$mailOn = Factory::getConfig()->get('mailonline') == '1';

use Joomla\CMS\User\UserHelper;

//include(dirname(__FILE__)."/form_filters.php")

//get form type params from menu item to decide new or existing user

$app = Factory::getApplication('com_waterways_guide');
//check access level of user
$user = Factory::getUser();
$componentpath = "/components/com_waterways_guide/views/profile/tmpl/";
$userGroups = $user->getAuthorisedGroups();

if (in_array("8", $userGroups) || in_array("22", $userGroups)) {

	$membershipadmin = true; //superuser or membershipadmin

} else {

	$membershipadmin = false;
}

if (in_array("24", $userGroups)) {

	$membershipview = true; //board members view only

} else {

	$membershipview = false;
}

$userid = $user->id;

if (in_array("25", $userGroups)) {

	$secondfamilymember = true;

	require_once("profile_second_member.php");
} else {

	$secondfamilymember = false;

	$myparams = $app->getTemplate(true)->params;

	$id = $myparams->get('id');

	function is_matched($text)
	{

		return preg_match("/\b(\w+)\s+(\\1)\b/i", $text);
	}

	function replace($text)
	{

		return preg_replace("/\b(\w+)\s+(\\1)\b/i", "\\1", $text);
	}

	function createRandomPassword()
	{

		$chars = "abcdefghijkmnopqrstuvwxyz23456789";

		srand((float)microtime() * 1000000);

		$i = 0;
		$pass = '';

		while ($i <= 7) {

			$num = rand() % 33;

			$tmp = substr($chars, $num, 1);

			$pass = $pass . $tmp;

			$i++;
		}

		return $pass;
	}

	$db = Factory::getDBO();

	require_once(JPATH_COMPONENT_SITE . "/commonV3.php");

	$test_vars = (array(

		'ID',
		'Title',
		'FirstName',
		'LastName',
		'Title2',
		'FirstName2',
		'LastName2',
		'Email2',
		'ID2',
		'Address1',
		'Address2',
		'Address3',
		'Address4',
		'PostCode',
		'Country',
		'CountryCode',
		'CountryCodeCruising',
		'Telephone1',
		'Telephone2',
		'Mobile2',
		'Services',
		'Situation',
		'ShipName',
		'ShipLength',
		'ShipBeam',
		'LastUpdate',
		'PostZone',
		'BasicSub',
		'PaymentMethod',
		'MemTypeCode',
		'MemType',
		'MemTypeCode',
		'Keywords',
		'MembershipNo',
		'MemNo',
		'Ref',
		'Level',
		'MemStatus',
		'DateJoined',
		'DatePaid',
		'DateCeased',
		'NotesAdmin',
		'errormessage',
		'profile_services',
		'userid',
		'preselect_iso',
		'Login',
		'Login2',
		'PW',
		'user_id',
		'go2value',
		'level',
		'infoid',
		'table',
		'subscription_desc',
		'SortCode',
		'AccountNo',
		'profileaction',
		'ref',
		'subaction',
		'Location',
		'LocationCruising',
		'Initials',
		'Initials2',
		'Notes',
		'Code',
		'SubStatus',
		'PaymentCurrency',
		'AmountPaid',
		'Email',
		'Committee',
		'Groups',
		'Track',
		'DateArchive',
		'PaymentMethodChanged',
		'newby',
		'introtext',
		'TheAccountNo',
		'TheSortCode',
		'TheDateJoined',
		'TheDatePaid',
		'TheDateCeased',
		'TheMemStatus',
		'TheNotesAdmin',
		'num_services',
		'coupon_email_recipient'

	));

	foreach ($test_vars as $test_var) {

		if (!$$test_var =  $app->input->getString($test_var)) {

			switch ($test_var) {

				case 'num_services':

					$$test_var = 0;

					break;

				default:

					$$test_var = "";

					break;
			}
		}
	}

	if (!$userid) {

		//logged in user is using this form, not admin

		$userid = $user->id;
	}

	if (!$userid) {

		$ID = "new";
	}

	//echo("User id=$userid");

	if (!$go2value) {

		$go2value = "Update >";
	}

	if (isset($table) && $table == "archive") {

		$memtable = "tblMembers_archive";
	} else {

		$memtable = "tblMembers";
	}

	//check if new application

	if (!$ID) {

		$ID = $infoid;

		//$userid=$ID;

	}

	if ($subaction == "subscribe2" || $subaction == "Update" || $subaction == "Approve") {

		if ($Location) {

			//split Country iso_zone

			list($CountryCode, $PostZone, $Country) = explode('_', $Location);
		}

		if ($LocationCruising) {

			//split Cruising Country iso_zone

			list($CountryCodeCruising, $CruisingPostZone, $CruisingCountry) = explode('_', $LocationCruising);
		}

		//validate

		$screenmessage = "";

		//check if this is the right member id

		if (!$userid && $infoid != "new" && $ID != "new") {

			$errormessage = "Sorry - there was a problem updating your member information. Please go to the <a href=\"" . $memberloginurl . "\">main login page</a> to try again or retrieve your password";

			$subaction = "back";
		} else {

			//check usernane is unique, added in 20090909 CJG to avoid duplicated applications

			$query = $db->getQuery(true)

				->select('*')

				->from($db->qn('tblMembers'))

				->where($db->qn('Login') . ' = ' . $db->q($Login));

			$result = $db->setQuery($query)->loadAssocList();

			$num_rows = count($result);

			if ($num_rows > 0) {

				//already exists

				if ($ID == "new") {

					//trying to join as new with existing email so reject

					$errormessage = "Sorry - there was a problem creating your member profile. The login '" . $Login . "' is already being used on another account. If you recognise it, please go to the <a href=\"" . $memberloginurl . "\">main login page</a> to login again or retrieve your password where you can update your details and pay subscriptions.";

					$subaction = "back";
				} else {

					//check if ID is same

					$row = reset($result);

					$id = $row["ID"];

					if ($id != $userid) {

						//trying to use emailaddress of another profile

						$errormessage = "Sorry - there was a problem updating your member information. The login '" . $Login . "' is already being used on another account.";

						$subaction = "back";
					}
				}
			}

			//check partner email is unique

			if (!empty($ID2) && !empty($Email2)) {

				$query = $db->getQuery(true)

					->select('*')

					->from($db->qn('#__users'))

					->where($db->qn('email') . ' = ' . $db->q($Email2));

				$result = $db->setQuery($query)->loadAssocList();

				$num_rows = count($result);

				if ($num_rows > 0) {

					//already exists

					if ($ID == "new") {

						//trying to join as new with existing email so reject

						$errormessage = "Sorry - there was a problem creating your member profile. The second member email  '" . $Email2 . "' is already being used on another account.";

						$subaction = "back";
					} else {

						//check if ID is same

						$row = reset($result);

						$id = $row["id"];

						if ($id != $ID2) {

							//trying to use emailaddress of another profile

							if ($Email2 == $Email) {

								//same as main

								$errormessage = "Sorry - there was a problem updating your member information. The second member email '" . $Email2 . "' must be different to the main member.";
							} else {

								$errormessage = "Sorry - there was a problem updating your member information. The second member email '" . $Email2 . "' is the already being used on another account.";
							}

							$subaction = "back";
						}
					}
				}
			}

			$username = $Login;

			$suffix = 1;

			$unique = false;

			while ($unique == false) {

				//test uniqueness

				$query = $db->getQuery(true)

					->select('*')

					->from($db->qn('#__users'))

					->where($db->qn('username') . ' = ' . $db->q($username));

				$usernames = $db->setQuery($query)->loadAssocList();

				$num_rows = count($usernames);

				if ($num_rows == 0) {

					$unique = true;
				} else {

					$username .= $suffix;

					$suffix += 1;
				}
			}
		}

		if ($subaction != "back") {

			//check and remove a second voting member detail if NOT family membership. 

			//This can happen when changing from family to single 

			if ($MemTypeCode != 2 && $MemTypeCode != 4) {

				//can't have second member

				$Title2 = "";

				$FirstName2 = "";

				$LastName2 = "";

				$Email2 = "";

				//need way to remove second account login too ...................................

			}


			if ($FirstName) {

				$fullname = $FirstName;
			} else {

				$fullname = "";
			}

			if ($LastName) {

				if ($fullname) {

					$fullname .= " " . $LastName;
				} else {

					$fullname .= $LastName;
				}
			}

			if ($FirstName2) {

				$fullname2 = $FirstName2;
			} else {

				$fullname2 = "";
			}

			if ($LastName2) {

				if ($fullname2) {

					$fullname2 .= " " . $LastName2;
				} else {

					$fullname2 .= $LastName2;
				}
			}

			//update keywords

			$keywords = "";

			$keywords .= " " . $fullname;

			$keywords .= " " . $fullname2;

			$keywords .= " " . $Email;

			$keywords .= " " . $Email2;

			$keywords .= " " . $Address1;

			$keywords .= " " . $Address2;

			$keywords .= " " . $Address3;

			$keywords .= " " . $Address4;

			$keywords .= " " . $PostCode;

			$keywords .= " " . $Country;

			$keywords .= " " . $CountryCode;

			$keywords .= " " . $MemNo;

			$keywords .= " " . $MembershipNo;

			$keywords .= " " . $Login;

			//change to lower case

			$keywords = strtolower($keywords);

			//remove dup words

			$text = $keywords;

			while (is_matched($text)) {

				$text = replace($text);
			}

			$changes = "";

			//autofill Login and Password if not exist

			if (!$PW) {

				$PW = createRandomPassword();
			}

			if (!$Login) {

				$Login = $MembershipNo;
			}

			if ($ID == "new") {

				//first add to jos user table

				$JoinDate = date("Y-m-d H:i:s");

				$name = "$fullname";

				$username = $Login;

				$suffix = 1;

				$unique = false;

				while ($unique == false) {

					//test uniqueness

					$query = $db->getQuery(true)

						->select('*')

						->from($db->qn('#__users'))

						->where($db->qn('username') . ' = ' . $db->q($username));

					$usernames = $db->setQuery($query)->loadAssocList();

					$num_rows = count($usernames);

					if ($num_rows == 0) {

						$unique = true;
					} else {

						$username .= $suffix;

						$suffix += 1;
					}
				}

				$email = $Email;

				$password = UserHelper::hashPassword($PW);

				$block = "1";

				$sendEmail = "1";

				$registerDate = $JoinDate;

				$lastvisitDate = "";

				$activation = "";

				$params = "{}";

				$insert = new \stdClass();

				$insert->name = $name;

				$insert->username = $username;

				$insert->email = $email;

				$insert->password = $password;

				$insert->block = $block;

				$insert->sendEmail = $sendEmail;

				$insert->registerDate = $registerDate;

				$insert->lastvisitDate = $lastvisitDate;

				$insert->activation = $activation;

				$insert->params = $params;

				$db->insertObject('#__users', $insert, 'id');

				$userid = $insert->id;

				$MembershipNo = "" . sprintf("%06d", $userid);

				$group_id = "2"; //registered

				$insert = new \stdClass();

				$insert->user_id = $userid;

				$insert->group_id = $group_id;

				$db->insertObject('#__user_usergroup_map', $insert);



				//work out Joomla group codes from membership type

				//second family member group 25

				switch ($MemTypeCode) {

					case "1":

						$memtypecode = "14";

						break;

					case "2":

						$memtypecode = "15";

						break;

					case "3":

						$memtypecode = "16";

						break;

					case "4":

						$memtypecode = "17";

						break;

					case "5":

						$memtypecode = "20";

						break;

					case "6":

						$memtypecode = "19";

						break;

					case "7":

						$memtypecode = "21";

						break;
				}



				$group_id = $memtypecode; //memtype code

				$insert = new \stdClass();

				$insert->user_id = $userid;

				$insert->group_id = $group_id;

				$db->insertObject('#__user_usergroup_map', $insert);

				//create new member in tblMembers for main user

				$newby = 1;

				$track = time();

				$insert = new \stdClass();

				$insert->ID = $userid;

				$insert->Login = $Login;

				$insert->PW = $PW;

				$insert->MemStatus = 1;

				$insert->Track = $track;

				$insert->DateJoined = $JoinDate;

				$insert->Level = 10;

				$insert->MembershipNo = $MembershipNo;

				$db->insertObject($memtable, $insert);

				$changes .= "New application submitted<br>";

				//MGM check if a coupon has been allocated to this email and if paying member and coupon not already used, adjust fee
				$couponmessage = "";
				$admincouponmessage = "";
				if ($BasicSub > 0) {
					//paying member
					$query = $db->getQuery(true)

						->select('*')

						->from($db->qn('tblCoupon'))

						->where($db->qn('email_recipient') . ' = ' . $db->q($Email));

					$result = $db->setQuery($query)->loadAssocList();

					$num_rows = count($result);

					if ($num_rows > 0) {
						//there is a coupon for this recipient email

						$row = reset($result);
						$coupon_id = $row["id"];
						$coupon_value = $row["value"];
						$coupon_type = $row["type"];
						$coupon_donor_id = $row["user_id"];
						$coupon_donor_email = $row["email_donor"];
						$coupon_date_used = $row["date_used"];

						if (!$coupon_date_used) {
							//still unused
							if ($coupon_type == "%" && $coupon_value > 0) {
								//valid value
								$discount_value = $BasicSub * $coupon_value / 100;
								$prediscount_value = $BasicSub;
								$BasicSub = $BasicSub - $discount_value;

								$couponmessage = "A discount has been applied of £" . $discount_value . " proposed by " . $coupon_donor_email;
								$admincouponmessage = "A discount has been applied of £" . $discount_value . " proposed by " . $coupon_donor_email;

								//update to used

								$update = new \stdClass();

								$used_date = date("Y-m-d");

								$update->date_used = $used_date;

								$update->user_id_recipient = $userid;

								$update->id = $coupon_id;

								$db->updateObject('tblCoupon', $update, 'id') or die("Couldn't update Coupon status");
							} else {
								//invalid value
								$admincouponmessage = "The voucher from " . $coupon_donor_email . " is invalid.";
							}
						} else {
							//already used
							$admincouponmessage = "A discount of £" . $discount_value . " proposed by " . $coupon_donor_email . " was used on " . $coupon_date_used . ".";
						}
					} else {

						//no coupon	for this email		

					}
				}

				//end of new mainmember inserts

			}

			if (!empty($ID2)) {

				//already a second member registered

				//may need updating or blocking if changed family to single



			} elseif ($MemTypeCode == 2 || $MemTypeCode == 4) {

				//new family membership could be new joiner or existing member upgrading

				// create second member

				//first add to jos user table



				if ($FirstName2 && $LastName2) {

					$name = "$FirstName2 $LastName2";
				} else {

					//use main member names with (partner) added

					$name = "$FirstName $LastName (partner)";
				}

				if ($FirstName2 && $LastName2) {

					$username = strtolower(str_replace(" ", "", $FirstName2 . $LastName2));
				} else {

					$username = strtolower(str_replace(" ", "", $FirstName . $LastName . "partner"));
				}

				//check unique, else add number on end until it is

				$suffix = 1;

				$unique = false;

				while ($unique == false) {

					//test uniqueness

					$query = $db->getQuery(true)

						->select('*')

						->from($db->qn('#__users'))

						->where($db->qn('username') . ' = ' . $db->q($username));

					$usernames = $db->setQuery($query)->loadAssocList();

					$num_rows = count($usernames);

					if ($num_rows == 0) {

						$unique = true;
					} else {

						$username .= $suffix;

						$suffix += 1;
					}
				}



				$JoinDate = date("Y-m-d H:i:s");

				if (!$Email2) {

					//should be email2 as mandatory field but in case not use main member email for now

					$email = $Email;
				} else {

					$email = $Email2;
				}

				//jimport('joomla.application.component.helper');

				$PW2 = UserHelper::genRandomPassword(8);

				// $salt = UserHelper::genRandomPassword(32);

				// $crypt = UserHelper::getCryptedPassword($PW2, $salt);

				// $password = $crypt . ':' . $salt;

				$password = UserHelper::hashPassword($PW2);

				if ($ID == "new") {

					//new member so block until paid

					$block = "1";
				} else {

					//old member upgrading so allow until next renewal

					$block = "0";
				}

				$sendEmail = "1";

				$registerDate = $JoinDate;

				$lastvisitDate = "";

				$activation = "";

				//$params=addslashes("{\"mainmemberid\":\"".$userid."\"}");

				$params = json_encode(["mainmemberid" => $userid]);



				$insert = new \stdClass();

				$insert->name = $name;

				$insert->username = $username;

				$insert->email = $email;

				$insert->password = $password;

				$insert->block = $block;

				$insert->sendEmail = $sendEmail;

				$insert->registerDate = $registerDate;

				$insert->lastvisitDate = $lastvisitDate;

				$insert->activation = $activation;

				$insert->params = $params;

				$db->insertObject('#__users', $insert, 'id');

				$ID2 = $insert->id;

				$Login2 = $username;



				$group_id = "2"; //registered

				$insert = new \stdClass();

				$insert->user_id = $ID2;

				$insert->group_id = $group_id;

				$db->insertObject('#__user_usergroup_map', $insert);



				$group_id = 25; //family partner code

				$insert = new \stdClass();

				$insert->user_id = $ID2;

				$insert->group_id = $group_id;

				$db->insertObject('#__user_usergroup_map', $insert);
			}



			//work out what has changed for change log

			$changedate = date("Y-m-d H:i:s");

			$query = $db->getQuery(true)

				->select('*')

				->from($db->qn($memtable))

				->where($db->qn('ID') . ' = ' . $db->q($userid));

			$result = $db->setQuery($query)->loadAssocList();

			$fields = "";

			$adminmessage = "";

			foreach ($result as $row) {

				foreach (array_keys($row) as $fieldname) {

					$fields .= ", " . $fieldname;

					//



					//echo("| ".$fieldname." - ".${$fieldname}." / ".$row[$fieldname]." ".$x." ". $y);

					if (isset(${$fieldname})) {

						if (addslashes(${$fieldname}) != addslashes($row[$fieldname])) {

							if ($fieldname == "PW") {

								$changes .= "Password changed<br>";

								//update in #__users

								// $salt = UserHelper::genRandomPassword(32);

								// $crypt = UserHelper::getCryptedPassword($PW, $salt);

								// $password = $crypt . ':' . $salt;

								$password = UserHelper::hashPassword($PW);

								$update = new \stdClass();

								$update->password = addslashes($password);

								$update->id = $userid;

								$db->updateObject('#__users', $update, 'id') or die("Couldn't update password");

								$screenmessage .= "\n\nPassword updated\n";

								$adminmessage .= "\n\nPassword updated";
							} else {

								$changes .= $fieldname . " changed from " . stripslashes($row[$fieldname]) . " to " . ${$fieldname} . "<br>";
							}

							if ($fieldname == "PaymentMethod") {

								//check if changed to dd

								if (${$fieldname} == "dd" && $row[$fieldname] != "" && $row[$fieldname] != "dd") {

									$PaymentMethodChanged = "dd";

									$screenmessage .= "\n\n<b>Thank you for changing your payment method </b>to Direct debit which is of great help to our subscription administration.<br><br><font color=ff0000><b>Please print out your direct debit form <a href=\"" . $ddformpath . "\">here</a></b></font>, fill in the details and post it to the address shown at the top of the form.<br><br>\n";

									$adminmessage .= "\n\nPayment method changed to DD";
								}
							}

							if ($fieldname == "MemType") {

								//check if changed

								if ($MemType == "Family" && $row["MemType"] == "Ordinary") {

									//changed to family

									$MemTypeCodeChanged = "tofamily";

									$screenmessage .= "\n\nYour subscription type has been changed from single to family. An email has been sent to " . $fullname2 . " at " . $Email2 . " with login instructions. As your annual subscription has increased, please contact membership@barges regarding this extra payment.";

									$adminmessage .= "\n\nSubscription type changed from ordinary (single) to family";

									//message to fam 2

									$to = $Email2;

									$from = $registrationemail;

									$fromname = 'DBA auto administration';

									$content = "Your DBA family member subscription has been activated. As the second family member, you can login at " . $memberloginurl . " to access the members section.\n\nYour login and password are:\nLogin: " . $Login2 . "\nPassword: Click reminder if you have forgotten it.\n\nThanks for subscribing to the " . $sitename . " website";

									if ($mailOn) {

										$mailer = Factory::getMailer();

										$mailer->setSender([$config->get('mailfrom'), $config->get('fromname')]);

										$mailer->addRecipient($to);

										$mailer->addReplyTo($from, $fromname);

										$mailer->setSubject($subject);

										$mailer->setBody(nl2br($content));

										$mailer->isHtml(true);

										$return = $mailer->Send();
									} else echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";



									//message admin

									$to = $registrationemail;

									$from = $registrationemail;

									$fromname = 'DBA auto administration';

									$content = $adminmessage . "\n\nMember " . $MembershipNo . " " . $LastName . " Payment by: " . $PaymentMethod;

									if ($mailOn) {

										$mailer = Factory::getMailer();

										$mailer->setSender([$config->get('mailfrom'), $config->get('fromname')]);

										$mailer->addRecipient($to);

										$mailer->addReplyTo($from, $fromname);

										$mailer->setSubject($subject);

										$mailer->setBody(nl2br($content));

										$mailer->isHtml(true);

										$return = $mailer->Send();
									} else echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";
								}

								if ($MemType == "Ordinary" && $row["MemType"] == "Family") {

									//changed to single

									$MemTypeCodeChanged = "tosingle";

									$screenmessage .= "\n\nYour subscription type has been changed from family to single. The second family member login has been blocked.";

									$adminmessage .= "\n\nSubscription type changed from family to ordinary (single)";

									//message admin

									$to = $registrationemail;

									$from = $registrationemail;

									$fromname = 'DBA auto administration';

									$content = $adminmessage . "\n\nMember " . $MembershipNo . " " . $LastName . " Payment by: " . $PaymentMethod;

									if ($mailOn) {

										$mailer = Factory::getMailer();

										$mailer->setSender([$config->get('mailfrom'), $config->get('fromname')]);

										$mailer->addRecipient($to);

										$mailer->addReplyTo($from, $fromname);

										$mailer->setSubject($subject);

										$mailer->setBody(nl2br($content));

										$mailer->isHtml(true);

										$return = $mailer->Send();
									} else echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";
								}
							}

							if ($fieldname == "Email") {

								//update #__users

								$screenmessage .= "\n\nEmail has been updated\n";

								$adminmessage .= "\n\nEmail has been updated";

								$update = new \stdClass();

								$update->email = addslashes($Email);

								$update->id = $userid;

								$db->updateObject('#__users', $update, 'id') or die("Couldn't update email");
							}

							if ($fieldname == "Email2") {

								//update #__users

								$screenmessage .= "\n\nPartner email has been updated\n";

								$adminmessage .= "\n\nPartner email has been updated";

								$update = new \stdClass();

								$update->email = addslashes($Email2);

								$update->id = $ID2;

								$db->updateObject('#__users', $update, 'id') or die("Couldn't update partner email");
							}



							if ($fieldname == "Login") {

								//update #__users

								$screenmessage .= "\n\nMain Login user name has been updated\n";

								$adminmessage .= "\n\nMain Login user name has been updated";

								$update = new \stdClass();

								$update->username = addslashes($Login);

								$update->id = $userid;

								$db->updateObject('#__users', $update, 'id') or die("Couldn't update Joomla user name");
							}

							if ($fieldname == "FirstName" || $fieldname == "LastName") {

								//update #__users

								$screenmessage .= "\n\nUser First or Family name has been updated\n";

								$adminmessage .= "\n\nUser First or Family name has been updated";

								$update = new \stdClass();

								$update->name = addslashes($fullname);

								$update->id = $userid;

								$db->updateObject('#__users', $update, 'id') or die("Couldn't update Joomla name");
							}

							if ($fieldname == "FirstName2" || $fieldname == "LastName2") {

								//update #__users

								$screenmessage .= "\n\nUser First or Family name of second family member has been updated\n";

								$adminmessage .= "\n\nUser First or Family name  seconof second family member has been updated";

								$update = new \stdClass();

								$update->name = addslashes($fullname2);

								$update->id = $ID2;

								$db->updateObject('#__users', $update, 'id') or die("Couldn't update Joomla partner name");
							}

							if ($fieldname == "MemStatus") {

								//update #__users

								if ($MemStatus != "5" && $MemStatus != "7") {

									//unblock J user 

									$screenmessage .= "\n\nStatus has been unblocked\n";

									$adminmessage .= "\n\nStatus has been unblocked";

									$update = new \stdClass();

									$update->block = '0';

									$update->id = $userid;

									$db->updateObject('#__users', $update, 'id') or die("Couldn't update Joomla status");

									//also family second member?

									if ($row["MemType"] == "Family") {

										$screenmessage .= "\n\nPartner status has been unblocked\n";

										$adminmessage .= "\n\nPartner status has been unblocked";

										$update = new \stdClass();

										$update->block = '0';

										$update->id = $ID2;

										$db->updateObject('#__users', $update, 'id') or die("Couldn't update Joomla partner status");
									}



									//check for a barge register entry and change status back to live if found.

									$query = $db->getQuery(true)

										->select('*')

										->from($db->qn('tblAssetsMembers'))

										->where($db->qn('MembershipNo') . ' = ' . $db->q($userid));

									$vessels = $db->setQuery($query)->loadAssocList();

									$vrows = count($vessels);

									if ($vrows > 0) {

										foreach ($vessels as $vrow) {

											$VesselID = stripslashes($vrow["ID"]);

											$update = new \stdClass();

											$update->Status = '1';

											$update->LastUpdate = $changedate;

											$update->ID = $VesselID;

											$db->updateObject('tblAssetsMembers', $update, 'ID') or die("Couldn't update register");
										}
									}

									//remove any DateCeased

									$TheDateCeased = "NULL";
								} elseif ($MemStatus == 5) {



									//set the termination date

									$TheDateCeased = $changedate;

									//set the joomla user block user from login

									$update = new \stdClass();

									$update->block = '1';

									$update->id = $userid;

									$db->updateObject('#__users', $update, 'id') or die("Couldn't update status");

									//and a family partner?

									if (!empty($ID2)) {

										$update = new \stdClass();

										$update->block = '1';

										$update->id = $ID2;

										$db->updateObject('#__users', $update, 'id') or die("Couldn't update Partner status");
									}
								}
							}

							if ($fieldname == "Services") {

								//check if volunteering has changed and inform committee

								$query = $db->getQuery(true)

									->select('*')

									->from($db->qn('tblServices'))

									->where($db->qn('ServiceCategory') . ' = ' . $db->q('volunteering'))

									->order($db->qn('ServiceSortOrder'));

								$sections = $db->setQuery($query)->loadObjectList();

								$num_services += count($sections);

								$past_volunteering = "|";

								$past_volunteering_desc = "|";

								$past_volunteering_total = 0;

								$past_services = $row[$fieldname];

								$new_volunteering = "|";

								$new_volunteering_desc = "|";

								$new_volunteering_total = 0;

								//$screenmessage.="\nPrevious services: ".$past_services."\nNew services: ".$Services;



								//narrow down the services just to volunteering if any

								foreach ($sections as $sectionrow) {

									$serviceid = $sectionrow->ServiceID;

									$servicedesc = $sectionrow->ServiceDescGB;

									//check if this service is in the old selection and if so add to temp old

									if ($found = strstr($past_services, "|" . $serviceid . "|")) {

										$past_volunteering .= $serviceid . "|";

										$past_volunteering_desc .= $servicedesc . "|";

										$past_volunteering_total += 1;
									}

									//check if this service is in the new selection and if so add to temp new

									if ($found = strstr($Services, "|" . $serviceid . "|")) {

										$new_volunteering .= $serviceid . "|";

										$new_volunteering_desc .= $servicedesc . "|";

										$new_volunteering_total += 1;
									}
								}

								//compare old and new

								if ($past_volunteering != $new_volunteering) {

									//changes so inform committee

									//work out any additions



									//look for additions

									$past_volunteering_array = explode("|", $past_volunteering);

									$past_volunteering_desc_array = explode("|", $past_volunteering_desc);

									$new_volunteering_array = explode("|", $new_volunteering);

									$new_volunteering_desc_array = explode("|", $new_volunteering_desc);

									$new_volunteering_removed_desc = "|";

									$new_volunteering_added_desc = "|";

									$current_volunteering_desc = "|";

									//go through past and see if exists in new or has been removed

									foreach ($past_volunteering_array as $key => $serviceid) {

										if (($serviceid != "" && !$found = strstr($new_volunteering, "|" . $serviceid . "|"))) {

											//has been removed

											$new_volunteering_removed_desc .= $past_volunteering_desc_array[$key] . "|";
										}
									}

									if ($new_volunteering_removed_desc == "|") {

										$new_volunteering_removed_desc = "|None|";
									}

									//go through new and see if exists in past

									foreach ($new_volunteering_array as $key => $serviceid) {

										if (($serviceid != "" && !$found = strstr($past_volunteering, "|" . $serviceid . "|"))) {

											$new_volunteering_added_desc .= $new_volunteering_desc_array[$key] . "|";
										}

										if ($serviceid != "") {

											$current_volunteering_desc .= $new_volunteering_desc_array[$key] . "|";
										}
									}

									if ($new_volunteering_added_desc == "|") {

										$new_volunteering_added_desc = "|None|";
									}

									if ($current_volunteering_desc == "|") {

										$current_volunteering_desc = "|None|";
									}

									$volunteer_message = "A member has made the following changes to their DBA profile volunteer options: \n- " . $FirstName . " " . $LastName . "\n- email address " . $Email . "\n- Telephone " . $Telephone1 . "\n- Postal Country " . $Country . "\n- Joining date " . $TheDateJoined . " ";

									$volunteer_message .= "\n\nVolunteering withdrawn:" . str_replace("|", "\n- ", $new_volunteering_removed_desc);

									$volunteer_message .= "\nVolunteering additions:" . str_replace("|", "\n- ", $new_volunteering_added_desc);

									$volunteer_message .= "\nCurrent Volunteering full list:" . str_replace("|", "\n- ", $current_volunteering_desc);

									$volunteer_message .= "\n\nThis email has been sent automatically as a result of this member making a profile update. Please contact the member using the email address or other information shown at the top of this email.\n\nDo not click 'reply' to the automated sender.";

									//email changes to alert board

									$to = "volunteer@barges.org";

									$from = $registrationemail;

									$fromname = 'DBA auto administration';

									$subject = "$sitename member profile volunteering alert";

									$content = $volunteer_message . "\n\n" . $emailfooter;

									if ($mailOn) {

										$mailer = Factory::getMailer();

										$mailer->setSender([$config->get('mailfrom'), $config->get('fromname')]);

										$mailer->addRecipient($to);

										$mailer->addReplyTo($from, $fromname);

										$mailer->setSubject($subject);

										$mailer->setBody(nl2br($content));

										$mailer->isHtml(true);

										$return = $mailer->Send();

										if ($return !== true) {

											throw new \Exception($db->getErrorMsg(), 500);
										}
									} else echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";

									$to = "dbawebsite@barges.org";

									if ($mailOn) {

										$mailer = Factory::getMailer();

										$mailer->setSender([$config->get('mailfrom'), $config->get('fromname')]);

										$mailer->addRecipient($to);

										$mailer->addReplyTo($from, $fromname);

										$mailer->setSubject($subject);

										$mailer->setBody(nl2br($content));

										$mailer->isHtml(true);

										$return = $mailer->Send();

										if ($return !== true) {

											throw new \Exception($db->getErrorMsg(), 500);
										}
									} else echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";
								}
							}
						}
					}
				}
			}

			//$message.=$changes;

			//check account exists

			if (!$userid) {

				$errormessage = "Sorry - there was a problem updating your member information. Please go to the <a href=\"" . $memberloginurl . "\">main login page</a> to try again or retrieve your password";

				$subaction = "back";
			} else {

				$changedate = date("Y-m-d H:i:s");

				$datenowdisplay = date("d/m/Y");

				$LastUpdate = "$changedate";

				//update  account details		

				$update = new \stdClass();

				$update->Login = addslashes($Login);

				$update->PW = addslashes($PW);

				$update->Title = addslashes($Title);

				$update->FirstName = addslashes($FirstName);

				$update->LastName = addslashes($LastName);

				$update->Title2 = addslashes($Title2);

				$update->FirstName2 = addslashes($FirstName2);

				$update->LastName2 = addslashes($LastName2);

				$update->Email = addslashes($Email);

				$update->Email2 = addslashes($Email2);

				if (!empty($ID2)) $update->ID2 = addslashes($ID2);

				$update->Address1 = addslashes($Address1);

				$update->Address2 = addslashes($Address2);

				$update->Address3  = addslashes($Address3);

				$update->Address4 = addslashes($Address4);

				$update->PostCode = addslashes($PostCode);

				$update->Country = addslashes($Country);

				$update->CountryCode = addslashes($CountryCode);

				$update->CountryCodeCruising = addslashes($CountryCodeCruising);

				$update->Telephone1 = addslashes($Telephone1);

				$update->Telephone2 = addslashes($Telephone2);

				$update->Mobile2 = addslashes($Mobile2);

				$update->Services = $Services;

				$update->Situation = $Situation;

				$update->LastUpdate = $LastUpdate;

				$update->PostZone = $PostZone;

				$update->BasicSub = $BasicSub;

				$update->PaymentMethod = $PaymentMethod;

				$update->MemTypeCode = $MemTypeCode;

				$update->MemType = $MemType;

				$update->Keywords = addslashes($keywords);

				$update->MembershipNo = addslashes($MembershipNo);



				if ($membershipadmin == true) {

					//add admin fields	

					$update->AccountNo = $TheAccountNo;

					$update->SortCode = $TheSortCode;

					$update->DateJoined = $TheDateJoined;

					$update->DatePaid = empty($TheDatePaid) ? null : $TheDatePaid;

					$update->MemStatus = addslashes($MemStatus);

					$update->NotesAdmin = addslashes($TheNotesAdmin);
				}

				if ($TheDateCeased) $update->DateCeased = $TheDateCeased == "NULL" ? null : $TheDateCeased;

				$update->ID = $userid;

				$db->updateObject($memtable, $update, 'ID', true);

				//MGM update coupon table if new proposer
				if ($coupon_email_recipient) {
					$insert = new \stdClass();

					//$insert->user_id_recipient = 0;

					//$insert->date_used = "";

					$insert->email_recipient = addslashes($coupon_email_recipient);

					$insert->email_donor = addslashes($Email);

					$insert->user_id = $userid;

					$proposed_date = date("Y-m-d");

					$insert->date_proposed = $proposed_date;

					$db->insertObject('tblCoupon', $insert) or die("Couldn't update coupon table");

					$changes .= "Member get Member proposal - recipient " . $coupon_email_recipient . "<br>";
				}

				if ($couponmessage) {

					$changes .= $couponmessage . "<br>";
				}

				//end MGM

				$subject = "Profile update";

				$changelogtext = "";

				$screenmessage .= "\n\nProfile changes have been saved.";



				if ($changes) {

					$changelogtext .= $changes;

					//update change log if not admin change

					$insert = new \stdClass();

					$insert->MemberID = $userid;

					$insert->Subject = $subject;

					$insert->ChangeDesc = $changelogtext;

					$insert->ChangeDate = $changedate;

					$db->insertObject('tblChangeLog', $insert) or die("Couldn't update change log");
				}





				if ($infoid) {

					//admin only options

					$maillist = "_" . $userid . "_";

					$screenmessage .= "<br>Details updated.";

					$screenmessage .= "<br>Letters <select class=formcontrol name='report' id='report'>\n";

					$screenmessage .= "<option value=\"DBA_letter_Welcome.rtf\">New member welcome</option>\n";

					$screenmessage .= "<option value=\"DBA_letter_Welcome_eBF.rtf\">New member welcome eBF</option>\n";

					$screenmessage .= "<option value=\"DBA_letter_Sub_cc_Reminder.rtf\">Sub due by cc reminder</option>\n";

					$screenmessage .= "<option value=\"DBA_letter_Sub_so_Reminder.rtf\">Sub due by so reminder</option>\n";

					$screenmessage .= "<option value=\"DBA_letter_Change_to_DD_thanks.rtf\">Change to DD / thanks</option>\n";

					$screenmessage .= "</select><input class=\"formcontrol\" type=\"button\" name=\"openletter\" onClick=\"mergedoc('','" . $userid . "')\" value=\"Open\">\n";



					//$screenmessage="Click <a href=\"javascript:doreport('../members/so_form.php?maillist=".$maillist."');\">here</a> to print a standing order form.";

				}


				if ($newby == 1) {

					$maillist = "_" . $userid . "_";

					if ($infoid) {



						$screenmessage = "New member added. Click <a href=\"javascript:doreport('../admin/maillist_report_welcome_letter.php?maillist=" . $maillist . "');\">here</a> to print a welcome letter.";

						$screenmessage = "Click <a href=\"javascript:doreport('../members/so_form.php?maillist=" . $maillist . "');\">here</a> to print a standing order form.";
					} else {

						if ($PaymentMethod == "cc") {

							$message = $thanksforsubscribing_cc;

							$screenmessage = $thanksforsubscribing_cc;

							//MGM
							if ($couponmessage) {
								$message .= $couponmessage;
								$screenmessage .= $couponmessage;
							}

							$item_name = "New DBA subscription - " . $MemType;

							$item_number = "DBA-SubNew";

							$shippingcost = 0;

							//end MGM

							$item_name = "New DBA subscription - " . $MemType;

							$item_number = "DBA-SubNew";

							$shippingcost = 0;

							//$buy="<form target=\"paypal\" action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\">\n";

							$buy = "<form action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\">\n";

							$buy .= "<input type=\"hidden\" name=\"add\" value=\"1\">\n";

							$buy .= "<input type=\"hidden\" name=\"cmd\" value=\"_cart\">\n";

							$buy .= "<input type=\"hidden\" name=\"business\" value=\"treasurer@barges.org\">\n";

							$buy .= "<input type=\"hidden\" name=\"item_name\" value=\"" . $item_name . "\">\n";

							$buy .= "<input type=\"hidden\" name=\"item_number\" value=\"" . $item_number . "\">\n";

							$buy .= "<input type=\"hidden\" name=\"amount\" value=\"" . $BasicSub . "\">\n";

							//$buy.="<input type=\"hidden\" name=\"no_note\" value=\"1\">\n";

							$buy .= "<input type=\"hidden\" name=\"currency_code\" value=\"GBP\">\n";

							$buy .= "<input type=\"hidden\" name=\"lc\" value=\"GB\">\n";

							$buy .= "<input type=\"hidden\" name=\"return\" value=\"" . $paypalthanks . "\">\n";

							$buy .= "<input type=\"hidden\" name=\"cancel_return\" value=\"" . $paypalcancel . "\">\n";

							$buy .= "<input type=\"hidden\" name=\"notify_url\" value=\"" . $paypalreturn . "\">\n";

							$buy .= "<input type=\"hidden\" name=\"no_shipping\" value=\"1\">\n";

							$buy .= "<input type=\"hidden\" name=\"custom\" value=\"" . $userid . "\">\n";

							$buy .= "<input type=\"hidden\" name=\"email\" value=\"" . $Email . "\">\n";

							$buy .= "<input type=\"hidden\" name=\"first_name\" value=\"" . $FirstName . "\">\n";

							$buy .= "<input type=\"hidden\" name=\"last_name\" value=\"" . $LastName . "\">\n";

							$buy .= "<input type=\"hidden\" name=\"address1\" value=\"" . $Address1 . "\">\n";

							$buy .= "<input type=\"hidden\" name=\"address2\" value=\"" . $Address2 . "\">\n";

							$buy .= "<input type=\"hidden\" name=\"city\" value=\"" . $Address3 . "\">\n";

							$buy .= "<input type=\"hidden\" name=\"state\" value=\"" . $Address4 . "\">\n";

							$buy .= "<input type=\"hidden\" name=\"applicationformstatus\" value=\"saved\">\n";

							$buy .= "<input type=\"hidden\" name=\"zip\" value=\"" . $PostCode . "\">\n";

							$buy .= "<input type=\"hidden\" name=\"country\" value=\"" . $CountryCode . "\">\n";

							$buy .= "<input type=\"hidden\" name=\"image_url\" value=\"http://www.barges.org/com_waterways_guide/paypal/dba_logo_icon_150x150.png\">\n";

							$buy .= "<input type=\"image\" src=\"Image/shop/btn_addtobasket.gif\" border=\"0\" name=\"submit\" alt=\"PayPal � pay online!\">\n";

							//$buy.="<a href=\"#\" onClick=\"document.paypal.submit()\"><img src=\"Image/shop/btn_addtobasket.gif\" width=\"144\" height=\"24\" border=\"0\" alt=\"Add to basket\"></a>";

							$buy .= "</form>\n";

							$screenmessage .= "<br><br>To complete your payment, click once on the 'Add to basket' button and proceed to the checkout.\n";
						} elseif ($PaymentMethod == "dd") {



							//direct debit

							$message = $thanksforsubscribing_dd;

							$message .= "\n\nIf you haven't already done so, please now print out your direct debit form, enter the additional information required and send it to:\n\n" . $membershipmailaddress . "\n";



							$screenmessage = $thanksforsubscribing_dd;

							//MGM
							if ($couponmessage) {
								$message .= $couponmessage;
								$screenmessage .= $couponmessage;
							}
							//end MGM

							$screenmessage .= "<br><br>To print out your direct debit form click <a href=\"" . $ddformpath . "\">here</a>. Please fill in the details and post to the address shown at the top of the form\n";
						} elseif ($PaymentMethod == "so") {



							//standing order

							$message = $thanksforsubscribing_so;

							//MGM
							if ($couponmessage) {
								$message .= $couponmessage;
								$screenmessage .= $couponmessage;
							}
							//end MgM

							$message .= "\n\nPlease now print out your standing order form, enter the additional information required and send it to:\n\n" . $membershipmailaddress . "\n";



							$screenmessage = $thanksforsubscribing_so;

							$screenmessage .= "<br><br>To print out your standing order form click <a href=\"javascript:doreport('../members/so_form_outer.php?maillist=" . $maillist . "');\">here</a>. Please fill in the details and post to the address shown at the bottom of the form\n";
						} elseif ($PaymentMethod == "ch") {

							//cheque

							$message = $thanksforsubscribing_ch;

							//MGM
							if ($couponmessage) {
								$message .= $couponmessage;
								$screenmessage .= $couponmessage;
							}
							//end MgM

							$message .= "\n\nPlease make your bank transfer for <b>GBP " . $BasicSub . "</b> quoting your membership number <b>" . $MembershipNo . "</b> as the reference\n\n";



							$screenmessage = $thanksforsubscribing_ch;

							$screenmessage .= "<br><br>Please make your bank transfer for <b>GBP " . $BasicSub . "</b> quoting your membership number <b>" . $MembershipNo . "</b> as the reference\n";



							//$MembershipNo $BasicSub

						} else {

							$message = $thanksforsubscribing_foc;

							$screenmessage = $thanksforsubscribing_foc;
						}

						//send 'new member' email to supervisor

						//Create email messages





						$to = $Email;

						$from = $registrationemail;

						$fromname = 'DBA administration';

						$subject = "Application to subscribe to the $sitename website";

						//check if admin addition and if so don't email



						//confirm emails

						$content = $message . "\n\n" . $emailfooter;

						if ($mailOn) {

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

						$screenmessage .= "<br>An email confirmation of this entry has been sent to " . $to . "\n";

						$subject = "Application to subscribe " . $LastName . " " . $MembershipNo . " to the $sitename website ";

						$content = "A new member " . $LastName . " " . $MembershipNo . " has submitted an application to subscribe to the $sitename website.\n\nTo authorise this entry, login at \n" . $siteurl . "/ and use contact manager to locate and complete the application.\n\nPayment Method:" . $PaymentMethod;

						$from = $registrationemail;

						//MGM
						if ($couponmessage) {

							$content .= "\n\n" . $couponmessage . "\n";
						}
						//end MgM

						$fromname = "DBA Registration";

						//copy mail to admin to await payment and manually activate

						$to = $registrationemail;



						if ($mailOn) {

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
			}
		}

		if ($subaction == "Approve") {

			//update status to 2 (paid up) to make live on site and send email to confirm acceptance

			$update = new \stdClass();

			$update->Status = 2;

			$update->ID = $userid;

			$db->updateObject($memtable, $update, 'ID');

			//unblock in user table in case blocked

			$update = new \stdClass();

			$update->block = '0';

			$update->id = $userid;

			$db->updateObject('#__users', $update, 'id') or die("Couldn't update Joomla status");



			//Create email messages

			$message = "Your application has been approved for the $sitename\n\nYou can now login at any time at " . $memberloginurl . " to change any details or access the members section.\n\nYour login and password are:\nLogin: " . $Login . "\nPassword: Click 'Forgot your password?' if you have forgotten it.\n\nThanks for subscribing to " . $sitename . ".";

			$to = $Email;



			$screenmessage .= "<br>The Profile for member '" . $MembershipNo . "' is now live and the following message has been emailed to " . $to . " <br><br>" . nl2br($message);

			$from = $registrationemail;

			$fromname = "DBA Registration";

			$subject = "Your application has been approved for the $sitename website";

			$content = $message . "\n\n" . $emailfooter;



			if ($mailOn) {

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



			//also family second member?

			if ($MemType == "Family") {

				$update = new \stdClass();

				$update->block = '0';

				$update->id = $ID2;

				$db->updateObject('#__users', $update, 'id') or die("Couldn't update Joomla partner status");

				//Create email messages

				$message = "Your family member application has been approved for the $sitename\n\nYou can now login at any time at " . $memberloginurl . " to access the members section.\n\nYour login and password are:\nLogin: " . $Login2 . "\nClick the 'Forgot your password?' to reset your password for the first time to one of your choice.\n\nThanks for subscribing to " . $sitename . ".";

				$to = $Email2;

				$screenmessage .= "<br>The Profile for the second family member is also now live and the following message has been emailed to " . $to . " <br><br>" . nl2br($message);

				$from = $registrationemail;

				$fromname = "DBA Registration";

				$subject = "Your application has been approved for the $sitename website";

				$content = $message . "\n\n" . $emailfooter;



				if ($mailOn) {

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



			$subject = "Profile update";

			$changelogtext = "Application approved<br>";

			$insert = new \stdClass();

			$insert->MemberID = $userid;

			$insert->Subject = $subject;

			$insert->ChangeDesc = $changelogtext;

			$insert->ChangeDate = $changedate;

			$db->insertObject('tblChangeLog', $insert) or die("Couldn't update change log");
		}

		//echo("<table>\n");

		echo (nl2br($screenmessage)) . $buy;

		//echo("</table>\n");

	}

?>

	<style type="text/css" media="screen,projection">
		.formtextarea {

			font-family: Arial, Helvetica, sans-serif;

			font-size: 95%;

			font-weight: lighter;

			height: 120px;

			width: 100%;

		}
	</style>





	<form name="form" enctype="multipart/form-data" method="post">

		<?php



		//Form display *******************************************************************************

		if (!$subaction || $subaction == "back") {





			if (!$errormessage && ($userid != $infoid || $ID == "new" || $ID == "" || $membershipadmin == true)) {

				if ($ID == "new") {

					$screenmessage = "Thank you for deciding to join the DBA. Please fill in the application form and click the 'Join' button at the end.";
				} else {

					$screenmessage = "To save any changes, click the 'Update' button at the end of the form.";
				}
			} else {

				$screenmessage = "<font color=#ff0000><b>$errormessage</b></font>";
			}

			//get existing member details

			if (!$user->guest && $userid != "new") {

				//existing user so get details

				//if(!$userid){

				//profile for logged in user

				//	$userid = $user->id;

				//	$Login=$user->username;

				//}

				$query = $db->getQuery(true)

					->select('*')

					->from($db->qn($memtable))

					->where($db->qn('ID') . ' = ' . $db->q($userid));

				$row = $db->setQuery($query)->loadObject();

				//print_r($row); 

				if ($row) {

					//$row=$db->loadRow();

					$Login = stripslashes($row->Login);

					$PW = stripslashes($row->PW);

					$Email = stripslashes($row->Email);

					$Title = stripslashes($row->Title);

					$FirstName = stripslashes($row->FirstName);

					$LastName = stripslashes($row->LastName);

					$Title2 = stripslashes($row->Title2);

					$FirstName2 = stripslashes($row->FirstName2);

					$LastName2 = stripslashes($row->LastName2);

					//lookup login user name and email in #__users in case changed

					$query = $db->getQuery(true)

						->select('*')

						->from($db->qn('#__users'))

						->where($db->qn('id') . ' = ' . $db->q($userid));

					$mainmember = $db->setQuery($query)->loadObject();

					if ($mainmember) {

						if ($Email != $mainmember->email || $Login != $mainmember->username) {

							//changes were made outside in #__users

							$update = new \stdClass();

							$update->Login = $mainmember->username;

							$update->Email = $mainmember->email;

							$update->ID = $userid;

							if ($db->updateObject($memtable, $update, 'ID')) {

								$Email = stripslashes($mainmember->email);

								$Login = stripslashes($mainmember->username);
							}
						}
					} else {

						//no email

						//$Email2="";

						//$Login2 = "?"; 

					}



					$ID2 = stripslashes($row->ID2);

					if (!empty($ID2)) {

						//lookup email in #__users in case changed

						$query = $db->getQuery(true)

							->select('*')

							->from($db->qn('#__users'))

							->where($db->qn('id') . ' = ' . $db->q($ID2));

						$partner = $db->setQuery($query)->loadObject();

						if ($partner) {

							$Email2 = stripslashes($partner->email);

							$Login2 = stripslashes($partner->username);
						} else {

							//no email

							$Email2 = "";

							$Login2 = "?";
						}
					}

					//$Email2 = stripslashes($row->Email2); 

					$Address1 = stripslashes($row->Address1);

					$Address2 = stripslashes($row->Address2);

					$Address3  = stripslashes($row->Address3);

					$Address4 = stripslashes($row->Address4);

					$PostCode = stripslashes($row->PostCode);

					$Country = stripslashes($row->Country);

					$CountryCode = stripslashes($row->CountryCode);

					$CountryCodeCruising = stripslashes($row->CountryCodeCruising);

					$Telephone1 = stripslashes($row->Telephone1);

					$Telephone2 = stripslashes($row->Telephone2);

					$Mobile2 = stripslashes($row->Mobile2);

					$Services = $row->Services;

					$Situation = $row->Situation;

					$ShipName = $row->ShipName;

					$ShipClass = $row->ShipClass;

					$ShipYear = $row->ShipYear;

					$ShipLength = $row->ShipLength;

					$ShipBeam = $row->ShipBeam;

					$LastUpdate = $row->LastUpdate;

					$PostZone = $row->PostZone;

					$BasicSub = $row->BasicSub;

					$PaymentMethod = $row->PaymentMethod;

					$MemTypeCode = $row->MemTypeCode;

					$MemType = $row->MemType;

					$Keywords = stripslashes($row->Keywords);

					$MembershipNo = $row->MembershipNo;

					$MemNo = $row->MemNo;

					$Level = $row->Level;

					$MemStatus = $row->MemStatus;

					$DateJoined = $row->DateJoined;

					$DatePaid = $row->DatePaid;

					$DateCeased = $row->DateCeased;

					$NotesAdmin = stripslashes($row->NotesAdmin);

					$SortCode = $row->SortCode;

					$AccountNo = $row->AccountNo;



					$contact = $FirstName . " " . $LastName;

					$name = $LastName;

					$memberlevel = $row->Level;

					$memberid = $row->ID;

					if ($MembershipNo) {

						$otherinfo = "<br>Membership number:<b> $MembershipNo</b>";
					}





					if ($DatePaid) {

						$datepaiddisplay = date_to_format($DatePaid, "d");
					}

					$membergroups = $row->Groups;

					//check if in any groups

					$query = $db->getQuery(true)

						->select('*')

						->from($db->qn('tblGroupType'))

						->order($db->qn('GroupDesc'));

					$groups = $db->setQuery($query)->loadAssocList();

					$numgroups = count($groups);

					if (empty($groups)) {

						echo ("<P>Error finding available groups</P>");

						exit();
					}

					$searchmembergroups = isset($membergroup) ? $membergroups : '';

					$allgroups = "";

					$mymembergroups = "";

					foreach ($groups as $grouprow) {

						$groupid = $grouprow["GroupID"];

						$groupdesc = $grouprow["GroupDesc"];

						$allgroups .= "$groupdesc ";

						$found = strstr($searchmembergroups, "|" . $groupid . "e|");

						$myeditmembergroups = "";

						if ($found > -1) {

							if ($myeditmembergroups) {

								$myeditmembergroups .= ", ";
							}

							$myeditmembergroups .= "" . $groupdesc . "";
						}

						$found = strstr($searchmembergroups, "|" . $groupid . "|");

						if ($found > -1) {

							if ($mymembergroups) {

								$mymembergroups .= ", ";
							}

							$mymembergroups .= "" . $groupdesc . "";
						}
					}



					if ($mymembergroups) {

						$mymembergrouptxt = "Group membership: <img height=16 src='/Image/common/group.gif' width=13 border=0 alt='Group member page'> " . $mymembergroups;
					} else {

						$mymembergrouptxt = "";
					}





					//maybe add admin levels from joomla login				

					/*if (in_array("8", $userGroups) || in_array("22", $userGroups)) {

					$membershipadmin=true; //superuser or membershipadmin

				}else{

					$membershipadmin=false;

				}

				*/





					if ($mymembergrouptxt) {

						$otherinfo .= "<br>$mymembergrouptxt";
					}



					$lastupdate = $LastUpdate;





					switch ($MemStatus) {

						case 1:

							$thisstatus = "Applied awaiting payment";

							$substatus = 0;

							break;

						case 2:

							$thisstatus = "Paid up - last payment received on $datepaiddisplay";

							$substatus = 1;

							break;

						case 3:

							$thisstatus = "Renewal due - last payment received on $datepaiddisplay";

							$substatus = 1;

							break;

						case 4:

							$thisstatus = "Gone away, please contact $membershipemail - last payment received on $datepaiddisplay";

							$substatus = 1;

							break;

						case 5:

							$thisstatus = "Terminated - last payment received on $datepaiddisplay";

							$substatus = 0;

							break;

						case 6:

							$thisstatus = "Complimentary";

							$substatus = 1;

							break;
					}

					if ($thisstatus) {

						$otherinfo .= "<br>Subscription status:<b> $thisstatus</b>";
					}

					$livestatus = $thisstatus;



					//how long till renewal

					$secs_now = time();

					$secsinayear = 31536000;

					$my_secs = strtotime($DatePaid);

					$error = ($secs_now - $my_secs);

					$daystorenew = number_format(($secsinayear - $error) / 86400);

					$datelastpaid = $DatePaid;





					$intro = "<div><b>Membership administration</b></div>\n";



					if ($substatus == 1) {

						//paid up or FOC so OK to access member features	

						$introtext = "You are logged in as <b>" . $contact . "</b> $otherinfo\n";

						//check if renewal due soon

						if (($MemStatus == 2 || $MemStatus == 3 || $MemStatus == 4) && ($PaymentMethod == "cc" || $PaymentMethod == "ch")) {

							//Paying member by cc or cheque



							if ($daystorenew <= 60) {

								//offer renewal as less than 60 days to go

								if ($daystorenew <= 0) {

									$daystorenew = $daystorenew * -1;

									$renewtext = "your subscription was due " . $daystorenew . " day(s) ago.";
								} elseif ($daystorenew == 0) {

									$renewtext = "your subscription is due today.";
								} else {

									$renewtext = "your subscription is due in " . $daystorenew . " day(s).";
								}

								if ($PaymentMethod == "cc") {

									$introtext = "You are logged in as <b>" . $contact . "</b> $otherinfo<br><br>According to our records, <font color='#ff0000'><b>" . $renewtext . "</font></b> Select an option from the list below to update your details and renew your subscription. Thank you.\n";

									$introtext .= "<div class=table_login_menu>\n";

									$introtext .= "<a href=\"#\" onClick=\"document.form.subaction.value='paysub'; document.form.submit();\"><img src=\"Image/common/icon_psysubscription.gif\" width=59 height=18 border=0 alt=\"Pay subscription\"> Pay subscription</a><br>\n";

									$introtext .= "<a href='javascript:Changelog(" . $userid . ")'><img src=\"Image/common/icon_history.gif\" width=59 height=18 border=0 alt=\"View Change History\"> View Change History</a><br>\n";

									$introtext .= "<a href=\"members/bargeregister/bargeregister-edit\"><img src=\"Image/common/icon_posting.gif\" 	width=59 height=18 border=0 alt=\"Add or edit barge register\"> Add or edit barge register</a><br>\n";

									if ($Situation == 3) {

										//commercial interest

										//$introtext.="<a href=\"main.php?section=".$section."&MyAccountaction=valid_member_check\"><img src=\"Image/common/icon_validate.gif\" 	width=59 height=18 border=0 alt=\"Validate member for discount\"> Validate member for DBA discount</a><br>\n";



									} else {

										$introtext .= "<a href=\"members/bargeregister/bargeregister-edit\"><img src=\"Image/common/icon_posting.gif\" 	width=59 height=18 border=0 alt=\"Add or edit classified adverts\"> Add or edit classified adverts</a><br>\n";
									}

									$introtext .= "</div>\n";
								}

								if ($PaymentMethod == "ch") {

									//cheque payer

									$introtext = "You are logged in as <b>" . $contact . "</b> $otherinfo<br><br>According to our records, <font color='#ff0000'><b>" . $renewtext . "</font></b> Select an option from the list below to update your details and renew your subscription which includes \n";

									$introtext .= "paying by an alternative method. If your details are correct, please forward your cheque for <b>GBP " . $BasicSub . "</b> having written your membership reference <b>" . $MembershipNo . "</b> on the back, to<br><b>\n";

									$introtext .= $membershipmailaddress . "</b>. Thank you.<div class=table_login_menu>\n";

									$introtext .= "<a href=\"#\" onClick=\"document.form.subaction.value='paysub'; document.form.submit();\"><img src=\"Image/common/icon_psysubscription.gif\" width=59 height=18 border=0 alt=\"Pay subscription\"> Pay subscription</a>\n";

									$introtext .= "<a href='javascript:Changelog(" . $userid . ")'><img src=\"Image/common/icon_history.gif\" width=59 height=18 border=0 alt=\"View Change History\"> View Change History</a><br>\n";

									$introtext .= "<a href=\"members/bargeregister/bargeregister-edit\"><img src=\"Image/common/icon_posting.gif\" 	width=59 height=18 border=0 alt=\"Add or edit barge register\"> Add or edit barge register</a><br>\n";



									if ($Situation == 3) {

										//commercial interest

										//$introtext.="<a href=\"main.php?section=".$section."&MyAccountaction=valid_member_check\"><img src=\"Image/common/icon_validate.gif\" 	width=59 height=18 border=0 alt=\"Validate member for discount\"> Validate member for DBA discount</a><br>\n";



									} else {

										$introtext .= "<a href=\"index.php?option=com_waterways_guide&view=classified&Itemid=771\"><img src=\"Image/common/icon_posting.gif\" 	width=59 height=18 border=0 alt=\"Add or edit classified adverts\"> Add or edit classified adverts</a><br>\n";
									}

									$introtext .= "</div>\n";
								}
							} else {

								$introtext .= "<div class=table_login_menu>\n";

								$introtext .= "<a href='javascript:Changelog(" . $userid . ")'><img src=\"Image/common/icon_history.gif\" width=59 height=18 border=0 alt=\"View Change History\"> View Change History</a><br>\n";

								$introtext .= "<a href=\"members/bargeregister/bargeregister-edit\"><img src=\"Image/common/icon_posting.gif\" 	width=59 height=18 border=0 alt=\"Add or edit barge register\"> Add or edit barge register</a><br>\n";

								if ($Situation == 3) {

									//commercial interest

									//$introtext.="<a href=\"main.php?section=".$section."&MyAccountaction=valid_member_check\"><img src=\"Image/common/icon_validate.gif\" 	width=59 height=18 border=0 alt=\"Validate member for discount\"> Validate member for DBA discount</a><br>\n";

								} else {

									$introtext .= "<a href=\"index.php?option=com_waterways_guide&view=classified&Itemid=771\"><img src=\"Image/common/icon_posting.gif\" 	width=59 height=18 border=0 alt=\"Add or edit classified adverts\"> Add or edit classified adverts</a><br>\n";
								}

								$introtext .= "</div>\n";
							}
						} else {

							//dd payer

							$introtext .= "<div class=table_login_menu>\n";

							$introtext .= "<a href='javascript:Changelog(" . $userid . ")'><img src=\"Image/common/icon_history.gif\" width=59 height=18 border=0 alt=\"View Change History\"> View Change History</a><br>\n";

							$introtext .= "<a href=\"members/bargeregister/bargeregister-edit\"><img src=\"Image/common/icon_posting.gif\" 	width=59 height=18 border=0 alt=\"Add or edit barge register\"> Add or edit barge register</a><br>\n";

							if ($Situation == 3) {

								//commercial interest

								//$introtext.="<a href=\"main.php?section=".$section."&MyAccountaction=valid_member_check\"><img src=\"Image/common/icon_validate.gif\" 	width=59 height=18 border=0 alt=\"Validate member for discount\"> Validate member for DBA discount</a><br>\n";

							} else {

								$introtext .= "<a href=\"index.php?option=com_waterways_guide&view=classified&Itemid=771\"><img src=\"Image/common/icon_posting.gif\" 	width=59 height=18 border=0 alt=\"Add or edit classified adverts\"> Add or edit classified adverts</a><br>\n";
							}

							//$introtext.="<a href=\"#\" onClick=\"document.form.subaction.value='paysub'; document.form.submit();\"><img src=\"Image/common/icon_psysubscription.gif\" width=59 height=18 border=0 alt=\"Pay subscription\"> Pay subscription</a>\n";

							$introtext .= "</div>\n";
						}
					} else {

						//sub NOT paid so offer renewal

						if ($PaymentMethod == "cc") {

							$introtext = "You are logged in as <b>" . $contact . "</b> $otherinfo<br><br>According to our records, <font color='#ff0000'><b>your subscription has not yet been paid or has lapsed.</font></b> Select an option from the list below to update your details or pay your subscription. Thank you.\n";

							$introtext .= "<div class=table_login_menu>\n";

							$introtext .= "<a href=\"#\" onClick=\"document.form.subaction.value='paysub'; document.form.submit();\"><img src=\"Image/common/icon_psysubscription.gif\" width=59 height=18 border=0 alt=\"Pay subscription\"> Pay subscription</a><br>\n";

							$introtext .= "<a href='javascript:Changelog(" . $userid . ")'><img src=\"Image/common/icon_history.gif\" width=59 height=18 border=0 alt=\"View Change History\"> View Change History</a><br>\n";

							$introtext .= "</div>\n";
						}

						if ($PaymentMethod == "ch") {

							//cheque payer

							$introtext = "You are logged in as <b>" . $contact . "</b> $otherinfo<br><br>According to our records, <font color='#ff0000'><b>your subscription, normally paid by cheque has not yet been paid or has lapsed.</font></b> Select an option from the list below to update your details which includes\n";

							$introtext .= "paying by an alternative method. If your details are correct, please forward your cheque for <b>GBP " . $BasicSub . "</b> having written your membership reference <b>" . $MembershipNo . "</b> on the back, to <br><b>\n";

							$introtext .= $membershipmailaddress . "</b>. Thank you.<div class=table_login_menu>\n";



							$introtext .= "<a href='javascript:Changelog(" . $userid . ")'><img src=\"Image/common/icon_history.gif\" width=59 height=18 border=0 alt=\"View Change History\"> View Change History</a><br>\n";

							$introtext .= "</div>\n";
						}

						if ($PaymentMethod == "so") {

							//standing order payer

							$introtext = "You are logged in as <b>" . $contact . "</b> $otherinfo<br><br>According to our records, <font color='#ff0000'><b>your subscription, normally paid by bankers order has not yet been received or has lapsed.</font></b> Select an option from the list below to update your details which includes\n";

							$introtext .= "paying by an alternative method. If your details are correct, <a href=\"javascript:doreport('members/so_form_outer.php?memberid=" . $memberid . "');\">please click here. to print out your standing order form</a>, fill in the details and post to the address shown at the bottom of the form. Thank you.\n";

							$introtext .= "<div class=table_login_menu>\n";

							$introtext .= "<a href='javascript:Changelog(" . $userid . ")'><img src=\"Image/common/icon_history.gif\" width=59 height=18 border=0 alt=\"View Change History\"> View Change History</a><br>\n";

							$introtext .= "</div>\n";
						}

						if ($PaymentMethod == "dd") {

							//direct debit payer

							$introtext = "You are logged in as <b>" . $contact . "</b> $otherinfo<br><br>According to our records, <font color='#ff0000'><b>your subscription, normally paid by direct debit has not yet been received or has lapsed.</font></b> Select an option from the list below to update your details which includes\n";

							$introtext .= "paying by an alternative method. If your details are correct, <a href=\"" . $ddformpath . "\">please click here. to print out your direct debit form</a>, fill in the details and post to the address shown on the form. Thank you.\n";

							$introtext .= "<div class=table_login_menu>\n";

							$introtext .= "<a href='javascript:Changelog(" . $userid . ")'><img src=\"Image/common/icon_history.gif\" width=59 height=18 border=0 alt=\"View Change History\"> View Change History</a><br>\n";

							$introtext .= "</div>\n";
						}
					}
				}

				//$introtext.=" substatus=$substatus memstatus=$MemStatus daystorenew=$daystorenew error=$error datelastpaid=$DatePaid";

			} else {

				$go2value = "Join";
			}







			echo ("<input type=\"hidden\"' name=\"userid\" value=\"" . $userid . "\">\n");

			echo ("<input type=\"hidden\"' name=\"ID2\" value=\"" . $ID2 . "\">\n");

			echo ("<input type=\"hidden\" name=\"subscription_desc\" value=\"" . $subscription_desc . "\">\n");

			echo ("<input type=\"hidden\" name=\"subaction\" value=\"" . $subaction . "\">\n");

			//for new mwmber add default services 1 barge register allow name, 35 allow email contact admin

			if (!$MembershipNo) {

				$Services = $Services_newmember; //held in commonV3.php

			}



			echo ("<input type=\"hidden\"' name=\"Services\" value=\"" . $Services . "\">\n");

			echo ("<input type=\"hidden\"' name=\"MemNo\" value=\"" . $MemNo . "\">\n");

			echo ("<input type=\"hidden\"' name=\"MembershipNo\" value=\"" . $MembershipNo . "\">\n");

			echo ("<input type=\"hidden\"' name=\"daystorenew\" value=\"" . (isset($daystorenew) ? $daystorenew : '') . "\">\n");



			if ($ref) {

				echo ("<input type=\"hidden\"' name=\"Track\" value=\"" . $ref . "\">\n");
			}

		?>



			<SCRIPT LANGUAGE="JavaScript">
				<!--
				function changesituationform() {

					var user_situation = document.form.Situation.value;

					if (user_situation < 4) {

						//no ship so hide input	

						document.getElementById("bargeowner1").style.display = 'none';

						document.getElementById("bargeowner2").style.display = 'none';

						document.getElementById("bargeowner3").style.display = 'none';

					} else {

						document.getElementById("bargeowner1").style.display = '';

						document.getElementById("bargeowner2").style.display = '';

						document.getElementById("bargeowner3").style.display = '';

					}

				}



				function changememtypeform(box) {

					var MemTypeCode = document.form.MemTypeCode.value;

					var MemCountrySelect = document.form.Location.value;

					var subamount = <?php if (!$BasicSub) {
										echo (0);
									} else {
										echo ($BasicSub);
									} ?>;

					var desc = "";

					var Overseas = "False";

					var MemType = "";

					var zone_array = MemCountrySelect.split("_");

					var iso = zone_array[0];

					var zone = zone_array[1];

					var country = zone_array[2];

					var admin = <?php if ($membershipadmin == true) {
									echo ("1");
								} else {
									echo ("0");
								} ?>;

					var select = document.getElementById("MemTypeCode");

					var ExistingMemTypeCode = <?php if ($MemTypeCode) {
													echo ($MemTypeCode);
												} else {
													echo ("0");
												} ?>;

					if (ExistingMemTypeCode == 0 && MemTypeCode == 0) {

						//new member update BF service default

						document.form.service10.checked = true;

					}

					if (!MemTypeCode || box == "country") {

						MemTypeCode = 0;

						var memoption = ["0"];

						var memdescription = ["Select type"];



						if (ExistingMemTypeCode > 4) {

							//leave as is and 

							if (admin == 1) {

								var memoption = ["0", "1", "2", "5", "6", "7"];

								var memdescription = ["Select type", "Single within Europe", "Family within Europe", "Honorary", "Press", "Voucher"];

							} else {

								if (ExistingMemTypeCode == 5) {

									var memoption = ["5"];

									var memdescription = ["Honorary"];

								} else if (ExistingMemTypeCode == 6) {

									var memoption = ["6"];

									var memdescription = ["Press"];

								} else if (ExistingMemTypeCode == 7) {

									var memoption = ["7"];

									var memdescription = ["Voucher"];

								}

							}

						} else {

							if ((zone == "UK" || zone == "EU") && (admin == 0)) {

								var memoption = ["0", "1", "2"];

								var memdescription = ["Select type", "Single within Europe", "Family within Europe"];

							} else if ((zone == "UK" || zone == "EU") && (admin == 1)) {

								var memoption = ["0", "1", "2", "5", "6", "7"];

								var memdescription = ["Select type", "Single within Europe", "Family within Europe", "Honorary", "Press", "Voucher"];

							} else if ((zone == "Z1" || zone == "Z2") && (admin == 0)) {

								var memoption = ["0", "3", "4"];

								var memdescription = ["Select type", "Single outside Europe", "Family outside Europe"];

							} else if ((zone == "Z1" || zone == "Z2") && (admin == 1)) {

								var memoption = ["0", "3", "4", "5", "6", "7"];

								var memdescription = ["Select type", "Single outside Europe", "Family outside Europe", "Honorary", "Press", "Voucher"];

							}

						}

						//empty and then refill the Memtype dropdown with appropriate types for country zone

						while (select.options.length) select.options[0] = null;



						for (var i = 0; i < memoption.length; i++) {

							var opt = memoption[i];

							var des = memdescription[i];

							var el = document.createElement("option");

							el.textContent = des;

							el.value = opt;

							select.appendChild(el);

							if (select.options[i].value == ExistingMemTypeCode) {

								select.options[i].selected = true;

								MemTypeCode = opt;

							}

						}

					}



					if (MemTypeCode < 5) {



						if (MemTypeCode == 2 || MemTypeCode == 4) {

							//family membership so open additional name	

							document.getElementById("section2").style.display = '';

							document.getElementById("title2").style.display = '';

							document.getElementById("forename2").style.display = '';

							document.getElementById("familyname2").style.display = '';

							document.getElementById("email2").style.display = '';

							document.getElementById("login2").style.display = '';

							document.getElementById("mobile2").style.display = '';



						} else {

							document.getElementById("section2").style.display = 'none';

							document.getElementById("title2").style.display = 'none';

							document.getElementById("forename2").style.display = 'none';

							document.getElementById("familyname2").style.display = 'none';

							document.getElementById("email2").style.display = 'none';

							document.getElementById("login2").style.display = 'none';

							document.getElementById("mobile2").style.display = 'none';

						}

					}

					switch (MemTypeCode) {

						//$BasicSub=0;

						<?php for ($i = 1; $i <= 7; $i++) {

						?>
							case '<?= $i; ?>':

								subamount = "<?= number_format(${'subamount' . $i}, 2, '.', ','); ?>";

								subamount_d = "<?= number_format(${'subamount' . $i . 'd'}, 2, '.', ','); ?>";

								desc = "<?= ${'subamount' . $i . 'desc'}; ?>";

								MemType = "<?= ${'subamount' . $i . 'memtype'}; ?>";

								break;

							<?php }

							?>
						default:

							subamount = "";

							subamount_d = "";



					}

					if (document.form.service10.checked == true) {

						//bfbp

						document.form.BasicSub.value = subamount;

					} else {

						//no bfbp

						document.form.BasicSub.value = subamount_d;

					}

					document.form.subscription_desc.value = desc;

					document.form.MemType.value = MemType;

				}





				function Help(HelpID) {

					var mypage = "help.php?FormEntryID\=" + HelpID;

					var myname = "help";

					//var w = (screen.width - 100);

					//var h = (screen.height - 100);

					var w = 530;

					var h = 300;

					var scroll = "yes";



					var winl = (screen.width - w) / 2;

					var wint = (screen.height - h) / 2;

					winprops = 'height=' + h + ',width=' + w + ',top=' + wint + ',left=' + winl + ',scrollbars=' + scroll + ',resizable'

					mypage += (mypage.indexOf('?') != -1 ? '&' : '?') + 'nocache=' + Date.now();

					win = window.open(mypage, myname, winprops)

					if (parseInt(navigator.appVersion) >= 4) {
						win.window.focus();
					}

				}



				function generatePassword() {

					let passChars = '1234567890abcdefghjkmnpqrtuvwxyABCDEFGHJKMNPQRTUVWXY';

					let passString = '';

					for (i = 0; i < 8; i++) passString += passChars[Math.floor(Math.random() * passChars.length)];

					document.form.PW.type = 'text';

					document.form.PW.value = passString;

				}



				function MM_validateForm() { //v3.0



					var errors = "";

					var user_type = document.form.MemType.value;

					var user_type_select = document.form.MemTypeCode.value;

					var user_add1 = document.form.Address1.value;

					var user_postcode = document.form.PostCode.value;

					var user_title = document.form.Title.value;

					var user_forename = document.form.FirstName.value;

					var user_familyname = document.form.LastName.value;

					var user_area = document.form.Location.value;

					var user_situation = document.form.Situation.value;

					var login = document.form.Login.value;

					var email = document.form.Email.value;

					var pw = document.form.PW.value;

					var user_LocationCruising = document.form.LocationCruising.value;





					if (user_area == "0_0_0") {

						errors += '- location\n';

						document.form.Location.style.backgroundColor = "#ffff00";

					} else {

						document.form.Location.style.backgroundColor = "#ffffff";

					}

					if (user_type == "0") {

						errors += '- member application type\n';

						document.form.MemType.style.backgroundColor = "#ffff00";

					} else {

						document.form.MemType.style.backgroundColor = "#ffffff";

					}



					if (user_type_select == 0) {

						errors += '- membership type\n';

						document.form.MemTypeCode.style.backgroundColor = "#ffff00";

					} else {

						document.form.MemTypeCode.style.backgroundColor = "#ffffff";

					}



					if (login == "") {

						errors += '- login user name\n';

						document.form.Login.style.backgroundColor = "#ffff00";

					} else {

						cd = login;

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

							(cd.indexOf("\}") != -1)) {

							errors += '- your login user name must only contain standard characters or numbers\n';

							document.form.Login.style.backgroundColor = "#ffff00";

						} else {

							document.form.Login.style.backgroundColor = "#ffffff";

						}

					}



					if (email == "") {

						errors += '- email address\n';

						document.form.Email.style.backgroundColor = "#ffff00";

					} else {

						if (!/^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/.test(email)) {

							errors += '- email does not seem to be a valid e-mail address.\n';

							document.form.Email.style.backgroundColor = "#ffff00";

						} else {

							document.form.Email.style.backgroundColor = "#ffffff";

						}

						// p=email.indexOf('@');

						// if (p<1 || p==(email.length-1)) {

						// errors+='- email does not seem to be a valid e-mail address.\n';

						// document.form.Email.style.backgroundColor ="#ffff00";

						// }else{

						// cd=email;

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

						// document.form.Email.style.backgroundColor ="#ffff00";

						// }else{

						// document.form.Email.style.backgroundColor ="#ffffff";

						// }

						// }

					}

					if (pw.length < 8 || pw == "") {

						errors += '- password\n';

						document.form.PW.style.backgroundColor = "#ffff00";

					} else {

						cd = pw;

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

							(cd.indexOf("\}") != -1)) {

							errors += '- the password must only contain standard characters or numbers and no spaces\n';

							document.form.PW.style.backgroundColor = "#ffff00";

						} else {

							document.form.PW.style.backgroundColor = "#ffffff";

						}

					}



					if (user_title == "") {

						errors += '- title Mr, Mrs, Ms etc\n';

						document.form.Title.style.backgroundColor = "#ffff00";

					} else {

						document.form.Title.style.backgroundColor = "#ffffff";

					}

					if (user_forename == "") {

						errors += '- forename\n';

						document.form.FirstName.style.backgroundColor = "#ffff00";

					} else {

						document.form.FirstName.style.backgroundColor = "#ffffff";

					}

					if (user_familyname == "") {

						errors += '- family name\n';

						document.form.LastName.style.backgroundColor = "#ffff00";

					} else {

						document.form.LastName.style.backgroundColor = "#ffffff";

					}



					if (user_type_select == 2 || user_type_select == 4) {

						//family membership so check second member details

						var user_title2 = document.form.Title2.value;

						var user_forename2 = document.form.FirstName2.value;

						var user_familyname2 = document.form.LastName2.value;

						var user_email2 = document.form.Email2.value;

						if (user_title2 == "") {

							errors += '- second member title Mr, Mrs, Ms etc\n';

							document.form.Title2.style.backgroundColor = "#ffff00";

						} else {

							document.form.Title2.style.backgroundColor = "#ffffff";

						}

						if (user_forename2 == "") {

							errors += '- second member forename\n';

							document.form.FirstName2.style.backgroundColor = "#ffff00";

						} else {

							document.form.FirstName2.style.backgroundColor = "#ffffff";

						}

						if (user_familyname2 == "") {

							errors += '- second member family name\n';

							document.form.LastName2.style.backgroundColor = "#ffff00";

						} else {

							document.form.LastName2.style.backgroundColor = "#ffffff";

						}

						if (user_email2 == "") {

							errors += '- second member email address\n';

							document.form.Email2.style.backgroundColor = "#ffff00";

						} else {

							if (!/^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/.test(user_email2)) {

								errors += '- email does not seem to be a valid e-mail address.\n';

								document.form.Email2.style.backgroundColor = "#ffff00";

							} else {

								document.form.Email2.style.backgroundColor = "#ffffff";

							}

							// p=user_email2.indexOf('@');

							// if (p<1 || p==(user_email2.length-1)) {

							// errors+='- second member email does not seem to be a valid e-mail address.\n';

							// document.form.Email2.style.backgroundColor ="#ffff00";

							// }else{

							// document.form.Email2.style.backgroundColor ="#ffffff";

							// }

						}

					}

					if (user_add1 == "") {

						errors += '- address\n';

						document.form.Address1.style.backgroundColor = "#ffff00";

					} else {

						document.form.Address1.style.backgroundColor = "#ffffff";

					}

					if (user_postcode == "" && user_area == "GB_UK_United Kingdom") {

						errors += '- postcode\n';

						document.form.PostCode.style.backgroundColor = "#ffff00";

					} else {

						document.form.PostCode.style.backgroundColor = "#ffffff";

					}

					if (user_situation == "0") {

						errors += '- situation or barge\n';

						document.form.Situation.style.backgroundColor = "#ffff00";

					} else {

						document.form.Situation.style.backgroundColor = "#ffffff";

					}

					if ((user_situation == "4" || user_situation == "5") && user_LocationCruising == "0_0_0") {

						errors += '- cruising country\n';

						document.form.LocationCruising.style.backgroundColor = "#ffff00";

					} else {

						document.form.LocationCruising.style.backgroundColor = "#ffffff";

					}



					if (errors) {

						alert('Please check the highlighted entries and try again:\n' + errors);

					} else {

						document.form.subaction.value = 'subscribe2';

						document.form.submit()

						//alert("submit");

					}

				}





				function Changelog(memberid) {

					var opendoc = "<?php echo ($componentpath); ?>view_change_log.php?memberid=" + memberid;

					//alert("mypage "+mypage);

					var mypage = opendoc;

					var myname = "Member_Changelog";

					var w = 800;

					var h = 600;

					var scroll = "yes";

					var winl = (screen.width - w) / 2;

					var wint = (screen.height - h) / 2;

					winprops = 'height=' + h + ',width=' + w + ',top=' + wint + ',left=' + winl + ',scrollbars=' + scroll + ',resizable'

					mypage += (mypage.indexOf('?') != -1 ? '&' : '?') + 'nocache=' + Date.now();

					win = window.open(mypage, myname, winprops)

					if (parseInt(navigator.appVersion) >= 4) {
						win.window.focus();
					}

				}



				//
				-->

			</script>



			<style type="text/css" media="screen,projection">
				td.profile_label {

					width: 300px;

					background-color: #CCEEF7;

					border: 1px;

					padding-top: 2px;

					padding-bottom: 2px;

					padding-right: 2px;

					padding-left: 4px;

					vertical-align: top
				}

				td.profile_gdpr {

					width: 300px;

					background-color: #FFFF00;

					border: 1px;

					padding-top: 2px;

					padding-bottom: 2px;

					padding-right: 2px;

					padding-left: 4px;

					vertical-align: top
				}

				td.profile_field {

					background-color: #CCEEF7;

					padding-top: 2px;

					padding-bottom: 2px;

					padding-right: 2px;

					padding-left: 2px;

				}
			</style>

			<h2>My Details<?php

							if (isset($table) && $table == "archive") {

								echo (" from the ARCHIVE");
							}

							?></h2>

			<?php

			echo ($introtext);

			echo ("<br>" . nl2br($screenmessage));

			?>

			<table border="0" cellpadding="3" cellspacing="2" width="100%">







				<?php

				if ($MembershipNo) {

					echo ("<tr><td colspan=2>Membership no: $MembershipNo");

					if ($MemNo) {

						echo (" <i>Old membership no: $MemNo</i>");
					}

					echo ("</td></tr>\n");
				}

				//echo("<tr><td>Services debug</td>td><td><input type='text' name='servicedebug' value='".$Services."'></td></tr>");

				?>



				<tr>



					<td colspan="2"><b>Your details</b> - the main subscriber and person responsible

						for upkeeping this profile. </td>
				</tr>



				<tr>



					<td class=profile_label>Postal Country *</td>

					<td class=profile_field>

						<select class=formcontrol name="Location" id="Location" onChange="changememtypeform('country')">

							<?

							$preselect_iso = $CountryCode;

							include("country_list.php");

							?>

						</select>
					</td>

				</tr>

				<tr>

					<td width="30%" class=profile_label>Membership type *<br>

						and annual subscription</td>

					<td width="70%" class=profile_field>

						<?php

						$MemTypeCode0 = "";

						$MemTypeCode1 = "";

						$MemTypeCode2 = "";

						$MemTypeCode3 = "";

						$MemTypeCode4 = "";

						$MemTypeCode5 = "";

						$MemTypeCode6 = "";

						$MemTypeCode7 = "";

						${'MemTypeCode' . $MemTypeCode} = " selected";



						?>





						<input type="hidden" name="MemType" value="<?php echo ($MemType); ?>">

						<select class=formcontrol name="MemTypeCode" id="MemTypeCode" onChange="changememtypeform('memtype')">



						</select>



						&pound;

						<input class=formcontrol name='BasicSub' type='text' id="BasicSub" readonly value="<?php echo ($BasicSub); ?>" size='7'>
						<em>Any discounts allocated to you will be applied at checkout</em>
					</td>
				</tr>

				<?php

				//blue flag by post / email

				$servicetitle = "Delivery of Blue Flag magazine:";

				$query = $db->getQuery(true)

					->select('*')

					->from($db->qn('tblServices'))

					->where($db->qn('ServiceCategory') . ' = ' . $db->q('blueflagdelivery'))

					->order($db->qn('ServiceSortOrder'));

				$sections = $db->setQuery($query)->loadObjectList();

				$boxes = "";

				$num_services = count($sections);

				foreach ($sections as $sectionrow) {

					$serviceid = $sectionrow->ServiceID;

					$servicedesc = $sectionrow->ServiceDescGB;



					$found = strstr($Services, "|" . $serviceid . "|");

					if (!$found) {

						$boxes .= "<input type=\"checkbox\" name=\"service" . $serviceid . "\" value=\"" . $serviceid . "\" onClick=\"servicetype(this," . $serviceid . "," . $num_services . ")\"> " . $servicedesc . "<br />\n";
					} else {

						$boxes .= "<input type=\"checkbox\" name=\"service" . $serviceid . "\" value=\"" . $serviceid . "\" checked onClick=\"servicetype(this," . $serviceid . "," . $num_services . ")\"> " . $servicedesc . "<br />\n";
					}
				}



				?>

				<tr>
					<td class=profile_label><?php echo ($servicetitle); ?></td>
					<td class=profile_field valign="top"><?php echo ($boxes); ?><div id="discount_message"></div>
					</td>
				</tr>



				<tr>

					<td colspan="2">
						<font color=ff0000>Please use UPPER and lower case as these details will be used to address correspondence.</font>
					</td>

				</tr>

				<tr>

					<td class=profile_label>Title <em>*</em> <em>(Mr, Mrs, Ms etc) </em></td>

					<td class=profile_field> <input class=formcontrol type='text' name='Title' size='30' value="<?php echo ($Title); ?>"></td>

				</tr>

				<tr>

					<td class=profile_label>Forename *</td>

					<td class=profile_field> <input class=formcontrol type='text' name='FirstName' size='30' value="<?php echo ($FirstName); ?>"></td>

				</tr>

				<tr>

					<td class=profile_label>Family name <em>*</em></td>

					<td class=profile_field> <input class=formcontrol type='text' name='LastName' size='30' value="<?php echo ($LastName); ?>"></td>

				</tr>





				<tr>



					<td class=profile_label>Email address <em>* </em></td>

					<td class=profile_field> <input class=formcontrol type='text' name='Email' size='30' value="<?php echo ($Email); ?>"> </td>

				</tr>

				<td class=profile_label>Tel number: </td>

				<td class=profile_field> <input class=formcontrol type='text' name='Telephone1' size='30' value="<?php echo ($Telephone1); ?>"> </td>

				</tr>

				<tr>

					<td class=profile_label>Tel mobile:</td>

					<td class=profile_field> <input class=formcontrol type='text' name='Telephone2' size='30' value="<?php echo ($Telephone2); ?>"> </td>

				</tr>





				<tr>

					<td class=profile_label>Login user name <em>* </em></td>

					<td class=profile_field> <input class=formcontrol type='text' name='Login' size='30' value="<?php echo ($Login); ?>">

						<em>excluding &lt;&gt;\&quot;'%;()&amp;</em>
					</td>

				</tr>



				<tr>



					<td class=profile_label>Password <em>* (single word

							alphanumeric) </em></td>

					<td class=profile_field> <input class=formcontrol type='password' name='PW' size='30' value="<?php echo ($PW); ?>">

						<em>at least 8 characters</em> <span class="btn btn-info" onclick="generatePassword()">Generate Password</span>
					</td>

				</tr>





				<tr id="section2">
					<td colspan=2>Second family member</td>
				</tr>

				<tr id="title2">

					<td class=profile_label>

						Title <em>* (Mr, Mrs, Ms etc) </em></td>

					<td class=profile_field> <input class=formcontrol type='text' name='Title2' size='30' value="<?php echo ($Title2); ?>">



					</td>

				</tr>

				<tr id="forename2">

					<td class=profile_label>Forename *</td>

					<td class=profile_field> <input class=formcontrol type='text' name='FirstName2' size='30' value="<?php echo ($FirstName2); ?>"></td>

				</tr>

				<tr id="familyname2">

					<td class=profile_label>Family name <em>*</em></td>

					<td class=profile_field> <input class=formcontrol type='text' name='LastName2' size='30' value="<?php echo ($LastName2); ?>"></td>

				</tr>
				<tr id="email2">

					<td class=profile_label>Email address <em>*</em></td>

					<td class=profile_field><input class=formcontrol type='text' name='Email2' size='30' value="<?php echo ($Email2); ?>" />



						<?php

						//if(!empty($ID2)){

						echo ("<em>must be different from main member</em>");

						//}

						?>



					</td>

				</tr>

				<tr id="mobile2">

					<td class=profile_label>Tel mobile:</td>

					<td class=profile_field> <input class=formcontrol type='text' name='Mobile2' size='30' value="<?php echo ($Mobile2); ?>"> </td>

				</tr>

				<tr id="login2">

					<td class=profile_label>Login user name</td>

					<td class=profile_field>

						<?php

						if (!empty($ID2)) {

							echo ($Login2);
						} else {

							echo ("This will be created when the form is submitted and can be found when you return to 'My Details'");
						}

						?>

					</td>

				</tr>



				<tr>

					<td colspan=2><strong>Postal address</strong> - <font color=ff0000>don't put the country as part of your address, it will be added from your 'Postal Country' above.</font>
					</td>

				<tr>



					<td class=profile_label>Line 1 *</td>

					<td class=profile_field><input class=formcontrol type='text' name='Address1' size='40' value="<?php echo ($Address1); ?>"></td>

				</tr>



				<tr>



					<td class=profile_label>Line 2</td>

					<td class=profile_field> <input class=formcontrol type='text' name='Address2' size='40' value="<?php echo ($Address2); ?>"></td>

				</tr>



				<tr>



					<td class=profile_label>Line 3</td>

					<td class=profile_field> <input class=formcontrol type='text' name='Address3' size='40' value="<?php echo ($Address3); ?>"></td>

				</tr>

				<tr>

					<td class=profile_label>Line 4</td>

					<td class=profile_field> <input class=formcontrol type='text' name='Address4' size='40' value="<?php echo ($Address4); ?>"></td>

				</tr>

				<td class=profile_label>Postcode *</td>

				<td class=profile_field> <input class=formcontrol type='text' name='PostCode' size='30' value="<?php echo ($PostCode); ?>"></td>

				</tr>
				<tr>

					<td colspan="2"><strong>Options</strong> Tick boxes for the following or untick to cancel.</td>

				</tr>







				<?php







				//GDPR settings main member (DBA)

				$servicetitle = "To give you more control and to meet General Data Protection Regulations, choose emails you would like from us:";

				$query = $db->getQuery(true)

					->select('*')

					->from($db->qn('tblServices'))

					->where($db->qn('ServiceCategory') . ' = ' . $db->q('GDPR'))

					->order($db->qn('ServiceSortOrder'));

				$sections = $db->setQuery($query)->loadObjectList();

				$boxes = "";

				$num_services += count($sections);

				foreach ($sections as $sectionrow) {

					$serviceid = $sectionrow->ServiceID;

					$servicedesc = $sectionrow->ServiceDescGB . " " . $sectionrow->ServiceHelpGB;



					$found = strstr($Services, "|" . $serviceid . "|");

					if (!$found) {

						$boxes .= "<input type=\"checkbox\" name=\"service" . $serviceid . "\" value=\"" . $serviceid . "\" onClick=\"servicetype(this," . $serviceid . "," . $num_services . ")\"> " . $servicedesc . "<br />\n";
					} else {

						$boxes .= "<input type=\"checkbox\" name=\"service" . $serviceid . "\" value=\"" . $serviceid . "\" checked onClick=\"servicetype(this," . $serviceid . "," . $num_services . ")\"> " . $servicedesc . "<br />\n";
					}
				}

				echo ("<tr><td colspan=2>" . $servicetitle . "</td></tr><tr><td class=profile_gdpr valign=\"top\"></td><td class=profile_gdpr valign=\"top\">" . $boxes . "</td></tr>\n");

				//GDPR settings main member (other members)

				$servicetitle = "Would you like to allow contact from other members through the Member Finder facility:";

				$query = $db->getQuery(true)

					->select('*')

					->from($db->qn('tblServices'))

					->where($db->qn('ServiceCategory') . ' = ' . $db->q('GDPR_mem'))

					->order($db->qn('ServiceSortOrder'));

				$sections = $db->setQuery($query)->loadObjectList();

				$boxes = "";

				$num_services += count($sections);

				foreach ($sections as $sectionrow) {

					$serviceid = $sectionrow->ServiceID;

					$servicedesc = $sectionrow->ServiceDescGB . " " . $sectionrow->ServiceHelpGB;



					$found = strstr($Services, "|" . $serviceid . "|");

					//Added the following in order to check by 'Email' by default

					$checked = ($serviceid == 51) ? 'checked="checked" ' : '';

					if (!$found) {

						$boxes .= "<input type=\"checkbox\" name=\"service" . $serviceid . "\" value=\"" . $serviceid . "\" " . $checked . " onClick=\"servicetype(this," . $serviceid . "," . $num_services . ")\"> " . $servicedesc . "<br />\n";
					} else {

						$boxes .= "<input type=\"checkbox\" name=\"service" . $serviceid . "\" value=\"" . $serviceid . "\" " . $checked . " onClick=\"servicetype(this," . $serviceid . "," . $num_services . ")\"> " . $servicedesc . "<br />\n";
					}
				}

				echo ("<tr><td colspan=2>" . $servicetitle . "</td></tr><tr><td class=profile_gdpr valign=\"top\"></td><td class=profile_gdpr valign=\"top\">" . $boxes . "</td></tr>\n");





				if ($membershipadmin == true) {

					//GDPR settings second member

					$servicetitle = "Admin only view of GDPR, options for the second member emails from us:";

					$query = $db->getQuery(true)

						->select('*')

						->from($db->qn('tblServices'))

						->where($db->qn('ServiceCategory') . ' = ' . $db->q('GDPR2'))

						->order($db->qn('ServiceSortOrder'));

					$sections = $db->setQuery($query)->loadObjectList();

					$boxes = "";

					$num_services += count($sections);

					foreach ($sections as $sectionrow) {

						$serviceid = $sectionrow->ServiceID;

						$servicedesc = $sectionrow->ServiceDescGB;



						$found = strstr($Services, "|" . $serviceid . "|");

						if (!$found) {

							$boxes .= "<input type=\"checkbox\" name=\"service" . $serviceid . "\" value=\"" . $serviceid . "\" onClick=\"servicetype(this," . $serviceid . "," . $num_services . ")\"> " . $servicedesc . "<br />\n";
						} else {

							$boxes .= "<input type=\"checkbox\" name=\"service" . $serviceid . "\" value=\"" . $serviceid . "\" checked onClick=\"servicetype(this," . $serviceid . "," . $num_services . ")\"> " . $servicedesc . "<br />\n";
						}
					}

					echo ("<tr><td colspan=2>" . $servicetitle . "</td></tr><tr><td class=profile_label valign=\"top\"></td><td class=profile_field valign=\"top\">" . $boxes . "</td></tr>\n");

					//GDPR settings second member (other members)

					$servicetitle = "Admin only view of GDPR, contact from other members through the Member Finder facility:";

					$query = $db->getQuery(true)

						->select('*')

						->from($db->qn('tblServices'))

						->where($db->qn('ServiceCategory') . ' = ' . $db->q('GDPR2_mem'))

						->order($db->qn('ServiceSortOrder'));

					$sections = $db->setQuery($query)->loadObjectList();

					$boxes = "";

					$num_services += count($sections);

					foreach ($sections as $sectionrow) {

						$serviceid = $sectionrow->ServiceID;

						$servicedesc = $sectionrow->ServiceDescGB;



						$found = strstr($Services, "|" . $serviceid . "|");

						if (!$found) {

							$boxes .= "<input type=\"checkbox\" name=\"service" . $serviceid . "\" value=\"" . $serviceid . "\" onClick=\"servicetype(this," . $serviceid . "," . $num_services . ")\"> " . $servicedesc . "<br />\n";
						} else {

							$boxes .= "<input type=\"checkbox\" name=\"service" . $serviceid . "\" value=\"" . $serviceid . "\" checked onClick=\"servicetype(this," . $serviceid . "," . $num_services . ")\"> " . $servicedesc . "<br />\n";
						}
					}

					echo ("<tr><td colspan=2>" . $servicetitle . "</td></tr><tr><td class=profile_label valign=\"top\"></td><td class=profile_field valign=\"top\">" . $boxes . "</td></tr>\n");
				}

				//classsified alerts

				$servicetitle = "Alert me by email when new classified adverts are posted in any of the following sections";

				$query = $db->getQuery(true)

					->select('*')

					->from($db->qn('tblServices'))

					->where($db->qn('ServiceCategory') . ' = ' . $db->q('classified'))

					->order($db->qn('ServiceSortOrder'));

				$sections = $db->setQuery($query)->loadObjectList();

				$boxes = "";

				$num_services += count($sections);

				foreach ($sections as $sectionrow) {

					$serviceid = $sectionrow->ServiceID;

					$servicedesc = $sectionrow->ServiceDescGB;



					$found = strstr($Services, "|" . $serviceid . "|");

					if (!$found) {

						$boxes .= "<input type=\"checkbox\" name=\"service" . $serviceid . "\" value=\"" . $serviceid . "\" onClick=\"servicetype(this," . $serviceid . "," . $num_services . ")\"> " . $servicedesc . "<br />\n";
					} else {

						$boxes .= "<input type=\"checkbox\" name=\"service" . $serviceid . "\" value=\"" . $serviceid . "\" checked onClick=\"servicetype(this," . $serviceid . "," . $num_services . ")\"> " . $servicedesc . "<br />\n";
					}



					/*

			if(isset(${"profile_service".$serviceid})){

				$boxes.="<input type=\"checkbox\" name=\"profile_service".$serviceid."\" value=\"".$servicedesc."\" checked> ".$servicedesc."<br />\n";

			}else{

				${"profile_service".$serviceid}="";

				$boxes.="<input type=\"checkbox\" name=\"profile_service".$serviceid."\" value=\"".$servicedesc."\"> ".$servicedesc."<br />\n";

			}

			*/
				}

				echo ("<tr><td colspan=2>" . $servicetitle . "</td></tr><tr><td class=profile_label valign=\"top\"></td><td class=profile_field valign=\"top\">" . $boxes . "</td></tr>\n");







				?>

				<tr>



					<td colspan="2"><b>Your situation or barge if you have one</b></td>

				</tr>

				<tr>

					<td class=profile_label>Situation *</td>

					<td class=profile_field>

						<?php

						$Situation0 = "";

						$Situation1 = "";

						$Situation2 = "";

						$Situation3 = "";

						$Situation4 = "";

						$Situation5 = "";

						${'Situation' . $Situation} = " selected";



						?>

						<select class=formcontrol name="Situation" id="Situation" onChange="changesituationform()">



							<option value="0" <?php echo ($Situation0); ?>>Select your situation</option>



							<option value="1" <?php echo ($Situation1); ?>>I am thinking about or looking for a barge</option>



							<option value="2" <?php echo ($Situation2); ?>>I am just interested in barges</option>

							<option value="3" <?php echo ($Situation3); ?>>I have a commercial interest in barges</option>

							<option value="4" <?php echo ($Situation4); ?>>I own a barge</option>

							<option value="5" <?php echo ($Situation5); ?>>I have an ownership share in a barge</option>

						</select>
					</td>

				</tr>

				<tr id="bargeowner1">

					<td class=profile_label>Details of your barge can be added and edited from the 'Members/Barge register/Edit' menu</td>

					<td class=profile_field>

						Name: <?php if (!$ShipName) {
									echo ("<font color=ff0000><i> Please add this to the barge register</i></font>");
								} else {
									echo ($ShipName);
								} ?><br>

						Class: <?php if (!$ShipClass) {
									echo ("<font color=ff0000><i> Please add this to the barge register</i></font>");
								} else {
									echo ($ShipClass);
								} ?><br>

						Year: <?php if ($ShipYear < 1) {
									echo ("<font color=ff0000><i> Please add this to the barge register</i></font>");
								} else {
									echo ($ShipYear);
								} ?><br>

						Length: <?php if ($ShipLength < 1) {
									echo ("<font color=ff0000><i> Please add this to the barge register</i></font>");
								} else {
									echo ($ShipLength);
								} ?><br>

						Beam: <?php if ($ShipBeam < 1) {
									echo ("<font color=ff0000><i> Please add this to the barge register</i></font>");
								} else {
									echo ($ShipBeam);
								} ?>

					</td>
				</tr>





				<tr id="bargeowner2">
					<td class=profile_field>Cruising Country where you spend most of your time *</td>

					<td class=profile_field>

						<select class=formcontrol name="LocationCruising" id="LocationCruising">

							<?

							if ($CountryCodeCruising) {

								$preselect_iso = $CountryCodeCruising;
							} else {

								$preselect_iso = "";
							}



							include("country_list.php");

							?>

						</select>

					</td>

				</tr>



				<?php



				//barge whose barge

				$serviceid = "1";

				$num_services += 1;

				$servicetitle = "Allow my name as keeper to appear in the membership Barge Register. All other contact details remain private.";

				$found = strstr($Services, "|" . $serviceid . "|");

				if (!$found) {

					echo ("<tr id=bargeowner3><td class=profile_field valign=\"top\">" . $servicetitle . "</td><td class=profile_field valign=\"top\"><input type=\"checkbox\" name=\"service" . $serviceid . "\" value=\"" . $serviceid . "\" onClick=\"servicetype(this," . $serviceid . "," . $num_services . ")\"></td></tr>\n");
				} else {

					echo ("<tr id=bargeowner3><td class=profile_field valign=\"top\">" . $servicetitle . "</td><td class=profile_field valign=\"top\" ><input type=\"checkbox\" name=\"service" . $serviceid . "\" value=\"" . $serviceid . "\" checked onClick=\"servicetype(this," . $serviceid . "," . $num_services . ")\"></td></tr>\n");
				}



				//how found



				$servicetitle = "<b>How did you first discover DBA?</b>";

				$query = $db->getQuery(true)

					->select('*')

					->from($db->qn('tblServices'))

					->where($db->qn('ServiceCategory') . ' = ' . $db->q('how_found'))

					->order($db->qn('ServiceSortOrder'));

				$sections = $db->setQuery($query)->loadObjectList();

				$boxes = "";

				$dropdown = "<select class=formcontrol name=\"HowFound\" id=\"HowFound\" onChange=\"changeHowFound()\">";

				$dropdown .= "<option value=\"0\">Select an option</option>\n";

				$num_services += count($sections);

				foreach ($sections as $sectionrow) {

					$serviceid = $sectionrow->ServiceID;

					$servicedesc = $sectionrow->ServiceDescGB;



					$found = strstr($Services, "|" . $serviceid . "|");

					if (!$found) {

						$dropdown .= "<option value=\"" . $serviceid . "\">" . $servicedesc . "</option>\n";

						$boxes .= "<input type=\"checkbox\" name=\"service" . $serviceid . "\" value=\"" . $serviceid . "\" onClick=\"servicetype(this," . $serviceid . "," . $num_services . ")\"> " . $servicedesc . "<br />\n";
					} else {

						$dropdown .= "<option value=\"" . $serviceid . "\" selected>" . $servicedesc . "</option>\n";

						$boxes .= "<input type=\"checkbox\" name=\"service" . $serviceid . "\" value=\"" . $serviceid . "\" checked onClick=\"servicetype(this," . $serviceid . "," . $num_services . ")\"> " . $servicedesc . "<br />\n";
					}



					/*

			if(isset(${"profile_service".$serviceid})){

				$boxes.="<input type=\"checkbox\" name=\"profile_service".$serviceid."\" value=\"".$servicedesc."\" checked> ".$servicedesc."<br />\n";

			}else{

				${"profile_service".$serviceid}="";

				$boxes.="<input type=\"checkbox\" name=\"profile_service".$serviceid."\" value=\"".$servicedesc."\"> ".$servicedesc."<br />\n";

			}

			*/
				}

				$dropdown .= "</select>";

				//echo("<tr><td colspan=2>".$servicetitle."</td></tr><tr><td class=profile_label valign=\"top\"></td><td class=profile_field valign=\"top\">".$boxes."<br>".$dropdown."</td></tr>\n");



				if (!$PaymentMethod) {

					//default to diret debit / standing order

					$PaymentMethod = "dd";
				}

				$PaymentMethodSelect_ch = "";

				$PaymentMethodSelect_so = "";

				$PaymentMethodSelect_dd = "";

				$PaymentMethodSelect_cc = "";

				$PaymentMethodSelect_foc = "";

				${'PaymentMethodSelect_' . $PaymentMethod} = " checked";



				?>

				<tr>



					<td colspan="2"><b>Subscription payment</b></td>

				</tr>

				<tr id=paydd>

					<td colspan=2 class=profile_field>

						<input name="PaymentMethod" type="radio" value="dd" <?php echo ($PaymentMethodSelect_dd); ?>>

						<b>Pay by UK bank direct debit.</b> If you have a UK bank account, select this option. Direct debit means the least amount of work for our volunteers when renewing subscriptions each year. You can print out a direct debit form <a href="<?php echo ($ddformpath); ?>" target='_blank'>here</a> to complete and post or scan and email to membership@barges.org once you have completed this page and clicked the 'join' button below.
					</td>

				</tr>

				<?php



				if ($userid != "new" && $PaymentMethod == "so") {

					//only existing members

				?>

					<tr id=payso>

						<td colspan=2 class=profile_field>

							<input name="PaymentMethod" type="radio" value="so" <?php echo ($PaymentMethodSelect_so); ?>>

							<b>Pay by UK bank standing order.</b> If you have a UK bank account, select this option. Standing order means less work for our volunteers when renewing subscriptions each year but direct debit is even better.
						</td>

					</tr>

				<?php

				}

				?>



				<tr id=paycc>

					<td colspan=2 class=profile_field><input name="PaymentMethod" type="radio" value="cc" <?php echo ($PaymentMethodSelect_cc); ?>>

						<b>Pay by secure credit or debit card.</b> For fast and secure payment by debit or credit card, select this option. An email reminder will be sent to you one month before renewal is due with instructions on how to pay securely on-line.
					</td>

				</tr>



				<?php

				if (($userid != "new" && $PaymentMethod == "ch") || ($membershipadmin == true)) {

					//only existing members

				?>



					<tr id=paych>

						<td colspan=2 class=profile_field>

							<input name="PaymentMethod" type="radio" value="ch" <?php echo ($PaymentMethodSelect_ch); ?>>

							<b>Pay by bank transfer or cheque.</b> If you have a UK bank account select this option. An email reminder will be sent to you one month before renewal is due with instructions on how to make a bank transfer or pay by cheque.
						</td>

					</tr>

				<?php

				}

				?>



				<?php

				if ($userid == "new") {

					//offer bank transfer to new members but not cheque

				?>



					<tr id=paych>

						<td colspan=2 class=profile_field>

							<input name="PaymentMethod" type="radio" value="ch" <?php echo ($PaymentMethodSelect_ch); ?>>

							<b>Pay by bank transfer.</b> If you have a UK bank account select this option. An email reminder will be sent to you one month before renewal is due with instructions on how to make a bank transfer.
						</td>

					</tr>

				<?php

				}

				?>

				<?php





				if ($membershipadmin == true || $PaymentMethod == "foc") {



				?>

					<tr id=paych>

						<td colspan=2 class=profile_field>

							<input name="PaymentMethod" type="radio" value="foc" <?php echo ($PaymentMethodSelect_foc); ?>>

							<b>Complimentary.</b> Select this option for Honarary or Press / Voucher members.
						</td>

					</tr>

				<?php

				}

				?>





			<?php

			//exit();

		}

		if ($subaction == "paysub") {

			$PaymentMethod = "cc";

			if ($BasicSub) {

				if ($PaymentMethod == "cc") {

					$message = $thanksforrenewing_cc;

					$screenmessage = $thanksforrenewing_cc;



					$item_name = "Renewed DBA subscription - " . $MemType;

					$item_number = "DBA-SubRenew";

					$shippingcost = 0;

					$buy = "<form></form><form name=\"paypal\" id=\"paypal\" action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\">\n";



					//$buy="<form></form><form name=\"paypal\" id=\"paypal\" target=\"paypal\" action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\">\n";

					$buy .= "<input type=\"hidden\" name=\"add\" value=\"1\">\n";

					$buy .= "<input type=\"hidden\" name=\"cmd\" value=\"_cart\">\n";

					$buy .= "<input type=\"hidden\" name=\"business\" value=\"treasurer@barges.org\">\n";

					$buy .= "<input type=\"hidden\" name=\"item_name\" value=\"" . $item_name . "\">\n";

					$buy .= "<input type=\"hidden\" name=\"item_number\" value=\"" . $item_number . "\">\n";

					$buy .= "<input type=\"hidden\" name=\"amount\" value=\"" . $BasicSub . "\">\n";

					//$buy.="<input type=\"hidden\" name=\"no_note\" value=\"1\">\n";

					$buy .= "<input type=\"hidden\" name=\"currency_code\" value=\"GBP\">\n";

					$buy .= "<input type=\"hidden\" name=\"lc\" value=\"GB\">\n";

					$buy .= "<input type=\"hidden\" name=\"return\" value=\"" . $paypalthanks . "\">\n";

					$buy .= "<input type=\"hidden\" name=\"cancel_return\" value=\"" . $paypalcancel . "\">\n";

					$buy .= "<input type=\"hidden\" name=\"notify_url\" value=\"" . $paypalreturn . "\">\n";

					$buy .= "<input type=\"hidden\" name=\"no_shipping\" value=\"1\">\n";

					$buy .= "<input type=\"hidden\" name=\"custom\" value=\"" . $userid . "\">\n";

					$buy .= "<input type=\"hidden\" name=\"email\" value=\"" . $Email . "\">\n";

					$buy .= "<input type=\"hidden\" name=\"first_name\" value=\"" . $FirstName . "\">\n";

					$buy .= "<input type=\"hidden\" name=\"last_name\" value=\"" . $LastName . "\">\n";

					$buy .= "<input type=\"hidden\" name=\"address1\" value=\"" . $Address1 . "\">\n";

					$buy .= "<input type=\"hidden\" name=\"address2\" value=\"" . $Address2 . "\">\n";

					$buy .= "<input type=\"hidden\" name=\"city\" value=\"" . $Address3 . "\">\n";

					$buy .= "<input type=\"hidden\" name=\"state\" value=\"" . $Address4 . "\">\n";

					$buy .= "<input type=\"hidden\" name=\"zip\" value=\"" . $PostCode . "\">\n";

					$buy .= "<input type=\"hidden\" name=\"country\" value=\"" . $CountryCode . "\">\n";

					$buy .= "<input type=\"hidden\" name=\"image_url\" value=\"http://www.barges.org/com_waterways_guide/paypal/dba_logo_icon_150x150.png\">\n";

					$buy .= "<input type=\"image\" src=\"Image/shop/btn_addtobasket.gif\" border=\"0\" name=\"submit\" alt=\"PayPal � pay online!\">\n";

					$buy .= "</form>\n";

					$screenmessage .= "<br><br>To complete your payment, click once on the 'Add to basket' button and proceed to the checkout.\n";
				}
			} else {

				//no sub amount

				$screenmessage = "<br><br>We do not seem to have enough information about your membership. Please choose 'Edit Profile' above to update your details and then try the renewal again";
			}

			echo (nl2br($screenmessage)) . $buy;
		}



			?>



			<?php

			if ($membershipadmin == true && !$subaction) {

				$LevelCode0 = "";

				$LevelCode1 = "";

				$LevelCode45 = "";

				$LevelCode50 = "";

				$LevelCode55 = "";

				$LevelCode56 = "";

				$LevelCode60 = "";

				${'LevelCode' . $Level} = " selected";

				$StatusCode1 = "";

				$StatusCode2 = "";

				$StatusCode3 = "";

				$StatusCode4 = "";

				$StatusCode5 = "";

				$StatusCode6 = "";

				$StatusCode7 = "";

				${'StatusCode' . $MemStatus} = " selected";



			?>





				<tr>



					<td colspan="2"><b>Admin fields<br></b>Membership no: <?php echo ($MembershipNo); ?> <i>Old membership no: <?php echo ($MemNo); ?></i> <br>

						Letters <select class=formcontrol name='report' id='report'>

							<option value="DBA_letter_Welcome.rtf">New member welcome</option>

							<option value="DBA_letter_Welcome_eBF.rtf">New member welcome eBF</option>

							<option value="DBA_letter_Sub_cc_Reminder.rtf">Sub due by cc reminder</option>

							<option value="DBA_letter_Sub_ch_Reminder.rtf">Sub due by ch reminder</option>

							<option value="DBA_letter_Sub_so_Reminder.rtf">Sub due by so reminder</option>

							<option value="DBA_letter_Change_to_DD_thanks.rtf">Change to DD / thanks</option>

							<option value="DBA_letter_Sub_dd_Notice.rtf">Direct Debit payment notice</option>

						</select><input class="formcontrol" type="button" name="openletter" onClick="mergedoc('','<?php echo ($userid); ?>')" value="Open">



						<b>if there are no updates</b> or make changes

						and then Press 'Update >' where you will then have another chance to print letters which will include any updates.
					</td>

				</tr>

				<tr>



					<td class=table_stripe_even>Administration rights</td>

					<td class=table_stripe_even><select class=formcontrol name="TheLevel" id="select2">

							<option value="1" <?php echo ($LevelCode1); ?>>None (member)</option>

							<option value="45" <?php echo ($LevelCode45); ?>>Editor group pages</option>

							<option value="50" <?php echo ($LevelCode50); ?>>Editor site</option>

							<option value="55" <?php echo ($LevelCode55); ?>>Administrator view only</option>

							<option value="56" <?php echo ($LevelCode56); ?>>Administrator view only and group pages edit</option>

							<option value="60" <?php echo ($LevelCode60); ?>>Administrator Editor</option>



						</select></td>

				</tr>

				<tr>



					<td class=table_stripe_even>Status</td>

					<td class=table_stripe_even><select class=formcontrol name="MemStatus" id="select2">

							<option value="1" <?php echo ($StatusCode1); ?>>Applied awaiting payment</option>

							<option value="2" <?php echo ($StatusCode2); ?>>Paid up</option>

							<option value="3" <?php echo ($StatusCode3); ?>>Renewal overdue</option>

							<option value="4" <?php echo ($StatusCode4); ?>>Gone away</option>

							<option value="7" <?php echo ($StatusCode7); ?>>Set to terminate</option>

							<option value="5" <?php echo ($StatusCode5); ?>>Terminated</option>

							<option value="6" <?php echo ($StatusCode6); ?>>Complimentary</option>



						</select></td>

				</tr>



				<tr>



					<td class=table_stripe_even>Date joined</td>

					<td class=table_stripe_even><input class=formcontrol type='text' name='TheDateJoined' id='TheDateJoined' size='30' value="<?php echo ($DateJoined); ?>"> <em>click box and choose from calendar</em></td>

				</tr>



				<tr>



					<td class=table_stripe_even>Date last renewed</td>

					<td class=table_stripe_even><input class=formcontrol type='text' name='TheDatePaid' id='TheDatePaid' size='30' value="<?php echo ($DatePaid); ?>"> <em>click box and choose from calendar</em></td>

				</tr>

				<tr>



					<td class=table_stripe_even>Date Ceased</td>

					<td class=table_stripe_even><?php echo ($DateCeased); ?></td>

				</tr>

				<tr>



					<?php

					//exit leaving codes

					$servicetitle = "Reason for leaving";

					$query = $db->getQuery(true)

						->select('*')

						->from($db->qn('tblServices'))

						->where($db->qn('ServiceCategory') . ' = ' . $db->q('exit_codes'))

						->order($db->qn('ServiceSortOrder'));

					$sections = $db->setQuery($query)->loadObjectList();

					$boxes = "";

					$num_services += count($sections);

					foreach ($sections as $sectionrow) {

						$serviceid = $sectionrow->ServiceID;

						$servicedesc = $sectionrow->ServiceDescGB;



						$found = strstr($Services, "|" . $serviceid . "|");

						if (!$found) {

							$boxes .= "<input type=\"checkbox\" name=\"service" . $serviceid . "\" value=\"" . $serviceid . "\" onClick=\"servicetype(this," . $serviceid . "," . $num_services . ")\"> " . $servicedesc . "<br />\n";
						} else {

							$boxes .= "<input type=\"checkbox\" name=\"service" . $serviceid . "\" value=\"" . $serviceid . "\" checked onClick=\"servicetype(this," . $serviceid . "," . $num_services . ")\"> " . $servicedesc . "<br />\n";
						}
					}

					echo ("<tr><td colspan=2>" . $servicetitle . "</td></tr><tr><td class=profile_label valign=\"top\"></td><td class=profile_field valign=\"top\">" . $boxes . "</td></tr>\n");


					//MGM Referrer proposals

					$query = $db->getQuery(true)

						->select('*')

						->from($db->qn('tblCoupon'))

						->where($db->qn('user_id') . ' = ' . $db->q($userid))

						->order($db->qn('id'));

					$result = $db->setQuery($query)->loadObjectList();

					$coupons = "";

					$num_coupons = count($result);
					echo ("<tr><td colspan=2>Referrer status</td></tr>\n");
					if ($num_coupons > 0) {
						echo ("<tr><td class=profile_label valign='top'><td class=profile_label valign='top'><table><tr><th>Recipient email</th><th>Date proposed</th><th>Date used</th></tr>\n");

						foreach ($result as $resultrow) {

							$coupon_recipient = $resultrow->email_recipient;

							$coupon_recipient_date_proposed = $resultrow->date_proposed;

							$coupon_recipient_date_used = $resultrow->date_used;

							if (!$coupon_recipient_date_used) {

								$coupon_recipient_date_used = "Unclaimed";
							}
							echo ("<tr><td>" . $coupon_recipient . "</td><td>" . $coupon_recipient_date_proposed . "</td><td>" . $coupon_recipient_date_used . "</td></tr>\n");
						}
						echo ("</table></td></tr>\n");
					} else {
						echo ("<td class=profile_label valign='top'></td><td class=profile_field valign='top'>None made</td></tr>\n");
					}

					//add new option
					echo ("<td class=profile_label>New recipient email</td><td class=profile_field><input class=formcontrol type='text' name='coupon_email_recipient' size='50' value=''></td></tr>\n");

					//end MgM


					//GDPR admin codes

					$servicetitle = "Quicklogin";

					$query = $db->getQuery(true)

						->select('*')

						->from($db->qn('tblServices'))

						->where($db->qn('ServiceCategory') . ' = ' . $db->q('GDPR_admin'))

						->order($db->qn('ServiceSortOrder'));

					$sections = $db->setQuery($query)->loadObjectList();

					$boxes = "";

					$num_services += count($sections);

					foreach ($sections as $sectionrow) {

						$serviceid = $sectionrow->ServiceID;

						$servicedesc = $sectionrow->ServiceDescGB;



						$found = strstr($Services, "|" . $serviceid . "|");

						if (!$found) {

							$boxes .= "<input type=\"checkbox\" name=\"service" . $serviceid . "\" value=\"" . $serviceid . "\" onClick=\"servicetype(this," . $serviceid . "," . $num_services . ")\"> " . $servicedesc . "<br />\n";
						} else {

							$boxes .= "<input type=\"checkbox\" name=\"service" . $serviceid . "\" value=\"" . $serviceid . "\" checked onClick=\"servicetype(this," . $serviceid . "," . $num_services . ")\"> " . $servicedesc . "<br />\n";
						}
					}

					echo ("<tr><td colspan=2>" . $servicetitle . "</td></tr><tr><td class=profile_label valign=\"top\"></td><td class=profile_field valign=\"top\">" . $boxes . "</td></tr>\n");

					?>





					<td class=table_stripe_even>Bank sort code</td>

					<td class=table_stripe_even><input class=formcontrol type='text' name='TheSortCode' size='8' value="<?php echo ($SortCode); ?>"></td>

				</tr>

				<tr>



					<td class=table_stripe_even>Bank account number</td>

					<td class=table_stripe_even><input class=formcontrol type='text' name='TheAccountNo' size='9' value="<?php echo ($AccountNo); ?>"> </td>

				</tr>

				<tr>



					<td valign="top" class=table_stripe_even>Groups management </td>



					<td class=table_stripe_even>

						<?php

						if ($userid == "new") {

							echo ("Save this new profile before administering Groups");
						}

						//Admin so allow adding or editing of groups	

						$gatekeeper_id = 1;

						echo ("<a href=\"javascript:groups($userid,$gatekeeper_id);\"> <img src=\"Image/common/close.gif\" width=\"18\" height=\"18\" alt=\"Administer Groups\" border=\"0\"> Click here to administer Groups list</a>  <img height=16 src='/Image/common/group.gif' width=13 border=0 alt='Group member page'> <img height=16 src='/Image/common/group_edit.gif' width=13 border=0 alt='Group edit page'>");



						?> </td>

				</tr>
				<tr>



					<td class=table_stripe_even>Notes </td>

					<td class=table_stripe_even><textarea name="TheNotesAdmin" cols="85" rows="5" class="formtextarea"><?php echo ($NotesAdmin); ?></textarea></td>

				</tr>



				<script type="text/javascript" src="../../popcal/calendar.js"></script>

				<script type="text/javascript" src="../../popcal/calendar-en.js"></script>

				<script type="text/javascript" src="../../popcal/calendar-setup.js"></script>

				<style type="text/css">
					@import url(../../popcal/calendar-blue.css);
				</style>

				<SCRIPT LANGUAGE="JavaScript">
					function fix(num) {

						string = "" + num;

						numberofdigits = string.length;

						if (numberofdigits < 2) {

							return '0' + string;

						} else {

							return string;

						}

					}

					function catcalc1(cal) {

						if (cal.dateClicked) {

							// OK, a date was clicked



							var y = cal.date.getFullYear();

							var m = cal.date.getMonth(); // integer, 0..11

							var d = cal.date.getDate(); // integer, 1..31



							var date = new Date(y, m, d);

							var now = new Date();

							var diff = date.getTime() - now.getTime();

							var days = Math.floor(diff / (1000 * 60 * 60 * 24));

							var dbdate = y + "-" + fix((m + 1)) + "-" + fix(d);

							var field = document.getElementById("TheDateJoined");

							field.value = dbdate;

							//"%A, %B %e, %Y",			

						}

					}

					function catcalc2(cal) {

						if (cal.dateClicked) {

							// OK, a date was clicked



							var y = cal.date.getFullYear();

							var m = cal.date.getMonth(); // integer, 0..11

							var d = cal.date.getDate(); // integer, 1..31



							var date = new Date(y, m, d);

							var now = new Date();

							var diff = date.getTime() - now.getTime();

							var days = Math.floor(diff / (1000 * 60 * 60 * 24));

							var dbdate = y + "-" + fix((m + 1)) + "-" + fix(d);

							var field = document.getElementById("TheDatePaid");

							field.value = dbdate;

							//"%A, %B %e, %Y",			

						}

					}

					Calendar.setup({

						inputField: "TheDateJoined",

						ifFormat: "%Y-%m-%d",

						showsTime: true,

						timeFormat: "24",

						onUpdate: catcalc1

					});

					Calendar.setup({

						inputField: "TheDatePaid",

						ifFormat: "%Y-%m-%d",

						showsTime: true,

						timeFormat: "24",

						onUpdate: catcalc2

					});



					function doreport(mypage, id) {

						var mypage = mypage + "?thismemberid=" + id;

						var form = 'form';

						var myname = "Reports";

						var w = 700;

						var h = 500;

						var scroll = "yes";

						var winl = (screen.width - w) / 2;

						var wint = (screen.height - h) / 2;

						winprops = 'height=' + h + ',width=' + w + ',top=' + wint + ',left=' + winl + ',scrollbars=' + scroll + ',resizable'

						mypage += (mypage.indexOf('?') != -1 ? '&' : '?') + 'nocache=' + Date.now();

						win = window.open(mypage, myname, winprops)

						if (parseInt(navigator.appVersion) >= 4) {
							win.window.focus();
						}

					}









					function groups(id, gkid) {



						var mypage = "<?php echo ($componentpath); ?>edit_groups.php?id=" + id + "&gkid=" + gkid;

						var myname = "groups";

						//var w = (screen.width - 100);

						//var h = (screen.height - 100);

						var w = 700;

						var h = 500;

						var scroll = "yes";



						var winl = (screen.width - w) / 2;

						var wint = (screen.height - h) / 2;

						winprops = 'height=' + h + ',width=' + w + ',top=' + wint + ',left=' + winl + ',scrollbars=' + scroll + ',resizable'

						mypage += (mypage.indexOf('?') != -1 ? '&' : '?') + 'nocache=' + Date.now();

						win = window.open(mypage, myname, winprops)

						if (parseInt(navigator.appVersion) >= 4) {
							win.window.focus();
						}



					}
				</script>

			<?php

			}

			?>



			<?php

			if ($membershipadmin == true && (!$subaction || $subaction == "back")) {

			?>

				<tr>

					<td colspan="2" class=bodytext><input type="button" class="formcontrol" name="go2" value="<?php echo ($go2value); ?>" onClick="document.form.subaction.value='subscribe2'; document.form.submit();">

						Admin - update without validation, blank login and password will be autofilled with Membership no and random password </td>

				</tr>

				<?php

				if ($MemStatus == 1) {

					//waiting for approval. This button does the same as admin Update but then changes the status to 2 paid and send an email welcome

				?>

					<tr>

						<td colspan="2" class=bodytext><input type="button" class="formcontrol" name="go2" value="Approve" onClick="document.form.subaction.value='Approve'; document.form.submit();">

							Admin - update without validation, updates status to 'paid' and then sends welcome email to this member

						</td>

					</tr>



				<?php

				}
			}



			if ((!$subaction || $subaction == "back") && $secondfamilymember == false) {

				?>

				<tr>



					<td colspan="2" class=bodytext><input type="button" class="btn btn-primary button_action" name="go2" value="<?php echo ($go2value); ?>" onClick="MM_validateForm();"></td>

				</tr>



			<?php

			}



			?>

			</table>

			<?php //after update admin option

			if ($membershipadmin == true && $subaction == "subscribe2") {

			?>

				<br /><b>Membership admin options</b><br /><br />

				Membership no: <?php echo ($MembershipNo); ?> <i>Old membership no: <?php echo ($MemNo); ?></i> <br>

				Letters <select class=formcontrol name='report' id='report'>

					<option value="DBA_letter_Welcome.rtf">New member welcome</option>

					<option value="DBA_letter_Welcome_eBF.rtf">New member welcome eBF</option>

					<option value="DBA_letter_Sub_cc_Reminder.rtf">Sub due by cc reminder</option>

					<option value="DBA_letter_Sub_ch_Reminder.rtf">Sub due by ch reminder</option>

					<option value="DBA_letter_Sub_so_Reminder.rtf">Sub due by so reminder</option>

					<option value="DBA_letter_Change_to_DD_thanks.rtf">Change to DD / thanks</option>

					<option value="DBA_letter_Sub_dd_Notice.rtf">Direct Debit payment notice</option>

				</select><input class="formcontrol" type="button" name="openletter" onClick="mergedoc('','<?php echo ($userid); ?>')" value="Open">





			<?php

			}

			?>

			<?php //membership admin

			if ($membershipadmin == true) {

			?>



				<SCRIPT LANGUAGE="JavaScript">
					function mergedoc(template, mid) {

						if (!template) {

							template = document.form.report.value;

						}

						var opendoc = "<?php echo ($componentpath); ?>mergedoc.php?template=" + template + "&memberid=" + mid;



						//alert(opendoc);

						var mypage = opendoc;

						var myname = "mergedoc";

						var w = 700;

						var h = 500;

						var scroll = "yes";

						var winl = (screen.width - w) / 2;

						var wint = (screen.height - h) / 2;

						winprops = 'height=' + h + ',width=' + w + ',top=' + wint + ',left=' + winl + ',scrollbars=' + scroll + ',resizable';

						mypage += (mypage.indexOf('?') != -1 ? '&' : '?') + 'nocache=' + Date.now();

						win = window.open(mypage, myname, winprops);

						if (parseInt(navigator.appVersion) >= 4) {
							win.window.focus();
						}



					}
				</script>



			<?php

			}

			?>





			<SCRIPT LANGUAGE="JavaScript">
				//hide option entry fields on startup or if current selection requires



				function changeHowFound() {

					var num_services = 90;

					var form = 'form';

					var dml = document.forms[form];

					var s = 0;

					var onload_services = '<?php echo ($Services); ?>';

					var cur_service_sel = document.form.Services.value;

					var new_service_sel = "|";



					var sel = document.getElementById('HowFound');

					var opt = sel.options[sel.selectedIndex];

					var servicecode = opt.value;



					var opt;







					//var x = document.getElementById("HowFound").selectedIndex;

					//var servicecode =document.getElementsByTagName("option")[x].value);



					if (servicecode != "0") {

						for (s = 0; s <= num_services; s++) {

							for (var i = 0, len = sel.options.length; i < len; i++) {

								opt = sel.options[i];

								var thisoption = opt.value;

								if ((cur_service_sel.indexOf("|" + s + "|") == -1 && new_service_sel.indexOf("|" + s + "|") == -1 && servicecode == s) || (cur_service_sel.indexOf("|" + s + "|") == -1 && new_service_sel.indexOf("|" + s + "|") == -1 && thisoption != s)) {

									//add it

									new_service_sel += s + "|";

								}

							}

						}

						alert(cur_service_sel + "\n" + new_service_sel + "\n " + servicecode);

						cur_service_sel = new_service_sel;

						document.form.Services.value = cur_service_sel;

					}

				}







				function servicetype(cbname, servicecode, num_services) {

					//override num_services

					var num_services = 90;

					var form = 'form';

					var dml = document.forms[form];

					var s = 0;

					var onload_services = '<?php echo ($Services); ?>';

					var cur_service_sel = document.form.Services.value;

					var new_service_sel = "|";

					var state = cbname.checked;

					var userid = document.form.userid.value;;

					var MemTypeCode = document.form.MemTypeCode.value;

					var BasicSub = document.form.BasicSub.value;

					var daystorenew = document.form.daystorenew.value;

					var oktoupdate = 1;

					var admin = <?php if ($membershipadmin == true) {
									echo ("1");
								} else {
									echo ("0");
								} ?>;

					var discount_message = "";

					if (onload_services.indexOf("|10|") == -1) {

						var pbfbp = false;

					} else {

						//previous BFP

						var pbfbp = true;

					}

					if (MemTypeCode < 5 && servicecode == 10 && state == false) {

						//choosing NOT to have paper BF and Single or Family member any paying mem type

						if (userid == 0 || userid == "new") {

							discount_message = "Your subscription has been reduced by opting not to have a postal copy";

						} else {

							//existing member changing option

							discount_message = "Your subscription will be reduced at the next renewal";

						}

					}

					if (MemTypeCode < 5 && servicecode == 10 && state == true) {

						//choosing to have paper BF and Single or Family member any paying mem type

						if (userid == 0 || userid == "new" || admin == 1) {

							discount_message = "Your subscription will be the standard by opting to have a postal copy";

						} else {

							//existing member changing option back

							if (pbfbp == false) {

								if (daystorenew <= 60) {

									//ok to tick postal BF as renewal due soon

									discount_message = "Your subscription will revert to the standard when your renewal is due in " + daystorenew + " days";

								} else {

									//Don't tick postal BF as renewal is > 60 days so need to contact membership

									discount_message = "Because your subscription has more than 60 days before renewal it is only possible to change back to a paper copy of Blue Flag by contacting the membership secretary and paying the additional subscription";

									oktoupdate = 0;

									cbname.checked = false;

								}

							}

						}



					}

					if (oktoupdate == 1) {

						for (s = 0; s <= num_services; s++) {

							if ((cur_service_sel.indexOf("|" + s + "|") == -1) && (servicecode != s)) {

								//ignore it

							} else {

								if ((cur_service_sel.indexOf("|" + s + "|") != -1) && (servicecode != s)) {

									//keep it

									new_service_sel += s + "|";

								} else {

									if ((cur_service_sel.indexOf("|" + s + "|") == -1) && (servicecode == s)) {

										if (state == 1) {

											//add it

											new_service_sel += s + "|";

										}

									}

								}

							}

						}

						//alert(cur_service_sel+" "+new_service_sel);

						cur_service_sel = new_service_sel;

						document.form.Services.value = cur_service_sel;

					}

					changememtypeform();

					document.getElementById('discount_message').innerHTML = discount_message;





				}





				changememtypeform();

				changesituationform();
			</script>



	</form>



<?

}

//echo("s".$subaction);

?>