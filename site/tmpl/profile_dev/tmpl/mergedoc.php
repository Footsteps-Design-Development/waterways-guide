<?php

use Joomla\CMS\Factory;

require_once("../../../commonV3.php");

$db = Factory::getDbo();

getpost_ifset(array('template','memberid'));
//echo("template ". $template. ",mid ".$memberid);

/*
function modifier($vars, $rftfile) {

	$xchange = array ('\\' => "\\\\", '<br />' => "\\par", 
						'{'  => "\{",
						   '}'  => "\}");

	$document = file_get_contents($rftfile);
	if(!$document) {
		return false;
	}
	$StartCode = strpos($document, "[TITLE]");
	$EndCode = strpos($document , "[SIGNATURE]");
	$Length=$EndCode+11-$StartCode;
	
	$body = substr($document, $StartCode, $Length);
	//echo("$StartCode - $EndCode $body <br><br>");
	foreach($vars as $key=>$value) {
		$search = "[".strtoupper($key)."]";

		foreach($xchange as $orig => $replace) {
			$value = str_replace($orig, $replace, $value);
		}
		$body = str_replace($search, $value, $body);
	}
	return $body;
}

*/

function modifier($vars, $rftfile) {

	$xchange = array ('\\' => "\\\\", '<br />' => "\\par", 
						'{'  => "\{",
						   '}'  => "\}");

	$document = file_get_contents($rftfile);
	if(!$document) {
		return false;
	}

	foreach($vars as $key=>$value) {
		$search = "[".strtoupper($key)."]";

		foreach($xchange as $orig => $replace) {
			$value = str_replace($orig, $replace, $value);
		}
		$document = str_replace($search, $value, $document);
	}
	return $document;
}


$new_rtf="";


if($template && $memberid){
	
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('tblMembers'))
		->where($db->qn('ID').' = '.$db->q($memberid));
	$memberinfo = $db->setQuery($query)->loadAssocList();
	if (!$memberinfo) {
		echo("no memberid");
		//break;
	}
	$row = reset($memberinfo);
	
	$LOGIN=$row["Login"];
	$PW=$row["PW"];
	$TITLE=$row["Title"];
	$FIRSTNAME=stripslashes($row["FirstName"]);
	$LASTNAME=stripslashes($row["LastName"]);
	$TITLE2=$row["Title2"];
	$FIRSTNAME2=stripslashes($row["FirstName2"]);
	$LASTNAME2=stripslashes($row["LastName2"]);

	//work out greeting whether one or two members
	if($LASTNAME2){
		if($LASTNAME==$LASTNAME2){
			//same surname
			$ADDRESSGREETING=$TITLE." ".$FIRSTNAME." and ".$TITLE2." ".$FIRSTNAME2." ".$LASTNAME;
			$GREETING=$TITLE." and ".$TITLE2." ".$LASTNAME;
	
		}else{
			//different surnames
			$GREETING=$TITLE." ".$LASTNAME." and ".$TITLE2." ".$LASTNAME2;
			$ADDRESSGREETING=$TITLE." ".$FIRSTNAME." ".$LASTNAME." and ".$TITLE2." ".$FIRSTNAME2." ".$LASTNAME2;
	
		}
	}else{
		//single member
		$GREETING=$TITLE." ".$LASTNAME;
		$ADDRESSGREETING=$TITLE." ".$FIRSTNAME." ".$LASTNAME;
				
	}


	$MEMBERSHIPNO=$row["MembershipNo"];
	$BASICSUB=$row["BasicSub"];
	if(empty($row["DatePaid"])) {
		$datelastpaid = 'blank - joined '. $DateJoined;
		$renewyear = date('Y', '+1 year');
		$DATERENEWAL = date("d F, Y", '+1 year'); 
		
	} else {
		$datelastpaid = date('Y-m-d', strtotime($row["DatePaid"]));
		$renewyear = date('Y', strtotime($row['DatePaid'].' +1 year'));
		$DATERENEWAL = date("d F, Y", strtotime($row['DatePaid'].' +1 year')); 
	}

	//Get Address
	$ADDRESS="";
	$Address1=stripslashes($row["Address1"]);
	if($Address1){
		$ADDRESS=$Address1;
	}
	$Address2=stripslashes($row["Address2"]);
	if($Address2){
		$ADDRESS.="<br />\n".$Address2;
	}
	$Address3=stripslashes($row["Address3"]);
	if($Address3){
		$ADDRESS.="<br />\n".$Address3;
	}
	$Address4=stripslashes($row["Address4"]);
	if($Address4){
		$ADDRESS.="<br />\n".$Address4;
	}
	$PostCode=stripslashes($row["PostCode"]);
	if($PostCode){
		$ADDRESS.="<br />\n".$PostCode;
	}
	$Country=$row["Country"];
	if($Country){
		$ADDRESS.="<br />\n".$Country;
	}		
		
	$DATE=date("j F, Y");	
	$SIGNATURE=stripslashes($membership_signature);
		
	
		
	 $vars = array('LOGIN' => $LOGIN,'PW' => $PW,'TITLE' => $ADDRESSGREETING,'GREETING' => $GREETING, 'FIRSTNAME' => $FIRSTNAME,'LASTNAME' => $LASTNAME,'MEMBERSHIPNO' => $MEMBERSHIPNO,'DATE' => $DATE,'ADDRESS' => $ADDRESS,'SIGNATURE' => $SIGNATURE, 'DATERENEWAL' => $DATERENEWAL, 'BASICSUB' => $BASICSUB );
						
	$new_rtf = modifier($vars, "../../../templates/".$template);
	
	//$template="DBA_letter.rtf";
	
	header("Content-type: application/rtf"); 
	header("Cache-Control: no-cache");
	header("Pragma: no-cache");
	header("Content-Disposition: attachment; filename=$template");
	
	echo($new_rtf);
}else{
	echo("error: template or member info missing");
}
?>


