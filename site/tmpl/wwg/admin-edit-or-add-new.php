<?php
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
    function diff($old, $new)
    {
        $maxlen = 0;
        foreach ($old as $oindex => $ovalue) {
            $nkeys = array_keys($new, $ovalue);
            foreach ($nkeys as $nindex) {
                $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
                    $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
                if ($matrix[$oindex][$nindex] > $maxlen) {
                    $maxlen = $matrix[$oindex][$nindex];
                    $omax = $oindex + 1 - $maxlen;
                    $nmax = $nindex + 1 - $maxlen;
                }
            }
        }
        if ($maxlen == 0) return array(array('d' => $old, 'i' => $new));
        return array_merge(
            diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
            array_slice($new, $nmax, $maxlen),
            diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen))
        );
    }

    function htmlDiff($old, $new)
    {
        if ($old != $new) {
            $diff = diff(explode(' ', $old), explode(' ', $new));
            $ret = '';
            foreach ($diff as $k) {
                if (is_array($k)) {
                    $ret .= (!empty($k['d']) ? "<del>" . implode(' ', $k['d']) . "</del> " : '') . (!empty($k['i']) ? "<ins>" . implode(' ', $k['i']) . "</ins> " : '');
                } else $ret .= $k . ' ';
            }

            return "<b>Changes</b><br><div class=guidechange>$ret</div>";
        } else {
            return;
        }
    }


    //echo("<tr><td colspan=4>");
    //echo("<iframe id=\"map\" src=\"guides_map.php\" width=550 height=550 marginwidth=0 marginheight=0 hspace=0 vspace=0 frameborder=0 scrolling=no></iframe>\n");

    //include("guides_edit.php");
    //echo("</td></tr>\n");


    echo ("<tr><td class=content_introduction><b>Edit entry by Administrator</b></td></tr>\n");
    echo ("<tr><td><input type=\"button\" class=\"btn btn-primary\" name=\"listback\" value=\"Back to the list\" onClick=\"document.form.guideaction.value='list';document.form.lastguideaction.value='list';document.form.submit()\"><input type=\"button\" class=\"btn btn-primary\" name=\"mapback\" value=\"Back to the map\" onClick=\"document.form.guideaction.value='map';document.form.lastguideaction.value='map';document.form.submit()\"> " . (isset($adminlink) ? $adminlink : '') . "</td></tr>\n");

    $user       = Factory::getUser();
    $login_memberid = $user->id;
    if($user->guest) {
        $link  = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode(JUri::current()), "You must be logged in to view this content");
        Factory::getApplication()->redirect($link);
    }

    if ($errmsg) {
        echo ("<tr><td><font color=ff0000><b>$errmsg</b></font></td></tr>\n");
    }
    $GuideUpdate = date("Y-m-d H:i:s");;

    if ($infoid == "newmooring") {
        echo ("<tr><td>Enter the mooring details below and click <a href=\"#\" onClick=\"document.form.guideaction.value='save';document.form.submit()\"> here <img src=\"Image/common/save.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Save this entry\" alt=\"Save this entry\"></a> to save.</td></tr>\n");
        $GuidePostingDate = $GuideUpdate;
        $GuideCategory = 1;
    } elseif ($infoid == "newhazard") {
        echo ("<tr><td>Enter the hazard details below and click <a href=\"#\" onClick=\"document.form.guideaction.value='save';document.form.submit()\"> here <img src=\"Image/common/save.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Save this entry\" alt=\"Save this entry\"></a> to save.</td></tr>\n");
        $GuidePostingDate = $GuideUpdate;
        $GuideCategory = 2;
    } else {
        if (!$errmsg) {
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->qn($guidetable))
                ->where($db->qn('GuideID') . ' = ' . $db->q($infoid));
            $result = $db->setQuery($query)->loadAssocList();
            foreach ($result as $row) {
                $GuideID = stripslashes($row["GuideID"]);
                $GuideNo = stripslashes($row["GuideNo"]);
                $GuideVer = stripslashes($row["GuideVer"]);
                $GuideCountry = stripslashes($row["GuideCountry"]);
                $GuideWaterway = stripslashes($row["GuideWaterway"]);
                $GuideSummary = stripslashes($row["GuideSummary"]);
                $GuideOrder = $row["GuideOrder"];
                $GuideName = stripslashes($row["GuideName"]);
                $GuideRef = stripslashes($row["GuideRef"]);
                $GuideLatLong = stripslashes($row["GuideLatLong"]);
                $GuideLocation = stripslashes($row["GuideLocation"]);
                $GuideMooring = stripslashes($row["GuideMooring"]);
                $GuideFacilities = stripslashes($row["GuideFacilities"]);
                $GuideCodes = stripslashes($row["GuideCodes"]);
                $GuideCosts = stripslashes($row["GuideCosts"]);
                // $GuideRating = stripslashes($row["GuideRating"]);
                $GuideRating = $row["GuideRating"];
                $GuideAmenities = stripslashes($row["GuideAmenities"]);
                $GuideContributors = stripslashes($row["GuideContributors"]);
                $GuideRemarks = stripslashes($row["GuideRemarks"]);
                $GuideLat = stripslashes($row["GuideLat"]);
                $GuideLong = stripslashes($row["GuideLong"]);
                $GuideDocs = stripslashes($row["GuideDocs"]);
                $GuidePostingDate = $row["GuidePostingDate"];
                $GuideCategory = stripslashes($row["GuideCategory"]);
                $GuideUpdate = $row["GuideUpdate"];
                $GuideStatus = stripslashes($row["GuideStatus"]);
                $GuideEditorMemNo = stripslashes($row["GuideEditorMemNo"]);
                if ($status == 1) {
                    $statustext = "posted on site";
                } elseif ($status == 0) {
                    $statustext = "pending";
                } elseif ($status == 3) {
                    $statustext = "archived";
                }
                $GuideUpdatedisplay = (empty($GuideUpdate) ? 'Date unknown' : date('Y-m-d', strtotime($GuideUpdate))) . " - Mooring Index: " . $GuideNo . " - Version: " . $GuideVer;
            }
        }
        //check for previous version to compare
        if ($GuideVer > 1) {
            $OldGuideVer = $GuideVer - 1;
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->qn('#__waterways_guide'))
                ->where($db->qn('GuideNo') . ' = ' . $db->q($GuideNo))
                ->where($db->qn('GuideVer') . ' = ' . $db->q($OldGuideVer));
            $oldguide = $db->setQuery($query)->loadAssocList();
            $rows = count($oldguide);
            if ($rows == 0) {
                echo ("<div>Sorry - can't find version " . $OldGuideVer . " of this guide</div>\n");
            } else {
                $row = reset($oldguide);
                $OldGuideCountry = stripslashes($row["GuideCountry"]);
                $ChangeGuideCountry = htmlDiff($OldGuideCountry, $GuideCountry);
                $OldGuideWaterway = stripslashes($row["GuideWaterway"]);
                $ChangeGuideWaterway = htmlDiff($OldGuideWaterway, $GuideWaterway);
                $OldGuideSummary = stripslashes($row["GuideSummary"]);
                $ChangeGuideSummary = htmlDiff($OldGuideSummary, $GuideSummary);
                $OldGuideOrder = $row["GuideOrder"];
                $ChangeGuideOrder = htmlDiff($OldGuideOrder, $GuideOrder);
                $OldGuideName = stripslashes($row["GuideName"]);
                $ChangeGuideName = htmlDiff($OldGuideName, $GuideName);
                $OldGuideRef = stripslashes($row["GuideRef"]);
                $ChangeGuideRef = htmlDiff($OldGuideRef, $GuideRef);
                $OldGuideLatLong = stripslashes($row["GuideLatLong"]);
                $ChangeGuideLatLong = htmlDiff($OldGuideLatLong, $GuideLatLong);
                $OldGuideLocation = stripslashes($row["GuideLocation"]);
                $ChangeGuideLocation = htmlDiff($OldGuideLocation, $GuideLocation);
                $OldGuideMooring = stripslashes($row["GuideMooring"]);
                $ChangeGuideMooring = htmlDiff($OldGuideMooring, $GuideMooring);
                $OldGuideFacilities = stripslashes($row["GuideFacilities"]);
                $ChangeGuideFacilities = htmlDiff($OldGuideFacilities, $GuideFacilities);
                $OldGuideCodes = stripslashes($row["GuideCodes"]);
                $ChangeGuideCodes = htmlDiff($OldGuideCodes, $GuideCodes);
                $OldGuideCosts = stripslashes($row["GuideCosts"]);
                $ChangeGuideCosts = htmlDiff($OldGuideCosts, $GuideCosts);
                $OldGuideRating = stripslashes($row["GuideRating"]);
                $ChangeGuideRating = htmlDiff($OldGuideRating, $GuideRating);
                $OldGuideAmenities = stripslashes($row["GuideAmenities"]);
                $ChangeGuideAmenities = htmlDiff($OldGuideAmenities, $GuideAmenities);
                $OldGuideContributors = stripslashes($row["GuideContributors"]);
                $ChangeGuideContributors = htmlDiff($OldGuideContributors, $GuideContributors);
                $OldGuideRemarks = stripslashes($row["GuideRemarks"]);
                $ChangeGuideRemarks = htmlDiff($OldGuideRemarks, $GuideRemarks);
                $OldGuideLat = stripslashes($row["GuideLat"]);
                $ChangeGuideLat = htmlDiff($OldGuideLat, $GuideLat);
                $OldGuideLong = stripslashes($row["GuideLong"]);
                $ChangeGuideLong = htmlDiff($OldGuideLong, $GuideLong);
            }
        }
        echo ("<tr><td>Change the details below and <input type=\"button\" class=\"btn btn-primary\" value=\"Save this entry\" onClick=\"document.form.guideaction.value='save';document.form.submit()\"> or 	<input type=\"button\" class=\"btn btn-primary\" value=\"Remove this entry\" onClick=\"document.form.guideaction.value='remove';document.form.submit()\">.</td></tr>\n");

        //echo("<tr><td>Change the details below and click 
        //<a href=\"#\" onClick=\"document.form.guideaction.value='save';document.form.submit()\"> here <img src=\"Image/common/save.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Save this entry\" alt=\"Save this entry\"></a>
        //to save or <a href=\"#\" onClick=\"document.form.guideaction.value='remove';document.form.submit()\"> here <img src=\"Image/common/clear.gif\" width=\"18\" height=\"18\" border=\"0\" title=\"Remove this entry\" alt=\"Remove this entry\"></a> to remove</td></tr>\n");
    }
    //get current countries
    $query = $db->getQuery(true)
        ->select($db->qn(['iso', 'printable_name']))
        ->from($db->qn('#__waterways_guide_country'))
        ->where($db->qn('postzone') . " IN ('EU', 'UK')")
        ->order($db->qn('printable_name'));
    $countries = $db->setQuery($query)->loadAssocList();
    $olist = "<select class=\"formcontrol\" name=\"olist_GuideCountry\" id=\"olist_GuideCountry\" onChange=\"insertcountry(this.form.olist_GuideCountry.options[this.form.olist_GuideCountry.selectedIndex].value)\">\n";
    $olist .= "<option value=\"0\">Choose a country</option>\n";
    foreach ($countries as $row) {
        if ($row["iso"] == $GuideCountry) {
            $olist .= "<option value=\"" . $row["iso"] . "\" selected>" . $row["printable_name"] . "</option>\n";
        } else {
            $olist .= "<option  value=\"" . $row["iso"] . "\">" . $row["printable_name"] . "</option>\n";
        }
    }
    $olist .= "</select> \n";

    //GuideCountry
    print "<tr><td><b>Country</b> (choose from the drop-down)<br> \n";


    if ($olist) {
        echo ($olist);
    }
    if ($CatHelp) {
        echo ("<br><img src=\"../Image/common/info.gif\" title=\"Help\" alt=\"Help\" /> $CatHelp\n");
    }

    print "<br><input type=\"text\" name=\"GuideCountry\" class=\"formcontrol\" readonly=\"true\" size=\"10\" value=\"" . $GuideCountry . "\"></td></tr>\n";

    //print"<tr><td><br></td></tr>\n";

    //get current waterways
    $query = $db->getQuery(true)
        ->select('DISTINCTROW ' . $db->qn('GuideWaterway'))
        ->from($db->qn($guidetable))
        ->order($db->qn('GuideWaterway'));
    $waterways = $db->setQuery($query)->loadAssocList();
    $rows = count($waterways);
    # If the search was unsuccessful then Display Message try again.
    if ($rows == 0) {
        $olist = "Enter the name of the waterway below.\n";
    } else {
        $olist = "<select name=\"olist_GuideWaterway\" class=\"formcontrol\" onChange=\"insertwaterway(this.form.olist_GuideWaterway.options[this.form.olist_GuideWaterway.selectedIndex].value)\">\n";
        $olist .= "<option value=\"0\">Waterways aready on-file</option>\n";
        foreach ($waterways as $row) {
            //$guideiso=" (".strtoupper($row["GuideCountry"].")");
            $ThisGuideWaterway = stripslashes($row["GuideWaterway"]);
            if ($ThisGuideWaterway == $GuideWaterway) {
                //$olist.="<option value=\"".$ThisGuideWaterway."\" selected>".$ThisGuideWaterway.$guideiso."</option>\n";
                $olist .= "<option value=\"" . $ThisGuideWaterway . "\" selected>" . $ThisGuideWaterway . "</option>\n";
            } else {
                $olist .= "<option  value=\"" . $ThisGuideWaterway . "\">" . $ThisGuideWaterway . "</option>\n";
            }
        }
        $olist .= "	</select>\n";
    }

    //GuideWaterway
    print "<tr><td><b>Waterway</b>(choose from the drop-down or enter a new waterway below)<br>\n";


    if ($olist) {
        echo ($olist);
    }
    if ($CatHelp) {
        echo ("<br><img src=\"../Image/common/info.gif\" title=\"Help\" alt=\"Help\" /> $CatHelp\n");
    }

    print "<br><input type=\"text\" id=\"GuideWaterway\" name=\"GuideWaterway\" class=\"formcontrol\" size=\"40\" value=\"" . $GuideWaterway . "\"></td></tr>\n";

    if ($GuideStatus == 0 && $infoid != "new") {
        //lookup submitter
        //GuideEditorMemNo
        if ($GuideEditorMemNo) {
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->qn('#__users'))
                ->where($db->qn('id') . ' = ' . $db->q($GuideEditorMemNo));
            $memberrow = $db->setQuery($query)->loadAssoc();

            /*$query = $db->getQuery(true)
                ->select('*')
                ->from($db->qn('tblMembers'))
                ->where($db->qn('MembershipNo') . ' = ' . $db->q($GuideEditorMemNo));*/
            $memberrow = $db->setQuery($query)->loadAssoc();
            $login_MembershipNo = $memberrow["id"];
            $contact = $memberrow["name"] . " " . $memberrow["email"] . ", User id No. " . $login_MembershipNo . "";
            $submitteremail = $memberrow["email"];
            $submitterid = $memberrow["id"];
        } else {
            $contact = "Unknown";
        }
        $listresults .= "<tr><td><table class='submit'></td></tr>\n";
        $listresults .= "<tr><td><b>SUBMISSION APPROVAL REQUIRED</b> Check and adjust if necessary the entry below, sequence order may need to be added, edit the email message in the box that will be sent to the submitter and click the appropriate button</td></tr>\n";
        $listresults .= "<tr><td><b>Submitter: </b>" . $contact . "</td></tr>\n";
        $listresults .= "<tr valign='top'><td><b>Version:</b> " . $GuideVer . " (version 1 will be a new submission)</td></tr>\n";
        $GuideMessage = "Many thanks for your update to the guide, '" . $GuideName . "' (" . $GuideWaterway . ") version " . $GuideVer . " made on " . date_to_format($GuideUpdate, 'd') . ". ";
        $GuideMessage .= "It has now been incorporated into the guides as the current version.\n\n";
        $GuideMessage .= "Guide Editor.\n";
        $listresults .= "<tr valign='top'><td><b>Message:</b> <i>Edit the <b>default</b> message which will be emailed to the submitter or delete it completely if you don't want to send an email</i><br><textarea cols=\"90\" rows=\"10\" name=\"GuideMessage\" class=\"formtextarea\">" . $GuideMessage . "</textarea></td></tr>\n";
        $listresults .= "<tr valign='top'><td><b>Status:</b> Pending - <input type=\"button\" class=\"formcontrol\" name=\"Approve\" value=\"Approve\" onClick=\"document.form.guideaction.value='approvesubmission';document.form.submit()\"> \n";

        //$listresults.="<input type=\"button\" class=\"formcontrol\" name=\"Reject\" value=\"Reject\" onClick=\"document.form.guideaction.value='rejectsubmission';document.form.submit()\"> \n"; 


        $listresults .= "</td></tr></table></td></tr>\n";
    }
    $listresults .= "<tr><td><table>";
    $listresults .= "<tr valign='top'><td><b>Name:</b><br><input type=\"text\" name=\"GuideName\" class=\"formcontrol\" size=\"50\" value=\"" . $GuideName . "\"></td><td>" . $ChangeGuideName . "</td></tr>\n";
    switch ($GuideCategory) {
        case "1":
            $GuideCategoryDesc = "Mooring";
            break;
        case "2":
            $GuideCategoryDesc = "<img src=\"Image/common/hazard_small.gif\" title=\"hazard\" alt=\"hazard\" width=\"16\" height=\"16\" border=\"0\"> Hazard";

            break;
    }
    $listresults .= "<tr valign='top'><td><b>Category:</b> " . $GuideCategoryDesc . "</td><td>" . $ChangeGuideCategoryDesc . "</td></tr>\n";


    $listresults .= "<tr valign='top'><td><b>Order:</b> <i>Ascending number along waterway</i><br><input type=\"text\" name=\"GuideOrder\" class=\"formcontrol\" size=\"4\" placeholder=\"1.00\" step=\"0.01\" min=\"0\" max=\"1000\" value=\"" . $GuideOrder . "\"></td><td>" . $ChangeGuideOrder . "</td></tr>\n";


    $listresults .= "<tr valign='top'>
                        <td><b>Is this edit? Rating:</b><br>
                            <select name=\"GuideRating\" class=\"formcontrol\">
                                <option value=\"0\"" . ($GuideRating == 0 ? ' selected' : '') . ">Doubtful</option>
                                <option value=\"1\"" . ($GuideRating == 1 ? ' selected' : '') . ">Adequate</option>
                                <option value=\"2\"" . ($GuideRating == 2 ? ' selected' : '') . ">Good</option>
                                <option value=\"3\"" . ($GuideRating == 3 ? ' selected' : '') . ">Very Good</option>
                              </select>
                        </td>
                        <td>" . $ChangeGuideRating . "</td></tr>\n";



    $listresults .= "<tr valign='top'><td><b>Map Marker: </b><i>To mark the location on the map, type the name of a nearby place in the 'Search Box' below and click on a place in the list that appears. Then drag the marker to the right spot. You can zoom in to make the location more accurate. France has many places with the same name so you may have to use another nearby place in the search.</i><br> <input type=\"hidden\" id=\"latlng\" name=\"latlng\" value=\"\" /><br></td></tr><tr valign='top'><td class=mooring_edit_underline><b>Decimal Latitude:</b> <input type=\"text\" id=\"GuideLat\" name=\"GuideLat\" class=\"formcontrol\" size=\"8\" value=\"$GuideLat\" readonly/> <b>Decimal Longitude:</b><input type=\"text\" id=\"GuideLong\" name=\"GuideLong\" class=\"formcontrol\" size=\"8\" value=\"$GuideLong\" readonly/> <br /><input id=\"pac-input\" class=\"controls\" type=\"text\" placeholder=\"Search Box\"/><div align=\"center\" id=\"map\" style=\"width: 100%; height: 400px\"><br/></div></td></tr>\n";
    $listresults .= "<tr valign='top'><td><b>Reference(PK?):</b><br><input type=\"text\" name=\"GuideRef\" class=\"formcontrol\"size=\"50\" value=\"" . $GuideRef . "\"></td><td>" . $ChangeGuideRef . "</td></tr>\n";
    $listresults .= "<tr valign='top'><td><b>Location:</b><br><textarea cols=\"90\" rows=\"10\" name=\"GuideLocation\" class=\"formtextarea\">" . $GuideLocation . "</textarea></td><td>" . $ChangeGuideLocation . "</td></tr>\n";

    //add tick boxes here

    switch ($GuideCategory) {
        case "1":
            $listresults .= "<tr valign='top'><td><b>Mooring:</b><br><textarea cols=\"90\" rows=\"10\" name=\"GuideMooring\" class=\"formtextarea\">" . $GuideMooring . "</textarea></td><td>" . $ChangeGuideMooring . "</td></tr>\n";
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->qn('#__waterways_guide_services'))
                ->where($db->qn('ServiceCategory') . " = 'mooringsguides'")
                ->order($db->qn('ServiceSortOrder'));
            $boxes = $db->setQuery($query)->loadAssocList();
            $boxestitle = "Tick the boxes for standard facilities available";
            break;
        case "2":
            $listresults .= "<tr valign='top'><td><b>Hazard:</b><br><textarea cols=\"90\" rows=\"10\" name=\"GuideMooring\" class=\"formtextarea\">" . $GuideMooring . "</textarea></td><td>" . $ChangeGuideMooring . "</td></tr>\n";
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->qn('#__waterways_guide_services'))
                ->where($db->qn('ServiceCategory') . " = 'hazardguides'")
                ->order($db->qn('ServiceSortOrder'));
            $boxes = $db->setQuery($query)->loadAssocList();
            $boxestitle = "Tick the boxes for the type of hazard found";
            break;
    }

    $boxhtml = "";
    foreach ($boxes as $boxrow) {
        $boxid = $boxrow["ServiceID"];
        $boxdesc = $boxrow["ServiceDescGB"];
        $found = strstr($GuideCodes, "|" . $boxid . "|");
        if (!$found) {
            $boxhtml .= "<input type=\"checkbox\" name=\"facility" . $boxid . "\" value=\"" . $boxid . "\" onClick=\"changecode(this,'" . $boxid . "')\"> " . $boxdesc . "<br />\n";
        } else {
            $boxhtml .= "<input type=\"checkbox\" name=\"facility" . $boxid . "\" value=\"" . $boxid . "\" checked onClick=\"changecode(this,'" . $boxid . "')\"> " . $boxdesc . "<br />\n";
        }
    }
    $listresults .= "<tr valign='top'><td><b>" . $boxestitle . "</b><br>" . $boxhtml . "</td><td></td></tr>\n";

    if ($GuideCategory == 1) {
        $listresults .= "<tr valign='top'><td><b>Facilities:</b><br><textarea cols=\"90\" rows=\"10\" name=\"GuideFacilities\" class=\"formtextarea\">" . $GuideFacilities . "</textarea></td><td>" . $ChangeGuideFacilities . "</td></tr>\n";
        //$listresults.="<tr valign='top'><td><b>Facilities Codes:</b> <i>S = shipyard, F = fuel, C = Chandlery, R = repairs W = wintering possible. WF = WiFi Enter with comma seperator no spaces e.g F,C,R</i><br><input type=\"text\" name=\"GuideCodes\" class=\"formcontrol\" size=\"50\" value=\"".$GuideCodes."\"></td></tr>\n";
        $listresults .= "<tr valign='top'><td><b>Costs:</b><br><textarea cols=\"90\" rows=\"10\" name=\"GuideCosts\" class=\"formtextarea\">" . $GuideCosts . "</textarea></td><td>" . $ChangeGuideCosts . "</td></tr>\n";
        $listresults .= "<tr valign='top'><td><b>Amenities:</b><br><textarea cols=\"90\" rows=\"10\" name=\"GuideAmenities\" class=\"formtextarea\">" . $GuideAmenities . "</textarea></td><td>" . $ChangeGuideAmenities . "</td></tr>\n";
    }
    $listresults .= "<tr valign='top'><td><b>Contributors:</b><br><textarea cols=\"90\" rows=\"10\" name=\"GuideContributors\" class=\"formtextarea\">" . $GuideContributors . "</textarea><td>" . $ChangeGuideContributors . "</td></td></tr>\n";
    $listresults .= "<tr valign='top'><td><b>Summary:</b> <i>Enter for first mooring only</i><br><textarea cols=\"90\" rows=\"10\" name=\"GuideSummary\" class=\"formtextarea\">" . $GuideSummary . "</textarea><td>" . $ChangeGuideSummary . "</td></td></tr>\n";
    $listresults .= "<tr valign='top'><td><b>Remarks:</b><br><textarea cols=\"90\" rows=\"10\" name=\"GuideRemarks\" class=\"formtextarea\">" . $GuideRemarks . "</textarea></td><td>" . $ChangeGuideRemarks . "</td></tr>\n";
    $listresults .= "<tr valign='top'><td><b>Posting Date:</b><br><input type=\"textbox\" name=\"GuidePostingDate\" id=\"GuidePostingDate\" class=\"formcontrol\" size=\"25\" readonly=\"true\" value=\"$GuidePostingDate\"></td><td></td></tr>\n";
    $listresults .= "<tr valign='top'><td><b>Last Update: </b>" . $GuideUpdatedisplay . "</td><td></td></tr>\n";
    $listresults .= "</table>\n";
    $listresults .= "</td></tr></table>\n";



    echo ($listresults);
    echo ("<input name=\"GuideCategory\" type=\"hidden\" value=\"$GuideCategory\">\n");
    echo ("<input name=\"country\" type=\"hidden\" value=\"$GuideCountry\">\n");
    echo ("<input name=\"waterway\" type=\"hidden\" value=\"$GuideWaterway\">\n");
    echo ("<input name=\"country_tmp\" type=\"hidden\" value=\"$GuideCountry\">\n");
    echo ("<input name=\"waterway_tmp\" type=\"hidden\" value=\"$GuideWaterway\">\n");
    echo ("<input name=\"GuideCodes\" type=\"hidden\" value=\"$GuideCodes\">\n");
    echo ("<input name=\"GuideNo\" type=\"hidden\" value=\"$GuideNo\">\n");
    echo ("<input name=\"GuideVer\" type=\"hidden\" value=\"$GuideVer\">\n");
    echo ("<input name=\"GuideEditorMemNo\" type=\"hidden\" value=\"$GuideEditorMemNo\">\n");
    echo ("<input name=\"submitteremail\" type=\"hidden\" value=\"$submitteremail\">\n");
    echo ("<input name=\"submitterid\" type=\"hidden\" value=\"$submitterid\">\n");
    echo ("<input name=\"Status0\" type=\"hidden\" value=\"$Status0\">\n");
    echo ("<input name=\"Status1\" type=\"hidden\" value=\"$Status1\">\n");
    echo ("<input name=\"Status2\" type=\"hidden\" value=\"$Status2\">\n");


?>

    <SCRIPT LANGUAGE="JavaScript">
        function fix(num) {
            string = "" + num;
            numberofdigits = string.length;
            if (numberofdigits < 2) {
                return '0' + string;
            } else {
                return string;
            }
        }

        function catcalc2(cal) {
            if (cal.dateClicked) {
                // OK, a date was clicked

                var y = cal.date.getFullYear();
                var m = cal.date.getMonth(); // integer, 0..11
                var d = cal.date.getDate(); // integer, 1..31

                var date = new Date(y, m, d);
                var now = new Date();
                var diff = date.getTime() - now.getTime();
                var days = Math.floor(diff / (1000 * 60 * 60 * 24));
                var dbdate = y + "-" + fix((m + 1)) + "-" + fix(d);
                var field = document.getElementById("GuidePostingDate");
                field.value = dbdate;
                //"%A, %B %e, %Y",			
            }
        }

        Calendar.setup({
            inputField: "GuidePostingDate",
            ifFormat: "%Y-%m-%d",
            showsTime: true,
            timeFormat: "24",
            onUpdate: catcalc2
        });



        function Help(Subject) {
            var mypage = Subject;
            var myname = "help";
            //var w = (screen.width - 100);
            //var h = (screen.height - 100);
            var w = 530;
            var h = 300;
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

        function changecode(cbname, code) {


            var cur_str = document.form.GuideCodes.value;
            var state = cbname.checked;
            var str_search = "|" + code + "|";
            if (state == 0) {
                //remove it
                if (str_search == cur_str) {
                    //only one so make blank
                    var new_str = cur_str.replace(str_search, '');
                } else {
                    var new_str = cur_str.replace(str_search, '|');
                }
            } else {
                //add it
                if (cur_str) {
                    //already some data so add on end
                    var new_str = cur_str + code + "|";
                } else {
                    var new_str = "|" + code + "|";
                }
            }

            //alert(cur_str+" - "+new_str);
            document.form.GuideCodes.value = new_str;
        }

        function insertcountry(text) {
            var txtarea = document.form.GuideCountry;
            //text = ' ' + text + ' ';
            txtarea.value = text;
            document.form.keywords.options["0"].selected = true;
            txtarea.focus();
        }

        function insertwaterway(text) {
            var txtarea = document.form.GuideWaterway;
            //text = ' ' + text + ' ';
            txtarea.value = text;
            document.form.keywords.options["0"].selected = true;
            txtarea.focus();
        }

        function storeCaret(textEl) {
            if (textEl.createTextRange) textEl.caretPos = document.selection.createRange().duplicate();
        }

        function SubmitContent() {
            document.form.upload.src = "Images/common/livinga22.gif";
            document.form.save.value = 'Please Wait . . . . Updating . .';
            document.form.submit();
        }

        function DeleteContent() {
            if (confirm("Confirm deletion by clicking OK")) {
                document.form.submit();
            } else {
                document.form.assetaction.value = 'detail';
            }
        }
    </script>