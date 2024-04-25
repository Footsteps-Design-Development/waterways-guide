<?php define( '_JEXEC', 1 );
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', strstr(__DIR__, 'public_html', true).'public_html');
define('JPATH_COMPONENT', JPATH_BASE .DS.'components'.DS.'com_waterways_guide');
require_once(JPATH_BASE .DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE .DS.'includes'.DS.'framework.php');
require_once(JPATH_COMPONENT .DS.'commonV3.php');

use Joomla\CMS\Factory;

$db = Factory::getDbo();
?><html>
<head>
<title>Change log</title>
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
<div class="pop_page_title"><h2>Change Log</h2></div>
<div class="pop_page_buttons"><a href="javascript:closeme()"><img src="../../../images/close.gif" width="18" height="18" alt="Close this window and return to search entry" border="0"><br>
        </a><a href="javascript:printWindow()"><img src="../../../images/print.gif" width="18" height="18" alt="Print this page" border="0"></a></div>

<table border='0' cellspacing='2' cellpadding='3' width='100%'>
<?php
$thisdate=date("d M Y");

getpost_ifset(array('memberid','table','wheresql','maillist','sort','status'));
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblChangeLog'))
	->where($db->qn('MemberID').' = '.$db->q($memberid))
	->order($db->qn('LogID').' DESC');
try {
	$result = $db->setQuery($query)->loadAssocList();
} catch(Exception $e) {
	echo("Can't find log info");
	exit();
}
$num_rows = count($result);
if($num_rows){
	echo("<tr><td><b>Subject</b></td>\n");
	echo("<td><b>Change details</b></td>\n");
	echo("<td><b>Date</b></td></tr>\n");
	//echo("<tr><td bgcolor=\"".$bgc."\" colspan=4><hr></td></tr>\n");
	$thisrow = "odd";
	foreach($result as $row) {
		$changedatedisplay=date('d M Y', strtotime($row["ChangeDate"]));
		if($thisrow=="odd"){
			$rowclass="table_stripe_even";
			$thisrow="even";
		}else{
			$rowclass="table_stripe_odd";		
			$thisrow="odd";
		}	

		echo("<tr><td width=90 valign=top class=".$rowclass.">".$row["Subject"]."</td>\n");
		echo("<td valign=top class=".$rowclass.">".$row["ChangeDesc"]."</td>\n");
		echo("<td valign=top width=90 class=".$rowclass.">".$changedatedisplay."</td></tr>\n");
	}
}else{
	echo("There are no recorded changes\n");
}

?>

</table>
</body>

</html>
