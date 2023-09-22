<?php define( '_JEXEC', 1 );
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', strstr(__DIR__, 'public_html', true).'public_html');
define('JPATH_COMPONENT', JPATH_BASE .DS.'components'.DS.'com_membership');
require_once(JPATH_BASE .DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE .DS.'includes'.DS.'framework.php');
require_once(JPATH_COMPONENT .DS.'commonV3.php');
getpost_ifset(array('author','groupid'));

use Joomla\CMS\Factory;

$db = Factory::getDbo();
?>
<html><HEAD>
<SCRIPT LANGUAGE="JavaScript">
<!--
function closeme() {
window.close(self);
}
function printWindow(){
   bV = parseInt(navigator.appVersion)
   if (bV >= 4) window.print()
}


//-->
</script>
<title>Groups listing</title>
<link rel="stylesheet" href="../../../style.css" type="text/css">
</HEAD>
<body>
<div class="pop_page_title"><h2>Groups listing</h2></div>
<div class="pop_page_buttons"><a href="javascript:closeme()"><img src="../../../images/close.gif" width="18" height="18" alt="Close this window and return to search entry" border="0"><br>
        </a><a href="javascript:printWindow()"><img src="../../../images/print.gif" width="18" height="18" alt="Print this page" border="0"></a></div>

<form name="form">
<a name="top"></a>
<table border="0" cellpadding="1" width="100%">

        <?php
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblMembers'))
	->where($db->qn('Groups')." LIKE '%|".$groupid."|%'")
	->order($db->qn('LastName'));
$result = $db->setQuery($query)->loadAssocList();
  		if (!$result) {
    	echo("<P>Error performing query: ".$query->__toString()."</P>");
	    exit();
		}
$num_rows = count($result);
$thisdate=date("d M Y");
$Title="Groups listing - $thisdate" ;
$maxrecords = 10000;

echo("<tr><td colspan=5><b>" . $Title . "</b><br><br>".$num_rows." match(es) found.</td></tr>");
//echo("<tr><td colspan=5><font face=verdana size=2>Search criteria: $criteria</font></td></tr>"); 
if (!$num_rows) {
    echo("<tr><td colspan=5><b>Sorry - there are currently no members in this Group</b></td></tr>");
	exit();
}

$datenow = time();

$header = "<tr><td><b>Member</b></font></td><td width='100'><b>Contact</b></td><td><b>email</b></td><td><font face=verdana size=2></td></tr>";
echo($header);
$line="even";
foreach($result as $row) {
	if ($line == "even"){
		$lineformat="<td class=table_stripe_odd>";
		$line="odd";
	}else{
		$lineformat="<td class=table_stripe_even>";
		$line="even";
	}
	$contactname=$row["FirstName"]." ".$row["LastName"];
	$membershipno=$row["MembershipNo"];
	echo("<tr>" . $lineformat .  $membershipno . "</td>" . $lineformat . $contactname. "</td>" . $lineformat . $row["Login"] . "</td>". $lineformat ."</td><td></td></tr>");	

}

if ($num_rows > 8) {
    echo("<tr><td colspan='5'><a href='#top'>Back to the top</a><br></td></tr>");
}
	
	
?>

</table>
</form>
</body></html>
