<?php
//$db = Factory::getDBO();
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblCountry'))
	->order($db->qn('name'));
$results = $db->setQuery($query)->loadObjectList(); 
$selectit="";
$clist="";

if(count($results)) {
	$clist.="<option value=\"0_0_0\">Select a country</option>\n";
	$clist.="<option value=\"GB_UK_United Kingdom\">United Kingdom</option>\n";
	foreach($results as $row) {
		$iso=$row->iso;
		$name=$row->name;
		$printable_name=$row->printable_name;
		$iso3=$row->iso3;
		$numcode=$row->numcode;
		$postzone=$row->postzone;
		if(!$postzone){
			$postzone="Z2";
		}
		if($preselect_iso){
			 if($iso==$preselect_iso){
				$selectit=" selected";
			}else{
				$selectit="";
			}
		}
		//put iso_postzone together GB EU Z1 Z2
		$clist.="<option value=\"".$iso."_".$postzone."_".$printable_name."\"".$selectit.">$printable_name</option>\n";
	}
}else{
	$message="No information available at this time";
	exit();
}


echo($clist);
?>