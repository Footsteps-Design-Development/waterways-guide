<?php

// V3 Google Maps API CJG 20210305
// Joomla 5 compatible

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Component\WaterWaysGuide\Site\Helper\WaterwaysHelper;

$db = Factory::getContainer()->get('DatabaseDriver');
$app = Factory::getApplication();
$input = $app->getInput();

// Get input values
$waterway = $input->getString('waterway', '');
$country = $input->getString('country', '');
$guidetable = $input->getString('guidetable', '');
$GuideMooringCodes = $input->getString('GuideMooringCodes', '');
$GuideHazardCodes = $input->getString('GuideHazardCodes', '');
$thisid = $input->getString('thisid', '');
$filteroption = $input->getString('filteroption', '');

if (!$guidetable) {
    $guidetable = $db->getPrefix() . "waterways_guide";
}

function parseToXML($htmlStr)
{
	$xmlStr = stripslashes($htmlStr);
	$xmlStr = str_replace('<', '&lt;', $xmlStr);
	$xmlStr = str_replace('>', '&gt;', $xmlStr);
	$xmlStr = str_replace('"', '&quot;', $xmlStr);
	$xmlStr = str_replace("\'", '&quot', $xmlStr);
	$xmlStr = str_replace("&", '&amp;', $xmlStr);
	$xmlStr = htmlspecialchars($xmlStr, ENT_QUOTES, 'UTF-8');
	return $xmlStr;
}

$query = $db->getQuery(true)
	->select('*')
	->from($db->quoteName($guidetable))
	->where($db->quoteName('GuideStatus') . ' = 1')
	->where($db->quoteName('GuideLat') . ' <> 0')
	->where($db->quoteName('GuideLong') . ' <> 0')
	->order($db->quoteName(['GuideCountry', 'GuideWaterway', 'GuideOrder']));

$whereSet = false;
if ($country && $country != 'All') {
	$query->where($db->quoteName('GuideCountry') . ' = ' . $db->quote($country));
	$whereSet = true;
}
if ($waterway && $waterway != 'All') {
	$query->where($db->quoteName('GuideWaterway') . ' = ' . $db->quote(stripslashes($waterway)));
	$whereSet = true;
}

// Filter options
if (!empty($filteroption)) {
	$filterwhere = '';
	if ($filteroption == "ALL" || $filteroption == "M") {
		$filterwhere = '(' . $db->quoteName('GuideCategory') . ' = 1';
		if (!empty($GuideMooringCodes)) {
			$codes = explode("|", $GuideMooringCodes);
			$maxcodes = sizeof($codes) - 2;
			$codeno = 1;
			while ($codeno <= $maxcodes) {
				$thiscode = "|" . $codes[$codeno] . "|";
				$filterwhere .= ' AND ' . $db->quoteName('GuideCodes') . " LIKE '%" . $thiscode . "%'";
				$codeno += 1;
			}
		}
		$filterwhere .= ")";
	}
	if ($filteroption == "ALL" || $filteroption == "H") {
		if ($filterwhere) $filterwhere .= ' OR ';
		$filterwhere .= '(' . $db->quoteName('GuideCategory') . ' = 2';
		if ($GuideHazardCodes) {
			$codes = explode("|", $GuideHazardCodes);
			$maxcodes = sizeof($codes) - 2;
			$codeno = 1;
			while ($codeno <= $maxcodes) {
				$thiscode = "|" . $codes[$codeno] . "|";
				$filterwhere .= ' AND ' . $db->quoteName('GuideCodes') . " LIKE '%" . $thiscode . "%'";
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
	if ($filteroption == "M") {
		$query->where($db->quoteName('GuideCategory') . ' = 1');
		$whereSet = true;
	} else if ($filteroption == "H") {
		$query->where($db->quoteName('GuideCategory') . ' = 2');
		$whereSet = true;
	}
}

if (!$whereSet) {
	$query->where($db->quoteName('GuideID') . ' = ' . $db->quote($thisid));
}

$result = $db->setQuery($query)->loadAssocList();

if ($result === false) die('Invalid query: ' . $query->__toString());

header("Content-type: text/xml");
echo '<markers>';

foreach ($result as $row) {
	$GuideCodes = $row['GuideCodes'];
	$boxhtml = "";
	if ($GuideCodes) {
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__waterways_guide_services'))
			->order($db->quoteName('ServiceSortOrder'));
		switch ($row['GuideCategory']) {
			case "1":
				$query->where($db->quoteName('ServiceCategory') . " = 'mooringsguides'");
				break;
			case "2":
				$query->where($db->quoteName('ServiceCategory') . " = 'hazardguides'");
				break;
		}
		$boxes = $db->setQuery($query)->loadAssocList();
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
	echo 'LatLng="' . parseToXML(WaterwaysHelper::decimalToDegree($row['GuideLat'], 'LAT') . " , " . WaterwaysHelper::decimalToDegree($row['GuideLong'], 'LON')) . '" ';
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
	if ($row['GuideUpdate'] == '0000-00-00 00:00:00' || empty($row['GuideUpdate'])) {
		echo 'Update = "' . parseToXML("-") . '" ';
	} else {
		echo 'Update = "' . parseToXML(WaterwaysHelper::dateToFormat($row['GuideUpdate'], "ymd")) . '" ';
	}
	echo '/>';
}

echo '</markers>';
