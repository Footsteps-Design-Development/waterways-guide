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

use Joomla\CMS\Factory;
//---------------------------------------pdf------------------------------------------------------
$mooringsguidedocintrotextpdf="";
$filtertext="";
require_once("../../../commonV3.php");

$db = Factory::getDbo();

getpost_ifset(array('waterway','waterway1','waterway2','country','guideaction','filteroption','GuideMooringCodes','GuideHazardCodes','mooringsguidedocintrotextpdf','msid','menu_url'));

//$user = Factory::getUser();
//$login_memberid = $user->id;
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblMembers'))
	->where('('.$db->qn('ID').' = '.$db->q($msid).' OR '.$db->qn('ID2').' = '.$db->q($msid).')');
$memberrow = $db->setQuery($query)->loadAssoc();
$login_MembershipNo=$memberrow["MembershipNo"];
if(empty($memberrow["ID2"])){
	$contact=$memberrow["FirstName"]." ".$memberrow["LastName"];
}else{
	$contact=$memberrow["FirstName"]." ".$memberrow["LastName"]." and ".$memberrow["FirstName2"]." ".$memberrow["LastName2"];
}
//create pdf and output
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('#__waterways_guide'))
	->where($db->qn('GuideStatus').' = 1')
	->order($db->qn(['GuideCountry', 'GuideWaterway', 'GuideOrder']));
if($country && $country != 'All') $query->where($db->qn('GuideCountry').' = '.$db->q($country));
if($waterway && $waterway != 'All') $query->where($db->qn('GuideWaterway').' = '.$db->q(stripslashes($waterway)));
elseif(isset($waterway1, $waterway2)) {
	//split down for France
	$pdfsubtitle=$country." - ".$waterway1." to ".$waterway2;
	$query->where($db->qn('GuideWaterway').' BETWEEN '.stripslashes($waterway1).' AND '.stripslashes($waterway2));
}
//filter options
if($filteroption=="ALL" || $filteroption=="M"){
	//add any ticks in $GuideMoringCodes and compare to $GuideCodes
	$filterwhere = '('.$db->qn('GuideCategory').' = 1';
	//explode to array
	if($GuideMooringCodes){
		$codes = explode ("|", $GuideMooringCodes);
		$maxcodes=sizeof ($codes)-2;
		$codeno=1;
		while($codeno<=$maxcodes){
			$thiscode="|".$codes[$codeno]."|";
			$filterwhere .= ' AND '.$db->qn('GuideCodes')." LIKE '%".$thiscode."%'";
			$codeno+=1;
		}
	}
	$filterwhere.=")";
}
if($filteroption=="ALL" || $filteroption=="H"){
	//add any ticks in $GuideHazardCodes and compare to $GuideCodes
	if($filterwhere) $filterwhere .= ' OR ';
	else $filterwhere = '';
	$filterwhere .= '('.$db->qn('GuideCategory').' = 2';
	//explode to array
	if($GuideHazardCodes){
		$codes = explode ("|", $GuideHazardCodes);
		$maxcodes=sizeof ($codes)-2;
		$codeno=1;
		while($codeno<=$maxcodes){
			$thiscode="|".$codes[$codeno]."|";
			$filterwhere .= ' AND '.$db->qn('GuideCodes')." LIKE '%".$thiscode."%'";
			$codeno+=1;
		}
	}
	$filterwhere.=")";
}
if($filterwhere) $query->where('('.$filterwhere.')');
elseif($filteroption!="All"){
	//filter on Moorings and/or hazards without filter
	if($filteroption == "M") $query->where($db->qn('GuideCategory').' = 1');
	else if($filteroption == "H") $query->where($db->qn('GuideCategory').' = 2');
}
$rows = 0;
$guides = $db->setQuery($query)->loadAssocList();
$rows = count($guides);
# If the search was unsuccessful then Display Message try again.
If ($rows == 0){
	PRINT "<tr><td class=bodytext colspan=3>Sorry - there are no guides at the moment matching your selection.</td></tr>\n";
}else{
	
	//build filter options description
	//filteroption','GuideMooringCodes','GuideHazardCodes'));
	if($filteroption){
		if($GuideMooringCodes){
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__waterways_guide_services'))
				->where($db->qn('ServiceCategory')." = 'mooringsguides'")
				->order($db->qn('ServiceSortOrder'));
			$boxes = $db->setQuery($query)->loadAssocList();
			$filtertext="<b>Essentials:</b> ";
			$boxtext="";
			foreach($boxes as $boxrow) {
				$boxid=$boxrow["ServiceID"];
				$boxdesc=$boxrow["ServiceDescGB"];
				$found = strstr ($GuideMooringCodes, "|".$boxid."|");
				if($found){
					$filtertext.=$boxdesc.", ";
				}
			}
			$filtertext.="\n";
		}
		if($GuideHazardCodes){
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__waterways_guide_services'))
				->where($db->qn('ServiceCategory')." = 'hazardguides'")
				->order($db->qn('ServiceSortOrder'));
			$boxes = $db->setQuery($query)->loadAssocList();
			$filtertext.="<b>Hazards:</b> ";
			$boxtext="";
			foreach($boxes as $boxrow) {
				$boxid=$boxrow["ServiceID"];
				$boxdesc=$boxrow["ServiceDescGB"];
				$found = strstr ($GuideHazardCodes, "|".$boxid."|");
				if($found){
					$filtertext.=$boxdesc.", ";
				}
			}
			$filtertext.="\n";
		}
	}



	$guidematch=0;
	$listresults = '';
	foreach($guides as $row) {
		$GuideID = stripslashes($row["GuideID"]);
		$GuideNo = stripslashes($row["GuideNo"]);
		$GuideVer = stripslashes($row["GuideVer"]);
		$GuideCountry = stripslashes($row["GuideCountry"]);
		$GuideWaterway = mb_convert_encoding(stripslashes($row["GuideWaterway"]), "ISO-8859-1", "UTF-8");

		$GuideWaterway = str_replace("&", "and", $GuideWaterway);
		$GuideSummary = mb_convert_encoding(stripslashes($row["GuideSummary"]), "ISO-8859-1", "UTF-8");
		$GuideName = mb_convert_encoding(stripslashes($row["GuideName"]), "ISO-8859-1", "UTF-8");
		$GuideRef = mb_convert_encoding(stripslashes($row["GuideRef"]), "ISO-8859-1", "UTF-8");
		$GuideRating = stripslashes($row["GuideRating"]);
		$GuideLatLong = stripslashes($row["GuideLatLong"]);
		$GuideLocation = mb_convert_encoding(stripslashes($row["GuideLocation"]), "ISO-8859-1", "UTF-8");
		$GuideMooring = mb_convert_encoding(stripslashes($row["GuideMooring"]), "ISO-8859-1", "UTF-8");
		$GuideFacilities = mb_convert_encoding(stripslashes($row["GuideFacilities"]), "ISO-8859-1", "UTF-8");
		$GuideCodes = stripslashes($row["GuideCodes"]);
		$GuideCosts = stripslashes($row["GuideCosts"]);
		$GuideAmenities = mb_convert_encoding(stripslashes($row["GuideAmenities"]), "ISO-8859-1", "UTF-8");
		$GuideContributors = mb_convert_encoding(stripslashes($row["GuideContributors"]), "ISO-8859-1", "UTF-8");
		$GuideRemarks = mb_convert_encoding(stripslashes($row["GuideRemarks"]), "ISO-8859-1", "UTF-8");
		$GuideLat = mb_convert_encoding(stripslashes($row["GuideLat"]), "ISO-8859-1", "UTF-8");
		$GuideLong = mb_convert_encoding(stripslashes($row["GuideLong"]), "ISO-8859-1", "UTF-8");
		$GuideDocs = stripslashes($row["GuideDocs"]);
		$GuidePostingDate = stripslashes($row["GuidePostingDate"]);
		$GuideCategory = $row["GuideCategory"];
		$GuideStatus = $row["GuideStatus"];
		$GuideOrder = $row["GuideOrder"];

		if($GuideStatus!=1){
			if($GuideStatus==0){
				$GuideName.=" (V. ".$GuideVer." Pending)";
			}
			if($GuideStatus==2){
				$GuideName.=" (V. ".$GuideVer." Archived)";				
			}
		}
		
		$GuideUpdate = stripslashes($row["GuideUpdate"]);
		$GuideUpdatedisplay = (empty($GuideUpdate) ? 'Pre 2009' : date('dmy', strtotime($GuideUpdate)))." - Mooring Index: ".$GuideNo." - Version: ".$GuideVer;
		if($GuideCategory==2){
			//add hazard icon in front of name
			switch ($GuideRating) {
				case "1":
				$ratingtitle="<b>Hazard:</b> Rating Low";
			break;
				case "2":
				$ratingtitle="<b>Hazard:</b> Rating Medium";					
			break;
				case "3":
				$ratingtitle="<b>Hazard:</b> Rating High";					
			}
			$GuideRatingText=$ratingtitle;

		}else{

			switch ($GuideRating) {
				case "":
				$ratingtitle="<b>Mooring:</b> Rating Unknown";
			break;
				case "0":
				$ratingtitle="<b>Mooring:</b> Rating Doubtful";
			break;
				case "1":
				$ratingtitle="<b>Mooring:</b> Rating Adequate";
			break;
				case "2":
				$ratingtitle="<b>Mooring:</b> Rating Good";					
			break;
				case "3":
				$ratingtitle="<b>Mooring:</b> Rating Very Good";					
			}
			$GuideRatingText=$ratingtitle;
		}    

		if(isset($GuideCountry, $thisGuideCountry) && strtoupper($GuideCountry)!=strtoupper($thisGuideCountry)){
			//lookup country name
			$query = $db->getQuery(true)
				->select($db->qn('printable_name'))
				->from($db->qn('#__waterways_guide_country'))
				->where($db->qn('iso').' = '.$db->q(strtoupper($GuideCountry)));
			$countryrow = $db->setQuery($query)->loadAssoc();
			$CountryName = stripslashes($countryrow["printable_name"]);
			//$listresults.="#NP\n";
			//$listresults.="#NP\n";
			$listresults.="1<<b>".strtoupper ($CountryName)."</b>>\n";	
			$thisGuideCountry=$GuideCountry;
			//$GuideMooringNo=0;
			
		}
		
		//new waterway so make heading
		if(!isset($thisGuideWaterway) || $GuideWaterway!=$thisGuideWaterway){
			//page break on waterway first letter change
			$GuideWaterwayAlpha=substr($GuideWaterway,0,1);
			if(!isset($thisGuideWaterwayAlpha) || $GuideWaterwayAlpha!=$thisGuideWaterwayAlpha){
				$listresults.="#NP\n";
				$thisGuideWaterwayAlpha=$GuideWaterwayAlpha;
				$thisGuideCountryChange=0;
			}
			$listresults.="1<<b>".$GuideWaterway."</b>>\n\n";
			if($GuideSummary){
				$listresults.="<b>Summary:</b> ".$GuideSummary."\n";
			}
			$listresults.="______________________________________________________________________________________________________\n";
			$DisplayGuideSummary="";
			$thisGuideWaterway=$GuideWaterway;
			$GuideMooringNo=0;
		}			
		
		$GuideMooringNo+=1;
		
		if($GuideSummary){
			//$DisplayGuideSummary.=$GuideSummary."\n";	
		}
		
		
		//convert dec to lat long
		if($GuideLat && $GuideLong){
			$GuideLatLong=decimal2degree($GuideLat,'LAT') ." , " . decimal2degree($GuideLong,'LON');
			$GuideLatLong=str_replace("&deg;", mb_convert_encoding("°", "ISO-8859-1", "UTF-8"), $GuideLatLong);
		}else{
			$GuideLatLong="Not known";
		}
				
		//$listresults.="<tr valign='top'><td><b>Waterway:</b></td><td>".$GuideWaterway."</td></tr>\n";
	
		$listresults.="2<".$GuideName.">\n";
		if($GuideRating){
			$listresults.=$GuideRatingText."\n";
		}	
		if($GuideLatLong){
			$listresults.="<b>Lat/Long:</b> ".$GuideLatLong."\n";
		}
		if($GuideRef){
			$listresults.="<b>Reference:</b> ".$GuideRef."\n";
		}
		if($GuideLocation){
			$listresults.="<b>Location:</b> ".$GuideLocation."\n";
		}
		if($GuideMooring){
			$listresults.="<b>Mooring:</b> ".$GuideMooring."\n";
		}

		if($GuideCodes){
			//add tick boxes here
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__waterways_guide_services'))
				->order($db->qn('ServiceSortOrder'));
			switch ($GuideCategory) {
				case "1":
					$query->where($db->qn('ServiceCategory')." = 'mooringsguides'");
					$boxestitle="Essentials";
					break;
				case "2":
					$query->where($db->qn('ServiceCategory')." = 'hazardguides'");
					$boxestitle="Hazard category";
					break;
			}
			$boxes = $db->setQuery($query)->loadAssocList();
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
	
			$listresults.="<b>".$boxestitle.":</b> ".$boxhtml."\n";
		}

		if($GuideFacilities){
			$listresults.="<b>Facilities:</b> ".$GuideFacilities."\n";
		}
		if($GuideCosts){
			$listresults.="<b>Costs:</b> ".$GuideCosts."\n";
		}
		if($GuideAmenities){
			$listresults.="<b>Amenities:</b> ".$GuideAmenities."\n";
		}
		if($GuideContributors){
			$listresults.="<b>Contributors:</b> ".$GuideContributors."\n";
		}
		if($GuideRemarks){
			$listresults.="<b>Remarks:</b> ".$GuideRemarks."\n";
		}
	
		if($GuideUpdate){
			$listresults.="<b>Last Update:</b> ".$GuideUpdatedisplay."\n";
		}
		//update on-line link guide no
		$listresults.="{".$GuideID."}\n";
		//$listresults.="<tr valign='top'><td colspan=2><b>".$thisguideenquiry."</b></td></tr>";
	
	
		$listresults.="______________________________________________________________________________________________________\n";

		$guidematch=1;
	}


	//$listresults.=$copyright_guides;
	
}


//$listresults = htmlentities($listresults, ENT_QUOTES, "UTF-8");



//===================================================================================================
// this is the php file which creates the readme.pdf file, this is not seriously 
// suggested as a good way to create such a file, nor a great example of prose,
// but hopefully it will be useful
//
// adding ?d=1 to the url calling this will cause the pdf code itself to ve echoed to the 
// browser, this is quite useful for debugging purposes.
// there is no option to save directly to a file here, but this would be trivial to implement.
//
// note that this file comprisises both the demo code, and the generator of the pdf documentation
//
//===================================================================================================


// don't want any warnings turning up in the pdf code if the server is set to 'anal' mode.
//error_reporting(7);
error_reporting(E_ALL);
set_time_limit(20000);
include 'class.ezpdf.php';
//include 'Cezpdf.php';
$thisdate=date("d/m/Y");
$longdate=date("F j, Y");

$col1r=0;
$col1g=0;
$col1b=0;

$col2r=0;
$col2g=0;
$col2b=0;

$col3r=0;
$col3g=0;
$col3b=255;

// define a clas extension to allow the use of a callback to get the table of contents, and to put the dots in the toc
class Creport extends Cezpdf {

var $reportContents = array();

function __construct($p,$o){
  $this->Cezpdf($p,$o);
}

function rf($info){
  // this callback records all of the table of contents entries, it also places a destination marker there
  // so that it can be linked too
  $tmp = $info['p'];
  $lvl = $tmp[0];
  $lbl = rawurldecode(substr($tmp,1));
  $num=$this->ezWhatPageNumber($this->ezGetCurrentPageNumber());
  $this->reportContents[] = array($lbl,$num,$lvl );
  $this->addDestination('toc'.(count($this->reportContents)-1),'FitH',$info['y']+$info['height']);
}

function dots($info){
  // draw a dotted line over to the right and put on a page number
  $tmp = $info['p'];
  $lvl = $tmp[0];
  $lbl = substr($tmp,1);
  $xpos = 500;

  switch($lvl){
    case '1':
      $size=16;
      $thick=1;
      break;
    case '2':
      $size=12;
      $thick=0.5;
      break;
  }

  $this->saveState();
  $this->setLineStyle($thick,'round','',array(0,10));
  $this->line($xpos,$info['y'],$info['x']+5,$info['y']);
  $this->restoreState();
  $this->addText($xpos+5,$info['y'],$size,$lbl);


}

}
// (defaults to legal)
// this code has been modified to use ezpdf.

//$pdf = new Cezpdf('a4','portrait');
$pdf = new Creport('a4','portrait');

$pdf -> ezSetMargins(40,50,70,70);//$top,$bottom,$left,$right
$lmx=70;
$rmx=595.25-$lmx=70;
//$lmx=70;
//$rmx=538;
// put a line bottom on all the pages
$all = $pdf->openObject();
$pdf->saveState();
$pdf->setStrokeColor($col1r,$col1g,$col1b,1);
$pdf->line($rmx,40,$lmx,40);
$pdf->setStrokeColor($col2r,$col2g,$col2b,1);
//$pdf->line(50,822,558,822);
$pdf->setColor($col2r,$col2g,$col2b,1);
$pdf->addText($lmx,28,10,$footer3." - ".$thisdate);

//add membership number to identify download and help protect copyright 20160915 CJG
$footer2="The guides are compiled from member contributions which have been shared with the DBA for the sole use of its members.\n";
$footer2a="Copyright ".date("Y ")." DBA The Barge Association, ".$login_MembershipNo.".\nFor use of DBA member ".$contact." only.\n";

$pdf->addText($lmx-2,18,5,$footer2);
$pdf->addText($lmx-2,10,5,$footer2a);

$pdf->restoreState();
$pdf->closeObject();
// note that object can be told to appear on just odd or even pages by changing 'all' to 'odd'
// or 'even'.
$pdf->addObject($all,'all');

$pdf->ezSetDy(-75);

$mainFont = '../fonts/Helvetica.afm';
//$mainFont = './fonts/Times-Roman.afm';
$codeFont = '../fonts/Courier.afm';
// select a font

$diff=array(196=>'Adieresis',228=>'adieresis',
            214=>'Odieresis',246=>'odieresis',
            220=>'Udieresis',252=>'udieresis',
            223=>'germandbls');
// and the first time that you call selectFont for each font, use
//$pdf->selectFont($mainFont,array('encoding'=>'WinAnsiEncoding','differences'=>$diff));
$pdf->selectFont($mainFont);


$pdf->setColor($col2r,$col2g,$col2b,1);
$pdf->ezText("<b>DBA - The Barge Association</b>\n",25,array('justification'=>'centre'));
$pdf->ezText("<b>Waterways Guide</b>\n",20,array('justification'=>'centre'));
$pdf->ezText($mooringsguidedocintrotextpdf,10,array('justification'=>'centre'));
if($filtertext){
	$pdf->ezText("<b>PLEASE NOTE - This guide is based on a filter selection:</b>\n\n".$filtertext,10,array('justification'=>'centre'));
}
$pdf->ezSetDy(-100);
// modified to use the local file if it can

$pdf->openHere('Fit');


$pdf->selectFont($mainFont);


//if (file_exists('Image/header/intro1.jpg')){
  //$pdf->addJpegFromFile('Image/header/intro1.jpg',1,$pdf->y-120,500,50);
  //addPngFromFile(imgFileName,x,y,w,[h])
//}
$pdf->ezSetDy(-120);
$pdf->ezText($longdate."\n",16,array('justification'=>'centre'));


$pdf->ezNewPage();
$pdf->setColor($col1r,$col1g,$col1b,1);
$pdf->ezStartPageNumbers($rmx,28,10,'left','',1);
$pdf->setColor($col2r,$col2g,$col2b,1);
$size=8;
$height = $pdf->getFontHeight($size);
$textOptions = array('justification'=>'left');
$collecting=0;
$code='';
//get main text and split into lines at \n
$lines = explode ("\n", $listresults);
$maxlines=sizeof ($lines);
$lineno=0;
while($lineno<$maxlines){
	$line=$lines[$lineno];
	// go through each line, showing it as required, if it is surrounded by '<>' then 
	// assume that it is a title
	$line=chop($line);
	if (strlen($line) && $line[0]=='#'){
		// comment, or new page request
		switch($line){
	  		case '#NP':
				$pdf->ezNewPage();
				break;
	  		case '#C':
				$pdf->selectFont($codeFont);
				$textOptions = array('justification'=>'left','left'=>20,'right'=>20);
				$size=10;
				break;
	  		case '#c':
				$pdf->selectFont($mainFont);
				$textOptions = array('justification'=>'full');
				$size=12;
				break;
	  		case '#X':
				$collecting=1;
				break;
	  		case '#x':
				$pdf->saveState();
				eval($code);
				$pdf->restoreState();
				$pdf->selectFont($mainFont);
				$code='';
				$collecting=0;
				break;
		}
	} else if ($collecting){
		$code.=$line;
	//} else if (((strlen($line)>1 && $line[1]=='<') || (strlen($line) && $line[0]=='<')) && $line[strlen($line)-1]=='>') {
	} else if (((strlen($line)>1 && $line[1]=='<') ) && $line[strlen($line)-1]=='>') {
	// then this is a title
		switch($line[0]){
		  case '1':
			$tmp = substr($line,2,strlen($line)-3);
			$tmp2 = $tmp.'<C:rf:1'.rawurlencode($tmp).'>';
			$pdf->setColor($col3r,$col3g,$col3b,1);
			$pdf->ezText($tmp2,14,array('justification'=>'left'));
			$pdf->setColor($col2r,$col2g,$col2b,1);

			break;

		  default:
			$tmp = substr($line,2,strlen($line)-3);
			// add a grey bar, highlighting the change
			$tmp2 = $tmp.'<C:rf:2'.rawurlencode($tmp).'>';
			$tmp2="<b>".$tmp2."</b>";
			$pdf->setColor($col1r,$col1g,$col1b,1);
			$pdf->ezText($tmp2,14,array('justification'=>'left'));
			$pdf->setColor($col2r,$col2g,$col2b,1);
			break;
		}
	} else if (((strlen($line)>1 && $line[0]=='{') ) && $line[strlen($line)-1]=='}') {
	// then this is an update link
		$tmp = substr($line,1,strlen($line)-2);
		//example link http://www.barges.org/members/waterwaysguide/waterwaysguide-search?guideaction=memberedit&infoid=7022
		$pdf->ezText('<c:alink:http://'.$menu_url.'?guideaction=memberedit&infoid='.$tmp.'>Click here to update this entry on-line</c:alink>');
	}else{
		// then this is just text
		// the ezpdf function will take care of all of the wrapping etc.
		$pdf->ezText($line,$size,$textOptions);
		//$pdf->ezText("","4",$textOptions);		
	}
	$lineno+=1;
}
	
$pdf->ezStopPageNumbers(1,1);


// now add the table of contents, including internal links
$pdf->ezInsertMode(1,1,'after');
$pdf->ezNewPage();
$pdf->setColor($col1r,$col1g,$col1b,1);
$pdf->ezText("<b>Index of waterways and locations</b>\n",16,array('justification'=>'left'));
$pdf->setColor($col2r,$col2g,$col2b,1);
$xpos = 520;
$contents = $pdf->reportContents;
foreach($contents as $k=>$v){
  switch ($v[2]){
    case '1':
      $y=$pdf->ezText('<c:ilink:toc'.$k.'>'.$v[0].'</c:ilink><C:dots:1'.$v[1].'>',12,array('aright'=>$xpos));
//      $y=$pdf->ezText($v[0].'<C:dots:1'.$v[1].'>',16,array('aright'=>$xpos));
      break;
    case '2':
      $pdf->ezText('<c:ilink:toc'.$k.'>'.$v[0].'</c:ilink><C:dots:2'.$v[1].'>',10,array('left'=>10,'aright'=>$xpos));
      //add the page number to the indexarray
	  $indexarray[$k][2] = $v[1];
	  break;
  }
}
//$pdf->ezText($contents,10);


//Create a Location index
/*$pdf->ezNewPage();
$pdf->setColor($col1r,$col1g,$col1b,1);
$pdf->ezText("<b>Index of Locations by Waterway</b>\n",20,array('justification'=>'left'));
$pdf->setColor($col2r,$col2g,$col2b,1);

$xpos = 520;

//create an array containing the key/value-pair you want to use the sort on
$helper_array = array();
$column_to_sort = 0;
foreach($indexarray as $k => $v)
{
     $helper_array[$k] = $v[$column_to_sort];
}

// sort the helper-array, make sure the index association is maintained
asort($helper_array, SORT_STRING );

// display the sorted result using the helper_array     
$curlocation="";
foreach($helper_array as $k => $v)
{
	$location=$indexarray[$k][0];
	$org=$indexarray[$k][1];
	if($curlocation!=$location){
		//new location
		$pdf->setColor($col1r,$col1g,$col1b,1);
		$pdf->ezText($location,16);
		$pdf->setColor($col2r,$col2g,$col2b,1);
		$curlocation=$location;		
	}
	$pdf->ezText('<c:ilink:toc'.$k.'>'.$org.'</c:ilink><C:dots:2'.$indexarray[$k][2].'>',12,array('left'=>10,'aright'=>$xpos));
}
*/
//$d=1;

if (isset($d) && $d){
  $pdfcode = $pdf->ezOutput(1);
  $pdfcode = str_replace("\n","\n<br>",htmlspecialchars($pdfcode));
  echo '<html><body>';
  echo($sql);	
  echo trim($pdfcode);
  
  echo '</body></html>';
} else {

	/*$filename="DBA_moorings_guide_".date("Ymd",time()).".pdf";
						
	header("Content-type: application/pdf");
	header("Pragma: ");
	header("Cache-Control: ");
	header("Content-Disposition: attachment; filename=$filename");
	*/
	$pdf->ezStream();
	//echo($pdf->ezOutput(1));
}
?>