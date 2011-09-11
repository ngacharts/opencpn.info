<?php
session_start();
$sess = session_id();
$post = array();
if($_POST) {
	foreach($_POST as $k => $v) {
		$post[$k] = trim(htmlspecialchars(strip_tags($v)));
	}
}

// ### Connect to the DB and fetch data from view for the chart
$link = mysql_connect('localhost', 'gunther', 'password');
if (!$link) {
    die('Connection failed: ' . mysql_error());
}
$db_selected = mysql_select_db('usr_web12_1', $link);
if (!$db_selected) {
    die ('Cannot use DB: ' . mysql_error());
}
$result = mysql_query('UPDATE ocpn_nga_kap SET locked = NOW(), locked_by = "'.$_SESSION['wp-user']['id'].'" WHERE number = "'.$post["cid"].'"');
if(!$result) {
	if(mysql_errno() !== 0) $db_errors = mysql_errno() . ": " . mysql_error() . "\n";
	echo $db_errors;
}



/*
echo time()."<br />";
print "$sess<br />"; 
print $_POST['sessionID']."<br />"; 
print_r($post);
*/
?>
