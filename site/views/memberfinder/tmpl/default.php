<?php
/**
 * @version     1.0.0
 * @package     com_waterways_guide Member search
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Rod North www.calara.co.uk
 */

/* Updated for Joomla 3 CJG 21061221 */

use Joomla\CMS\Factory;

	$app = Factory::getApplication('com_waterways_guide');
	$db = Factory::getDBO();
	require_once(JPATH_COMPONENT_SITE."/commonV3.php");

	//get menu parameters
	$currentMenuItem = $app->getMenu()->getActive();
	$view = $currentMenuItem->getParams()->get('membersearch_view');

/* End of updated for Joomla 3 CJG 21061221 */

?>

<form name="form" enctype="multipart/form-data" method="post">
	<div>
		<?php
			$test_vars=(array('level', 'input_surname', 'input_boat'));
		
			foreach($test_vars as $test_var) { 
				if(!$$test_var =  $app->input->getString($test_var)){
					$$test_var = "";
				}
			}

			echo("<h2 class='art-postheader'>Member Finder</h2>");
			$admin="";
	
	
			//---------------------------------------Build Form---------------------------------------------
	
			echo('<br /> Here, you can search for a member by family name or a barge name. If they have agreed, you will see their contact details. <br />You can change your settings in <a href="members/mydetails">My Details</a><br /><br />');
			
			echo("<div class='col-sm'>Enter Family Name: <input type='text' name='input_surname' size='40' minlength='3' value=\"" . stripslashes($input_surname) . "\"></div>");
			echo("<div class='col-sm'> Enter Barge Name: <input type='text' name='input_boat' size='40' value=\"" . stripslashes($input_boat) . "\"></div>");
			echo("<br /><button type='submit' class='btn btn-primary' name='search' value='Search'>Search</button><br />");
		?>
	</div>
</form>

<div>
	<?php 				
		//build WHERE clause from inputs - sanitise to remove html tags
		if($input_surname){
			$safename = strtoupper(filter_var($input_surname, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW));
			$names = explode(" ", $safename);
			$countnames = count($names) - 1;
			$surname = $names[$countnames];
		}else{
			$surname = "";
		}
		
		if($input_boat){
			$boat = strtoupper(filter_var($input_boat, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW));
		}else{
			$boat = "";
		}
		
		if($surname || $boat){
			$subQuery1 = $db->getQuery(true)
				->select('1 AS '.$db->qn('Fam'))
				->select($db->qn(['m.ID', 'am.Ship', 'am.ID'],['SortID', 'ShipName', 'VesselID']))
				->select('YEAR('.$db->qn('m.DateJoined').') AS '.$db->qn('JoinedYear'))
				->select($db->qn(['m.ID', 'm.FirstName', 'm.LastName', 'm.Telephone2', 'm.Email', 'm.Services', 'm.MemTypeCode']))
				->from($db->qn('tblMembers', 'm'))
				->leftJoin($db->qn('tblAssetsMembers', 'am').' ON '.$db->qn('am.MembershipNo').' = '.$db->qn('m.MembershipNo'))
				->where($db->qn('m.MemStatus').' NOT IN (4,5)');
			$subQuery2 = $db->getQuery(true)
				->select('2 AS '.$db->qn('Fam'))
				->select($db->qn(['m.ID', 'am.Ship', 'am.ID'],['SortID', 'ShipName', 'VesselID']))
				->select('YEAR('.$db->qn('m.DateJoined').') AS '.$db->qn('JoinedYear'))
				->select($db->qn(['m.ID2', 'm.FirstName2', 'm.LastName2', 'm.Mobile2', 'm.Email2'],['ID', 'FirstName', 'LastName', 'Telephone2', 'Email']))
				->select($db->qn(['m.Services', 'm.MemTypeCode']))
				->from($db->qn('tblMembers', 'm'))
				->leftJoin($db->qn('tblAssetsMembers', 'am').' ON '.$db->qn('am.MembershipNo').' = '.$db->qn('m.MembershipNo'))
				->where($db->qn('m.MemStatus').' NOT IN (4,5)');
			if($surname) {
				$subQuery1->where($db->qn('m.LastName').' LIKE '.$db->q($surname.'%'));
				$subQuery2->where($db->qn('m.LastName2').' LIKE '.$db->q($surname.'%'));
			} else {
				$subQuery2->where($db->qn('m.MemTypeCode').' IN (2,4)');
			}
			if($boat) {
				$subQuery1->where($db->qn('am.Ship').' LIKE '.$db->q('%'.$boat.'%'));
				$subQuery2->where($db->qn('am.Ship').' LIKE '.$db->q('%'.$boat.'%'));
			}
			$query = $db->getQuery(true)
				->select($db->qn(['x.SortID', 'x.ID', 'x.Fam', 'x.JoinedYear', 'x.FirstName', 'x.LastName', 'x.Telephone2', 'x.Email', 'x.ShipName', 'x.Services', 'x.MemTypeCode', 'x.VesselID']))
				->from('('.$subQuery1->unionAll($subQuery2).') AS '.$db->qn('x'))
				->order($db->qn('x.SortID'));
			$member_data = $db->setQuery($query)->loadObjectList();
			if(count($member_data) > 0) {
				$nameresult = count($member_data);
				foreach($member_data as $row) {
					$memberid = $row->ID;
					$fam = $row->Fam;
					$joinedyear = $row->JoinedYear;
					$firstname = $row->FirstName;
					$lastname = $row->LastName;
					$mobile = $row->Telephone2;
					$email = $row->Email;
					$ship = $row->ShipName;
					$services = $row->Services;
					$memtype = $row->MemTypeCode;
					$vesselid = $row->VesselID;
					
					echo '<div class="member-finder member-finder-fam-'.$fam.'">';

					if($joinedyear == "1899") {
						$joinedyear = "Unknown";
					}
					
					if(!$ship) {
						$ship = "None Listed";
					}else{
						$ship = '<a href="members/bargeregister/search?assetaction=detail&vesselid=' . $vesselid . '">' . $ship . '</a>'; 
					}
					
					if(!$email) {
						$email = "Not Listed";
					}else if(strpos($services, "|51|")=== false && $fam == "1"){
						$email = "Private";
					}else if(strpos($services, "|59|")=== false && $fam == "2"){
						$email = "Private";
					}else{
						$email = '<a href="mailto:'. $email . '">' . $email . '</a>';
					}
					
					if(!$mobile) {
						$mobile = "Not Listed";
					}else if(strpos($services, "|50|")=== false && $fam == "1"){
						$mobile = "Private";
					}else if(strpos($services, "|58|")=== false && $fam == "2"){
						$mobile = "Private";
					}
					
					if(is_numeric($mobile)) {
						$mobile = '<a href="tel:' . $mobile . '">' . $mobile . '</a>';
					}else{
						$mobile = $mobile;
					} 
					
					if(strpos($services, "|57|")=== false && $fam == "1"){
						$private = '';
					}else if(strpos($services, "|65|")=== false && $fam == "2"){
						$private = '';
					// }else{
						// $private = '<a href="discussion-forum/private-messaging?task=new&recip=' . $memberid . '"><img src="Image/common/email.gif" width="18" height="18" border="0" alt="Send a Private Message to Member" title="Send a Private Message to ' . $firstname . ' ' . $lastname . '">Private Message</a>';
					}
					
					echo($firstname . " " . $lastname . "<br/>");
					echo('<span style="padding: 5px">Member Since: ' . $joinedyear . '</span><span style="padding: 5px"> Barge: ' . $ship . '</span><span style="padding: 5px"> Email: ' . $email . '</span><span style="padding: 5px"> Mobile: ' . $mobile . '</span><span style="padding: 5px">' . $private . '</span><br />');
					echo '</div>';
				}
			}else{
				echo("<br />No Results Found<br />".phpversion());
			}
		}
	?>
</div>
