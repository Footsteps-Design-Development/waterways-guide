<?php

$mapvars = "";

if ($country) {
    $mapvars = "?country=" . $country;
}
if ($waterway) {
    if (!$mapvars) {
        $mapvars = "?waterway=" . $waterway;
    } else {
        $mapvars = "?country=" . $country . "&waterway=" . $waterway;
    }
}
if ($guidetable) {
    if (!$mapvars) {
        $mapvars = "?guidetable=" . $guidetable;
    } else {
        $mapvars .= "&guidetable=" . $guidetable;
    }
}
if ($GuideMooringCodes) {
    if (!$mapvars) {
        $mapvars = "?GuideMooringCodes=" . $GuideMooringCodes;
    } else {
        $mapvars .= "&GuideMooringCodes=" . $GuideMooringCodes;
    }
}
if ($GuideHazardCodes) {
    if (!$mapvars) {
        $mapvars = "?GuideHazardCodes=" . $GuideHazardCodes;
    } else {
        $mapvars .= "&GuideHazardCodes=" . $GuideHazardCodes;
    }
}
if ($filteroption) {
    if (!$mapvars) {
        $mapvars = "?filteroption=" . $filteroption;
    } else {
        $mapvars .= "&filteroption=" . $filteroption;
    }
}
if ($thisid) {
    if (!$mapvars) {
        $mapvars = "?thisid=" . $thisid;
    } else {
        $mapvars .= "&thisid=" . $thisid;
    }
}
echo($mapvars);

?>


<div id="header" class="Header"></div>

<div class="col-xs-9" id="map" style="overflow: hidden; height: 550px;"></div>
<div class="col-xs-3" id="sidebar" style="overflow: auto; height: auto;"></div>
