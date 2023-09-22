<?php
//load Joomla helpers for emailsending
define( '_JEXEC', 1 );
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', strstr(__DIR__, 'public_html', true).'public_html');
//define('JPATH_BASE', substr(__FILE__,0,strrpos(__FILE__, DS."components")));
define('JPATH_COMPONENT', JPATH_BASE .DS.'components'.DS.'com_membership');
require_once(JPATH_BASE .DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE .DS.'includes'.DS.'framework.php');
require_once(JPATH_COMPONENT .DS.'commonV3.php');

getpost_ifset(array('cat_select','report','startdate','enddate'));
if(!$startdate){
	$startdate='01-11-2012';
}
if(!$enddate){
	$enddate=date("d-m-Y");
}
$starttimestamp = strtotime($startdate);
$endtimestamp = strtotime($enddate);
$totalposts=0;
if($report=="subscriptions"){
	//make the header
	$list="<table class=table_forum_subscribers><tr><td align='right'><b>No</b></td><td align='right'><b>UserID</b></td><td align='left'><b>User</b></td>\n";
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('#__kunena_categories'))
		->where($db->qn('published').' = 1')
		->order($db->qn('id'));
	if($cat_select && $cat_select != "ALL") $query->where($db->qn('id').' = '.$db->q($cat_select));
	$categories = $db->setQuery($query)->loadAssocList();
	foreach($categories as $catrow) {
		$catid = $catrow["id"];
		$catname = $catrow["name"];
		$list.="<td><b>$catname</b></td>\n";
	}
	$list.="</tr>\n";
	
	
	//get users subscribed
	$query = $db->getQuery(true)
		->select('DISTINCTROW '.$db->qn('c.user_id').', '.$db->qn('u.name'))
		->from($db->qn('#__kunena_user_categories', 'c'))
		->innerJoin($db->qn('#__users', 'u').' ON '.$db->qn('c.user_id').' = '.$db->qn('u.id'))
		->order($db->qn('u.name'));
	if($cat_select && $cat_select != 'ALL') {
		$query->where($db->qn('category_id').' = '.$db->q($cat_select))
			->where($db->qn('subscribed').' = 1');
	}
	$subscribers = $db->setQuery($query)->loadAssocList();
	$user_no=0;
	$line = "even";
	foreach($subscribers as $row) {
		$user_id = $row["user_id"];
		//$category_id = $row["category_id"];
		//$subscribed = $row["subscribed"];
		$name = $row["name"];
		if ($line == "even"){
			$lineformat="class='table_stripe_odd'";
			$line="odd";
		}else{
			$lineformat="class='table_stripe_even'";
			$line="even";
		}
		$user_no+=1;
		$list.="<tr><td $lineformat align='right'>$user_no</td><td $lineformat align='right'>$user_id</td><td $lineformat align='left'>$name</td>\n";
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__kunena_categories'))
			->where($db->qn('published').' = 1')
			->order($db->qn('id'));
		if($cat_select && $cat_select != "ALL") $query->where($db->qn('id').' = '.$db->q($cat_select));
		$categories = $db->setQuery($query)->loadAssocList();
		foreach($categories as $catrow) {
			$catid = $catrow["id"];
			$catname = $catrow["name"];
	
			$words = explode(" ", $catname);
			$catnameshort = "";
			foreach ($words as $value) {
				$catnameshort .= substr($value, 0, 1);
			}
			
			//lookup catuser to see number of posts
			$query = $db->getQuery(true)
				->select('COUNT(*)')
				->from($db->qn('#__kunena_messages'))
				->where($db->qn('userid').' = '.$db->q($user_id))
				->where($db->qn('catid').' = '.$db->q($catid));
			$myposts = $db->setQuery($query)->loadResult();
			$totalposts+=$myposts;
			if($myposts<1){
				$myposts="";
			}
				
			//lookup catuser to see if subscribed
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__kunena_categories'))
				->where($db->qn('user_id').' = '.$db->q($user_id))
				->where($db->qn('category_id').' = '.$db->q($catid))
				->where($db->qn('subscribed').' = 1');
			$subscribed = $db->setQuery($query)->loadAssocList();
			if(count($subscribed)){
				$list.="<td $lineformat >[".$catnameshort."] ".$myposts."</td>\n";
			}else{
				$list.="<td $lineformat >".$myposts."</td>\n";
			}
		}
		$list.="</tr>\n";	
				
		//#__kunena_categories id	parent_id	name	alias
		//#__kunena_user_categories user_id	category_id	role	allreadtime	subscribed	params
		//#__users id	name
	}
	$list.="</table>\n";
}elseif($report=="emailposters"){

	//make the header
	$list="";
	//$list="Data as from ".$startdate;
	$list.="<table class=table_forum_subscribers><tr><td align='right'><b>No</b></td><td align='right'><b>UserID</b></td><td align='left'><b>User</b></td>\n";
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('#__kunena_categories'))
		->where($db->qn('published').' = 1')
		->order($db->qn('id'));
	if($cat_select && $cat_select != "ALL") $query->where($db->qn('id').' = '.$db->q($cat_select));
	$categories = $db->setQuery($query)->loadAssocList();
	foreach($categories as $catrow) {
		$catid = $catrow["id"];
		$catname = $catrow["name"];
		$list.="<td><b>$catname</b></td>\n";
	}
	$list.="</tr>\n";
	
	
	//get users subscribed
	$query = $db->getQuery(true)
		->select('DISTINCTROW '.$db->qn('m.userid').', '.$db->qn('u.name'))
		->from($db->qn('#__kunena_messages', 'm'))
		->innerJoin($db->qn('#__users', 'u').' ON '.$db->qn('m.userid').' = '.$db->qn('u.id'))
		->where($db->qn('ip').' = '.$db->q('96.127.138.194'))
		->where($db->qn('ip').' = '.$db->q('181.224.142.6'))
		->where($db->qn('time').' BETWEEN '.$db->q($starttimestamp).' AND '.$db->q($endtimestamp))
		->order($db->qn('u.name'));
	if($cat_select && $cat_select != 'ALL') $query->where($db->qn('catid').' = '.$db->q($cat_select));
	$subscribers = $db->setQuery($query)->loadAssocList();
	$user_no=0;
	$line = "even";
	foreach($subscribers as $row) {
		$user_id = $row["userid"];
		//$category_id = $row["category_id"];
		//$subscribed = $row["subscribed"];
		$name = $row["name"];
		if ($line == "even"){
			$lineformat="class='table_stripe_odd'";
			$line="odd";
		}else{
			$lineformat="class='table_stripe_even'";
			$line="even";
		}
		$user_no+=1;
		$list.="<tr><td $lineformat align='right'>$user_no</td><td $lineformat align='right'>$user_id</td><td $lineformat align='left'>$name</td>\n";
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__kunena_categories'))
			->where($db->qn('published').' = 1')
			->order($db->qn('id'));
		if($cat_select && $cat_select != "ALL") $query->where($db->qn('id').' = '.$db->q($cat_select));
		$categories = $db->setQuery($query)->loadAssocList();
		foreach($categories as $catrow) {
			$catid = $catrow["id"];
			$catname = $catrow["name"];
	
			$words = explode(" ", $catname);
			$catnameshort = "";
			foreach ($words as $value) {
				$catnameshort .= substr($value, 0, 1);
			}
	
			//lookup catuser to see number of posts
			$query = $db->getQuery(true)
				->select('COUNT(*)')
				->from($db->qn('#__kunena_messages'))
				->where($db->qn('ip').' = '.$db->q('96.127.138.194'))
				->where($db->qn('ip').' = '.$db->q('181.224.142.6'))
				->where($db->qn('time').' BETWEEN '.$db->q($starttimestamp).' AND '.$db->q($endtimestamp))
				->where($db->qn('catid').' = '.$db->q($catid))
				->where($db->qn('userid').' = '.$db->q($user_id));
			if($cat_select && $cat_select != 'ALL') $query->where($db->qn('catid').' = '.$db->q($cat_select));
			$myposts = $db->setQuery($query)->loadResult();
			$totalposts+=$myposts;
			if($myposts<1){
				$myposts="";
			}
				
			$list.="<td $lineformat >".$myposts."</td>\n";

		}
		$list.="</tr>\n";	
				
		//#__kunena_categories id	parent_id	name	alias
		//#__kunena_user_categories user_id	category_id	role	allreadtime	subscribed	params
		//#__users id	name
	}
	$list.="</table>\n";

}elseif($report=="websiteposters"){
	
	//make the header
	$list="";
	//$list="Data as from ".$startdate;
	$list.="<table class=table_forum_subscribers><tr><td align='right'><b>No</b></td><td align='right'><b>UserID</b></td><td align='left'><b>User</b></td>\n";
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('#__kunena_categories'))
		->where($db->qn('published').' = 1')
		->order($db->qn('id'));
	if($cat_select && $cat_select != "ALL") $query->where($db->qn('id').' = '.$db->q($cat_select));
	$categories = $db->setQuery($query)->loadAssocList();
	foreach($categories as $catrow) {
		$catid = $catrow["id"];
		$catname = $catrow["name"];
		$list.="<td><b>$catname</b></td>\n";
	}
	$list.="</tr>\n";
	
	
	//get users who have posted
	$query = $db->getQuery(true)
		->select('DISTINCTROW '.$db->qn('m.userid').', '.$db->qn('u.name'))
		->from($db->qn('#__kunena_messages', 'm'))
		->innerJoin($db->qn('#__users', 'u').' ON '.$db->qn('m.userid').' = '.$db->qn('u.id'))
		->where($db->qn('ip').' != '.$db->q('96.127.138.194'))
		->where($db->qn('ip').' != '.$db->q('181.224.142.6'))
		->where($db->qn('time').' BETWEEN '.$db->q($starttimestamp).' AND '.$db->q($endtimestamp))
		->order($db->qn('u.name'));
	if($cat_select && $cat_select != 'ALL') $query->where($db->qn('catid').' = '.$db->q($cat_select));
	$subscribers = $db->setQuery($query)->loadAssocList();
	$user_no=0;
	$line = "even";
	foreach($subscribers as $row) {
		$user_id = $row["userid"];
		//$category_id = $row["category_id"];
		//$subscribed = $row["subscribed"];
		$name = $row["name"];
		if ($line == "even"){
			$lineformat="class='table_stripe_odd'";
			$line="odd";
		}else{
			$lineformat="class='table_stripe_even'";
			$line="even";
		}
		$user_no+=1;
		$list.="<tr><td $lineformat align='right'>$user_no</td><td $lineformat align='right'>$user_id</td><td $lineformat align='left'>$name</td>\n";
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__kunena_categories'))
			->where($db->qn('published').' = 1')
			->order($db->qn('id'));
		if($cat_select && $cat_select != "ALL") $query->where($db->qn('id').' = '.$db->q($cat_select));
		$categories = $db->setQuery($query)->loadAssocList();
		foreach($categories as $catrow) {
			$catid = $catrow["id"];
			$catname = $catrow["name"];
	
			$words = explode(" ", $catname);
			$catnameshort = "";
			foreach ($words as $value) {
				$catnameshort .= substr($value, 0, 1);
			}
	
			//lookup catuser to see number of posts
			$query = $db->getQuery(true)
				->select('COUNT(*)')
				->from($db->qn('#__kunena_messages'))
				->where($db->qn('ip').' != '.$db->q('96.127.138.194'))
				->where($db->qn('ip').' != '.$db->q('181.224.142.6'))
				->where($db->qn('time').' BETWEEN '.$db->q($starttimestamp).' AND '.$db->q($endtimestamp))
				->where($db->qn('catid').' = '.$db->q($catid))
				->where($db->qn('userid').' = '.$db->q($user_id));
			if($cat_select && $cat_select != 'ALL') $query->where($db->qn('catid').' = '.$db->q($cat_select));
			$myposts = $db->setQuery($query)->loadResult();
			$totalposts+=$myposts;
			if($myposts<1){
				$myposts="";
			}
				
			$list.="<td $lineformat >".$myposts."</td>\n";
		}
		$list.="</tr>\n";	
				
		//#__kunena_categories id	parent_id	name	alias
		//#__kunena_user_categories user_id	category_id	role	allreadtime	subscribed	params
		//#__users id	name
	}
	$list.="</table>\n";
}else{
	$list="";
}



?>
<html>
<head>
<title>Forum subscribers report</title>
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

<form name="form" enctype="multipart/form-data"  method="post">
<div class="pop_page_title">
  <h2>Forum  reports</h2>
</div>


<br><br>
<div>
  <p>&nbsp;</p>
  <p>Category filter 
    <select name="cat_select"> 
      <option value='ALL'>ALL</option>
      <?php 
	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('#__kunena_categories'))
		->where($db->qn('published').' = 1')
		->order($db->qn('id'));
	$categories = $db->setQuery($query)->loadAssocList();
	foreach($categories as $catrow) {
		$catid = $catrow["id"];
		$catname = $catrow["name"];
		if($catid==$cat_select){
			echo("<option selected value='".$catid."'>".$catname."</option>\n");
		}else{
			echo("<option value='".$catid."'>".$catname."</option>\n");		
		}
	}
	
?>
            </select>
    
      <?php 
	if($report=="subscriptions"){
		$subscriptions_selected=" selected";
	}else{
		$subscriptions_selected="";
	}
	if($report=="emailposters"){
		$emailposters_selected=" selected";
	}else{
		$emailposters_selected="";
	}
	if($report=="websiteposters"){
		$websiteposters_selected=" selected";
	}else{
		$websiteposters_selected="";
	}
	
?>
    Report 
    <select name="report"> 
      <option value='subscriptions'<?php echo($subscriptions_selected); ?>>Subscriptions</option>
      <option value='emailposters'<?php echo($emailposters_selected); ?>>Email posters</option>
      <option value='websiteposters'<?php echo($websiteposters_selected); ?>>Website posters</option>
      </select>
    
    
      <input type="submit" name="submit" id="submit" value="Go">
      <?php echo(" ".$user_no." members. Totalposts:".$totalposts.". [] indicates currently subscribed to the category. "); 
		echo("<br>From: $startdate to $enddate (timestamp $starttimestamp - $endtimestamp)");
	echo ($list);

?>
    </p>
</div>
</form>

</body>

</html>