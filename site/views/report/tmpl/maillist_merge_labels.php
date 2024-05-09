<?php
$login_email= $_SESSION["login_email"];
$login_name= $_SESSION["login_name"];
$wheresql = stripslashes($_SESSION["wheresql"]);
$sort = $_SESSION["sort"];
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
	//exit();
}
$num_records = count($records);
$thisdate=date("d M Y");
$Title="Target listing - $thisdate" ;
$maxrecords = 10000;
$members = explode ("_", $maillist);
$maxmembers=sizeof ($members)-2;
//records found so ok to create file
$status="submitted";
if ($status=="submitted"){
	//do the merge 
	$file="<html>";
	$file.="<br><b>$Title</b> <br>$maxmembers have been selected for the merge list from $num_records members on the database<br>\n";
	$file.="<b>Search criteria:</b> ".(isset($criteria) ? $criteria : '')."<br>\n"; 
	$file.="The attached .xls can be opened in Microsoft Excel<br>\n"; 
	$file.="<table>\n";
	$header = "<tr>
	<td class=list_small><b>MembershipNo</b></td>
	<td class=list_small><b>DateRenewal</b></td>
	<td class=list_small><b>Type</b></td>
	<td class=list_small><b>Status</b></td>
	<td class=list_small><b>Label</b></td>
	<td class=list_small><b>Greeting</b></td>
	<td class=list_small><b>AddressName</b></td>
	<td class=list_small><b>Address1</b></td>
	<td class=list_small><b>Address2</b></td>
	<td class=list_small><b>Address3</b></td>
	<td class=list_small><b>Address4</b></td>
	<td class=list_small><b>Postcode</b></td>
	<td class=list_small><b>Country</b></td>
	<td class=list_small><b>MailZone</b></td>
	</tr>\n";
	//$filedata = "Organisation\",\"AdminTitle\",\"AdminInitial\",\"AdminForename\",\"AdminSurname\",\"AdminJobTitle\",\"AdminEmail\",\"AdminTel\",\"SeniorTitle\",\"SeniorInitial\",\"SeniorForename\",\"SeniorSurname\",\"SeniorJobTitle\",\"SeniorEmail\",\"SeniorTel\",\"Address1\",\"Address2\",\"Address3\",\"Town\",\"Postcode\"\n";
	$file.=$header;
	$line="even";
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
			// break;
		}
		$row = reset($memberinfo);
		//Get details
		
		$label="";
		$TITLE=stripslashes($row["Title"]);
		$FIRSTNAME=stripslashes($row["FirstName"]);
		$LASTNAME=stripslashes($row["LastName"]);
		$TITLE2=stripslashes($row["Title2"]);
		$FIRSTNAME2=stripslashes($row["FirstName2"]);
		$LASTNAME2=stripslashes($row["LastName2"]);
		
		
		$secs_now = time();
		$secsinayear=31536000;
		$my_secs=strtotime($row['DatePaid']);
		$DateRenew=date("d F, Y",$my_secs+$secsinayear); 
	
		//work out greeting whether one or two members
		if($LASTNAME2){
			if($LASTNAME==$LASTNAME2){
				//same surname
				$AddressName=$TITLE." ".$FIRSTNAME." and ".$TITLE2." ".$FIRSTNAME2." ".$LASTNAME;
				$Greeting=$TITLE." and ".$TITLE2." ".$LASTNAME;
		
			}else{
				//different surnames
				$Greeting=$TITLE." ".$LASTNAME." and ".$TITLE2." ".$LASTNAME2;
				$AddressName=$TITLE." ".$FIRSTNAME." ".$LASTNAME." and ".$TITLE2." ".$FIRSTNAME2." ".$LASTNAME2;
		
			}
		}else{
			//single member
			$Greeting=$TITLE." ".$LASTNAME;
			$AddressName=$TITLE." ".$FIRSTNAME." ".$LASTNAME;
					
		}
		
		
		
		
		if($AddressName){$label="".$AddressName;};
		if($row["Address1"]){$label.="\r\n".stripslashes($row["Address1"]);}
		if($row["Address2"]){$label.="\r\n".stripslashes($row["Address2"]);}
		if($row["Address3"]){$label.="\r\n".stripslashes($row["Address3"]);}
		if($row["Address4"]){$label.="\r\n".stripslashes($row["Address4"]);}
		if($row["PostCode"]){$label.="\r\n".stripslashes($row["PostCode"]);}
		if($row["Country"]){$label.="\r\n".stripslashes($row["Country"]);}
		//$file.="</td><td class=list_small>".$label;
		$file.="<tr></td><td class=list_small>\"".$row["MembershipNo"]."\"";
		$file.="</td><td class=list_small>'".$DateRenew."'";		
		$file.="</td><td class=list_small>".stripslashes($row["MemTypeCode"]);
		$file.="</td><td class=list_small>".stripslashes($row["MemStatus"]);
		$file.="</td><td class=list_small>".$label;
		$file.="</td><td class=list_small>".$Greeting;
		$file.="</td><td class=list_small>".$AddressName;
		$file.="</td><td class=list_small>".stripslashes($row["Address1"]);
		$file.="</td><td class=list_small>".stripslashes($row["Address2"]);
		$file.="</td><td class=list_small>".stripslashes($row["Address3"]);
		$file.="</td><td class=list_small>".stripslashes($row["Address4"]);
		$file.="</td><td class=list_small>\"".stripslashes($row["PostCode"])."\"";
		$file.="</td><td class=list_small>".stripslashes($row["Country"]);
		$file.="</td><td class=list_small>".stripslashes($row["PostZone"]);
		$file.="</td></tr>\n";
		
		$emailno+=1;
	} 
	$file.="</table></html>\n"; 
	
	$filename="member_mailmerge_".date("YmdHi",time()).".xls";
	//Remove colour bands from Excel file bgcolor='#FFFFCC' to bgcolor='#FFFFFF'
	$excelfile=str_replace("FFFFCC","#FFFFFF",$file);
	header("Content-type: application/x-msexcel");
	header("Pragma: ");
    header("Cache-Control: ");
        # replace excelfile.xls with whatever you want the filename to  default to
    header("Content-Disposition: attachment; filename=$filename");
	echo($excelfile);
	exit();
}
?>
