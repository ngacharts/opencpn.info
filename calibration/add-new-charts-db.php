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

$basepath = 'http://www.charts.noaa.gov/NGAViewer/';
$imageprop = '/ImageProperties.xml';

$new_charts = file('NGA-Charts-new-props.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

#print_r($new_charts);


for ($i = 0; $i < count($new_charts); $i=$i+6) {
	$number =$new_charts[$i];
	$scale = preg_replace('/,/', '', $new_charts[$i + 1]);
	$title = addslashes($new_charts[$i + 2]);
	$edition = (int)$new_charts[$i + 3];
	$date = date("Y-m-d", strtotime($new_charts[$i + 4]));
	$year = date("Y", strtotime($date));
	if($year > date("Y")){
		$date = date("Y-m-d", mktime(0, 0, 0, date("m", strtotime($new_charts[$i + 4])), date("d", strtotime($new_charts[$i + 4])), date("Y", strtotime($new_charts[$i + 4]))-100));
		#echo"$date<br>";
	}
	$correction = $new_charts[$i + 5];
	
	$url = $basepath.trim($new_charts[$i]).$imageprop;
	$props = @file_get_contents($url);
	if($props) {
		preg_match('/WIDTH="([0-9]*)"/', $props, $treffer);
		$width = $treffer[1];
		preg_match('/HEIGHT="([0-9]*)"/', $props, $treffer);
		$height = $treffer[1];
		preg_match('/TILESIZE="([0-9]*)"/', $props, $treffer);
		$tilesize = $treffer[1];
		
		$xtiles = floor($width / 256);
		$ytiles = floor($height / 256);
		$tiles = $xtiles * $ytiles + $xtiles + $ytiles + 1;

		if($width >= $height) $zoomlevel = ceil(log($width/256, 2));
		else $zoomlevel = ceil(log($height/256, 2));

		if($debug) echo"number: $number<br> scale: $scale<br> title: $title<br> edition: $edition<br> date: $date<br> correction: $correction<br> width: $width<br> height: $height<br> tiles: $tiles<br> xtiles: $xtiles<br> ytiles: $ytiles<br> tilesize: $tilesize<br> zoomlevel: $zoomlevel<br><br>";
		else {
			$result = mysql_query("INSERT INTO ocpn_nga_charts (number, scale, title, edition, date, correction, width, height, tiles, xtiles, ytiles, tilesize, zoomlevel) VALUES(".$number.", ".$scale.", '".$title."', ".$edition.", '".$date."', '".$correction."', ".$width.", ".$height.", ".$tiles.", ".$xtiles.", ".$ytiles.", ".$tilesize.", ".$zoomlevel.") ");  
			if (!$result) {
				die('Ungültige Abfrage: ' . mysql_error());
			}
		}
	}
}

?>
