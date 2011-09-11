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

$gp = file('NGA-Charts.txt');

foreach($gp as $v) {
	$number = (int)trim($v);

	
	$result = mysql_query("INSERT INTO ocpn_nga_charts_links (number) VALUES(".$number.") ");  
	if (!$result) {
    die('Ungültige Abfrage: ' . mysql_error());
}

	#echo "$number<br>$scale<br>$title<br>$edition<br>$date<br>$correction<br>$width<br>$height<br>$tiles<br>$xtiles<br>$ytiles<br>$tilesize<br>$zoomlevel<br><br>";
}
?>
