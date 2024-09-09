<?php

echo ("<input name=\"country_tmp\" type=\"hidden\" value=\"$country\">\n");
echo ("<input name=\"waterway_tmp\" type=\"hidden\" value=\"$waterway\">\n");
if (isset($message)) {
    echo ("<tr><td colspan=4>$message<br></td></tr>\n");
}
echo ("<tr><td colspan=4><input type=\"button\" class=\"btn btn-primary button_action\" name=\"mapback\" value=\"Back to the filter\" onClick=\"document.form.guideaction.value='waterways';document.form.submit()\"><input type=\"button\" class=\"btn btn-primary\" name=\"listback\" value=\"Back to the list\" onClick=\"document.form.guideaction.value='list';document.form.lastguideaction.value='list';document.form.submit()\"></td></tr>\n");
echo ("<tr><td colspan=4>");
echo ("<input name=\"thisid\" type=\"hidden\" value=\"" . $thisid . "\">\n");
echo '<div id="guides_map"';
include("guides_map.php");
echo '</div>';
echo ("</td></tr>\n");
