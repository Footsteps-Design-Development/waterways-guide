
<?php
/**
 * @version     1.0.0
 * @package     com_membership Shop
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Chris Grant www.productif.co.uk
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

$app = Factory::getApplication('com_membership');

$db = Factory::getDBO();
require_once(JPATH_COMPONENT_SITE."/commonV3.php");


$test_vars=(array(
'docid',
'itemid',
'itemaction',
'infoid',
'ClientGroup',
'level',
'editpage',
'cat_items',
'thisrow',
'listresults',
'lastupdated',
'ItemShip',
'showclosed',
'login_postzone',
'viewcart1',
'catdesc',
'errmsg',
'section',
'ItemTitle',
'ItemCategory',
'ItemTitle',
'ItemDetails',
'ItemPriceList',
'ItemPriceDBA',
'ItemShipUK',
'ItemShipEU',
'ItemShipZ2',
'ItemWeblink',
'ItemImagePath',
'ItemDate',
'ItemPostingDate',
'ItemID',
'ItemContact',
'ItemShipZ1',
'ItemStatus',
'login_memberid',
'admin',
'login_country',
'UKchecked',
'EUchecked',
'Z1checked',
'Z2checked',
'login_email',
'login_FirstName',
'login_LastName',
'login_Address1',
'login_Address2',
'login_Address3',
'login_Address4',
'login_PostCode',
'thefile'

));
foreach($test_vars as $test_var) { 
	if(!$$test_var =  $app->input->getString($test_var)){
		$$test_var = "";
	}
}

//check access level of user
$user = Factory::getUser();
$componentpath="/components/com_membership/";

$userGroups = $user->getAuthorisedGroups();
if (in_array("8", $userGroups) || in_array("30", $userGroups)) {
    //superusers or bookshop group
	$membershipadmin=true;
}else{
    $membershipadmin=false;	
}

$userid = $user->id;
$memtable="tblMembers"; 
if($userid){
	//Need to get for main member
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn($memtable))
		->where($db->qn('ID').' = '.$db->q($userid));
	$row = $db->setQuery($query)->loadObject();
	$login_memberid=$userid;
	if($row) {
		$login_country=stripslashes($row->Country); 
		$login_email = stripslashes($row->Login);
		$login_FirstName = stripslashes($row->FirstName); 
		$login_LastName = stripslashes($row->LastName); 
		$login_Address1 = stripslashes($row->Address1); 
		$login_Address2 = stripslashes($row->Address2); 
		$login_Address3 = stripslashes($row->Address3); 
		$login_Address4 = stripslashes($row->Address4); 
		$login_PostCode = stripslashes($row->PostCode); 
		$login_postzone = $row->PostZone;
	}else{
		//can't find member details
	}
}

//over ride 19/08/2016 CJG to list all as shop now has been reduced
$cat_items="All";
if(!$itemaction){
	$itemaction="list";
}

?>
<style type="text/css" media="screen,projection">
.table_admin_profile{
	border-top: 1px dotted #333333;
	border-right: 1px dotted #333333;
	border-bottom: 1px dotted #333333;
	border-left: 1px dotted #333333;
}
.formcontrol {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 95%;
	font-weight: lighter;
}

.formtextarea {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 95%;
	font-weight: lighter;
	height:120px;
	width:100%;
}

.table_stripe_odd {background-color: #DDDDDD; color: #333333; font-size: 95%;} 
.table_stripe_odd a:link {color: #333333;}
.table_stripe_odd a:visited {color: #333333;}
.table_stripe_odd a:hover {color: #FAA637;}

.table_stripe_even {background-color: #CCEEF7; color: #333333; font-size: 95%;} 
.table_stripe_even a:link {color: #333333;}
.table_stripe_even a:visited {color: #333333;}
.table_stripe_even a:hover {color: #FAA637;}

.trailer {color: #333333; font-size: 95%; font-weight: normal; margin-left:6px; border-bottom: 8px solid #ffffff; border-top: 0px solid #cccccc; border-left: 0px solid #06A8D9; border-right: 0px solid #06A8D9;} 
.trailer a:link {color: #333333; text-decoration: none}
.trailer a:visited {color: #333333; text-decoration: none}
.trailer a:hover {color: #FAA637; text-decoration: none}

.guidechange{
	margin-top: 6px;
	padding: 3px;
	background: #ffff00;
	font-size: 95%;
} 
</style>

<h1>Shop - <?php echo($cat_items); ?></h1>
<form name="form" enctype="multipart/form-data"  method="post">
<table border="0" cellspacing="0" cellpadding="4" width="100%">
<?php
if($membershipadmin==true){

	$admin="open";
}


echo("<input name=\"docid\" type=\"hidden\" value=\"$docid\">\n");
echo("<input name=\"itemid\" type=\"hidden\" value=\"$itemid\">\n");
echo("<input name=\"itemaction\" type=\"hidden\" value=\"$itemaction\">\n");
echo("<input name=\"infoid\" type=\"hidden\" value=\"$infoid\">\n");


//echo("<input name=\"section\" type=\"hidden\" value=\"$section\">");
echo("<input name=\"MyClientGroup\" type=\"hidden\" value=\"$ClientGroup\">\n");

//---------------------------------------item save---------------------------------------------

if ($itemaction=="save") {
	echo("<input name=\"cat_items\" type=\"hidden\" value=\"$cat_items\">");
	echo("<input name=\"showclosed\" type=\"hidden\" value=\"$showclosed\">");

	$errmsg="";
	if(!$ItemTitle){
		$errmsg.="Item Title";
	}
	/*if(!$ItemContact){
		if($errmsg){
			$errmsg.=", ";
		}
		$errmsg.=" Item Contact";
	}*/
	/*if(!$ItemTime){
		if($errmsg){
			$errmsg.=", ";
		}

		$errmsg.=" Time";
	}*/
	/*if(!$ItemCost){
		if($errmsg){
			$errmsg.=", ";
		}
		$errmsg.=" Cost";
	}*/
	/*if(!$ItemWeblink){
		if($errmsg){
			$errmsg.=", ";
		}
		$errmsg.=" Web link";
	}
	if(!$ItemLocation){
		if($errmsg){
			$errmsg.=", ";
		}
		$errmsg.=" Location";
	}
	if(!$ItemDate){
		if($errmsg){
			$errmsg.=", ";
		}
		$errmsg.=" Date";
	}
	if(!$ItemPostingDate){
		if($errmsg){
			//$errmsg.=", ";
		}
		//$errmsg.=" Date posted";
	}*/
	if(!$ItemDetails){
		if($errmsg){
			$errmsg.=", ";
		}
		$errmsg.=" Details";
	}
	if($ItemCategory=="0"){
		if($errmsg){
			$errmsg.=", ";
		}
		$errmsg.=" Category";
	}
	if($errmsg){
		$errmsg="Please check ".$errmsg;
		$itemaction="edit";
	}else{
		//entry OK so update
		
		
	
		$updates=0;
		$ItemUpdate=date("Y-m-d H:i:s");
		$ItemTime=date("Y-m-d H:i:s");
		$updatetext="";
		$subject="Items";
		if($infoid=="new"){
			//add new
			$status=1;
			$newby=1;
			$ItemStatus=1;
			$insert = new stdClass();
			$insert->ItemTitle = $ItemTitle;
			$insert->ItemContact = $ItemContact;
			$insert->ItemTime = $ItemTime;
			$insert->ItemPriceList = $ItemPriceList;
			$insert->ItemPriceDBA = $ItemPriceDBA;
			$insert->ItemShipUK = $ItemShipUK;
			$insert->ItemShipEU = $ItemShipEU;
			$insert->ItemShipZ1 = $ItemShipZ1;
			$insert->ItemShipZ2 = $ItemShipZ2;
			$insert->ItemWeblink = $ItemWeblink;
			$insert->ItemImagePath = $ItemImagePath;
			$insert->ItemDate = $ItemDate;
			$insert->ItemPostingDate = $ItemPostingDate;
			$insert->ItemDetails = $ItemDetails;
			$insert->ItemCategory = $ItemCategory;
			$insert->ItemUpdate = $ItemUpdate;
			$insert->ItemStatus = $ItemStatus;
			$result = $db->insertObject('tblShop', $insert, 'ItemID');
			if(!$result){die ("Couldn't update database ".print_r($insert, true));}
			$changelogtext="Item  - '".$ItemTitle."' added.";
			$updates=1;
			$infoid = $insert->ItemID;
		}elseif($infoid>0){
			$status=1;
			$update = new \stdClass();
			$update->ItemTitle = $ItemTitle;
			$update->ItemContact = $ItemContact;
			$update->ItemTime = $ItemTime;
			$update->ItemPriceList = $ItemPriceList;
			$update->ItemPriceDBA = $ItemPriceDBA;
			$update->ItemShipUK = $ItemShipUK;
			$update->ItemShipEU = $ItemShipEU;
			$update->ItemShipZ1 = $ItemShipZ1;
			$update->ItemShipZ2 = $ItemShipZ2;
			$update->ItemWeblink = $ItemWeblink;
			$update->ItemImagePath = $ItemImagePath;
			$update->ItemDate = $ItemDate;
			$update->ItemPostingDate = $ItemPostingDate;
			$update-> ItemDetails = $ItemDetails;
			$update->ItemCategory = $ItemCategory;
			$update->ItemUpdate = $ItemUpdate;
			$update->ItemStatus = $ItemStatus;
			$update->ItemID = $infoid;
			$result = $db->updateObject('tblShop', $update, 'ItemID');
			if(!$result){
				echo("Couldn't update item $sql");
			}else{
				$updates+=1;
				$changelogtext="Item - '".$ItemTitle."' updated";
			}
			$updates=1;
		}


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
	
	
		if ($_FILES['thefile']['error'] === UPLOAD_ERR_OK){
			// upload ok
			//variables . . . . . . . . . . . . . . 
			$the_imagepath="Image/shop";
			$image_resize_width=200;
			$imagepercent="";
			$the_clippath=""; 
			$clip_width=20;
			$error="";
			$allowedExts = array("jpg", "jpeg");
			//the file name will be the asset id
			$myfilename=$infoid.".jpg";
	
					
			# validate the file $the_file		
			$thefiletmp =$_FILES["thefile"]["tmp_name"];
			$thefilename =$_FILES["thefile"]["name"];
			$thefiletype =$_FILES["thefile"]["type"];
			$thefilesize =$_FILES["thefile"]["size"];
	
			# check if we are allowed to upload this file_type	
		
			$extension = strtolower(end(explode(".", $thefilename)));
			if ((($thefiletype == "image/jpeg")
				|| ($thefiletype == "image/pjpeg"))
				&& in_array($extension, $allowedExts))   {
	
				//its an allowed image
				$size = GetImageSize($thefiletmp);
				list($foo,$width,$bar,$height) = explode("\"",$size[3]);		
				if (file_exists($the_imagepath . "/" . $myfilename)) {
					//delete old version if valid
					unlink ($the_imagepath . "/" . $myfilename);
					//$statusmessage.="<br>$the_imagepath / $the_file_name has been deleted\n";
				}
				if (!@copy($thefiletmp, $the_imagepath . "/" . $myfilename)) {
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
				//update image path
				$ItemImagePath=$the_imagepath . "/" . $myfilename;
				$update = new \stdClass();
				$update->ItemImagePath = $ItemImagePath;
				$update->ItemDate = $ItemDate;
				$update->ItemID = $infoid;
				$db->updateObject('tblShop', $update, 'ItemID');
			}else{
				$statusmessage.="<br>The image $thefiletype was not of an allowed type\n"; 
			}
		}else{
			if($_FILES['thefile']['error'] === UPLOAD_ERR_NO_FILE){
				$statusmessage.="<br>No image uploaded"; 
			}else{
				$statusmessage.="<br>There was an error uploading the image: " . file_upload_error_message($_FILES['thefile']['error']); 
			}
		}
		//end of image upload



		if($updates>0){
 
			//update log
			$updatetext.=$changelogtext."<br>";
			$insert = new \stdClass();
			$insert->MemberID = $login_memberid;
			$insert->Subject = $subject;
			$insert->ChangeDesc = $changelogtext;
			$insert->ChangeDate = $ItemUpdate;
			$update = $db->insertObject('tblChangeLog', $insert);
			if(!$update){
				echo("Couldn't update changes");
			}else{
				echo("<tr><td>The change history log and site have been updated with the following details:</td></tr>\n");
				echo("<tr><td>".$updatetext."</td></tr>\n");
			}
			echo("<tr><td><a href=\"#\" onClick=\"document.form.itemaction.value='list';document.form.submit()\">Back to the list <img src=\"Image/common/back1.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Back to the list of items\"></a></td></tr>\n");
		}
	}
}

//---------------------------------------item remove---------------------------------------------

if ($itemaction=="remove") {
	echo("<input name=\"cat_items\" type=\"hidden\" value=\"$cat_items\">");
	echo("<input name=\"showclosed\" type=\"hidden\" value=\"$showclosed\">");

	//get details for log before deleting
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblShop'))
		->where($db->qn('ItemID').' = '.$db->q($infoid));
	$result = $db->setQuery($query)->loadAssocList();
	$num_rows = count($result);
	if (!$num_rows) {
		echo("<tr><td class=bodytext>Sorry - no details available for this item</td></tr>"); 
		exit();
	}
	$row = reset($result);
	$ItemTitle = stripslashes($row["ItemTitle"]);
	$ItemContact = stripslashes($row["ItemContact"]);

	$updates=0;
	$changedate=date("Y-m-d H:i:s");
	$updatetext="";
	$subject="Items";
	$query = $db->getQuery(true)
		->delete($db->qn('tblShop'))
		->where($db->qn('ItemID').' = '.$db->q($infoid));
	$update = $db->setQuery($query)->execute();
	if(!$update){
		echo("Couldn't delete entry");
	}else{
		$changelogtext="Item - '".$ItemTitle."'(".$ItemContact.") removed";			
	}

	$updates=1;
	if($updates>0){
		//update log
		$updatetext.=$changelogtext."<br>";
		$insert = new \stdClass();
		$insert->MemberID = $login_memberid;
		$insert->Subject = $subject;
		$insert->ChangeDesc = $changelogtext;
		$insert->ChangeDate = $changedate;
		$update = $db->insertObject('tblChangeLog', $insert);
		if(!$update){
			echo("Couldn't update changes");
		}else{
			echo("<tr><td>The change history log and site have been updated with the following details:</td></tr>\n");
		}	
		echo("<tr><td>".$updatetext."</td></tr>\n");
		echo("<tr><td><a href=\"#\" onClick=\"document.form.itemaction.value='list';document.form.submit()\">Back to the list <img src=\"Image/common/back1.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Back to the list\"></a></td></tr>\n");
	}
}



//---------------------------------------List items---------------------------------------------

if ($itemaction=="" || $itemaction=="list") {
		
	//get current categories
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblShop_categories'))
		->where($db->qn('status').' = 1')
		->order($db->qn('CatDesc'));
	$categories = $db->setQuery($query)->loadAssocList();
	echo "<tr><td colspan=2 class=bodytext>$shopintro</td></tr>\n";
	echo("<input name=\"catdesc\" type=\"hidden\" value=\"$catdesc\">");


	if($cat_items){
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('tblShop'))
			->order($db->qn('ItemTitle'));
		if($admin != "open") $query->where($db->qn('ItemStatus').' = 1'); //only show live
		if($cat_items != "All" && $cat_items) $query->where($db->qn('ItemCategory').' = '.$db->q($cat_items));
		$items = $db->setQuery($query)->loadAssocList();
		$rows = count($items);
		
		# If the search was unsuccessful then Display Message try again.
		If ($rows == 0){
			PRINT "<tr><td colspan=2>Sorry - there are no items at the moment.</td></tr>\n";
		}else{
			foreach($items as $row) {
				# Display item Results, l
			
				$itemmatch=0;
	
				$ItemID = stripslashes($row["ItemID"]);
				$ItemTitle = stripslashes($row["ItemTitle"]);
				$ItemContact = stripslashes($row["ItemContact"]);
				$ItemTime = stripslashes($row["ItemTime"]);
				$ItemWeblink = stripslashes($row["ItemWeblink"]);
				$ItemDate = stripslashes($row["ItemDate"]);
				$ItemPostingDate = stripslashes($row["ItemPostingDate"]);
				$ItemDetails = stripslashes($row["ItemDetails"]);
				$ItemCategory = stripslashes($row["ItemCategory"]);
				$ItemUpdate = stripslashes($row["ItemUpdate"]);
				$ItemStatus = stripslashes($row["ItemStatus"]);
				$item_number="DBA".sprintf("%06d",$ItemID);
				$msgtrail=substr($row["ItemDetails"], 0, 120)." . . . . . . .";
				$itemmatch=1;
				if($thisrow=="odd"){
					$rowclass="table_stripe_even";
					$thisrow="even";
				}else{
					$rowclass="table_stripe_odd";		
					$thisrow="odd";
				}
				$rowclass="table_stripe_odd";
				if($ItemStatus==0){
					$rowclass.="_strike";
				}
				if($admin=="open"){
					$adminlink="$item_number <a href=\"#\" onClick=\"document.form.itemaction.value='edit';document.form.infoid.value='$ItemID';document.form.submit()\"><img src=\"Image/common/open.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Edit this entry\"></a>";
				}else{
					$adminlink="$item_number";
				}
				$listresults.="<tr><td class=$rowclass><a href=\"#\" onClick=\"document.form.itemaction.value='detail';document.form.itemid.value='$ItemID';document.form.submit()\">$ItemTitle</a></td><td class=$rowclass></td><td class=$rowclass>$adminlink</td></tr>\n";
				$listresults.="<tr><td class=trailer colspan=2><a href=\"#\" onClick=\"document.form.itemaction.value='detail';document.form.itemid.value='$ItemID';document.form.submit()\">".$msgtrail."</a></td><td></td></tr>\n";
			}
			if($itemmatch==1){
				
				PRINT "<tr><td colspan=2 class=bodytext><b>Current list</b> - <i>Click on a title for details</i></td></tr>\n";
				PRINT $listresults."\n";
			}else{
				PRINT "<tr><td class=bodytext colspan=2>Sorry - there are no items in that category at the moment.</td></tr>\n";		
			}
		}
		if($admin=="open"){
			if($showclosed=="1"){
				$showclosedselected=" checked";		
			}else{
				$showclosedselected="";
			}
	
			echo("<tr><td class=table_stripe_even colspan=2><b>Administrators:</b> Click the <img src=\"Image/common/open.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Edit this entry\"></a> to the right of the row to edit the entry or <a href=\"#\" onClick=\"document.form.itemaction.value='edit';document.form.infoid.value='new';document.form.submit()\"> here <img src=\"Image/common/new.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Add a new entry\"></a> to add a new entry.\n");
			echo(" Show closed items <a href=\"#\" onClick=\"document.form.itemaction.value='list';document.form.submit()\"><input name=\"showclosed\" type=\"checkbox\" value=\"1\" $showclosedselected></a> 
			</td></tr>\n");
		}
	}else{
		//print"<tr><td colspan=2>Choose a Category from the list above</td></tr>\n";
	}
}
//---------------------------------------item details---------------------------------------------

elseif ($itemaction=="detail") {
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblShop'))
		->where($db->qn('ItemID').' = '.$db->q($itemid));
	$result = $db->setQuery($query)->loadAssocList();
	$num_rows = count($result);
	
	# If the search was unsuccessful then Display Message try again.
	if (!$num_rows) {
		echo("<tr><td class=bodytext>Sorry - no details available for this item<br><hr></td></tr>"); 
		exit();
	}

	$datenow = time();
	$row = reset($result);
	$ItemStatus = stripslashes($row["ItemStatus"]);
	if($ItemStatus ==1){
		//Item live so display
		$ItemID = stripslashes($row["ItemID"]);
		$ItemTitle = stripslashes($row["ItemTitle"]);
		$ItemPriceList = stripslashes($row["ItemPriceList"]);
		$ItemPriceDBA = stripslashes($row["ItemPriceDBA"]);
		$ItemShipUK = stripslashes($row["ItemShipUK"]);
		$ItemShipEU = stripslashes($row["ItemShipEU"]);
		$ItemShipZ1 = stripslashes($row["ItemShipZ1"]);
		$ItemShipZ2 = stripslashes($row["ItemShipZ2"]);
		$ItemImagePath = stripslashes($row["ItemImagePath"]);
		$ItemContact = stripslashes($row["ItemContact"]);
		$ItemTime = stripslashes($row["ItemTime"]);
		$ItemWeblink = stripslashes($row["ItemWeblink"]);
		$ItemDate = stripslashes($row["ItemDate"]);
		$ItemPostingDate = stripslashes($row["ItemPostingDate"]);
		$ItemDetails = stripslashes($row["ItemDetails"]);
		$ItemCategory = stripslashes($row["ItemCategory"]);
		$ItemUpdate = stripslashes($row["ItemUpdate"]);
		$item_number="DBA".sprintf("%06d",$ItemID);
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('tblShop_categories'))
			->where($db->qn('CatID').' = '.$db->q($ItemCategory));
		$catrow = $db->setQuery($query)->loadAssoc();
		$thisitemenquiry="Reference: ".$ItemContact." - ".$ItemTitle;
	
		//$Requirements = stripslashes(nl2br($row["Requirements"]));
		
		if($userid){
			//member logged in
			//$shippinginfo="Delivery to your membership address in $login_country $login_postzone
			//$login_country
			$amount=$ItemPriceDBA;
			$item_name=$ItemTitle;
			if($ItemPriceList==$ItemPriceDBA){
				//no discount
				$ItemCost="GBP ".sprintf("%.2f",$ItemPriceList);
			}else{
				$ItemCost="GBP ".sprintf("%.2f",$ItemPriceDBA)." - on this item as a member you have saved GBP ".sprintf("%.2f",$ItemPriceList-$ItemPriceDBA);
			}
			$shippingcost=${"ItemShip".$login_postzone};
			$ItemShip="GBP ".sprintf("%.2f",$shippingcost)." to your registered address in ".$login_country." or select an alternative delivery zone.";
			if($login_postzone=="UK"){
				$UKchecked=" checked";
				$EUchecked="";
				$Z1checked="";
				$Z2checked="";
			}elseif($login_postzone=="EU"){
				$UKchecked="";
				$EUchecked=" checked";
				$Z1checked="";
				$Z2checked="";
			}elseif($login_postzone=="Z1"){
				$UKchecked="";
				$EUchecked="";
				$Z1checked=" checked";
				$Z2checked="";
			}elseif($login_postzone=="Z2"){
				$UKchecked=" checked";
				$EUchecked="";
				$Z1checked="";
				$Z2checked=" checked";
			}
			$ItemShip.="<br /><input name=\"shipping\" type=\"radio\" value=\"".$ItemShipUK."\"".$UKchecked.">UK GBP ".sprintf("%.2f",$ItemShipUK);
			$ItemShip.="<br /><input name=\"shipping\" type=\"radio\" value=\"".$ItemShipEU."\"".$EUchecked.">Eurozone outside UK GBP ".sprintf("%.2f",$ItemShipEU);
			$ItemShip.="<br /><input name=\"shipping\" type=\"radio\" value=\"".$ItemShipZ1."\"".$Z1checked.">Zone 1 GBP ".sprintf("%.2f",$ItemShipZ1);
			$ItemShip.="<br /><input name=\"shipping\" type=\"radio\" value=\"".$ItemShipZ2."\"".$Z2checked.">Zone 2 GBP ".sprintf("%.2f",$ItemShipZ2);
	
			//$ItemPrice=ItemPriceDBA
		}else{
			//list price
			$amount=$ItemPriceList;
			$item_name=$ItemTitle;
			if($ItemPriceList==$ItemPriceDBA){
				//no discount
				$ItemCost="GBP ".sprintf("%.2f",$ItemPriceList);
			}else{
				$ItemCost="GBP ".sprintf("%.2f",$ItemPriceList)." - on this item <a href=\"".$memberloginurl."\">members can login to save</a> GBP ".sprintf("%.2f",$ItemPriceList-$ItemPriceDBA);
			}
	
	
			//$shippingcost=${"ItemShip".$login_postzone};
			$ItemShip.="<br /><input name=\"shipping\" type=\"radio\" value=\"".$ItemShipUK."\" checked>UK GBP ".sprintf("%.2f",$ItemShipUK);
			$ItemShip.="<br /><input name=\"shipping\" type=\"radio\" value=\"".$ItemShipEU."\">Eurozone outside UK GBP ".sprintf("%.2f",$ItemShipEU);
			$ItemShip.="<br /><input name=\"shipping\" type=\"radio\" value=\"".$ItemShipZ1."\">Zone 1 GBP ".sprintf("%.2f",$ItemShipZ1);

			$ItemShip.="<br /><input name=\"shipping\" type=\"radio\" value=\"".$ItemShipZ2."\">Zone 2 GBP ".sprintf("%.2f",$ItemShipZ2);


	
		}
		
		echo("<tr><td class='bodytext'><a href=\"#\" onClick=\"document.form.itemaction.value='list';document.form.submit()\">Back to the list <img src=\"Image/common/back1.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Back to the list\"></a></td></tr>\n");
		echo("<input name=\"cat_items\" type=\"hidden\" value=\"$cat_items\">\n<input name=\"showclosed\" type=\"hidden\" value=\"$showclosed\">\n");
		echo("<form></form>\n");	
	
		/*$buy="<form name=\"paypal\" id=\"paypal\" target=\"paypal\" action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\">\n";
		//$buy="<form name=\"paypal\" id=\"paypal\" action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\">\n";
		//$buy.="<input type=\"hidden\" name=\"shopping_url\" value=\"http://www.barges.org/members/shop/browse?itemaction=list&cat_items=".$cat_items."\">\n";
		$buy.="<input type=\"hidden\" name=\"add\" value=\"1\">\n";
		$buy.="<input type=\"hidden\" name=\"cmd\" value=\"_cart\">\n";
		$buy.="<input type=\"hidden\" name=\"business\" value=\"treasurer@barges.org\">\n";
		$buy.="<input type=\"hidden\" name=\"item_name\" value=\"".$item_name."\">\n";
		$buy.="<input type=\"hidden\" name=\"item_number\" value=\"".$item_number."\">\n";
		$buy.="<input type=\"hidden\" name=\"amount\" value=\"".$amount."\">\n";
		$buy.="<input type=\"hidden\" name=\"no_note\" value=\"0\">\n";
		$buy.="<input type=\"hidden\" name=\"currency_code\" value=\"GBP \">\n";
		$buy.="<input type=\"hidden\" name=\"lc\" value=\"GB\">\n";
	
	
	
		//$buy.="<input type="hidden" name="cmd" value="_xclick">
		$buy.="<input type=\"hidden\" name=\"no_shipping\" value=\"2\">\n";
		//$buy.="<input type=\"hidden\" name=\"shipping\" value=\"".$shippingcost."\">\n";
	
		if($userid){
			//member fields
	
			$buy.="<input type=\"hidden\" name=\"return\" value=\"".$paypalthanks."\">\n";
			$buy.="<input type=\"hidden\" name=\"cancel_return\" value=\"".$paypalcancel."\">\n";
			$buy.="<input type=\"hidden\" name=\"notify_url\" value=\"".$paypalreturn."\">\n";
	
			$buy.="<input type=\"hidden\" name=\"custom\" value=\"".$login_memberid."\">\n";
			$buy.="<input type=\"hidden\" name=\"email\" value=\"".$login_email."\">\n";
			$buy.="<input type=\"hidden\" name=\"first_name\" value=\"".$login_FirstName."\">\n";
			$buy.="<input type=\"hidden\" name=\"last_name\" value=\"".$login_LastName."\">\n";
			$buy.="<input type=\"hidden\" name=\"address1\" value=\"".$login_Address1."\">\n";
			$buy.="<input type=\"hidden\" name=\"address2\" value=\"".$login_Address2."\">\n";
			$buy.="<input type=\"hidden\" name=\"city\" value=\"".$login_Address3."\">\n";
			$buy.="<input type=\"hidden\" name=\"state\" value=\"".$login_Address4."\">\n";
			$buy.="<input type=\"hidden\" name=\"zip\" value=\"".$login_PostCode."\">\n";
		}
		//$buy.="<input type=\"hidden\" name=\"test_ipn\" value=\"1\">\n";
	
		//<input type=button onClick=\"document.form.itemsection.value='list';document.form.submit()\" value=\"Back\"><br><br>\n";
		echo($buy);*/
		$listresults="<form target=\"paypal\" action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\" >";
		$listresults.="<table border=\"0\" cellpadding=\"0\" cellspacing=\"5\">\n";
		
	
		$listresults.="<tr valign='top'><td class='bodytext'></td><td colspan=2 class='bodytext'>".$viewcart1."</td></tr>\n";
		
		//$listresults.="<tr valign='top'><td class='bodytext' colspan=2><b>".$thisitemenquiry."</b></td></tr>";
		$listresults.="<tr valign='top'><td class='bodytext'><b>Item:</b></td><td colspan=2 class='bodytext'>".$ItemTitle."</td></tr>\n";
		$listresults.="<tr valign='top'><td class='bodytext'><b>Order Code:</b></td><td colspan=2 class='bodytext'>".$item_number."</td></tr>\n";

		if($ItemImagePath){
			$listresults.="<tr valign='top'><td class='bodytext'><b>Details:</b></td><td class='bodytext'>".nl2br($ItemDetails)."</td></td><td class='bodytext'><img src=\"".$ItemImagePath."\" alt=\"".$ItemTitle."\"></td></tr>\n";
		}else{
			$listresults.="<tr valign='top'><td class='bodytext'><b>Details:</b></td><td colspan=2 class='bodytext'>".nl2br($ItemDetails)."</td></tr>\n";
		}
	
		if($ItemContact){
			$listresults.="<tr valign='top'><td class='bodytext'><b>Reference:</b></td><td colspan=2 class='bodytext'>".$ItemContact."</td></tr>\n";
		}
		if($ItemWeblink){
			$listresults.="<tr valign='top'><td class='bodytext'><b>Web link:</b></td ><td colspan=2 class='bodytext'>".$ItemWeblink."</td></tr>\n";
		}
	
		$listresults.="<tr valign='top'><td class='bodytext'><b>Price:</b></td><td colspan=2 class='bodytext'>".$ItemCost."</td></tr>\n";
		
		
			
		$listresults.="<tr valign='top'><td class='bodytext'><b>Shipping:</b></td><td colspan=2 class='bodytext'>".$ItemShip."</td></tr>\n";
	
		//$listresults.="<tr valign='top'><td class='bodytext'><b>Web link:</b></td><td class='bodytext'>".$ItemWeblink."</td></tr>\n";
		//PRINT "<tr valign='top'><td class='bodytext'><b>Duties:</b></td><td class='bodytext'>".$Duties."</td></tr>";
		//$listresults.="<tr valign='top'><td class='bodytext'><b>Date posted:</b></td><td class='bodytext'>".$ItemPostingDatedisplay."</td></tr>\n";
	
		
		//make buy link
			
		//$buylink="<a href=\"#\" onClick=\"document.paypal.submit()\"><img src=\"Image/shop/btn_addtobasket.gif\" width=\"144\" height=\"24\" border=\"0\" alt=\"Add to basket\"></a>";
	
		//$listresults.="<tr valign='top'><td class='bodytext'><b>Buy</b></td><td colspan=2 class='bodytext'>".$buylink."</td></tr>\n";
		//$listresults.="<tr valign='top'><td class='bodytext'><b>Category:</b></td><td colspan=2 class='bodytext'>".$ItemCategory."</td></tr>\n";
	
		$listresults.="</table><br><br>\n";
		
		
		echo($listresults);
		
		
		?>
		
		
		<input type="hidden" name="cmd" value="_cart">
		<input type="hidden" name="business" value="treasurer@barges.org">
		<input type="hidden" name="lc" value="GB">
		<input type="hidden" name="item_name" value="<?php echo($item_name); ?>">
		<input type="hidden" name="item_number" value="<?php echo($item_number); ?>">
		<input type="hidden" name="amount" value="<?php echo($amount); ?>">
		<input type="hidden" name="amount" value="<?php echo($amount); ?>">
		<input type="hidden" name="currency_code" value="GBP">
		<input type="hidden" name="button_subtype" value="products">
		<input type="hidden" name="no_note" value="0">
		<input type="hidden" name="add" value="1">
		
		<input type="hidden" name="bn" value="PP-ShopCartBF:btn_cart_LG.gif:NonHostedGuest">
		<input type="image" src="Image/shop/btn_addtobasket.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online!">
		
		<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
		</form>

		<?php
		if($userid){
			//member fields
			
			
	
			$buy.="<input type=\"hidden\" name=\"return\" value=\"".$paypalthanks."\">\n";
			$buy.="<input type=\"hidden\" name=\"cancel_return\" value=\"".$paypalcancel."\">\n";
			$buy.="<input type=\"hidden\" name=\"notify_url\" value=\"".$paypalreturn."\">\n";
	
			$buy.="<input type=\"hidden\" name=\"custom\" value=\"".$login_memberid."\">\n";
			$buy.="<input type=\"hidden\" name=\"email\" value=\"".$login_email."\">\n";
			$buy.="<input type=\"hidden\" name=\"first_name\" value=\"".$login_FirstName."\">\n";
			$buy.="<input type=\"hidden\" name=\"last_name\" value=\"".$login_LastName."\">\n";
			$buy.="<input type=\"hidden\" name=\"address1\" value=\"".$login_Address1."\">\n";
			$buy.="<input type=\"hidden\" name=\"address2\" value=\"".$login_Address2."\">\n";
			$buy.="<input type=\"hidden\" name=\"city\" value=\"".$login_Address3."\">\n";
			$buy.="<input type=\"hidden\" name=\"state\" value=\"".$login_Address4."\">\n";
			$buy.="<input type=\"hidden\" name=\"zip\" value=\"".$login_PostCode."\">\n";
			echo($buy);
			
			
		}
		
		
		
		//echo("</form>\n");
		//echo("<tr><td><a href=\"#\" onClick=\"document.form.itemaction.value='memberinfo';document.form.infoid.value='".$memberid."';document.form.submit()\">More information on the organisation posting this item here <img src=\"Image/common/icon_profile.gif\" width=\"59\" height=\"18\" border=\"0\" alt=\"More information on the organisation posting this item here \"></a></td></tr>\n");
	}else{
		//Item pending
		echo("<tr><td class='bodytext'><a href=\"#\" onClick=\"document.form.itemaction.value='list';document.form.submit()\">Back to the list <img src=\"Image/common/back1.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Back to the list\"></a></td></tr>\n");
		echo("<tr><td class='bodytext'>Sorry - This item is currently unavailable</td></tr>\n");
	}
	

}

//---------------------------------------Update---------------------------------------------

if ($itemaction=="update") {

	echo("<tr><td class=content_introduction>Update item entries</td></tr>\n");

	echo("<table class=bodytext cellpadding=\"10\" cellspacing=\"0\" border=\"0\">");
	echo("<tr><td>You can add new items from here by clicking on 'Add' 
            against 'New' below. To modify an existing entry (if there are any) click 'Edit' 
            in the ACTION column beside the title. Entries will cease to appear in the main list seven days after the expiry date
			but will remain in this list below until they are deleted.<br>");
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblShop'))
		->where($db->qn('ItemStatus').' = 1')
		->order($db->qn('ItemDate'));
	$items = $db->setQuery($query)->loadAssocList();
	$itemlist="<table border=0 cellspacing=1 cellpadding=3><tr><td class=bodytext><b>ITEM</b></td><td colspan=2 class=bodytext><b>ACTION</b></td></tr>\n";
	$itemlist.="<tr><td class=profile>New</td><td class=profile><a href=\"#\" onClick=\"document.form.itemaction.value='edit';document.form.infoid.value='new';document.form.submit()\">Add</a></td><td></td></tr>\n";

	foreach($items as $row) {
		$ItemID = stripslashes($row["ItemID"]);
		$ItemTitle = stripslashes($row["ItemTitle"]);
			
		//$itemlist.="<tr><td class=profile>".$row["headline"]."</td><td class=profile><a href=\"#\" onClick=\"document.form.itemaction.value='edit';document.form.infoid.value='$itemid';document.form.submit()\">Edit</a></td><td class=profile><a href=\"#\" onClick=\"document.form.itemaction.value='remove';document.form.infoid.value='$itemid';document.form.submit()\">Delete</a></td></tr>\n";
		$itemlist.="<tr><td class=profile>".$ItemTitle."</td><td class=profile><a href=\"#\" onClick=\"document.form.itemaction.value='edit';document.form.infoid.value='$ItemID';document.form.submit()\">Edit</a></td><td class=profile></td></tr>\n";
	
	}
	$itemlist.="</table>\n";			
	echo($itemlist);
	
	echo("<input name=\"cat_items\" type=\"hidden\" value=\"$cat_items\">");	
	$returnpath="<a href=\"#\" onClick=\"document.form.itemaction.value='list';document.form.submit()\">Back to the list <img src=\"Image/common/back1.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Back to the list\"></a>";

}
//---------------------------------------edit or add new---------------------------------------------

if ($itemaction=="edit") {
	echo("<script language=\"JavaScript\" src=\"pick.js\" type=\"text/javascript\"></script>\n");
	
	echo("<tr><td class=content_introduction><b>Edit entry</b></td></tr>\n");
	echo("<tr><td class='bodytext'><a href=\"#\" onClick=\"document.form.itemaction.value='list';document.form.submit()\">Back to the list <img src=\"Image/common/back1.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Back to the list of notices\"></a></td></tr>\n");
	echo("<input name=\"cat_items\" type=\"hidden\" value=\"$cat_items\">");
	echo("<input name=\"showclosed\" type=\"hidden\" value=\"$showclosed\">");
	if($errmsg){
		echo("<tr><td><font color=ff0000><b>$errmsg</b></font></td></tr>\n");
	}
	if($infoid=="new"){
		echo("<tr><td>Enter the details below and click 
		<a href=\"#\" onClick=\"document.form.itemaction.value='save';document.form.submit()\"> here <img src=\"Image/common/save.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Save this entry\"></a>
		to save.</td></tr>\n");
	}else{
		if(!$errmsg){
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('tblShop'))
				->where($db->qn('ItemID').' = '.$db->q($infoid));
			$row = $db->setQuery($query)->loadAssoc();
			$ItemID = stripslashes($row["ItemID"]);
			$ItemTitle = stripslashes($row["ItemTitle"]);
			$ItemPriceList = stripslashes($row["ItemPriceList"]);
			$ItemPriceDBA = stripslashes($row["ItemPriceDBA"]);
			$ItemShipUK = stripslashes($row["ItemShipUK"]);
			$ItemShipEU = stripslashes($row["ItemShipEU"]);
			$ItemShipZ1 = stripslashes($row["ItemShipZ1"]);
			$ItemShipZ2 = stripslashes($row["ItemShipZ2"]);
			$ItemImagePath = stripslashes($row["ItemImagePath"]);
			$ItemContact = stripslashes($row["ItemContact"]);
			$ItemTime = stripslashes($row["ItemTime"]);
			$ItemWeblink = stripslashes($row["ItemWeblink"]);
			$ItemDate = stripslashes($row["ItemDate"]);
			$ItemPostingDate = stripslashes($row["ItemPostingDate"]);
			$ItemDetails = stripslashes($row["ItemDetails"]);
			$ItemCategory = stripslashes($row["ItemCategory"]);
			$ItemUpdate = stripslashes($row["ItemUpdate"]);
			$ItemStatus = stripslashes($row["ItemStatus"]);
			if($ItemStatus==1){
				$statustext="live";
			}else{
				$statustext="pending";
			}
		}

		echo("<tr><td>Change the details below and click 
		<a href=\"#\" onClick=\"document.form.itemaction.value='save';document.form.submit()\"> here <img src=\"Image/common/save.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Save this entry\"></a>
		to save or <a href=\"#\" onClick=\"document.form.itemaction.value='remove';document.form.submit()\"> here <img src=\"Image/common/clear.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Remove this entry\"></a> to remove</td></tr>\n");
	}
	//get current categories
	echo("<tr><td>Category<br><select name=\"ItemCategory\" class=\"formcontrol\">\n"); 
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblShop_categories'))
		->order($db->qn('CatDesc'));
	$categories = $db->setQuery($query)->loadAssocList();
	foreach($categories as $catrow) {
		if($catrow["CatDesc"]==$ItemCategory){
			print"<option  value=\"".$catrow["CatDesc"]."\" selected>".$catrow["CatDesc"]."</option>\n";
		}else{
			print"<option  value=\"".$catrow["CatDesc"]."\">".$catrow["CatDesc"]."</option>\n";
		}
	}
	echo("</select></td></tr>\n");
	echo("<tr><td>Item Title<br><input type=\"text\" class=\"formcontrol\" name=\"ItemTitle\" size=\"60\" value=\"".$ItemTitle."\"></td></tr>\n
	<tr><td>Details<br><textarea name=\"ItemDetails\" class=\"formtextarea\" cols=\"50\" rows=\"10\" id=\"ItemDetails\">".$ItemDetails."</textarea></td></tr>\n
	<tr><td><b>For all the following price fields ONLY enter a number or decimal number and NO text or pound sign</b></td></tr>\n
	<tr><td>Price (list) <br>GBP  <input type=\"text\" class=\"formcontrol\" name=\"ItemPriceList\" size=\"10\" value=\"".$ItemPriceList."\"></td></tr>\n
	<tr><td>Price DBA<br>GBP  <input type=\"text\" class=\"formcontrol\" name=\"ItemPriceDBA\" size=\"10\" value=\"".$ItemPriceDBA."\"></td></tr>\n
	<tr><td>Ship UK<br>GBP  <input type=\"text\" class=\"formcontrol\" name=\"ItemShipUK\" size=\"10\" value=\"".$ItemShipUK."\"></td></tr>\n
	<tr><td>Ship EU<br>GBP  <input type=\"text\" class=\"formcontrol\" name=\"ItemShipEU\" size=\"10\" value=\"".$ItemShipEU."\"></td></tr>\n
	<tr><td>Ship Z1<br>GBP  <input type=\"text\" class=\"formcontrol\" name=\"ItemShipZ1\" size=\"10\" value=\"".$ItemShipZ1."\"></td></tr>\n
	<tr><td>Ship Z2<br>GBP  <input type=\"text\" class=\"formcontrol\" name=\"ItemShipZ2\" size=\"10\" value=\"".$ItemShipZ2."\"></td></tr>\n
	<tr><td>Web link<br><input type=\"text\" class=\"formcontrol\" name=\"ItemWeblink\" size=\"60\" value=\"".$ItemWeblink."\"></td></tr>\n
	<tr><td>Image Path<br><input type=\"text\" class=\"formcontrol\" name=\"ItemImagePath\" size=\"60\" value=\"".$ItemImagePath."\"></td></tr>\n");

	if($ItemImagePath){
		echo("<tr valign='top'><td><img src=\"".$ItemImagePath."\" alt=\"".$ItemTitle."\"></td></tr>\n");
	}

	echo("<tr><td>Enter or change the Image path above for an image already on the server or click 'Choose File' to upload a new one. Images for uploading must be jpeg, less then 10Mb in size and will be automatically resized to 200 pixels wide during the save operation.<br><input type=\"FILE\" name=\"thefile\" id=\"thefile\" class=\"formcontrol\" size=\"70\" />
	<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"1000000\">
	</td></tr>\n
	<tr><td> Valid from date<br><input type=\"textbox\" class=\"formcontrol\" name=\"ItemDate\" id=\"ItemDate\" size=\"25\" readonly=\"true\" value=\"$ItemDate\"> <em>click box and choose from calendar</em></td></tr>\n
	<tr><td>Date posted<br><input type=\"textbox\" class=\"formcontrol\" name=\"ItemPostingDate\" id=\"ItemPostingDate\" size=\"25\" readonly=\"true\" value=\"$ItemPostingDate\"> <em>click box and choose from calendar</em></td></tr>\n");
	echo("<tr><td>Status<br><select name=\"ItemStatus\" class=\"formcontrol\">\n"); 
	if($ItemStatus==0){
		print"<option  value=\"0\" selected>Pending</option>\n";
		print"<option  value=\"1\" >Live</option>\n";
	}else{
		print"<option  value=\"0\" >Pending</option>\n";
		print"<option  value=\"1\" selected>Live</option>\n";
	}
	echo("</select></td></tr>\n");
	//add editor link text for direct link
	if($ItemID && $section){
		echo("<tr><td>Direct link to item detail - copy the text below into a link in the Content manager to reach this item from another page<br>\n"); 
		echo("main.php?section=".$section."&itemaction=detail&itemid=".$ItemID."</td></tr>\n");
	}
	?>

	<script type="text/javascript" src="<?php echo($componentpath); ?>popcal/calendar.js"></script>
	<script type="text/javascript" src="<?php echo($componentpath); ?>popcal/calendar-en.js"></script>
	<script type="text/javascript" src="<?php echo($componentpath); ?>popcal/calendar-setup.js"></script>
	<style type="text/css">@import url(<?php echo($componentpath); ?>popcal/calendar-blue.css);</style>
	<SCRIPT LANGUAGE="JavaScript">	
	function fix(num) {
		string = "" + num;
		numberofdigits = string.length;
		if (numberofdigits <2){
			return '0'+string;
		}else{
			return string;
		}
	}
	function catcalc1(cal) {
		if (cal.dateClicked) {
      		// OK, a date was clicked
			
			var y = cal.date.getFullYear();
      		var m = cal.date.getMonth();     // integer, 0..11
      		var d = cal.date.getDate();      // integer, 1..31

			var date = new Date(y,m,d);
			var now = new Date();
			var diff = date.getTime() - now.getTime();
			var days = Math.floor(diff / (1000 * 60 * 60 * 24));
			var dbdate=y+"-"+fix((m+1))+"-"+fix(d);
			if (days < -1) {
	
				var field = document.getElementById("ItemDate");
				field.value = "";
				alert("Please choose a date that is today or in the future");
			}else{
				var field = document.getElementById("ItemDate");
				field.value = dbdate;
				//"%A, %B %e, %Y",			
			}
		}
	}
	function catcalc2(cal) {
		if (cal.dateClicked) {
      		// OK, a date was clicked
			
			var y = cal.date.getFullYear();
      		var m = cal.date.getMonth();     // integer, 0..11
      		var d = cal.date.getDate();      // integer, 1..31

			var date = new Date(y,m,d);
			var now = new Date();
			var diff = date.getTime() - now.getTime();
			var days = Math.floor(diff / (1000 * 60 * 60 * 24));
			var dbdate=y+"-"+fix((m+1))+"-"+fix(d);
			if (days < -1) {
	
				var field = document.getElementById("ItemPostingDate");
				field.value = "";
				alert("Please choose a date that is today or in the future");
			}else{
				var field = document.getElementById("ItemPostingDate");
				field.value = dbdate;
				//"%A, %B %e, %Y",			
			}
		}
	}
	Calendar.setup({
        inputField     :    "ItemDate",
        ifFormat       :    "%Y-%m-%d",
        showsTime      :    true,
        timeFormat     :    "24",
        onUpdate       :    catcalc1
    });
    Calendar.setup({
        inputField     :    "ItemPostingDate",
        ifFormat       :    "%Y-%m-%d",
        showsTime      :    true,
        timeFormat     :    "24",
        onUpdate       :    catcalc2
    });
		</script>
	<?php

}


?>

</table>

</form>


