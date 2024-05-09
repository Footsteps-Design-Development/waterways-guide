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
	$file.="<b>Search criteria:</b> ".(isset($criteria) ? $criteria : '')."<br><br>\n"; 
	$file.="<table>\n";
	$header = "<tr>
	<td class=list_small><b>Title</b></td>
	<td class=list_small><b>Forename</b></td>
	<td class=list_small><b>SecondTitle</b></td>
	<td class=list_small><b>SecondForename</b></td>
	<td class=list_small><b>MembershipNo</b></td>
	<td class=list_small><b>PostZone</b></td>
	<td class=list_small><b>MemTypeCode</b></td>
	<td class=list_small><b>Situation</b></td>
	<td class=list_small><b>Joined</b></td>
	<td class=list_small><b>Left</b></td>
	<td class=list_small><b>Years</b></td>
	<td class=list_small><b>Ship</b></td>
	<td class=list_small><b>Ship Year</b></td>
	<td class=list_small><b>Ship Length</b></td>
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
		
		if($row["ShipName"]){
			$Ship="Yes";
		}else{
			$Ship="";
		}
		if($row["ShipYear"]){
			$ShipYear=$row["ShipYear"];
		}else{
			$ShipYear="";
		}
		if($row["ShipLength"]){
			$ShipLength=$row["ShipLength"];
		}else{
			$ShipLength="";
		}
		
		$file.="<tr><td class=list_small>".$row["Title"];
		$file.="</td><td class=list_small>".$row["FirstName"];
		$file.="</td><td class=list_small>".$row["Title2"];
		$file.="</td><td class=list_small>".$row["FirstName2"];
		$file.="</td><td class=list_small>'".$row["MembershipNo"];
		$file.="</td><td class=list_small>".$row["PostZone"];
		$file.="</td><td class=list_small>".$row["MemTypeCode"];
		$file.="</td><td class=list_small>".$row["Situation"];
		
		$YearJoined= date('Y', strtotime($row["DateJoined"]));
		if($row["DateCeased"]){
			$YearCeased= date('Y', strtotime($row["DateCeased"]));
			$YearsMember=$YearCeased-$YearJoined;
		}else{
			$YearsMember=date('Y',time())-$YearJoined;
			$YearCeased="";
		}
		
		$file.="</td><td class=list_small>".$YearJoined;
		$file.="</td><td class=list_small>".$YearCeased;
		$file.="</td><td class=list_small>".$YearsMember;
		$file.="</td><td class=list_small>".$Ship;
		$file.="</td><td class=list_small>".$ShipYear;
		$file.="</td><td class=list_small>".$ShipLength;
		$file.="</td></tr>\n";
		
		$emailno+=1;
		//if(substr($string,-2,2)=="00"){
			//echo($emailno."<br>");
			//ob_flush();
			//flush();
		//}
	} 
	$file.="</table></html>\n"; 
	
	$filename="member_mailmerge_survey_anonymised".date("YmdHi",time()).".xls";
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