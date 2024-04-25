<?php


echo ("<input name=\"country_tmp\" type=\"hidden\" value=\"$country\">\n");
echo ("<input name=\"waterway_tmp\" type=\"hidden\" value=\"$waterway\">\n");

if (!$guideid && $infoid) {
    $guideid = $infoid;
}
echo ("<input name=\"thisid\" type=\"hidden\" value=\"" . $thisid . "\">\n");
$query = $db->getQuery(true)
    ->select('*')
    ->from($db->qn($guidetable));
if ($guideno) {
    $query->where($db->qn('GuideNo') . ' = ' . $db->q($guideno))
        ->order($db->qn('GuideVer') . ' DESC')
        ->setLimit(1);
} else $query->where($db->qn('GuideID') . ' = ' . $db->q($guideid));
$result = $db->setQuery($query)->loadAssocList();
$num_rows = count($result);

# If the search was unsuccessful then Display Message try again.
if (!$num_rows) {
    echo ("<tr><td colspan=4>Sorry - no details available for this guide<br><hr></td></tr>");
    exit();
}

$datenow = time();
$row = reset($result);
$GuideID = stripslashes($row["GuideID"]);
$GuideNo = stripslashes($row["GuideNo"]);
$GuideVer = stripslashes($row["GuideVer"]);
$GuideCountry = stripslashes($row["GuideCountry"]);
$GuideWaterway = stripslashes($row["GuideWaterway"]);
$GuideSummary = nl2br(stripslashes($row["GuideSummary"]));
$GuideName = stripslashes($row["GuideName"]);
$GuideRef = stripslashes($row["GuideRef"]);
$GuideOrder = stripslashes($row["GuideOrder"]);
$GuideLatLong = stripslashes($row["GuideLatLong"]);
$GuideLocation = nl2br(stripslashes($row["GuideLocation"]));
$GuideMooring = nl2br(stripslashes($row["GuideMooring"]));
$GuideFacilities = nl2br(stripslashes($row["GuideFacilities"]));
$GuideCodes = stripslashes($row["GuideCodes"]);
$GuideCosts = nl2br(stripslashes($row["GuideCosts"]));
$GuideRating = stripslashes($row["GuideRating"]);
$GuideAmenities = nl2br(stripslashes($row["GuideAmenities"]));
$GuideContributors = nl2br(stripslashes($row["GuideContributors"]));
$GuideRemarks = nl2br(stripslashes($row["GuideRemarks"]));
$GuideLat = stripslashes($row["GuideLat"]);
$GuideLong = stripslashes($row["GuideLong"]);
//convert dec to lat long
if ($GuideLat && $GuideLong) {
    $GuideLatLong = decimal2degree($GuideLat, 'LAT') . " , " . decimal2degree($GuideLong, 'LON');
    //$mapicon=" View on a map <a href='javascript:Map(\"/components/com_membership/views/wwg/tmpl/guides_map_pop.php?thisid=".$GuideID."\")'><img src=\"Image/common/compass.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"View on a map\" alt=\"View on a map\"></a>";

    $mapicon = " View on a map <a href=\"#\" onClick=\"document.form.guideaction.value='map';document.form.lastguideaction.value='map';document.form.thisid.value=" . $GuideID . ";document.form.submit()\"><img src=\"Image/common/compass.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"View on a map\" alt=\"View on a map\"></a>";
    $mapping = 1;
} else {
    $GuideLatLong = "Not known";
    $mapicon = "";
}

$GuideDocs = stripslashes($row["GuideDocs"]);
$GuidePostingDate = stripslashes($row["GuidePostingDate"]);
$GuideCategory = stripslashes($row["GuideCategory"]);
$GuideUpdate = stripslashes($row["GuideUpdate"]);
$GuideStatus = stripslashes($row["GuideStatus"]);
$GuideUpdatedisplay = (empty($GuideUpdate) ? 'Date unknown' : date('Y-m-d', strtotime($GuideUpdate))) . " - Mooring Index: " . $GuideNo . " - Version: " . $GuideVer;
$i = 1;
while ($i <= $GuideRating) {
    $GuideRatingIcon .= "<img src=\"Image/common/star.gif\" title=\"rating\" alt=\"rating\" width=\"16\" height=\"16\" border=\"0\">";
    $i++;
}
if ($admin == "open") {
    //$adminlink="";
    $adminlink = "<input type=\"button\" class=\"btn btn-primary\" name=\"edit\" value=\"Update this entry (admin)\" onClick=\"document.form.guideaction.value='edit';document.form.infoid.value='$GuideID';document.form.submit()\">";
    //$adminlink="<a href=\"#\" onClick=\"document.form.guideaction.value='edit';document.form.infoid.value='$GuideID';document.form.submit()\">Update this entry (admin) <img src=\"Image/common/open.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Update this entry\" alt=\"Update this entry\"></a> <a href=\"#\" onClick=\"document.form.guideaction.value='memberedit';document.form.infoid.value='$GuideID';document.form.submit()\">Update this entry <img src=\"Image/common/open.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Submit an update to this mooring\" alt=\"Submit an update to this mooring\"></a>";
} else {
    $adminlink = "<input type=\"button\" class=\"btn btn-primary\" name=\"memberedit\" value=\"Submit an update to this entry\" onClick=\"document.form.guideaction.value='memberedit';document.form.infoid.value='$GuideID';document.form.submit()\">";
    //$adminlink="<a href=\"#\" onClick=\"document.form.guideaction.value='memberedit';document.form.infoid.value='$GuideID';document.form.submit()\">Submit an update to this entry <img src=\"Image/common/open.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Submit an update to this entry\" alt=\"Submit an update to this entry\"></a>";
}

//echo("<tr><td colspan=4><a href=\"#\" onClick=\"document.form.guideaction.value='list';document.form.submit()\">Back to the list <img src=\"Image/common/back1.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Back to the list\" alt=\"Back to the list\"></a> ".$adminlink."</td></tr>\n");
echo ("<tr><td colspan=4><input type=\"button\" class=\"btn btn-primary\" name=\"listback\" value=\"Back to the list\" onClick=\"document.form.guideaction.value='list';document.form.lastguideaction.value='list';document.form.submit()\"><input type=\"button\" class=\"btn btn-primary\" name=\"mapback\" value=\"Back to the map\" onClick=\"document.form.guideaction.value='map';document.form.lastguideaction.value='map';document.form.submit()\"> " . $adminlink . "</td></tr>\n");

$listresults = "<table border=\"0\" cellpadding=\"1\" cellspacing=\"2\">\n";
//$listresults.="<tr valign='top'><td colspan=2><b>".$thisguideenquiry."</b></td></tr>";
$listresults .= "<tr valign='top'><td><b>Waterway:</b></td><td>" . $GuideWaterway . "</td></tr>\n";

$listresults .= "<tr valign='top'><td><b>Name:</b></td><td>" . $GuideName . "</td></tr>\n";
if ($GuideRating) {
    $listresults .= "<tr valign='top'><td><b>Rating:</b></td><td>" . $GuideRatingIcon . "</td></tr>\n";
}

if ($GuideLatLong) {
    $listresults .= "<tr valign='top'><td><b>Position:</b></td><td>" . $GuideLatLong . " " . $mapicon . "</td></tr>\n";
}
if ($GuideRef) {
    $listresults .= "<tr valign='top'><td><b>Reference:</b></td ><td>" . $GuideRef . "</td></tr>\n";
}
if ($GuideLocation) {
    $listresults .= "<tr valign='top'><td><b>Location:</b></td ><td>" . $GuideLocation . "</td></tr>\n";
}
switch ($GuideCategory) {
    case "1":
        $GuideCategoryDesc = "Mooring";
        break;
    case "2":
        $GuideCategoryDesc = "<img src=\"Image/common/hazard_small.gif\" title=\"hazard\" alt=\"hazard\" width=\"16\" height=\"16\" border=\"0\"> Hazard";
        break;
}
$listresults .= "<tr valign='top'><td><b>Category:</b></td><td>" . $GuideCategoryDesc . "</td></tr>\n";

if ($GuideMooring) {
    $listresults .= "<tr valign='top'><td><b>Mooring:</b></td><td>" . $GuideMooring . "</td></tr>\n";
}

if ($GuideCodes) {
    //add tick boxes here
    switch ($GuideCategory) {
        case "1":
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->qn('tblServices'))
                ->where($db->qn('ServiceCategory') . " = 'mooringsguides'")
                ->order($db->qn('ServiceSortOrder'));
            $boxes = $db->setQuery($query)->loadAssocList();
            $boxestitle = "Essentials";
            break;
        case "2":
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->qn('tblServices'))
                ->where($db->qn('ServiceCategory') . " = 'hazardguides'")
                ->order($db->qn('ServiceSortOrder'));
            $boxes = $db->setQuery($query)->loadAssocList();
            $boxestitle = "Hazard category";
            break;
    }

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


    $listresults .= "<tr valign='top'><td><b>" . $boxestitle . "</b></td><td>" . $boxhtml . "</td></tr>\n";
}


if ($GuideFacilities) {
    $listresults .= "<tr valign='top'><td><b>Facilities:</b></td><td>" . $GuideFacilities . "</td></tr>\n";
}

if ($GuideCosts) {
    $listresults .= "<tr valign='top'><td><b>Costs:</b></td><td>" . $GuideCosts . "</td></tr>\n";
}
if ($GuideAmenities) {
    $listresults .= "<tr valign='top'><td><b>Amenities:</b></td ><td>" . $GuideAmenities . "</td></tr>\n";
}


if ($GuideContributors) {
    $listresults .= "<tr valign='top'><td><b>Contributors:</b></td ><td>" . $GuideContributors . "</td></tr>\n";
}
if ($GuideRemarks) {
    $listresults .= "<tr valign='top'><td><b>Remarks:</b></td><td>" . $GuideRemarks . "</td></tr>\n";
}

if ($GuideUpdate) {
    $listresults .= "<tr valign='top'><td><b>Last Update:</b></td><td>" . $GuideUpdatedisplay . "</td></tr>\n";
}

$listresults .= "<tr><td colspan=2>" . $copyright_guides . "</td></tr>\n";
$listresults .= "</table>\n";
echo ("<tr><td colspan=4>" . $listresults . "</td></tr>\n");

//exit();
?>

<script type="text/javascript">
    function Map(path) {
        var mypage = path;
        var myname = "map";
        //var w = (screen.width - 100);
        //var h = (screen.height - 100);
        var w = 810;
        var h = 500;
        var scroll = "yes";
        var winl = (screen.width - w) / 2;
        var wint = (screen.height - h) / 2;
        winprops = 'height=' + h + ',width=' + w + ',top=' + wint + ',left=' + winl + ',scrollbars=' + scroll + ',resizable'
        mypage += (mypage.indexOf('?') != -1 ? '&' : '?') + 'nocache=' + Date.now();
        win = window.open(mypage, myname, winprops)
        if (parseInt(navigator.appVersion) >= 4) {
            win.window.focus();
        }
    }
</script>