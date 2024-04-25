<?php
/**
 * @version     1.0.0
 * @package     com_waterways_guide Member check
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Chris Grant www.productif.co.uk
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

$app = Factory::getApplication('com_waterways_guide');

$db = Factory::getDBO();
require_once(JPATH_COMPONENT_SITE."/commonV3.php");


?>

<form name="form" enctype="multipart/form-data" method="post">
<div>
<?php



$test_vars=(array(
'level',
'input_mn'
));
foreach($test_vars as $test_var) { 
	if(!$$test_var =  $app->input->getString($test_var)){
		$$test_var = "";
	}
}

echo("<h2 class='art-postheader'>Member Check</h2>");
$admin="";
	
	
//---------------------------------------Validate member---------------------------------------------
	
echo("<br />To validate membership, enter the <b>six digit</b> membership number (including preceding zeros), family name or email address below and click 'Check'<br /><br />");
echo("<input type='text' name='input_mn' size='40' value=\"".stripslashes($input_mn)."\"> <input type='submit' name='check' value='Check'>");
	

if($input_mn){

	//do check

	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblMembers'))
		->where($db->qn('MembershipNo').' = '.$db->q($input_mn), 'OR')
		->where($db->qn('LastName').' = '.$db->q($input_mn))
		->where($db->qn('LastName2').' = '.$db->q($input_mn))
		->where($db->qn('Email').' = '.$db->q($input_mn))
		->where($db->qn('Email2').' = '.$db->q($input_mn));
	$member_data = $db->setQuery($query)->loadObjectList();
	if(count($member_data)) {
		foreach($member_data as $row) {
			$DatePaid=$row->DatePaid;
			if($DatePaid){
				$datepaiddisplay=date_to_format($DatePaid,"d") ;
			}
			$MemStatus=$row->MemStatus;
			switch ($MemStatus) {
					case 1:
						$thisstatus="Applied awaiting payment";
						$substatus=0;
						break;
					case 2:
						$thisstatus="Paid up - last payment received on $datepaiddisplay";
						$substatus=1;
						break;
					case 3:
						$thisstatus="Renewal due - last payment received on $datepaiddisplay";
						$substatus=1;
						break;	
					case 4:
						$thisstatus="Gone away, - last payment received on $datepaiddisplay";
						$substatus=1;
						break;
					case 5:
						$thisstatus="Terminated - last payment received on $datepaiddisplay";
						$substatus=0;
						break;
					case 6:
						$thisstatus="Complimentary";
						$substatus=1;
						break;
				}
	
			$Email=$row->Email;
			$Email2=$row->Email2;
			$ID2=$row->ID2;
			$MembershipNo=$row->MembershipNo;
			$MemberID=$row->ID;
			$contactname="";
			$contactname=$row->FirstName;
			if($row->FirstName && $contactname){
				$contactname.=" ".$row->LastName;
			}else{
				$contactname=$row->LastName;
			}
			
			//check if there is a barge register entry
			$mybarge_url="";
			$mylocation_url="";
			$query = $db->getQuery(true)
				->select($db->qn('ID'))
				->from($db->qn('tblAssetsMembers'))
				->where($db->qn('MembershipNo').' = '.$db->q($MembershipNo));
			if ($mybarge = $db->setQuery($query)->loadObject()) {
				//there is an entry
			//check the barge name in register
				$query = $db->getQuery(true)
					->select('*')
					->from($db->qn('tblAssets'))
					->where($db->qn('VesselID').' = '.$db->q($mybarge->ID))
					->where($db->qn('AssetCategory').' = 1');
				if ($mybargename = $db->setQuery($query)->loadObject()) {
					$mybarge_url="<img src=\"components/com_kunena/template/blue_eagle/images/icons/mybarge_icon.png\" width=\"22\" height=\"22\" border=\"0\" alt=\"See the barge in the register\" title=\"See the barge in the register\"> View barge register for <a href=\"members/bargeregister/bargeregister-search/?assetaction=detail&vesselid=".$mybarge->ID."\">".$mybargename->AssetTitle."</a>";
				//check if there is an MMSI in register
					$query = $db->getQuery(true)
						->select('*')
						->from($db->qn('tblAssets'))
						->where($db->qn('VesselID').' = '.$db->q($mybarge->ID))
						->where($db->qn('AssetCategory').' = 14');
					if ($mylocation = $db->setQuery($query)->loadObject()) {
						//there is an entry
						$mylocation_url="<img src=\"Image/common/compass.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Try to find the current barge location on AIS Marinetraffic register\" title=\"Try to find the current barge location on AIS Marinetraffic register\"> Search for current <a href=\"members/bargeregister/bargeregister-search/?assetaction=position&vesselid=".$mybarge->ID."&MMSI=".$mylocation->AssetTitle."\">barge location</a>";
					}
				}	
			}
			
			
			echo("<br /><br /><br />
			Member: $contactname<br /> 
			Membership number: $MembershipNo<br />
			Status: $thisstatus\n");
			if($MemStatus==2 OR $MemStatus==6){
				echo("<br /><img src=\"Image/common/tick.gif\" title=\"Valid member\" alt=\"Valid member\"> Verified as a current DBA member\n");
				echo("<br /><img src=\"Image/common/email.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Send a private message\" title=\"Send a private message\"> Send a private message to <a href=\"discussion-forum/private-messaging?task=new&recip=".$MemberID."\"> ".$contactname."</a>\n");
			}else{
				echo("<br /><img src=\"Image/common/cross.gif\" title=\"Not a Valid member\" alt=\"Not a Valid member\"> Not verified as a current DBA member\n");			
			}
			
			if($mybarge_url){
				echo ("<br />".$mybarge_url);
				if($mylocation_url){
					echo ("<br />".$mylocation_url);
				}
			}
			
			if(!empty($ID2)){
				$contactname2="";
				$contactname2=$row->FirstName2;
				if($row->FirstName2 && $contactname2){
					$contactname2.=" ".$row->LastName2;
				}else{
					$contactname2=$row->LastName2;
				}
				echo("<br /><br />
					 Second member: $contactname2\n");
				if($MemStatus==2 OR $MemStatus==6){
					echo("<br /><img src=\"Image/common/email.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Send a private message\" title=\"Send a private message\"> Send a private message to <a href=\"discussion-forum/private-messaging?task=new&recip=".$ID2."\"> ".$contactname2."</a>\n");
		 		}
			}
		}
	}else{
		echo("<br /><br /><br /><img src=\"Image/common/cross.gif\" title=\"Membership details not found\" alt=\"Membership details not found\"> Membership details not found\n");	
	}
}

//echo("<br /><br /><i>'Member check' is available to companies or organisations giving discounts to DBA members or having reason to validate membership prior to offering DBA member benefits. You are only seeing this option under the 'Members Area' menu because you have been included in this category. <br /><br />For more information about this service or if you know of other members that might qualify, contact $membershipemail</i>\n");

?>
</div>
</form>