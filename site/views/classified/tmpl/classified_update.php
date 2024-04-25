<?php

use Joomla\CMS\Factory;
$config = Factory::getConfig();
$mailOn = Factory::getConfig()->get('mailonline') == '1';

$app = Factory::getApplication('com_waterways_guide');

?><h2>Classified Adverts Update</h2>

<table border="0" cellspacing="2" cellpadding="3" width="100%">
<?php


$user = Factory::getUser();
$login_memberid = $user->id;
$login_email = $user->email;

$editlisthelp="Cick on the ad entry for the detail where you can then edit each one. <br>If you wish to remove an ad from the listings, click on the Title to open the edit page and then click the 'delete' icon at the top. 
 You can add additional ads to your profile by clicking 'Add a new entry'";

$disclaimer_message="Transactions are at your own risk. DBA take no responsibility for the accuracy or honesty of buyers or sellers or for the items sold here.<br>
Sellers should make sure cheques are fully cleared (not just appearing on your balance) and beware of refunding overpayments which is a known scam.";

$test_vars1=(array(
'ClassifiedID',
'ClassifiedSection',
'classifiedsort',
'classifiedaction',
'classifiedsearchtext',
'ClassifiedSeekOffer',
'statusmessage',
'ClassifiedDatePosted',
'ClassifiedDateDisplay',
'errmsg',
'ClassifiedCurStatus',
'ClassifiedStatus',
'ClassifiedRef',
'ClassifiedTitle',
'extend',
'ClassifiedLocation',
'ClassifiedDescription',
'ClassifiedContactName',
'ClassifiedContactEmail',
'ClassifiedContactTel',
'ClassifiedWeblink',
'ClassifiedPrice',
'ClassifiedPriceCurrency',
'ClassifiedDatePosted',
'ClassifiedStartDate',
'ClassifiedEndDate',
'ClassifiedMemberID',
'MemTypeCode0',
'MemTypeCode1',
'MemTypeCode2',
'MemTypeCode3',
'MemTypeCode4',
'MemTypeCode5',
'login_Telephone1',
'images',
'uploadfile'
));
foreach($test_vars1 as $test_var) { 
	if(!$$test_var =  $app->input->getString($test_var)){
		$$test_var = "";
	}
}


//---------------------------------------Delete classified------------------------------------------

if ($classifiedaction=="delete") {
	
	//lookup classifieds to see if any images
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblClassified'))
		->where($db->qn('ClassifiedID').' = '.$db->q($ClassifiedID));
	try {
		$row = $db->setQuery($query)->loadAssoc();
	} catch(Exception $e) {
		echo("Can't find classifieds");
		exit();
	}
	$ClassifiedSection = stripslashes($row["ClassifiedSection"]);
	$ClassifiedRef = stripslashes($row["ClassifiedRef"]);
	$ClassifiedTitle = stripslashes($row["ClassifiedTitle"]);
	$ClassifiedMemberID = stripslashes($row["ClassifiedMemberID"]);
	$imagepath="Image/classified/".$ClassifiedID.".jpg";
	if (file_exists($imagepath)) {
		//delete image
		unlink ($imagepath);
		$images+=1;
	}
	$imagepath="Image/classified/clips/".$ClassifiedID.".jpg";
	if (file_exists($imagepath)) {
		//delete clip image
		unlink ($imagepath);
	}
	//now remove classifieds	
	$query = $db->getQuery(true)
		->delete($db->qn('tblClassified'))
		->where($db->qn('ClassifiedID').' = '.$db->q($ClassifiedID));
	$update = $db->setQuery($query)->execute();
	if(!$update){
		echo("Couldn't remove entry from database");
	}

	//update change log
	$datenow = date("Y-m-d H:i:s");
	$changelogtext=$ClassifiedRef." '".addslashes($ClassifiedTitle)."' deleted.";
	$subject="Classified";
	$insert = new \stdClass();
	$insert->MemberID = $ClassifiedMemberID;
	$insert->Subject = $subject;
	$insert->ChangeDesc = $changelogtext;
	$insert->ChangeDate = $datenow;
	$db->insertObject('tblChangeLog', $insert) or die ("Couldn't update change log ".print_r($insert, true).' into tblChangeLog');
	$statusmessage.=$changelogtext;
	if($images>0){
		$statusmessage.="<br>$images image(s) deleted\n";
	}
	$classifiedaction="list";
	
}




//---------------------------------------Save or new set of details------------------------------------------

if ($classifiedaction=="save") {
	//getpost_ifset(array('ClassifiedID','ClassifiedRef','ClassifiedSection','ClassifiedTitle','ClassifiedLocation','ClassifiedDescription','ClassifiedContactName','ClassifiedContactEmail','ClassifiedContactTel','ClassifiedWeblink','ClassifiedPrice','ClassifiedPriceCurrency','ClassifiedMemberID','ClassifiedDatePosted','ClassifiedStartDate','ClassifiedEndDate','ClassifiedSeekOffer','ClassifiedStatus','extend'));

	$datenow = date("Y-m-d H:i:s");
	if($ClassifiedID=="new"){
		//add a blank details
		$ClassifiedDate=$datenow;
		$ClassifiedStatus=0; //pending
		$ClassifiedCurStatus="0";
		$ClassifiedMemberID=$login_memberid;
		$insert = new \stdClass();
		$insert->ClassifiedTitle = addslashes($ClassifiedTitle);
		$insert->ClassifiedDatePosted = $ClassifiedDatePosted;
		$insert->ClassifiedMemberID = $ClassifiedMemberID;
		$insert->ClassifiedStatus = $ClassifiedStatus;
		$result = $db->insertObject('tblClassified', $insert, 'ClassifiedID');
		if(!$result){die ("Couldn't add new entry");}
		$ClassifiedID = $insert->ClassifiedID;
		$thisclassifiedaction="list";
		$ClassifiedRef= sprintf("%06d",$ClassifiedID); 
		$changelogtext=$ClassifiedRef." '".addslashes($ClassifiedTitle)."' added";
		$statusmessage.=$ClassifiedRef." '".addslashes($ClassifiedTitle)."' details have been posted to the classified section. Members opting in their membership profile to receive notification of new classifieds in that section will receive an email link to your entry.";

		//send email o admin for approval
		//$classifiedemail
		$subject="Classified addition to the $sitename";
		$headers = "From: Classified submissions <$classifiedemail>\n";
		$message="The following Classified has been posted by a member on the $sitename\n\n";

		$browsermessage="Thanks for your classified posting which has been added to the listing.<br>\n";
		$message.="Classified title: $ClassifiedTitle \n\nContact name: ".$ClassifiedContactName." \n\nSubmitters Email address: ".$ClassifiedContactEmail." \n\nDescription: ".$ClassifiedDescription." \n\n";
		$message.="Please check the full entry for suitability and edit and save if necessary. Otherwise no action is required. ".$siteurl."/classifiedadmin\n\n";	
		if($mailOn) {
			$mailer = Factory::getMailer();		
			$mailer->setSender([$config->get('mailfrom'), 'Classified submissions']);
			$mailer->addRecipient($classifiedemail);
			$mailer->addReplyTo($classifiedemail);
			$mailer->setSubject($subject);
			$mailer->setBody(nl2br($message));
			$mailer->isHtml(true);
			$mailer->Send();
		} else echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";
		//classified.php?classifiedaction=detail&classifiedid=".$ClassifiedID
	}
	//update existing record
	//check status change
	// 0 Pending awaiting approval;
	// 1 Live;
	// 5 Reject;
	// 2 Extend 90 days from end date;
	// 3 Suspend;
	// 4 Archive;
	if($ClassifiedCurStatus=="0" && $ClassifiedStatus=="0"){
		//new posting so make live and inform admin
		$ClassifiedStatus="1";
	}
	if($ClassifiedCurStatus=="0" && $ClassifiedStatus=="1"){
		//new posting made live so inform poster and members
		//change date to 90 days from posting
		$now=time();
		$futuredate = (86400*90) + $now ; //86,400 per day
		$ClassifiedStartDate=date("Y-m-d H:i:s", $now) ; 
		$ClassifiedEndDate=date("Y-m-d H:i:s", $futuredate) ; 
	}elseif($ClassifiedCurStatus=="1" && $ClassifiedStatus=="2"){
		//live posting extended for 90 days from end date
		//change date to 90 days from current end date
		$my_secs=strtotime($ClassifiedEndDate);
		$futuredate = (86400*90) + $my_secs ; //86,400 per day
		$ClassifiedEndDate=date("Y-m-d H:i:s", $futuredate) ;
		$ClassifiedStatus="1" ;
		$statusmessage.=$ClassifiedRef." '".$ClassifiedTitle."' extended for 90 days to ".$ClassifiedEndDate;
		$changelogtext=$ClassifiedRef." '".addslashes($ClassifiedTitle)."' extended for 90 days to ".$ClassifiedEndDate;
	}elseif($ClassifiedCurStatus!="3" && $ClassifiedStatus=="3"){
		//live posting suspended
		//just save with suspended status
		$statusmessage.=$ClassifiedRef." '".$ClassifiedTitle."' suspended";
		$changelogtext=$ClassifiedRef." '".addslashes($ClassifiedTitle)."' suspended";

	}elseif($ClassifiedCurStatus!="4" && $ClassifiedStatus=="4"){
		//posting archived
		//just save with archived status
		$statusmessage.=$ClassifiedRef." '".$ClassifiedTitle."' archived";
		$changelogtext=$ClassifiedRef." '".addslashes($ClassifiedTitle)."' archived";
	}elseif($ClassifiedCurStatus!="5" && $ClassifiedStatus=="5"){
		//posting rejected
		//change end date to now
		$ClassifiedEndDate=$now;
		$statusmessage.=$ClassifiedRef." '".$ClassifiedTitle."' rejected as unsuitable";
		$changelogtext=$ClassifiedRef." '".addslashes($ClassifiedTitle)."' rejected as unsuitable";
		//send reject message . . . . . . . . . . .
		
		
	}else{
		//no status change just an edit update
		$statusmessage.=$ClassifiedRef." '".$ClassifiedTitle."' details have been updated";
		$changelogtext=$ClassifiedRef." '".addslashes($ClassifiedTitle)."' updated";
	}	
	
	if($extend>0){
		//posting extended by poster from end date
		//change date to $extend days from current end date or now if later
		$now=time();
		$my_secs=strtotime($ClassifiedEndDate);
		if($my_secs>$now){
			//extend from end date
			$futuredate = (86400*$extend) + $my_secs ; //86,400 per day
		}else{
			//extend from now
			$futuredate = (86400*$extend) + $now ; //86,400 per day
		}
		
		$ClassifiedEndDate=date("Y-m-d H:i:s", $futuredate) ;
		$statusmessage.=" and extended for ".$extend." days to ".$ClassifiedEndDate;
		$changelogtext.=" and extended for ".$extend." days to ".$ClassifiedEndDate;
		if($ClassifiedCurStatus=="4"){
			//archived so make live again
			$ClassifiedStatus="1" ;
		}
	}
	
	$update = new \stdClass();
	$update->ClassifiedTitle = addslashes($ClassifiedTitle);
	$update->ClassifiedRef = addslashes($ClassifiedRef);
	$update->ClassifiedSection = addslashes($ClassifiedSection);
	$update->ClassifiedLocation = addslashes($ClassifiedLocation);
	$update->ClassifiedDescription = addslashes($ClassifiedDescription);
	$update->ClassifiedContactName = addslashes($ClassifiedContactName);
	$update->ClassifiedContactEmail = addslashes($ClassifiedContactEmail);
	$update->ClassifiedContactTel = addslashes($ClassifiedContactTel);
	$update->ClassifiedWeblink = addslashes($ClassifiedWeblink);
	$update->ClassifiedPrice = $ClassifiedPrice;
	$update->ClassifiedPriceCurrency = addslashes($ClassifiedPriceCurrency);
	$update->ClassifiedDatePosted = addslashes($ClassifiedDatePosted);
	$update->ClassifiedStartDate = addslashes($ClassifiedStartDate);
	$update->ClassifiedEndDate = addslashes($ClassifiedEndDate);
	$update->ClassifiedSeekOffer = addslashes($ClassifiedSeekOffer);
	$update->ClassifiedStatus = addslashes($ClassifiedStatus);
	$update->ClassifiedID = $ClassifiedID;
	$result = $db->updateObject('tblClassified', $update, 'ClassifiedID');
	if(!$result){ die ("Couldn't update database - ".print_r($update, true).' update tblSlassified'); }

	//image upload
	function file_upload_error_message($error_code) {
		switch ($error_code) { 
			case UPLOAD_ERR_INI_SIZE: 
				return 'The uploaded file exceeds the upload_max_filesize directive in php.ini'; 
			case UPLOAD_ERR_FORM_SIZE: 
				return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'; 
			case UPLOAD_ERR_PARTIAL: 
				return 'The uploaded file was only partially uploaded'; 
			case UPLOAD_ERR_NO_FILE: 
				return 'No file was uploaded'; 
			case UPLOAD_ERR_NO_TMP_DIR: 
				return 'Missing a temporary folder'; 
			case UPLOAD_ERR_CANT_WRITE: 
				return 'Failed to write file to disk'; 
			case UPLOAD_ERR_EXTENSION: 
				return 'File upload stopped by extension'; 
			default: 
				return 'Unknown upload error'; 
		} 
	} 


	if ($_FILES['uploadfile']['error'] === UPLOAD_ERR_OK){
		// upload ok
		//variables . . . . . . . . . . . . . . 
		$the_imagepath="Image/classified";
		$image_resize_width=700;
		$imagepercent="";
		$the_clippath="Image/classified/clips"; 
		$clip_width=120;
		$error="";
		$allowedExts = array("jpg", "jpeg");
		//the file name will be the asset id
		$myfilename=$ClassifiedID.".jpg";

				
		# validate the file $the_file		
		$uploadfiletmp =$_FILES["uploadfile"]["tmp_name"];
		$uploadfilename =$_FILES["uploadfile"]["name"];
		$uploadfiletype =$_FILES["uploadfile"]["type"];
		$uploadfilesize =$_FILES["uploadfile"]["size"];

		# check if we are allowed to upload this file_type	
	
		$extension = strtolower(end(explode(".", $uploadfilename)));
		if ((($uploadfiletype == "image/jpeg")
			|| ($uploadfiletype == "image/pjpeg"))
			&& in_array($extension, $allowedExts))   {

			//its an allowed image
			$size = GetImageSize($uploadfiletmp);
			list($foo,$width,$bar,$height) = explode("\"",$size[3]);		
			if (file_exists($the_imagepath . "/" . $myfilename)) {
				//delete old version if valid
				unlink ($the_imagepath . "/" . $myfilename);
				//$statusmessage.="<br>$the_imagepath / $the_file_name has been deleted\n";
			}
			if (!@copy($uploadfiletmp, $the_imagepath . "/" . $myfilename)) {
				$error.="A problem arose during the upload to (" . $the_imagepath . "/" . $myfilename. ")";
			}else{
				//Upload OK so calculate file size and make clip if image			
				$mediafilesize=filesize ($the_imagepath . "/" . $myfilename);		
				$statusmessage.="<br>File $myfilename ($mediafilesize Bytes) has been uploaded successfully"; 
				#-+ Check if the file exists 
				if (!file_exists($the_imagepath . "/" . $myfilename)){ 
					die ("Error: File not found..."); 
				} 
				$filename = $the_imagepath . "/" . $myfilename;
				
				// Get dimensions
				list($org_w, $org_h) = getimagesize($filename);
				$src_img = imagecreatefromjpeg($filename);	
				if($the_clippath){
					// Directory where the thumbnails will be saved 
					// Calculate thumbnail height
					$new_width = $clip_width;
					$new_height = floor($clip_width * $org_h / $org_w);					
					$clip_height=$new_height;
					// Resample
					$image_p = imagecreatetruecolor($new_width, $new_height);

					imagecopyresampled($image_p, $src_img, 0, 0, 0, 0, $new_width, $new_height, $org_w, $org_h);
					// Save it!
					imagejpeg($image_p, $the_clippath . "/" . $myfilename, 100);

					$mediaclipurl=$the_clippathurl . "/" . $myfilename;
					$statusmessage.="<br>Image clip has been created\n"; 
					$statusmessage.="<br>Original dimensions: $org_w x $org_h\n"; 
					$statusmessage.="<br>Clip dimensions: $clip_width x $clip_height\n";
				}
				//resize original if too big
				if($image_resize_width || $imagepercent){
					if($imagepercent){
						$new_width = floor($org_w * $imagepercent/100);
						if($new_width<$org_w){
							//no need to resize
							$new_width=$org_w;
							$new_height = $org_h;
						}else{
							$new_height = floor($org_h * $imagepercent/100);
						}
						
					}elseif($image_resize_width){
						$new_width= floor($image_resize_width);
						if($new_width<$org_w){
							//need to resize
							$new_height = floor($org_h * ($new_width / $org_w));
						}else{
							$new_width=$org_w;
							$new_height = $org_h;
						}
					}else{
						//no change
						$new_width=$org_w;
						$new_height = $org_h;
					}
					//resample to reduce file size and width if necessary
					//20090309 CJG uploads <500 were not being resampled so could end up big files
					//now all are resampled even if less than 500 to optimise the file
					//if($new_width!=$org_w){
					//needs resizing
					$new_w=$new_width;
					$new_h=$new_height;
					// Resample
					$image_p = imagecreatetruecolor($new_width, $new_height);
					//$src_img = imagecreatefromjpeg($filename);
					imagecopyresampled($image_p, $src_img, 0, 0, 0, 0, $new_width, $new_height, $org_w, $org_h);
					//delete old file ????

					//unlink ("../".$oldmedialocationurl);
					
					// Save it!
					imagejpeg($image_p, $the_imagepath . "/" . $myfilename, 100);
					
					$oldmediafilesize=$mediafilesize;
					$mediafilesize=filesize ($the_imagepath . "/" . $myfilename);		
					$statusmessage.="<br>The image has been resized"; 
					$statusmessage.=" from $org_w x $org_h\n"; 
					$statusmessage.="to $new_w x $new_h\n"; 
					imagedestroy($src_img);
				}
			}
		}else{
			$statusmessage.="<br>The image $uploadfiletype was not of an allowed type\n"; 
		}
	}else{
		if($_FILES['uploadfile']['error'] === UPLOAD_ERR_NO_FILE){
			$statusmessage.="<br>No image uploaded"; 
		}else{
			$statusmessage.="<br>There was an error uploading the image: " . $_FILES['uploadfile']['error']. file_upload_error_message($_FILES['uploadfile']['error']); 
			$statusmessage.="<br>Temp Name:". $_FILES["uploadfile"]["tmp_name"];
			$statusmessage.="<br>Name:". $_FILES["uploadfile"]["name"];
			$statusmessage.="<br>Type:". $_FILES["uploadfile"]["type"];
			$statusmessage.="<br>Size:". $_FILES["uploadfile"]["size"];
			$statusmessage.="<br>The File:". $uploadfile;
			
			
		}
	}
	//end of image upload

	
	$classifiedaction="list";
	if(!empty($error)){
		$statusmessage.="<br>".$error;
		//update change log
		$datenow = date("Y-m-d H:i:s");
		
		$subject="Classified";
		$insert = new \stdClass();
		$insert->MemberID = $ClassifiedMemberID;
		$insert->Subject = $subject;
		$insert->ChangeDesc = $changelogtext;
		$insert->ChangeDate = $datenow;
		$db->insertObject('tblChangeLog', $insert) or die ("Couldn't update change log ".print_r($insert, true).' into tblChangeLog');
		
	}

}

echo("<input name=\"classifiedsort\" type=\"hidden\" value=\"$classifiedsort\">\n");
echo("<input name=\"classifiedaction\" type=\"hidden\" value=\"$classifiedaction\">\n");
echo("<input name=\"ClassifiedID\" type=\"hidden\" value=\"$ClassifiedID\">\n");

//---------------------------------------List classifieds---------------------------------------------

if ($classifiedaction=="" || $classifiedaction=="list") {
	
	
	//get the classified(s)
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblClassified'))
		->order($db->qn('ClassifiedEndDate').' DESC');
	if($admin != "open") {
		//just list the login member
		$query->where($db->qn('ClassifiedMemberID').' = '.$db->q($login_memberid));
	}
	$classifieds = $db->setQuery($query)->loadAssocList();
	$vrows = count($classifieds);
	# If the search was unsuccessful then Display Message try again.
	$rowclass="table_links";
	$listresults="<tr><td class=table_links><b>Title</b></td><td class=table_links><b>End date</b></td><td class=table_links><b>Status</b></td></tr>\n";
	$listresults.="<tr><td colspan=3 class=$rowclass><a href=\"#\" onClick=\"document.form.classifiedaction.value='edit';document.form.ClassifiedID.value='new';document.form.submit()\"><font color=#ff0000><b>Add a new entry</b></font> </a></td></tr>\n";
	foreach($classifieds as $row) {

		# Display classified Results, l
		$ClassifiedID = $row["ClassifiedID"];
		$ClassifiedDatePosted = stripslashes($row["ClassifiedDatePosted"]);
		if($ClassifiedDatePosted && $ClassifiedDatePosted!= "0000-00-00 00:00:00"){
			$ClassifiedDatePostedDisplay=date_to_format($ClassifiedDatePosted,"d") ;
		}else{
			$ClassifiedDatePostedDisplay="To follow";
		}
		$ClassifiedEndDate = stripslashes($row["ClassifiedEndDate"]);			
		if($ClassifiedEndDate && $ClassifiedEndDate!= "0000-00-00 00:00:00"){
			list ($myDate, $myTime) = explode (' ', $ClassifiedEndDate);
			$ClassifiedEndDateDisplay=$myDate ;
		}else{
			$ClassifiedEndDateDisplay="Open";
		}					
		$ClassifiedTitle = stripslashes($row["ClassifiedTitle"]);
		$ClassifiedStatus = $row["ClassifiedStatus"];
		switch ($ClassifiedStatus) {
			case 0:
				$ClassifiedStatusText="Pending";
				break;
			case 1:
				$ClassifiedStatusText="Live";
				break;
			case 2:
				$ClassifiedStatusText="Extended";
				break;
			case 3:
				$ClassifiedStatusText="Suspended";
				break;
			case 4:
				$ClassifiedStatusText="Archive";
				break;
			case 5:
				$ClassifiedStatusText="Rejected";
				break;
		}

		$listresults.="<tr><td class=$rowclass><a href=\"#\" onClick=\"document.form.classifiedaction.value='edit';document.form.ClassifiedID.value='".$ClassifiedID."';document.form.submit()\">".$ClassifiedTitle."</a>".$ClassifiedDateDisplay."</td>\n";
		$listresults.="<td class=$rowclass>".$ClassifiedEndDateDisplay."</td>\n";
		$listresults.="<td class=$rowclass>".$ClassifiedStatusText."</td>\n";
		$listresults.="</tr>\n";

		//debug row $listresults.="<tr><td colspan=6 class=$rowclass>".$a[$listrow][15]."</td></tr>\n";	
	}

	
	echo("<tr><td colspan=3 class=list_small_member>$editlisthelp</td></tr>\n");	
	if($statusmessage){
		echo("<tr><td colspan=3 class=list_small><font color=ff0000><b>".$statusmessage."</b></font></td></tr>\n");
	}
	if($admin=="open"){
		echo("<tr><td colspan=3 class=list_small><b>Aministrating $vrows classified(s)</b><br>Click on a Title to view, edit, remove details or change status.</td></tr>\n");
	}else{
	
		if($vrows==0){
			echo("<tr><td colspan=3 class=list_small><b>There are currently no classifieds attached to your profile</b><br>Click on 'Add a new entry' to enter details</td></tr>\n");
		}elseif($vrows==1){
			echo("<tr><td colspan=3 class=list_small><b>There is currently $vrows classified attached to your profile</b><br>Click on the Title to view, edit or remove details</td></tr>\n");
	
		}else{
			echo("<tr><td colspan=3 class=list_small><b>There are currently $vrows classifieds attached to your profile</b><br>Click on a Title to view, edit or remove details</td></tr>\n");
		
		}
	}
	echo($listresults);
}


//---------------------------------------classified edit---------------------------------------------

if ($classifiedaction=="edit" || $classifiedaction=="verify") {
	//lookup this classified detail or input new
	$datenow = date("Y-m-d H:i:s");

?>
	<script language="JavaScript">
  
    function Help(Subject){
    var mypage = Subject;
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
    
    function inserttext(text) {
        var txtarea = document.form.ClassifiedTitle;
        //text = ' ' + text + ' ';
        txtarea.value  = text;
		document.form.keywords.options["0"].selected=true;
        txtarea.focus();
    }
    
    function storeCaret(textEl) {
        if (textEl.createTextRange) textEl.caretPos = document.selection.createRange().duplicate();
    }

    function SubmitContent() {	
		var errors="";
		var ClassifiedTitle = document.form.ClassifiedTitle.value;
		if (ClassifiedTitle == ""){
			errors+='- title\n';
			document.form.ClassifiedTitle.style.backgroundColor ="#ffff00";
		}else{
			document.form.ClassifiedTitle.style.backgroundColor ="#ffffff";
		}
		var ClassifiedLocation = document.form.ClassifiedLocation.value;
		if (ClassifiedLocation == "0"){
			errors+='- location\n';
			document.form.ClassifiedLocation.style.backgroundColor ="#ffff00";
		}else{
			document.form.ClassifiedLocation.style.backgroundColor ="#ffffff";
		}	
		var ClassifiedSection = document.form.ClassifiedSection.value;
		if (ClassifiedSection == "0"){
			errors+='- section\n';
			document.form.ClassifiedSection.style.backgroundColor ="#ffff00";
		}else{
			document.form.ClassifiedSection.style.backgroundColor ="#ffffff";
		}	
		var ClassifiedDescription = document.form.ClassifiedDescription.value;
		if (ClassifiedDescription == ""){
			errors+='- description\n';
			document.form.ClassifiedDescription.style.backgroundColor ="#ffff00";
		}else{
			document.form.ClassifiedDescription.style.backgroundColor ="#ffffff";
		}	
		var ClassifiedContactName = document.form.ClassifiedContactName.value;
		if (ClassifiedContactName == ""){
			errors+='- contact name\n';
			document.form.ClassifiedContactName.style.backgroundColor ="#ffff00";
		}else{
			document.form.ClassifiedContactName.style.backgroundColor ="#ffffff";
		}					
		var ClassifiedContactEmail = document.form.ClassifiedContactEmail.value;
		if (ClassifiedContactEmail == ""){
			errors+='- contact email\n';
			document.form.ClassifiedContactEmail.style.backgroundColor ="#ffff00";
		}else{
			document.form.ClassifiedContactEmail.style.backgroundColor ="#ffffff";
		}	
		/*var ClassifiedWeblink = document.form.ClassifiedWeblink.value;
		if (ClassifiedWeblink.indexOf("ttp:")>0){
			errors+='- remove http:// from weblink\n';
			document.form.ClassifiedWeblink.style.backgroundColor ="#ffff00";
		}else{
			document.form.ClassifiedWeblink.style.backgroundColor ="#ffffff";
		}*/	
		if (errors) {
			alert('Please check the highlighted entries and try again:\n'+errors);
		}else{
			document.form.save.value='Please Wait . . . . Updating . .';
			//document.form.save.src="Images/common/progressbar.gif";
			document.form.submit();
		}
	}

	function DeleteContent() {
		if (confirm("Confirm deletion by clicking OK")) {
			document.form.submit();	
		}else{
			document.form.classifiedaction.value='detail';
		}
	}	
	
	/* This script and many more are available free online at
	The JavaScript Source!! http://javascript.internet.com
	Created by: Mario Costa |  */
	function currencyFormat(fld, milSep, decSep, e) {
	  var sep = 0;
	  var key = '';
	  var i = j = 0;
	  var len = len2 = 0;
	  var strCheck = '.0123456789';
	  var aux = aux2 = '';
	  var whichCode = (window.Event) ? e.which : e.keyCode;
	
	  if (whichCode == 13) return true;  // Enter
	  if (whichCode == 8) return true;  // Delete
	  key = String.fromCharCode(whichCode);  // Get key value from key code
	  if (strCheck.indexOf(key) == -1) return false;  // Not a valid key
	  
	  //return false;
	}

   
    </script>
 <tr><td colspan=3 class='list_small'><a href="#" onClick="document.form.classifiedaction.value='<?php echo($thisclassifiedaction); ?>';document.form.submit()">Back to the list <img src="Image/common/back1.gif" width="18" height="18" border="0" alt="Back to the details"></a>
 </td></tr>
   


 	<?php
 	echo("<tr><td class=bodytext colspan=3><font color=ff0000>".$disclaimer_message."</font></td></tr>\n");

 	if($errmsg){
		echo("<tr><td colspan=3 class='list_small'><font color=ff0000><b>$errmsg</b></font></td></tr>\n");
	}
	if($ClassifiedID=="new"){
		
		echo("<tr><td colspan=3 class='list_small'>Enter the details below and click 
		<input type=\"button\" name=\"save\" class=\"formcontrol\" value=\"Save &raquo;\" onclick=\"document.form.classifiedaction.value='save';SubmitContent(this);\" />
		<img src=\"Image/common/save.gif\" name=\"save\"  alt=\"save\" border=\"0\" width=\"23\" height=\"20\">
		to add to the live listing. A link will then be emailed out to all members requesting notification in their user profile.
		<br>All ads are checked and validated and may be removed or suspended if found to be of a commercial nature or inappropriate.
		<br>Your ad will appear on the live 'classified section' site for 90 days. Towards the end of this time you will receive an email asking if you wish to renew for a further 90 days. If during the 90 days, your ad is successful, please log in and remove it to avoid further responses.</td></tr>\n");
			
		//get some default member info
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('tblMembers'))
			->where($db->qn('ID').' = '.$db->q($user->id));
		$row = $db->setQuery($query)->loadAssoc();
		$login_FirstName=$row["FirstName"];
		$login_LastName=$row["LastName"];
		$login_countrycode=$row["CountryCode"];
		$login_email=$row["Email"];
		$login_Telephone1=$row["Telephone1"];	
		
		$ClassifiedUpdate=date("Y-m-d H:i:s");
		$ClassifiedDatePosted = $ClassifiedUpdate;
		$ClassifiedContactName=$login_FirstName." ".$login_LastName;
		$ClassifiedLocation=$login_countrycode;
		$ClassifiedContactEmail=$login_email;
		$ClassifiedContactTel=$login_Telephone1;
		$ClassifiedStatus = 0; //pending
		
	}else{
		if(!$errmsg){
			//existing detail
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('tblClassified'))
				->where($db->qn('ClassifiedID').' = '.$db->q($ClassifiedID));
			if($admin != "open") {
				$query->where($db->qn('ClassifiedMemberID').' = '.$db->q($login_memberid));
			}
			try {
				$result = $db->setQuery($query)->loadAssocList();
			} catch(Exception $e) {
				echo("Can't find classified detail to edit");
				exit();
			}
			
			$num_rows = count($result);
			
			# If the search was unsuccessful then Display Message try again.
			if (empty($num_rows)) {
				echo("<tr><td colspan=3 class='list_small'>Sorry - no details available for this classified<br><hr></td></tr>"); 
				exit();
			}
		
			$datenow = time();
			$row = reset($result);

			$ClassifiedID = $row["ClassifiedID"];
			$ClassifiedRef = stripslashes($row["ClassifiedRef"]);
			$ClassifiedSection = stripslashes($row["ClassifiedSection"]);
			$ClassifiedTitle = $row["ClassifiedTitle"];
			$ClassifiedLocation = stripslashes($row["ClassifiedLocation"]);
			if(!$ClassifiedLocation){
				$ClassifiedLocation=$login_countrycode;
			}

			$ClassifiedDescription = stripslashes($row["ClassifiedDescription"]);
			$ClassifiedContactName = stripslashes($row["ClassifiedContactName"]);
			if(!$ClassifiedContactName){
				$ClassifiedContactName=$login_FirstName." ".$login_LastName;
			}
			$ClassifiedContactEmail = stripslashes($row["ClassifiedContactEmail"]);
			if(!$ClassifiedContactEmail){
				$ClassifiedContactEmail=$login_email;
			}
			$ClassifiedContactTel = stripslashes($row["ClassifiedContactTel"]);
			if(!$ClassifiedContactTel){
				$ClassifiedContactTel=$login_Telephone1;
			}
			$ClassifiedWeblink = stripslashes($row["ClassifiedWeblink"]);
			$ClassifiedPrice = $row["ClassifiedPrice"];
			$ClassifiedPriceCurrency = stripslashes($row["ClassifiedPriceCurrency"]);
			$ClassifiedMemberID = $row["ClassifiedMemberID"];
			$ClassifiedDatePosted = stripslashes($row["ClassifiedDatePosted"]);
			$ClassifiedStartDate = stripslashes($row["ClassifiedStartDate"]);
			if(!$ClassifiedStartDate){
				$ClassifiedStartDate= date("Y-m-d");
			}
			$ClassifiedEndDate = stripslashes($row["ClassifiedEndDate"]);
			$ClassifiedSeekOffer = stripslashes($row["ClassifiedSeekOffer"]);
			$ClassifiedContactAddress = stripslashes($row["ClassifiedContactAddress"]);
			$ClassifiedStatus = $row["ClassifiedStatus"];
				
			echo("<tr><td colspan=3 class='list_small'>Edit the details below and click 
			<input type=\"button\" name=\"save\" class=\"formcontrol\" value=\"Save &raquo;\" onclick=\"document.form.classifiedaction.value='save';SubmitContent(this);\" />
			<img src=\"Image/common/save.gif\" name=\"save\"  alt=\"save\" border=\"0\" width=\"23\" height=\"20\">
			or <input type=\"button\" name=\"delete\" class=\"formcontrol\" value=\"Delete\" onClick=\"document.form.classifiedaction.value='delete';DeleteContent(this);\">
			<img src=\"Image/common/clear.gif\" name=\"delete\"  alt=\"Delete\" border=\"0\" width=\"23\" height=\"20\" /> to remove.
		
			</td>
 		   	</tr>\n");
		}
	}
	
 	?>

 <tr>
      <td colspan=3 class='list_small'>
      
    <strong>Details</strong> (* required entries)<span class="table_stripe_even">
    
    </span></td>
  </tr>

	<tr>
	  <td valign="top" class='table_stripe_even'>Title (60) *</td>
	  <td class='table_stripe_even'><input name="ClassifiedTitle" type="text" class="formcontrol" id="ClassifiedTitle" value="<?php echo(htmlentities($ClassifiedTitle)); ?>" size="80" maxlength="60" /></td>
  </tr>
  <tr>
  	  <td valign="top" class='table_stripe_even'>Country location *</td>
	  <td class='table_stripe_even'><select class="formcontrol" name="ClassifiedLocation" id="ClassifiedLocation" >
        <option value="0">Choose a country</option>
        <?php
	$query = $db->getQuery(true)
		->select($db->qn(['iso', 'printable_name']))
		->from($db->qn('tblCountry'))
		->order($db->qn('printable_name'));
	$countries = $db->setQuery($query)->loadAssocList();
	foreach($countries as $row) {
		if($row["iso"]==$ClassifiedLocation){
			$olist.="<option value=\"".$row["iso"]."\" selected>".$row["printable_name"]."</option>\n";
		}else{
			$olist.="<option  value=\"".$row["iso"]."\">".$row["printable_name"]."</option>\n";
		}
	}
	echo($olist);
	?>
      </select></td>
  </tr>
  <tr>
  	  <td valign="top" class='table_stripe_even'>Section *</td>
	  <td class='table_stripe_even'>
    <select class="formcontrol" name="ClassifiedSection" id="ClassifiedSection" >
	<option value="0">Choose a section</option>
	<?php
	$olist="";
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblClassifiedSections'))
		->where($db->qn('Status').' = 1')
		->order($db->qn('ClassifiedSectionName'));
	$sections = $db->setQuery($query)->loadAssocList();
	foreach($sections as $row) {
		if($row["ClassifiedSectionID"]==$ClassifiedSection){
			$olist.="<option value=\"".$row["ClassifiedSectionID"]."\" selected>".$row["ClassifiedSectionName"]."</option>\n";
		}else{
			$olist.="<option  value=\"".$row["ClassifiedSectionID"]."\">".$row["ClassifiedSectionName"]."</option>\n";
		}
	}
	echo($olist);
	?> 
    </select>	</td>
  </tr>

			
  
  <tr>
<td valign="top" class='table_stripe_even'> Offered Wanted *</td>
<td class='table_stripe_even'>
	<?php
    
    $found = strstr ($ClassifiedSeekOffer, "w");
    if($found){
        $ClassifiedSeekOfferWChecked=" checked";
		$ClassifiedSeekOfferOChecked="";
    }else{
        $ClassifiedSeekOfferWChecked="";
        $ClassifiedSeekOfferOChecked=" checked";
    }
    ?>
    <input name="ClassifiedSeekOffer" type="radio" class="formcontrol" id="ClassifiedSeekOffer" value="o" <?php echo($ClassifiedSeekOfferOChecked); ?>/>
Offered 
<input name="ClassifiedSeekOffer" type="radio" class="formcontrol" id="ClassifiedSeekOffer" value="w" <?php echo($ClassifiedSeekOfferWChecked); ?>/> Wanted </td></tr>
    <tr>
<td valign="top" class='table_stripe_even'> Full description *<br />
  <em>(please DO NOT enter price and contact details here. Use the special boxes below)</em></td>
<td class='table_stripe_even'><textarea name="ClassifiedDescription" class="formtextarea" cols="86" rows="20">
<?php echo(htmlentities($ClassifiedDescription)); ?></textarea></td>
    </tr>

<tr>
  <td valign="top" class='table_stripe_even'>
Contact name *</td>
<td class='table_stripe_even'><input name="ClassifiedContactName" type="text" class="formcontrol" id="ClassifiedContactName" value="<?php echo(htmlentities($ClassifiedContactName)); ?>" size="50" maxlength="50" /></td></tr>
 
<tr>
  <td valign="top" class='table_stripe_even'>
Contact email *</td>
<td class='table_stripe_even'><input name="ClassifiedContactEmail" type="text" class="formcontrol" id="ClassifiedContactEmail" value="<?php echo(htmlentities($ClassifiedContactEmail)); ?>" size="50" maxlength="50" /></td></tr>

<tr><td valign="top" class='table_stripe_even'>
Contact telephone</td>
<td class='table_stripe_even'><input name="ClassifiedContactTel" type="text" class="formcontrol" id="ClassifiedContactTel" value="<?php echo(htmlentities($ClassifiedContactTel)); ?>" size="50" maxlength="50" /> 
  <em> include  country code</em></td>
</tr>

<tr>
<td valign="top" class='table_stripe_even'>
Website link </td>
<td class='table_stripe_even'><input name="ClassifiedWeblink" type="text" class="formcontrol" id="ClassifiedWeblink" value="<?php echo(htmlentities($ClassifiedWeblink)); ?>" size="100" maxlength="100" /></td>
</tr>

<tr><td valign="top" class='table_stripe_even'>
Price</td>
<td class='table_stripe_even'><input name="ClassifiedPrice" type="text" class="formcontrol" id="ClassifiedPrice" value="<?php echo($ClassifiedPrice); ?>" size="10" maxlength="20" onKeyPress="return(currencyFormat(this,'','.',event))"/>
<select class="formcontrol" name="ClassifiedPriceCurrency" id="ClassifiedPriceCurrency" >
	<?php
	$olist="";
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblClassifiedCurrency'));
	$currencies = $db->setQuery($query)->loadAssocList();
	foreach($currencies as $row) {
		if($row["Currency"]==$ClassifiedPriceCurrency){
			$olist.="<option value=\"".$row["Currency"]."\" selected>".$row["CurrencyDescription"]."</option>\n";
		}else{
			$olist.="<option  value=\"".$row["Currency"]."\">".$row["CurrencyDescription"]."</option>\n";
		}
	}
	echo($olist);
	?> 
  </select> </td>
</tr>


<tr>
<td valign="top" class='table_stripe_even'> Picture</td>
<td class='table_stripe_even'><?php
	 //check if there is an image
	 $imagepath="Image/classified/clips/".$ClassifiedID.".jpg";
	//add any images underneath 
 
	if (file_exists($imagepath)) {
		$imageInfo = getimagesize($imagepath);
		//$imwidth = $imageInfo[0];
		//$imheight = $imageInfo[1]; 
		$imwidth = 120;
		$imheight = $imageInfo[1]/($imageInfo[0]/120); 
		$mediatitle=$ClassifiedTitle;
		$imagedetail.="<img src=\"/".$imagepath."\" width=".$imwidth." height=".$imheight." border=0 title=\"".$mediatitle."\" alt=\"".$mediatitle."\">\n";  
		echo($imagedetail);
	}
	?>
     
     
     <input type="FILE" name="uploadfile" id="uploadfile" class="formcontrol" size="70" />
     
     <input type="hidden" name="MAX_FILE_SIZE" value="1000000">
     <a href='javascript:Help("/components/com_waterways_guide/views/classified/tmpl/help_upload_file.php")'><img src="Image/common/help.gif" width="20" height="20" alt="Help on uploading an image" border="0"></a></td>
    </tr>
    <tr>
      <td valign="top" class='table_stripe_even'>
Reference</td>
<td class='table_stripe_even'>

<?php 
if(!$ClassifiedRef){
	echo("To be added on submission");
}else{
	echo($ClassifiedRef);
}
?>
<input name="ClassifiedRef" type="hidden" id="ClassifiedRef" value="<?php echo($ClassifiedRef); ?>" /></td></tr>
    <tr>
      <td valign="top" class='table_stripe_even'>
Date first posted</td>
<td class='table_stripe_even'>
<?php 
if(!$ClassifiedDatePosted){
	echo("To be added on submission");
}else{
	echo(date("d M Y", strtotime($ClassifiedDatePosted)));
}
?>
<input name="ClassifiedDatePosted" type="hidden" id="ClassifiedDatePosted" value="<?php echo($ClassifiedDatePosted); ?>" /></td>
</tr>

<tr>
  <td valign="top" class='table_stripe_even'> Date start</td>
<td class='table_stripe_even'>
<?php 
if(!$ClassifiedStartDate){
	echo("To be added on approval");
}else{
	echo(date("d M Y", strtotime($ClassifiedStartDate)));
}
?>
<input name="ClassifiedStartDate" type="hidden" id="ClassifiedStartDate" value="<?php echo($ClassifiedStartDate); ?>" /></td>
</tr>

  <tr><td valign="top" class='table_stripe_even'>
Date end</td>
<td class='table_stripe_even'>
<?php 
if(!$ClassifiedEndDate){
	echo("To be added on approval");
}else{
	echo(date("d M Y", strtotime($ClassifiedEndDate)));
	//check for expiry and offer renewal options
	//86400second/day 2592000=30 days
	if(time()>(strtotime($ClassifiedEndDate)-2592000)){
		echo("<br><font color=ff0000><b>Your ad has already or is about to expire. If you would like to extend it, choose the number of days to extend and make any other changes to the details before clicking 'Save'.</b></font><b><br>Extend Date End by: <input name=\"extend\" type=\"radio\" value=\"0\" checked />0 <input name=\"extend\" type=\"radio\" value=\"30\">30 <input name=\"extend\" type=\"radio\" value=\"60\">60 <input name=\"extend\" type=\"radio\" value=\"90\">90 days");
	}
}
?>
<input name="ClassifiedEndDate" type="hidden" id="ClassifiedEndDate" value="<?php echo($ClassifiedEndDate); ?>" /></td>
</tr>

  <tr><td valign="top" class='table_stripe_even'>
Status</td>
<td class='table_stripe_even'>
<?php 
if($admin=="open"){
	//admin so allow a status dropdown
	${'MemTypeCode'.$ClassifiedStatus}=" selected";
	$olist="<select class=\"formcontrol\" name=\"ClassifiedStatus\" id=\"ClassifiedStatus\" >\n";
	$olist.="<option value=\"0\"".$MemTypeCode0.">Pending awaiting approval</option>\n";
	$olist.="<option value=\"1\"".$MemTypeCode1.">Live</option>\n";
	$olist.="<option value=\"5\"".$MemTypeCode5.">Reject</option>\n";
	$olist.="<option value=\"2\"".$MemTypeCode2.">Extend 90 days from end date</option>\n";
	$olist.="<option value=\"3\"".$MemTypeCode3.">Suspend</option>\n";
	$olist.="<option value=\"4\"".$MemTypeCode4.">Archive</option>\n";
	$olist.="</select>\n";
	echo($olist);
}else{
	if(!$ClassifiedStatus){
		echo("Pending");
	}else{
		switch ($ClassifiedStatus) {
			case 0:
				$ClassifiedStatusText="Pending awaiting approval";
				break;
			case 1:
				$ClassifiedStatusText="Live";
				break;
			case 5:
				$ClassifiedStatusText="Rejected";
				break;
			case 2:
				$ClassifiedStatusText="Extended";
				break;
			case 3:
				$ClassifiedStatusText="Suspended";
				break;
   			case 4:
				$ClassifiedStatusText="Archive";
				break;
		}
		
		echo($ClassifiedStatusText);
		echo("<input name=\"ClassifiedStatus\" type=\"hidden\" value=\"".$ClassifiedStatus."\" />\n");
	}
}

echo("<tr><td colspan=3 class='list_small'>Edit the details above and click 
	<input type=\"button\" name=\"save\" class=\"formcontrol\" value=\"Save &raquo;\" onclick=\"document.form.classifiedaction.value='save';SubmitContent(this);\" />
	<img src=\"Image/common/save.gif\" name=\"save\"  alt=\"save\" border=\"0\" width=\"23\" height=\"20\">
	or <input type=\"button\" name=\"delete\" class=\"formcontrol\" value=\"Delete\" onClick=\"document.form.classifiedaction.value='delete';DeleteContent(this);\">
	<img src=\"Image/common/clear.gif\" name=\"delete\"  alt=\"Delete\" border=\"0\" width=\"23\" height=\"20\" /> to remove.

	</td>
	</tr>\n");
?>            
<input name="ClassifiedID" type="hidden" value="<?php echo($ClassifiedID); ?>" />
<input name="ClassifiedMemberID" type="hidden" value="<?php echo($ClassifiedMemberID); ?>">
<input name="ClassifiedCurStatus" type="hidden" value="<?php echo($ClassifiedStatus); ?>"></td></tr>
 
<?php
}
?>
</table>
