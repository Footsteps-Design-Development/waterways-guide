<?php
/**
 * @version     1.0.0
 * @package     com_membership classified adverts
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Chris Grant www.productif.co.uk
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/* Updated for Joomla 3 CJG 21061221 */

$app = Factory::getApplication('com_membership');
$db = Factory::getDBO();
require_once(JPATH_COMPONENT_SITE."/commonV3.php");
//get menu parameters
$currentMenuItem = $app->getMenu()->getActive();
$view = $currentMenuItem->getParams()->get('classified_view');

//check access level of user
$user = Factory::getUser();
$userid = $user->id;

/* End of updated for Joomla 3 CJG 21061221 */



?>

<style type="text/css" media="screen,projection">
.trailer {color: #333333; font-size: 100%; font-weight: normal; margin-left:6px; border-bottom: 8px solid #ffffff; border-top: 0px solid #cccccc; border-left: 0px solid #06A8D9; border-right: 0px solid #06A8D9;} 
.trailer a:link {color: #333333; text-decoration: none}
.trailer a:visited {color: #333333; text-decoration: none}
.trailer a:hover {color: #FAA637; text-decoration: none}

.formtextarea {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 95%;
	font-weight: lighter;
	height:120px;
	width:100%;
}

</style>
<form name="form" enctype="multipart/form-data" method="post">



<table border="0" cellspacing="1" cellpadding="3" width="100%">
<?php

/*header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
	header('Pragma: no-cache');

if (isset($_POST["classifiedid"])) {
	// do data processing
    header("Location: default.php");
    //die();
}else{
	header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
	header('Pragma: no-cache');
}
*/
if(!$view || $view=="search"){

	$test_vars=(array(
	'level',
	'editpage',
	'classifiedid',
	'classifiedaction',
	'infoid',
	'message',
	'offset',
	'catdesc',
	'ClassifiedSection',
	'ClassifiedSeekOffer',
	'thissection',
	'listresults',
	'thisrow',
	'where',
	'myClassifiedEndDate',
	'olist'
	));
	foreach($test_vars as $test_var) { 
		if(!$$test_var =  $app->input->getString($test_var)){
			$$test_var = "";
		}
	}
	
    

	echo("<h2 class='art-postheader'>Classified Advertising</h2>");
	$admin="";
	$disclaimer_message="Transactions are at your own risk. DBA take no responsibility for the accuracy or honesty of buyers or sellers or for the items sold here.
	Sellers should make sure cheques are fully cleared (not just appearing on your balance) and beware of refunding overpayments which is a known scam.";
	
	if($level>=50 || $editpage==1){
		$admin="open";
	}
	echo("<input name=\"classifiedid\" type=\"hidden\" value=\"$classifiedid\">\n");
	echo("<input name=\"classifiedaction\" type=\"hidden\" value=\"$classifiedaction\">\n");
	echo("<input name=\"infoid\" type=\"hidden\" value=\"$infoid\">\n");
	
	
	//---------------------------------------List sections---------------------------------------------
	
	if ($classifiedaction=="" || $classifiedaction=="sections" || $classifiedaction=="list" || $classifiedaction=="detail") {
	
		if(isset($message)){
			echo("<tr><td class=bodytext colspan=4>$message<br></td></tr>\n");
		}
		echo("<tr><td class=bodytext colspan=4><select name=\"ClassifiedSection\" id=\"ClassifiedSection\" class=\"formcontrol\" onChange=\"document.form.classifiedaction.value='list';document.form.submit()\">\n"); 
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->qn('tblClassified'))
			->where($db->qn('ClassifiedSeekOffer')." = 'w'")
			->where('('.$db->qn('ClassifiedStatus').' = 1 OR '.$db->qn('ClassifiedStatus').' = 2)');
		$wanted = $db->setQuery($query)->loadResult();		
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->qn('tblClassified'))
			->where($db->qn('ClassifiedSeekOffer')." = 'o'")
			->where('('.$db->qn('ClassifiedStatus').' = 1 OR '.$db->qn('ClassifiedStatus').' = 2)');
		$offered = $db->setQuery($query)->loadResult();		
		$offeredwanted="$offered Offered . . . $wanted Wanted";
		echo("<option value=\"All\">All . . . ".$offeredwanted."</option>\n");
		//echo("<option value=\"All\">All</option>\n");
		//get current sections
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('tblClassifiedSections'))
			->where($db->qn('Status').' = 1')
			->order($db->qn('ClassifiedSectionName'));
		$sections = $db->setQuery($query)->loadAssocList();
		foreach($sections as $row) {
			//check how many offer wanted
			$thisClassifiedSection = stripslashes($row["ClassifiedSectionID"]);
			$query = $db->getQuery(true)
				->select('COUNT(*)')
				->from($db->qn('tblClassified'))
				->where($db->qn('ClassifiedSection').' = '.$db->q($thisClassifiedSection))
				->where($db->qn('ClassifiedSeekOffer')." = 'w'")
				->where('('.$db->qn('ClassifiedStatus').' = 1 OR '.$db->qn('ClassifiedStatus').' = 2)');
			$wanted = $db->setQuery($query)->loadResult();		
			$query = $db->getQuery(true)
				->select('COUNT(*)')
				->from($db->qn('tblClassified'))
				->where($db->qn('ClassifiedSection').' = '.$db->q($thisClassifiedSection))
				->where($db->qn('ClassifiedSeekOffer')." = 'o'")
				->where('('.$db->qn('ClassifiedStatus').' = 1 OR '.$db->qn('ClassifiedStatus').' = 2)');
			$offered = $db->setQuery($query)->loadResult();		
			$offeredwanted="$offered Offered . . . $wanted Wanted";
			if($thisClassifiedSection==$ClassifiedSection){
				$olist.="<option value=\"".$thisClassifiedSection."\" selected>".$row["ClassifiedSectionName"]." . . . ".$offeredwanted."</option>\n";
			}else{
				$olist.="<option  value=\"".$thisClassifiedSection."\">".$row["ClassifiedSectionName"]." . . . ".$offeredwanted."</option>\n";
			}
		}
		echo($olist);
		echo("</select> \n");
		if($ClassifiedSeekOffer=="w"){
			$ClassifiedSeekOfferWChecked=" checked";
			$ClassifiedSeekOfferOChecked="";
		}else{
			$ClassifiedSeekOfferWChecked="";
			$ClassifiedSeekOfferOChecked=" checked";
		}
		echo("<input type=\"button\" class=\"btn btn-primary\" name=\"Search\" value=\"Search\" onClick=\"document.form.classifiedaction.value='list';document.form.submit()\">\n");
		echo("<br>Offered <input name=\"ClassifiedSeekOffer\" type=\"radio\" class=\"formcontrol\" id=\"ClassifiedSeekOffer\" value=\"o\"".$ClassifiedSeekOfferOChecked.">\n");
		echo("Wanted <input name=\"ClassifiedSeekOffer\" type=\"radio\" class=\"formcontrol\" id=\"ClassifiedSeekOffer\" value=\"w\"".$ClassifiedSeekOfferWChecked.">\n");
		
		echo("</td></tr>\n");
		
		//echo(" <a href=\"#\" onClick=\"document.form.classifiedaction.value='list';document.form.submit()\"> <img src=\"Image/common/preview.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Find classifieds in this section\"> Search </a></td></tr>\n");
		if(!$classifiedaction){
			echo("<tr class=table_classified><td class=bodytext colspan=4>".$classifiedsectionintrotext."</td></tr>\n");
		}
	}
	
	
	
	//---------------------------------------List classifieds---------------------------------------------
	
	if ($classifiedaction=="list") {
	
	
		echo("<input name=\"catdesc\" type=\"hidden\" value=\"$catdesc\">");
	
		if($ClassifiedSeekOffer=="w"){
			$ClassifiedSeekOfferDesc="Wanted";
		}else{
			$ClassifiedSeekOfferDesc="Available";
		}
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('tblClassified'))
			->where($db->qn('ClassifiedSeekOffer').' = '.$db->q($ClassifiedSeekOffer))
			->where('('.$db->qn('ClassifiedStatus').' = 1 OR '.$db->qn('ClassifiedStatus').' = 2)')
			->order($db->qn('ClassifiedSection'))
			->order($db->qn('ClassifiedEndDate').' DESC');
		if($ClassifiedSection && $ClassifiedSection != 'All') $query->where($db->qn('ClassifiedSection').' = '.$db->q($ClassifiedSection));
		$rows = 0;
		$classifieds = $db->setQuery($query)->loadAssocList();
		$rows = count($classifieds);
		
		# If the search was unsuccessful then Display Message try again.
		If ($rows == 0){
			PRINT "<tr><td class=bodytext colspan=4>Sorry - there are no classifieds in that section at the moment.</td></tr>\n";
		}else{
			
			$classifiedmatch=0;			
			foreach($classifieds as $row) {
				$ClassifiedID = stripslashes($row["ClassifiedID"]);
				$ClassifiedSection = stripslashes($row["ClassifiedSection"]);
				$ClassifiedRef = stripslashes($row["ClassifiedRef"]);
				$ClassifiedTitle = stripslashes($row["ClassifiedTitle"]);
				$ClassifiedDescription = stripslashes($row["ClassifiedDescription"]);
				$ClassifiedLocation = stripslashes($row["ClassifiedLocation"]);   
				$ClassifiedDatePosted = stripslashes($row["ClassifiedDatePosted"]);
				$ClassifiedDatePostedDesc=date_to_format($ClassifiedDatePosted,"");
				$ClassifiedPrice = $row["ClassifiedPrice"];
				$ClassifiedPriceCurrency = stripslashes($row["ClassifiedPriceCurrency"]);
				$ClassifiedMemberID = stripslashes($row["ClassifiedMemberID"]);
				if($ClassifiedPrice>0){
					$ClassifiedPriceFormatted=number_format($ClassifiedPrice);
					$pricedisplay="$ClassifiedPriceCurrency $ClassifiedPriceFormatted";
				}else{
					$pricedisplay="?";
				}
				$ClassifiedViews = $row["ClassifiedViews"]; 
				if($row["ClassifiedDescription"]){
					$msgtrail=substr($ClassifiedDescription, 0, 120)." . . . . . . .";
				}else{
					$msgtrail="";
				}
				$classifiedmatch=1;
				if($thissection != $ClassifiedSection){
					$query = $db->getQuery(true)
						->select($db->qn('ClassifiedSectionName'))
						->from($db->qn('tblClassifiedSections'))
						->where($db->qn('ClassifiedSectionID').' = '.$db->q($ClassifiedSection));
					$ClassifiedSectionDesc = $db->setQuery($query)->loadResult();
					$listresults.="<tr><td colspan=4><h2>$ClassifiedSectionDesc $ClassifiedSeekOfferDesc</h2></td></tr>\n";
					$listresults.="<tr><td class=bodytext><b>Title</b></td><td class=bodytext><b>Location</b></td><td class=bodytext><b>Price</b></td>";
					
					//hits counter column removed 20190204 CJG
					$listresults.="<td></td></tr>\n";
					
						
					$thissection = $ClassifiedSection;
				}
	
				if($thisrow=="odd"){
					$rowclass="table_stripe_even";
					$thisrow="even";
				}else{
					$rowclass="table_stripe_even";		
					$thisrow="odd";
				}
				
				$listresults.="<tr><td class=$rowclass><a href=\"#\" onClick=\"document.form.classifiedaction.value='detail';document.form.classifiedid.value='$ClassifiedID';document.form.submit()\">$ClassifiedTitle</a></td><td class=$rowclass>$ClassifiedLocation</td><td class=$rowclass>$pricedisplay</td>";
				
				$listresults.="<td></td></tr>\n";
								
				if($msgtrail){
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
						$imagedetail="<img src=\"".$imagepath."\" width=".$imwidth." height=".$imheight." border=0 title=\"".$mediatitle."\" alt=\"".$mediatitle."\">\n";  
						$listresults.="<tr><td class=trailer colspan=2 valign=top><a href=\"#\" onClick=\"document.form.classifiedaction.value='detail';document.form.classifiedid.value='$ClassifiedID';document.form.submit()\">".$msgtrail."</a></td><td class=trailer><a href=\"#\" onClick=\"document.form.classifiedaction.value='detail';document.form.classifiedid.value='$ClassifiedID';document.form.submit()\">".$imagedetail."</a></td><td></td></tr>\n";
	
					}else{
						$listresults.="<tr><td class=trailer colspan=3><a href=\"#\" onClick=\"document.form.classifiedaction.value='detail';document.form.classifiedid.value='$ClassifiedID';document.form.submit()\">".$msgtrail."</a></td><td></td></tr>\n";
					}
					$listresults.="<tr><td colspan=4><hr /></td></tr>\n";
				}
				
			}
			if($classifiedmatch==1){
				if($rows==1){
					PRINT "<tr><td class=bodytext colspan=4>$rows entry listed - click on the Title column for details \n";
				}else{
					PRINT "<tr><td class=bodytext colspan=4>$rows entries listed - click on a Title column for details \n";			
				}
				//print or email
				//PRINT "<tr><td class=bodytext colspan=4><a href=\"#\" onClick=\"document.form.classifiedaction.value='print';document.form.classifiedid.value='$ClassifiedID';document.form.submit()\">Print this listing <img src=\"Image/common/print.gif\" alt=\"Print this listing\" width=\"18\" height=\"18\" border=\"0\"></a> with details or <a href=\"#\" onClick=\"document.form.classifiedaction.value='emailme';document.form.classifiedid.value='$ClassifiedID';document.form.submit()\">email it to me <img src=\"Image/common/email.gif\" alt=\"email it to me\" width=\"18\" height=\"18\" border=\"0\"></a></td></tr>\n";
				//PRINT " or <a href=\"#\" onClick=\"document.form.classifiedaction.value='printlist';document.form.submit()\">here to view, save or email the full details <img src=\"Image/common/txt.gif\" alt=\"View, save or email the full details\" width=\"18\" height=\"18\" border=\"0\"></a></td></tr>\n";
				PRINT "</td></tr>\n";
				print "<tr><td class=bodytext colspan=4><font color=ff0000>".$disclaimer_message."</font></td></tr>\n";
				
				PRINT $listresults."\n";
			}else{
				PRINT "<tr><td class=bodytext colspan=4>Sorry - there are no classifieds in that section at the moment.</td></tr>\n";		
			}
		}
	
	
		//exit();
	}
	//---------------------------------------classified details---------------------------------------------
	
	if ($classifiedaction=="detail") {
		$num_rows = 0;
		
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('tblClassified'))
			->where($db->qn('ClassifiedID').' = '.$db->q($classifiedid))
			->where('('.$db->qn('ClassifiedStatus').' = 1 OR '.$db->qn('ClassifiedStatus').' = 2)');
		$result = $db->setQuery($query)->loadAssocList();
		$num_rows = count($result);
		
		# If the search was unsuccessful then Display Message try again.
		if (!$num_rows) {
			echo("<tr><td class=bodytext>Sorry - no details available for this classified<br><hr></td></tr>"); 
			//exit();
		}
	
		$datenow = time();
		$row = reset($result);
	
		$ClassifiedID = $row["ClassifiedID"];
		$ClassifiedRef = stripslashes($row["ClassifiedRef"]);
		$ClassifiedSection = stripslashes($row["ClassifiedSection"]);
		$ClassifiedTitle = stripslashes($row["ClassifiedTitle"]);
		$ClassifiedLocation = stripslashes($row["ClassifiedLocation"]);
		if(!$ClassifiedLocation){
			$ClassifiedLocation=$login_country;
		}
	
		$ClassifiedDescription = stripslashes(nl2br($row["ClassifiedDescription"]));
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
			$ClassifiedContactTel="N/A";
		}
		$ClassifiedWeblink = stripslashes($row["ClassifiedWeblink"]);
		$ClassifiedPrice = $row["ClassifiedPrice"];
		$ClassifiedPriceCurrency = stripslashes($row["ClassifiedPriceCurrency"]);
		$ClassifiedPrice = $row["ClassifiedPrice"];
		$ClassifiedPriceCurrency = stripslashes($row["ClassifiedPriceCurrency"]);
		if($ClassifiedPrice>0){
			$ClassifiedPriceFormatted=number_format($ClassifiedPrice);
			$pricedisplay="$ClassifiedPriceCurrency $ClassifiedPriceFormatted";
		}else{
			$pricedisplay="?";
		}
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
		
		list ($ClassifiedDatePosted, $myTime) = explode (' ', "$ClassifiedDatePosted ");
		list ($ClassifiedStartDate, $myTime) = explode (' ', "$ClassifiedStartDate ");
		list ($myClassifiedEndDate, $myTime) = explode (' ', "$ClassifiedEndDate ");
		$now=time();
		$my_secs=strtotime($ClassifiedEndDate);
		$secstogo=$my_secs-$now;
		$daytogo=number_format (($secstogo/86400),0);
		$ClassifiedViews = $row["ClassifiedViews"]; 
		//increment views
		 $ClassifiedViews+=1;
		$update = new \stdClass();
		$update->ClassifiedViews = $ClassifiedViews;
		$update->ClassifiedID = $ClassifiedID;
		$result = $db->updateObject('tblClassified', $update, 'ClassifiedID');
		if(!$result){die ("Couldn't update database");}
		
		echo("<tr><td class='bodytext' colspan=2><a href=\"#\" onClick=\"document.form.classifiedaction.value='list';document.form.submit()\">Back to the list <img src=\"Image/common/back1.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Back to the list of notices\"></a></td></tr>\n");
		echo("<tr><td class=bodytext colspan=2><font color=ff0000>".$disclaimer_message."</font></td></tr>\n");
		?>
	
	<tr>
		<td valign="top" class='bodytext' colspan=2><h2><?php echo($ClassifiedTitle); ?></h2></td>
	  </tr>	
			  <tr>
		<td valign="top" class='bodytext'> Offered / Wanted</td>
		<td class='bodytext'>
		<?php
			
			$found = strstr ($ClassifiedSeekOffer, "w");
			if($found){
				$ClassifiedSeekOfferType=" Wanted";
			}else{
				$ClassifiedSeekOfferType=" Offered";
			}
			?><?php echo($ClassifiedSeekOfferType); ?>	</td></tr>
			<tr><td></td><td><?php
			 //check if there is an image
			 $imagepath="Image/classified/".$ClassifiedID.".jpg";
			//add any images underneath 
		 
			if (file_exists($imagepath)) {
				$imageInfo = getimagesize($imagepath);
				$imwidth = $imageInfo[0];
				$imheight = $imageInfo[1]; 
				$mediatitle=$ClassifiedTitle;
				$imagedetail="<img src=\"".$imagepath."\" border=0 title=\"".$mediatitle."\" alt=\"".$mediatitle."\">\n";  
				//$imagedetail="<img src=".$imagepath." width=".$imwidth." height=".$imheight." border=0 title=\"".$mediatitle."\" alt=\"".$mediatitle."\">\n";  
				echo($imagedetail);
			}
			?>		 </td>
			</tr>
			<tr>
		<td valign="top" class='bodytext'>Description</td>
		<td class='bodytext'><?php echo($ClassifiedDescription); ?></td>
			</tr>
		<tr><td valign="top" class='bodytext'>
		Price</td>
		<td class='bodytext'><?php echo($pricedisplay); ?>		</td></tr>
		
		
	
			<tr>
			  <td valign="top" class='bodytext'>
		Reference</td>
		<td class='bodytext'>
		
		<?php echo($ClassifiedRef);	?>	</td></tr>	
	  
	
	  
	  <tr>
	  <td valign="top" class='bodytext'>
		Contact</td>
	  <td class='bodytext'><?php echo($ClassifiedContactName); ?></td></tr>
		  
		  
		<tr>
		<td valign="top" class='bodytext'>
		email</td>
		<td class='bodytext'><?php echo($ClassifiedContactEmail); ?></td></tr>
		
		<tr>
		<td valign="top" class='bodytext'>Telephone</td>
		<td class='bodytext'><?php echo($ClassifiedContactTel); ?></td>
		</tr>
		
		<tr>
		  <td valign="top" class='bodytext'>
		Website</td>
		<td class='bodytext'><?php 
		if($ClassifiedWeblink){
			$pos = strpos($ClassifiedWeblink, "htt");
			if ($pos === false) {
				echo("<a href=\"http://".$ClassifiedWeblink."\" target=\"_blank\">".$ClassifiedWeblink."</a>");
			} else {
    			echo("<a href=\"".$ClassifiedWeblink."\" target=\"_blank\">".$ClassifiedWeblink."</a>");
			}
			
		}
		 ?></td></tr>
		<?php
		/*<tr>
		  <td valign="top" class='bodytext'> Placed	</td>
		<td class='bodytext'>
		<?php echo($ClassifiedStartDate); ?></td>
		</tr>
		*/
		?>
		  <tr>
		<td valign="top" class='bodytext'>
		Ending</td>
		<td class='bodytext'>
		<?php echo($ClassifiedEndDate." (".$daytogo." days left)"); ?></td>
		</tr>
		
		  <tr><td valign="top" class='bodytext'>
		Status</td>
		<td class='bodytext'>
		<?php 
		if(!$ClassifiedStatus){
			echo("Pending");
		}else{
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
			
			echo($ClassifiedStatusText);
		}
		?>	</td></tr>
	  
		<?php 
		
		if($userid==$ClassifiedMemberID){
			//show hits counter to seller only, added 20190204 CJG
			echo("<tr><td valign='top' class='bodytext'>Times viewed</td><td class='bodytext'>$ClassifiedViews</td></tr>\n");
		}
		?>

	<tr>
		<td valign="top" class='bodytext'>
		Direct link</td>
		<td class='bodytext'>
		<a href="http://barges.org/adverts/search?classifiedaction=detail&classifiedid=<?php echo($ClassifiedID); ?>">http://barges.org/adverts/search?classifiedaction=detail&classifiedid=<?php echo($ClassifiedID); ?></a></td>
	  </tr>
		
		<?
	
	
	
	
		
		//exit();
	}
	
	
	if ($classifiedaction=="" || $classifiedaction=="list" || $classifiedaction=="detail") {
		$info = $db->setQuery("SHOW TABLE STATUS LIKE 'tblClassified'")->loadAssoc(); 
		$lastupdated=date_to_format($info["Update_time"],"dt"); 
		// echo("<tr><td class=bodytext colspan=3><font size=1> Last update $lastupdated</font> $classifiedaction</td></tr>\n");
	}
}elseif($view=="member_edit"){
	$admin="";
	include("classified_update.php");

}elseif($view=="admin_edit"){

//echo("admin edit");
	//admin edit of member record

	$admin="open";
	include("classified_update.php");

}




?>
</table>
</form>