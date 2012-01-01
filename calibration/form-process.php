<?php
session_start();
$sessID = session_id();
$ret = '';
$db_errors = '';

$cid = strip_tags($_POST['chartID']);
$sid = strip_tags($_POST['sessionID']);
if($sessID != $sid) $ret = "Session ID does not match!";
else {
	foreach($_POST as $k => $v) {
		$params[$k] = trim(strip_tags($v));
		$ret .= $params[$k]."<br />";
	}
}

// ### Connect to the DB
$link = mysql_connect('localhost', 'gunther', 'tatJana2603');
if (!$link) {
    die('Connection failed: ' . mysql_error());
}
$db_selected = mysql_select_db('usr_web12_1', $link);
if (!$db_selected) {
    die ('Cannot use DB: ' . mysql_error());
}

// The saving starts here =====================================================================================================
if (isset($_POST['chartID'])) //A very stupid check whether we actually should save something
{
	//load all data, so we can fill in what the form does not send back.
	$result = mysql_query('SELECT * FROM ocpn_nga_charts_with_params WHERE number = "'.$cid.'"');
	$chart = mysql_fetch_array($result);
	mysql_free_result($result);
	if ($chart['prerotate'] == 90 || $chart['prerotate'] == 270) {
		$hlp = $chart['width'];
		$chart['width'] = $chart['height'];
		$chart['height'] = $hlp;
	}
	//timestamp
	$timestamp = time();
	//find the current KAP
	$result = mysql_query('SELECT kap_id, scale, title FROM ocpn_nga_kap WHERE active = 1 AND is_main = 1 AND number = '.$cid.' LIMIT 0,1;');
	$kap = mysql_fetch_array($result);
	mysql_free_result($result);
	//Update ocpn_nga_charts
	mysql_query('UPDATE ocpn_nga_charts SET status_id = '.undef2null($params['status']).',bsb_chf = '.str2db($params['chart_type'], true).', bsb_chf_other = '.str2db($params['chart_type_other'], true).', status_other = '.str2db($params['status_other'], true).' WHERE number = '.$cid);
	if(mysql_errno() !== 0) $db_errors .= mysql_errno() . ": " . mysql_error() . "\n";
	//Invalidate the existing KAP info
	mysql_query('UPDATE ocpn_nga_kap SET active = 0 WHERE kap_id = '.$kap['kap_id']);
	if(mysql_errno() !== 0) $db_errors .= mysql_errno() . ": " . mysql_error() . "\n";
	//Insert the new data
	$query_fmt = 'INSERT INTO ocpn_nga_kap (number, is_main, status_id, locked, scale, title, NU, GD, PR, PP, UN, SD, DTMx, DTMy, DTMdat, changed, changed_by, active, bsb_type, GD_other, PR_other, UN_other, SD_other, DTMdat_other, locked_by, comments, noPP, noDTM, gpx)
		VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, FROM_UNIXTIME(%s), %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)';

	if ($_FILES['gpx']['size'] > 0)
	{
		// get contents of a file into a string
		$handle = fopen( $_FILES['gpx']['tmp_name'], "r");
		$gpx_str = '\''.mysql_real_escape_string(fread($handle, $_FILES['gpx']['size'])).'\'';
		fclose($handle);
	}
	else
	{
		$gpx_str = 'NULL';
	}
	//: Some values are not clear, so we will "invent" them for now
	//PP is not clear from the data sent from the form
	$pp_fake_hemi = 'N'; //TODO: will the form be extended or should we "invent" it here from the chart data - not 100% safe to do, but would almost be
	if ($params['pp_deg'] == -9999 || $params['pp_deg'] === '') //if not even degree is set, let's assume PP is totally incorrect or not entered at all
		$pp = 'NULL';
	else {
		if ($params['pp_min'] == -9999 || $params['pp_min'] === '')
			$ppmin = 0;
		else
			$ppmin = $params['pp_min'];
		$pp = deg2dbl($params['pp_deg'], $ppmin, 0, $pp_fake_hemi);
	}
	if (isset($params['noPP']))
	{
		$params['noPP'] = 1;
		$pp = 'NULL';
	}
	else
		$params['noPP'] = 'NULL';
	if (isset($params['noDTM']))
	{
		$params['noDTM'] = 1;
		$params['datum_adj_x'] = '';
		$params['datum_adj_y'] = '';
		$params['datum_adj_we'] = '';
		$params['datum_adj_ns'] = '';
		$params['datum_correction'] = '';
		$params['datum_correction_other'] = '';
	}
	else
		$params['noDTM'] = 'NULL';
	
	$query = sprintf($query_fmt, $cid, 1, undef2null($params['status']), 'NULL', $chart['scale'], str2db($chart['title']), str2db($cid), str2db(empty2null($params['datum'])), str2db(empty2null($params['projection'])),$pp , str2db(empty2null($params['soundings'])), str2db(empty2null($params['soundings_datum'])), min2dbl($params['datum_adj_x'], 0, $params['datum_adj_we']), min2dbl($params['datum_adj_y'], 0, $params['datum_adj_ns']), str2db($params['datum_correction'], true), $timestamp, $_SESSION['wp-user']['id'], 1,  str2db('BASE'), str2db($params['datum_other'], true), str2db($params['projection_other'], true), str2db($params['soundings_other'], true), str2db($params['soundings_datum_other'], true), str2db($params['datum_correction_other'], true), 'NULL', str2db($params['comment'], true), $params['noPP'], $params['noDTM'], $gpx_str);
	mysql_query($query);
	if(mysql_errno() !== 0) $db_errors .= mysql_errno() . ": " . mysql_error() . "\n";
	$new_kap_id = mysql_insert_id($link);
	//Invalidate REF points
	mysql_query('UPDATE ocpn_nga_kap_point SET active = 0 WHERE point_type=\'REF\' AND kap_id = '.$kap['kap_id']);
	if(mysql_errno() !== 0) $db_errors .= mysql_errno() . ": " . mysql_error() . "\n";
	//Insert new points
	$query_fmt = 'INSERT INTO ocpn_nga_kap_point (kap_id, latitude, longitude, x, y, point_type, created_by, created, sequence, active) VALUES (%s, %s, %s, %s, %s, %s, %s, FROM_UNIXTIME(%s), %s, %s)';
	//SW and NE corners are a special case - we save them even in case we have just LAT/LON and no coordinates
	if (($params['xcoordh_sw'] !== '' && $params['ycoordh_sw'] !== '' && $params['xcoordh_sw'] != 25 && $params['ycoordh_sw'] != $chart['height'] - 25) || deg2dbl($params['lat_deg_sw'], $params['lat_min_sw'], $params['lat_sec_sw'], $params['lat_ns_sw']) != 'NULL' || deg2dbl($params['lng_deg_sw'], $params['lng_min_sw'], $params['lng_sec_sw'], $params['lng_we_sw']) != 'NULL') 
	{
		$latitude =  deg2dbl($params['lat_deg_sw'], $params['lat_min_sw'], $params['lat_sec_sw'], $params['lat_ns_sw']);
		$longitude =  deg2dbl($params['lng_deg_sw'], $params['lng_min_sw'], $params['lng_sec_sw'], $params['lng_we_sw']);
		$query = sprintf($query_fmt, $new_kap_id, $latitude, $longitude, empty2null($params['xcoordh_sw']), empty2null($params['ycoordh_sw']), str2db('REF'), $_SESSION['wp-user']['id'], $timestamp, 1, 1);
		mysql_query($query);
		if(mysql_errno() !== 0) $db_errors .= mysql_errno() . ": " . mysql_error() . "\n";
	}
	if ($params['xcoordh_nw'] !== '' && $params['ycoordh_nw'] !== '' && $params['xcoordh_nw'] != 25 && $params['ycoordh_sw'] != 25)
	{
		$latitude =  deg2dbl($params['lat_deg_ne'], $params['lat_min_ne'], $params['lat_sec_ne'], $params['lat_ns_ne']);
		$longitude =  deg2dbl($params['lng_deg_sw'], $params['lng_min_sw'], $params['lng_sec_sw'], $params['lng_we_sw']);
		$query = sprintf($query_fmt, $new_kap_id, $latitude, $longitude, $params['xcoordh_nw'], $params['ycoordh_nw'], str2db('REF'), $_SESSION['wp-user']['id'], $timestamp, 2, 1);
		mysql_query($query);
		if(mysql_errno() !== 0) $db_errors .= mysql_errno() . ": " . mysql_error() . "\n";
	}
	if (($params['xcoordh_ne'] !== '' && $params['ycoordh_ne'] !== '' && $params['xcoordh_ne'] != $chart['width'] - 25 && $params['ycoordh_ne'] != 25) || deg2dbl($params['lat_deg_ne'], $params['lat_min_ne'], $params['lat_sec_ne'], $params['lat_ns_ne']) != 'NULL' || deg2dbl($params['lng_deg_ne'], $params['lng_min_ne'], $params['lng_sec_ne'], $params['lng_we_ne']) != 'NULL') 
	{
		$latitude =  deg2dbl($params['lat_deg_ne'], $params['lat_min_ne'], $params['lat_sec_ne'], $params['lat_ns_ne']);
		$longitude =  deg2dbl($params['lng_deg_ne'], $params['lng_min_ne'], $params['lng_sec_ne'], $params['lng_we_ne']);
		$query = sprintf($query_fmt, $new_kap_id, $latitude, $longitude, empty2null($params['xcoordh_ne']), empty2null($params['ycoordh_ne']), str2db('REF'), $_SESSION['wp-user']['id'], $timestamp, 3, 1);
		mysql_query($query);
		if(mysql_errno() !== 0) $db_errors .= mysql_errno() . ": " . mysql_error() . "\n";
	}
	if ($params['xcoordh_se'] !== '' && $params['ycoordh_se'] !== '' && $params['xcoordh_se'] != $chart['width'] - 25 && $params['ycoordh_se'] != $chart['height'] - 25)
	{
		$latitude =  deg2dbl($params['lat_deg_sw'], $params['lat_min_sw'], $params['lat_sec_sw'], $params['lat_ns_sw']);
		$longitude =  deg2dbl($params['lng_deg_ne'], $params['lng_min_ne'], $params['lng_sec_ne'], $params['lng_we_ne']);
		$query = sprintf($query_fmt, $new_kap_id, $latitude, $longitude, $params['xcoordh_se'], $params['ycoordh_se'], str2db('REF'), $_SESSION['wp-user']['id'], $timestamp, 4, 1);
		mysql_query($query);
		if(mysql_errno() !== 0) $db_errors .= mysql_errno() . ": " . mysql_error() . "\n";
	}
}
// The saving ends here =====================================================================================================

// Helper functions declarations ============================================================================================

// LAT or LON in human format to double
function deg2dbl($deg, $min, $sec, $hemi)
{
	if ($hemi == '-9999' || $hemi === '' || $deg === '' || $min === '' || $sec === '')
		return 'NULL'; //If something is not set, we consider the value invalid
	if (strtoupper($hemi) == 'S' || strtoupper($hemi) == 'W')
		$hemi = -1;
	else
		$hemi = 1;
	return $hemi * ($deg + $min / 60 + $sec / 3600);
}

// Datum shift in human format to double
function min2dbl($min, $sec, $dir)
{
	if ($min === '' || $sec === '')
		return 'NULL';
	if(!is_numeric($dir))
 		if (strtoupper($dir) == 'S' || strtoupper($dir) == 'W')
			$dir = -1;
		else
			$dir = 1;
	return $dir * ($min + $sec / 60);
}

// undefined value (-9999) to null
function undef2null($value)
{
	if ($value == -9999)
		return 'NULL';
	return $value;
}

// empty value to null
function empty2null($value)
{
	if ($value === '')
		return 'NULL';
	return $value;
}

// string value to database string, with the possibility to replace empty with NULL
function str2db($value, $empty2null = false)
{
	if (($empty2null && $value === '') || $value === 'NULL' || $value === '-9999')
		return 'NULL';
	return '\''.mysql_real_escape_string($value).'\'';
}

/*
#print_r($params);
$response = "
	<script type=\"text/javascript\">
		alert('Response sent!');
	</script>
";
*/
echo"$db_errors";
?>

