<?php
// ### Start the session
session_start();

#print_r($_SESSION); exit;
// ### First let's see if the user is logged-in and if not redirect to the login page
if(!$_SESSION['wp-user']['id']) {
	header('Location: http://opencpn.info/en/nga-charts-edit');
	exit;
}
if($_COOKIE['ChartNumber']) {
	header('Location: http://opencpn.info/en/nga-charts-edit-active');
	exit;
}
if(!$_GET) exit;

$id = trim(strip_tags($_GET['no']));
if(is_numeric($id)) {
	$cook = setcookie("ChartNumber", $id, 0, "/", "opencpn.info", 0, 0);
	$_SESSION['charts'][$id] = array();
	$_SESSION['charts'][$id]['chartID'] = $id;
	$_SESSION['charts'][$id]['sessionID'] = session_id();
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>NGA Chart <?php print $id;?> - Prerequisites Check</title>
		<script type="text/javascript">window.location.replace('http://opencpn.info/nga/calibrate.php');</script>
	</head>
	<body onload="document.getElementById('js').style.display = 'none';">
		<p id="js" style="margin: 10px auto; text-align: center;">If you see this text here, it means that you have JavaScript disabled in your browser.<br />Please enable JS in your browser, as it is mandatory - thanks!</p>
		<?php
			if(!$_COOKIE['PHPSESSID']) print '<p id="cook" style="margin: 10px auto;">You have to accept Cookies - at least for the actual session.</p>';
		?>
	</body>
</html>