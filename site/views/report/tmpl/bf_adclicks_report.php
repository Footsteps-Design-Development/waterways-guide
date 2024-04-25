<?php
//load Joomla helpers for emailsending
define( '_JEXEC', 1 );
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', strstr(__DIR__, 'public_html', true).'public_html');
//define('JPATH_BASE', substr(__FILE__,0,strrpos(__FILE__, DS."components")));
define('JPATH_COMPONENT', JPATH_BASE .DS.'components'.DS.'com_waterways_guide');
require_once(JPATH_BASE .DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE .DS.'includes'.DS.'framework.php');
require_once(JPATH_COMPONENT .DS.'commonV3.php');

getpost_ifset(array('url','startdate','enddate'));
if(!$startdate){
	$startdate='01-11-2017';
}
if(!$enddate){
	$enddate=date("d-m-Y");
}
$starttimestamp = strtotime($startdate);
$endtimestamp = strtotime($enddate);
$totalclicks=0;

if(!$url || $url=="ALL"){
	$where=" ";

}else{
	$where=" WHERE url='".$url."' ";

}


	//make the header

	$list="<table class=table_forum_subscribers><tr><td align='right'><b>Issue</b></td><td align='right'><b>Page</b></td><td align='left'><b>URL</b></td><td align='left'><b>Clicks</b></td>\n";
	
	$list.="</tr>\n";
	
	
	//get users subscribed
	$query = $db->getQuery(true)
		->select('COUNT('.$db->qn('url').') AS '.$db->qn('clicks_total'))
		->select($db->qn(['issue', 'page', 'url']))
		->from($db->qn('tblBlueFlagAdClicks'))
		->where($where)
		->group($db->qn(['url', 'issue']))
		->order($db->qn('issue').' DESC')
		->order($db->qn('clicks_total').' DESC');
	$clicks = $db->setQuery($query)->loadAssocList();
	$line = "even";
	$total_clicks=0;
	foreach($clicks as $row) {
		$issue = $row["issue"];
		//$category_id = $row["category_id"];
		//$subscribed = $row["subscribed"];
		$page = $row["page"];
		$url = $row["url"];
		$clicks_total = $row["clicks_total"];
		if ($line == "even"){
			$lineformat="class='table_stripe_odd'";
			$line="odd";
		}else{
			$lineformat="class='table_stripe_even'";
			$line="even";
		}
		$list.="<tr><td $lineformat align='right'>$issue</td><td $lineformat align='right'>$page</td><td $lineformat align='left'>$url</td><td $lineformat align='left'>$clicks_total</td>\n";
		
	
		$list.="</tr>\n";
		$total_clicks+=$clicks_total;
		

	}
	$list.="</table>\n";
$list.="<br>Total clicks to date: ".$total_clicks;	



?>
<html>
<head>
<title>eBlue Flag click report</title>
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
<style>
.table_forum_subscribers{
	font-size: 100%;
	margin: 1px;
	padding: 3px;
	border-collapse:collapse;
}

.table_forum_subscribers,th, td{
	border: 1px solid #666666;
	padding: 4px;
}
</style>



  <h2>eBF click report</h2>



    
      
  <?php
	echo ($list);

?>
   



</body>

</html>