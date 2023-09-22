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
define('JPATH_COMPONENT', JPATH_BASE .DS.'components'.DS.'com_membership');
require_once(JPATH_BASE .DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE .DS.'includes'.DS.'framework.php');
require_once(JPATH_COMPONENT .DS.'commonV3.php');
use Joomla\CMS\Factory;
$db = Factory::getDbo();
getpost_ifset(array('criteria','table','wheresql','maillist','sort'));
?>
<html>
<head>
<title>Report</title>
<SCRIPT LANGUAGE="JavaScript">
function closeme() {
window.close(self);
}
function printWindow(){
   bV = parseInt(navigator.appVersion)
   if (bV >= 4) window.print()
}
</script>
</head>
<link href="../../../style.css" rel="stylesheet" type="text/css">
<body bgcolor="#FFFFFF">
<form name="form" method="post" action="maillist_merge.php">
<div class="pop_page_title"><h2>Membership report</h2></div>
<div class="pop_page_buttons"><a href="javascript:closeme()"><img src="../../../images/close.gif" width="18" height="18" alt="Close this window and return to search entry" border="0"><br>
        </a><a href="javascript:printWindow()"><img src="../../../images/print.gif" width="18" height="18" alt="Print this page" border="0"></a></div>
<table border="0" cellpadding="2" bgcolor="#FFFFFF" width="100%">
  <tr>
    <td colspan='2'> 
        <table border="0" cellpadding="2" cellspacing="0" width="550"> 
		</table>
            
        
</td>
    </tr>
<tr><td colspan="2"> 
<?php
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
	echo("<P>Error finding members</P>");
	//exit();
}
$num_records = count($records);
$thisdate=date("d M Y");
$Title="Target listing - $thisdate" ;
$maxrecords = 10000;
$members = explode ("_", $maillist);
$maxmembers=sizeof ($members)-2;
//save variables for later
echo("<input type='hidden' name='wheresql' value='".$wheresql."'>\n");
echo("<input type='hidden' name='sort' value='".$sort."'>\n");
echo("<input type='hidden' name='criteria' value='".(isset($criteria) ? $criteria : '')."'>\n");
echo("<input type='hidden' name='maillist' value='".$maillist."'>\n");
//records found so ok to list
$message="".$maxmembers." have been selected for the report from ".$num_records." members on the database<br>\n";
$message.="<b>Search criteria:</b> ".(isset($criteria) ? $criteria : '')."<br>\n"; 
$message.="<table>\n";
$header = "<tr>
<td class=list_small><b>Name</b></td>
<td class=list_small><b>Address</b></td>
<td class=list_small><b>Zone</b></td>
<td class=list_small><b>email</b></td>
<td class=list_small><b>Tel</b></td>
<td class=list_small><b>ShipName</b></td>
<td class=list_small><b>MemNo</b></td>
<td class=list_small><b>MemCode</b></td>
<td class=list_small><b>Status</b></td>
</tr>\n";
$message.=$header;
$line="even";
$thismemberid=0;
$emailno=1;
while($emailno<=$maxmembers){
	$thismemberid=$members[$emailno];
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn($memtable))
		->where($db->qn('ID').' = '.$thismemberid);
	$memberinfo = $db->setQuery($query)->loadAssocList();
	if (!$memberinfo) {
		break;
	}
	$row = reset($memberinfo);
	$name="";
	$name=$row["FirstName"];
	if($row["FirstName"] && $name){
		$name.=" ".$row["LastName"];
	}else{
		$name=$row["LastName"];
	}
	//Get Address
	$address="";
	$Address1=$row["Address1"];
	if($Address1){
		$address=$Address1;
	}
	$Address2=$row["Address2"];
	if($Address2){
		$address.=", ".$Address2;
	}
	$Address3=$row["Address3"];
	if($Address3){
		$address.=", ".$Address3;
	}
	$Address4=$row["Address4"];
	if($Address4){
		$address.=", ".$Address4;
	}
	$PostCode=$row["PostCode"];
	if($PostCode){
		$address.=", ".$PostCode;
	}
	$Country=$row["Country"];
	if($Country){
		$address.=", ".$Country;
	}		
	if ($line == "even"){
		$rowclass=" class=table_stripe_odd";
		$line="odd";
	}else{
		$rowclass=" class=table_stripe_even";
		$line="even";
	}
	$message.="<tr><td".$rowclass.">".$name;
	$message.="</td><td".$rowclass.">".$address;
	$message.="</td><td".$rowclass.">".$row["PostZone"];
	$message.="</td><td".$rowclass.">".$row["Email"];
	$message.="</td><td".$rowclass.">".$row["Telephone1"];
	$message.="</td><td".$rowclass.">".$row["ShipName"];
	$message.="</td><td".$rowclass.">".$row["MembershipNo"];
	$message.="</td><td".$rowclass.">".$row["MemTypeCode"];
	$message.="</td><td".$rowclass.">".$row["MemStatus"];
	$message.="</td><td".$rowclass."></tr>\n";
	$emailno+=1;
} 
$message.="</table>\n"; 
echo($message); 
?> 
      </td>
    </tr>
  </table>
</form>
</body>
</html>
