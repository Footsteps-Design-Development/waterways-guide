<?php

/**
 * Waterways Guide PDF Generator
 * Joomla 5 Compatible Version
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Component\WaterWaysGuide\Site\Helper\WaterwaysHelper;

//---------------------------------------pdf------------------------------------------------------
$mooringsguidedocintrotextpdf = "";
$filtertext = "";

$app = Factory::getApplication();
$input = $app->getInput();
$db = Factory::getContainer()->get('DatabaseDriver');
$cParams = ComponentHelper::getParams('com_waterways_guide');

// Get input parameters
$waterway = $input->getString('waterway', '');
$waterway1 = $input->getString('waterway1', '');
$waterway2 = $input->getString('waterway2', '');
$country = $input->getString('country', '');
$guideaction = $input->getString('guideaction', '');
$filteroption = $input->getString('filteroption', '');
$GuideMooringCodes = $input->getString('GuideMooringCodes', '');
$GuideHazardCodes = $input->getString('GuideHazardCodes', '');
$msid = $input->getInt('msid', 0);
$menu_url = $input->getString('menu_url', '');

// Get footer text from component params
$footer3 = $cParams->get('footer3', 'DBA The Barge Association');

// Get member info - using Joomla user instead of tblMembers
$user = Factory::getApplication()->getIdentity();
$login_MembershipNo = $user->id;
$contact = $user->name;

// create pdf and output
$query = $db->getQuery(true)
    ->select('*')
    ->from($db->quoteName('#__waterways_guide'))
    ->where($db->quoteName('GuideStatus') . ' = 1')
    ->order($db->quoteName(['GuideCountry', 'GuideWaterway', 'GuideOrder']));

if ($country && $country != 'All') {
    $query->where($db->quoteName('GuideCountry') . ' = ' . $db->quote($country));
}
if ($waterway && $waterway != 'All') {
    $query->where($db->quoteName('GuideWaterway') . ' = ' . $db->quote(stripslashes($waterway)));
} elseif ($waterway1 && $waterway2) {
    // split down for France
    $pdfsubtitle = $country . " - " . $waterway1 . " to " . $waterway2;
    $query->where($db->quoteName('GuideWaterway') . ' BETWEEN ' . $db->quote(stripslashes($waterway1)) . ' AND ' . $db->quote(stripslashes($waterway2)));
}

// filter options
$filterwhere = '';
if ($filteroption == "ALL" || $filteroption == "M") {
    $filterwhere = '(' . $db->quoteName('GuideCategory') . ' = 1';
    if ($GuideMooringCodes) {
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
    if ($filterwhere) {
        $filterwhere .= ' OR ';
    }
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
if ($filterwhere) {
    $query->where('(' . $filterwhere . ')');
} elseif ($filteroption && $filteroption != "All") {
    if ($filteroption == "M") {
        $query->where($db->quoteName('GuideCategory') . ' = 1');
    } elseif ($filteroption == "H") {
        $query->where($db->quoteName('GuideCategory') . ' = 2');
    }
}

$rows = 0;
$guides = $db->setQuery($query)->loadAssocList();
$rows = count($guides);

if ($rows == 0) {
    echo "<tr><td class=bodytext colspan=3>Sorry - there are no guides at the moment matching your selection.</td></tr>\n";
} else {
    // build filter options description
    if ($filteroption) {
        if ($GuideMooringCodes) {
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__waterways_guide_services'))
                ->where($db->quoteName('ServiceCategory') . " = 'mooringsguides'")
                ->order($db->quoteName('ServiceSortOrder'));
            $boxes = $db->setQuery($query)->loadAssocList();
            $filtertext = "<b>Essentials:</b> ";
            foreach ($boxes as $boxrow) {
                $boxid = $boxrow["ServiceID"];
                $boxdesc = $boxrow["ServiceDescGB"];
                $found = strstr($GuideMooringCodes, "|" . $boxid . "|");
                if ($found) {
                    $filtertext .= $boxdesc . ", ";
                }
            }
            $filtertext .= "\n";
        }
        if ($GuideHazardCodes) {
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__waterways_guide_services'))
                ->where($db->quoteName('ServiceCategory') . " = 'hazardguides'")
                ->order($db->quoteName('ServiceSortOrder'));
            $boxes = $db->setQuery($query)->loadAssocList();
            $filtertext .= "<b>Hazards:</b> ";
            foreach ($boxes as $boxrow) {
                $boxid = $boxrow["ServiceID"];
                $boxdesc = $boxrow["ServiceDescGB"];
                $found = strstr($GuideHazardCodes, "|" . $boxid . "|");
                if ($found) {
                    $filtertext .= $boxdesc . ", ";
                }
            }
            $filtertext .= "\n";
        }
    }

    $guidematch = 0;
    $listresults = '';
    $thisGuideCountry = '';
    $thisGuideWaterway = '';
    $thisGuideWaterwayAlpha = '';

    foreach ($guides as $row) {
        $GuideID = stripslashes($row["GuideID"]);
        $GuideNo = stripslashes($row["GuideNo"]);
        $GuideVer = stripslashes($row["GuideVer"]);
        $GuideCountry = stripslashes($row["GuideCountry"]);
        $GuideWaterway = mb_convert_encoding(stripslashes($row["GuideWaterway"]), "ISO-8859-1", "UTF-8");
        $GuideWaterway = str_replace("&", "and", $GuideWaterway);
        $GuideSummary = mb_convert_encoding(stripslashes($row["GuideSummary"]), "ISO-8859-1", "UTF-8");
        $GuideName = mb_convert_encoding(stripslashes($row["GuideName"]), "ISO-8859-1", "UTF-8");
        $GuideRef = mb_convert_encoding(stripslashes($row["GuideRef"]), "ISO-8859-1", "UTF-8");
        $GuideRating = stripslashes($row["GuideRating"]);
        $GuideLatLong = stripslashes($row["GuideLatLong"]);
        $GuideLocation = mb_convert_encoding(stripslashes($row["GuideLocation"]), "ISO-8859-1", "UTF-8");
        $GuideMooring = mb_convert_encoding(stripslashes($row["GuideMooring"]), "ISO-8859-1", "UTF-8");
        $GuideFacilities = mb_convert_encoding(stripslashes($row["GuideFacilities"]), "ISO-8859-1", "UTF-8");
        $GuideCodes = stripslashes($row["GuideCodes"]);
        $GuideCosts = stripslashes($row["GuideCosts"]);
        $GuideAmenities = mb_convert_encoding(stripslashes($row["GuideAmenities"]), "ISO-8859-1", "UTF-8");
        $GuideContributors = mb_convert_encoding(stripslashes($row["GuideContributors"]), "ISO-8859-1", "UTF-8");
        $GuideRemarks = mb_convert_encoding(stripslashes($row["GuideRemarks"]), "ISO-8859-1", "UTF-8");
        $GuideLat = mb_convert_encoding(stripslashes($row["GuideLat"]), "ISO-8859-1", "UTF-8");
        $GuideLong = mb_convert_encoding(stripslashes($row["GuideLong"]), "ISO-8859-1", "UTF-8");
        $GuideDocs = stripslashes($row["GuideDocs"]);
        $GuidePostingDate = stripslashes($row["GuidePostingDate"]);
        $GuideCategory = $row["GuideCategory"];
        $GuideStatus = $row["GuideStatus"];
        $GuideOrder = $row["GuideOrder"];

        if ($GuideStatus != 1) {
            if ($GuideStatus == 0) {
                $GuideName .= " (V. " . $GuideVer . " Pending)";
            }
            if ($GuideStatus == 2) {
                $GuideName .= " (V. " . $GuideVer . " Archived)";
            }
        }

        $GuideUpdate = stripslashes($row["GuideUpdate"]);
        $GuideUpdatedisplay = (empty($GuideUpdate) ? 'Pre 2009' : date('dmy', strtotime($GuideUpdate))) . " - Mooring Index: " . $GuideNo . " - Version: " . $GuideVer;

        if ($GuideCategory == 2) {
            switch ($GuideRating) {
                case "1":
                    $ratingtitle = "<b>Hazard:</b> Rating Low";
                    break;
                case "2":
                    $ratingtitle = "<b>Hazard:</b> Rating Medium";
                    break;
                case "3":
                    $ratingtitle = "<b>Hazard:</b> Rating High";
                    break;
                default:
                    $ratingtitle = "<b>Hazard:</b> Rating Unknown";
            }
            $GuideRatingText = $ratingtitle;
        } else {
            switch ($GuideRating) {
                case "":
                    $ratingtitle = "<b>Mooring:</b> Rating Unknown";
                    break;
                case "0":
                    $ratingtitle = "<b>Mooring:</b> Rating Doubtful";
                    break;
                case "1":
                    $ratingtitle = "<b>Mooring:</b> Rating Adequate";
                    break;
                case "2":
                    $ratingtitle = "<b>Mooring:</b> Rating Good";
                    break;
                case "3":
                    $ratingtitle = "<b>Mooring:</b> Rating Very Good";
                    break;
                default:
                    $ratingtitle = "<b>Mooring:</b> Rating Unknown";
            }
            $GuideRatingText = $ratingtitle;
        }

        if ($GuideCountry && strtoupper($GuideCountry) != strtoupper($thisGuideCountry)) {
            // lookup country name
            $query = $db->getQuery(true)
                ->select($db->quoteName('printable_name'))
                ->from($db->quoteName('#__waterways_guide_country'))
                ->where($db->quoteName('iso') . ' = ' . $db->quote(strtoupper($GuideCountry)));
            $countryrow = $db->setQuery($query)->loadAssoc();
            $CountryName = stripslashes($countryrow["printable_name"] ?? $GuideCountry);
            $listresults .= "1<<b>" . strtoupper($CountryName) . "</b>>\n";
            $thisGuideCountry = $GuideCountry;
        }

        // new waterway so make heading
        if ($GuideWaterway != $thisGuideWaterway) {
            $GuideWaterwayAlpha = substr($GuideWaterway, 0, 1);
            if ($GuideWaterwayAlpha != $thisGuideWaterwayAlpha) {
                $listresults .= "#NP\n";
                $thisGuideWaterwayAlpha = $GuideWaterwayAlpha;
            }
            $listresults .= "1<<b>" . $GuideWaterway . "</b>>\n\n";
            if ($GuideSummary) {
                $listresults .= "<b>Summary:</b> " . $GuideSummary . "\n";
            }
            $listresults .= "______________________________________________________________________________________________________\n";
            $thisGuideWaterway = $GuideWaterway;
            $GuideMooringNo = 0;
        }

        $GuideMooringNo = ($GuideMooringNo ?? 0) + 1;

        // convert dec to lat long
        if ($GuideLat && $GuideLong) {
            $GuideLatLong = WaterwaysHelper::decimalToDegree($GuideLat, 'LAT') . " , " . WaterwaysHelper::decimalToDegree($GuideLong, 'LON');
            $GuideLatLong = str_replace("&deg;", mb_convert_encoding("°", "ISO-8859-1", "UTF-8"), $GuideLatLong);
            $GuideLatLong = str_replace("&apos;", "'", $GuideLatLong);
        } else {
            $GuideLatLong = "Not known";
        }

        $listresults .= "2<" . $GuideName . ">\n";
        if ($GuideRating) {
            $listresults .= $GuideRatingText . "\n";
        }
        if ($GuideLatLong) {
            $listresults .= "<b>Lat/Long:</b> " . $GuideLatLong . "\n";
        }
        if ($GuideRef) {
            $listresults .= "<b>Reference:</b> " . $GuideRef . "\n";
        }
        if ($GuideLocation) {
            $listresults .= "<b>Location:</b> " . $GuideLocation . "\n";
        }
        if ($GuideMooring) {
            $listresults .= "<b>Mooring:</b> " . $GuideMooring . "\n";
        }

        if ($GuideCodes) {
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__waterways_guide_services'))
                ->order($db->quoteName('ServiceSortOrder'));
            switch ($GuideCategory) {
                case "1":
                    $query->where($db->quoteName('ServiceCategory') . " = 'mooringsguides'");
                    $boxestitle = "Essentials";
                    break;
                case "2":
                    $query->where($db->quoteName('ServiceCategory') . " = 'hazardguides'");
                    $boxestitle = "Hazard category";
                    break;
            }
            $boxes = $db->setQuery($query)->loadAssocList();
            $boxhtml = "";
            foreach ($boxes as $boxrow) {
                $boxid = $boxrow["ServiceID"];
                $boxdesc = $boxrow["ServiceDescGB"];
                $found = strstr($GuideCodes, "|" . $boxid . "|");
                if ($found) {
                    if ($boxhtml) {
                        $boxhtml .= ", ";
                    }
                    $boxhtml .= $boxdesc;
                }
            }
            $listresults .= "<b>" . $boxestitle . ":</b> " . $boxhtml . "\n";
        }

        if ($GuideFacilities) {
            $listresults .= "<b>Facilities:</b> " . $GuideFacilities . "\n";
        }
        if ($GuideCosts) {
            $listresults .= "<b>Costs:</b> " . $GuideCosts . "\n";
        }
        if ($GuideAmenities) {
            $listresults .= "<b>Amenities:</b> " . $GuideAmenities . "\n";
        }
        if ($GuideContributors) {
            $listresults .= "<b>Contributors:</b> " . $GuideContributors . "\n";
        }
        if ($GuideRemarks) {
            $listresults .= "<b>Remarks:</b> " . $GuideRemarks . "\n";
        }

        if ($GuideUpdate) {
            $listresults .= "<b>Last Update:</b> " . $GuideUpdatedisplay . "\n";
        }
        // update on-line link guide no
        $listresults .= "{" . $GuideID . "}\n";
        $listresults .= "______________________________________________________________________________________________________\n";

        $guidematch = 1;
    }
}

// PDF Generation using ezpdf
error_reporting(E_ALL);
set_time_limit(20000);

// Include ezpdf class - adjust path as needed
$ezpdfPath = __DIR__ . '/../tmpl/wwg/class.ezpdf.php';
if (!file_exists($ezpdfPath)) {
    die('PDF library not found: ' . $ezpdfPath);
}
include $ezpdfPath;

$thisdate = date("d/m/Y");
$longdate = date("F j, Y");

$col1r = 0;
$col1g = 0;
$col1b = 0;

$col2r = 0;
$col2g = 0;
$col2b = 0;

$col3r = 0;
$col3g = 0;
$col3b = 255;

// define a class extension for TOC
class Creport extends Cezpdf
{
    public $reportContents = array();

    function __construct($p, $o)
    {
        parent::__construct($p, $o);
    }

    function rf($info)
    {
        $tmp = $info['p'];
        $lvl = $tmp[0];
        $lbl = rawurldecode(substr($tmp, 1));
        $num = $this->ezWhatPageNumber($this->ezGetCurrentPageNumber());
        $this->reportContents[] = array($lbl, $num, $lvl);
        $this->addDestination('toc' . (count($this->reportContents) - 1), 'FitH', $info['y'] + $info['height']);
    }

    function dots($info)
    {
        $tmp = $info['p'];
        $lvl = $tmp[0];
        $lbl = substr($tmp, 1);
        $xpos = 500;

        switch ($lvl) {
            case '1':
                $size = 16;
                $thick = 1;
                break;
            case '2':
                $size = 12;
                $thick = 0.5;
                break;
            default:
                $size = 10;
                $thick = 0.5;
        }

        $this->saveState();
        $this->setLineStyle($thick, 'round', '', array(0, 10));
        $this->line($xpos, $info['y'], $info['x'] + 5, $info['y']);
        $this->restoreState();
        $this->addText($xpos + 5, $info['y'], $size, $lbl);
    }
}

$pdf = new Creport('a4', 'portrait');

$pdf->ezSetMargins(40, 50, 70, 70);
$lmx = 70;
$rmx = 595.25 - $lmx;

// put a line bottom on all pages
$all = $pdf->openObject();
$pdf->saveState();
$pdf->setStrokeColor($col1r, $col1g, $col1b, 1);
$pdf->line($rmx, 40, $lmx, 40);
$pdf->setStrokeColor($col2r, $col2g, $col2b, 1);
$pdf->setColor($col2r, $col2g, $col2b, 1);
$pdf->addText($lmx, 28, 10, $footer3 . " - " . $thisdate);

$footer2 = "The guides are compiled from member contributions which have been shared with the DBA for the sole use of its members.\n";
$footer2a = "Copyright " . date("Y ") . " DBA The Barge Association, " . $login_MembershipNo . ".\nFor use of DBA member " . $contact . " only.\n";

$pdf->addText($lmx - 2, 18, 5, $footer2);
$pdf->addText($lmx - 2, 10, 5, $footer2a);

$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all, 'all');

$pdf->ezSetDy(-75);

// Font paths - use component media folder
$mainFont = JPATH_SITE . '/media/com_waterways_guide/fonts/Helvetica.afm';
$codeFont = JPATH_SITE . '/media/com_waterways_guide/fonts/Courier.afm';

// Fallback to ezpdf fonts if component fonts don't exist
if (!file_exists($mainFont)) {
    $mainFont = __DIR__ . '/../tmpl/wwg/fonts/Helvetica.afm';
}
if (!file_exists($codeFont)) {
    $codeFont = __DIR__ . '/../tmpl/wwg/fonts/Courier.afm';
}

$pdf->selectFont($mainFont);

$pdf->setColor($col2r, $col2g, $col2b, 1);
$pdf->ezText("<b>DBA - The Barge Association</b>\n", 25, array('justification' => 'centre'));
$pdf->ezText("<b>Waterways Guide</b>\n", 20, array('justification' => 'centre'));
$pdf->ezText($mooringsguidedocintrotextpdf, 10, array('justification' => 'centre'));
if ($filtertext) {
    $pdf->ezText("<b>PLEASE NOTE - This guide is based on a filter selection:</b>\n\n" . $filtertext, 10, array('justification' => 'centre'));
}
$pdf->ezSetDy(-100);

$pdf->openHere('Fit');
$pdf->selectFont($mainFont);
$pdf->ezSetDy(-120);
$pdf->ezText($longdate . "\n", 16, array('justification' => 'centre'));

$pdf->ezNewPage();
$pdf->setColor($col1r, $col1g, $col1b, 1);
$pdf->ezStartPageNumbers($rmx, 28, 10, 'left', '', 1);
$pdf->setColor($col2r, $col2g, $col2b, 1);
$size = 8;
$textOptions = array('justification' => 'left');
$collecting = 0;
$code = '';

// Process the content
$lines = explode("\n", $listresults ?? '');
$maxlines = sizeof($lines);
$lineno = 0;

while ($lineno < $maxlines) {
    $line = $lines[$lineno];
    $line = chop($line);

    if (strlen($line) && $line[0] == '#') {
        switch ($line) {
            case '#NP':
                $pdf->ezNewPage();
                break;
            case '#C':
                $pdf->selectFont($codeFont);
                $textOptions = array('justification' => 'left', 'left' => 20, 'right' => 20);
                $size = 10;
                break;
            case '#c':
                $pdf->selectFont($mainFont);
                $textOptions = array('justification' => 'full');
                $size = 12;
                break;
        }
    } elseif ($collecting) {
        $code .= $line;
    } elseif (((strlen($line) > 1 && $line[1] == '<')) && $line[strlen($line) - 1] == '>') {
        switch ($line[0]) {
            case '1':
                $tmp = substr($line, 2, strlen($line) - 3);
                $tmp2 = $tmp . '<C:rf:1' . rawurlencode($tmp) . '>';
                $pdf->setColor($col3r, $col3g, $col3b, 1);
                $pdf->ezText($tmp2, 14, array('justification' => 'left'));
                $pdf->setColor($col2r, $col2g, $col2b, 1);
                break;
            default:
                $tmp = substr($line, 2, strlen($line) - 3);
                $tmp2 = $tmp . '<C:rf:2' . rawurlencode($tmp) . '>';
                $tmp2 = "<b>" . $tmp2 . "</b>";
                $pdf->setColor($col1r, $col1g, $col1b, 1);
                $pdf->ezText($tmp2, 14, array('justification' => 'left'));
                $pdf->setColor($col2r, $col2g, $col2b, 1);
                break;
        }
    } elseif (((strlen($line) > 1 && $line[0] == '{')) && $line[strlen($line) - 1] == '}') {
        $tmp = substr($line, 1, strlen($line) - 2);
        $pdf->ezText('<c:alink:http://' . $menu_url . '?guideaction=memberedit&infoid=' . $tmp . '>Click here to update this entry on-line</c:alink>');
    } else {
        $pdf->ezText($line, $size, $textOptions);
    }
    $lineno += 1;
}

$pdf->ezStopPageNumbers(1, 1);

// Add table of contents
$pdf->ezInsertMode(1, 1, 'after');
$pdf->ezNewPage();
$pdf->setColor($col1r, $col1g, $col1b, 1);
$pdf->ezText("<b>Index of waterways and locations</b>\n", 16, array('justification' => 'left'));
$pdf->setColor($col2r, $col2g, $col2b, 1);
$xpos = 520;
$contents = $pdf->reportContents;

foreach ($contents as $k => $v) {
    switch ($v[2]) {
        case '1':
            $pdf->ezText('<c:ilink:toc' . $k . '>' . $v[0] . '</c:ilink><C:dots:1' . $v[1] . '>', 12, array('aright' => $xpos));
            break;
        case '2':
            $pdf->ezText('<c:ilink:toc' . $k . '>' . $v[0] . '</c:ilink><C:dots:2' . $v[1] . '>', 10, array('left' => 10, 'aright' => $xpos));
            break;
    }
}

// Output the PDF
$pdf->ezStream();
