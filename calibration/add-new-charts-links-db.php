<?php
$debug = false;

$link = mysql_connect('localhost', 'gunther', 'password');
if (!$link) {
    die('keine Verbindung möglich: ' . mysql_error());
}

$db_selected = mysql_select_db('usr_web12_1', $link);
if (!$db_selected) {
    die ('Kann foo nicht benutzen : ' . mysql_error());
}

$ftp_server = "ftp.weizter.net";
$ftp_user_name = 'ftpuser';
$ftp_user_pass = 'password';
// Verbindung aufbauen
$conn_id = ftp_connect($ftp_server);
// Login mit Benutzername und Passwort
$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

#$gp = file('NGA-Charts.txt');
$ftpbaseDir = '/nga-charts/';

$new_charts = file('NGA-Charts-new.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
print_r($new_charts);

foreach($new_charts as $v) {
	$number = (int)trim($v);
	#if($number < 20000 || $number > 29999) continue;
	$result = mysql_query("INSERT INTO ocpn_nga_charts_links (number) VALUES($number)");  
	if (!$result) {
		die('Ungültige Abfrage: ' . mysql_error());
	}
	
	$contents = ftp_nlist($conn_id, '/nga-charts/'.$number.'/');
	if($contents) {
		$val = str_replace('/nga-charts/', 'ftp://ftp.weizterfish.com/', $contents[0]);
		$size = ftp_size($conn_id, '/nga-charts/'.$number.'/'.$number.'.jpg');
		if($size && $size != -1) {
			$sql = 'UPDATE ocpn_nga_charts_links SET image_fullsize="'.urlencode($val).'", image_fullsize_filesize='.$size.', status=2 WHERE number="'.$number.'"';
			$retval = mysql_query( $sql, $link );
			if(! $retval ) {
				die('Could not update data: ' . mysql_error());
			}
		}
		else echo"Error in size at $number<br>";
	}
}

?>
