<table width="100%" border="0" cellpadding="3" cellspacing="1">


	<tr>
		<td class=content_introduction><b>Member update</b><br />
			Thank you for helping to keep the guide uptodate by sending us any additions or amendments you come across on your cruise. GPS positions would be particularly welcome to enable more locations to be displayed on the guide maps<br />Enter your changes in the appropriate boxes adding or deleting data as necessary.<br />On completion click 'Send' - your contribution will be on its way to our editor.</td>
	</tr>
	<tr>
		<td class='bodytext'><input type="button" class="btn btn-primary" value="Back to the list" onClick="document.form.guideaction.value='list';document.form.submit()"><input type="button" class="btn btn-primary" value="Back to the map" onClick="document.form.guideaction.value='map';document.form.submit()"></td>
	</tr>


	<?php

	function diff($old, $new)
	{
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
		$diff = diff(explode(' ', $old), explode(' ', $new));
		foreach ($diff as $k) {
			if (is_array($k))
				$ret .= (!empty($k['d']) ? "<del>" . implode(' ', $k['d']) . "</del> " : '') .
					(!empty($k['i']) ? "<ins>" . implode(' ', $k['i']) . "</ins> " : '');
			else $ret .= $k . ' ';
		}
		return $ret;
	}


	if ($infoid == "new") {
		echo ("<tr><td>Enter the details below and <input type=\"button\" class=\"btn btn-primary\" value=\"Send\" onClick=\"document.form.guideaction.value='membersave';ValidateForm()\"> to the editor.</td></tr>\n");


		//<input type=\"button\" class=\"formcontrol\" name=\"SUBMIT\" value=\"Send\" onClick=\"document.form.guideaction.value='membersave';ValidateForm()\"> to the editor.</td></tr>\n");
		$GuideUpdate = date("Y-m-d H:i:s");
		$GuidePostingDate = $GuideUpdate;
		$GuideLat = 0; //default
		$GuideLong = 0;
		$GuideCountry = $country;
		$GuideWaterway = $waterway;
		$GuideUpdatedisplay = $GuideUpdate . " - Mooring Index: New - Version: 1";
		$GuideCategory = 1; //default for mooring, hazards 2 to be added
	} else {
		echo ("<tr><td>Change the details below and <input type=\"button\" class=\"btn btn-primary\" value=\"Send\" onClick=\"document.form.guideaction.value='membersave';ValidateForm()\"> to the editor.</td></tr>\n");
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
			$GuideRating = stripslashes($row["GuideRating"]);
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
			//$old="Hello how are you";
			//$new="Hello, how is john doing";
			//$diff=htmlDiff($old, $new);
			if ($GuideStatus == 1) {
				$statustext = "posted on site";
			} else {
				$statustext = "pending";
			}
			$GuideUpdatedisplay = (empty($GuideUpdate) ? 'Date unknown' : date('Y-m-d', strtotime($GuideUpdate))) . " - Mooring Index: " . $GuideNo . " - Version: " . $GuideVer;
		}
	}


	//get current countries
	$query = $db->getQuery(true)
		->select($db->qn(['iso', 'printable_name']))
		->from($db->qn('tblCountry'))
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
	print "<tr><td><b>Country:</b> <i>choose from the drop-down</i><br> \n";


	if ($olist) {
		echo ($olist);
	}
	if ($CatHelp) {
		echo ("<br><img src=\"../Image/common/info.gif\" alt=\"Help\" /> $CatHelp\n");
	}

	print "<br><input type=\"text\" name=\"GuideCountry\" class=\"formcontrol\" readonly=\"true\" size=\"4\" value=\"" . $GuideCountry . "\"></td></tr>\n";

	//print"<tr><td><br></td></tr>\n";

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
		$olist = "Enter the name of the waterway below.\n";
	} else {
		$olist = "<select name='olist_GuideWaterway' class='formcontrol' onChange=\"insertwaterway(this.form.olist_GuideWaterway.options[this.form.olist_GuideWaterway.selectedIndex].value)\">\n";
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
	print "<tr><td><b>Waterway:</b> <i>choose from the drop-down or enter a new waterway below [60]</i></td></tr><tr valign='top'><td class=mooring_edit_underline>\n";


	if ($olist) {
		echo ($olist);
	}
	if ($CatHelp) {
		echo ("<br><img src=\"/Image/common/info.gif\" alt=\"Help\" /> $CatHelp\n");
	}

	print "<br><input type=\"text\" id=\"GuideWaterway\" name=\"GuideWaterway\" class=\"formcontrol\" size=\"60\" maxlength=\"60\" value=\"" . $GuideWaterway . "\"></td></tr>\n";


	?>
	<tr valign='top'>
		<td><b>Name:</b> <i>Town or locality name [50] </i></td>
	</tr>
	<tr valign='top'>
		<td class=mooring_edit_underline><input name="GuideName" type="text" class="formcontrol" value="<?php echo ($GuideName); ?>" size="50" maxlength="50" /></td>
	</tr>
	<tr valign='top'>
		<td><b>Rating:</b> <i>Overall impression;</i></td>
	</tr>
	<tr valign='top'>
		<td class=mooring_edit_underline>
			<?php
			${'GuideRating' . $GuideRating} = " selected";

			?>
			<select class=formcontrol name="GuideRating" id="GuideRating">

				<option value="0" <?php echo ($GuideRating0); ?>>Doubtful</option>

				<option value="1" <?php echo ($GuideRating1); ?>>Adequate</option>

				<option value="2" <?php echo ($GuideRating2); ?>>Good</option>
				<option value="3" <?php echo ($GuideRating3); ?>>Very Good</option>
			</select>
		</td>
	</tr>


	<tr valign='top'>
		<td><b>Reference:</b> <i>PK/km/MP Number if known</i></td>
	</tr>
	<tr valign='top'>
		<td class=mooring_edit_underline><input type="text" name="GuideRef" class="formcontrol" size="50" maxlength="50" value="<?php echo ($GuideRef); ?>"></td>
	</tr>
	<tr valign='top'>
		<td><b>Location:</b> <i>A description of the location of the mooring place sufficient for the user to find it with confidence. Include which bank and if possible a reference to conspicuous features such as a bridge or lock</i></td>
	</tr>
	<tr valign='top'>
		<td class=mooring_edit_underline><textarea cols="120" rows="5" name="GuideLocation" class="formtextarea"><?php echo ($GuideLocation); ?></textarea></td>
	</tr>


	<tr valign='top'>
		<td><b>Map Marker: </b><i>To mark the location on the map, type the name of a nearby place in the 'Search Box' below and click on a place in the list that appears. Then drag the marker to the right spot. You can zoom in to make the location more accurate. France has many places with the same name so you may have to use another nearby place in the search.</i><br>
			<input type="hidden" id="latlng" name="latlng" value="" /><br>
		</td>
	</tr>
	<tr valign='top'>
		<td class=mooring_edit_underline>
			<b>Decimal Latitude:</b> <input type="text" id="GuideLat" name="GuideLat" class="formcontrol" size="8" value="<?php echo ($GuideLat); ?> " readonly />
			<b>Decimal Longitude:</b>
			<input type="text" id="GuideLong" name="GuideLong" class="formcontrol" size="8" value="<?php echo ($GuideLong); ?>" readonly /> <br />


			<input id="pac-input" class="controls" type="text" placeholder="Search Box" />

			<div align="center" id="map" style="width: 100%; height: 400px"><br /></div>



		</td>
	</tr>
	<tr valign='top'>
		<td><b>Mooring:</b> <i>A description of the mooring itself - e.g. length and nature of quay, securing arrangements, water depth, any limitations, contact details of administration etc.</i></td>
	</tr>
	<tr valign='top'>
		<td class=mooring_edit_underline><textarea cols="120" rows="5" name="GuideMooring" class="formtextarea"><?php echo ($GuideMooring); ?></textarea></td>
	</tr>
	<tr valign='top'>
		<td>
			<?php
			//add tick boxes here
			$boxestitle = "Tick the boxes for standard facilities available";
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('tblServices'))
				->where($db->qn('ServiceCategory') . " = 'mooringsguides'")
				->order($db->qn('ServiceSortOrder'));
			$boxes = $db->setQuery($query)->loadAssocList();
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
			echo ("<b>" . $boxestitle . "</b></td></tr><tr valign='top'><td class=mooring_edit_underline>" . $boxhtml . "\n");

			?>

		</td>
	</tr>
	<tr valign='top'>
		<td><b>Facilities:</b> <i>Add some more about the ticked facilities or additional info not covered in the ticks</i></td>
	</tr>
	<tr valign='top'>
		<td class=mooring_edit_underline><textarea cols="120" rows="5" name="GuideFacilities" class="formtextarea"><?php echo ($GuideFacilities); ?></textarea></td>
	</tr>


	<tr valign='top'>
		<td><b>Costs:</b> <i>Indicative costs - typically daily related to length or bracket of lengths plus costs of use of facilities</i></td>
	</tr>
	<tr valign='top'>
		<td class=mooring_edit_underline><textarea cols="120" rows="5" name="GuideCosts" class="formtextarea"><?php echo ($GuideCosts); ?></textarea></td>
	</tr>
	<tr valign='top'>
		<td><b>Amenities:</b> <i>Amenities available in the nearest town or village with distances - shops, cafes, railway etc etc </i></td>
	</tr>
	<tr valign='top'>
		<td class=mooring_edit_underline><textarea cols="120" rows="5" name="GuideAmenities" class="formtextarea"><?php echo ($GuideAmenities); ?></textarea></td>
	</tr>

	<tr valign='top'>
		<td><b>Contributors:</b> <i>Boat name, length x draft, date of contribution(M/YY)</i></td>
	</tr>
	<tr valign='top'>
		<td class=mooring_edit_underline><textarea cols="120" rows="5" name="GuideContributors" class="formtextarea"><?php echo ($GuideContributors); ?></textarea></td>
	</tr>
	<tr valign='top'>
		<td><b>Remarks:</b> <i>Any general comments, recommendations or opinions!</i></td>
	</tr>
	<tr valign='top'>
		<td class=mooring_edit_underline><textarea cols="120" rows="5" name="GuideRemarks" class="formtextarea"><?php echo ($GuideRemarks); ?></textarea></td>
	</tr>
	<tr valign='top'>
		<td><b>Summary:</b> <i>This is the basis for the summary of the <U>whole waterway</U> that appears as the header to each guide. Please check this and if you have any changes or additions edit the Summary box in the <U>first mooring of this waterway</U></i> </td>
	</tr>
	<tr valign='top'>
		<td class=mooring_edit_underline>
			<textarea cols="120" rows="5" name="GuideSummary" class="formtextarea"><?php echo ($GuideSummary); ?></textarea>
		</td>
	</tr>
	<tr valign='top'>
		<td><b>Last update: </b><?php echo ($GuideUpdatedisplay); ?></td>
	</tr>
	<?php
	if ($infoid == "new") {
		echo ("<tr><td>Enter the details above and <input type=\"button\" class=\"btn btn-primary\" value=\"Send\" onClick=\"document.form.guideaction.value='membersave';ValidateForm()\"> to the editor.</td></tr>\n");

		//echo("<tr><td>Enter the details above and <input type=\"button\" class=\"formcontrol\" name=\"SUBMIT\" value=\"Send\" onClick=\"document.form.guideaction.value='membersave';ValidateForm()\"> to the editor.</td></tr>\n");
	} else {
		echo ("<tr><td>Change the details above and <input type=\"button\" class=\"btn btn-primary\" value=\"Send\" onClick=\"document.form.guideaction.value='membersave';ValidateForm()\"> to the editor.</td></tr>\n");

		//echo("<tr><td>Change the details above and <input type=\"button\" class=\"formcontrol\" name=\"SUBMIT\" value=\"Send\" onClick=\"document.form.guideaction.value='membersave';ValidateForm()\"> to the editor.</td></tr>\n");
	}
	?>
	<SCRIPT LANGUAGE="JavaScript">
		function ValidateForm() {
			var errors = "";
			if (document.form.GuideCountry.value == "All") {
				errors += '- Country\n';
				document.form.GuideCountry.style.backgroundColor = "#ffff00";
			} else {
				document.form.GuideCountry.style.backgroundColor = "#ffffff";
			}
			if (document.form.GuideWaterway.value == "All") {
				errors += '- Waterway\n';
				document.form.GuideWaterway.style.backgroundColor = "#ffff00";
			} else {
				document.form.GuideWaterway.style.backgroundColor = "#ffffff";
			}
			//check waterway for illegal chars
			myStr = document.form.GuideWaterway.value;
			if (myStr.match(/[\<\>!@#\$%^&\*]+/i)) {
				errors += "- Waterway '" + myStr + "' must only contain alpha numeric characters or , . and space\n";
				document.form.GuideWaterway.style.backgroundColor = "#ffff00";
			} else {
				document.form.GuideWaterway.style.backgroundColor = "#ffffff";
			}
			if (document.form.GuideName.value == "") {
				errors += '- Town or locality name\n';
				document.form.GuideName.style.backgroundColor = "#ffff00";
			} else {
				document.form.GuideName.style.backgroundColor = "#ffffff";
			}
			if (document.form.GuideLocation.value == "") {
				errors += '- Location\n';
				document.form.GuideLocation.style.backgroundColor = "#ffff00";
			} else {
				document.form.GuideLocation.style.backgroundColor = "#ffffff";
			}
			if (document.form.GuideRemarks.value == "") {
				errors += '- Remarks\n';
				document.form.GuideRemarks.style.backgroundColor = "#ffff00";
			} else {
				document.form.GuideRemarks.style.backgroundColor = "#ffffff";
			}
			if (errors) {
				alert('Please add some detail to the highlighted entries and try again:\n' + errors);
			} else {
				document.form.submit()
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
			document.form.upload.src = "/Images/common/livinga22.gif";
			document.form.save.value = 'Please Wait . . . . Updating . .';
			document.form.submit();
		}
	</script>


</table>