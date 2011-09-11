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

$gp = file_get_contents('nga-charts-props-misc-ser.txt');
$chart = unserialize($gp);

#print_r($chart[10]); exit;

/*
foreach($chart[37010] as $k => $v) {
	if($k == 'scale') {
		$scale = $v;
		#strtr($scale,",", "");
		#echo"$scale<br>";
		$num = preg_replace('/,/', '', $scale);
	}
	if($k == 'date') {
		$date = $v;
		$date = strtotime($date);
		$new = date("Y-m-d", $date); 
		#$date = split('[/.-]', $v);
		#$isodate = sprintf("%04d-%02d-%02d", $date);
	}
	if($k == 'width') {
		$w = $v;
	}
}
*/
/*
$chart[24491][width] = 20200;
$chart[24491][height] = 15000;
$chart[24491][tiles] = 4661;
$chart[24491][xtiles] = 78;
$chart[24491][ytiles] = 58;
$chart[24491][tilesize] = 256;

$chart[37166][width] = 16800;
$chart[37166][height] = 12800;
$chart[37166][tiles] = 3116;
$chart[37166][xtiles] = 65;
$chart[37166][ytiles] = 50;
$chart[37166][tilesize] = 256;
*/



foreach($chart as $k => $v) {
	$number = (int)$k;
	$scale = $v['scale'];
	$scale = preg_replace('/,/', '', $scale);
	$title = addslashes($v['title']);
	$edition = (int)$v['edition'];
	$date = $v['date'];
	$date = date("Y-m-d", strtotime($date)); 
	$correction = $v['corrected'];
	$width = (int)$v['width'];
	$height = (int)$v['height'];
	$tiles = (int)$v['tiles'];
	$xtiles = (int)$v['xtiles'];
	$ytiles = (int)$v['ytiles'];
	$tilesize = (int)$v['tilesize'];
	if($width >= $height) $zoomlevel = ceil(log($width/256, 2));
	else $zoomlevel = ceil(log($height/256, 2));
	
	$result = mysql_query("INSERT INTO ocpn_nga_charts (number, scale, title, edition, date, correction, width, height, tiles, xtiles, ytiles, tilesize, zoomlevel) VALUES(".$number.", ".$scale.", '".$title."', ".$edition.", '".$date."', '".$correction."', ".$width.", ".$height.", ".$tiles.", ".$xtiles.", ".$ytiles.", ".$tilesize.", ".$zoomlevel.") ");  
	if (!$result) {
		die('Ungültige Abfrage: ' . mysql_error());
	}
}

	echo "$number<br>$scale<br>$title<br>$edition<br>$date<br>$correction<br>$width<br>$height<br>$tiles<br>$xtiles<br>$ytiles<br>$tilesize<br>$zoomlevel<br><br>";
}
?>
