<?php
$link = mysql_connect('localhost', 'gunther', 'password');
if (!$link) {
    die('keine Verbindung möglich: ' . mysql_error());
}

$db_selected = mysql_select_db('usr_web12_1', $link);
if (!$db_selected) {
    die ('Kann foo nicht benutzen : ' . mysql_error());
}
else echo 'Verbindung erfolgreich<br>';

$gp = file_get_contents('has-addition-ser-9.txt');
$chart = unserialize($gp);

foreach($chart as $k => $v) {
    if(count($v) > 2) {
        $sql = 'UPDATE ocpn_nga_charts SET has_addition="1" WHERE number="'.$k.'"';
			$retval = mysql_query( $sql, $link );
			if(! $retval ) {
				die('Could not update data: ' . mysql_error());
			}
    }
}


?>
