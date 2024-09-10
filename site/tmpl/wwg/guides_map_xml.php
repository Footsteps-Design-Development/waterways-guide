<?php
//V3 Google Maps API CJG 20210305
// no direct access
// require_once("../../../commonV3.php");

// no direct access
defined('_JEXEC') or die;

require_once("../../commonV3.php");

use Joomla\CMS\Factory;

$db = Factory::getDbo();

getpost_ifset(array('waterway', 'country', 'guidetable', 'GuideMooringCodes', 'GuideHazardCodes', 'thisid', 'filteroption'));

if (!$guidetable) {
    $guidetable = $db->getPrefix() . "waterways_guide";
}

//$thisid="4858";

function parseToXML($htmlStr)
{
	//$xmlStr=str_replace('\\','',$htmlStr);
	$xmlStr = stripslashes($htmlStr);
	//$xmlStr=str_replace('–','-',$xmlStr); 
	$xmlStr = str_replace('<', '&lt;', $xmlStr);
	$xmlStr = str_replace('>', '&gt;', $xmlStr);
	$xmlStr = str_replace('"', '&quot;', $xmlStr);
	$xmlStr = str_replace("\'", '&quot', $xmlStr);
	$xmlStr = str_replace("&", '&amp;', $xmlStr);
	//$xmlStr = htmlspecialchars(strip_tags(($xmlStr)));
	// $xmlStr = utf8_encode($xmlStr);

	$xmlStr = htmlspecialchars($xmlStr, ENT_QUOTES, 'UTF-8');

	//$xmlStr=str_replace("’",'&apos;',$xmlStr); 
	return $xmlStr;
}
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn($guidetable))
	->where($db->qn('GuideStatus') . ' = 1')
	->where($db->qn('GuideLat') . ' <> 0')
	->where($db->qn('GuideLong') . ' <> 0')
	->order($db->qn(['GuideCountry', 'GuideWaterway', 'GuideOrder']));
//$country="SE";
$whereSet = false;
if ($country && $country != 'All') {
	$query->where($db->qn('GuideCountry') . ' = ' . $db->q($country));
	$whereSet = true;
}
if ($waterway && $waterway != 'All') {
	$query->where($db->qn('GuideWaterway') . ' = ' . $db->q(stripslashes($waterway)));
	$whereSet = true;
}
//filter options
if (!empty($filteroption)) {
	if ($filteroption == "ALL" || $filteroption == "M") {
		//add any ticks in $GuideMoringCodes and compare to $GuideCodes
		$filterwhere = '(' . $db->qn('GuideCategory') . ' = 1';
		//explode to array
		if (!empty($GuideMooringCodes)) {
			$codes = explode("|", $GuideMooringCodes);
			$maxcodes = sizeof($codes) - 2;
			$codeno = 1;
			while ($codeno <= $maxcodes) {
				$thiscode = "|" . $codes[$codeno] . "|";
				$filterwhere .= ' AND ' . $db->qn('GuideCodes') . " LIKE '%" . $thiscode . "%'";
				$codeno += 1;
			}
		}
		$filterwhere .= ")";
	}
	if ($filteroption == "ALL" || $filteroption == "H") {
		//add any ticks in $GuideHazardCodes and compare to $GuideCodes
		if ($filterwhere) $filterwhere .= ' OR ';
		else $filterwhere = '';
		$filterwhere .= '(' . $db->qn('GuideCategory') . ' = 2';
		//explode to array
		if ($GuideHazardCodes) {
			$codes = explode("|", $GuideHazardCodes);
			$maxcodes = sizeof($codes) - 2;
			$codeno = 1;
			while ($codeno <= $maxcodes) {
				$thiscode = "|" . $codes[$codeno] . "|";
				$filterwhere .= ' AND ' . $db->qn('GuideCodes') . " LIKE '%" . $thiscode . "%'";
				$codeno += 1;
			}
		}
		$filterwhere .= ")";
	}
}
if (!empty($filterwhere)) {
	$query->where('(' . $filterwhere . ')');
	$whereSet = true;
} elseif (!empty($filteroption) && $filteroption != "All") {
	//filter on Moorings and/or hazards without filter
	if ($filteroption == "M") {
		$query->where($db->qn('GuideCategory') . ' = 1');
		$whereSet = true;
	} else if ($filteroption == "H") {
		$query->where($db->qn('GuideCategory') . ' = 2');
		$whereSet = true;
	}
}
if (!$whereSet) {
	$query->where($db->qn('GuideID') . ' = ' . $db->q($thisid)); //live
}
// $query = $mapsql;
$result = $db->setQuery($query)->loadAssocList();

//Debugging
echo "<pre>";echo " hello ";print_r($result);die;

if ($result === false) die('Invalid query: ' . $query->__toString());
header("Content-type: text/xml");
// Start XML file, echo parent node
echo '<markers>';
// Iterate through the rows, printing XML nodes for each
foreach ($result as $row) {
	// ADD TO XML DOCUMENT NODE
	$GuideCodes = $row['GuideCodes'];
	$boxhtml = "";
	if ($GuideCodes) {
		//add tick boxes here
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__waterways_guide_services'))
			->order($db->qn('ServiceSortOrder'));
		switch ($row['GuideCategory']) {
			case "1":
				$query->where($db->qn('ServiceCategory') . " = 'mooringsguides'");
				break;
			case "2":
				$query->where($db->qn('ServiceCategory') . " = 'hazardguides'");
				break;
		}
		$boxes = $db->setQuery($query)->loadAssocList();
		$num_facilities = count($boxes) + 1;
		foreach ($boxes as $boxrow) {
			$boxid = $boxrow['ServiceID'];
			$boxdesc = $boxrow['ServiceDescGB'];
			$found = strstr($GuideCodes, "|" . $boxid . "|");
			if ($found) {
				if ($boxhtml) {
					$boxhtml .= ", ";
				}

				$boxhtml .= $boxdesc;
			}
		}
	}


	echo '<marker ';
	echo 'ID="' . parseToXML($row['GuideID']) . '" ';
	if (!empty($thisid)) {
		if ($row['GuideID'] == $thisid) {
			echo 'Openit = "' . parseToXML($row['GuideID']) . '" ';
		} else {
			echo 'Openit = "' . parseToXML("-") . '" ';
		}
	}
	echo 'Country="' . parseToXML((($row['GuideCountry']))) . '" ';
	echo 'Summary="' . parseToXML((($row['GuideSummary']))) . '" ';
	echo 'Name="' . parseToXML((($row['GuideName']))) . '" ';
	echo 'LatLng="' . parseToXML(decimal2degree($row['GuideLat'], 'LAT') . " , " . decimal2degree($row['GuideLong'], 'LON')) . '" ';
	echo 'Reference="' . parseToXML((($row['GuideRef']))) . '" ';
	echo 'Waterway="' . parseToXML((($row['GuideWaterway']))) . '" ';
	echo 'Remarks="' . parseToXML((($row['GuideRemarks']))) . '" ';
	echo 'Lat="' . $row['GuideLat'] . '" ';
	echo 'Lng="' . $row['GuideLong'] . '" ';
	echo 'Rating="' . $row['GuideRating'] . '" ';
	echo 'Location = "' . parseToXML($row['GuideLocation']) . '" ';
	echo 'Mooring = "' . parseToXML((($row['GuideMooring']))) . '" ';
	echo 'Facilities = "' . parseToXML((($row['GuideFacilities']))) . '" ';
	echo 'Codes = "' . parseToXML((($boxhtml))) . '" ';
	echo 'Costs = "' . parseToXML((($row['GuideCosts']))) . '" ';
	echo 'Amenities = "' . parseToXML((($row['GuideAmenities']))) . '" ';
	echo 'Contributors = "' . parseToXML((($row['GuideContributors']))) . '" ';
	echo 'Cat = "' . parseToXML((($row['GuideCategory']))) . '" ';
	if ($row['GuideUpdate'] == '0000-00-00 00:00:00') {
		echo 'Update = "' . parseToXML("-") . '" ';
	} else {
		echo 'Update = "' . parseToXML(date_to_format($row['GuideUpdate'], "ymd")) . '" ';
	}
	echo '/>';
}



// End XML file
echo '</markers>';
