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
getpost_ifset(array('table','wheresql','maillist','criteria','status','sort'));
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
    		echo("<P>Error finding members</P>");
	    	exit();
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
    	echo("<P>Error finding members</P>");
	    exit();
		}
//records found so ok to do email
$num_records = count($records);
$thisdate=date("d M Y");
$Title="Target listing - $thisdate" ;
$maxrecords = 10000;
$members = explode ("_", $maillist);
$maxmembers=sizeof ($members)-2;
$status="submitted";
if ($status=="submitted"){
	//do the merge 
	$message="<html>";
	$message.="<br><b>$Title</b> <br>$maxmembers have been selected for the merge list from $num_records members on the database<br>\n";
	$message.="<b>Search criteria:</b> ".(isset($criteria) ? $criteria : '')."<br>\n"; 
	$message.="The attached .xls can be opened in Microsoft Excel<br></html>\n"; 
	
	$file="<html>";
	$file.="<br><b>$Title</b> <br>$maxmembers have been selected for the merge list from $num_records members on the database<br>\n";
	$file.="<b>Search criteria:</b> ".(isset($criteria) ? $criteria : '')."<br>\n"; 
	$file.="The attached .xls can be opened in Microsoft Excel<br>\n"; 
	$file.="<table>\n";
	$header = "<tr>
	<td class=list_small><b>Title</b></td>
	<td class=list_small><b>Forename</b></td>
	<td class=list_small><b>Surname</b></td>
	<td class=list_small><b>Email</b></td>
	<td class=list_small><b>SecondTitle</b></td>
	<td class=list_small><b>SecondForename</b></td>
	<td class=list_small><b>SecondSurname</b></td>
	<td class=list_small><b>SecondEmail</b></td>
	<td class=list_small><b>MembershipNo</b></td>
	<td class=list_small><b>Country</b></td>
	<td class=list_small><b>PostZone</b></td>
	<td class=list_small><b>MemTypeCode</b></td>
	<td class=list_small><b>Situation</b></td>
	<td class=list_small><b>Year Joined</b></td>
	<td class=list_small><b>Ship Name</b></td>
	<td class=list_small><b>Ship Type</b></td>
	<td class=list_small><b>Ship Year</b></td>
	<td class=list_small><b>Ship Length</b></td>
	<td class=list_small><b>Ship Beam</b></td>
	</tr>\n";
	//$filedata = "Organisation\",\"AdminTitle\",\"AdminInitial\",\"AdminForename\",\"AdminSurname\",\"AdminJobTitle\",\"AdminEmail\",\"AdminTel\",\"SeniorTitle\",\"SeniorInitial\",\"SeniorForename\",\"SeniorSurname\",\"SeniorJobTitle\",\"SeniorEmail\",\"SeniorTel\",\"Address1\",\"Address2\",\"Address3\",\"Town\",\"Postcode\"\n";
	$file.=$header;
	$line="even";
	//$headers = "From: ".$admincontact." <".$adminemail.">\n";
	$thismemberid=0;
	$emailno=1;
	while($emailno<=$maxmembers){
		$thismemberid=$members[$emailno];
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn($memtable))
			->where($db->qn('ID').' = '.$db->q($thismemberid));
		$memberinfo = $db->setQuery($query)->loadAssocList();
		if (!$memberinfo) {
			break;
		}
		$row = reset($memberinfo);
		//Get details
		if ($line == "even"){
			//$lineformat="<td bgcolor=\"#FFFFCC\"><font face=verdana size=1>";
			$line="odd";
		}else{
			//$lineformat="<td bgcolor=\"#FFFFFF\"><font face=verdana size=1>";
			$line="even";
		}
		//$filedata.=$row["Company"]."\",\"".$row["Title"]."\",\"".$row["Initial"]."\",\"".$row["Forename"]."\",\"".$row["Surname"]."\",\"".$row["JobTitle"]."\",\"".$row["Email"]."\",\"".$row["Tel"]."\",\"".$row["Title2"]."\",\"".$row["Initial2"]."\",\"".$row["Forename2"]."\",\"".$row["Surname2"]."\",\"".$row["JobTitle2"]."\",\"".$row["Email2"]."\",\"".$row["Tel2"]."\",\"".$row["Add1"]."\",\"".$row["Add2"]."\",\"".$row["Add3"]."\",\"".$row["Town"]."\",\"".$row["PostCode"]."\"n";
		//$filedata.=$row["Company"]."\",\"".$row["Title"]."\",\"".$row["Initial"]."\",\"".$row["Forename"]."\",\"".$row["Surname"]."\",\"".$row["JobTitle"]."\",\"".$row["Email"]."\",\"".$row["Tel"]."\",\"".$row["Title2"]."\",\"".$row["Initial2"]."\",\"".$row["Forename2"]."\",\"".$row["Surname2"]."\",\"".$row["JobTitle2"]."\",\"".$row["Email2"]."\",\"".$row["Tel2"]."\",\"".$row["Add1"]."\",\"".$row["Add2"]."\",\"".$row["Add3"]."\",\"".$row["Town"]."\",\"".$row["PostCode"]."\"n";
		
		//$file.="<tr>".$lineformat.$row["Company"]."</td>".$lineformat.$row["Title"]."</td>".$lineformat.$row["Initial"]."</td>".$lineformat.$row["Forename"]."</td>".$lineformat.$row["Surname"]."</td>".$lineformat.$row["JobTitle"]."</td>".$lineformat.$row["Email"]."</td>".$lineformat.$row["Tel"]."</td>".$lineformat.$row["Title2"]."</td>".$lineformat.$row["Initial2"]."</td>".$lineformat.$row["Forename2"]."</td>".$lineformat.$row["Surname2"]."</td>".$lineformat.$row["JobTitle2"]."</td>".$lineformat.$row["Email2"]."</td>".$lineformat.$row["Tel2"]."</td>".$lineformat.$row["Add1"]."</td>".$lineformat.$row["Add2"]."</td>".$lineformat.$row["Add3"]."</td>".$lineformat.$row["Town"]."</td>".$lineformat.$row["PostCode"]."</td></tr>\n";
		$file.="<tr><td class=list_small>".$row["Title"];
		$file.="</td><td class=list_small>".$row["FirstName"];
		$file.="</td><td class=list_small>".$row["LastName"];
		$file.="</td><td class=list_small>".$row["Email"];
		$file.="</td><td class=list_small>".$row["Title2"];
		$file.="</td><td class=list_small>".$row["FirstName2"];
		$file.="</td><td class=list_small>".$row["LastName2"];
		$file.="</td><td class=list_small>".$row["Email2"];
		$file.="</td><td class=list_small>'".$row["MembershipNo"];
		$file.="</td><td class=list_small>".$row["Country"];
		$file.="</td><td class=list_small>".$row["PostZone"];
		$file.="</td><td class=list_small>".$row["MemTypeCode"];
		$file.="</td><td class=list_small>".$row["Situation"];
		$file.="</td><td class=list_small>".$row["DateJoined"];
		$file.="</td><td class=list_small>".$row["ShipName"];
		$file.="</td><td class=list_small>".$row["ShipClass"];
		$file.="</td><td class=list_small>".$row["ShipYear"];
		$file.="</td><td class=list_small>".$row["ShipLength"];
		$file.="</td><td class=list_small>".$row["ShipBeam"];
		$file.="</td></tr>\n";
		
		$emailno+=1;
		//if(substr($string,-2,2)=="00"){
			//echo($emailno."<br>");
			//ob_flush();
			//flush();
		//}
	} 
	$file.="</table></html>\n"; 
	
	$filename="member_mailmerge_survey_".date("YmdHi",time()).".xls";
	//Remove colour bands from Excel file bgcolor='#FFFFCC' to bgcolor='#FFFFFF'
	$excelfile=str_replace("FFFFCC","#FFFFFF",$file);
	 header("Content-type: application/x-msexcel");
     header("Pragma: ");
    header("Cache-Control: ");
        # replace excelfile.xls with whatever you want the filename to default to
     header("Content-Disposition: attachment; filename=$filename");
	 echo($excelfile);
	 //echo($maillist);
	 
	exit();
}
?>