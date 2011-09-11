<?php
$ftp_server = "ftp.weizter.net";
$ftp_user_name = 'ftpuser';
$ftp_user_pass = 'password';

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}


// Verbindung aufbauen
$conn_id = ftp_connect($ftp_server);

// Login mit Benutzername und Passwort
$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);


$time_start = microtime_float();

$contents = ftp_nlist($conn_id, "/nga-charts/incoming");
#print_r($contents);


if($contents) {
	$cont_1 = array();
	$err_1 = array();
	foreach($contents as $key => $val) {
		$val = strtolower(trim($val));
		$suchmuster = '#/nga-charts/incoming/([0-9]+)\.jpg#';
		preg_match($suchmuster, $val, $match);
		if($match[1] && $match[1] > 0) {
			$id = $match[1];
			$res = ftp_size($conn_id, $val);
			if ($res != -1) {
				$cont_1[$id] = $res;
			}
		}
		else {
			array_push($err_1, $val);
		}
	}
}

$time_end = microtime_float();
$time = $time_end - $time_start;

if($time < 1.0) {
	sleep(1.0);
}

$contents = ftp_nlist($conn_id, "/nga-charts/incoming");

if($contents) {
	$cont_2 = array();
	$err_2 = array();
	foreach($contents as $key => $val) {
		$val = strtolower(trim($val));
		$suchmuster = '#/nga-charts/incoming/([0-9]+)\.jpg#';
		preg_match($suchmuster, $val, $match);
		if($match[1] && $match[1] > 0) {
			$id = $match[1];
			$res = ftp_size($conn_id, $val);
			if ($res != -1) {
				$cont_2[$id] = $res;
			}
		}
		else {
			array_push($err_2, $val);
		}
	}
}


$diff = array_diff_assoc($cont_1, $cont_2);
$moved = array();
foreach($cont_1 as $key => $val) {
	if(!array_key_exists($key, $diff)) {
		$gp = '/nga-charts/incoming/'.$key.'.jpg => /nga-charts/'.$key.'/'.$key.'.jpg';
		echo"$gp<br>";
		$rename = ftp_rename($conn_id, '/nga-charts/incoming/'.$key.'.jpg', '/nga-charts/'.$key.'/'.$key.'.jpg');
		if($rename) array_push($moved, $key);
	}
}



$link = mysql_connect('localhost', 'gunther', 'password');
if (!$link) {
    die('keine Verbindung möglich: ' . mysql_error());
}

$db_selected = mysql_select_db('usr_web12_1', $link);
if (!$db_selected) {
    die ('Kann foo nicht benutzen : ' . mysql_error());
}

foreach($moved as $v) {
	$number = (int)trim($v);
	#if($number < 20000 || $number > 29999) continue;
	$contents = ftp_nlist($conn_id, '/nga-charts/'.$number.'/');
	if($contents) {
		$val = str_replace('/nga-charts/', 'ftp://ftp.weizterfish.com/', $contents[0]);
		$size = ftp_size($conn_id, '/nga-charts/'.$number.'/'.$number.'.jpg');
		if($size && $size != -1) {
			$sql = 'UPDATE ocpn_nga_charts_links SET image_fullsize="'.urlencode($val).'", image_fullsize_filesize='.$size.', status=2 WHERE number="'.$number.'"';
			$retval = mysql_query($sql, $link);
			if(!$retval) {
				die('Could not update data: ' . mysql_error());
			}
		}
		else echo"Error in size at $number<br>";
	}
}

// Verbindung schließen
ftp_close($conn_id);
mysql_close($link);
?> 
