<?php

//ini_set('session.cache_limiter', 'private');

$SMTPserver="leicester.eukhosting.net";

//Quicklogin secret string to MD5 encrypt
$secretstring="gdprsecretstring"; 

//page shortcuts
$contactpage="https://www.barges.org/homepage/contacts";
$eventsurl="https://www.barges.org/events";
$memberloginurl="https://www.barges.org/login";
$registerurl="https://www.barges.org/join";
$paypalreturn="https://www.barges.org/ppreturn.php";
$paypalthanks="https://www.barges.org/index.php";
$paypalcancel="https://www.barges.org/index.php";
						
$channel=1; //default for groups
$currentdirectory=getcwd();


//the membershipno of the admin person looking after detached asset register vessels
$VesselNoKeeperMID="007444"; //Barge register admin
//$VesselNoKeeperMID="001501"; //Ian Ferguson
//$VesselNoKeeperMID="001000"; //Membership sec

//DBA Bank details for so
$DBABankDetails="Lloyds TSB, Sort Code 30-94-08, Account Number 02675978";

//sub amounts GBP Apr 2010 increase of 10 last update
//sub amounts GBP Apr 2018 increase of 5 last update
//Single membership in Europe
$subamount1="40.00";
$subamount1d="35.00";
//Family membership in Europe
$subamount2="50.00";
$subamount2d="45.00";
//Single membership outside Europe
$subamount3="45.00";
$subamount3d="35.00";
//Family membership outside Europe
$subamount4="55.00";
$subamount4d="45.00";
//Honorary membership
$subamount5="0.00";
$subamount5d="0.00";
//Press membership (press/voucher split to 6 and 7 Paul Whitehouse request 06/04/2009)
$subamount6="0.00";
$subamount6d="0.00";
//Voucher membership
$subamount7="0.00";
$subamount7d="0.00";

$Services_newmember="|1|10|"; //|35| (Email occasional DBA news and informatio) removed to comply with GDPR Jan 2018

$baseorderno="1000";
$sitename="DBA - The Barge Association";
$siteurl="www.barges.org";
 
//mail forwarding
$adminemail="admin@barges.org";
//$adminemail="admin@productif.co.uk";
//$guidesemail="guideseditor@barges.org";
//$guidesemail="guideseditor@productif.co.uk";
$forumemail="admin@barges.org";
$bulletinemail="admin@barges.org";
//$membershipemail="membership@productif.co.uk";
$membershipemail="membership@barges.org";
$supportemail="admin@barges.org";
//$supportemail="membership@barges.org";
$feedbackemail="feedback@barges.org";
//$registrationemail="admin@productif.co.uk";
$registrationemail="admin@barges.org";
$eventsemail="events@barges.org";
$linksemail="links@barges.org";
$webmasteremail="dba@productif.co.uk";
$classifiedemail="classified@barges.org";
$contactus="email <a href=\"mailto:info@barges.org\">info@barges.org</a>";
$site="dba";
$defaultsite="dba";
$urlpath="medialibrary/";

$membership_address="DBA - The Barge Association, Cormorant, Spade Oak Reach, Cookham, SL6 9RQ<br />
//Tel: +44 (0)7939073820<br />
E-mail: membership@barges.org";

//Default letter signatories

/*$membership_signature="With best wishes<br />
<img src=\"http://www.barges.org/Image/general/DBA_letter_sig_membership_sec.jpg\" width=\"150\" height=\"39\">
Caroline Soper - Membership secretary";*/

$membership_signature="With best regards<br /><br /><br /><br /><br />
Caroline Soper - Membership secretary";


$footer1="<hr><font face=verdana size=2><b>DBA - The Barge Association</b></font><br><font face=verdana size=1></font><br><hr>";
$emailfooter="DBA - The Barge Association - www.barges.org - This email has been sent to you from the administration system because your email address exists within the secure Members section of the website. DBA's privacy policy can be seen at www.barges.org/homepage/privacy-a-cookies .";

$footerassetcontactemail="DBA - The Barge Association - www.barges.org - This email has been sent to you from a member regarging your entry in the Barge Register, via the secure Members section of the website. 
We respect your privacy and if you do not wish to continue receiving email from us, please go to www.barges.org/homepage/privacy-a-cookies to view our privacy policy. You can also Login to change your membership preferences.";

$emailfooter="DBA - The Barge Association - www.barges.org - This email has been sent to you from the administration system because your email address exists within the secure Members section of the website. DBA's privacy policy can be seen at www.barges.org/homepage/privacy-a-cookies .";

$copyright_guides="The guides are compiled from members contributions which have been 
shared with DBA for the sole use of its members. Copyright rests 
with DBA and must be respected. Copyright &copy; ".date("Y ").$sitename;

$footerguide="DBA - The Barge Association - www.barges.org - This email has been sent to you automatically from the waterways guide database in response to a request. DBA's privacy policy can be seen at www.barges.org/homepage/privacy-a-cookies .";

$message_guides="The waterways guide that you requested is attached to this email. To receive a full index of available guides, reply to this email with the single word 'index' in the subject line. You should then receive an email containing the guide index within a few minutes from which you can request further automated guides. 
\n\nAlternatively you can visit the member area of the website at www.barges.org/waterwaysguides where you can search by country and waterway and print, save or email each guide listing. 
\n\nFor other enquiries relating to guides and to report updates please contact guideseditor@barges.org
\n\nThe Waterways Guide is compiled from contributions by DBA members. Please help to keep it up to date by sending additions or amendments to the editor, guideseditor@barges.org
\n\nTo contribute a new entry there is a blank entry in the attached word document at the end of the list. You can copy it as often as necessary and write in your new data. Follow the suggested general pattern of each part wherever you can � but they are not exhaustive - the idea is to record anything you think may be useful to a newcomer to the location.
\n\nIf you are already using the guide your amendments are invaluable. If they are minor, simply list them in an email. If they are extensive then you might incorporate your new data in the affected box, copy it (or the entire guide), and email back to the editor at guideseditor@barges.org
\n\nPlease use a different colour or font, or the 'track changes' facility in the reviewing menu of Word, so that changes can be easily identified.
\n\nThank you in anticipation!.";

$message_index="The waterways guide index that you requested is shown below. This Index lists those canals or rivers in Europe for which the DBA Waterways Guide has data, the number of locations on each and the date of the most recent update.
\n\nTo receive an automated waterways guide, reply to this email and copy and past exactly and ONLY the name of the waterway into the subject line. You should then receive an email containing the guide within a few minutes. 
\n\nAlternatively you can visit the member area of the website at www.barges.org/members/waterwaysguide where you can search by country and waterway and print or save each guide listing. 
\n\nFor other enquiries relating to guides and to report updates please contact guideseditor@barges.org.
\n\nThe Guide has been compiled from contributions by DBA members. Please help to keep it up to date by sending amendments or additions to the editor at guideseditor@barges.org .";


$mooringsguidedocintrotext="This Guide has been compiled from contributions by many DBA members over several years. Without past member input the guide would not have been possible in the first place. Without continuing updating, amendment and correction from users it would soon become obsolete.\n
Please help us keep the guide relevant and useful by sending us any additions or amendments you come across on your cruise. \n
You can do this on-line following the instructions on the welcome page of the Waterways Guide section of the DBA website. \n
Alternatively you may amend this Word document by entering your additions or deletions and then emailing it as an attachment to the editor at guideseditor@barges.org.  For a new location copy one of the boxes and over-write all the data. If possible please use a different colour or highlight your changes so that they can be easily identified.\n
And if yours is a minor or urgent amendment just send a simple text email to the editor at guideseditor@barges.org identifying the waterway, location  name and km/PK/MP number and describing your proposed change. This is most useful if you wish to contribute whilst actually cruising.
\n\nThank you in anticipation!.\n\n";


$mooringsguidesectionintrotext="<p><strong><em>Note! ... There is a temporary problem with the Maps due to a change by Google. We are working to use their new version.</em></strong></p><p>Select a <em>Country</em> from the dropdown above, then select <em>All</em> or a single <em>Waterway</em>. &nbsp;You can use the Filter tick-boxes to narrow the search, e.g., to find a location with water. &nbsp;You then have options to&nbsp;</p>
<p>1 View a <strong>List</strong> of the selected locations<br /> 2 Download&nbsp;the information in a&nbsp;<strong>PDF</strong> file for saving or printing<br /> 3 Download the&nbsp;information&nbsp;in a <strong>KML</strong> file to add markers for the DBA locations to a mapping program* &nbsp;<br /> 4 Choose to see the locations on a <strong>Map</strong><br /> 5 <strong>Add</strong> a new entry<br /><strong></strong></p>
<p>Click a location in the List or click a pin on the Map to see the location's details.</p>
<p><em><strong>To update an entry</strong></em>, select a location in the list or on the map and click the update link. &nbsp;An editable version of the entry will appear with instructions above each box. Enter your changes in the appropriate boxes adding or deleting data as necessary.&nbsp;Change the entry to describe the location as it is now and please delete out-of-date information. [We keep a copy of each version so no information can be lost] &nbsp;When you've finished, click 'Send'. &nbsp;&nbsp;Your update will be send to the Guide editor for checking and tidying up before it appears on the live Guide.</p>
<p><em>If necessary you may send updates by email to the editor at guideseditor@barges.org . If you do this you must clearly identify the waterway and location. This is useful for minor or urgent amendments sent whilst cruising. It should be used sparingly as it causes more work for the Guide Editor than a normal update.</em></p>
<p><strong>If you use the Guide, please contribute to it with new entries or changes. That's the only way it works!</strong> &nbsp;</p>
<hr />
<p>* To use the KML files you must have a mapping program or app which can import them. &nbsp;Good examples are Google Earth (on PC or Mac) or Maps.me (on an iOS or Android phone/tablet). &nbsp;For these, just download the KML file to the device and open it. &nbsp;The DBA locations will appear as markers on the map. &nbsp;Clicking a location displays all the information from the Guide. &nbsp;&nbsp;</p>
<hr />
<p><em><strong>PDF and KML files are the property and Copyright of DBA The Barge Association, for use of members only.</strong></em></p>
";



/*$mooringsguidesectionintrotext="These Guides have been compiled from contributions by many DBA members over several years. Without past member input the guide would not have been possible in the first place. Without continuing updating, amendment and correction from users it would soon become obsolete.
\n<b>To use the guide</b>\nSelect a country from the dropdown above and then waterway and optional 'facilities' filter for that country to list all locations for which we currently have information. 
\nThe results can then be listed on screen or viewed and saved to your computer as an Adobe PDF document.
\nAlternatively you can use the map option to see a map on which are identified all locations for which we have a latitude and longitude. Click on the appropriate balloon to see location details. 
\n<b>To Contribute to the Guide</b>\nPlease help to keep the guide relevant and useful by proposing any additions or amendments you come across on your cruise. 
\n 
\n- For a new location - Select 'Add a new entry' from the options after the waterway drop-down. 
\n- For an Amendment - Select from the List the location Name for which you have amendments. Click on 'Edit this entry'
\nIn either case an editable version of the guide will appear with instructions above each box. Enter your changes in the appropriate boxes adding or deleting data as necessary. 
\nOn completion click 'Send' - your contribution will sent to the editor for checking. You will be emailed when the update is approved. This may take minutes or hours depending on how busy the editor is so please be patient and don't create aother update to the entry again until you have confirmation! Contact guideseditor@barges.org if you have a problem or question.
\nIf you are unable to get online but have some updates, send an email to the editor at guideseditor@barges.org identifying the waterway and name and PK number of the location and describing your proposed change.
";*/

$classifiedsectionintrotext="This page contains adverts placed by members. Select a section from the drop-down above to search.
\nMembers can place <b>private non-commercial</b> ads by logging in and selecting 'Add or edit classified adverts' in the 'Members / My Details' section.
\nPlacings are for 90 days with an option to renew and are free of charge to members. An image can also be uploaded.
\nAs soon as an ad has been placed, a link will be emailed out to all members requesting notification in their user profile. All ads are checked and validated and may be removed or suspended if inappropriate.
\nNon members can join <a href='/homepage/joining-and-paying-dba'>here</a> to benefit from this free service and many other member advantages.
\n<b>Transactions are at your own risk</b>. DBA take no responsibility for the accuracy or honesty of buyers or sellers or for the items sold here.
\nSellers should make sure cheques are fully cleared (not just appearing on your balance) and beware of refunding overpayments which is a known scam.
\n<b>More information about how to place commercial adverts in our Blue Flag magazine</b> can be found <a href='publications/blue-flag'>here</a></b>\n\n";


$shopintro="Listed items can be purchased by adding to the shopping basket and paying by credit or debit card or
by sending a UK bank cheque payable to DBA - The Barge Association, to:<br>DBA - The Barge Association, Cormorant, Spade Oak Reach, Cookham, MAIDENHEAD, SL6 9RQ, UK<br>
Telephone credit or debit card orders can be made to +44 (0)7939 073 820<br />
<b>For two items or more call for p&p prices or email bookshop@barges.org</b><br />
Buying books and insignia through DBA supports your Association";

//MEMBERSHIP

$membershipmailaddress="DBA - The Barge Association, Cormorant, Spade Oak Reach, Cookham, MAIDENHEAD, SL6 9RQ, UK";

$thanksforsubscribing_cc="Thank you for subscribing to DBA - The Barge Association. As soon as we have received notification of your card payment we will email you to confirm that you can login to gain access to the full member facilities. You will receive a welcome pack in the post within a few days.";

$thanksforpayment_cc="Thank you for your subscription payment. Your DBA - The Barge Association membership has been activated and we will despatch a welcome pack in the post within a few days.";

$thanksforsubscribing_dd="Thank you for subscribing. As soon as we receive your direct debit form, we will despatch a welcome pack in the post within a few days with login details so that you can access the full member facilities.";

$thanksforsubscribing_so="Thank you for subscribing. As soon as we receive your Standing order form, we will despatch a welcome pack in the post within a few days with login details so that you can access the full member facilities.";

$thanksforsubscribing_ch="Thank you for subscribing. As soon as we receive your bank transfer, we will despatch a welcome pack in the post within a few days with login details so that you can access the full member facilities. For UK account holders: Sort code: 30-94-08, Account: 02675978 For non-UK account holders: BIC: LOYDGB21124  IBAN: GB52 LOYD 3094 0802 6759 78 - Be aware that your bank may charge you a fee and that the payment may take a few days to come through.";

$thanksforsubscribing_foc="Thank you for subscribing. As soon as your application has been approved, we will despatch a welcome pack in the post within a few days with login details so that you can access the full member facilities.";
//Direct debit form path
$ddformpath="https://www.barges.org/File/media/175_DDIForm.pdf";

//subs renewal
$thanksforrenewing_cc="Thank you for deciding to renew your subscription with DBA - The Barge Association. As soon as we have received notification of your card payment we will email you to confirm that you can login again to gain access to the full member facilities.<br><br>If you don't have a PayPal account you can simply follow the 'Pay without a PayPal account' or 'Pay with a debit or credit card' link on the next screen immediately below the PayPal login section";

//<img src=\"https://www.paypalobjects.com/MERCHANTPAYMENTWEB-610-20100225-1/en_GB/i/logo/logo_ccVisa.gif\" alt=\"Visa\" border=\"0\" /><img src=\"https://www.paypalobjects.com/MERCHANTPAYMENTWEB-610-20100225-1/en_GB/i/logo/logo_ccMC.gif\" alt=\"Mastercard\" border=\"0\" /><img src=\"https://www.paypalobjects.com/MERCHANTPAYMENTWEB-610-20100225-1/en_GB/i/logo/logo_ccAmex.gif\" alt=\"American Express\" border=\"0\" /><img src=\"https://www.paypalobjects.com/MERCHANTPAYMENTWEB-610-20100225-1/en_GB/i/logo/logo_ccSwitch.gif\" alt=\"Switch\" border=\"0\" /><img src=\"https://www.paypalobjects.com/MERCHANTPAYMENTWEB-610-20100225-1/en_GB/i/logo/logo_ccSolo.gif\" alt=\"Solo\" border=\"0\" /><img src=\"https://www.paypalobjects.com/MERCHANTPAYMENTWEB-610-20100225-1/en_GB/i/logo/logo_ccDelta.gif\" alt=\"Visa Delta\" border=\"0\" /><img src=\"https://www.paypalobjects.com/MERCHANTPAYMENTWEB-610-20100225-1/en_GB/i/logo/logo_ccElectron.gif\" alt=\"Visa Electron\" border=\"0\" /><br>";

$thanksforrenewingpayment_cc="Thank you for your DBA - The Barge Association subscription renewal payment. Your membership has been re-activated.";

$thanksforrenewing_dd="Thank you for renewing your subscription with DBA - The Barge Association. As soon as we receive your direct debit form, we will re-activate your membership and post within a few days login details so that you can access the full member facilities.";

$thanksforrenewing_so="Thank you for renewing your subscription with DBA - The Barge Association. As soon as we receive your Standing order form, we will re-activate your membership and post within a few days login details so that you can access the full member facilities.";

$thanksforrenewing_ch="Thank you for renewing your subscription with DBA - The Barge Association. As soon as we receive your bank transfer or cheque, we will re-activate your membership.";

//$thanksforrenewing_foc="Thank you for subscribing. As soon as your application has been approved, we will despatch a welcome pack in the post within a few days with login details so that you can access the full member facilities.";


/*<meta name="DC.Type.category" lang="eng" content="web page">
<meta name="DC.Coverage: Spatial" lang="eng" content="England, UK">
<meta name="DC.Creator" lang="eng" content="Learning and Skills Council">
<meta name="DC.Format" scheme="IMT" content="html/text">
<meta name="DC.Language" scheme="ISO639-2/B" content="eng">
<meta name="DC.Publisher" lang="eng" content="--enter publisher information here--">
<meta name="DC.Rights.copyright" lang="eng" content="Learning and Skills Council http://www.lsc.gov.uk/national/corporate/copyright.htm">
<meta name="DC.Date.Created" scheme="ISO8601" content="--enter creation date here--">
<meta name="DC.Date.Modified" scheme="ISO8601" content="--enter modified date here--">
<meta name="DC.Identifier" scheme="URL" content="--enter root url of site here--">*/
$header2="This information is supplied by The Barge Association. Please note that some information supplied through the website may be of a general nature and may not relate specifically to the company.";
$footer2="<b>The Barge Association</b> - www.barges.org<br>Whilst every effort has been made to ensure the accuracy of this information, users should be aware that this cannot be guaranteed, and it is recommended that the organisation be contacted to confirm details.";
$footer3="<b>The Barge Association</b> - www.barges.org";
$adobehelp="<a href=\"http://www.adobe.com/products/acrobat/readstep2.html\" target=\"_blank\"><img src=\"Image/common/get_adobe_reader.gif\" alt=\"Get Adobe reader\" border=\"0\"></a>";


function decimal2degree($decimal_coord="",$latorlon="")	{
	//accepts a coordinate in decimal format and returns the equivalent degree
	//as a string

	$decpos=strpos($decimal_coord,'.');
	$whole_part=substr($decimal_coord,0,$decpos);
	$decimal_part=abs($decimal_coord-$whole_part);
	$minutes=intval($decimal_part*60);
	$seconds=intval((($decimal_part*60)-$minutes)*60);
	if ($latorlon=="LAT"){
		if ($whole_part<0){
			$whole_part=($whole_part*(-1));
			$L="S";
		}else{
			$L="N";
		}
	}else{
		if($latorlon=="LON"){
			if ($whole_part<0){
				$whole_part=($whole_part*(-1));
				$L="W";
			}else{
				$L="E";
			}
		}
	}
	$whole_part_padded=str_pad($whole_part,3, "0", STR_PAD_LEFT);
	$dec_mins=number_format($minutes+($seconds/60),2);
	//list($lhs,$rhs)=split(".",$dec_mins);
	//$dec_minutes_padded=str_pad($lhs,2, "0", STR_PAD_LEFT).".".str_pad($rhs,2, "0");
	//$dec_minutes_padded=str_pad($dec_mins,2, "0");
	//$formatted=str_pad($minutes,2, "0", STR_PAD_LEFT);
	$dec_minutes_padded=str_pad(number_format($dec_mins,2),5, "0",STR_PAD_LEFT);
	//printf("%03d", $num);
	//$formatted = sprintf("%02d.2f", $dec_mins);
	//format deg min sec
	//$degree=$whole_part."&deg;".$minutes."'".$seconds."&quot;".$L;;
	//format deg decmins
	$degree=$whole_part_padded."&deg;".$dec_minutes_padded."".$L;
	return $degree;
}


function unEscape($codedString)
{
	$decodedString = eregi_replace("#", "\"", $codedString);
	$decodedString = eregi_replace("~", ",", $decodedString);
	$decodedString = eregi_replace("\^", "<br>", $decodedString);
	$decodedString = eregi_replace("&", "&amp;", $decodedString);
	$decodedString = eregi_replace("�", "&pound;", $decodedString);

	return $decodedString;
}

function check_login($thislevel){
	if (!isset($_SESSION['useraccess'])) return false;
	if (!isset($_SESSION['login_memberid'])) return false;
	if ($thislevel=="admin" && $_SESSION['level'] >= 55){
		return true;
	}elseif ($thislevel=="editor" && $_SESSION['level'] >= 50){
		return true;
	}elseif ($thislevel=="editor_page" && $_SESSION['level'] >= 45){
		return true;
	}
}

//register variables functions http://www.zend.com/zend/art/art-sweat4.php

function register($varname, $defval=NULL) { 
	if (array_key_exists($varname, $_SERVER)) { 
		$retval = $_SERVER[$varname]; 
	} elseif (array_key_exists($varname, $_COOKIE)) { 
		$retval = $_COOKIE[$varname]; 
	} elseif (array_key_exists($varname, $_POST)) { 
		$retval = $_POST[$varname]; 
	} elseif (array_key_exists($varname, $_GET)) { 
		$retval = $_GET[$varname]; 
	} elseif (array_key_exists($varname, $_ENV)) { 
		$retval = $_ENV[$varname]; 
	} else { 
		$retval = $defval; 
	} 
	return $retval; 
}
	
function getpost_ifset($test_vars){ 
	if (!is_array($test_vars)) { 
		$test_vars = array($test_vars); 
	} 
	 
	foreach($test_vars as $test_var) { 
		if (isset($_POST[$test_var])) { 
			global $$test_var; 
			$$test_var = $_POST[$test_var]; 
		} elseif (isset($_GET[$test_var])) { 
			global $$test_var; 
			$$test_var = $_GET[$test_var]; 
		} 
	} 
}


function split_date($dt) 
{ 
	$elements = explode(" ", $dt);
	$dateElements = explode("-", $elements[0]);
    return $dateElements; 
} 

# This function just takes the date/time and strips the time off to leave only the date

function date_to_human($dt) 
{ 
	$elements = explode(" ", $dt);
    return split_date($elements[0]);
} 

function date_to_format($dt,$format) { 
	list ($datepart, $timepart) = explode (' ', $dt);
	list ($year, $month, $day) = explode ('-', $datepart);
	list ($hours, $minutes, $seconds) = explode (':', $timepart);
	if($format=="dt"){
		$datedisplay=date("jS F, Y - h:ma", mktime ($hours,$minutes,$seconds,$month,$day,$year));
	}elseif($format=="ymd"){
		$datedisplay=date("Y-m-d", mktime ($hours,$minutes,$seconds,$month,$day,$year));
	}elseif($format=="dmy"){
		$datedisplay=date("d-m-Y", mktime ($hours,$minutes,$seconds,$month,$day,$year));
	}else{
		$datedisplay=date("jS F, Y", mktime ($hours,$minutes,$seconds,$month,$day,$year));
	}	
	//$datedisplay=" ".date("j F, Y H:i:s", mktime ($hours,$minutes,$seconds,$month,$day,$year));
	return $datedisplay;
}
/*function sendemail($From,$FromName,$subject,$message){
	require("class.phpmailer.php");

	$mail = new PHPMailer();
	
	//Your SMTP servers details
	
	$mail->IsSMTP();               // set mailer to use SMTP
	$mail->Host = "leicester.eukhosting.net";  // specify main and backup server or localhost
	//$mail->Host = "localhost";  // specify main and backup server or localhost
	$mail->SMTPAuth = true;     // turn on SMTP authentication
	$mail->Username = "web@barges.org";  // SMTP username
	$mail->Password = "yFH&fQyXW$Uv"; // SMTP password
	//It should be same as that of the SMTP user
	
	
	$mail->From = $From;	//Default From email same as smtp user
	$mail->FromName = $FromName;
	
	//$feedbackemail="chris@productif.co.uk";
	$mail->AddAddress("$feedbackemail", "Connexions-bs website submission"); //Email address where you wish to receive/collect those emails.
	
	//$mail->WordWrap = 50;                                 // set word wrap to 50 characters
	$mail->IsHTML(false);                                  // set email format to HTML
	
	$mail->Subject = $subject;
	//$message = "Name of the requestor :".$_POST['fullname']." \r\n <br>Email Adrress :".$_POST['email']." \r\n <br> Query :".$_POST['query'];
	$mail->Body = $message;
	
	if(!$mail->Send()){
	   echo "Message could not be sent. <p>";
	   echo "Mailer Error: " . $mail->ErrorInfo;
	   exit;
	}
}
*/
/*$dbname="barges53_joodbadev";
$dbcnx = @mysql_connect("localhost","barges53_dev","S35P6qt1yl");
if (!$dbcnx) {
  echo( "<P>Unable to connect to the database server at this time.</P>" );
  exit();  
}
mysql_select_db("barges53_joodbadev") or die ("Can't open database" );
*/

$dbname="barges53_joodba";
$dbcnx = @mysql_connect("localhost","barges53_joodba","S35P6qt1yl");
if (!$dbcnx) {
  echo( "<P>Unable to connect to the database server at this time.</P>" );
  exit();  
}
mysql_select_db("barges53_joodba") or die ("Can't open database" );



?>