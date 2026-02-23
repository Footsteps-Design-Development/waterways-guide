<?php

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

$doc = Factory::getDocument();

$doc->addScript('media/com_waterways_guide/js/show-facility-tick-box-filter.js', 'text/javascript');

$query = $db->getQuery(true)
    ->select('*')
    ->from($db->qn('#__waterways_guide_services'))
    ->where($db->qn('ServiceCategory') . " = 'mooringsguides'")
    ->order($db->qn('ServiceSortOrder'));
$boxes = $db->setQuery($query)->loadAssocList();
$boxhtml = "";
foreach ($boxes as $boxrow) {
    $boxid = $boxrow["ServiceID"];
    $boxdesc = $boxrow["ServiceDescGB"];
    $found = strstr($GuideMooringCodes, "|" . $boxid . "|");
    if (!$found) {
        $boxhtml .= "<input type=\"checkbox\" name=\"facility" . $boxid . "\" value=\"" . $boxid . "\" onClick=\"changemooringcode(this,'" . $boxid . "')\">" . $boxdesc . "&nbsp;\n";
    } else {
        $boxhtml .= "<input type=\"checkbox\" name=\"facility" . $boxid . "\" value=\"" . $boxid . "\" checked onClick=\"changemooringcode(this,'" . $boxid . "')\">" . $boxdesc . "&nbsp;\n";
    }
}
//$filter.="<div id=\"mooringfilter\" style=\"margin-left:6px; background-color:dddddd; padding:6px;\"><b>Moorings</b> ".$boxhtml."</div>\n";
$filter .= "<div id=\"mooringfilter\" style=\"margin-left:6px; background-color:dddddd; padding:6px;\">" . $boxhtml . "</div>\n";

$query = $db->getQuery(true)
    ->select('*')
    ->from($db->qn('#__waterways_guide_services'))
    ->where($db->qn('ServiceCategory') . " = 'hazardguides'")
    ->order($db->qn('ServiceSortOrder'));
$boxes = $db->setQuery($query)->loadAssocList();
$boxhtml = "";
foreach ($boxes as $boxrow) {
    $boxid = $boxrow["ServiceID"];
    $boxdesc = $boxrow["ServiceDescGB"];
    $found = strstr($GuideHazardCodes, "|" . $boxid . "|");
    if (!$found) {
        $boxhtml .= "<input type=\"checkbox\" name=\"facility" . $boxid . "\" value=\"" . $boxid . "\" onClick=\"changehazardcode(this,'" . $boxid . "')\">" . $boxdesc . "&nbsp;\n";
    } else {
        $boxhtml .= "<input type=\"checkbox\" name=\"facility" . $boxid . "\" value=\"" . $boxid . "\" checked onClick=\"changehazardcode(this,'" . $boxid . "')\">" . $boxdesc . "&nbsp;\n";
    }
}

$filter .= "</td></tr>\n";
echo ("<input name=\"filteroption\" type=\"hidden\" value=\"M\">\n");
echo ("<tr><td colspan=4><b>Filter:</b> - tick boxes to refine filter or leave for everything, then click List or Map to display or refresh results<br>" . $filter . "</td> </tr>\n");

?>
<SCRIPT LANGUAGE="JavaScript">
    showfilter();
</script>
<?php

//---------------------------------------Display and Admin options---------------------------------------------


if ($admin == "open") {
    //allow filter by status	
    if (!$Status0 && !$Status1 && !$Status2) {
        $Status1 = 1; //live default
    }
    if ($Status0 == 1) {
        $status0clicked = " checked";
    } else {
        $status0clicked = "";
    }
    if ($Status1 == 1) {
        $status1clicked = " checked";
    } else {
        $status1clicked = "";
    }
    if ($Status2 == 1) {
        $status2clicked = " checked";
    } else {
        $status2clicked = "";
    }
    print "<tr><td class=table_stripe_even colspan=4><b>Admin status filter:</b> <input type=\"checkbox\" name=\"Status0\" value=\"1\"" . $status0clicked . "> Pending <input type=\"checkbox\" name=\"Status1\" value=\"1\"" . $status1clicked . "> Live <input type=\"checkbox\" name=\"Status2\" value=\"1\"" . $status2clicked . "> Archive</td></tr>\n";
}
print "<tr><td colspan=4>\n";
echo ("<input type=\"button\" class=\"btn btn-primary button_action\" name=\"list\" value=\"List\" onClick=\"document.form.guideaction.value='list';document.form.lastguideaction.value='list';document.form.submit()\">");

//print"<a href=\"#\" onClick=\"document.form.guideaction.value='list';document.form.submit()\"><img src=\"Image/common/preview.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Find guides on this waterway\" alt=\"Find guides on this waterway\"> List </a> ";
echo ("<input type=\"button\" class=\"btn btn-primary button_action\" name=\"map\" value=\"Map\" onClick=\"document.form.guideaction.value='map';document.form.lastguideaction.value='map';document.form.submit()\">");
//print"<br><a href=\"#\" onClick=\"document.form.guideaction.value='map';document.form.submit()\"><img src=\"Image/common/compass.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"View guides on a map\" alt=\"View guides on a map\"> Map </a>";

if (($country == "FR" && $waterway == "All") || ($country == "FR" && $waterway == "")) {
    echo ("<input type=\"button\" class=\"btn btn-primary button_action\" name=\"PDFfile1\" value=\"PDF text file A-L\" onClick=\"javascript:listtopdf(1)\">");
    echo ("<input type=\"button\" class=\"btn btn-primary button_action\" name=\"PDFfile2\" value=\"PDF text file M-Z\" onClick=\"javascript:listtopdf(2)\">");
    //print"<br><img src=\"Image/common/pdf.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Download PDF text file\" alt=\"Download PDF text file\"> Download PDF text file <a href=\"#\" onClick=\"javascript:listtopdf(1)\">Aa to Lys</a>&nbsp;&nbsp;<a href=\"#\" onClick=\"javascript:listtopdf(2)\">Marne to Yonne</a></a> ";

    //print"&nbsp;&nbsp;&nbsp;Save to kml <a href=\"#\" onClick=\"javascript:listtokml(1)\">Aa to Lys</a>&nbsp;&nbsp;<a href=\"#\" onClick=\"javascript:listtokml(2)\">Marne to Yonne</a><img src=\"Image/common/xml.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Save to a .kmz google maps file\" alt=\"Save to a .kmz google maps file\"></a> ";

} else {
    //print"<br><a href=\"#\" onClick=\"javascript:listtopdf()\"><img src=\"Image/common/pdf.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Download PDF text file\" alt=\"Download PDF text file\"> Download PDF text file </a> ";
    echo ("<input type=\"button\" class=\"btn btn-primary button_action\" name=\"PDFfile0\" value=\"PDF text file\" onClick=\"javascript:listtopdf()\">");
}
//print"</td><td colspan=2>\n";
//print"<br><a href=\"#\" onClick=\"javascript:listtokml()\"><img src=\"Image/common/xml.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Download KML map file\" alt=\"Download KML map file\"> Download KML map file </a> ";
echo ("<input type=\"button\" class=\"btn btn-primary button_action\" name=\"btnlisttokml\" value=\"KML map file\" onClick=\"javascript:listtokml()\">");

echo ("<input type=\"button\" class=\"btn btn-primary button_action\" name=\"map\" value=\"Add a new mooring\" onClick=\"document.form.guideaction.value='memberedit';document.form.infoid.value='new';document.form.submit()\">");
//print"<br><a href=\"#\" onClick=\"document.form.guideaction.value='memberedit';document.form.infoid.value='new';document.form.submit()\"><img src=\"Image/common/new.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Add a new mooring\" alt=\"Add a new mooring\"> Add a new mooring </a>";
echo ("<input type=\"button\" class=\"btn btn-primary button_action\" name=\"help\" value=\"Help\" onClick=\"document.form.guideaction.value='';document.form.infoid.value='new';document.form.submit()\">");
//echo("<a href=\"http://barges.org/component/content/article/113-faq/312-help-wg\" class=\"btn btn-primary button_action\" role=\"button\">Help</a>");
//<input type=\"button\" class=\"btn btn-primary button_action\" name=\"help\" value=\"Help\" onClick=\"document.form.guideaction.value='memberedit';document.form.infoid.value='new';document.form.submit()\">");
//print"<br><a href=\"http://barges.org/component/content/article/113-faq/312-help-wg\"><img src=\"Image/common/help.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"help\" alt=\"help\"> Help </a>";



print "</td></tr>\n";

if ($admin == "open") {
    print "</td></tr><td colspan=4>\n";
    print "<br><b>ADMIN:</b> <a href=\"#\" onClick=\"document.form.guideaction.value='edit';document.form.infoid.value='newmooring';document.form.submit()\">New mooring <img src=\"Image/common/new.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Add a new mooring entry\" alt=\"Add a new mooring entry\"></a>";
    print "&nbsp;&nbsp;&nbsp;<a href=\"#\" onClick=\"document.form.guideaction.value='edit';document.form.infoid.value='newhazard';document.form.submit()\">New hazard <img src=\"Image/common/new.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Add a new hazard entry\" alt=\"Add a new hazard entry\"></a>";
    print "&nbsp;&nbsp;&nbsp;<a href=\"#\" onClick=\"document.form.guideaction.value='adminreports';document.form.submit()\">Reports (admin) <img src=\"Image/common/txt.gif\" title=\"Run reports\" alt=\"Run reports\" width=\"18\" height=\"18\" border=\"0\"></a>";
    print "</td></tr>\n";
}

?>


<SCRIPT LANGUAGE="JavaScript">
			function listtopdf(option) {
				var form = 'form';
				dml = document.forms[form];
				var country = dml.country.value;
				if (country == "All") {
					alert("Please select a single country for this option.");
				} else {

					var waterway = dml.waterway.value;
					var filteroption = dml.filteroption.value;
					var GuideMooringCodes = dml.GuideMooringCodes.value;
					var GuideHazardCodes = dml.GuideHazardCodes.value;
					// var reportname = "/components/com_waterways_guide/views/wwg/tmpl/guides_list_to_pdf.php";
					var reportname = "<?php echo Route::_('index.php?option=com_waterways_guide&task=wwg.generatepdf', false); ?>";
   					// var reportname = "<?php // echo Route::_('index.php?option=com_waterways_guide&task=generatePdf', false); ?>";

					//var reportname = "/components/com_waterways_guide/views/wwg/tmpl/guides_list_to_pdf.php";
					var msid = "<?php echo ($login_memberid); ?>";
					var menu_url = "<?php echo ($menu_url); ?>";
					//check

					if (option > 0) {

						if (option == 1) {
							//alert("Please select another country, unfortunately France is not yet available as pdf");
							var waterway1 = "Aa";
							var waterway2 = "Lys (Fr)";
						}
						if (option == 2) {
							var waterway1 = "Marne";
							var waterway2 = "Yonne";
						}
						var mypage = reportname + "&country=" + country + "&waterway=" + waterway + "&filteroption=" + filteroption + "&GuideMooringCodes=" + GuideMooringCodes + "&GuideHazardCodes=" + GuideHazardCodes + "&waterway1=" + waterway1 + "&waterway2=" + waterway2 + "&msid=" + msid + "&menu_url=" + menu_url;

					} else {
						var mypage = reportname + "&country=" + country + "&waterway=" + waterway + "&filteroption=" + filteroption + "&GuideMooringCodes=" + GuideMooringCodes + "&GuideHazardCodes=" + GuideHazardCodes + "&msid=" + msid + "&menu_url=" + menu_url;
					}

					//alert(mypage);
					var myname = "WaterwaysGuide";
					var w = 1000;
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
			}

			function listtokml(option) {
				var form = 'form';
				dml = document.forms[form];
				var country = dml.country.value;
				if (country == "All") {
					alert("Please select a single country for this option.");
				} else {

					var waterway = dml.waterway.value;
					var filteroption = dml.filteroption.value;
					var GuideMooringCodes = dml.GuideMooringCodes.value;
					var GuideHazardCodes = dml.GuideHazardCodes.value;
					// var reportname = "/components/com_waterways_guide/views/wwg/tmpl/guides_list_to_kml.php";
					var reportname = "<?php echo Route::_('index.php?option=com_waterways_guide&task=wwg.generatekml', false); ?>";
					// var reportname = "<?php //echo Route::_('index.php?option=com_waterways_guide&task=generateKml', false); ?>";
					//var reportname = "components/com_waterways_guide/views/wwg/tmpl/guides_list_to_kml.php";
					var msid = "<?php echo ($login_memberid); ?>";
					var menu_url = "<?php echo ($menu_url); ?>";
					if (option > 0) {

						if (option == 1) {
							//alert("Please select another country, unfortunately France is not yet available as pdf");
							var waterway1 = "Aa";
							var waterway2 = "Lys (Fr)";
						}
						if (option == 2) {
							var waterway1 = "Marne";
							var waterway2 = "Yonne";
						}
						var mypage = reportname + "&country=" + country + "&waterway=" + waterway + "&filteroption=" + filteroption + "&GuideMooringCodes=" + GuideMooringCodes + "&GuideHazardCodes=" + GuideHazardCodes + "&waterway1=" + waterway1 + "&waterway2=" + waterway2 + "&msid=" + msid + "&menu_url=" + menu_url;

					} else {
						var mypage = reportname + "&country=" + country + "&waterway=" + waterway + "&filteroption=" + filteroption + "&GuideMooringCodes=" + GuideMooringCodes + "&GuideHazardCodes=" + GuideHazardCodes + "&msid=" + msid + "&menu_url=" + menu_url;
					}

					//alert(mypage);
					var myname = "WaterwaysGuide";
					var w = 400;
					var h = 200;
					var scroll = "no";
					var winl = (screen.width - w) / 2;
					var wint = (screen.height - h) / 2;
					winprops = 'height=' + h + ',width=' + w + ',top=' + wint + ',left=' + winl + ',scrollbars=' + scroll + ',resizable'
					mypage += (mypage.indexOf('?') != -1 ? '&' : '?') + 'nocache=' + Date.now();
					win = window.open(mypage, myname, winprops)
					if (parseInt(navigator.appVersion) >= 4) {
						win.window.focus();
					}

				}
			}
		</script>