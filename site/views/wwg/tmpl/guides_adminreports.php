
<!--
TO do



-->

<?php
	echo("<input name=\"colsort\" type=\"hidden\" value=\"$colsort\">\n");

	$sort_col="<img src='Image/common/arrow_down.gif' alt='Sorted on this column. Click another heading to re-sort' title='Sorted on this column. Click another heading to re-sort' width='9' height='6' border='0'>";
	$reporttype="listfull";
	if(!$colsort){
		$colsort="GuideRequestDate";
	}
	${"sort_col_".$colsort}=$sort_col;
	$GuideRequestDate=date("Y-m-d H:i:s");
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblGuidesRequests'))
		->order($colsort);
	if($country && $country != 'All') $query->where($db->qn('GuideCountry').' = '.$db->q($country));
	if($waterway && $waterway != 'All') $query->where($db->qn('GuideWaterway').' = '.$db->q(stripslashes($waterway)));
	$result = $db->setQuery($query)->loadAssocList() or die ("Couldn't find report ".$query->__toString());
	$listresults="<table width=\100%\" border=\"0\" cellpadding=\"1\" cellspacing=\"0\">";
	
	$listresults.="<tr><td class=list_small><a href=\"#\" onClick=\"document.form.guideaction.value='adminreports';document.form.colsort.value='MemberID';document.form.submit()\"><b>Requester</b> ".$sort_col_MemberID."</a></td>\n";		
	$listresults.="<td class=list_small><a href=\"#\" onClick=\"document.form.guideaction.value='adminreports';document.form.colsort.value='GuideCountry';document.form.submit()\"><b>Country</b> ".$sort_col_GuideCountry."</a></td>\n";		
	$listresults.="<td class=list_small><a href=\"#\" onClick=\"document.form.guideaction.value='adminreports';document.form.colsort.value='GuideWaterway';document.form.submit()\"><b>Waterway</b> ".$sort_col_GuideWaterway."</a></td>\n";		
	$listresults.="<td class=list_small><a href=\"#\" onClick=\"document.form.guideaction.value='adminreports';document.form.colsort.value='GuideRequestMethod';document.form.submit()\"><b>Method</b> ".$sort_col_GuideRequestMethod."</a></td>\n";		
	$listresults.="<td class=list_small><a href=\"#\" onClick=\"document.form.guideaction.value='adminreports';document.form.colsort.value='GuideRequestDate';document.form.submit()\"><b>Date</b> ".$sort_col_GuideRequestDate."</a></td>\n";		
	$listresults.="<td class=list_small><b>No.</b></td>\n";	
	$listresults.="<td class=list_small><b>*</b></td>\n";
	$listresults.="</td></tr>\n";
	$thisfield="";
	foreach($result as $row) {
		$MemberID = stripslashes($row["MemberID"]);
		if($reporttype=="listfull"){
			//lookup name
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('tblMembers'))
				->where($db->qn('ID').' = '.$db->q($MemberID));
			$memberinfo = $db->setQuery($query)->loadAssoc();		
			$fullname=$memberinfo["LastName"];
		}
		$GuideCountry = stripslashes($row["GuideCountry"]);
		$GuideWaterway = stripslashes($row["GuideWaterway"]);
		$GuideRequestDate = date_to_format($row["GuideRequestDate"],"ymd");
		$GuideRequestMethod = stripslashes($row["GuideRequestMethod"]);
		$GuideRequestStatus = stripslashes($row["GuideRequestStatus"]);
		if($row[$colsort]!=$thisfield){
			$seq=1;
			$seqdisplay="<b>$seq</b>";
			$thisfield=$row[$colsort];
		}else{
			$seq+=1;
			$seqdisplay="$seq";
		}
		if($rowclass==""){
			$rowclass=" class=table_stripe_lblue";
		}else{
			$rowclass="";		
		}
		$listresults.="<tr".$rowclass."><td>$fullname</td><td>$GuideCountry</td><td>$GuideWaterway</td><td>$GuideRequestMethod</td><td>$GuideRequestDate</td><td>$seqdisplay</td><td>$GuideRequestStatus</td></tr>\n";
	}
	$listresults.="</table>\n";
	
	//sumary

	$Summary="<b>Summary</b><br>\n";
	$Summary.="<table width=\100%\" border=\"0\" cellpadding=\"1\" cellspacing=\"0\">";
	$Summary.="<tr><td><b>Country / Waterway</b></td><td><b>Requests</b></td></tr>\n";
	$query = $db->getQuery(true)
		->select($db->qn('GuideCountry'))
		->select('COUNT(*) AS '.$db->qn('Count'))
		->from($db->qn('tblGuidesRequests'))
		->group($db->qn('GuideCountry'));
	if($country && $country != 'All') $query->where($db->qn('GuideCountry').' = '.$db->q($country));
	if($waterway && $waterway != 'All') $query->where($db->qn('GuideWaterway').' = '.$db->q(s$waterway));
	$mycountries = $db->setQuery($query)->loadAssocList();
	$countries = count($mycountries);	
	foreach($mycountries as $row) {
		$GuideCountry = stripslashes($row["GuideCountry"]);
		$GuideCountryCount= $row["COUNT(*)"];
		$Summary.="<tr class=table_stripe_lblue><td><b>$GuideCountry</b></td><td>$GuideCountryCount</td></tr>\n";
		$query = $db->getQuery(true)
			->select($db->qn('GuideWaterway'))
			->select('COUNT(*) AS '.$db->qn('Number'))
			->from($db->qn('tblGuidesRequests'))
			->where($db->qn('GuideCountry').' = '.$db->q($GuideCountry))
			->group($db->qn('GuideWaterway'))
			->order($db->qn('Number').' DESC');
		$mywaterways = $db->setQuery($query)->loadAssocList();
		$waterways = count($mywaterways);
		foreach($mywaterways as $wwrow) {
			$GuideWaterway = stripslashes($wwrow["GuideWaterway"]);
			$GuideWaterwayCount= $wwrow["Number"];
			$Summary.="<tr><td> $GuideWaterway</td><td>$GuideWaterwayCount</td></tr>\n";
		}

	}
	$Summary.="</table>\n";
	echo("<h2>Admin reports</h2>");
	echo($Summary);
	echo("<br><b>Full listing</b> <i>Column * 1=OK 0=rejected, non-member</i><br><i>Click a column heading to sort the list</i>");
	echo($listresults);
?>


