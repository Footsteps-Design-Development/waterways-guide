<?php


/*
To do ................................

*/




//load Joomla helpers for emailsending
define( '_JEXEC', 1 );
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', strstr(__DIR__, 'public_html', true).'public_html');
define('JPATH_COMPONENT', JPATH_BASE .DS.'components'.DS.'com_waterways_guide');
require_once(JPATH_BASE .DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE .DS.'includes'.DS.'framework.php');
require_once(JPATH_BASE .DS.'libraries/joomla/user/helper.php');
require_once(JPATH_BASE .DS.'libraries/joomla/factory.php' );
require_once(JPATH_COMPONENT .DS.'commonV3.php');

use Joomla\CMS\Factory;

$db =Factory::getDBO();



//simulate or live
$live=1;
$livemail=1;
$livemailadmin=1;

$htmlheader.="<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n<html>\n<head>\n";

$htmlheader.="<style type=\"text/css\"><!--\n.content {\n	font-family: Arial, Helvetica, sans-serif;\n	font-size: 90%;\n	font-style: normal;\n	font-weight: normal;\n}\n-->\n</style>\n";

$htmlheader.="</head>\n";
$htmlheader.="<body>\n";

function get_parameter($ParameterName){
	$query = $db->getQuery(true)
		->select($db->qn('ParameterValue'))
		->from($db->qn('tblParameters'))
		->where($db->qn('ParameterName').' = '.$db->q($ParameterName));
	return $db->setQuery($query)->loadResult();
}
 
//reset reporting values  
$classifiedpostings=0;
$classifiedalerts=0;
$classifiedrenewalreminder=0;
$classifiedexpired=0;
$num_members=0;
$newjoinernotpaid=0;
$overduearchived=0;
$overduefinalreminder=0;
$reminderch =0;
$remindercc=0;
$reminderso=0;
$reminderdd=0;
$terminatedarchived=0;
$live_members=0;
$joiner_ch=0;
$joiner_cc=0;
$joiner_dd=0;

$datenow = date("Y-m-d");



//$listing.="</body>\n";
//$listing.="</html>\n";



//update stats added 20090206

$MemTot=0;
$MemLive=0;
$MemNewToday=0;
$MemTermToday=0;
$MemDD=0;
$MemBO=0;
$MemCH=0;
$MemCC=0;
$MemFOC=0;
$MemOrdinary=0;
$MemFamily=0;
$MemHonorary=0;
$MemPress=0;
$MemVoucher=0;
$MemUK=0;
$MemEU=0;
$MemZ1=0;
$MemZ2=0;
$MemOwner=0;
$MemDreamer=0;
$MemCommercial=0;
$MemSitUnknown=0;
$MemTerminated=0;
$MemNew=0;
$MemCruiseGB=0;
$MemCruiseBE=0;
$MemCruiseNL=0;
$MemCruiseFR=0;
$MemCruiseOther=0;
$MonthMemNew=0;
$MonthMemTerminated=0;
$YearMemNew=0;
$YearMemTerminated=0;
$LastYearMemNew=0;
$LastYearMemTerminated=0;

//1 Applied awaiting payment
//2 Paid up
//3 Renewal overdue
//4 Gone away
//5 Terminated
//6 Complimentary

$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblMembers'))
	->where($db->qn('DateJoined').' > '.$db->q('0000-00-00 00:00:00'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('MemStatus')." != ''")
	->where($db->qn('DateCeased').' IS NULL');
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$MemLive=$num_rows;
}
$query = $db->getQuery(true)
	->select('COUNT(*) AS '.$db->qn('NumberOfRecords').', SUM('.$db->qn('BasicSub').') AS '.$db->qn('TotalSubs'))
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('PaymentMethod')." = 'DD'");
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	
	$row = reset($mymembers);
	$TotalSubsDD=$row["TotalSubs"];
	$MemDD=$row["NumberOfRecords"];
	
}
$query = $db->getQuery(true)
	->select('COUNT(*) AS '.$db->qn('NumberOfRecords').', SUM('.$db->qn('BasicSub').') AS '.$db->qn('TotalSubs'))
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('PaymentMethod')." = 'SO'");
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$row = reset($mymembers);
	$TotalSubsBO=$row["TotalSubs"];
	$MemBO=$row["NumberOfRecords"];
}
$query = $db->getQuery(true)
	->select('COUNT(*) AS '.$db->qn('NumberOfRecords').', SUM('.$db->qn('BasicSub').') AS '.$db->qn('TotalSubs'))
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('PaymentMethod')." = 'CH'");
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$row = reset($mymembers);
	$TotalSubsCH=$row["TotalSubs"];
	$MemCH=$row["NumberOfRecords"];
}
$query = $db->getQuery(true)
	->select('COUNT(*) AS '.$db->qn('NumberOfRecords').', SUM('.$db->qn('BasicSub').') AS '.$db->qn('TotalSubs'))
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('PaymentMethod')." = 'CC'");
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$row = reset($mymembers);
	$TotalSubsCC=$row["TotalSubs"];
	$MemCC=$row["NumberOfRecords"];
}
$query = $db->getQuery(true)
	->select('COUNT(*) AS '.$db->qn('NumberOfRecords').', SUM('.$db->qn('BasicSub').') AS '.$db->qn('TotalSubs'))
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('PaymentMethod')." = 'FOC'");
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$row = reset($mymembers);
	$TotalSubsFOC=$row["TotalSubs"];
	$MemFOC=$row["NumberOfRecords"];
}
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('MemTypeCode').' IN (1,3)');
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$MemOrdinary=$num_rows;
}
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('MemTypeCode').' IN (2,4)');
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$MemFamily=$num_rows;
}
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('MemTypeCode').' = 5');
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$MemHonorary=$num_rows;
}
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('MemTypeCode').' = 6');
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$MemPress=$num_rows;
}
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('MemTypeCode').' = 7');
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$MemVoucher=$num_rows;
}
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblMembers'));
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$MemTot=$num_rows;
}
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('PostZone')." = 'UK'");
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$MemUK=$num_rows;
}
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('PostZone')." = 'EU'");
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$MemEU=$num_rows;
}
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('PostZone')." = 'Z1'");
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$MemZ1=$num_rows;
}
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('PostZone')." = 'Z2'");
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$MemZ2=$num_rows;
}
//Cruising country
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('CountryCodeCruising')." = 'GB'")
	->where('('.$db->qn('Situation').' = 4 OR '.$db->qn('Situation').' = 5)');
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$MemCruiseGB=$num_rows;
}
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('CountryCodeCruising')." = 'NL'")
	->where('('.$db->qn('Situation').' = 4 OR '.$db->qn('Situation').' = 5)');
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$MemCruiseNL=$num_rows;
}
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('CountryCodeCruising')." = 'BE'")
	->where('('.$db->qn('Situation').' = 4 OR '.$db->qn('Situation').' = 5)');
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$MemCruiseBE=$num_rows;
}
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('CountryCodeCruising')." = 'FR'")
	->where('('.$db->qn('Situation').' = 4 OR '.$db->qn('Situation').' = 5)');
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$MemCruiseFR=$num_rows;
}
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where('('.$db->qn('Situation').' = 4 OR '.$db->qn('Situation').' = 5)');
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$MemOwner=$num_rows;
}
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where('('.$db->qn('Situation').' = 1 OR '.$db->qn('Situation').' = 2)');
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$MemDreamer=$num_rows;
}
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where($db->qn('Situation').' = 3');
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$MemCommercial=$num_rows;
}
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where('('.$db->qn('Situation')." = '' OR ".$db->qn('Situation').' = 0)');
$mymembers = $db->setQuery($query)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$MemSitUnknown=$num_rows;
}

//calc monthly todate figures (20161020)
$ThisMonth=date("n");
$ThisYear=date("Y");
$ThisDay=date("j");
$query = $db->getQuery(true)
	->select($db->qn('ID'))
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' != 5')
	->where($db->qn('MemStatus').' != 1')
	->where('DAY('.$db->qn('DateJoined').') = '.$db->q($ThisDay))
	->where('MONTH('.$db->qn('DateJoined').') = '.$db->q($ThisMonth))
	->where('YEAR('.$db->qn('DateJoined').') = '.$db->q($ThisYear));
$result = $db->setQuery($query)->loadAssocList();
$num_rows = count($result);
if($num_rows>0){
	$MemNewToday=$num_rows;
}
$query = $db->getQuery(true)
	->select($db->qn('ID'))
	->from($db->qn('tblMembers'))
	->where($db->qn('MemStatus').' = 5')
	->where('DAY('.$db->qn('DateCeased').') = '.$db->q($ThisDay))
	->where('MONTH('.$db->qn('DateCeased').') = '.$db->q($ThisMonth))
	->where('YEAR('.$db->qn('DateCeased').') = '.$db->q($ThisYear));
$result = $db->setQuery($query)->loadAssocList();
$num_rows = count($result);
if($num_rows>0){
	$MemTermToday=$num_rows;
}
$query = $db->getQuery(true)
	->select($db->qn('ID'))
	->from($db->qn('tblMembers'))
	->where('MONTH('.$db->qn('DateJoined').') = '.$db->q($ThisMonth))
	->where('YEAR('.$db->qn('DateJoined').') = '.$db->q($ThisYear));
$result = $db->setQuery($query)->loadAssocList();
$num_rows = count($result);
if($num_rows>0){
	$MonthMemNew=$num_rows;
}
$query = $db->getQuery(true)
	->select($db->qn('ID'))
	->from($db->qn('tblMembers'))
	->where('MONTH('.$db->qn('DateCeased').') = '.$db->q($ThisMonth))
	->where('YEAR('.$db->qn('DateCeased').') = '.$db->q($ThisYear));
$result = $db->setQuery($query)->loadAssocList();
$num_rows = count($result);
if($num_rows>0){
	$MonthMemTerminated=$num_rows;
}
$query = $db->getQuery(true)
	->select($db->qn('ID'))
	->from($db->qn('tblMembers'))
	->where('YEAR('.$db->qn('DateJoined').') = '.$db->q($ThisYear));
$result = $db->setQuery($query)->loadAssocList();
$num_rows = count($result);
if($num_rows>0){
	$YearMemNew=$num_rows;
}
$query = $db->getQuery(true)
	->select($db->qn('ID'))
	->from($db->qn('tblMembers'))
	->where('YEAR('.$db->qn('DateCeased').') = '.$db->q($ThisYear));
$result = $db->setQuery($query)->loadAssocList();
$num_rows = count($result);
if($num_rows>0){
	$YearMemTerminated=$num_rows;
}
$LastYear=date("Y")-1;
$query = $db->getQuery(true)
	->select($db->qn('ID'))
	->from($db->qn('tblMembers'))
	->where('YEAR('.$db->qn('DateJoined').') = '.$db->q($LastYear));
$result = $db->setQuery($query)->loadAssocList();
$num_rows = count($result);
if($num_rows>0){
	$LastYearMemNew=$num_rows;
}
$query = $db->getQuery(true)
	->select($db->qn('ID'))
	->from($db->qn('tblMembers'))
	->where('YEAR('.$db->qn('DateCeased').') = '.$db->q($LastYear));
$result = $db->setQuery($query)->loadAssocList();
$num_rows = count($result);
if($num_rows>0){
	$LastYearMemTerminated=$num_rows;
}

$today_start=date("Y-m-d 00:00:00");
$today_end=date("Y-m-d 23:59:59");	
$query = $db->getQuery(true)
	->select('*')
	->from($db->qn('tblMembers'))
	->where($db->qn('DateJoined').' >= CURDATE()')
$mymembers = $db->setQuery(true)->loadAssocList();
$num_rows = count($mymembers);
if($num_rows>0){
	$MemNew=$num_rows;
}
//$MemTerminated=$overduearchived+$terminatedarchived;
$MemTerminated=$overduearchived;


$StatDate=date("Y-m-d H:i:s");	

$statssummary="<b>STATS SUMMARY ".$StatDate."</b></br>";
$statssummary.="<br>\n";
$statssummary.="<b>".$ThisYear." to date</b></br>\n";
$statssummary.="&nbsp;Members Live ".$MemLive."</br>\n";

$statssummary.="<b>Today</b><br>\n";
$statssummary.="&nbsp;New ".$MemNewToday."</br>\n";
$statssummary.="&nbsp;Terminated ".$MemTermToday."</br>\n";
$statssummary.="&nbsp;Change ".($MemNewToday-$MemTermToday)."</br>\n";

$Netmonthlymemberchange=$MonthMemNew-$MonthMemTerminated;
$Netannualmemberchange=$YearMemNew-$YearMemTerminated;

//$statssummary.="&nbsp;New ".$YearMemNew."<br>\n";
//$statssummary.="&nbsp;Terminated ".$YearMemTerminated."<br>\n";
//$statssummary.="&nbsp;Net annual change ".$Netannualmemberchange."<br>\n";
//$statssummary.="<b>".date("M")." to date</b><br>\n";
//$statssummary.="&nbsp;New ".$MonthMemNew."<br>\n";
//$statssummary.="&nbsp;Terminated ".$MonthMemTerminated."<br>\n";
//$statssummary.="&nbsp;Net month change ".$Netmonthlymemberchange."<br>\n";

$LastYearNetannualmemberchange=$LastYearMemNew-$LastYearMemTerminated;

//$statssummary.="<b>".$LastYear."</b><br>\n";
//$statssummary.="&nbsp;New ".$LastYearMemNew."<br>\n";
//$statssummary.="&nbsp;Terminated ".$LastYearMemTerminated."<br>\n";
//$statssummary.="&nbsp;Net annual change ".$LastYearNetannualmemberchange."<br>\n";
$statssummary.="<style>
td {
	border-left:1px solid black;
	border-top:1px solid black;
	width:40px;
}
table {
	border-right:1px solid black;
	border-bottom:1px solid black;
}
</style>";

//this year
$statssummary.="<br>\n";
$statssummary.="<br><b>Member Status</b>\n";
$YearSpan=25;
$YearTo=date("Y");
$YearFrom=$YearTo-$YearSpan;

$ThisYear=$YearFrom;
$lastmonthtotal=0;
while($ThisYear <= $YearTo){

	$statssummary.="<br><table cellpadding='2' cellspacing='0'>";
	$statssummary.="<tr><td>".$ThisYear."</td><td align='right'>Jan</td><td align='right'>Feb</td><td align='right'>Mar</td><td align='right'>Apr</td><td align='right'>May</td><td align='right'>Jun</td><td align='right'>Jul</td><td align='right'>Aug</td><td align='right'>Sep</td><td align='right'>Oct</td><td align='right'>Nov</td><td align='right'>Dec</td><td>Total</td></tr>";
	$mon=1;
	$memtot_row="";
	$new_row="";
	$term_row="";
	$change_row="";
	$check_row="";
	$term_row_total=0;
	$new_row_total=0;
	
	while($mon < 13){
		$startmonth=($ThisYear)."-".str_pad($mon, 2, '0', STR_PAD_LEFT)."-01";
		$query = $db->getQuery(true)
			->select($db->qn('ID'))
			->from($db->qn('tblMembers'))
			->where($db->qn('MemStatus').' != 1')
			->where($db->qn('MemStatus')." != ''")
			->where($db->qn('DateJoined').' > '.$db->q('0000-00-00 00:00:00'))
			->where($db->qn('DateJoined').' <= LAST_DAY('.$db->q($startmonth).')')
			->where('('.$db->qn('DateCeased').' IS NULL OR '.$db->qn('DateCeased').' > LAST_DAY('.$db->q($startmonth).'))');
		$result = $db->setQuery($query)->loadAssocList();
		$num_total = count($result);
		$memtot_row.="<td align='right'>".$num_total."</td>";
		
		$query = $db->getQuery(true)
			->select($db->qn('ID'))
			->from($db->qn('tblMembers'))
			->where($db->qn('MemStatus').' != 1')
			->where($db->qn('MemStatus')." != ''")
			->where('MONTH('.$db->qn('DateJoined').') = '.$db->q(str_pad($mon, 2, '0', STR_PAD_LEFT)))
			->where('YEAR('.$db->qn('DateJoined').') = '.$db->q($ThisYear));
		$result = $db->setQuery($query)->loadAssocList();
		$num_rows_new = count($result);
		$new_row.="<td align='right'>".$num_rows_new."</td>";
		$new_row_total+=$num_rows_new;
		
		$query = $db->getQuery(true)
			->select($db->qn('ID'))
			->from($db->qn('tblMembers'))
			->where($db->qn('MemStatus').' = 5')
			->where($db->qn('DateCeased')." != ''")
			->where('MONTH('.$db->qn('DateCeased').') = '.$db->q(str_pad($mon, 2, '0', STR_PAD_LEFT)))
			->where('YEAR('.$db->qn('DateCeased').') = '.$db->q($ThisYear));
		$result = $db->setQuery($query)->loadAssocList();
		$num_rows_term = count($result);
		$term_row.="<td align='right'>".$num_rows_term."</td>";
		$term_row_total+=$num_rows_term;
		$Debug_sql.=$term_sql."<br><br>";
		
		$change_row.="<td align='right'>".($num_rows_new-$num_rows_term)."</td>";
		$change_row_total=$new_row_total-$term_row_total;
		if(($lastmonthtotal+($num_rows_new-$num_rows_term))!=$num_total){
			$check_row.="<td align='right' bgcolor='FFFF00'>".(($lastmonthtotal+($num_rows_new-$num_rows_term))-$num_total)."</td>";
		}else{
			$check_row.="<td align='right'>".($lastmonthtotal+($num_rows_new-$num_rows_term))."</td>";
		}
		$lastmonthtotal=$num_total;
		$mon+=1;
	}
	$statssummary.="<tr><td>Total</td>".$memtot_row."<td></td></tr>";
	$statssummary.="<tr><td>New</td>".$new_row."<td><b>".$new_row_total."</b></td></tr>";
	$statssummary.="<tr><td>Terminated</td>".$term_row."<td><b>".$term_row_total."</b></td></tr>";
	$statssummary.="<tr><td>Change</td>".$change_row."<td><b>".$change_row_total."</b></td></tr>";
	$statssummary.="<tr><td>Check</td>".$check_row."<td><b></b></td></tr>";
	$statssummary.="</table>";
	$ThisYear+=1;
}


//$statssummary.=$Debug_sql;

$statssummary.="<br>\n";
$TotalSubs=$TotalSubsDD+$TotalSubsBO+$TotalSubsCH+$TotalSubsCC+$TotalSubsFOC;
$statssummary.="<b>Current membership</b><br>\n";

$statssummary.="<br><b>Payment method</b></br>\n";
$statssummary.="<table cellpadding='2' cellspacing='0'>";
$statssummary.="<tr><td>Direct Debit </td><td align='right'>".$MemDD."
</td><td align='right'>".(round($MemDD/$MemLive*100,1))."%
</td><td align='right'>&pound;".number_format($TotalSubsDD)."
</td><td align='right'>".(round($TotalSubsDD/$TotalSubs*100,1))."%</td></tr>\n";

$statssummary.="<tr><td>Bankers_Order</td><td align='right'>".$MemBO."
</td><td align='right'>".(round($MemBO/$MemLive*100,1))."% 
</td><td align='right'>&pound;".number_format($TotalSubsBO)."
</td><td align='right'>".(round($TotalSubsBO/$TotalSubs*100,1))."%</td></tr>\n";

$statssummary.="<tr><td>Cheque</td><td align='right'>".$MemCH."
</td><td align='right'>".(round($MemCH/$MemLive*100,1))."% 
</td><td align='right'>&pound;".number_format($TotalSubsCH)."
</td><td align='right'>".(round($TotalSubsCH/$TotalSubs*100,1))."%</td></tr>\n";

$statssummary.="<tr><td>Card</td><td align='right'>".$MemCC."
</td><td align='right'>".(round($MemCC/$MemLive*100,1))."% 
</td><td align='right'>&pound;".number_format($TotalSubsCC)."
</td><td align='right'>".(round($TotalSubsCC/$TotalSubs*100,1))."%</td></tr>\n";

$statssummary.="<tr><td>FOC</td><td align='right'>".$MemFOC."
</td><td align='right'>".(round($MemFOC/$MemLive*100,1))."% 
</td><td align='right'>&pound;".number_format($TotalSubsFOC)."
</td><td align='right'>".(round($TotalSubsFOC/$TotalSubs*100,1))."%</td></tr>\n";

$statssummary.="<tr><td>Total </td><td align='right'>".($MemDD+$MemBO+$MemCH+$MemCC+$MemFOC)."
</td><td align='right'></td><td>&pound;".(number_format($TotalSubs))."
</td><td></td></tr>\n";
$statssummary.="</table>\n";

$statssummary.="<br><b>Member type</b></br>\n";
$statssummary.="<table cellpadding='2' cellspacing='0'>";
$statssummary.="<tr><td>Single </td><td align='right'>".$MemOrdinary."
</td><td align='right'>".(round($MemOrdinary/$MemLive*100,1))."%</td></tr>\n";

$statssummary.="<tr><td>Family </td><td align='right'>".$MemFamily."
</td><td align='right'>".(round($MemFamily/$MemLive*100,1))."%</td></tr>\n";

$statssummary.="<tr><td>Honorary </td><td align='right'>".$MemHonorary."
</td><td align='right'>".(round($MemHonorary/$MemLive*100,1))."%</td></tr>\n";

$statssummary.="<tr><td>Press </td><td align='right'>".$MemPress."
</td><td align='right'>".(round($MemPress/$MemLive*100,1))."%</td></tr>\n";

$statssummary.="<tr><td>Voucher </td><td align='right'>".$MemVoucher."
</td><td align='right'>".(round($MemVoucher/$MemLive*100,1))."%</td></tr>\n";

$statssummary.="<tr><td>Total</td><td align='right'>".($MemOrdinary+$MemFamily+$MemHonorary+$MemPress+$MemVoucher)."
</td><td></td></tr>\n";
$statssummary.="</table>\n";


$statssummary.="<br><b>Address location</b></br>\n";
$statssummary.="<table cellpadding='2' cellspacing='0'>";
$statssummary.="<tr><td>UK</td><td align='right'>".$MemUK."</td><td align='right'>".(round($MemUK/$MemLive*100,1))."%</td>";
$statssummary.="<tr><td>EU_not_UK </td><td align='right'>".$MemEU."</td><td align='right'>".(round($MemEU/$MemLive*100,1))."%</td>";
$statssummary.="<tr><td>Z1</td><td align='right'>".$MemZ1."</td><td align='right'>".(round($MemZ1/$MemLive*100,1))."%</td>";
$statssummary.="<tr><td>Z2</td><td align='right'>".$MemZ2."</td><td align='right'>".(round($MemZ2/$MemLive*100,1))."%</td>";
$statssummary.="<tr><td>Total</td><td align='right'>".($MemUK+$MemEU+$MemZ1+$MemZ2)."</td><td align='right'></td>";
$statssummary.="</td></tr>\n";
$statssummary.="</table>\n";

$statssummary.="<br><b>Situation</b></br>\n";
$statssummary.="<table cellpadding='2' cellspacing='0'>";
$statssummary.="<tr><td>Owner</td><td align='right'>".$MemOwner."</td><td align='right'>".(round($MemOwner/$MemLive*100,1))."%</td>";
$statssummary.="<tr><td>Dreamer</td><td align='right'>".$MemDreamer."</td><td align='right'>".(round($MemDreamer/$MemLive*100,1))."%</td>";
$statssummary.="<tr><td>Commercial</td><td align='right'>".$MemCommercial."</td><td align='right'>".(round($MemCommercial/$MemLive*100,1))."%</td>";
$statssummary.="<tr><td>Unknown</td><td align='right'>".$MemSitUnknown."</td><td align='right'>".(round($MemSitUnknown/$MemLive*100,1))."%</td>";
$statssummary.="<tr><td>Total</td><td align='right'>".($MemOwner+$MemDreamer+$MemCommercial+$MemSitUnknown)."</td><td align='right'></td>";
$statssummary.="</td></tr>\n";
$statssummary.="</table>\n";

$MemCruiseOther=$MemOwner-($MemCruiseGB+$MemCruiseBE+$MemCruiseNL+$MemCruiseFR);
$statssummary.="<br><b>Barge location</b></br>\n";
$statssummary.="<table cellpadding='2' cellspacing='0'>";
$statssummary.="<tr><td>GB</td><td align='right'>".$MemCruiseGB."</td><td align='right'>".(round($MemCruiseGB/$MemOwner*100,1))."%</td>";
$statssummary.="<tr><td>Non_GB</td><td align='right'>".($MemCruiseBE+$MemCruiseNL+$MemCruiseFR+$MemCruiseOther)."</td><td align='right'>".(round(($MemCruiseBE+$MemCruiseNL+$MemCruiseFR+$MemCruiseOther)/$MemOwner*100,1))."%</td>";
$statssummary.="<tr><td>BE</td><td align='right'>".$MemCruiseBE."</td><td align='right'>".(round($MemCruiseBE/$MemOwner*100,1))."%</td>";
$statssummary.="<tr><td>NL</td><td align='right'>".$MemCruiseNL."</td><td align='right'>".(round($MemCruiseNL/$MemOwner*100,1))."%</td>";
$statssummary.="<tr><td>FR</td><td align='right'>".$MemCruiseFR."</td><td align='right'>".(round($MemCruiseFR/$MemOwner*100,1))."%</td>";
$statssummary.="<tr><td>Other</td><td align='right'>".$MemCruiseOther."</td><td align='right'>".(round($MemCruiseOther/$MemOwner*100,1))."%</td>";
$statssummary.="<tr><td>Total</td><td align='right'>".($MemCruiseGB+$MemCruiseBE+$MemCruiseNL+$MemCruiseFR+$MemCruiseOther)."</td><td align='right'></td>";
$statssummary.="</td></tr>\n";
$statssummary.="</table>\n";


//confirm emails
//$content=$message."\n\n".$emailfooter;
$content="$htmlheader <div class=content>";

$content.="<h2>DBA - The Barge Association. Membership report</h2><br>";


$content.=$statssummary. "<br></div>";


$content.=$listing;

$content.="</body>\n";
$content.="</html>\n";





//echo("<br>done registeremail:$registrationemail webmasteremail:$webmasteremail LastTo:$to livemailadmin:$livemailadmin");



echo($content);

?>
