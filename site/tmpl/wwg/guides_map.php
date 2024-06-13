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
//echo($mapvars);

?>


<style type="text/css">
	<!--
	.pinpop {
		font-family: Verdana, Arial, Helvetica, sans-serif;
		font-family: Arial, sans-serif;
		font-size: 11px;
		width: 250px;
	}

	.sidebar {
		font-family: Verdana, Arial, Helvetica, sans-serif;
		font-size: 10px;
	}

	.Country {
		font-family: Verdana, Arial, Helvetica, sans-serif;
		font-size: 11px;
		font-weight: bold;
		color: #000000;
		background: #DDDDDD;
	}

	.Waterway {
		font-family: Verdana, Arial, Helvetica, sans-serif;
		font-size: 11px;
		font-weight: bold;
		color: #000000;
		background: #CCEEF7;
	}

	.Key {
		font-family: Verdana, Arial, Helvetica, sans-serif;
		font-size: 11px;
		color: #333333;
		background: #CCEEF7;
	}

	.pinpop_copyright {
		font-family: Verdana, Arial, Helvetica, sans-serif;

		font-size: 9px;
		color: #0000ff;
	}

	.Header {
		font-family: Verdana, Arial, Helvetica, sans-serif;
		font-size: 11px;
		color: #333333;
		background: #ffffff;
		padding-top: 4px;
		padding-bottom: 4px;
		padding-right: 4px;
		padding-left: 4px;
		border-bottom: 1px solid #333333;
	}
	-->
</style>


<div id="header" class="Header"></div>

<div class="col-xs-9" id="map" style="overflow: hidden; height: 550px;"></div>
<div class="col-xs-3" id="sidebar" style="overflow: auto; height: auto;"></div>