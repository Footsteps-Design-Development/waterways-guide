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
$docName = $dom->createElement('name', ($country ?: 'All') . '-' . ($waterway ?: 'All'));
$docNode->appendChild($docName);

// Add mooring style
$styleNode = $dom->createElement('Style');
$styleNode->setAttribute('id', 'mooringStyle');
$iconStyleNode = $dom->createElement('IconStyle');
$iconStyleNode->setAttribute('id', 'mooringIcon');
$iconNode = $dom->createElement('Icon');
$hrefNode = $dom->createElement('href', 'http://www.barges.org/Image/common/mooring1.png');
$iconNode->appendChild($hrefNode);
$iconStyleNode->appendChild($iconNode);
$styleNode->appendChild($iconStyleNode);
$docNode->appendChild($styleNode);

// Get component params for copyright
$cParams = WaterwaysHelper::getParams();
$sitename = Factory::getApplication()->get('sitename', 'DBA The Barge Association');
$memberNumber = $cParams->get('member_number', '');
$user = Factory::getApplication()->getIdentity();

foreach ($guides as $row) {
    // Skip entries without valid coordinates
    if (empty($row['GuideLat']) || empty($row['GuideLong']) ||
        $row['GuideLat'] == 0 || $row['GuideLong'] == 0) {
        continue;
    }

    $GuideID = $row['GuideID'];
    $GuideNo = $row['GuideNo'];
    $GuideVer = $row['GuideVer'];
    $GuideName = stripslashes($row['GuideName']);
    $GuideWaterway = stripslashes($row['GuideWaterway']);
    $GuideRating = $row['GuideRating'];
    $GuideRef = stripslashes($row['GuideRef']);
    $GuideLocation = stripslashes($row['GuideLocation']);
    $GuideMooring = stripslashes($row['GuideMooring']);
    $GuideCodes = stripslashes($row['GuideCodes']);
    $GuideFacilities = stripslashes($row['GuideFacilities']);
    $GuideCosts = stripslashes($row['GuideCosts']);
    $GuideAmenities = stripslashes($row['GuideAmenities']);
    $GuideContributors = stripslashes($row['GuideContributors']);
    $GuideSummary = stripslashes($row['GuideSummary']);
    $GuideRemarks = stripslashes($row['GuideRemarks']);
    $GuideUpdate = $row['GuideUpdate'];
    $GuideCategory = $row['GuideCategory'];
    $GuideLat = stripslashes($row['GuideLat']);
    $GuideLong = stripslashes($row['GuideLong']);

    // Format lat/long for display
    $GuideLatLong = WaterwaysHelper::decimalToDegree($GuideLat, 'LAT') . ' , ' .
                    WaterwaysHelper::decimalToDegree($GuideLong, 'LON');

    // Get mooring rating text
    $mooringRating = '';
    switch ($GuideRating) {
        case 1: $mooringRating = 'Rating Poor'; break;
        case 2: $mooringRating = 'Rating Adequate'; break;
        case 3: $mooringRating = 'Rating Good'; break;
        case 4: $mooringRating = 'Rating Excellent'; break;
    }

    // Get service codes description
    $serviceDesc = '';
    if ($GuideCodes) {
        $query = $db->getQuery(true)
            ->select('ServiceDescGB')
            ->from($db->quoteName('#__waterways_guide_services'))
            ->where($db->quoteName('ServiceCategory') . ' = ' .
                   $db->quote($GuideCategory == 1 ? 'mooringsguides' : 'hazardguides'))
            ->order($db->quoteName('ServiceSortOrder'));
        $services = $db->setQuery($query)->loadColumn();

        $essentials = [];
        foreach ($services as $service) {
            $serviceId = '';
            // Match service to code (simplified - you may need to enhance this)
            if (strpos($GuideCodes, '|' . $service . '|') !== false) {
                $essentials[] = $service;
            }
        }
        // Get actual service descriptions
        $query = $db->getQuery(true)
            ->select(['ServiceID', 'ServiceDescGB'])
            ->from($db->quoteName('#__waterways_guide_services'))
            ->where($db->quoteName('ServiceCategory') . ' = ' .
                   $db->quote($GuideCategory == 1 ? 'mooringsguides' : 'hazardguides'))
            ->order($db->quoteName('ServiceSortOrder'));
        $allServices = $db->setQuery($query)->loadAssocList('ServiceID');

        $essentials = [];
        foreach ($allServices as $serviceId => $service) {
            if (strpos($GuideCodes, '|' . $serviceId . '|') !== false) {
                $essentials[] = $service['ServiceDescGB'];
            }
        }
        $serviceDesc = implode(', ', $essentials);
    }

    // Build description HTML
    $description = '<b>Waterway:</b> ' . htmlspecialchars($GuideWaterway) . '<br />' . "\n";
    if ($mooringRating) {
        $description .= '<b>Mooring:</b> ' . $mooringRating . '<br />' . "\n";
    }
    $description .= '<b>Lat/Long:</b> ' . htmlspecialchars($GuideLatLong) . '<br />' . "\n";
    if ($GuideRef) {
        $description .= '<b>Reference:</b> ' . htmlspecialchars($GuideRef) . '<br />' . "\n";
    }
    if ($GuideLocation) {
        $description .= '<b>Location:</b> ' . htmlspecialchars($GuideLocation) . '<br />' . "\n";
    }
    if ($GuideMooring) {
        $description .= '<b>Mooring:</b> ' . htmlspecialchars($GuideMooring) . '<br />' . "\n";
    }
    if ($serviceDesc) {
        $description .= '<b>Essentials:</b> ' . htmlspecialchars($serviceDesc) . '<br />' . "\n";
    }
    if ($GuideFacilities) {
        $description .= '<b>Facilities:</b> ' . htmlspecialchars($GuideFacilities) . '<br />' . "\n";
    }
    if ($GuideCosts) {
        $description .= '<b>Costs:</b> ' . htmlspecialchars($GuideCosts) . '<br />' . "\n";
    }
    if ($GuideAmenities) {
        $description .= '<b>Amenities:</b> ' . htmlspecialchars($GuideAmenities) . '<br />' . "\n";
    }
    if ($GuideContributors) {
        $description .= '<b>Contributors:</b> ' . htmlspecialchars($GuideContributors) . '<br />' . "\n";
    }
    if ($GuideRemarks) {
        $description .= '<b>Remarks:</b> ' . htmlspecialchars($GuideRemarks) . '<br />' . "\n";
    }
    if ($GuideUpdate && $GuideUpdate != '0000-00-00 00:00:00') {
        $updateDate = date('dmy', strtotime($GuideUpdate));
        $description .= '<b>Last Update:</b> ' . $updateDate . ' - Mooring Index: ' . $GuideNo . ' - Version: ' . $GuideVer . '<br />' . "\n";
    }

    // Add edit link
    $editUrl = 'http://barges.org/knowledgebase/waterways-guide/waterways-guide?guideaction=memberedit&infoid=' . $GuideID;
    $description .= '<a href=\'' . $editUrl . '\'>Click here to update this entry on-line</a><br />' . "\n";

    // Add copyright
    $description .= 'Copyright ' . date('Y') . '  ' . htmlspecialchars($sitename) . ', ' . htmlspecialchars($memberNumber) . '.<br />' . "\n";
    $description .= 'For use of DBA member ' . htmlspecialchars($user->name) . ' only.<br />' . "\n";

    // Create placemark
    $node = $dom->createElement('Placemark');
    $node->setAttribute('id', 'placemark' . $GuideID);
    $placeNode = $docNode->appendChild($node);

    // Name with CDATA
    $nameNode = $dom->createElement('name');
    $cdataNode = $dom->createCDATASection($GuideName);
    $nameNode->appendChild($cdataNode);
    $placeNode->appendChild($nameNode);

    // Description with CDATA
    $descNode = $dom->createElement('description');
    $descCdata = $dom->createCDATASection($description);
    $descNode->appendChild($descCdata);
    $placeNode->appendChild($descNode);

    // Style URL
    $styleUrlNode = $dom->createElement('styleUrl', '#mooringStyle');
    $placeNode->appendChild($styleUrlNode);

    // Point coordinates
    $pointNode = $dom->createElement('Point');
    $placeNode->appendChild($pointNode);

    $coorStr = $GuideLong . ',' . $GuideLat;
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
