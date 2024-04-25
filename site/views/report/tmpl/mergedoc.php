<?php
/*
$login_email= $_SESSION["login_email"];
$login_name= $_SESSION["login_name"];
$wheresql = stripslashes($_SESSION["wheresql"]);
$sort = $_SESSION["sort"];
*/
define( '_JEXEC', 1 );
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', strstr(__DIR__, 'public_html', true).'public_html');
define('JPATH_COMPONENT', JPATH_BASE .DS.'components'.DS.'com_waterways_guide');
require_once(JPATH_BASE .DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE .DS.'includes'.DS.'framework.php');
require_once(JPATH_COMPONENT .DS.'commonV3.php');
use Joomla\CMS\Factory;
$db = Factory::getDbo();
getpost_ifset(array('login_email','table','wheresql','maillist','subject','message','send','sort','status','template'));
$secs_now = time();
$secsinayear=31536000;
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
	//echo("<br><br>$StartCode - $EndCode $body <br><br>");
	foreach($vars as $key=>$value) {
		$search = "[".strtoupper($key)."]";
		foreach($xchange as $orig => $replace) {
			$value = str_replace($orig, $replace, $value);
		}
		$body = str_replace($search, $value, $body);
	}
	return $body;
}
	
if(!$template){
	$template="DBA_letter_Sub_cc_Reminder.rtf";
}
//echo("Where=$wheresql");
if($template && $maillist){
	if(isset($table) && $table=="archive"){
		$memtable="tblMembers_archive";
	}else{
		$memtable="tblMembers";
	}
	
	//check if we have a list
	if($maillist=="_"){
		//assume direct from wheresql bypassing select list so create $maillist
		$query = $db->getQuery(true)
			->select($db->qn('ID'))
			->from($db->qn($memtable))
			->where($wheresql)
			->order($sort);
		$result = $db->setQuery($query)->loadAssocList();
			if (!$result) {
				echo("<P>Error finding members $sql</P>");
				//exit();
			}
		$num_rows = count($result);
		$maillist="";
		foreach($result as $row) {
			$maillist.="_".$row["ID"];
		}
		$maillist.="_";
	}
	
	$query = $db->getQuery(true)
		->select($db->qn('ID'))
		->from($db->qn($memtable));
	$records = $db->setQuery($query)->loadAssocList();
	if (!$records) {
		echo("<P>Error finding members: " . $query->__toString() . "</P>");
		exit();
	}
	$num_records = count($records);
	
	$thisdate=date("d M Y");
	$Title="Target listing - $thisdate" ;
	$maxrecords = 10000;
	$members = explode ("_", $maillist);
	$maxmembers=sizeof ($members);
	
	
	//records found so ok to do email
	
	$status="submitted";
	
	if ($status=="submitted"){
		//do the merge 
		$message="<html>";
		$message.="<br><b>$Title</b> <br>$maxmembers have been selected for the merge list from $num_records members on the database<br>\n";
		$message.="<b>Search criteria:</b> ".(isset($criteria) ? $criteria : '')."<br>\n"; 
		$message.="<br></html>\n"; 
		
		$thismemberid=0;
		$mergeno=1;
		while($mergeno<$maxmembers){
			$thismemberid=$members[$mergeno];
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn($memtable))
				->where($db->qn('ID').' = '.$db->q($thismemberid));
			$memberinfo = $db->setQuery($query)->loadAssocList();
			if (!$memberinfo) {
				break;
			}
			$row = reset($memberinfo);
			
			$LOGIN=$row["Login"];
			$PW=$row["PW"];
			$EMAIL=$row["Email"];
			$TITLE=$row["Title"];
			$FIRSTNAME=stripslashes($row["FirstName"]);
			$LASTNAME=stripslashes($row["LastName"]);
			$TITLE2=$row["Title2"];
			$FIRSTNAME2=stripslashes($row["FirstName2"]);
			$LASTNAME2=stripslashes($row["LastName2"]);
			$DateJoined=$row["DateJoined"];
			$DatePaid=$row["DatePaid"];
		
			//work out greeting whether one or two members
			$ADDRESSGREETING="";
			$GREETING="";
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
			if($DatePaid){
				list ($myDate, $myTime) = explode (' ', $row["DatePaid"]);
				list ($myyear, $mymonth, $myday) = explode ('-', $myDate);
				list ($myhour, $mymin, $mysec) = explode (':', $myTime);
				if($myyear){
					$datelastpaid="$myyear-$mymonth-$myday";
				}else{
					$datelastpaid="blank - joined $DateJoined";
				}
				$renewyear=$myyear+1;
			}
			$my_secs=strtotime($DatePaid);
			
			$DATERENEWAL=date("d F, Y",$my_secs+$secsinayear); 
		
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
				
			
			$vars = array('LOGIN' => $LOGIN,'PW' => $PW,'EMAIL' => $EMAIL,'GREETING' => $GREETING, 'TITLE' => $ADDRESSGREETING,'FIRSTNAME' => $FIRSTNAME,'LASTNAME' => $LASTNAME,'MEMBERSHIPNO' => $MEMBERSHIPNO,'DATE' => $DATE,'ADDRESS' => $ADDRESS,'SIGNATURE' => $SIGNATURE, 'DATERENEWAL' => $DATERENEWAL, 'BASICSUB' => $BASICSUB );
				
			//$vars = array('LOGIN' => $LOGIN,'PW' => $PW,'TITLE' => $TITLE,'FIRSTNAME' => $FIRSTNAME,'LASTNAME' => $LASTNAME,'MEMBERSHIPNO' => $MEMBERSHIPNO,'DATE' => $DATE,'ADDRESS' => $ADDRESS,'SIGNATURE' => $SIGNATURE, 'DATERENEWAL' => $DATERENEWAL, 'BASICSUB' => $BASICSUB );
			if(empty($pagecontent)){
				$pagecontent= modifier($vars, "../../../templates/".$template);
			}else{
				$page= modifier($vars, "../../../templates/".$template);
				$pagecontent.= " \par \page ".$page;
			}
			//echo("<br><br><br>Content = ".$pagecontent);
			$mergeno+=1;
			//echo("<br>GREETING = ".$GREETING);
			//echo("<br>mergeno = ".$mergeno);
		
		}
	}	
	//find header and footer and assemble body pages between				
	$rftfile="../../../templates/".$template;
	$document = file_get_contents($rftfile);
	if(empty($document)) {
		return false;
	}
	$StartCode = strpos($document, "[TITLE]");
	$EndCode = strpos($document , "[SIGNATURE]");
	$EndCode+=10;
	$header=substr($document, 0, $StartCode-1);
	$footer = substr($document, $EndCode+1);
	$new_rtf=$header.(isset($pagecontent) ? $pagecontent : '').$footer;
	//echo("<br>".$maillist);
	//echo("<br>".$new_rtf);
	header("Content-type: application/rtf"); 
	header("Cache-Control: no-cache");
	header("Pragma: no-cache");
	header("Content-Disposition: attachment; filename=$template");
	
	echo($new_rtf);
	//
}else{
	echo("error: template or member info missing");
}
?>
