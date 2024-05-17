<?php

$config = Factory::getConfig();
$mailOn = Factory::getConfig()->get('mailonline') == '1';

	function UpdateGuideRequests($memberid,$GuideCountry,$GuideWaterway,$GuideRequestMethod,$GuideRequestStatus)	{
		//Update Guide Request log
		if(!$login_memberid){
			$login_memberid=$memberid;
		}
		$GuideRequestDate=date("Y-m-d H:i:s");
		$insert = new \stdClass();
		$insert->MemberID = $login_memberid;
		$insert->GuideCountry = $GuideCountry;
		$insert->GuideWaterway = $GuideWaterway;
		$insert->GuideRequestDate = $GuideRequestDate;
		$insert->GuideRequestMethod = $GuideRequestMethod;
		$insert->GuideRequestStatus = $GuideRequestStatus;
		//GuideRequestStatus 1=success 0=refused
		$db->insertObject('#__waterways_guide_requests', $insert) or die ("Couldn't update request log $query");

	}
	$waterway=str_replace("_", " ", $waterway);
	$waterway=stripslashes($waterway);
	if(!$guidetable){
		$guidetable="#__waterways_guide";
	}	

	if(!$login_memberid){
		//not logged in so try to find Member ID from email
		if($email){
			echo("<br>start of member check");
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('tblMembers'))
				->where($db->qn('Email').' = '.$db->q($email));
			$members = $db->setQuery($query)->loadAssocList();
			if (!$members) {
				echo("<P>Error performing query: " . $query->__toString() . "</P>");
				//exit();
			}
			$num_memberrow = count($members);
			if($num_memberrow == 0){
				//not a valid member
				$thisstatus="email address not found in current membership";
			}else{
				//valid member but check sub paid up
				$memberrow = reset($members);
				$login_memberid=$memberrow["ID"];
				$contact=$memberrow["FirstName"]." ".$memberrow["LastName"];
				$name=$memberrow["LastName"];
				$memberlevel=$memberrow["Level"];
				$memberid=$memberrow["ID"];
				$adminaccess=$memberrow["AdminAccess"];
				$PaymentMethod=$memberrow["PaymentMethod"];
				$DatePaid=$memberrow["DatePaid"];
				$MembershipNo=$memberrow["MembershipNo"];
				$BasicSub=$memberrow["BasicSub"];
				$Situation=$memberrow["Situation"];
				if($memberlevel==60){
					$supervisor=1;	
				}
				$datepaiddisplay=date_to_format($DatePaid,"d") ;
				
				switch ($memberrow["MemStatus"]) {
					case 1:
						$thisstatus="Applied awaiting payment";
						$substatus=0;
						break;
					case 2:
						$thisstatus="Paid up - last payment received on $datepaiddisplay";
						$substatus=1;
						break;
					case 3:
						$thisstatus="Renewal due - last payment received on $datepaiddisplay";
						$substatus=0;
						break;	
					case 4:
						$thisstatus="Gone away - last payment received on $datepaiddisplay";
						$substatus=0;
						break;
					case 5:
						$thisstatus="Terminated - last payment received on $datepaiddisplay";
						$substatus=0;
						break;
					case 6:
						$thisstatus="Complimentary";
						$substatus=1;
						break;
				}
				if($thisstatus){
					$otherinfo.="Subscription status: ".$thisstatus."\n\n";	
				}
				$livestatus=$thisstatus;
				
				
				
				
				$login_memberid=$memberrow["ID"];
			}
			if($substatus!=1 || $num_memberrow == 0){
				//not paid up or FOC so not OK to access member features
				$from="<mooringsguides@barges.org>";
				$headers= "From: ".$from." <".$from."\n>"."X-Mailer: PHP 4.x";
				$otherinfo="Subscription status: ".$thisstatus."\n\n";	
				$otherinfo.="Unfortunately, due to the status shown above, we are not able to supply you with the information requested. Please go to www.barges.org/members to login and check your subscription.";								
				$body=$otherinfo." \n\n".$footermooringsguide;
				$subject="DBA moorings guides request rejected";
				$recipient=$email;
				if($mailOn) {
					$mailer = Factory::getMailer();	
					$mailer->setSender([$config->get('mailfrom'), $config->get('fromname')]);
					$mailer->addRecipient($recipient);
					$mailer->addReplyTo($from, $from);
					$mailer->setSubject($subject);
					$mailer->setBody(nl2br($body));
					$mailer->isHtml(true);
					$mailer->Send();
				} else echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";

	
				if(!$guiderequestmethod){
					$guiderequestmethod="email_site";
				}
				$ret=UpdateGuideRequests($login_memberid,$country,$waterway,$guiderequestmethod,"0");

			}
		}
	}else{
		$substatus=1;
	}
	if($substatus==1){
		if($guideaction=="emailindex"){
			$actionmessage="emailindex";
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn($guidetable))
				->where($db->qn('GuideStatus').' = 1')
				->order($db->qn(['GuideCountry', 'GuideWaterway']));
			$guides = $db->setQuery($query)->loadAssocList();
			$rows = count($guides);
			
			# If the search was unsuccessful then Display Message try again.
			If ($rows == 0){
				$listresults="Sorry - there are no guides available at the moment";
			}else{
				
				$guidematch=0;	
		
				$listresults="";			
				$thiswaterwaycount=0;
				foreach($guides as $row) {
					$GuideID = stripslashes($row["GuideID"]);
					$GuideCountry = stripslashes($row["GuideCountry"]);
					$GuideWaterway = stripslashes($row["GuideWaterway"]);
					$GuideNo = stripslashes($row["GuideNo"]);
					$GuideVer = stripslashes($row["GuideVer"]);
					$GuideUpdate = stripslashes($row["GuideUpdate"]);
					$GuideUpdatedisplay = 'Update:'.(empty($GuideUpdate) ? 'Unknown' : date('dmy', strtotime($GuideUpdate)))." - Guide No: ".$GuideNo." - Version: ".$GuideVer;
					if(strtoupper($GuideCountry)!=strtoupper($thisGuideCountry)){
						//lookup country name
						$query = $db->getQuery(true)
							->select($db->qn('printable_name'))
							->from($db->qn('#__waterways_guide_country'))
							->where($db->qn('iso').' = '.$db->q(strtoupper($GuideCountry)));
						$countryrow = $db->setQuery($query)->loadAssoc();	
						$CountryName = stripslashes($countryrow["printable_name"]);
						if($thiswaterwaycount>0){
							if($thiswaterwaycount==1){
								$listresults.=" $thiswaterwaycount location\n";
							}else{
								$listresults.=" $thiswaterwaycount locations\n";
							}
							
						}
						$listresults.="\n\n".strtoupper($CountryName)."\n---------------------------------------------";
						$thisGuideCountry=$GuideCountry;
						$thisGuideWaterway="";
						$thiswaterwaycount=0;
					}
	
					if($GuideWaterway!=$thisGuideWaterway){
						if($thiswaterwaycount>0){
							if($thiswaterwaycount==1){
								$listresults.=" $thiswaterwaycount location\n";
							}else{
								$listresults.=" $thiswaterwaycount locations\n";
							}
						}
						$listresults.="\n$GuideWaterway [$GuideUpdatedisplay]";
						$thisGuideWaterway=$GuideWaterway;
						$thiswaterwaycount=0;
					}
					$thiswaterwaycount+=1;			
				
					
					//$listresults.="<tr><td class=bodytext colspan=2><hr></td></tr>\n";
		
					$guidematch=1;
				}
				if($thiswaterwaycount>0){
					if($thiswaterwaycount==1){
						$listresults.=" $thiswaterwaycount location\n";
					}else{
						$listresults.=" $thiswaterwaycount locations\n";
					}
				}
				$listresults.="\n";
			
				
				$message=$message_index;
				$from="<mooringsguides@barges.org>";
		
				$message.=$listresults." \n\n".$copyright_guides."\n\n".$footermooringsguide;
				//$replyto="DBA moorings guides <moorings@barges.org>";
				$subject="DBA moorings guides index ".date("Ymd",time());
				$subject=stripslashes($subject);
				$recipient=$email;
				$body=$message;

				if($mailOn) {
					$mailer = Factory::getMailer();
					$mailer->setSender([$config->get('mailfrom'), $config->get('fromname')]);
					$mailer->addRecipient($recipient);
					$mailer->addReplyTo($from, $from);
					$mailer->setSubject($subject);
					$mailer->setBody(nl2br($body));
					$mailer->isHtml(true);
					$mailer->Send();
				} else echo "<br />\r\n<span style='color: red'>Mail Disabled</span><br />\r\n";

				if(!$guiderequestmethod){
					$guiderequestmethod="email_site";
				}
				$ret=UpdateGuideRequests($login_memberid,"ALL","Index",$guiderequestmethod,"1");
				echo("An email with moorings guide index has been sent to $email and should arrive in a few minutes"); 
			}
		}else{
		
			//create listing
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn($guidetable))
				->where($db->qn('GuideStatus').' = 1')
				->order($db->qn(['GuideCountry', 'GuideWaterway', 'GuideOrder']));
			if($country && $country != "All" || !$country) $query->where($db->qn('GuideCountry').' = '.$db->q($country));
			if($waterway && $waterway != "All") $query->where($db->qn('GuideWaterway').' = '.$db->q(stripslashes($waterway)));
			$guides = $db->setQuery($query)->loadAssocList();
			$rows = count($guides);
			
			# If the search was unsuccessful then Display Message try again.
			If ($rows == 0){
				PRINT "<tr><td class=bodytext colspan=3>Sorry - there are no guides at the moment.</td></tr>\n";
			}else{
				
				$guidematch=0;	
				$listresults="<table border=\"0\" cellpadding=\"1\" cellspacing=\"2\">\n";		
				foreach($guids as $row) {
					$GuideID = stripslashes($row["GuideID"]);
					$GuideNo = stripslashes($row["GuideNo"]);
					$GuideVer = stripslashes($row["GuideVer"]);
					$GuideCountry = stripslashes($row["GuideCountry"]);
					$GuideWaterway = stripslashes($row["GuideWaterway"]);
					$GuideSummary = nl2br(stripslashes($row["GuideSummary"]));
					$GuideName = stripslashes($row["GuideName"]);
					$GuideRef = stripslashes($row["GuideRef"]);
					$GuideRating = stripslashes($row["GuideRating"]);
					$GuideLatLong = stripslashes($row["GuideLatLong"]);
					$GuideLocation = stripslashes($row["GuideLocation"]);
					$GuideMooring = nl2br(stripslashes($row["GuideMooring"]));
					$GuideFacilities = nl2br(stripslashes($row["GuideFacilities"]));
					$GuideCodes = nl2br(stripslashes($row["GuideCodes"]));
					$GuideCosts = nl2br(stripslashes($row["GuideCosts"]));
					$GuideAmenities = nl2br(stripslashes($row["GuideAmenities"]));
					$GuideContributors = nl2br(stripslashes($row["GuideContributors"]));
					$GuideRemarks = nl2br(stripslashes($row["GuideRemarks"]));
					$GuideLat = stripslashes($row["GuideLat"]);
					$GuideLong = stripslashes($row["GuideLong"]);
					$GuideDocs = stripslashes($row["GuideDocs"]);
					$GuidePostingDate = stripslashes($row["GuidePostingDate"]);
					$GuideCategory = stripslashes($row["GuideCategory"]);
					$GuideUpdate = stripslashes($row["GuideUpdate"]);
					$GuideStatus = stripslashes($row["GuideStatus"]);
					$GuideUpdatedisplay = (empty($GuideUpdate) ? 'Date unknown' : date('Y-m-d', strtotime($GuideUpdate)))." - Mooring Index: ".$GuideNo." - Version: ".$GuideVer;
					if(strtoupper($GuideCountry)!=strtoupper($thisGuideCountry)){
						//lookup country name
						$query = $db->getQuery(true)
							->select($db->qn('printable_name'))
							->from($db->qn('#__waterways_guide_country'))
							->where($db->qn('iso').' = '.$db->q(strtoupper($GuideCountry)));
						$countryrow = $db->setQuery($query)->loadAssoc();	
						$CountryName = stripslashes($countryrow["printable_name"]);
						$listresults.="<tr><td class=bodytext colspan=2><h1>$CountryName</h1></td></tr>\n";
						$thisGuideCountry=$GuideCountry;
						$thisGuideWaterway="";
					}
					if($GuideWaterway!=$thisGuideWaterway){
						$listresults.="<tr><td class=bodytext colspan=2><h1>$GuideWaterway</h1></td></tr>\n";
						$thisGuideWaterway=$GuideWaterway;
					}
					
					//convert dec to lat long
					if($GuideLat && $GuideLong){
						$GuideLatLong=decimal2degree($GuideLat,'LAT') ." , " . decimal2degree($GuideLong,'LON');
					}else{
						$GuideLatLong="Not known";
					}
					
					$i=1;
					$GuideRatingIcon="";
					while ($i <= $GuideRating) {
						$GuideRatingIcon.=" * ";
						$i++;
					}    
				
					//$listresults.="<tr valign='top'><td class='bodytext'><b>Waterway:</b></td><td class='bodytext'>".$GuideWaterway."</td></tr>\n";
				
					$listresults.="<tr valign='top'><td class='bodytext'><b>Name:</b></td><td class='bodytext'>".$GuideName."</td></tr>\n";
					if($GuideRating){
						$listresults.="<tr valign='top'><td class='bodytext'><b>Rating:</b></td><td class='bodytext'>".$GuideRatingIcon."</td></tr>\n";
					}	
					if($GuideLatLong){
						$listresults.="<tr valign='top'><td class='bodytext'><b>Lat/Long:</b></td><td class='bodytext'>".$GuideLatLong."</td></tr>\n";
					}
					if($GuideRef){
						$listresults.="<tr valign='top'><td class='bodytext'><b>Reference:</b></td ><td class='bodytext'>".$GuideRef."</td></tr>\n";
					}
					if($GuideLocation){
						$listresults.="<tr valign='top'><td class='bodytext'><b>Location:</b></td ><td class='bodytext'>".$GuideLocation."</td></tr>\n";
					}
					if($GuideMooring){
						$listresults.="<tr valign='top'><td class='bodytext'><b>Mooring:</b></td><td class='bodytext'>".$GuideMooring."</td></tr>\n";
					}

					if($GuideCodes){
						//add tick boxes here
						$query = $db->getQuery(true)
							->select('*')
							->from($db->qn('#__waterways_guide_services'))
							->where($db->qn('ServiceCategory').' = '.$db->q('mooringsguides'))
							->order($db->qn('ServiceSortOrder'));
						$boxes = $db->setQuery($query)->loadAssocList();
						$num_boxes = count($sections);
						$num_facilities=$num_boxes+1;
						$boxhtml="";
						foreach($boxes as $boxrow) {
							$boxid=$boxrow["ServiceID"];
							$boxdesc=$boxrow["ServiceDescGB"];
							$found = strstr ($GuideCodes, "|".$boxid."|");
							if($found){
								if($boxhtml){
									$boxhtml.=", ";
								}
								
								$boxhtml.=$boxdesc;
							}
						}
				
				
						$listresults.="<tr valign='top'><td class='bodytext'><b>Essentials:</b></td><td class='bodytext'>".$boxhtml."</td></tr>\n";
					}
					if($GuideFacilities){
						$listresults.="<tr valign='top'><td class='bodytext'><b>Facilities:</b></td><td class='bodytext'>".$GuideFacilities."</td></tr>\n";
					}
					if($GuideCosts){
						$listresults.="<tr valign='top'><td class='bodytext'><b>Costs:</b></td><td class='bodytext'>".$GuideCosts."</td></tr>\n";
					}
					if($GuideAmenities){
						$listresults.="<tr valign='top'><td class='bodytext'><b>Amenities:</b></td ><td class='bodytext'>".$GuideAmenities."</td></tr>\n";
					}
					if($GuideContributors){
						$listresults.="<tr valign='top'><td class='bodytext'><b>Contributors:</b></td ><td class='bodytext'>".$GuideContributors."</td></tr>\n";
					}
					if($GuideSummary){
						$listresults.="<tr valign='top'><td class='bodytext'><b>Summary:</b></td><td class='bodytext'>".$GuideSummary."</td></tr>\n";
					}
					if($GuideRemarks){
						$listresults.="<tr valign='top'><td class='bodytext'><b>Remarks:</b></td><td class='bodytext'>".$GuideRemarks."</td></tr>\n";
					}
				
					if($GuideUpdate){
						$listresults.="<tr valign='top'><td class='bodytext'><b>Last Update:</b></td><td class='bodytext'>".$GuideUpdatedisplay."</td></tr>\n";
					}
					//$listresults.="<tr valign='top'><td class='bodytext' colspan=2><b>".$thisguideenquiry."</b></td></tr>";
				
				
					$listresults.="<tr><td class=bodytext colspan=2><hr></td></tr>\n";
		
					$guidematch=1;
				}
				/*add blank entry
				$listresults.="<tr valign='top'><td class='bodytext' colspan=2><b>Please add new entries here</b></td></tr>\n";
	
				$listresults.="<tr valign='top'><td class='bodytext'><b>Waterway:</b></td><td class='bodytext'>?</td></tr>\n";
			
				$listresults.="<tr valign='top'><td class='bodytext'><b>Name:</b></td><td class='bodytext'>?</td></tr>\n";
				$listresults.="<tr valign='top'><td class='bodytext'><b>Rating:</b></td><td class='bodytext'>?</td></tr>\n";
				$listresults.="<tr valign='top'><td class='bodytext'><b>Lat/Long:</b></td><td class='bodytext'>?</td></tr>\n";
				$listresults.="<tr valign='top'><td class='bodytext'><b>Location:</b></td ><td class='bodytext'>?</td></tr>\n";
				$listresults.="<tr valign='top'><td class='bodytext'><b>Mooring:</b></td><td class='bodytext'>?</td></tr>\n";
				$listresults.="<tr valign='top'><td class='bodytext'><b>Facilities:</b></td><td class='bodytext'>?</td></tr>\n";
				$listresults.="<tr valign='top'><td class='bodytext'><b>Costs:</b></td><td class='bodytext'>?</td></tr>\n";
				$listresults.="<tr valign='top'><td class='bodytext'><b>Amenities:</b></td ><td class='bodytext'>?</td></tr>\n";
				$listresults.="<tr valign='top'><td class='bodytext'><b>Contributors:</b></td ><td class='bodytext'>?</td></tr>\n";
				$listresults.="<tr valign='top'><td class='bodytext'><b>Summary:</b></td><td class='bodytext'>?</td></tr>\n";
				$listresults.="<tr valign='top'><td class='bodytext'><b>Remarks:</b></td><td class='bodytext'>?</td></tr>\n";
				//$listresults.="<tr valign='top'><td class='bodytext'><b>Last Update:</b></td><td class='bodytext'>".$GuideDatedisplay."</td></tr>\n";
				*/
			
				$listresults.="<tr><td class=bodytext colspan=2><hr></td></tr>\n";
	
				
				
				$listresults.="</table>\n";
				$listresults.=$copyright_guides;
				if($guidematch==1){
			
					switch ($guideaction) {
					case "emailmelist":
	
						if($substatus==1){
							$actionmessage="emailmelist";
							$file="<html>";
							$file.="<head>";
							$file.="<meta name=\"DC.Format\" scheme=\"IMT\" content=\"html/text\">";
							//$file.="<meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\" />";
							$file.="</head>";
							$file.="<body><font size=2>";
							$file.="<br><b>DBA Moorings guide ".date("d M, Y",time())."</b> <br>\n";
							//$file.="The attached .doc can be opened in Microsoft Word<br>\n"; 
							$file.=$mooringsguidedocintrotext;
							$file.=$listresults;
							$file.="</font></body>";
							$file.="</html>\n"; 
							$message=$message_guides;
							$message.="\n\n".$copyright_guides."\n\n".$footermooringsguide;
							$thismessage=nl2br($message);
							//Remove colour bands from Excel file bgcolor='#FFFFCC' to bgcolor='#FFFFFF'
		
							$urlwaterway=str_replace(" ", "_", $waterway);
							include ("mime.inc");
							$sendto=$email;
							$replyto="DBA moorings guides <mooringsguides@barges.org>";
							$subject="DBA moorings guides ".$waterway." ".date("Ymd",time());
							$mimetype ="application/X-MS-Word"; // Mime type
							$filename="DBA_moorings_guide_".$urlwaterway."_".date("Ymd",time()).".doc";
							//Remove colour bands from Excel file bgcolor='#FFFFCC' to bgcolor='#FFFFFF'
							$mailfile = new CMailFile($subject,$sendto,$replyto,$thismessage,$filename,$file,$mimetype);
							$mailfile->sendfile();
		
							echo("An email with moorings guide file attached has been sent to $email and should arrive in a few minutes"); 
							if(!$guiderequestmethod){
								$guiderequestmethod="email_site";
							}
							$ret=UpdateGuideRequests($login_memberid,$country,$waterway,$guiderequestmethod,"1");
							//add to log
							
							if($login_memberid){
								$subject="Moorings guide email request";
								$changedate=date("Y-m-d H:i:s");
								$changelogtext="Guide for '$waterway' emailed to $email";
								$insert = new \stdClass();
								$insert->MemberID = $login_memberid;
								$insert->Subject = $subject;
								$insert->ChangeDesc = $changelogtext;
								$insert->ChangeDate = $changedate;
								$db->insertObject('#__waterways_guide_changelog', $insert) or die ("Couldn't update change log");
							}		
						}
	
						break;
					case "savewordlist":
						$actionmessage="savewordlist";
						$file="<html>";
						$file.="<head>";
						$file.="<meta name=\"DC.Format\" scheme=\"IMT\" content=\"html/text\">";
						//$file.="<meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\" />";
						$file.="</head>";
						$file.="<body><font size=2>";
						$file.="<br><b>DBA Moorings guide ".date("d M, Y",time())."</b> <br>\n";
						//$file.="The attached .doc can be opened in Microsoft Word<br>\n"; 
						$file.=$mooringsguidedocintrotext;
						$file.=$listresults;
						$file.="</font></body>";
						$file.="</html>\n"; 
						$urlwaterway=str_replace(" ", "_", $waterway);
						$filename="DBA_moorings_guide_".$urlwaterway."_".date("Ymd",time()).".doc";
						//Remove colour bands from Excel file bgcolor='#FFFFCC' to bgcolor='#FFFFFF'
						$sendfile=str_replace("FFFFCC","#FFFFFF",$file);
		
						header("Content-type: application/x-msword");
						header("Pragma: ");
						header("Cache-Control: ");
						# replace excelfile.xls with whatever you want the filename to  default to
						header("Content-Disposition: attachment; filename=$filename");
						echo($sendfile);
						if(!$guiderequestmethod){
							$guiderequestmethod="word_site";
						}
						$ret=UpdateGuideRequests($login_memberid,$country,$waterway,$guiderequestmethod,"1");
						//exit();
						break;
					case "printlist":			
						$actionmessage="printlist";
						break;
					}
				}
			}
		}
	}

?>

