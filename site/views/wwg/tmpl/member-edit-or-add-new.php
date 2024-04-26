<?php
//Find GuideNo from GuideID
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
				if ($GuideStatus == 1) {
					$statustext = "posted on site";
				} else {
					$statustext = "pending";
				}

				$GuideUpdatedisplay = (empty($row['GuideUpdate']) ? 'Date unknown' : date('Y-m-d', strtotime($row['GuideUpdate']))) . " - Mooring Index: " . $GuideNo . " - Version: " . $GuideVer;
			}


			//echo("<tr><td colspan=4> update $guidetable $GuideNo $GuideEditorMemNo $submitteremail");
			echo ("<tr><td colspan=4>");
			//Check if this GuideNo has any outstanding pending already and if so reject another edit and inform 'still pending'
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn($guidetable))
				->where($db->qn('GuideNo') . ' = ' . $db->q($GuideNo))
				->where($db->qn('GuideStatus') . ' = 0');
			$dup_check = $db->setQuery($query)->loadAssocList();
			$rows = count($dup_check);
			if ($rows) {
				//already pending
				$row = reset($dup_check);
				$GuideUpdate = $row["GuideUpdate"];

				echo ("<tr><td class=content_introduction><b>Member update</b><br />Thank you for helping to keep the guide up to date. However, we have found an update to this entry submitted for approval on $GuideUpdate and still pending. If this was from you, please wait until you have received confirmation about it from the editor before submitting further changes. If not from you, please try the update in a day or two. If it still doesn't work please email guideeditor@barges.org who will look at the problem.</td></tr>");

			?>
				<tr>
					<td class='bodytext'><input type="button" class="btn btn-primary" value="Back to the list" onClick="document.form.guideaction.value='list';document.form.submit()"><input type="button" class="btn btn-primary" value="Back to the map" onClick="document.form.guideaction.value='map';document.form.submit()"></td>
				</tr>

			<?php

			} else {

				include("guides_edit.php");
			}

			//store non member fields to pass through to new version
			echo ("<input name=\"country\" type=\"hidden\" value=\"$GuideCountry\">\n");
			echo ("<input name=\"waterway\" type=\"hidden\" value=\"" . stripslashes($GuideWaterway) . "\">\n");
			echo ("<input name=\"country_tmp\" type=\"hidden\" value=\"$GuideCountry\">\n");
			echo ("<input name=\"waterway_tmp\" type=\"hidden\" value=\"$GuideWaterway\">\n");

			echo ("<input name=\"GuideOrder\" type=\"hidden\" value=\"$GuideOrder\">\n");
			echo ("<input name=\"GuideDocs\" type=\"hidden\" value=\"$GuideDocs\">\n");
			echo ("<input name=\"GuideSummary\" type=\"hidden\" value=\"$GuideSummary\">\n");
			echo ("<input name=\"GuidePostingDate\" type=\"hidden\" value=\"$GuidePostingDate\">\n");
			echo ("<input name=\"GuideCategory\" type=\"hidden\" value=\"$GuideCategory\">\n");
			echo ("<input name=\"GuideCodes\" type=\"hidden\" value=\"$GuideCodes\">\n");
			echo ("<input name=\"GuideNo\" type=\"hidden\" value=\"$GuideNo\">\n");
			echo ("<input name=\"GuideVer\" type=\"hidden\" value=\"$GuideVer\">\n");
			echo ("<input name=\"guidesemail\" type=\"hidden\" value=\"$guidesemail\">\n");
			echo ("</td></tr>\n");