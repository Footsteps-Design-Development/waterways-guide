<?php
/**
 * @version     1.0.0
 * @package     com_membership bargeregister
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Chris Grant www.productif.co.uk
 */


/* Updated for Joomla 3 CJG 21061221 */

use Joomla\CMS\Factory;

$app = Factory::getApplication('com_membership');
$db = Factory::getDBO();
require_once(JPATH_COMPONENT_SITE."/commonV3.php");
//get menu parameters
$currentMenuItem = $app->getMenu()->getActive();
$view = $currentMenuItem->getParams()->get('bargeregister_view');

/* End of updated for Joomla 3 CJG 21061221 */

$contactpage="members/bargeregister/contact-the-editor/";

$user = Factory::getUser();
$login_memberid = $user->id;

$test_vars=(array(
'assetaction',
'assetid',
'assetsort',
'vesselid',
'assetyearfrom',
'assetyearto',
'assetlengthfrom',
'assetlengthto',
'assetclass',
'assetpropulsion',
'assetsearchtext',
'listresults',
'thisrow',
'editpage',
'assetlisttype',
'where',
'thisvesselID',
'SearchYear',
'SearchLength',
'SearchClass',
'SearchPropulsion',
'searchindex',
'found',
'search',
'foundrows',
'sort_col1',
'sort_col2',
'sort_col3',
'sort_col4',
'sort_col5',
'sort_col7',
'sort_col15',
'admin',
'MMSI',
'assetadminstatus',
'AIS',
'adminheader',
'debug',
'SearchAIS',
'filtermatch',
'assetAIS',
'asset_login_memberid',
'detail',
'login_MembershipNo',
'contact',
'length',
'beam',
'type',
'propulsion',
'yearbuilt',
'admintransferlink'
));

foreach($test_vars as $test_var) { 
	if(!$$test_var =  $app->input->getString($test_var)){
		$$test_var = "";
	}
}

$foundrows = (isset($foundrows) && is_int($foundrows)) ? $foundrows : 0;

//get user MembershipNo
$query = $db->getQuery(true)
	->select($db->qn('MembershipNo'))
	->from($db->qn('tblMembers'))
	->where($db->qn('ID').' = '.$db->q($user->id));
$MembershipNo=$db->setQuery($query)->loadResult();
// echo("Mem no=".$MembershipNo);
	
if($view=="admin_edit"){
	$admin="open";
	$adminheader=" - Administration";
}else{
	$admin="";
}
	
?>

<style type="text/css" media="screen,projection">
.formtextarea {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 95%;
	font-weight: lighter;
	height:120px;
	width:100%;
}

.button_action {
    width: 150px !important;
	margin-right: 4px;
	margin-bottom: 4px;
}

.table_stripe_odd {background-color: #ffffff;} 

.table_stripe_even {background-color: #CCEEF7;} 



</style>

<form name="form" enctype="multipart/form-data" method="post">


<?php


//---------------------------------------List assets---------------------------------------------





if((!$view || $view=="search" && $assetaction!="detail") || ($view=="admin_edit" && $assetaction!="adminchangelog" && $assetaction!="editdetail" && $assetaction!="edit" && $assetaction!="save"  && $assetaction!="delete" && $assetaction!="deletevessel" && $assetaction!="detachvessel"  && $assetaction!="addvessel" )){

	/*$searchhelp="<b>How to search the register</b>. Enter one or more key words separated by spaces into the search box and press 'go'.
	If more than one word is entered, records returned will contain ALL words.<br>You can use 'wildcards' to represent parts of words or single characters. 
	Use * for any characters or ? for a single character e.g. 19* will find all dates from 1900 to 1999 but 19?0 will find decades 1910, 1920 etc. 24.* will find all barges between 24.0 and 24.9m. 
	The search will be made on all of the information in the register which includes things like class [luxemotor, klipper, spitz...], length and beam in metres, date of build, name, power or sail and history. 
	To list everything, type * in the search box and you will get an ideas of what to look for.";
	*/
	
	$searchhelp="To search the register, select a filter from the drop-down boxes. Alternatively, or in addition, enter one or more key words in the 'Search Text' box. If more than one word is entered, records returned will contain ALL words or parts of words.<br>
There are two search buttons: 'Whose barge?' - returns the barge name and keeper's name(s) and 'Full register' - returns a list with links to all details provided<br><br>To search by owner name, use the <a class=\"btn btn-primary\" href=\"/members/member-finder\" title=\"Member finder\">Member finder</a>   You may add or update your own boat details <a class=\"btn btn-primary\" href=\"members/bargeregister/bargeregister-edit\">here.</a><br>
To list all entries, leave all fields as 'Any' and leave the 'Search text' box blank then click either search button.";
	
	
	/*$searchhelp="<b>To search the register</b>, select a filter from the drop-down boxes. Alternatively or in addition, Enter one or more key words separated by spaces into the search text box. 
	If more than one word is entered, records returned will contain ALL words or parts of words. There are two search buttons, 'Whose barge?' returns barge and keeper name, 'Full register' returns a list with links to full details on each barge where members have provided it. To list everything, just click either button.";
	*/
	

	
	$detailhelp="Information shown has been provided by the current or past keepers of the barge in good faith and with permission for it to be shared on this website. <br><b>DBA - the Barge Association cannot accept responsibility for the accuracy of details.</b>";
	
	$contacthelp="The barge keeper has agreed to be contacted by email in relation to the barge shown below in the form.";
	
	function sort_array($array, $col = NULL, $dir = SORT_ASC, $natsort = true, $keep_keys = false) { 
		//check whether sorting is really neccessary 
		if (!is_array($array) or count($array) < 2) 
			return; 
	
		//detect column name 
		if (is_null($col)) 
			$col = current(array_keys(current($array))); 
	
		//if array index is not a number warp it into ' 
		if (!is_numeric($col)) 
			$col = "'".$col."'"; 
		
		foreach($array as $k=>$v) {
			if(!isset($v[$col])) unset($array[$k]);
		}
				
		//keep array indices or not? 
		$sort = $keep_keys ? 'uasort' : 'usort'; 
	
		//do natural sort? 
		$natsort = $natsort ? 'strnatcmp' : 'strcmp'; 
	
		//check sort order 
		if ($dir == SORT_DESC) 
			$natsort = '-1 * '.$natsort; 
		 
		//do sorting 
		$sort($array, function($a, $b) use($natsort, $col) {
			return $natsort($a[$col], $b[$col]);
		});
	
		return $array;
	}
	
	
	
	echo("<h3>Barge Register search $adminheader</h3>\n");
	echo("<table border='0' cellspacing='0' cellpadding='2' width='100%'>\n");
	if ($assetaction=="" || $assetaction=="list_1" || $assetaction=="list_2" || $assetaction=="list" ) {
			
		//reset fromdetail to assetlisttype=="list_1"
		echo("<input name=\"assetlisttype\" type=\"hidden\" value=\"$assetaction\">\n");
		echo("<tr><td colspan=7 class=list_small_member>");
		
		if($assetaction=="list"){
			$assetaction=$assetlisttype;
			
		}
		
		if ($assetlisttype!="list_1" && $assetlisttype!="list_2" && $assetaction!="list" && $assetaction!="list_1" && $assetaction!="list_2") {
			if(empty($detailhelp)) $detailhelp = '';
			echo("<p>$searchhelp<br><br>$detailhelp</p>\n");
		}
	
		$filter="";	
		//free text
	
		//'assetyearfrom','assetyearto','assetlengthfrom','assetlengthto','assetclass','assetpropulsion'
	
		//AssetCategory=7 yearbuiltfrom
		$query = $db->getQuery(true)
			->select('DISTINCTROW LEFT('.$db->qn('AssetTitle').', 4) AS '.$db->qn('YearFrom'))
			->from($db->qn('tblAssets'))
			->where($db->qn('AssetCategory').' = 7')
			->order($db->qn('YearFrom').' DESC');
		$filter.="<div class=\"row-fluid\"><div class=\"span3\">Year built from:<br><select class='formcontrol' name='assetyearfrom'>\n";
		$filter.="<option selected value=\"0\">Any</option>\n";
		foreach($db->setQuery($query)->loadColumn() as $rowYearFrom) {
			$option = stripslashes($rowYearFrom);
			if($option && $option==$assetyearfrom){$selected=" selected";}else{$selected="";}
			$filter.="<option value=\"".$option."\"".$selected.">".$option."</option>\n"; 
		}
		$filter.="</select></div>\n";
		
		//AssetCategory=7 yearbuiltto
		$query = $db->getQuery(true)
			->select('DISTINCTROW LEFT('.$db->qn('AssetTitle').', 4) AS '.$db->qn('YearTo'))
			->from($db->qn('tblAssets'))
			->where($db->qn('AssetCategory').' = 7')
			->order($db->qn('YearTo').' DESC');
		$filter.="<div class=\"span3\">to:<br><select class='formcontrol' name='assetyearto'>\n";
		$filter.="<option selected value=\"0\">Any</option>\n";
		foreach($db->setQuery($query)->loadColumn() as $rowYearTo) {
			$option = stripslashes($rowYearTo);
			if($option && $option==$assetyearto){$selected=" selected";}else{$selected="";}
			$filter.="<option value=\"".$option."\"".$selected.">".$option."</option>\n"; 
		}
		$filter.="</select></div></div>\n";
		
		//AssetCategory=4 lengthfrom 
		$LengthMax=40;
		$filter.="<div class=\"row-fluid\"><div class=\"span3\">Length from:<br><select class='formcontrol' name='assetlengthfrom'>\n";
		$filter.="<option selected value=\"0\">Any</option>\n";
		$option=10;
		while ($option < $LengthMax){
			if($option && $option==$assetlengthfrom){$selected=" selected";}else{$selected="";}
			$filter.="<option value=\"".$option."\"".$selected.">".$option."</option>\n";
			$option+=1;
		}
		$filter.="</select></div>\n";
		
		//AssetCategory=4 lengthto 
		$filter.="<div class=\"span3\">to:<br><select class='formcontrol' name='assetlengthto'>\n";
		$filter.="<option selected value=\"0\">Any</option>\n";
		$option=10;
		while ($option < $LengthMax){
			if($option && $option==$assetlengthto){$selected=" selected";}else{$selected="";}
			$filter.="<option value=\"".$option."\"".$selected.">".$option."</option>\n"; 
			$option+=1;
		}
		$filter.="</select></div></div>\n";
		
		//AssetCategory=3 class
		$query = $db->getQuery(true)
			->select('DISTINCTROW '.$db->qn('AssetTitle'))
			->from($db->qn('tblAssets'))
			->where($db->qn('AssetCategory').' = 3')
			->order($db->qn('AssetTitle'));
		$filter.="<div class=\"row-fluid\"><div class=\"span3\">Class:<br><select class='formcontrol' name='assetclass'>\n";
		$filter.="<option selected value=\"*\">Any</option>\n";
		foreach($db->setQuery($query)->loadColumn() as $rowAssetTitle) {
			$option = stripslashes($rowAssetTitle);
			if($option && $option==$assetclass){$selected=" selected";}else{$selected="";}
			$filter.="<option value=\"".$option."\"".$selected.">".$option."</option>\n"; 
		}
		$filter.="</select></div>\n";
		
		//AssetCategory=2 assetpropulsion
		$filter.="<div class=\"span3\">Propulsion:<br><select class='formcontrol' name='assetpropulsion'>\n";
		$filter.="<option selected value=\"*\">Any</option>\n";
		$option="Unpowered";
		if($option && $option==$assetpropulsion){$selected=" selected";}else{$selected="";}
		$filter.="<option value=\"Unpowered\"".$selected.">Unpowered (no engine)</option>\n"; 
		$option="Power";
		if($option && $option==$assetpropulsion){$selected=" selected";}else{$selected="";}
		$filter.="<option value=\"Power\"".$selected.">Power (includes ex sailing barges)</option>\n"; 
		$option="Sail / Power";	
		if($option && $option==$assetpropulsion){$selected=" selected";}else{$selected="";}
		$filter.="<option value=\"Sail / Power\"".$selected.">Sail / Power (still has rig)</option>\n"; 
		$option="Sail only";	
		if($option && $option==$assetpropulsion){$selected=" selected";}else{$selected="";}
		$filter.="<option value=\"Sail only\"".$selected.">Sail only (rig with no engine)</option>\n"; 
		$filter.="</select></div></div>\n";
		
		$filter.="<div class=\"row-fluid\"><div class=\"span3\">Search text<br><input class=\"formcontrol\" name=\"assetsearchtext\" type=\"text\" value=\"$assetsearchtext\" size=\"30\" onKeyPress=\"return assetsearchenter(this,event)\"></div></div>\n";
		
		if($admin=="open"){
			$filter.="<div class=\"span6\"><i>Admin filter:<br></i><select class='formcontrol' name='assetadminstatus'>\n";
			if($assetadminstatus=="0"){$selected0=" selected";}else{$selected0="";}
			$filter.="<option $selected0 value=\"0\">All</option>\n";
			if($assetadminstatus=="1"){$selected1=" selected";}else{$selected1="";}
			$filter.="<option $selected1 value=\"1\">Member owner</option>\n";
			if($assetadminstatus=="2"){$selected2=" selected";}else{$selected2="";}
			$filter.="<option $selected2 value=\"2\">Detached to Admin</option>\n";
			$filter.="</select></div>\n";
		}
		//AssetCategory=14 AIS
		if($AIS==1){
			$filter.="<div class=\"row-fluid\"><div class=\"span6\">AIS or other position monitoring <input type=\"checkbox\" name=\"AIS\" value=\"1\" checked></div></div>\n";
		}else{
			$filter.="<div class=\"row-fluid\"><div class=\"span6\">AIS or other position monitoring <input type=\"checkbox\" name=\"AIS\" value=\"1\"></div></div>\n";
		}
		
		

		$filter.="</div><div uk-grid><div id='search' class=\"row-fluid\"><div class=\"span6\"><b>Search</b><br><input type=\"button\" class=\"btn btn-primary button_action\" name=\"list_1\" value=\"Whose barge?\" onClick=\"document.form.assetaction.value='list_1';document.form.submit()\">";		
		$filter.="<input type=\"button\" class=\"btn btn-primary button_action\" name=\"list_2\" value=\"Full register\" onClick=\"document.form.assetaction.value='list_2';document.form.submit()\"></div></div>\n";

		//$filter.="</table>\n";
		
		echo "<div uk-grid>";
		echo($filter);
		echo "</div>";
		echo("</td></tr>\n");
		
		if ($assetaction!=""){
			//work out where clause from filter
			//'assetyearfrom','assetyearto','assetlengthfrom','assetlengthto','assetclass','assetpropulsion'
			$filterquery="";
			$filterset="";
			if(empty($assetyearfrom) && empty($assetyearto)){
				$filterset="0";
				$filterquery.="year >= $assetyearfrom && year <= $assetyearto <br>";
			}else{
				//we have a year range
				if(empty($assetyearto)){
					$assetyearto=date("Y",time()); 
				}
				if(empty($assetyearfrom)){
					$assetyearfrom=1850; 
				}
				if($assetyearto>=$assetyearfrom){
					//range selection increasing
					$filterquery.="year >= $assetyearfrom && year <= $assetyearto <br>";
				}else{
					//range selection decreasing
					$filterquery.="year >= $assetyearto && year <= $assetyearfrom <br>";
				}
				$filterset="1";
			}
			if(empty($assetlengthfrom) && empty($assetlengthto)){
				$filterset.="0";
				$filterquery.="length >= $assetlengthfrom && length <=$assetlengthto <br>";
			}else{
				//we have a length range
				if(empty($assetlengthto)){
					$assetlengthto=40; 
				}
				if(empty($assetlengthfrom)){
					$assetlengthfrom=10; 
				}
		
				if($assetlengthto>=$assetlengthfrom){
					//range selection increasing
					$filterquery.="length >= $assetlengthfrom && length <=$assetlengthto <br>";
				}else{
					//range selection decreasing
					$filterquery.="length >= $assetlengthto && length <=$assetlengthfrom <br>";
				}
				$filterset.="1";
			}
		
			if($assetclass=="*"){
				$filterset.="0";
				$filterquery.="class = $assetclass <br>";
			}else{
				//we have a class
				$filterquery.="class = $assetclass <br>";
				$filterset.="1";
			}	
			if($assetpropulsion=="*"){
				$filterset.="0";
				$filterquery.="propulsion = $assetpropulsion <br>";
			}else{
				//we have a propulsion
				$filterquery.="propulsion = $assetpropulsion <br>";
				$filterset.="1";
			}
			if($AIS=="1"){
				$filterset.="1";
				$filterquery.="AIS = include <br>";
			}else{
				//we have a propulsion
				$filterquery.="AIS = no <br>";
				$filterset.="0";
			}			
		
			//variables for latest updates since secsinterval
			$secs_interval=2678400; //last month
		
			$secs_now = time();
			
			if($assetsearchtext){
				//we have some search text
				$search = trim(strtolower($assetsearchtext));
				$search = preg_replace("/\s+/", " ", $search); 
				$keywords = explode(" ", $search); 
				//Clean empty arrays so they don�t get every row as result
				$keywords = array_diff($keywords, array("")); 
				$filterset.="1";
			}else{
				$filterset.="0";
			}
		
			//start of listing
			$sort_col="<img src='/Image/common/arrow_down.gif' alt='Sorted on this column. Click another heading to re-sort' title='Sorted on this column. Click another heading to re-sort' width='9' height='6' border='0'>";
			
			if(!$assetsort){
				$assetsort=1;
			}
			${"sort_col".$assetsort}=$sort_col;
		
			$rowcount=1;
			//$search=$assetsearchtext;
			
			$query = $db->getQuery(true)
				->select('a.*')
				->select($db->qn('am.Status'))
				->from($db->qn('tblAssets', 'a'))
				->innerJoin($db->qn('tblAssetsMembers', 'am').' ON '.$db->qn('a.VesselID').' = '.$db->qn('am.ID'))
				->order($db->qn(['a.VesselID', 'a.AssetID']));

			if($assetadminstatus=="1"){
				//owner member
				$query->where($db->qn('am.Status').' = 1');
			}
			if($assetadminstatus=="2"){
				//detached
				$query->where($db->qn('am.Status').' = 2');
			}
			
			$assets = $db->setQuery($query)->loadAssocList();
			
			# If the search was unsuccessful then Display Message try again.
			if (empty($assets)){
				$assetinfo.="<td class=$rowclass colspan=6>No further information on this barge</td>\n";
			}else{
				$rows = count($assets);
				$thisvesselID=0;
				$maillist="";
				foreach($assets as $row) {
					# Display asset Results, l
					$AssetID = stripslashes($row["AssetID"]);
					$VesselID = stripslashes($row["VesselID"]);
					$AssetCategory = stripslashes($row["AssetCategory"]);		
					$AssetTitle = stripslashes($row["AssetTitle"]);
					$AssetDescription = $row["AssetDescription"] !== null ? stripslashes($row["AssetDescription"]) : '';
					$AssetLastUpdate = $row["AssetLastUpdate"] !== null ? stripslashes($row["AssetLastUpdate"]) : '';
					if($VesselID!=$thisvesselID){
						if($filterset!="000000"){
							//Next vessel so check concatenated fields fro last against filter		
							
							//year assetyearfrom','assetyearto'
							
							if($SearchYear>=$assetyearfrom && $SearchYear<=$assetyearto){
								//match
								$filtermatch="1";
							}else{
								$filtermatch="0";
							}
							
							//length 'assetlengthfrom','assetlengthto'
							if($SearchLength>=$assetlengthfrom && $SearchLength<=$assetlengthto){
								//match
								$filtermatch.="1";
							}else{
								$filtermatch.="0";
							}
							
							//class 'assetclass'
							if($assetclass!="0" && $SearchClass==$assetclass){
								$filtermatch.="1";
							}else{
								$filtermatch.="0";
							}
							
							
							//propulsion 'assetpropulsion',
							if($assetpropulsion!="0" && $SearchPropulsion==$assetpropulsion){
								$filtermatch.="1";
							}else{
								$filtermatch.="0";
							}		
							
							//AIS,
							if($AIS==1 && $SearchAIS>0){
								$filtermatch.="1";
							}else{
								$filtermatch.="0";
							}					
			
							if($search){
								for ($i=0; $i<count($keywords); $i++) {
									//if(fnmatch("*$keywords[$i]*", $searchindex)) {
									$pos = strpos ($searchindex, $keywords[$i]);
									if($pos!==false){
										$found+=1;
									}
								}
								if($found==$i){
									$filtermatch.="1";
								}else{
									$filtermatch.="0";
								}
							}else{
								$filtermatch.="0";
							}

							//if allmatch add the vessel id into col 0 to validate it as found
							if($filterset==$filtermatch){
								$resultrow[$rowcount][0]=$thisvesselID;
								$foundrows += 1;
								$foundmem=$found;
								$found=0;
								
								//if list_1 then we need owner name
								if($assetaction=="list_1"){
									if($thisvesselID){
										$fullname="";
										$query = $db->getQuery(true)
											->select('m.*')
											->select($db->qn(['am.ID', 'am.Status']))
											->from($db->qn('tblMembers', 'm'))
											->innerJoin($db->qn('tblAssetsMembers', 'am').' ON '.$db->qn('m.MembershipNo').' = '.$db->qn('am.MembershipNo'))
											->where($db->qn('am.ID').' = '.$db->q($thisvesselID));
										$memberrow = $db->setQuery($query)->loadAssoc();
										if (!empty($memberrow)) {	
											$MembershipNo=$memberrow["MembershipNo"];
											$Status=$memberrow["Status"];
											if($Status==2){
												//vessel in limbo under DBA admin keeper
												$fullname="Ex member - DBA holding records";
											}elseif($MembershipNo){
												$asset_login_memberid=$memberrow["ID"];
												$Services=$memberrow["Services"];
												$MemStatus=$memberrow["MemStatus"];
												if($MemStatus==2 || $MemStatus==3 || $MemStatus==4|| $MemStatus==6){
													//paid up member, renewal due, gone away or FOC
													//OK to show name if $Services includes |1|
													$pos = strpos ($Services, "|1|");
													//$debug="Services: $Services pos:$pos ms:$MemStatus $MembershipNo";
													if($pos>-1){
														//Get details and place first member surname first for sort order
														$fullname="";
														if($memberrow["LastName"]){
															$fullname=$memberrow["LastName"];
														}
														if($memberrow["LastName2"] && ($memberrow["LastName2"] != $memberrow["LastName"])){
															$fullname.=" / ".$memberrow["LastName2"];
														}
														if($memberrow["FirstName"]){
															if($fullname){
																$fullname.=", ".$memberrow["FirstName"];
															}else{
																$fullname=$memberrow["FirstName"];
															}
														}
														if($memberrow["FirstName2"]){
															if($fullname){
																$fullname.=" & ".$memberrow["FirstName2"];
															}else{
																$fullname=$memberrow["LastName"];
															}
														}
														if($admin=="open"){
															//add to maillist for admin contact manager unless opting out of email contact service=35
															$pos = strpos ($Services, "|35|");
															if($pos>-1){
																$maillist.="_".$memberrow["ID"];
															}
														}
														if(!$fullname){
															$fullname="Unknown";
														}
													}else{
														//keeper wants to remain annonymouse
														$fullname="Wishes to remain anonymous";
													}	
												}else{
													//keeper not paid up
													$fullname="Ex member - DBA holding records";
												}
											}else{
												//keeper not found
												$fullname="Ex member - DBA holding records";
											}		
										}else{
											//keeper not found in members table
											$fullname="Ex member - DBA holding records";
										}
									}else{
										$fullname="Unknown";
									}
									$resultrow[$rowcount]["Keeper"]=$fullname;
								}
							}
						}else{
							//no filter so add it anyway
							$resultrow[$rowcount][0]=$thisvesselID;
							//if list_1 then we need owner name
							if($assetaction=="list_1"){
								if($thisvesselID){
									$fullname="";
									$query = $db->getQuery(true)
										->select('m.*')
										->select($db->qn(['am.ID', 'am.Status']))
										->from($db->qn('tblMembers', 'm'))
										->innerJoin($db->qn('tblAssetsMembers', 'am').' ON '.$db->qn('m.MembershipNo').' = '.$db->qn('am.MembershipNo'))
										->where($db->qn('am.ID').' = '.$db->q($thisvesselID));
									$memberrow = $db->setQuery($query)->loadAssoc();
									if (!empty($memberrow)) {	
										$MembershipNo=$memberrow["MembershipNo"];
										$Status=$memberrow["Status"];
										if($Status==2){
											//vessel in limbo under DBA admin keeper
											$fullname="Ex member - DBA holding records";
										}elseif($MembershipNo){
											$asset_login_memberid=$memberrow["ID"];
											$Services=$memberrow["Services"];
											$MemStatus=$memberrow["MemStatus"];
											if($MemStatus==2 || $MemStatus==3 || $MemStatus==4|| $MemStatus==6){
												//paid up member, renewal due, gone away or FOC
												//OK to show name if $Services includes |1|
												$pos = strpos ($Services, "|1|");
												//$debug="Services: $Services pos:$pos ms:$MemStatus $MembershipNo";
												if($pos>-1){
													//Get details and place first member surname first for sort order
													$fullname="";
													if($memberrow["LastName"]){
														$fullname=$memberrow["LastName"];
													}
													if($memberrow["LastName2"] && ($memberrow["LastName2"] != $memberrow["LastName"])){
														$fullname.=" / ".$memberrow["LastName2"];
													}
													if($memberrow["FirstName"]){
														if($fullname){
															$fullname.=", ".$memberrow["FirstName"];
														}else{
															$fullname=$memberrow["FirstName"];
														}
													}
													if($memberrow["FirstName2"]){
														if($fullname){
															$fullname.=" & ".$memberrow["FirstName2"];
														}else{
															$fullname=$memberrow["LastName"];
														}
													}
													if($admin=="open"){
														//add to maillist for admin contact manager unless opting out of email contact service=35
														$pos = strpos ($Services, "|35|");
														if($pos>-1){
															$maillist.="_".$memberrow["ID"];
														}
													}
													if(!$fullname){
														$fullname="Unknown";
													}
												}else{
													//keeper wants to remain annonymouse
													$fullname="Wishes to remain anonymous";
												}	
											}else{
												//keeper not paid up
												$fullname="Ex member - DBA holding records";
											}
										}else{
											//keeper not found
											$fullname="Ex member - DBA holding records";
										}		
									}else{
										//keeper not found in members table
										$fullname="Ex member - DBA holding records";
									}
								}else{
									$fullname="Unknown";
								}
								$resultrow[$rowcount]["Keeper"]=$fullname;
							}
							$foundrows += 1;
							$foundmem=$found;
							$found=0;
						}
						//reset for the next set of data
						//debug col
						//$resultrow[$rowcount][15]="$foundmem $i $searchindex";
		
						$found=0;
						$rowcount+=1;
						$thisvesselID=$VesselID;
						$last_my_secs=0;
						$searchindex="";
						$SearchYear="0";
						$SearchLength="0";
						$SearchClass="";
						$SearchPropulsion="";
						$SearchAIS="";
						
						$debug.="[".$filterset.":".$filtermatch."] ";
						
						$filtermatch="";
					}
					if($search != NULL){
						//add together the two search fields into the searchindex
						$searchindex.=" ".strtolower($AssetTitle)." ".strtolower($AssetDescription)." ref".$VesselID;
					}	
					//extract temp values from filter keys
					switch ($AssetCategory) {
						case "2":
							$SearchPropulsion=$AssetTitle;
							break;
						case "3":
							$SearchClass=$AssetTitle;
							break;
						case "4":
							$SearchLength=$AssetTitle;
							break;
						case "7":
							$SearchYear=$AssetTitle;
							break;
						case "14":
							$SearchAIS=$AssetTitle;
							break;
					}
					
		
					//workout how old the data is since last update
					//$secs_interval
					//$secs_now
					
					//list ($myDate, $myTime) = explode (' ', $row["AssetLastUpdate"]);
					//list ($myyear, $mymonth, $myday) = explode ('-', $myDate);
					//list ($myhour, $mymin, $mysec) = explode (':', $myTime);
					$my_secs = ($row["AssetLastUpdate"] !== null) ? strtotime($row["AssetLastUpdate"]) : 0;
					if($my_secs>=$last_my_secs){
						//latest so far
						$resultrow[$rowcount]['lastupdate']=$my_secs;
						$last_my_secs=$my_secs;
					}	
		
					//add the data into the array for each field
					$resultrow[$rowcount][$AssetCategory]=$AssetTitle;
					
					//echo("- $rowcount, $AssetCategory $VesselID ".$row["AssetTitle"]." <br>");
				}
				//check last row
				if($filterset!="000000"){
					//Last vessel so check concatenated fields fro last against filter		
					//year assetyearfrom','assetyearto'
					if($SearchYear>=$assetyearfrom && $SearchYear<=$assetyearto){
						//match
						$filtermatch="1";
					}else{
						$filtermatch="0";
					}
					
					//length 'assetlengthfrom','assetlengthto'
					if($SearchLength>=$assetlengthfrom && $SearchLength<=$assetlengthto){
						//match
						$filtermatch.="1";
					}else{
						$filtermatch.="0";
					}
					
					//class 'assetclass'
					if($assetclass!="0" && $SearchClass==$assetclass){
						$filtermatch.="1";
					}else{
						$filtermatch.="0";
					}
					
					
					//propulsion 'assetpropulsion',
					if($assetpropulsion!="0" && $SearchPropulsion==$assetpropulsion){
						$filtermatch.="1";
					}else{
						$filtermatch.="0";
					}		
					
					//AIS,
					if($AIS==1 && $SearchAIS>0){
						$filtermatch.="1";
					}else{
						$filtermatch.="0";
					}			
	
					if($search){
						for ($i=0; $i<count($keywords); $i++) {
							//if(fnmatch("*$keywords[$i]*", $searchindex)) {
							$pos = strpos ($searchindex, $keywords[$i]);
							if($pos!==false){
								$found+=1;
							}
						}
						if($found==$i){
							$filtermatch.="1";
						}else{
							$filtermatch.="0";
						}
					}else{
						$filtermatch.="0";
					}

					//if allmatch add the vessel id into col 0 to validate it as found
					if($filterset==$filtermatch){
						$resultrow[$rowcount][0]=$thisvesselID;
						$foundrows += 1;
						$foundmem=$found;
						$found=0;
						
						//if list_1 then we need owner name
						if($assetaction=="list_1"){
							if($thisvesselID){
								$fullname="";
								$query = $db->getQuery(true)
									->select('m.*')
									->select($db->qn(['am.ID', 'am.Status']))
									->from($db->qn('tblMembers', 'm'))
									->innerJoin($db->qn('tblAssetsMembers', 'am').' ON '.$db->qn('m.MembershipNo').' = '.$db->qn('am.MembershipNo'))
									->where($db->qn('am.ID').' = '.$db->q($thisvesselID));
								$memberrow = $db->setQuery($query)->loadAssoc();
								if (!empty($memberrow)) {	
									$MembershipNo=$memberrow["MembershipNo"];
									$Status=$memberrow["Status"];
									if($Status==2){
										//vessel in limbo under DBA admin keeper
										$fullname="Ex member - DBA holding records";
									}elseif($MembershipNo){
										$asset_login_memberid=$memberrow["ID"];
										$Services=$memberrow["Services"];
										$MemStatus=$memberrow["MemStatus"];
										if($MemStatus==2 || $MemStatus==3 || $MemStatus==4|| $MemStatus==6){
											//paid up member, renewal due, gone away or FOC
											//OK to show name if $Services includes |1|
											$pos = strpos ($Services, "|1|");
											//$debug="Services: $Services pos:$pos ms:$MemStatus $MembershipNo";
											if($pos>-1){
												//Get details and place first member surname first for sort order
												$fullname="";
												if($memberrow["LastName"]){
													$fullname=$memberrow["LastName"];
												}
												if($memberrow["LastName2"] && ($memberrow["LastName2"] != $memberrow["LastName"])){
													$fullname.=" / ".$memberrow["LastName2"];
												}
												if($memberrow["FirstName"]){
													if($fullname){
														$fullname.=", ".$memberrow["FirstName"];
													}else{
														$fullname=$memberrow["FirstName"];
													}
												}
												if($memberrow["FirstName2"]){
													if($fullname){
														$fullname.=" & ".$memberrow["FirstName2"];
													}else{
														$fullname=$memberrow["LastName"];
													}
												}
												if($admin=="open"){
													//add to maillist for admin contact manager unless opting out of email contact service=35
													$pos = strpos ($Services, "|35|");
													if($pos>-1){
														$maillist.="_".$memberrow["ID"];
													}
												}
												if(!$fullname){
													$fullname="Unknown";
												}
											}else{
												//keeper wants to remain annonymouse
												$fullname="Wishes to remain anonymous";
											}	
										}else{
											//keeper not paid up
											$fullname="Ex member - DBA holding records";
										}
									}else{
										//keeper not found
										$fullname="Ex member - DBA holding records";
									}		
								}else{
									//keeper not found in members table
									$fullname="Ex member - DBA holding records";
								}
							}else{
								$fullname="Unknown";
							}
							$resultrow[$rowcount]["Keeper"]=$fullname;
						}
					}
				}else{
					//no filter so add it anyway
					$resultrow[$rowcount][0]=$thisvesselID;
					//if list_1 then we need owner name
					if($assetaction=="list_1"){
						if($thisvesselID){
							$fullname="";
							$query = $db->getQuery(true)
								->select('m.*')
								->select($db->qn(['am.ID', 'am.Status']))
								->from($db->qn('tblMembers', 'm'))
								->innerJoin($db->qn('tblAssetsMembers', 'am').' ON '.$db->qn('m.MembershipNo').' = '.$db->qn('am.MembershipNo'))
								->where($db->qn('am.ID').' = '.$db->q($thisvesselID));
							$memberrow = $db->setQuery($query)->loadAssoc();
							if (!empty($memberrow)) {	
								$MembershipNo=$memberrow["MembershipNo"];
								$Status=$memberrow["Status"];
								if($Status==2){
									//vessel in limbo under DBA admin keeper
									$fullname="Ex member - DBA holding records";
								}elseif($MembershipNo){
									$asset_login_memberid=$memberrow["ID"];
									$Services=$memberrow["Services"];
									$MemStatus=$memberrow["MemStatus"];
									if($MemStatus==2 || $MemStatus==3 || $MemStatus==4|| $MemStatus==6){
										//paid up member, renewal due, gone away or FOC
										//OK to show name if $Services includes |1|
										$pos = strpos ($Services, "|1|");
										//$debug="Services: $Services pos:$pos ms:$MemStatus $MembershipNo";
										if($pos>-1){
											//Get details and place first member surname first for sort order
											$fullname="";
											if($memberrow["LastName"]){
												$fullname=$memberrow["LastName"];
											}
											if($memberrow["LastName2"] && ($memberrow["LastName2"] != $memberrow["LastName"])){
												$fullname.=" / ".$memberrow["LastName2"];
											}
											if($memberrow["FirstName"]){
												if($fullname){
													$fullname.=", ".$memberrow["FirstName"];
												}else{
													$fullname=$memberrow["FirstName"];
												}
											}
											if($memberrow["FirstName2"]){
												if($fullname){
													$fullname.=" & ".$memberrow["FirstName2"];
												}else{
													$fullname=$memberrow["LastName"];
												}
											}
											if($admin=="open"){
												//add to maillist for admin contact manager unless opting out of email contact service=35
												$pos = strpos ($Services, "|35|");
												if($pos>-1){
													$maillist.="_".$memberrow["ID"];
												}
											}
											if(!$fullname){
												$fullname="Unknown";
											}
										}else{
											//keeper wants to remain annonymouse
											$fullname="Wishes to remain anonymous";
										}	
									}else{
										//keeper not paid up
										$fullname="Ex member - DBA holding records";
									}
								}else{
									//keeper not found
									$fullname="Ex member - DBA holding records";
								}		
							}else{
								//keeper not found in members table
								$fullname="Ex member - DBA holding records";
							}
						}else{
							$fullname="Unknown";
						}
						$resultrow[$rowcount]["Keeper"]=$fullname;
					}
					$foundrows += 1;
					$foundmem=$found;
					$found=0;
				}
			}
			if($foundrows){	
				//sort the array if required
				if($assetsort=="lastupdate"){
					//lastupdate sort
					//$col="Lastupdate";
					$a = sort_array($resultrow, $col, SORT_DESC);
				}else{
					$col=$assetsort;			
					$a = sort_array($resultrow, $col, SORT_ASC);
				}
		
				if($assetaction=="list_1"){
					//whos barge simplelist
					$listrow=0;
			
			
					
					
					
					
					$listresults="<tr><td class=list_small><a href=\"#\" onClick=\"document.form.assetaction.value='list_1';document.form.assetsort.value='1';document.form.submit()\"><b>Barge Name</b> ".$sort_col1."</a></td>\n";		
					$listresults.="<td class=list_small><a href=\"#\" onClick=\"document.form.assetaction.value='list_1';document.form.assetsort.value='Keeper';document.form.submit()\"><b>Keeper</b> ".$sort_col3."</a></td>\n";		
					$listresults.="<td class=list_small><a href=\"#\" onClick=\"document.form.assetaction.value='list_1';document.form.assetsort.value='15';document.form.submit()\">".$sort_col15."</a></td></tr>\n";
				
					while($listrow<=$rowcount){
						if(isset($a[$listrow][0])){	
							if($a[$listrow][0]>0){
								//valid vessel id so include it
								if($thisrow=="odd"){
									$rowclass="table_stripe_odd";
									$thisrow="even";
								}else{
									$rowclass="table_stripe_even";		
									$thisrow="odd";
								}	
								
								
								
								if($admin=="open"){
									$listresults.="<tr><td class=$rowclass><a href=\"#\" onClick=\"document.form.assetaction.value='editdetail';document.form.vesselid.value='".$a[$listrow][0]."';document.form.submit()\">".$a[$listrow][1]."</a></td>\n";
								}else{
									$listresults.="<tr><td class=$rowclass><a href=\"#\" onClick=\"document.form.assetaction.value='detail';document.form.vesselid.value='".$a[$listrow][0]."';document.form.submit()\">".$a[$listrow][1]."</a></td>\n";							
								}
								$listresults.="<td class=$rowclass>".$a[$listrow]["Keeper"]."</td>\n";
								//$listresults.="<td class=$rowclass>".$a[$listrow]["Lastupdate"]."</td></tr>\n";
								$mysecs=$a[$listrow]["lastupdate"];
								if($secs_now-$mysecs<$secs_interval){
									//recent update
									$latestupdate=date('d-m-Y', $mysecs);
				
									$listresults.="<td class=$rowclass><img src=\"Image/common/new_register.gif\" width=\"28\" height=\"11\" border=\"0\" alt=\"Recent update $latestupdate\" title=\"Recent update $latestupdate\"></td></tr>\n";						
								}else{
									$listresults.="<td class=$rowclass></td></tr>\n";
								}	
							}
						}
						$listrow+=1;
					}
		
				}else{
					//full list
					
					$listrow=0;
				
					$listresults="<tr><td class=list_small><a href=\"#\" onClick=\"document.form.assetaction.value='list';document.form.assetsort.value='1';document.form.submit()\"><b>Barge Name</b> ".$sort_col1."</a></td>\n";		
					$listresults.="<td class=list_small><a href=\"#\" onClick=\"document.form.assetaction.value='list';document.form.assetsort.value='3';document.form.submit()\"><b>Class</b> ".$sort_col3."</a></td>\n";		
					$listresults.="<td class=list_small><a href=\"#\" onClick=\"document.form.assetaction.value='list';document.form.assetsort.value='2';document.form.submit()\"><b>Propulsion</b> ".$sort_col2."</a></td>\n";		
					$listresults.="<td class=list_small><a href=\"#\" onClick=\"document.form.assetaction.value='list';document.form.assetsort.value='7';document.form.submit()\"><b>Built</b> ".$sort_col7."</a></td>\n";		
					$listresults.="<td class=list_small><a href=\"#\" onClick=\"document.form.assetaction.value='list';document.form.assetsort.value='4';document.form.submit()\"><b>L (m)</b> ".$sort_col4."</a></td>\n";		
					$listresults.="<td class=list_small><a href=\"#\" onClick=\"document.form.assetaction.value='list';document.form.assetsort.value='5';document.form.submit()\"><b>B (m)</b> ".$sort_col5."</a></td>\n";
					$listresults.="<td class=list_small><a href=\"#\" onClick=\"document.form.assetaction.value='list';document.form.assetsort.value='15';document.form.submit()\">".$sort_col15."</a></td>\n";
					$listresults.="<td></td>";
					$listresults.="</tr>\n";
			
			
					while($listrow<=$rowcount){
						if(isset($a[$listrow][0])){	
							if($a[$listrow][0]>0){
								//valid vessel id so include it
								if($thisrow=="odd"){
									$rowclass="table_stripe_odd";
									$thisrow="even";
								}else{
									$rowclass="table_stripe_even";		
									$thisrow="odd";
								}		
								if($admin=="open"){
									$listresults.="<tr><td class=$rowclass><a href=\"#\" onClick=\"document.form.assetaction.value='editdetail';document.form.vesselid.value='".$a[$listrow][0]."';document.form.submit()\">".$a[$listrow][1]."</a></td>\n";
								}else{
									$listresults.="<tr><td class=$rowclass><a href=\"#\" onClick=\"document.form.assetaction.value='detail';document.form.vesselid.value='".$a[$listrow][0]."';document.form.submit()\">".$a[$listrow][1]."</a></td>\n";							
								}
								if(isset($a[$listrow][3])){	
									$listresults.="<td class=$rowclass>".$a[$listrow][3]."</td>\n";
								}else{
									$listresults.="<td class=$rowclass></td>\n";
								}
								if(isset($a[$listrow][2])){	
									$listresults.="<td class=$rowclass>".$a[$listrow][2]."</td>\n";
								}else{
									$listresults.="<td class=$rowclass></td>\n";
								}
								if(isset($a[$listrow][7])){	
									$listresults.="<td class=$rowclass>".$a[$listrow][7]."</td>\n";
								}else{
									$listresults.="<td class=$rowclass></td>\n";
								}
								if(isset($a[$listrow][4])){	
									$listresults.="<td class=$rowclass>".$a[$listrow][4]."</td>\n";
								}else{
									$listresults.="<td class=$rowclass></td>\n";
								}
								if(isset($a[$listrow][5])){	
									$listresults.="<td class=$rowclass>".$a[$listrow][5]."</td>\n";
								}else{
									$listresults.="<td class=$rowclass></td>\n";
								}
								/*$listresults.="<td class=$rowclass>".$a[$listrow]["Lastupdate"]."</td></tr>\n";
								$mysecs=$a[$listrow]["lastupdate"];
								if($secs_now-$mysecs<$secs_interval){
									//recent update
									$latestupdate=date('d-m-Y', $mysecs);
				
									$listresults.="<td class=$rowclass><img src=\"Image/common/new_register.gif\" width=\"28\" height=\"11\" border=\"0\" alt=\"Recent update $latestupdate\" title=\"Recent update $latestupdate\"></td></tr>\n";						
								}else{
									$listresults.="<td class=$rowclass></td>\n";
								}
								*/
								$listresults.="<td></td>";
								$listresults.="</tr>\n";	
							}
						}
						$listrow+=1;
					}
				}
			}
		
			if(empty($foundrows)){
				echo("<tr><td colspan=7 class=list_small><b>There were no records found matching your search</b> - <i>try some different search words</i></td></tr>\n");
			}else{
				if($foundrows==1){
					echo("<tr><td colspan=7 class=list_small><b>$foundrows record found</b> - <i>Click on the barge name for full details</i></td></tr>\n");
				}else{
					echo("<tr><td colspan=7 class=list_small><b>$foundrows records found</b> - <i>Click on a barge name for full details</i> or on a column heading to sort the list");
					//echo(" or <a href=\"#\" onClick=\"document.form.assetaction.value='list';document.form.assetsort.value='lastupdate';document.form.submit()\"><b>here to sort by latest updates </b><img src=\"Image/common/new.gif\" width=\"28\" height=\"11\" border=\"0\" alt=\"Recent update $latestupdate\" title=\"Recent update $latestupdate\"></a></td></tr>\n");
					if($maillist!="" && $admin=="open"){
						//admin email button
						echo(" <input type=button value='Admin email' onClick='javascript:doreport()' name='button'>");
					}
					echo("</td></tr>\n");
		
				}
			}
		
			echo($listresults);	
		}
		//$debug.="<br>".$filterquery." - ".$filterset." - ".$foundrows." - ".$maillist;
		//echo("<tr><td colspan=7 class=list_small>Debug: $debug</td></tr>\n");
		?>
        <SCRIPT LANGUAGE="JavaScript">
        function doreport(){
			alert("ok");
			var maillist = "_199_";
			var	wheresql = "";
			var criteria = "1";
			var docname = "/components/com_membership/views/search/tmpl/maillist_email.php?var=1";
			var mypage = docname+"&maillist="+maillist+"&criteria="+criteria;			
			var myname = "Reports";
			var w = 800;
			var h = 600;
			var scroll = "yes";
			var winl = (screen.width - w) / 2;
			var wint = (screen.height - h) / 2;
			winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scroll+',resizable'
			mypage += (mypage.indexOf('?') != -1 ? '&' : '?') + 'nocache=' + Date.now();
			win = window.open(mypage, myname, winprops)
			if (parseInt(navigator.appVersion) >= 4) { win.window.focus(); }
			
		}
		</script>
 
       <?php
		echo("</table>");
	}
}
//---------------------------------------asset details---------------------------------------------

if ($assetaction=="detail") {

	//store temp filter settings

	echo("<input name=\"assetyearfrom\" type=\"hidden\" value=\"$assetyearfrom\">\n");
	echo("<input name=\"assetyearto\" type=\"hidden\" value=\"$assetyearto\">\n");
	echo("<input name=\"assetlengthfrom\" type=\"hidden\" value=\"$assetlengthfrom\">\n");
	echo("<input name=\"assetlengthto\" type=\"hidden\" value=\"$assetlengthto\">\n");
	echo("<input name=\"assetclass\" type=\"hidden\" value=\"$assetclass\">\n");
	echo("<input name=\"assetpropulsion\" type=\"hidden\" value=\"$assetpropulsion\">\n");
	echo("<input name=\"assetAIS\" type=\"hidden\" value=\"$assetAIS\">\n");
	echo("<input name=\"assetlisttype\" type=\"hidden\" value=\"$assetlisttype\">\n");
	echo("<input name=\"asset_login_memberid\" type=\"hidden\" value=\"$asset_login_memberid\">\n");
	echo("<input name=\"assetadminstatus\" type=\"hidden\" value=\"$assetadminstatus\">\n");
	echo("<input name=\"assetsearchtext\" type=\"hidden\" value=\"$assetsearchtext\">\n");


	echo("<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"1\">");
	
	//lookup current owner 
	if($vesselid){
		$fullname="";
		$showname=0;
		$query = $db->getQuery(true)
			->select('m.*')
			->select($db->qn(['am.Status', 'am.Keeper']))
			->from($db->qn('tblMembers', 'm'))
			->innerJoin($db->qn('tblAssetsMembers', 'am').' ON '.$db->qn('m.MembershipNo').' = '.$db->qn('am.MembershipNo'))
			->where($db->qn('am.ID').' = '.$db->q($vesselid));
		$memberrow = $db->setQuery($query)->loadAssoc();
		if (!empty($memberrow)) {	
			$MembershipNo=$memberrow["MembershipNo"];
			$memid=$memberrow["ID"];
			$Status=$memberrow["Status"];
			$DateJoined=$memberrow["DateJoined"];
			$DatePaid=$memberrow["DatePaid"];
			$Country=$memberrow["Country"];
			$Telephone1=$memberrow["Telephone1"];
			$Telephone2=$memberrow["Telephone2"];
			$Email=$memberrow["Email"];
			$Keepers=$memberrow["Keeper"];
			$LastUpdate=$memberrow["LastUpdate"];
			
			if($Status==2){
				//vessel in limbo under DBA admin keeper
				$fullname="Ex member - DBA holding records";
			}elseif($MembershipNo){
				$asset_login_memberid=$memberrow["ID"];
				$Services=$memberrow["Services"];
				$MemStatus=$memberrow["MemStatus"];
				if($MemStatus==2 || $MemStatus==3 || $MemStatus==4|| $MemStatus==6){
					//paid up member, renewal due, gone away or FOC
					//OK to show name if $Services includes |1|
					$pos = strpos ($Services, "|1|");
					//$debug="Services: $Services pos:$pos ms:$MemStatus $MembershipNo";
					if($pos>-1){
						//Get details and place first member surname first for sort order
						$showname=1;
						$fullname="";
						if($memberrow["FirstName"]){
							$fullname=$memberrow["FirstName"];
						}
						if($memberrow["LastName2"] && ($memberrow["LastName2"] != $memberrow["LastName"])){
							//different surnames
							$fullname.=" ".$memberrow["LastName"]." & ".$memberrow["FirstName2"]." ".$memberrow["LastName2"];
						}elseif($memberrow["LastName2"] && ($memberrow["LastName2"] == $memberrow["LastName"])){
							//same surname
							$fullname.=" & ".$memberrow["FirstName2"]." ".$memberrow["LastName"];
						}else{
							//no second member
							$fullname.=" ".$memberrow["LastName"];
						}
						
						if(!$fullname){
							$fullname="Unknown";
						}
					}else{
						//keeper wants to remain annonymouse
						$fullname="Wishes to remain anonymous";
					}	
				}else{
					//keeper not paid up
					$fullname="Ex member - DBA holding records";
				}
			}else{
				//keeper not found
				$fullname="Ex member - DBA holding records";
			}		
		}else{
			//keeper not found in members table
			$fullname="Ex member - DBA holding records";
		}
	}else{
		$fullname="Unknown";
	}
	//$fullname.=" ".$MembershipNo;
	$query = $db->getQuery(true)
		->select('a.*')
		->select($db->qn('ac.CatSort'))
		->from($db->qn('tblAssets', 'a'))
		->innerJoin($db->qn('tblAssetsCategories', 'ac').' ON '.$db->qn('a.AssetCategory').' = '.$db->qn('ac.CatID'))
		->where($db->qn('a.VesselID').' = '.$db->q($vesselid))
		->order($db->qn(['ac.CatSort', 'a.AssetTitle']));
	try {
		$result = $db->setQuery($query)->loadAssocList();
	} catch(Exception $e) {
		echo("Can't find assets");
		exit();
	}
	$num_rows = count($result);
	
	# If the search was unsuccessful then Display Message try again.
	if (empty($num_rows)) {
		echo("<tr><td class=list_small>Sorry - no details available for this barge<br><hr></td></tr>"); 
		exit();
	}

	$datenow = time();
	foreach($result as $row) {
		
		# Display asset Results, l
		$AssetID = stripslashes($row["AssetID"]);
		$VesselID = stripslashes($row["VesselID"]);
		$AssetCategory = stripslashes($row["AssetCategory"]);
		$AssetCategoryDesc = stripslashes($row["AssetCategoryDesc"]);				
		$AssetTitle = stripslashes($row["AssetTitle"]);
		$AssetDescription = stripslashes($row["AssetDescription"]);
		$AssetDate = stripslashes($row["AssetDate"]);
		$AssetLastUpdate = stripslashes($row["AssetLastUpdate"]);
		$AssetPrivacy = stripslashes($row["AssetPrivacy"]);
		if($admin=="open"){				
			$editlink="Admin <a href=\"#\" onClick=\"document.form.asset_login_memberid.value='".$asset_login_memberid."';document.form.assetaction.value='edit';document.form.assetid.value='$AssetID';document.form.submit()\"><img src=\"Image/common/open.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Edit this detail\" title=\"Edit this detail\"></a>";
		}else{
			$editlink="";
		}
		if($AssetCategory==1){
			$vesselname=$AssetTitle;
		}
		if($AssetCategory==14){
			$MMSI=$AssetTitle;
		} 
		if($AssetCategory==4){
			$length=$AssetTitle;
		}
		if($AssetCategory==5){
			$beam=$AssetTitle;
		}
		if($AssetCategory==3){
			$type=$AssetTitle;
		}
		if($AssetCategory==2){
			$propulsion=$AssetTitle;
		}
		if($AssetCategory==7){
			$yearbuilt=$AssetTitle;
		}			
		

		//$data[$AssetCategory] = stripslashes($row["AssetTitle"]);
		$rowclass="list_small";
		$detail.="<tr><td class=$rowclass valign=top colspan=2><b>".str_replace("_", " ", $AssetCategoryDesc).":</b>\n";
		$detail.=" ".nl2br($AssetTitle)."</td>\n";
		$detail.="<td class=$rowclass  valign=top>".$editlink."</td></tr>";		
		if($AssetDescription){
			$detail.="<tr><td colspan= class=$rowclass  valign=top>".nl2br($AssetDescription)."</td><td></td></tr>\n";
		}
		//echo("- $rowcount, $AssetCategory $VesselID ".$row["AssetTitle"]." <br>");

		$imagepath="Image/register/".$AssetID.".jpg";
		//add any images underneath 
 
		if (file_exists($imagepath)) {
			$imageInfo = getimagesize($imagepath);
			$imwidth = $imageInfo[0];
			$imheight = $imageInfo[1]; 
			$mediatitle=$AssetTitle;
			//$detail.="<tr><td colspan=3><img src=\"".$imagepath."\" width=".$imwidth." height=".$imheight." border=0 alt=\"".$mediatitle."\"  title=\"".$mediatitle."\"></td></tr>\n";  
			$detail.="<tr><td colspan=3><img src=\"".$imagepath."\" border=0 alt=\"".$mediatitle."\"  title=\"".$mediatitle."\"></td></tr>\n";  
		}
		$detail.="<tr><td colspan=3 ><hr></td></tr>\n";

	}
	if(empty($detailhelp)) $detailhelp = '';
	echo("<tr><td colspan=3 class='list_small_member'>$detailhelp</td></tr>\n");
	//echo("<tr><td colspan=3 class='list_small'><input type=\"button\" class=\"btn btn-primary\" name=\"list\" value=\"<span class='glyphicon glyphicon-hand-left'></span> Back to the list\" onClick=\"document.form.assetaction.value='list';document.form.submit()\"></td></tr>\n");
	echo("<tr><td colspan=3 class='list_small'><a href=\"javascript: onClick=document.form.assetaction.value='list';document.form.submit()\" class=\"btn btn-primary\"><span class=\"glyphicon glyphicon-hand-left\"></span> Back to the list</a></td></tr>\n");
	
	if($fullname){
		
		if($MembershipNo && ($MembershipNo==$login_MembershipNo)){
			//this vessel belongs to the logged-in member so offer edit shortcut
			$editlink="<a href=\"".$registerurl."&MyAccountaction=assets_update\"> edit here <img src=\"Image/common/icon_accdetail.gif\" width=16 height=17 border=0 alt=\"Add or edit barge register\"></a><br>\n";
			echo("<tr><td colspan=3 class='list_small'>Barge owner: <font color=#ff0000><b>".$fullname.". <br>This is currently registered as your own barge which you can ".$editlink."</b></font>. <br>If you think that the ownership has changed, please contact us <a href=\"".$contactpage."?subject=DBA Change of barge ownership&message=This is to report barge owner change ref no: ".$VesselID." ".$vesselname." from owner: ".$fullname." to ".$contact." Membership no.".$login_MembershipNo."&feedback_subject=Change of barge ownership\">here</a>. Barge ref no: ".$VesselID.", ".$vesselname."</td></tr>\n");
		}else{
			//$profile_link
			echo("<tr><td colspan=3 class='list_small'>Barge owner: ".$fullname.". If you think that the ownership has changed, please contact us <a href=\"".$contactpage."?subject=DBA Change of barge ownership&message=This is to report barge owner change ref no: ".$VesselID.", ".$vesselname." from owner: ".$fullname." to ".$contact." Membership no.".$login_MembershipNo."&feedback_subject=Change of barge ownership\">here</a>.<br>Barge ref no: ".$VesselID.", ".$vesselname."</td></tr>\n");
		}
	}else{
		echo("<tr><td colspan=3 class='list_small'>Barge owner: ".$fullname.". If you think that you are the owner or can help us to trace the owner, please contact us <a href=\"".$contactpage."?subject=DBA Change of barge ownership&message=This is to report barge owner change ref no: ".$VesselID.", ".$vesselname." from owner: ".$fullname." to ".$contact." Membership no.".$login_MembershipNo."&feedback_subject=Change of barge ownership\">here</a>.<br>Barge ref no: ".$VesselID.", ".$vesselname."</td></tr>\n");
	}

	
	//$showname=0;

	if($showname==1){
		//allow name link to PM discussion-forum/private-messaging?task=new&recip=7591
		// $mypm_url="<a href=\"discussion-forum/private-messaging?task=new&recip=".$asset_login_memberid."\"><img src=\"Image/common/email.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Send a private message to Keeper\" title=\"Send a private message to Keeper\"> ".$fullname."</a>";
		// echo("<tr><td colspan=3 class='list_small'>Contact the keeper ".$mypm_url."</td></tr>\n");
		echo '<tr><td colspan="3" class="list_small">To contact the Keeper, use <a href="/members/member-finder">Member Finder</a></td></tr>';
	}

	if($MMSI){
		//allow position to be shown
		$mylocation_url="<a href=\"members/bargeregister/bargeregister-search/?assetaction=position&MMSI=".$MMSI."\"><img src=\"Image/common/compass.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Try to find my current barge location on AIS Marinetraffic register\" title=\"Try to find my current barge location on AIS Marinetraffic register\">MyLocation</a>";
		//$maplink="http://maps.google.co.uk/maps?hl=en&ll=".$Position."&z=12";
		echo("<tr><td colspan=3 class='list_small'>For the latest AIS position click ".$mylocation_url."</td></tr>\n");
	}
	
	$showname=0;	
	//echo("<tr><td class=list_small><b>Info</b></td><td class=list_small></td><td class=list_small><b>Contact</b></td></tr>\n");
	echo("<tr><td class=list_small><b>Info</b></td><td class=list_small></td><td class=list_small></td></tr>\n");
	echo("<tr><td colspan=3 class='list_small'><hr></td></tr>\n");

	echo($detail);

	//$directlink="http://www.barges.org/main.php?section=".$section."&assetaction=detail&vesselid=".$vesselid;
	//echo("<tr><td colspan=3 class='list_small'>Use this link to send to a friend to go directly to this vessel<br><a href=\"".$directlink."\">".$directlink."</a></td></tr>\n");

	echo("</table>");
	

}

if($assetaction=="position"){
	if($MMSI){
		//load map
		?>
		<b>The location map below</b> is updated by volunteer local base stations that pick up AIS signals within their region of about 30km and relay this to a central website to display ship positions. 
		If you don't see the barge you wanted to at certain times, don't be too surprised as the coverage of the system is not guaranteed.
		If you have AIS and want to add you own barge, edit your <a href="members/bargeregister/bargeregister-edit">register entry here</a> and choose 'MMSI' from the 'Add a new detail' dropdown.  
		<br /><br />
		<script type="text/javascript">
		width='100%';          //the width of the embedded map in pixels or percentage
		height=550;         //the height of the embedded map in pixels or percentage
		border=0;           //the width of border around the map. Zero means no border
		notation=false;     //true or false to display or not the vessel icons and options at the left
		shownames=true;    //true or false to dispaly ship names on the map
		latitude=37.4460;   //the latitude of the center of the map in decimal degrees
		longitude=24.9467;  //the longitude of the center of the map in decimal degrees
		zoom=9;             //the zoom level of the map. Use values between 2 and 17
		maptype=3;          //use 0 for Normal map, 1 for Satellite, 2 for Hybrid, 3 for Terrain
		trackvessel=<?php echo($MMSI); ?>;      //the MMSI of the vessel to track, if within the range of the system
		fleet='';           //the registered email address of a user-defined fleet to display
		remember=false;     //true or false to remember or not the last position of the map
		</script>
		<script type="text/javascript" src="https://www.marinetraffic.com/ais/embed.js"></script>
	
		<br />
		<b>Quick Links to marinetraffic.com for more info on how to report your position:</b><br />
		<a class="data" target="_blank" href="https://www.marinetraffic.com/ais/freestation.aspx">Get an AIS receiver for free!</a><br />
		<a class="data" target="_blank" href="https://www.marinetraffic.com/ais/selfreporttext.aspx">Report your own position</a><br />
		<a class="data" target="_blank" href="https://www.marinetraffic.com/ais/datasheet.aspx?datasource=STATIONS">Receiving Stations</a><br /><br />



	<?php

	}else{
		echo("There is no MMSI number available to search for this ship.");
	}
}


if ($assetaction=="contact") {
	echo("<input name=\"assetsearchtext\" type=\"hidden\" value=\"$assetsearchtext\">\n");
	
	//lookup the details
	//lookup current keeper 
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblAssetsMembers'))
		->where($db->qn('ID').' = '.$db->q($vesselid));
	try {
		$memberrow = $db->setQuery($query)->loadAssocList();
	} catch(Exception $e) {
		echo("Can't find assets");
		exit();
	}
	$MembershipNo=$memberrow["MembershipNo"];
	$keeperemail=$memberrow["Email"];
	
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblAssets'))
		->where($db->qn('VesselID').' = '.$db->q($vesselid))
		->order($db->qn('AssetCategory'));
	try {
		$result = $db->setQuery($query)->loadAssocList();
	} catch(Exception $e) {
		echo("Can't find assets");
		exit();
	}
	
	$num_rows = count($result);
	
	# If the search was unsuccessful then Display Message try again.
	if (empty($num_rows)) {
		echo("<tr><td class=list_small>Sorry - no details available for this barge<br><hr></td></tr>"); 
		exit();
	}

	$datenow = time();
	foreach($result as $row) {

		# Display asset Results, l
		$AssetID = stripslashes($row["AssetID"]);
		$VesselID = stripslashes($row["VesselID"]);
		$AssetCategory = stripslashes($row["AssetCategory"]);
		$AssetCategoryDesc = stripslashes($row["AssetCategoryDesc"]);				
		$AssetTitle = stripslashes($row["AssetTitle"]);
		$AssetDescription = stripslashes($row["AssetDescription"]);
		$AssetDate = stripslashes($row["AssetDate"]);
		$AssetLastUpdate = stripslashes($row["AssetLastUpdate"]);
		$AssetPrivacy = stripslashes($row["AssetPrivacy"]);
	
		if($AssetCategory==1){
			//vesselname
			$vesselname=str_replace("_", " ", $AssetCategoryDesc)." $AssetTitle";
		}
		if($AssetID==$assetid){
			//vesselname
			$assetenquiry=str_replace("_", " ", $AssetCategoryDesc)." $AssetTitle";
		}
	}
	if(!$vesselname){
		$vesselname="Barge name unknown";
	}
	
	$assetenquiry="Equiry related to: ".$assetenquiry;
	
	$requesteremail=$login_email;
	
	if($contactaction=="send"){
		$config = Factory::getConfig();
		$mailer = Factory::getMailer();
		$mailer->setSender([$config->get('mailfrom'), 'Website enquiry']);
		$mailer->addRecipient([$keeperemail, $webmasteremail]);
		if($copyemail) $mailer->addRecipient($requesteremail);
		$mailer->isHtml(true);
		$mailer->Encoding = 'base64';
		$subject="Website enquiry from $sitename barge register";
		$mailer->setSubject($subject);
		if($requesteremail){
			$mailer->addReplyTo($requesteremail, $contact);
			$message="The following enquiry has been sent from the $sitename website barge register\n\n";
		}else{
			$mailer->addReplyTo($feedbackemail, $contact);
			$message="The following enquiry has been sent from the $sitename website barge register\nNo email was given SO PLEASE REPLY BY POST OR TELEPHONE IF GIVEN\n\n";
		}
		$mailer->Send();
		$browsermessage="Your enquiry has been sent to the current keeper of the barge:<br>\n";
		$feedbackdate=time();
		$browsermessage.="<b>Name:</b> $contact<br><b>email:</b> $requesteremail<br><b>Barge:</b> $vesselname<br><b>Subject:</b> $Subject<br><b>Message:</b><br>".nl2br($Message)."<br>\n";
		$message.="Name: $contact\n\nemail: $requesteremail\n\nBarge: $vesselname\n\nSubject: $Subject\n\nMessage:\n$Message\n\n$footerassetcontactemail";
		echo($browsermessage);
		echo("<tr><td class='list_small'><a href=\"#\" onClick=\"document.form.assetaction.value='detail';document.form.submit()\">Back to the detail <img src=\"Image/common/back1.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Back to the detail\" title=\"Back to the detail\"></a></td></tr>\n");
 
	
		exit();
	}else{
		//open the requester form
		?>
		<script language="JavaScript">
		<!--
		function MM_validateForm() { 
			var errors='';
		
			if (document.forms.form.Subject.value == ""){
				errors+='- the subject of your enquiry.\n';
			}
			if (document.forms.form.Message.value == "") {
				errors+='- a message\n';
			}
			if (errors) {alert('Please check and enter the following required information:\n'+errors);
			} else {
				document.forms.form.contactaction.value = 'send';
				//ok
			}
			document.MM_returnValue = (errors == '');  
		}
		
		//-->
		</script>
	
	
        <input type="hidden" name="feedbackaction" value="">
    
        <tr><td class='list_small_member'><?php echo($contacthelp); ?></td></tr>
        <tr><td class='list_small'><a href="#" onClick="document.form.assetaction.value='detail';document.form.submit()">Back to the detail <img src="Image/common/back1.gif" width="18" height="18" border="0" alt="Back to the detail" title="Back to the detail"></a></td></tr>
        <tr><td class='list_small'>
        <table class=maincontent width="100%" border="0" cellspacing="0" cellpadding="1">
        <tr>
        <td colspan="2">Please complete the fields below, then click on the button to submit your message.<br>	</td>
        </tr>
        <tr>
        <td>Your name: <?php echo($contact); ?></td>
        <td>Your email address: <?php echo($requesteremail); ?></td>
        </tr>
                  
        <tr>
        <td colspan="2">Subject
          <input name="Subject" type="text" class="formcontrol" value="<?php echo($assetenquiry); ?>" size="75" />
          <br></td>
        </tr>
        <tr>
        <td colspan="2">Message<br>
         
        <textarea name="Message" class="formtextarea" cols="60" rows="10"><?php echo($feedback_message); ?></textarea>
        <br>
        <input type="submit" name="Submit" value="Send" class="formcontrol" onClick="MM_validateForm();return document.MM_returnValue">
        <label>
        <input type="checkbox" name="copyemail" id="copyemail" class="formcontrol" />
        Tick this box to receive an email copy of this message</label>	</td>
        </tr>
        </table></td>
        </tr>
          
        <?
            //exit();
	}

	echo("</table>");


}elseif($view=="admin_edit"){

	echo("<h2>Barge Register Administration edit</h2>");
	//admin edit of member record
	$admin="open";
	include("update_assets.php");

}elseif($view=="admin_attach"){

	echo("<h2>Barge Register Administration attach</h2>");
	//admin edit of member record
	$admin="open";
	include("update_assets.php");

}elseif($view=="member_edit"){
	echo("<h2>Barge Register edit</h2>");
	include("update_assets.php");

}elseif($view=="ais_position"){
	//AIS request, load AIS ships and MMSI
	if($MMSI){
		//load map
		
		
		
		
		
	}else{
		echo("There is no MMSI number available to search for this ship.");
	}
}
	
echo("<input name=\"assetid\" type=\"hidden\" value=\"$assetid\">\n");
echo("<input name=\"assetsort\" type=\"hidden\" value=\"$assetsort\">\n");
echo("<input name=\"assetaction\" type=\"hidden\" value=\"\">\n");
echo("<input name=\"vesselid\" type=\"hidden\" value=\"$vesselid\">\n");
echo("<input name=\"contactaction\" type=\"hidden\" value=\"\">\n");
echo("<input name=\"admin\" type=\"hidden\" value=\"$admin\">\n");

?>


<!--End of content include -->

</form>
