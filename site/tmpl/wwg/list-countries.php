<?php

if (isset($message)) {
    echo ("<tr><td colspan=4>$message<br></td></tr>\n");
}



//get current countries
$query = $db->getQuery(true)
    ->select('DISTINCTROW ' . $db->qn('gt.GuideCountry') . ', ' . $db->qn('c.printable_name'))
    ->from($db->qn($guidetable) . ' AS ' . $db->qn('gt'))
    ->innerJoin($db->qn('#__waterways_guide_country', 'c') . ' ON ' . $db->qn('gt.GuideCountry') . ' = ' . $db->qn('c.iso'))
    ->order($db->qn('printable_name'));
$countries = $db->setQuery($query)->loadAssocList();
print "<tr><td colspan=4><b>Country</b><br><select name=\"country\" class=\"formcontrol\" onChange=\"document.form.guideaction.value='waterways';document.form.submit()\">\n";
print "<option value='All'>All</option>\n";
foreach ($countries as $row) {
    if ($row["GuideCountry"] == $country) {
        print "<option value=\"" . $row["GuideCountry"] . "\" selected>" . $row["printable_name"] . "</option>\n";
    } else {
        print "<option  value=\"" . $row["GuideCountry"] . "\">" . $row["printable_name"] . "</option>\n";
    }
}
print "	</select>  <a href=\"#\" onClick=\"document.form.guideaction.value='waterways';document.form.submit()\"> <img src=\"Image/common/preview.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Find guides in this country\" alt=\"Find guides in this country\"></a></td></tr>\n";

if (!$guideaction) {
    echo ("<tr><td colspan=4>" . $mooringsguidesectionintrotext . "</td></tr>\n");
}