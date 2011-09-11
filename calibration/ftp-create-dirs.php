<?php
$ftp_server = "ftp.weizter.net";
$ftp_user_name = 'ftpuser';
$ftp_user_pass = 'password';

// Verbindung aufbauen
$conn_id = ftp_connect($ftp_server);

// Login mit Benutzername und Passwort
$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

$chart = file('NGA-Charts.txt');
foreach($chart as $v) {
	$dir = 'nga-charts/'.trim($v);
	// Versuche das Verzeichnis $dir zu erzeugen
	if (ftp_mkdir($conn_id, $dir)) {
	 #echo "$dir erfolgreich erzeugt\n";
	} else {
	 echo "Es trat ein Fehler beim Erzeugen von $dir auf\n";
	}
}


// Verbindung schließen
ftp_close($conn_id);
?> 
