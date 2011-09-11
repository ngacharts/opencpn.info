<?php
// set the URL and Path variables
$basePath = '/var/www/web12/html/nga/chartimages/thumbs/';
$baseUrl = 'http://www.charts.noaa.gov/NGAViewer/';

$endUrl = '/TileGroup0/0-0-0.jpg';

$gp = file('NGA-Charts-new.txt');

foreach($gp as $v) {
	$v = trim($v);
	$img = file_get_contents($baseUrl.$v.$pathEnd);
	if($img) {
		file_put_contents($basePath.$v.'_zl0.jpg', $img);
        chmod($basePath.$v.'_zl0.jpg', 0644);
		echo "$v<br>";
	}
}
?>