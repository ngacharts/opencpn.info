<?php
$link = mysql_connect('localhost', 'gunther', 'password');
if (!$link) {
    die('Connection failed: ' . mysql_error());
}
$db_selected = mysql_select_db('usr_web12_1', $link);
if (!$db_selected) {
    die ('Cannot use DB: ' . mysql_error());
}

$gp = array();
$result = mysql_query("SELECT number FROM ocpn_nga_charts_with_params");
while ($row = mysql_fetch_array($result)) {
	array_push($gp, $row['number']);
}

#print_r($gp);

foreach($gp as $val) {
	#echo"$val<br>";
	$sql = 'UPDATE ocpn_nga_charts_with_params SET Snhemi=NULL WHERE number="'.$val.'"';
	$retval = mysql_query( $sql, $link );
	if(! $retval ) {
		die('Could not update data: ' . mysql_error());
	}
}



?>
