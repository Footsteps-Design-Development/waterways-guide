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

//get current waterways
$query = $db->getQuery(true)
    ->select('DISTINCTROW ' . $db->qn('GuideWaterway'))
    ->from($db->qn($guidetable))
    ->where($db->qn('GuideStatus') . ' = 1')
    ->order($db->qn('GuideWaterway'));
if ($country && $country != 'All') $query->where($db->qn('GuideCountry') . ' = ' . $db->q($country));
$waterways = $db->setQuery($query)->loadAssocList();
$rows = count($waterways);

# If the search was unsuccessful then Display Message try again.
if ($rows == 0) {
    print "<tr><td colspan=4>Sorry - there are no waterways in that country listed at the moment.</td></tr>\n";
} else {



    print "<tr><td colspan=4><b>Waterway</b><br><select name=\"waterway\" class=\"formcontrol\" onChange=\"document.form.guideaction.value='tick_filter';document.form.submit()\">\n";

    print "<option value='All'>All</option>\n";
    foreach ($waterways as $row) {
        $GuideWaterway = $row["GuideWaterway"];
        //$debug.=",".$GuideWaterway;
        if (addslashes($GuideWaterway) == addslashes($waterway)) {
            print "<option value=\"" . ($GuideWaterway) . "\" selected>" . stripslashes($GuideWaterway) . "</option>\n";
        } else {
            print "<option value=\"" . ($GuideWaterway) . "\">" . stripslashes($GuideWaterway) . "</option>\n";
        }
    }
    print "	</select>  <a href=\"#\" onClick=\"document.form.guideaction.value='tick_filter';document.form.submit()\"> <img src=\"Image/common/preview.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Find guides on this waterway\" alt=\"Find guides on this waterway\"></a></td> </tr>\n";
}
