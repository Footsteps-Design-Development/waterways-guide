<?php


//---------------------------------------kmz------------------------------------------------------

//http://www.barges.org/components/com_waterways_guide/views/waterwaysguide/tmpl/guides_list_to_kmz.php

$mooringsguidedocintrotextpdf="";
$filtertext="";
require_once("../../../commonV3.php");

use Joomla\CMS\Factory;
$db = Factory::getDbo();

getpost_ifset(array('waterway','waterway1','waterway2','country','guideaction','filteroption','GuideMooringCodes','GuideHazardCodes','mooringsguidedocintrotextpdf','msid','menu_url'));

$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblMembers'))
	->where('('.$db->qn('ID').' = '.$db->q($msid).' OR '.$db->qn('ID2').' = '.$db->q($msid).')');
$memberrow = $db->setQuery($query)->loadAssoc();
$login_MembershipNo=$memberrow["MembershipNo"];
if(empty($memberrow["ID2"])){
	$contact=$memberrow["FirstName"]." ".$memberrow["LastName"];
}else{
	$contact=$memberrow["FirstName"]." ".$memberrow["LastName"]." and ".$memberrow["FirstName2"]." ".$memberrow["LastName2"];
}

//create  output
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('#__waterways_guide'))
	->where($db->qn('GuideStatus').' = 1')
	->order($db->qn(['GuideCountry', 'GuideWaterway', 'GuideOrder']));
if($country && $country != 'All') $query->where($db->qn('GuideCountry').' = '.$db->q($country));
if($waterway && $waterway != 'All') $query->where($db->qn('GuideWaterway').' = '.$db->q(stripslashes($waterway)));
//filter options
if($filteroption=="ALL" || $filteroption=="M"){
	//add any ticks in $GuideMoringCodes and compare to $GuideCodes
	$filterwhere = '('.$db->qn('GuideCategory').' = 1';
	//explode to array
	if($GuideMooringCodes){
		$codes = explode ("|", $GuideMooringCodes);
		$maxcodes=sizeof ($codes)-2;
		$codeno=1;
		while($codeno<=$maxcodes){
			$thiscode="|".$codes[$codeno]."|";
			$filterwhere .= ' AND '.$db->qn('GuideCodes')." LIKE '%".$thiscode."%'";
			$codeno+=1;
		}
	}
	$filterwhere.=")";
}
if($filteroption=="ALL" || $filteroption=="H"){
	//add any ticks in $GuideHazardCodes and compare to $GuideCodes
	if($filterwhere) $filterwhere .= ' OR ';
	else $filterwhere = '';
	$filterwhere .= '('.$db->qn('GuideCategory').' = 2';
	//explode to array
	if($GuideHazardCodes){
		$codes = explode ("|", $GuideHazardCodes);
		$maxcodes=sizeof ($codes)-2;
		$codeno=1;
		while($codeno<=$maxcodes){
			$thiscode="|".$codes[$codeno]."|";
			$filterwhere .= ' AND '.$db->qn('GuideCodes')." LIKE '%".$thiscode."%'";
			$codeno+=1;
		}
	}
	$filterwhere.=")";
}
if($filterwhere) $query->where('('.$filterwhere.')');
elseif($filteroption!="All"){
	//filter on Moorings and/or hazards without filter
	if($filteroption == "M") $query->where($db->qn('GuideCategory').' = 1');
	else if($filteroption == "H") $query->where($db->qn('GuideCategory').' = 2');
}
$rows = 0;
$guides = $db->setQuery($query)->loadAssocList();
$rows = count($guides);
# If the search was unsuccessful then Display Message try again.
If ($rows == 0){
	PRINT "<tr><td class=bodytext colspan=3>Sorry - there are no guides at the moment matching your selection.</td></tr>\n";
}else{
	
	//build filter options description
		//filteroption','GuideMooringCodes','GuideHazardCodes'));
	if($filteroption){
		if($GuideMooringCodes){
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__waterways_guide_services'))
				->where($db->qn('ServiceCategory')." = 'mooringsguides'")
				->order($db->qn('ServiceSortOrder'));
			$boxes = $db->setQuery($query)->loadAssocList();
			$filtertext="<b>Essentials:</b> ";
			$boxtext="";
			foreach($boxes as $boxrow) {
				$boxid=$boxrow["ServiceID"];
				$boxdesc=$boxrow["ServiceDescGB"];
				$found = strstr ($GuideMooringCodes, "|".$boxid."|");
				if($found){
					$filtertext.=$boxdesc.", ";
				}
			}
			$filtertext.="\n";
		}
		if($GuideHazardCodes){
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__waterways_guide_services'))
				->where($db->qn('ServiceCategory')." = 'hazardguides'")
				->order($db->qn('ServiceSortOrder'));
			$boxes = $db->setQuery($query)->loadAssocList();
			$filtertext.="<b>Hazards:</b> ";
			$boxtext="";
			foreach($boxes as $boxrow) {
				$boxid=$boxrow["ServiceID"];
				$boxdesc=$boxrow["ServiceDescGB"];
				$found = strstr ($GuideHazardCodes, "|".$boxid."|");
				if($found){
					$filtertext.=$boxdesc.", ";
				}
			}
			$filtertext.="\n";
		}
	}

	$guidematch=0;	
	
	// Creates the Document.
	$dom = new DOMDocument('1.0', 'UTF-8');
	
	// Creates the root KML element and appends it to the root document.
	//<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2" xmlns:kml="http://www.opengis.net/kml/2.2" xmlns:atom="http://www.w3.org/2005/Atom">

	//$dnode = $dom->createElement('<kml xmlns=\"http://www.opengis.net/kml/2.2\" xmlns:gx=\"http://www.google.com/kml/ext/2.2\" xmlns:kml=\"http://www.opengis.net/kml/2.2\" xmlns:atom=\"http://www.w3.org/2005/Atom\">');
	//$docNode = $parNode->appendChild($dnode);
	
	/*$root = $doc->createElementNS('http://www.w3.org/2005/Atom', 'element');
$doc->appendChild($root);
$root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:g', 'http://base.google.com/ns/1.0');
$item = $doc->createElementNS('http://base.google.com/ns/1.0', 'g:item_type', 'house');
$root->appendChild($item);

*/
	//Set up output file name
	$filedate = strftime('%Y%m%d');
	$waterwayfilename = str_replace(" ", "-", $waterway);
	
	$name="DBAGuide-".$country."-".$waterwayfilename."-".$filedate.".kml";
	
	//original 
	//$node = $dom->createElementNS('http://earth.google.com/kml/2.2', 'kml');
	//$parNode = $dom->appendChild($node);
	
	$root = $dom->createElementNS('http://www.opengis.net/kml/2.2', 'kml');
	$parNode = $dom->appendChild($root);
	
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:gx', 'http://www.google.com/kml/ext/2.2');
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:kml', 'http://www.opengis.net/kml/2.2');
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:atom', 'http://www.w3.org/2005/Atom');
	
	// Creates a KML Document element and append it to the KML element.
	$dnode = $dom->createElement('Document');
	$docNode = $parNode->appendChild($dnode);
	
	$docname = $country."-".$waterway;

	//add document name tag - Rod North 06/10/18 built on existing commented code
	$docnamenode = $dom->createElement('name', $docname);
	$docNode->appendchild($docnamenode);
	
	/*
	$namenode = $dom->createElement('Name', $docname);
	$docNode = $parNode->appendChild($namenode);
	
	$docname="DBA-".$country."-".$waterway."-".$filedate;
	
	$dnode = $dom->createElement('name','');
	$docNode = $parNode->appendChild($dnode);
	$cdataNode = $dom->createCDATASection($docname);
	$docNode->appendChild($cdataNode);
	*/	
		//$nameNode = $dom->createElement('name','name' . $GuideID);
		//$nameText = nl2br($docname);

		//$placeNode->appendChild($nameNode);
	
	
	//<name>KML Samples</name>
    //<open>1</open>
    //<description>Unleash your creativity with the help of these examples!</description>


	// Creates the Style elements and append the elements to the Document element.
	$mooringStyleNode = $dom->createElement('Style');
	$mooringStyleNode->setAttribute('id', 'mooringStyle');
	$mooringIconstyleNode = $dom->createElement('IconStyle');
	$mooringIconstyleNode->setAttribute('id', 'mooringIcon');
	$mooringIconNode = $dom->createElement('Icon');
	$mooringHref = $dom->createElement('href', 'http://www.barges.org/Image/common/mooring1.png');
	$mooringIconNode->appendChild($mooringHref);
	$mooringIconstyleNode->appendChild($mooringIconNode);
	$mooringStyleNode->appendChild($mooringIconstyleNode);
	$docNode->appendChild($mooringStyleNode);
	foreach($guides as $row) {
		$GuideID = stripslashes($row["GuideID"]);
		$GuideNo = stripslashes($row["GuideNo"]);
		$GuideVer = stripslashes($row["GuideVer"]);
		$GuideCountry = stripslashes($row["GuideCountry"]);
		$GuideWaterway = stripslashes($row["GuideWaterway"]);

		$GuideWaterway = str_replace("&", "and", $GuideWaterway);
		$GuideSummary = stripslashes($row["GuideSummary"]);
		$GuideName = stripslashes($row["GuideName"]);
		//&sbquo;
		$GuideNameClean=str_replace(",", '&sbquo', $GuideName);
		$GuideRef = stripslashes($row["GuideRef"]);
		$GuideRating = stripslashes($row["GuideRating"]);
		$GuideLatLong = stripslashes($row["GuideLatLong"]);
		$GuideLocation = stripslashes($row["GuideLocation"]);
		$GuideMooring = stripslashes($row["GuideMooring"]);
		$GuideFacilities = stripslashes($row["GuideFacilities"]);
		$GuideCodes = stripslashes($row["GuideCodes"]);
		$GuideCosts = stripslashes($row["GuideCosts"]);
		$GuideAmenities = stripslashes($row["GuideAmenities"]);
		$GuideContributors = stripslashes($row["GuideContributors"]);
		$GuideRemarks = stripslashes($row["GuideRemarks"]);
		$GuideLat = stripslashes($row["GuideLat"]);
		$GuideLong = stripslashes($row["GuideLong"]);
		$GuideDocs = stripslashes($row["GuideDocs"]);
		$GuidePostingDate = stripslashes($row["GuidePostingDate"]);
		$GuideCategory = $row["GuideCategory"];
		$GuideStatus = $row["GuideStatus"];
		$GuideOrder = $row["GuideOrder"];

		if($GuideStatus!=1){
			if($GuideStatus==0){
				$GuideNameClean.=" (V. ".$GuideVer." Pending)";
			}
			if($GuideStatus==2){
				$GuideNameClean.=" (V. ".$GuideVer." Archived)";				
			}
		}
		//create update link
		//$pdf->ezText('<c:alink:http://www.barges.org/members/waterwaysguide/waterwaysguide-search?guideaction=memberedit&infoid='.$tmp.'>Click here to update this entry on-line</c:alink>');

		$GuideUpdate = stripslashes($row["GuideUpdate"]);
		$GuideUpdatedisplay = (empty($GuideUpdate) ? 'Pre 2009' : date('dmy', strtotime($GuideUpdate)))." - Mooring Index: ".$GuideNo." - Version: ".$GuideVer;
		if($GuideCategory==2){
			//add hazard icon in front of name
			switch ($GuideRating) {
				case "1":
				$ratingtitle="<b>Hazard:</b> Rating Low";
			break;
				case "2":
				$ratingtitle="<b>Hazard:</b> Rating Medium";					
			break;
				case "3":
				$ratingtitle="<b>Hazard:</b> Rating High";					
			}
			$GuideRatingText=$ratingtitle;

		}else{

			switch ($GuideRating) {
				case "":
				$ratingtitle="<b>Mooring:</b> Rating Unknown";
			break;
				case "0":
				$ratingtitle="<b>Mooring:</b> Rating Doubtful";
			break;
				case "1":
				$ratingtitle="<b>Mooring:</b> Rating Adequate";
			break;
				case "2":
				$ratingtitle="<b>Mooring:</b> Rating Good";					
			break;
				case "3":
				$ratingtitle="<b>Mooring:</b> Rating Very Good";					
			}
			$GuideRatingText=$ratingtitle;
		}    

		
		
		
		$listresults="";
		
		if($GuideSummary){
			//$DisplayGuideSummary.=$GuideSummary."\n";	
		}
		
		
		//convert dec to lat long needs to be format Lat/Long: 051°00.57N , 005°14.40E
		// Lat/Long: 051а0.57N , 005б4.40E
		if($GuideLat && $GuideLong){
			$GuideLatLong=decimal2degree($GuideLat,'LAT') ." , " . decimal2degree($GuideLong,'LON');
			$GuideLatLong=str_replace("&deg;", "°", $GuideLatLong); 
		}else{
			$GuideLatLong="Not known";
		}
				
		//$listresults.="<tr valign='top'><td><b>Waterway:</b></td><td>".$GuideWaterway."</td></tr>\n";
	
		//$listresults.="2<".$GuideNameClean.">\n";
		if($GuideWaterway){
			$listresults.="<b>Waterway:</b> ".$GuideWaterway."\n";
		}
		if($GuideRating){
			$listresults.=$GuideRatingText."\n";
		}	
		if($GuideLatLong){
			$listresults.="<b>Lat/Long:</b> ".$GuideLatLong."\n";
		}
		if($GuideRef){
			$listresults.="<b>Reference:</b> ".$GuideRef."\n";
		}
		if($GuideLocation){
			$listresults.="<b>Location:</b> ".$GuideLocation."\n";
		}
		if($GuideMooring){
			$listresults.="<b>Mooring:</b> ".$GuideMooring."\n";
		}

		if($GuideCodes){
			//add tick boxes here
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__waterways_guide_services'))
				->order($db->qn('ServiceSortOrder'));
			switch ($GuideCategory) {
				case "1":
					$query->where($db->qn('ServiceCategory')." = 'mooringsguides'");
					$boxestitle="Essentials";
					break;
				case "2":
					$query->where($db->qn('ServiceCategory')." = 'hazardguides'");
					$boxestitle="Hazard category";
					break;
			}
			$boxes = $db->setQuery($query)->loadAssocList();
			$boxhtml="";
			foreach($boxes as $boxrow) {
				$boxid=$boxrow["ServiceID"];
				$boxdesc=$boxrow["ServiceDescGB"];
				$found = strstr ($GuideCodes, "|".$boxid."|");
				if($found){
					if($boxhtml){
						$boxhtml.=", ";
					}
					
					$boxhtml.=$boxdesc;
				}
			}
	
			$listresults.="<b>".$boxestitle.":</b> ".$boxhtml."\n";
		}

		if($GuideFacilities){
			$listresults.="<b>Facilities:</b> ".$GuideFacilities."\n";
		}
		if($GuideCosts){
			$listresults.="<b>Costs:</b> ".$GuideCosts."\n";
		}
		if($GuideAmenities){
			$listresults.="<b>Amenities:</b> ".$GuideAmenities."\n";
		}
		if($GuideContributors){
			$listresults.="<b>Contributors:</b> ".$GuideContributors."\n";
		}
		if($GuideRemarks){
			$listresults.="<b>Remarks:</b> ".$GuideRemarks."\n";
		}
	
		if($GuideUpdate){
			$listresults.="<b>Last Update:</b> ".$GuideUpdatedisplay."\n";
		}
		
		//update on-line link guide no
		
		//example link http://www.barges.org/members/waterwaysguide/waterwaysguide-search?guideaction=memberedit&infoid=7022
		$listresults.="<a href='http://".$menu_url."?guideaction=memberedit&infoid=".$GuideID."'>Click here to update this entry on-line</a>\n";
		
		//$listresults.="Siteurl: <a href='http://".$menu_url."'>".$menu_url."</a>\n";
		//$listresults.="<tr valign='top'><td colspan=2><b>".$thisguideenquiry."</b></td></tr>";
	
		$listresults.="Copyright ".date("Y ")." DBA The Barge Association, ".$login_MembershipNo.".\nFor use of DBA member ".$contact." only.\n";
		/* change folder for each waterway
		$node = $dom->createElement('Placemark');
		$placeNode = $docNode->appendChild($node);
		<Folder>
      	<name>Placemarks</name>
		*/


		$guidematch=1;
		
		// Creates a Placemark and append it to the Document.

		$node = $dom->createElement('Placemark');
		$placeNode = $docNode->appendChild($node);
	
		// Creates an id attribute and assign it the value of id column.
		$placeNode->setAttribute('id', 'placemark' . $GuideID);
		
		
		
		
		
		
		


		// Create name, and description elements and assigns them the values of the name and address columns from the results.
		$nameNode = $dom->createElement('name',htmlspecialchars($GuideNameClean));
		//$nameNode = $dom->createElement('name','name' . $GuideID);
		$nameText = nl2br($GuideNameClean);
		$nameNode = $dom->createElement('name', '');
		$cdataNode = $dom->createCDATASection($nameText);
		$nameNode->appendChild($cdataNode);
		$placeNode->appendChild($nameNode);
		
		//htmlentities($GuideNameClean));
		//$placeNode->appendChild($nameNode);
		//$descNode = $dom->createElement('description', 'description');
		//$descNode = $dom->createElement->createCDATASection($desc);
		$descText = nl2br($listresults);
		$descNode = $dom->createElement('description', '');
		$cdataNode = $dom->createCDATASection($descText);
		$descNode->appendChild($cdataNode);
		$placeNode->appendChild($descNode);
		
		
		$styleUrl = $dom->createElement('styleUrl', '#' . 'mooring' . 'Style');
		$placeNode->appendChild($styleUrl);
	
	  // Creates a Point element.
		$pointNode = $dom->createElement('Point');
		$placeNode->appendChild($pointNode);
		// Creates a coordinates element and gives it the value of the lng and lat columns from the results.

		$coorStr = $GuideLong . ','  . $GuideLat;
		$coorNode = $dom->createElement('coordinates', $coorStr);
		$pointNode->appendChild($coorNode);
		
		//echo("$GuideName <br>$listresults <br>$GuideCategory<br>$GuideLat ,$GuideLong <br><br>");
		

	}
	//$kmlOutput = $dom->saveXML();
	
	
	header("Content-Disposition: attachment; filename=\"". $name. "\"");
	header("Content-type: application/vnd.google-earth.kml+xml");

	//echo $kmlOutput;
	//$listresults = htmlentities($listresults, ENT_QUOTES, "UTF-8");

	//$listresults.=$copyright_guides;


    echo $dom->saveXML();
	



}





?>