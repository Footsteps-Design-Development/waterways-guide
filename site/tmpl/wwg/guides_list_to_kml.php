<?php

/**
 * Waterways Guide KML Generator
 * Joomla 5 Compatible Version
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Component\WaterWaysGuide\Site\Helper\WaterwaysHelper;

$app = Factory::getApplication();
$input = $app->getInput();
$db = Factory::getContainer()->get('DatabaseDriver');

// Get input parameters
$waterway = $input->getString('waterway', '');
$waterway1 = $input->getString('waterway1', '');
$waterway2 = $input->getString('waterway2', '');
$country = $input->getString('country', '');
$filteroption = $input->getString('filteroption', '');
$GuideMooringCodes = $input->getString('GuideMooringCodes', '');
$GuideHazardCodes = $input->getString('GuideHazardCodes', '');

// Build query
$query = $db->getQuery(true)
    ->select('*')
    ->from($db->quoteName('#__waterways_guide'))
    ->where($db->quoteName('GuideStatus') . ' = 1')
    ->order($db->quoteName(['GuideCountry', 'GuideWaterway', 'GuideOrder']));

if ($country && $country !== 'All') {
    $query->where($db->quoteName('GuideCountry') . ' = ' . $db->quote($country));
}

if ($waterway && $waterway !== 'All') {
    $query->where($db->quoteName('GuideWaterway') . ' = ' . $db->quote($waterway));
}

// Handle waterway range (waterway1 to waterway2)
if ($waterway1 && $waterway2) {
    $query->where($db->quoteName('GuideWaterway') . ' >= ' . $db->quote($waterway1));
    $query->where($db->quoteName('GuideWaterway') . ' <= ' . $db->quote($waterway2));
}

// Apply filter options
if ($filteroption && $filteroption !== 'All') {
    if ($filteroption === 'M') {
        $query->where($db->quoteName('GuideCategory') . ' = 1');
    } elseif ($filteroption === 'H') {
        $query->where($db->quoteName('GuideCategory') . ' = 2');
    }
}

$guides = $db->setQuery($query)->loadAssocList();

// Build KML document
$dom = new DOMDocument('1.0', 'UTF-8');
$root = $dom->createElementNS('http://www.opengis.net/kml/2.2', 'kml');
$parNode = $dom->appendChild($root);
$dnode = $dom->createElement('Document');
$docNode = $parNode->appendChild($dnode);

// Add document name
$docName = $dom->createElement('name', 'Waterways Guide - ' . ($country ?: 'All Countries'));
$docNode->appendChild($docName);

foreach ($guides as $row) {
    // Skip entries without valid coordinates
    if (empty($row['GuideLat']) || empty($row['GuideLong']) ||
        $row['GuideLat'] == 0 || $row['GuideLong'] == 0) {
        continue;
    }

    $GuideName = htmlspecialchars(stripslashes($row['GuideName']));
    $GuideWaterway = htmlspecialchars(stripslashes($row['GuideWaterway']));
    $GuideSummary = htmlspecialchars(stripslashes($row['GuideSummary']));
    $GuideLat = stripslashes($row['GuideLat']);
    $GuideLong = stripslashes($row['GuideLong']);

    $node = $dom->createElement('Placemark');
    $placeNode = $docNode->appendChild($node);

    // Name with CDATA
    $nameNode = $dom->createElement('name');
    $cdataNode = $dom->createCDATASection($GuideName);
    $nameNode->appendChild($cdataNode);
    $placeNode->appendChild($nameNode);

    // Description with waterway and summary
    $description = $GuideWaterway;
    if ($GuideSummary) {
        $description .= "\n" . $GuideSummary;
    }
    $descNode = $dom->createElement('description');
    $descCdata = $dom->createCDATASection($description);
    $descNode->appendChild($descCdata);
    $placeNode->appendChild($descNode);

    // Point coordinates
    $pointNode = $dom->createElement('Point');
    $placeNode->appendChild($pointNode);

    $coorStr = $GuideLong . ',' . $GuideLat . ',0';
    $coorNode = $dom->createElement('coordinates', $coorStr);
    $pointNode->appendChild($coorNode);
}

// Generate filename
$filename = 'waterways_guide';
if ($country && $country !== 'All') {
    $filename .= '_' . strtolower($country);
}
if ($waterway && $waterway !== 'All') {
    $filename .= '_' . preg_replace('/[^a-z0-9]+/i', '_', strtolower($waterway));
}
$filename .= '.kml';

// Output KML
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Type: application/vnd.google-earth.kml+xml');
echo $dom->saveXML();
