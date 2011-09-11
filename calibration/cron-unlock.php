<?php
// ### Connect to the DB
$link = mysql_connect('localhost', 'gunther', 'password');
if (!$link) {
    die('Connection failed: ' . mysql_error());
}
$db_selected = mysql_select_db('usr_web12_1', $link);
if (!$db_selected) {
    die ('Cannot use DB: ' . mysql_error());
}
$result = mysql_query('UPDATE ocpn_nga_kap SET locked=NULL, locked_by=NULL WHERE UNIX_TIMESTAMP() - UNIX_TIMESTAMP(locked) > 300');
?>
