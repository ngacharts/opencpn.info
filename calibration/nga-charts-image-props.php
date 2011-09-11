<?php
$basepath = 'http://www.charts.noaa.gov/NGAViewer/';
$imageprop = '/ImageProperties.xml';

#for($a=1; $a<=9; $a++) {
	
	#$numbers = file('NGA-Charts-misc.txt');
	$numbers = array(622);

	$doc = new DomDocument('1.0', 'UTF-8');

	$xml_charts = $doc->createElement('charts');

	foreach($numbers as $val) {
		#echo "$val<br>";
		$url = $basepath.trim($val).$imageprop;
		$props = file_get_contents($url);
		
		if($props) {
			preg_match('/WIDTH="([0-9]*)"/', $props, $treffer);
			$width = $treffer[1];
			preg_match('/HEIGHT="([0-9]*)"/', $props, $treffer);
			$height = $treffer[1];
			preg_match('/TILESIZE="([0-9]*)"/', $props, $treffer);
			$tileSize = $treffer[1];
			
			$x = floor($width / 256);
			$y = floor($height / 256);
			$numTilesLevel = $x * $y + $x + $y + 1;
			
			$xml_chart = $doc->createElement('chart');
			
			$xml_number = $doc->createElement('number',$val);
			$xml_chart->appendChild($xml_number);

			$xml_properties = $doc->createElement('properties');

			#$xml_number = $doc->createElement('number',$chart);
			#$xml_properties->appendChild($xml_number);

			$xml_width = $doc->createElement('width',$width);
			$xml_properties->appendChild($xml_width);

			$xml_height = $doc->createElement('height',$height);
			$xml_properties->appendChild($xml_height);

			$xml_tiles = $doc->createElement('tiles',$numTilesLevel);
			$xml_properties->appendChild($xml_tiles);

			$xml_xtiles = $doc->createElement('xtiles',$x);
			$xml_properties->appendChild($xml_xtiles);

			$xml_ytiles = $doc->createElement('ytiles',$y);
			$xml_properties->appendChild($xml_ytiles);

			$xml_tilesize = $doc->createElement('tilesize',$tileSize);
			$xml_properties->appendChild($xml_tilesize);

			$xml_chart->appendChild($xml_properties);
			$xml_charts->appendChild($xml_chart);
			
			echo"success $val<br>";
			flush();
		}
		else {
			echo"missing $val<br>";
			flush();
		}
	}
	$doc->appendChild($xml_charts);
	echo $doc->saveXML();
	$doc->save('nga-charts-image-props-misc.xml');
#}
?>