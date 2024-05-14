<?php

/**
 * @version     1.0.0
 * @package     com_waterways_guide
 * @copyright   Copyright (C) 2024. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Russell English
 */

// no direct access
defined('_JEXEC') or die;

echo ("<input name=\"thisid\" type=\"hidden\" value=\"" . $thisid . "\">\n");
echo ("<input name=\"country_tmp\" type=\"hidden\" value=\"$country\">\n");
echo ("<input name=\"waterway_tmp\" type=\"hidden\" value=\"$waterway\">\n");
if (isset($message)) {
    echo ("<tr><td colspan=4>$message<br></td></tr>\n");
}
echo ("<tr><td colspan=4><input type=\"button\" class=\"btn btn-primary\" name=\"filterback\" value=\"Back to the filter\" onClick=\"document.form.guideaction.value='waterways';document.form.submit()\"></td></tr>\n");
//echo("<tr><td colspan=4><a href=\"#\" onClick=\"document.form.guideaction.value='waterways';document.form.submit()\">Back to the filter <img src=\"Image/common/back1.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Back to the filter\" alt=\"Back to the filter\"></a></td></tr>\n");
$query = $db->getQuery(true)
    ->select('*')
    ->from($db->qn($guidetable))
    ->order($db->qn(['GuideCountry', 'GuideWaterway', 'GuideOrder']));
if ($country && $country != 'All') $query->where($db->qn('GuideCountry') . ' = ' . $db->q($country));
if ($waterway && $waterway != 'All') $query->where($db->qn('GuideWaterway') . ' = ' . $db->q(stripslashes($waterway)));
//filter options
if ($filteroption == "ALL" || $filteroption == "M") {
    //add any ticks in $GuideMooringCodes and compare to $GuideCodes
    $filterwhere = '(' . $db->qn('GuideCategory') . ' = 1';
    //explode to array
    if ($GuideMooringCodes) {
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
if ($filterwhere) $query->where('(' . $filterwhere . ')');
elseif ($filteroption != "All") {
    //filter on Moorings and/or hazards without filter
    if ($filteroption == "M") $query->where($db->qn('GuideCategory') . ' = 1');
    else if ($filteroption == "H") $query->where($db->qn('GuideCategory') . ' = 2');
}
if (!$Status0 && !$Status1 && !$Status2) {
    $query->where($db->qn('GuideStatus') . ' = 1'); //live
} else {
    $GuideStatusWhere = [];
    if ($Status0 == 1) $GuideStatusWhere[] = $db->qn('GuideStatus') . ' = 0'; //pending
    if ($Status1 == 1) $GuideStatusWhere[] = $db->qn('GuideStatus') . ' = 1'; //live
    if ($Status2 == 1) $GuideStatusWhere[] = $db->qn('GuideStatus') . ' = 2'; //archive
    $query->where('(' . implode(' OR ', $GuideStatusWhere) . ')');
}
$guides = $db->setQuery($query)->loadAssocList();
$rows = count($guides);
# If the search was unsuccessful then Display Message try again.
if ($rows == 0) {
    print "<tr><td colspan=4>Sorry - there are no guides meeting your selection choices at the moment.</td></tr>\n";
} else {
    $GuideMooringNo = 0;
    $guidematch = 0;
    $ThisGuideWaterway = "";
    $listresults = "";
    $thisrow = "odd";
    $mapping = 0;
    foreach ($guides as $row) {
        $GuideID = stripslashes($row["GuideID"]);
        $GuideCountry = stripslashes($row["GuideCountry"]);
        $GuideWaterway = stripslashes($row["GuideWaterway"]);
        $GuideSummary = nl2br(stripslashes($row["GuideSummary"]));
        $GuideName = stripslashes($row["GuideName"]);
        $GuideRemarks = stripslashes($row["GuideRemarks"]);
        $GuideLocation = nl2br(stripslashes($row["GuideLocation"]));
        $GuideRating = $row["GuideRating"];
        $GuideStatus = $row["GuideStatus"];
        $GuideNo = stripslashes($row["GuideNo"]);
        $GuideOrder = $row["GuideOrder"];
        $GuideVer = stripslashes($row["GuideVer"]);
        $GuideCategory = $row["GuideCategory"];
        $GuideRef = $row["GuideRef"];

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
            //add hazard icon in front of name
            switch ($GuideRating) {
                case "1":
                    $ratingtitle = "Hazard rating Low";
                    break;
                case "2":
                    $ratingtitle = "Hazard rating Medium";
                    break;
                case "3":
                    $ratingtitle = "Hazard rating High";
            }
            $GuideCategoryIcon = "<img src=\"Image/common/hazard_small.gif\" title=\"" . $ratingtitle . "\" alt=\"" . $ratingtitle . "\" width=\"16\" height=\"16\" border=\"0\"> <b>HAZARD</b> ";
            //convert rating into stars
            $i = 1;
            $GuideRatingIcon = "";


            while ($i <= $GuideRating) {
                $GuideRatingIcon .= "<img src=\"Image/common/hazard_small.gif\" title=\"" . $ratingtitle . "\" alt=\"" . $ratingtitle . "\" width=\"16\" height=\"16\" border=\"0\">";
                $i++;
            }
        } else {

            switch ($GuideRating) {
                case "":
                    $ratingtitle = "Mooring rating Unknown";
                    break;
                case "0":
                    $ratingtitle = "Mooring rating Doubtful";
                    break;
                case "1":
                    $ratingtitle = "Mooring rating Adequate";
                    break;
                case "2":
                    $ratingtitle = "Mooring rating Good";
                    break;
                case "3":
                    $ratingtitle = "Mooring rating Very Good";
            }
            $GuideCategoryIcon = "";
            //convert rating into stars
            $i = 1;
            $GuideRatingIcon = "";


            while ($i <= $GuideRating) {
                $GuideRatingIcon .= "<img src=\"Image/common/star.gif\" title=\"" . $ratingtitle . "\" alt=\"" . $ratingtitle . "\" width=\"16\" height=\"16\" border=\"0\">";
                $i++;
            }
        }





        if ($row["GuideRemarks"]) {
            $msgtrail = substr($row["GuideRemarks"], 0, 120) . " . . . . . . . <br>Last update: " . $GuideUpdatedisplay;
        } else {
            $msgtrail = "Last update: " . $GuideUpdatedisplay;
        }

        $GuideLatLong = $row["GuideLatLong"];
        $GuideLat = $row["GuideLat"];
        $GuideLong = $row["GuideLong"];

        //create map latlong
        if ($GuideLat && $GuideLong) {
            //$GuideLatLong=decimal2degree($GuideLat,'LAT') ." , " . decimal2degree($GuideLong,'LON');
            //$declatlng="Available on map option ".$GuideLatLong;
            //$mapicon="<img src=\"Image/common/compass.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"".$declatlng."\" alt=\"".$declatlng."\">";			
            $mapicon = "<a href=\"#\" onClick=\"document.form.guideaction.value='map';document.form.lastguideaction.value='map';document.form.thisid.value=" . $GuideID . ";document.form.submit()\"><img src=\"Image/common/compass.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"View guides on a map\" alt=\"View guides on a map\"></a>";
            $mapping = 1;
        } else {
            $mapicon = "";
        }
        $guidematch = 1;

        if ($country != $thiscountry) {
            //lookup country name
            $query = $db->getQuery(true)
                ->select($db->qn('printable_name'))
                ->from($db->qn('#__waterways_guide_country'))
                ->where($db->qn('iso') . ' = ' . $db->q(strtoupper($GuideCountry)));
            $countryrow = $db->setQuery($query)->loadAssoc();
            $CountryName = stripslashes($countryrow["printable_name"]);
            $outputlistresults .= "<tr><td colspan=4><h2>$CountryName</h2></td></tr>\n";
            $thiscountry = $country;
        }
        if ($GuideWaterway != $ThisGuideWaterway && $listresults) {
            //new waterway so output last one details
            if ($GuideMooringNo == 1) {
                $GuideMooringNoSummary = $GuideMooringNo . " location listed";
            } else {
                $GuideMooringNoSummary = $GuideMooringNo . " locations listed";
            }

            $outputlistresults .= "<tr valign='top'><td colspan=4 class='table_admin_profile'>" . $DisplayGuideSummary . $GuideMooringNoSummary . "</td></tr>\n";
            $outputlistresults .= "<tr><td><b>Name</b></td><td><b>Location</b></td><td><b>Rating</b></td><td><b>Map</b></td></tr>\n";
            $outputlistresults .= $listresults;
            $outputlistresults .= "<tr valign='top'><td colspan=4><hr></td></tr>\n";
            $listresults = "";
            $DisplayGuideSummary = "";
            $thisrow = "odd";
            $GuideMooringNo = 0;
        }
        if ($GuideWaterway != $ThisGuideWaterway && !$listresults) {
            //new waterway
            $outputlistresults .= "<tr valign='top'><td colspan=4><h3>$GuideWaterway</h3></td></tr>\n";
            $DisplayGuideSummary = "";
            $ThisGuideWaterway = $GuideWaterway;
        }
        $GuideMooringNo += 1;

        //get summmary and concatinate
        if ($GuideSummary) {
            $DisplayGuideSummary .= $GuideSummary . "<br><br>";
        }


        if ($thisrow == "odd") {
            $rowclass = "table_stripe_even";
            $thisrow = "even";
        } else {
            $rowclass = "table_stripe_odd";
            $thisrow = "odd";
        }
        if ($GuideCategory == 2) {
            //hazard
            //$rowclass="table_stripe_hazard";	
        }
        if ($admin == "open") {
            if ($GuideRef == "") {
                $GuideRef = "?";
            }
            $adminlink = " - Ref: " . $GuideRef . " - Sequence: " . $GuideOrder . "";
            //option if you want to edit from list rather than detail
            //$adminlink="<a href=\"#\" onClick=\"document.form.guideaction.value='edit';document.form.infoid.value='$GuideID';document.form.submit()\"><img src=\"Image/common/open.gif\" width=\"18\" height=\"18\" border=\"0\" alt=\"Update this entry\"></a>";
        } else {
            $adminlink = "";
        }


        $listresults .= "<tr><td class=$rowclass><a href=\"#\" onClick=\"document.form.guideaction.value='detail';document.form.guideid.value='$GuideID';document.form.submit()\">" . $GuideName . "</a></td><td class=$rowclass>" . $GuideCategoryIcon . $GuideLocation . "</td><td class=$rowclass>$GuideRatingIcon</td><td class=$rowclass>$mapicon</td></tr>\n";
        if ($msgtrail) {
            $listresults .= "<tr><td class=trailer colspan=4><a href=\"#\" onClick=\"document.form.guideaction.value='detail';document.form.guideid.value='$GuideID';document.form.submit()\">" . $msgtrail . "</a>" . $adminlink . "</td></tr>\n";
        }
    }
    if ($GuideMooringNo > 0) {
        //add on last one
        if ($GuideMooringNo == 1) {
            $GuideMooringNoSummary = $GuideMooringNo . " location listed";
        } else {
            $GuideMooringNoSummary = $GuideMooringNo . " locations listed";
        }
        $outputlistresults .= "<tr valign='top'><td colspan=4 class='table_admin_profile'>" . $DisplayGuideSummary . $GuideMooringNoSummary . "</td></tr>\n";
        $outputlistresults .= "<tr><td><b>Name</b></td><td><b>Location</b></td><td><b>Rating</b></td><td><b>Map</b></td></tr>\n";
        $outputlistresults .= $listresults;
        $outputlistresults .= "<tr valign='top'><td colspan=4><hr></td></tr>\n";
        $DisplayGuideSummary = "";
    }

    $listresults = $outputlistresults;

    $listresults .= "<tr><td colspan=4>" . $copyright_guides . "</td></tr>";

    if ($guidematch == 1) {
        if ($rows == 1) {
            print "<tr><td colspan=4>$rows location listed - click the Name column for details \n";
        } else {
            print "<tr><td colspan=4>$rows locations listed - click the Name column for details \n";
        }
        //print or email
        //PRINT "<tr><td colspan=4><a href=\"#\" onClick=\"document.form.guideaction.value='print';document.form.guideid.value='$GuideID';document.form.submit()\">Print this listing <img src=\"Image/common/print.gif\" alt=\"Print this listing\" width=\"18\" height=\"18\" border=\"0\"></a> with details or <a href=\"#\" onClick=\"document.form.guideaction.value='emailme';document.form.guideid.value='$GuideID';document.form.submit()\">email it to me <img src=\"Image/common/email.gif\" alt=\"email it to me\" width=\"18\" height=\"18\" border=\"0\"></a></td></tr>\n";
        //PRINT " or <a href=\"#\" onClick=\"document.form.guideaction.value='printlist';document.form.submit()\">here to view, save or email the full details <img src=\"Image/common/txt.gif\" title=\"View, save or email the full details\" alt=\"View, save or email the full details\" width=\"18\" height=\"18\" border=\"0\"></a>\n";
        print "</td></tr>";
        print $listresults . "\n";
        //PRINT "<tr><td colspan=4>".$GuideMooringCodes."</td></tr>\n";

    } else {
        print "<tr><td colspan=4>Sorry - there are no guides meeting your selection choices at the moment.</td></tr>\n";
    }
}
//PRINT "<tr><td colspan=4>". $where." Filter option=".$filteroption."</td></tr>";
echo ("<input name=\"mapping\" type=\"hidden\" value=\"" . (isset($mapping) ? $mapping : '') . "\">\n");


			//exit();