<?php
$ftp_server = "ftp.weizter.net";
$ftp_user_name = 'ftpuser';
$ftp_user_pass = 'password';

// Variablen definieren
$local_file = 'jpg_checksums.md5';
$server_file = './jpg_checksums.md5';

// DD3DmwFMzErzmUDr
// find ./nga-charts -type f -exec md5sum {} \; >> jpg_checksums.md5 

// Verbindung aufbauen
$conn_id = ftp_connect($ftp_server);

// Login mit Benutzername und Passwort
$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

// Versuche $server_file herunterzuladen und in $local_file zu speichern
if (ftp_get($conn_id, $local_file, $server_file, FTP_ASCII)) {
    echo "$local_file wurde erfolgreich geschrieben\n";
} else {
    echo "Ein Fehler ist aufgetreten\n";
}


$checksums = file('jpg_checksums.md5');

$link = mysql_connect('localhost', 'gunther', 'password');
if (!$link) {
    die('keine Verbindung möglich: ' . mysql_error());
}

$db_selected = mysql_select_db('usr_web12_1', $link);
if (!$db_selected) {
    die ('Kann foo nicht benutzen : ' . mysql_error());
}
else echo 'Verbindung erfolgreich<br>';

$out = '';
foreach($checksums as $line) {
	$pos = strpos($line, '/incoming');
	if ($pos === false) {
		$val = explode('  ', $line);
		#print_r($val);
		$suchmuster = '#\./nga-charts/([0-9]{2,5})/[0-9]{2,5}\.jpg#';
		$id = preg_replace($suchmuster, '\1', $val[1]);
		#echo"$id<br>";
		$sql = 'UPDATE ocpn_nga_charts_links SET image_fullsize_md5="'.$val[0].'" WHERE number="'.$id.'"';
		$retval = mysql_query( $sql, $link );
		if(! $retval ) {
			die('Could not update data: ' . mysql_error());
		}
		$out .= trim($id).": ".trim($val[0])."\r\n";
	}
}
$wf = file_put_contents('jpg_fullsize.md5', $out);
if(!$wf === false) {
	// Datei hochladen
	if (ftp_put($conn_id, 'jpg_fullsize.md5', 'jpg_fullsize.md5', FTP_ASCII)) {
		echo "$file erfolgreich hochgeladen\n";
		$rename = ftp_rename($conn_id, '/jpg_fullsize.md5', '/nga-charts/jpg_fullsize.md5');
	}
	else {
		echo "Ein Fehler trat beim Hochladen von $file\n";
	}
}

// Verbindung schließen
ftp_close($conn_id);
mysql_close($link);
?> 
