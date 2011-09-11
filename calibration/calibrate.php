<?php
// ### Start the session
session_start();

// ### First let's see if the user is logged-in and if not redirect to the login page
if(!$_SESSION['wp-user']['id']) {
	header('Location: http://opencpn.info/en/nga-charts-edit');
	exit;
}
if(!isset($_COOKIE['chartnumber'])) {
	header('Location: http://opencpn.info/en/nga-charts-status');
	exit;
}
if(isset($_COOKIE['chartnumber']) && isset($_SESSION['charts'][$_COOKIE['chartnumber']])) {
	if($_COOKIE['chartnumber'] != $_SESSION['charts'][$_COOKIE['chartnumber']]['chartID']) {
		header('Location: http://opencpn.info/en/nga-charts-status');
		exit;
	}
}

$chart_id = $_COOKIE['chartnumber'];

// ### Connect to the DB
$link = mysql_connect('localhost', 'gunther', 'password');
if (!$link) {
    die('Connection failed: ' . mysql_error());
}
$db_selected = mysql_select_db('usr_web12_1', $link);
if (!$db_selected) {
    die ('Cannot use DB: ' . mysql_error());
}

// ### Check if chart is actually LOCKED by another user
$result = mysql_query('SELECT locked, locked_by, changed, changed_by FROM ocpn_nga_kap WHERE number = "'.$chart_id.'"');
$row = mysql_fetch_row($result);
$locked = $row[0];
$locked_by = $row[1];
$changed = strtotime($row[2]);
$changed_by = $row[3];
mysql_free_result($result);
if(!is_null($locked)) {
	if($locked_by != $_SESSION['wp-user']['id']) {
		$cook = setcookie("chartnumber", "", time() - 3600, "/", "opencpn.info", 0, 0);
		print '<div style="margin: 10px auto; text-align: center;"><p>The chart is currently locked.<br />This means another user is already editing the chart.<br />Please go back and select another chart to edit or retry in a couple of minutes.</p><p>Many thanks for your support!<br />The OpenCPN NGA Chart team</p></div>';
		exit;
	}
}

$result = mysql_query('UPDATE ocpn_nga_kap SET locked = NOW(), locked_by = "'.$_SESSION['wp-user']['id'].'" WHERE number = "'.$chart_id.'"');
if(!result) echo"Error on locking chart!";

$result = mysql_query('SELECT * FROM ocpn_nga_charts_with_params WHERE number = "'.$chart_id.'"');
$chart = mysql_fetch_array($result);
mysql_free_result($result);

$status = array();
$result = mysql_query('SELECT status_id, description FROM ocpn_nga_status WHERE status_usage="CHART"');
while ($row = mysql_fetch_array($result)) {
    $status[$row[0]] = $row[1];  
}
mysql_free_result($result);

$chartCount = count($chart);

$checked = '<img src="images/checked.png" />';
$questionmark = '<img src="images/questionmark.png" />';
$unchecked = '<img src="images/unchecked.png" />';
$initResetInput = '<input type="button" disabled="disabled" class="reset_but_input" value="Reset" onclick="resetInitial($(this).attr(\'id\'), $(this).attr(\'title\'),  $(this).parent(\'td\').next().next().find(\'input\').attr(\'id\'));" />';
$initResetSelect = '<input type="button" disabled="disabled" class="reset_but_select" value="Reset" onclick="resetInitial($(this).attr(\'id\'), $(this).attr(\'title\'), $(this).parent(\'td\').next().next().find(\'select\').attr(\'id\'));" />';
$initResetAdjGD = '<input type="button" disabled="disabled" class="reset_but_adj_gd" value="Reset" onclick="resetInitial($(this).attr(\'id\'), $(this).attr(\'title\'), \'adjust_gd\');" />';
$initResetCoordsSW = '<input type="button" disabled="disabled" class="reset_but_coords_sw" value="Reset" onclick="resetInitial($(this).attr(\'id\'), $(this).attr(\'title\'), \'coords_sw\');" />';
$initResetCoordsNE = '<input type="button" disabled="disabled" class="reset_but_coords_ne" value="Reset" onclick="resetInitial($(this).attr(\'id\'), $(this).attr(\'title\'), \'coords_ne\');" />';
$initResetCorner = '<input type="button" disabled="disabled" class="reset_but_corner" value="Reset" onclick="resetInitial($(this).attr(\'id\'), $(this).attr(\'title\'), \'corner_\'+$(this).parent(\'td\').prev().find(\'input\').attr(\'id\').substr(7, 2));" />';
$initResetComment = '<input type="button" disabled="disabled" class="reset_but_comment" value="Reset" onclick="resetInitial($(this).attr(\'id\'), $(this).attr(\'title\'), $(this).parent().prev().attr(\'id\'));" />';
?>


<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>NGA Chart <?php print $chart_id; ?> - Edit</title>
    <link type="text/css" href="css/ui-lightness/jquery-ui-1.8.14.custom.css" rel="stylesheet" />
    <link type="text/css" href="css/cal_default.css" rel="stylesheet" />
	<link type="text/css" href="css/jquery.validity.css" rel="stylesheet" />
    <script type="text/javascript" src="js/jquery-1.5.1.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui-1.8.14.custom.min.js"></script>
	<script type="text/javascript" src="js/json2.min.js"></script>
	<script type="text/javascript" src="js/jquery.hoverIntent.minified.js"></script>
	<script type="text/javascript" src="js/jquery.scrollTo-1.4.2-min.js"></script>
	<script type="text/javascript" src="js/jquery.validity.pack.js"></script>
	<script type="text/javascript" src="js/jquery.cookie.js"></script>
	<script type="text/javascript" src="js/jquery.bt.min.js"></script>
	<script type="text/javascript" src="js/calibrating-functions.js"></script>
	<script type="text/javascript">
		<?php print "var lastEdit = $changed;\n"; ?>
		var id = <?php print $chart_id; ?>;
		var chart = new Object();
		chart['id'] = id;
		
<?php
// ### Define the JS variables
foreach($chart as $k => $v) {
	if(is_string($k)) {
		if(isset($v)) {
			if(is_numeric($v)) {
				print "\t\tchart['".$k."'] = $v;\n";
				print "\t\tchart['".$k."_initial'] = $v;\n";
			}
			else {
				if($k == 'status_other' || $k == 'comments') {
					$nl_v = explode("\n", $v);
					if(!empty($nl_v)) {
						foreach($nl_v as $key => $line) {
							if($key == 0) $v = trim($line);
							else $v .= "\\n".trim($line);
						}
						#$v = str_replace('\'', '%27', $v);
					}
				}
				$v = trim(htmlspecialchars($v, ENT_QUOTES, "UTF-8"));
				print "\t\tchart['".$k."'] = '$v';\n";
				print "\t\tchart['".$k."_initial'] = '$v';\n";
			}
		}
		else {
			print "\t\tchart['".$k."'] = null;\n";
		}
	}
}
?>
		var chart_width = chart['width'];
		var chart_height = chart['height'];
		var corner_src = corner_base + id + "/" + id + "_" + corner + ".png";
	</script>
</head>

<body onunload="deleteSession();">
    <div id="page_wrapper">
        <form name="chart_calibration_form" id="chart_calibration_form" action="">
		<div id="top_container">
			<div id="checklist">
				<p class="nm">Checklist for chart: <strong><?php print $chart_id; ?></strong><br />The checklist shows which values are already present in the database.<br /><small>(The italicized entries can not be changed! If you think there is an error, please fill in the comment box at the end of the page - thanks.)</small></p>
				<table id="cecklist_table" cellspacing="0" cellpadding="0">
					<colgroup><col width="280" /><col width="670" /><col width="*" /></colgroup>
					<tr>
						<td colspan="3" class="headline"><h4>Chart data (preserved)</h4></td>
					</tr>
					<tr>
						<td class="italic">Title: </td><td><?php print $chart['title']; ?></td><td><?php if($chart['title']) print $checked; else print $unchecked;?></td>
					</tr>
					<tr>
						<td class="italic">Scale: </td><td>1:<?php print number_format($chart['scale'], 0, '.', ','); ?></td><td><?php if($chart['scale']) print $checked; else print $unchecked;?></td>
					</tr>
					<tr>
						<td class="italic">Edition: </td><td><?php print $chart['edition']; ?></td><td><?php if($chart['edition']) print $checked; else print $unchecked;?></td>
					</tr>
					<tr>
						<td class="italic">Edition Date: </td><td><?php print $chart['date']; ?></td><td><?php if($chart['date']) print $checked; else print $unchecked;?></td>
					</tr>
					<tr>
						<td class="italic">Corrected to NTM: </td><td><?php print $chart['correction']; ?></td><td><?php if($chart['correction']) print $checked; else print $unchecked;?></td>
					</tr>
					<tr class="hl" title="a1">
						<td colspan="3" class="headline"><h4>General chart and header data</h4></td>
					</tr>
					<tr>
						<td>Chart Status:</td><td><?php print $status[$chart['status_id']]; if($status[$chart['status_id']] == 'other') print " => {$chart['status_other']}"; ?></td><td><?php if(isset($chart['status_id'])) print $checked; else print $unchecked;?></td>
					</tr>
					<tr>
						<td>Chart Type:</td><td><?php print $chart['bsb_chf']; if($chart['bsb_chf'] == 'other') print " => {$chart['bsb_chf_other']}"; ?></td><td><?php if($chart['bsb_chf'] && $chart['bsb_chf'] != 'unknown') print $checked; elseif($chart['bsb_chf'] && $chart['bsb_chf'] == 'unknown') print $questionmark; else print $unchecked;?></td>
					</tr>
					<tr>
						<td>Scale at:</td><td><?php if(isset($chart['PPdeg']) && isset($chart['PPmin'])) print $chart['PPdeg'].'<sup>°</sup>&nbsp;'.$chart['PPmin']."<sup>'</sup>"; elseif($chart['noPP']) print 'no values/ info given on chart'; ?></td><td><?php if(isset($chart['PPdeg']) && isset($chart['PPmin']) || $chart['noPP']) print $checked; else print $unchecked;?></td>
					</tr>
					<tr>
						<td>Projection: </td><td><?php print $chart['PR']; ?></td><td><?php if($chart['PR'] && $chart['PR'] != 'unknown') print $checked; elseif($chart['PR'] && $chart['PR'] == 'unknown') print $questionmark; else print $unchecked;?></td>
					</tr>
					<tr>
						<td>Datum: </td><td><?php print $chart['GD']; ?></td><td><?php if($chart['PR'] && $chart['GD'] != 'unknown') print $checked; elseif($chart['GD'] && $chart['GD'] == 'unknown') print $questionmark; else print $unchecked;?></td>
					</tr>
					<?php
					if($chart['GD'] && $chart['GD'] != 'WGS84') {
					if($chart['noDTM']) {print '
					<tr class="dat_adj">
						<td>Adjustments to Chart Datum:</td><td>'; if($chart["noDTM"]) print "no DATUM NOTE given on chart"; print'</td><td>'; if($chart["noDTM"]) print $checked; else print $unchecked; print'</td>
					</tr>';}
					else { print'
					<tr class="dat_adj">
						<td>Adjustments to Chart Datum</td><td>&nbsp;</td><td>&nbsp;</td>
					</tr>';
					print '
					<tr class="dat_adj">
						<td>Corrects Chart Datum to:</td><td>';print "{$chart['DTMdat']}</td><td>"; print $checked; print"</td>
					</tr>";
					print '
					<tr class="dat_adj">
						<td>Adjustment north-/southward:</td><td>';print "{$chart['DTMy']} <sup>'</sup> "; if($chart['DTMy_dir'] == 1) print'NORTHWARD'; else print "SOUTHWARD"; print"</td><td>"; print $checked; print"</td>
					</tr>";
					print '
					<tr class="dat_adj">
						<td>Adjustment east-/westward:</td><td>';print "{$chart['DTMx']} <sup>'</sup> "; if($chart['DTMx_dir'] == 1) print'EASTWARD'; else print "WESTWARD"; print"</td><td>"; print $checked; print"</td>
					</tr>";
					}
					}
					?>
					<tr>
						<td>Soundings in: </td><td><?php print $chart['UN']; ?></td><td><?php if($chart['UN'] && $chart['UN'] != 'unknown') print $checked; elseif($chart['UN'] && $chart['UN'] == 'unknown') print $questionmark; else print $unchecked;?></td>
					</tr>
					<tr>
						<td>Soundings Datum: </td><td><?php print $chart['SD']; ?></td><td><?php if($chart['SD'] && $chart['SD'] != 'unknown') print $checked; elseif($chart['SD'] && $chart['SD'] == 'unknown') print $questionmark; else print $unchecked;?></td>
					</tr>
					<tr class="hl" title="a2">
						<td colspan="3" class="headline"><h4>SW and NE coordinates</h4></td>
					</tr>
					<tr>
						<td>SW Lat: </td><td><?php if(isset($chart['Sdeg'])) print $chart['Sdeg'].'<sup>°</sup>'; ?><?php if(isset($chart['Smin'])) print $chart['Smin']."<sup>'</sup>"; ?><?php if(isset($chart['Ssec'])) print $chart['Ssec'].'<sup>"</sup>'; ?> <?php if(isset($chart['Snhemi'])) {if($chart['Snhemi'] > 0) print 'N'; else print 'S';} ?></td><td><?php if(isset($chart['Sdeg']) && isset($chart['Smin']) && isset($chart['Ssec']) && isset($chart['Snhemi'])) print $checked; else print $unchecked;?></td>
					</tr>
					<tr>
						<td>SW Lng: </td><td><?php if(isset($chart['Wdeg'])) print $chart['Wdeg'].'<sup>°</sup>'; ?><?php if(isset($chart['Wmin'])) print $chart['Wmin']."<sup>'</sup>"; ?><?php if(isset($chart['Wsec'])) print $chart['Wsec'].'<sup>"</sup>'; ?> <?php if(isset($chart['Wehemi'])) {if($chart['Wehemi'] > 0) print 'E'; else print 'W';} ?></td><td><?php if(isset($chart['Wdeg']) && isset($chart['Wmin']) && isset($chart['Wsec']) && isset($chart['Wehemi'])) print $checked; else print $unchecked;?></td>
					</tr>
					<tr>
						<td>NE Lat: </td><td><?php if(isset($chart['Ndeg'])) print $chart['Ndeg'].'<sup>°</sup>'; ?><?php if(isset($chart['Nmin'])) print $chart['Nmin']."<sup>'</sup>"; ?><?php if(isset($chart['Nsec'])) print $chart['Nsec'].'<sup>"</sup>'; ?> <?php if(isset($chart['Nnhemi'])) {if($chart['Nnhemi'] > 0) print 'N'; else print 'S';} ?></td><td><?php if(isset($chart['Ndeg']) && isset($chart['Nmin']) && isset($chart['Nsec']) && isset($chart['Nnhemi'])) print $checked; else print $unchecked;?></td>
					</tr>
					<tr>
						<td>NE Lng: </td><td><?php if(isset($chart['Edeg'])) print $chart['Edeg'].'<sup>°</sup>'; ?><?php if(isset($chart['Emin'])) print $chart['Emin']."<sup>'</sup>"; ?><?php if(isset($chart['Esec'])) print $chart['Esec'].'<sup>"</sup>'; ?> <?php if(isset($chart['Eehemi'])) {if($chart['Eehemi'] > 0) print 'E'; else print 'W';} ?></td><td><?php if(isset($chart['Edeg']) && isset($chart['Emin']) && isset($chart['Esec']) && isset($chart['Eehemi'])) print $checked; else print $unchecked;?></td>
					</tr>
					<tr class="hl" title="a3">
						<td colspan="3" class="headline"><h4>Calibrating chart corners (REF points)</h4></td>
					</tr>
					<tr>
						<td>SW corner: </td><td><?php if(isset($chart['Xsw'])) print '('.$chart['Xsw'].'|'.$chart['Ysw'].')'; ?></td><td><?php if(isset($chart['Xsw']) && isset($chart['Ysw'])) print $checked; else print $unchecked;?></td>
					</tr>
					<tr>
						<td>NW corner: </td><td><?php if(isset($chart['Xnw'])) print '('.$chart['Xnw'].'|'.$chart['Ynw'].')'; ?></td><td><?php if(isset($chart['Xnw']) && isset($chart['Ynw'])) print $checked; else print $unchecked;?></td>
					</tr>
					<tr>
						<td>NE corner: </td><td><?php if(isset($chart['Xne'])) print '('.$chart['Xne'].'|'.$chart['Yne'].')'; ?></td><td><?php if(isset($chart['Xne']) && isset($chart['Yne'])) print $checked; else print $unchecked;?></td>
					</tr>
					<tr>
						<td>SE corner: </td><td><?php if(isset($chart['Xse'])) print '('.$chart['Xse'].'|'.$chart['Yse'].')'; ?></td><td><?php if(isset($chart['Xse']) && isset($chart['Yse'])) print $checked; else print $unchecked;?></td>
					</tr>
				</table>
			</div>
			<div id="panel_tab"><a id="panel_tab_a" class="trigger" href="#" onclick="checklist();">▲</a></div>
		</div>
		
		<div id="validation_errors" class="validity-summary-container">
			<p>Here's a summary of the validation failures:</p>
			<ul></ul>
		</div>

		<div id="group_container">
			<h2 id="a1" class="trigger">
				<a class="trigger" href="#">General chart and header data</a>
				<p class="indicator">
					<img id="led_status" src="" alt="status led" title="" />
					<img id="led_chart_type" src="" alt="status led" title="" />
					<img id="led_scale_at" src="" alt="status led" title="" />
					<img id="led_projection" src="" alt="status led" title="" />
					<img id="led_datum" src="" alt="status led" title="" />
					<img id="led_datum_adjust" src="" alt="status led" title="" />
					<img id="led_soundings" src="" alt="status led" title="" />
					<img id="led_soundings_datum" src="" alt="status led" title="" />
				</p>
			</h2>
			<div class="toggle_container">
				<p class="block_center center"><a class="trigger" href="http://www.charts.noaa.gov/NGAViewer/<?php print $chart_id;?>.shtml" onclick="ngaViewer(this.href);"><img id="img_zl2" src="chartimages/thumbs/<?php print $chart_id;?>_zl2.jpg" onFocus="this.blur();" alt="chart image preview" title="Click on the image to open the chart in the NGA-Viewer at NOAA website (will be opened in a new window/ tab)" /></a></p>
				
				<table id="header_data_tab" width="96%" cellspacing="2" cellpadding="0" style="margin: 0 auto;">
					<colgroup><col width="75" /><col width="250" /><col width="250" /><col width="*" /></colgroup>
					<tr>
						<td class="vtop"><?php print $initResetSelect;?></td>
						<td class="vtop_p">Chart Status:</td>
						<td class="vtop_p">
							<select name="status" id="status" size="1" onchange="selChanged(this.id); chart['status_id'] = this.value;" title="Please select the most appropriate variant as only single selection is allowed. If you are going to choose 'Broken in other way', please enter the reason in the appearing text box on the right - thanks!">
								<option value="-9999">&lt;select&gt;</option>
								<?php
								foreach($status as $k => $v) {
									print '<option value="'.$k.'"';
									if($chart['status_id'] == $k) print' selected="selected"';
									print '>'.$v.'</option>';
								}
								?>
							</select>
						</td>
						<td class="hidden vtop_p"><textarea name="status_other" id="status_other" cols="40" rows="3" onchange="chart['status_other'] = this.value;"></textarea></td>
					</tr>
					<tr>
						<td><?php print $initResetSelect;?></td>
						<td>Chart Type:</td>
						<td>
							<select name="chart_type" id="chart_type" size="1" onchange="selChanged(this.id); chart['bsb_chf'] = this.value;" title="Please select the type of the chart. If you think the chart type is other than the listed options, choose 'other' and enter the type in the appearing text field on the right.">
								<option value="-9999">&lt;select&gt;</option>
								<option<?php if($chart['bsb_chf'] == 'GENERAL') print ' selected="selected"';?>>GENERAL</option>
								<option<?php if($chart['bsb_chf'] == 'COASTAL') print ' selected="selected"';?>>COASTAL</option>
								<option<?php if($chart['bsb_chf'] == 'HARBOR') print ' selected="selected"';?>>HARBOR</option>
								<option<?php if($chart['bsb_chf'] == 'INTERNATIONAL') print ' selected="selected"';?>>INTERNATIONAL</option>
								<option<?php if($chart['bsb_chf'] == 'SAILING') print ' selected="selected"';?>>SAILING</option>
								<option<?php if($chart['bsb_chf'] == 'IWW ROUTE') print ' selected="selected"';?>>IWW ROUTE</option>
								<option<?php if($chart['bsb_chf'] == 'SMALL CRAFT ROUTE') print ' selected="selected"';?>>SMALL CRAFT ROUTE</option>
								<option<?php if($chart['bsb_chf'] == 'other') print ' selected="selected"';?>>other</option>
								<option<?php if($chart['bsb_chf'] == 'unknown') print ' selected="selected"';?>>unknown</option>
							</select>
						</td>
						<td class="hidden"><input type="text" class="left" name="chart_type_other" id="chart_type_other" value="<?php if($chart['bsb_chf'] == 'other' && isset($chart['bsb_chf_other'])) print $chart['bsb_chf_other'];?>" onchange="chart['bsb_chf_other'] = this.value;" title="Enter the type of the chart<br />(5 - 50 characters).<br />Uppercase or lowercase does not matter." /></td>
					</tr>
					<tr>
						<td><?php print $initResetInput;?></td>
						<td>Scale 1:<?php if($chart['scale']) print $chart['scale'];?> at:</td>
						<td>
							<input type="hidden" disabled="disabled" value="<?php if($chart['scale']) print $chart['scale'];?>" name="scale" id="scale" size="7" disabled="disabled" />
							<input style="width: 26px;" type="text" value="<?php if(isset($chart['PPdeg'])) print $chart['PPdeg'];?>" name="pp_deg" id="pp_deg" title="Scale at degrees" size="1" maxlength="3" /><sup>°</sup>
							<input style="width: 26px;" type="text" class="digit2" value="<?php if(isset($chart['PPmin'])) print $chart['PPmin'];?>" name="pp_min" id="pp_min" title="Scale at minutes" size="1" maxlength="2" /><sup>'</sup>
						</td>
						<td class="">
							<input type="checkbox" name="noPP" id="noPP" value="1" onchange="cbChanged(this.id);" <?php if(isset($chart['noPP'])) print 'checked="checked" ';?> title="If you are sure that there are no latitude or longitude values for the 'scale at' position given on the chart, please check the checkbox."/><small>no values/ info given on chart</small>
							<!--<small><sup><strong>* </strong></sup> leave empty if no values given!</small>-->
						</td>
					</tr>
					<tr>
						<td><?php print $initResetSelect;?></td>
						<td>Projection:</td>
						<td>
							<select name="projection" id="projection" size="1" onchange="selChanged(this.id); chart['PR'] = this.value;" title="Please select the projection of the chart. If the projection is not listed, choose 'other' and enter the projection in the appearing text field on the right.">
								<option value="-9999">&lt;select&gt;</option>
								<option<?php if($chart['PR'] == 'MERCATOR') print ' selected="selected"';?>>MERCATOR</option>
								<option<?php if($chart['PR'] == 'TRANSVERSE MERCATOR') print ' selected="selected"';?>>TRANSVERSE MERCATOR</option>
								<option<?php if($chart['PR'] == 'LAMBERT CONFORMAL CONIC') print ' selected="selected"';?>>LAMBERT CONFORMAL CONIC</option>
								<option<?php if($chart['PR'] == 'GNOMONIC') print ' selected="selected"';?>>GNOMONIC</option>
								<option<?php if($chart['PR'] == 'POLYCONIC') print ' selected="selected"';?>>POLYCONIC</option>
								<option<?php if($chart['PR'] == 'other') print ' selected="selected"';?>>other</option>
								<option<?php if($chart['PR'] == 'unknown') print ' selected="selected"';?>>unknown</option>
							</select>
						</td>
						<td class="hidden"><input type="text" class="left" name="projection_other" id="projection_other" class="validate[optional,minSize[3]]" value="<?php if($chart['PR'] == 'other' && isset($chart['PR_other'])) print $chart['PR_other'];?>" onchange="chart['PR_other'] = this.value;" title="Enter the projection of the chart<br />(5 - 50 characters).<br />Uppercase or lowercase does not matter." /></td>
					</tr>
					<tr>
						<td><?php print $initResetSelect;?></td>
						<td>Datum:</td>
						<td>
							<select name="datum" id="datum" size="1" onchange="selChanged(this.id); chart['GD'] = this.value;" title="Please select the datum of the chart. If the datum is not listed, choose 'other' and enter the datum in the appearing text field on the right.<br /><br />If the datum is other than 'WGS84' an additional group of fields show up where to enter the 'Adjustments to Chart Datum' (called 'DATUM NOTE' on most charts).">
								<option value="-9999">&lt;select&gt;</option>
								<option<?php if($chart['GD'] == 'WGS84') print ' selected="selected"';?>>WGS84</option>
								<option<?php if($chart['GD'] == 'WGS72') print ' selected="selected"';?>>WGS72</option>
								<option<?php if($chart['GD'] == 'ED') print ' selected="selected"';?> value="ED">ED (European Datum)</option>
								<option<?php if($chart['GD'] == 'other') print ' selected="selected"';?>>other</option>
								<option<?php if($chart['GD'] == 'unknown') print ' selected="selected"';?>>unknown</option>
							</select>
						</td>
						<td class="hidden"><input type="text" class="left" name="datum_other" id="datum_other" value="<?php if($chart['GD'] == 'other' && isset($chart['GD_other'])) print $chart['GD_other'];?>" onchange="chart['GD_other'] = this.value;" title="Enter the datum of the chart<br />(5 - 50 characters).<br />Uppercase or lowercase does not matter." /></td>
					</tr>
					<tr class="datum_adjust_tr">
						<td><?php print $initResetAdjGD;?></td>
						<td>Adjustments to Chart Datum:</td>
						<td>
							&nbsp;
						</td>
						<td>
							<input type="checkbox" name="noDTM" id="noDTM" value="1" onchange="cbChanged(this.id);" <?php if(isset($chart['noDTM'])) print 'checked="checked" ';?> title="If you are sure that there is no information about any datum correction given on the chart, please check the checkbox." /><small>no DATUM NOTE given on chart</small>
						</td>
					</tr>
					<tr class="datum_adjust_tr">
						<td>&nbsp;</td>
						<td>Corrects Chart Datum to:</td>
						<td>
							<select name="datum_correction" id="datum_correction" size="1" onchange="selChanged(this.id); chart['DTMdat'] = this.value;">
								<option value="-9999">&lt;select&gt;</option>
								<option<?php if($chart['DTMdat'] == 'WGS84') print ' selected="selected"';?>>WGS84</option>
								<option<?php if($chart['DTMdat'] == 'WGS72') print ' selected="selected"';?>>WGS72</option>
								<option<?php if($chart['DTMdat'] == 'other') print ' selected="selected"';?>>other</option>
								<option<?php if($chart['DTMdat'] == 'unknown') print ' selected="selected"';?>>unknown</option>
							</select>
						</td>
						<td class="hidden"><input type="text" class="left" name="datum_correction_other" id="datum_correction_other" value="<?php if($chart['DTMdat'] == 'other' && isset($chart['DTMdat_other'])) print $chart['DTMdat_other'];?>" onchange="chart['DTMdat_other'] = this.value;" title="Enter the datum the chart is corrected to<br />(5 - 50 characters).<br />Uppercase or lowercase does not matter." /></td>
					</tr>
					<tr class="datum_adjust_tr">
						<td>&nbsp;</td>
						<td>Adjustment north-/southward:</td>
						<td>
							<input type="text" class="digits_4" name="datum_adj_y" id="datum_adj_y" title="Adjustment north-/southward" value="<?php if(isset($chart['DTMy_abs'])) printf('%.2f',$chart['DTMy_abs']); else print "0.0"?>" size="1" />'
							<select name="datum_adj_ns" id="datum_adj_ns" style="width: 150px;" size="1" onchange="selChanged(this.id); chart['DTMy_dir'] = this.value;">
								<option value="-9999">&lt;select&gt;</option>
								<option value="1"<?php if($chart['DTMy_dir'] == 1) print ' selected="selected"';?>>NORTHWARD</option>
								<option value="-1"<?php if($chart['DTMy_dir'] == -1) print ' selected="selected"';?>>SOUTHWARD</option>
							</select>
						</td>
						<td class="hidden">&nbsp;</td>
					</tr>
					<tr class="datum_adjust_tr">
						<td>&nbsp;</td>
						<td>Adjustment east-/westward:</td>
						<td>
							<input type="text" class="digits_4" name="datum_adj_x" id="datum_adj_x" title="Adjustment east-/westward" value="<?php if(isset($chart['DTMx_abs'])) printf('%.2f',$chart['DTMx_abs']); else print "0.0"?>" size="1" />'
							<select name="datum_adj_we" id="datum_adj_we" style="width: 150px;" size="1" onchange="selChanged(this.id); chart['DTMx_dir'] = this.value;">
								<option value="-9999">&lt;select&gt;</option>
								<option value="1"<?php if($chart['DTMx_dir'] == 1) print ' selected="selected"';?>>EASTWARD</option>
								<option value="-1"<?php if($chart['DTMx_dir'] == -1) print ' selected="selected"';?>>WESTWARD</option>
							</select>
						</td>
						<td class="hidden">&nbsp;</td>
					</tr>
					<tr>
						<td><?php print $initResetSelect;?></td>
						<td>Soundings Unit:</td>
						<td>
							<select name="soundings" id="soundings" size="1" onchange="selChanged(this.id); chart['UN'] = this.value;">
								<option value="-9999">&lt;select&gt;</option>
								<option<?php if($chart['UN'] == 'METERS') print ' selected="selected"';?>>METERS</option>
								<option<?php if($chart['UN'] == 'FEET') print ' selected="selected"';?>>FEET</option>
								<option<?php if($chart['UN'] == 'FATHOMS') print ' selected="selected"';?>>FATHOMS</option>
								<option<?php if($chart['UN'] == 'other') print ' selected="selected"';?>>other</option>
								<option<?php if($chart['UN'] == 'unknown') print ' selected="selected"';?>>unknown</option>
							</select>
						</td>
						<td class="hidden"><input type="text" class="left" name="soundings_other" id="soundings_other" value="<?php if($chart['UN'] == 'other' && isset($chart['UN_other'])) print $chart['UN_other'];?>" onchange="chart['UN_other'] = this.value;" title="Enter the soundings unit of the chart<br />(5 - 50 characters).<br />Uppercase or lowercase does not matter." /></td>
					</tr>
					<tr>
						<td><?php print $initResetSelect;?></td>
						<td>Soundings Datum:</td>
						<td>
							<select name="soundings_datum" id="soundings_datum" size="1" onchange="selChanged(this.id); chart['SD'] = this.value;">
								<option value="-9999">&lt;select&gt;</option>
								<option<?php if($chart['SD'] == 'CD') print ' selected="selected"';?>>CD</option>
								<option<?php if($chart['SD'] == 'HAT') print ' selected="selected"';?>>HAT</option>
								<option<?php if($chart['SD'] == 'HHWLT') print ' selected="selected"';?>>HHWLT</option>
								<option<?php if($chart['SD'] == 'LAT') print ' selected="selected"';?>>LAT</option>
								<option<?php if($chart['SD'] == 'LLW') print ' selected="selected"';?>>LLW</option>
								<option<?php if($chart['SD'] == 'LNT') print ' selected="selected"';?>>LNT</option>
								<option<?php if($chart['SD'] == 'MHHW') print ' selected="selected"';?>>MHHW</option>
								<option<?php if($chart['SD'] == 'MHW') print ' selected="selected"';?>>MHW</option>
								<option<?php if($chart['SD'] == 'MHWN') print ' selected="selected"';?>>MHWN</option>
								<option<?php if($chart['SD'] == 'MHWS') print ' selected="selected"';?>>MHWS</option>
								<option<?php if($chart['SD'] == 'MLLW') print ' selected="selected"';?>>MLLW</option>
								<option<?php if($chart['SD'] == 'MLLWN') print ' selected="selected"';?>>MLLWN</option>
								<option<?php if($chart['SD'] == 'MLLWS') print ' selected="selected"';?>>MLLWS</option>
								<option<?php if($chart['SD'] == 'MLW') print ' selected="selected"';?>>MLW</option>
								<option<?php if($chart['SD'] == 'MLWN') print ' selected="selected"';?>>MLWN</option>
								<option<?php if($chart['SD'] == 'MLWS') print ' selected="selected"';?>>MLWS</option>
								<option<?php if($chart['SD'] == 'MSL') print ' selected="selected"';?>>MSL</option>
								<option<?php if($chart['SD'] == 'MTL') print ' selected="selected"';?>>MTL</option>
								<option<?php if($chart['SD'] == 'other') print ' selected="selected"';?>>other</option>
								<option<?php if($chart['SD'] == 'unknown') print ' selected="selected"';?>>unknown</option>
							</select> 
						</td>
						<td class="hidden"><input type="text" class="left" name="soundings_datum_other" id="soundings_datum_other" value="<?php if($chart['SD'] == 'other' && isset($chart['SD_other'])) print $chart['SD_other'];?>" onchange="chart['SD_other'] = this.value;" title="Enter the soundings datum of the chart<br />(5 - 50 characters).<br />Uppercase or lowercase does not matter." /></td>
					</tr>
				</table>
			</div>
			
			<h2 id="a2" class="trigger"><a class="trigger" href="#">SW and NE coordinates</a><p class="indicator"><img id="led_coords_sw" src="" alt="green led" title="" /><img id="led_coords_ne" src="" alt="red led" title="" /></p></h2>
			<div class="toggle_container">
				<table width="1002" cellspacing="0" cellpadding="0">
					<tr>
						<td width="501" style="text-align: left; padding: 2px 0;">
							<img id="coord_sw" src="http://opencpn.xtr.cz/nga-charts/<?php print $chart_id;?>/<?php print $chart_id;?>_sw.png" width="500" onFocus="this.blur();" />
						</td>
						<td width="501" style="text-align: right; padding: 2px 0;">
							<img id="coord_ne" src="http://opencpn.xtr.cz/nga-charts/<?php print $chart_id;?>/<?php print $chart_id;?>_ne.png" width="500" onFocus="this.blur();" />
						</td>
					</tr>
					<tr>
						<td>
							<table id="coords_sw_tab" class="coords_tab1">
								<tr>
									<td colspan="5">
										Coordinates of the SW corner
									</td>
								</tr>
								<tr>
									<td>
										Lat: 
									</td>
									<td>
										<input type="text" class="" value="<?php if(isset($chart['Sdeg'])) print $chart['Sdeg']; ?>" name="lat_deg_sw" id="lat_deg_sw" title="Value for SW corner latitude degrees" size="2" maxlength="2" /><sup>°</sup>
									</td>
									<td>
										<input type="text" class="digit2" value="<?php if(isset($chart['Smin'])) print $chart['Smin']; ?>" name="lat_min_sw" id="lat_min_sw" title="Value for SW corner latitude minutes" size="2" maxlength="2" /><sup>'</sup>
									</td>
									<td>
										<input type="text" class="digit2" value="<?php if(isset($chart['Ssec'])) print $chart['Ssec']; ?>" name="lat_sec_sw" id="lat_sec_sw" title="Value for SW corner latitude seconds" size="2" maxlength="4" /><sup>"</sup>
									</td>
									<td>
										<select class="" name="lat_ns_sw" id="lat_ns_sw" size="1">
											<option value="-9999">&lt;select&gt;</option>
											<option<?php if(isset($chart['Snhemi']) && $chart['Snhemi'] > 0) print ' selected="selected"';?>>N</option>
											<option<?php if(isset($chart['Snhemi']) && $chart['Snhemi'] < 0) print ' selected="selected"';?>>S</option>
										</select>
									</td>
								</tr>
								<tr>
									<td>
										Lng: 
									</td>
									<td>
										<input type="text" value="<?php if(isset($chart['Wdeg'])) print $chart['Wdeg']; ?>" name="lng_deg_sw" id="lng_deg_sw" title="Value for SW corner longitude degrees" size="2" maxlength="3" /><sup>°</sup>
									</td>
									<td>
										<input type="text" class="digit2" value="<?php if(isset($chart['Wmin'])) print $chart['Wmin']; ?>" name="lng_min_sw" id="lng_min_sw" title="Value for SW corner longitude minutes" size="2" maxlength="2" /><sup>'</sup>
									</td>
									<td>
										<input type="text" class="digit2" value="<?php if(isset($chart['Wsec'])) print $chart['Wsec']; ?>" name="lng_sec_sw" id="lng_sec_sw" title="Value for SW corner longitude seconds" size="2" maxlength="4" /><sup>"</sup>
									</td>
									<td>
										<select name="lng_we_sw" id="lng_we_sw" size="1">
											<option value="-9999">&lt;select&gt;</option>
											<option<?php if(isset($chart['Wehemi']) && $chart['Wehemi'] > 0) print ' selected="selected"';?>>E</option>
											<option<?php if(isset($chart['Wehemi']) && $chart['Wehemi'] < 0) print ' selected="selected"';?>>W</option>
										</select>
									</td>
								</tr>
								<tr>
									<td colspan="5"><?php print $initResetCoordsSW;?></td>
								</tr>
							</table>
						</td>
						<td>
							<table id="coords_ne_tab" class="coords_tab1">
								<tr>
									<td colspan="5">
										Coordinates of the NE corner
									</td>
								</tr>
								<tr>
									<td>
										Lat: 
									</td>
									<td>
										<input type="text" value="<?php if(isset($chart['Ndeg'])) print $chart['Ndeg']; ?>" name="lat_deg_ne" id="lat_deg_ne" title="Value for NE corner latitude degrees" size="2" maxlength="2" /><sup>°</sup>
									</td>
									<td>
										<input type="text" class="digit2" value="<?php if(isset($chart['Nmin'])) print $chart['Nmin']; ?>" name="lat_min_ne" id="lat_min_ne" title="Value for NE corner latitude minutes" size="2" maxlength="2" /><sup>'</sup>
									</td>
									<td>
										<input type="text" class="digit2" value="<?php if(isset($chart['Nsec'])) print $chart['Nsec']; ?>" name="lat_sec_ne" id="lat_sec_ne" title="Value for NE corner latitude seconds" size="2" maxlength="4" /><sup>"</sup>
									</td>
									<td>
										<select name="lat_ns_ne" id="lat_ns_ne" size="1">
											<option value="-9999">&lt;select&gt;</option>
											<option<?php if(isset($chart['Nnhemi']) && $chart['Nnhemi'] > 0) print ' selected="selected"';?>>N</option>
											<option<?php if(isset($chart['Nnhemi']) && $chart['Nnhemi'] < 0) print ' selected="selected"';?>>S</option>
										</select>
									</td>
								</tr>
								<tr>
									<td>
										Lng: 
									</td>
									<td>
										<input type="text" value="<?php if(isset($chart['Edeg'])) print $chart['Edeg']; ?>" name="lng_deg_ne" id="lng_deg_ne" title="Value for NE corner longitude degrees" size="2" maxlength="3" /><sup>°</sup>
									</td>
									<td>
										<input type="text" class="digit2" value="<?php if(isset($chart['Emin'])) print $chart['Emin']; ?>" name="lng_min_ne" id="lng_min_ne" title="Value for NE corner longitude minutes" size="2" maxlength="2" /><sup>'</sup>
									</td>
									<td>
										<input type="text" class="digit2" value="<?php if(isset($chart['Esec'])) print $chart['Esec']; ?>" name="lng_sec_ne" id="lng_sec_ne" title="Value for NE corner longitude seconds" size="2" maxlength="4" /><sup>"</sup>
									</td>
									<td>
										<select name="lng_we_ne" id="lng_we_ne" size="1">
											<option value="-9999">&lt;select&gt;</option>
											<option<?php if(isset($chart['Eehemi']) && $chart['Eehemi'] > 0) print ' selected="selected"';?>>E</option>
											<option<?php if(isset($chart['Eehemi']) && $chart['Eehemi'] < 0) print ' selected="selected"';?>>W</option>
										</select>
									</td>
								</tr>
								<tr>
									<td colspan="5"><?php print $initResetCoordsNE;?></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
			
			<h2 id="a3" class="trigger">
				<a class="trigger" href="#">Calibrating chart corners (REF points)</a>
				<p class="indicator">
					<img id="led_cal_corner_sw" src="" alt="status led" title="" />
					<img id="led_cal_corner_nw" src="" alt="status led" title="" />
					<img id="led_cal_corner_ne" src="" alt="status led" title="" />
					<img id="led_cal_corner_se" src="" alt="status led" title="" />
				</p>
			</h2>
			<div class="toggle_container">
				<div id="img_container_wrapper">
					<div id="container_0">
						<table id="cal_corner_container_tab" class="" cellspacing="0" cellpadding="0">
						<colgroup><col width="1*" /><col width="1*" /></colgroup>
							<tr>
								<td>
									<table id="cal_corner_tab" class="coords_tab1 block_center" cellspacing="0" cellpadding="0">
										<colgroup><col width="1*" /><col width="1*" /><col width="1*" /><col width="1*" /></colgroup>
										<tr>
											<td colspan="4">Choose the corner you want to calibrate:</td>
										</tr>
										<tr class="highlight">
											<td><input type="radio" name="cal_corner" id="cal_corner_sw" value="SW" /> SW</td>
											<td><span>X:</span><input class="right" id="xcoord_sw" type="text" value="<?php if(isset($chart['Xsw'])) print "{$chart['Xsw']}";?>" disabled="disabled" size="5" /><input name="xcoordh_sw" id="xcoordh_sw" type="hidden" value="<?php if(isset($chart['Xsw'])) print "{$chart['Xsw']}";?>" /></td>
											<td><span>Y:</span><input class="right" id="ycoord_sw" type="text" value="<?php if(isset($chart['Ysw'])) print "{$chart['Ysw']}";?>" disabled="disabled" size="5" /><input name="ycoordh_sw" id="ycoordh_sw" type="hidden" value="<?php if(isset($chart['Ysw'])) print "{$chart['Ysw']}";?>" /></td>
											<td><?php print "$initResetCorner";?></td>
										</tr>
										<tr class="">
											<td><input type="radio" name="cal_corner" id="cal_corner_nw" value="NW" /> NW</td>
											<td><span>X:</span><input class="right" id="xcoord_nw" type="text" value="<?php if(isset($chart['Xnw'])) print "{$chart['Xnw']}";?>" disabled="disabled" size="5" /><input name="xcoordh_nw" id="xcoordh_nw" type="hidden" value="<?php if(isset($chart['Xnw'])) print "{$chart['Xnw']}";?>" /></td>
											<td><span>Y:</span><input class="right" id="ycoord_nw" type="text" value="<?php if(isset($chart['Ynw'])) print "{$chart['Ynw']}";?>" disabled="disabled" size="5" /><input name="ycoordh_nw" id="ycoordh_nw" type="hidden" value="<?php if(isset($chart['Ynw'])) print "{$chart['Ynw']}";?>" /></td>
											<td><?php print "$initResetCorner";?></td>
										</tr>
										<tr class="">
											<td><input type="radio" name="cal_corner" id="cal_corner_ne" value="NE" /> NE</td>
											<td><span>X:</span><input class="right" id="xcoord_ne" type="text" value="<?php if(isset($chart['Xne'])) print "{$chart['Xne']}";?>" disabled="disabled" size="5" /><input name="xcoordh_ne" id="xcoordh_ne" type="hidden" value="<?php if(isset($chart['Xne'])) print "{$chart['Xne']}";?>" /></td>
											<td><span>Y:</span><input class="right" id="ycoord_ne" type="text" value="<?php if(isset($chart['Yne'])) print "{$chart['Yne']}";?>" disabled="disabled" size="5" /><input name="ycoordh_ne" id="ycoordh_ne" type="hidden" value="<?php if(isset($chart['Yne'])) print "{$chart['Yne']}";?>" /></td>
											<td><?php print "$initResetCorner";?></td>
										</tr>
										<tr class="">
											<td><input type="radio" name="cal_corner" id="cal_corner_se" value="SE" /> SE</td>
											<td><span>X:</span><input class="right" id="xcoord_se" type="text" value="<?php if(isset($chart['Xse'])) print "{$chart['Xse']}";?>" disabled="disabled" size="5" /><input name="xcoordh_se" id="xcoordh_se" type="hidden" value="<?php if(isset($chart['Xse'])) print "{$chart['Xse']}";?>" /></td>
											<td><span>Y:</span><input class="right" id="ycoord_se" type="text" value="<?php if(isset($chart['Yse'])) print "{$chart['Yse']}";?>" disabled="disabled" size="5" /><input name="ycoordh_se" id="ycoordh_se" type="hidden" value="<?php if(isset($chart['Yse'])) print "{$chart['Yse']}";?>" /></td>
											<td><?php print "$initResetCorner";?></td>
										</tr>
									</table>
								</td>
								<td>
									<table id="cal_corner_instruct_tab" class="coords_tab1 block_center" cellspacing="0" cellpadding="0">
										<tr>
											<td>
												<div>
													<h3>Instructions</h3>
													<p>At first get an overview of the elements:
														<ol style="cursor: help;">
															<li onmouseover="$('table#cal_corner_tab').effect('pulsate', '', 900);" onmouseout="$('table#cal_corner_tab').stop(true, true).removeAttr('style').fadeIn();">Corner selection panel</li>
															<li onmouseover="$('div#container_1').effect('pulsate', '', 900);" onmouseout="$('div#container_1').stop(true, true).removeAttr('style').fadeIn();">Corner image view</li>
															<li onmouseover="$('div#crop').effect('pulsate', '', 900);" onmouseout="$('div#crop').stop(true, true).removeAttr('style').fadeIn();" id="selection_square_li">Selection square for zoomed view</li>
															<li onmouseover="$('div#container_2').effect('pulsate', '', 900);" onmouseout="$('div#container_2').stop(true, true).removeAttr('style').fadeIn();">Corner image (10 x) zoomed view</li>
															<li onmouseover="$('div#control_1').effect('pulsate', '', 900);" onmouseout="$('div#control_1').stop(true, true).removeAttr('style').fadeIn();" id="scale_selection_li" title="click to scroll to the element">Scale selection for corner image view</li>
															<li onmouseover="$('div#control_2').effect('pulsate', '', 900);" onmouseout="$('div#control_2').stop(true, true).removeAttr('style').fadeIn();" id="move_buttons_li" title="click to scroll to the element">Move buttons for corner image zoomed view</li>
														</ol>
													</p>
													<p>
														After loading the page the image of the SW corner is preselected. The selection square (3) resides in the upper-left corner of the image view (2). The scale is initially set to '1:3' which gives you the fullview of the corner image.
													</p>
													<p>
														<strong>Image section:</strong><br />For changing the image section do the following:<br />First make a left mouse click on the selection square (3) and keep the mousebutton pressed. By doing so you can drag the square around. Now move it to the approximately right place and release the mousebutton. The image zoomed view (4) is automatically updated. For the fine-tuning of the position you can<br />a) drag the image in the zoomed view (4)<br />b) use the 'Move buttons' (6) below the zoomed view (4)
													</p>
													<p>
														<strong>Scale:</strong><br />Here you can change the scale of the corner image. A scale of '1:3' means fullview whereas '1:1' shows the image in its original size. If scale is other than '1:3' the image is also draggable.
													</p>
													<p>
														<strong>Correct position:</strong><br />Move the image right up until the center mark is just inside the inner corner of the chart's outer grid (see picture - example for a SW corner).<br /><img src="images/calibration_point.png" style="padding: 5px;" /><br />If you positioned the image correctly just leave it as is - the coordinates will be submitted when you 'Save & Exit' the page.<br /><br />If you'd like to you may proceed with the next corner ...!
													</p>
													<p>
														<strong>Reset/ Cancel:</strong><br />Just in case you want to cancel the calibration of a corner or you have accidentally moved a corner image that was already calibrated (see checklist) just click the according 'Reset' button.
													</p>
												</div>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</div>
					<div id="container_1">
						<img id="img1" src="http://opencpn.xtr.cz/nga-charts/<?php print $chart_id;?>/<?php print $chart_id;?>_sw.png" onFocus="this.blur();" />
						<div id="crop" class="draggable"></div>
					</div>

					<div id="container_2">
						<div id="vert"></div><div id="horiz"></div>
						<img id="img2" src="http://opencpn.xtr.cz/nga-charts/<?php print $chart_id;?>/<?php print $chart_id;?>_sw.png" onFocus="this.blur();" />
					</div>
				</div>
				
				<div id="control_wrapper">
					<div id="control_1">
						Scale<br />
						<input type="button" value="1:3" onClick="factor=3; changeimg1Zoom();" disabled="disabled" />
						<input type="button" value="1:2" onClick="factor=2; changeimg1Zoom();" />
						<input type="button" value="1:1" onClick="factor=1; changeimg1Zoom();" />
					</div>
					
					<div id="control_2">
						<table id="control_but" class="coords_tab1">
							<tr>
								<td colspan="2" class="center">Move center point</td>
							</tr>
							<tr>
								<td colspan="2" class="center"><input class="cbut" type="button" value="Up" onClick="move('up');" /></td>
							</tr>
							<tr>
								<td><input class="cbut" type="button" value="Left" onClick="move('left');" /></td>
								<td class="right"><input class="cbut" type="button" value="Right" onClick="move('right');" /></td>
							</tr>
							<tr>
								<td colspan="2" class="center"><input class="cbut" type="button" value="Down" onClick="move('down');" /></td>
							</tr>
						</table>
						
					</div>
				</div>
			</div>
			
			<h2 id="a4" class="trigger"><a class="trigger" href="#">Comment Box [optional]</a><p class="indicator"><img id="led_comment" src="" alt="status led" title="" /></p></h2>
			<div class="toggle_container">
				<div id="comment_container">
					<p>If you would like to submit some additional information and/ or report an error in one of the non-editable values, please enter it in the textfield below. Only plain text is allowed (no HTML/BB-Code)!</p>
					<textarea name="comment" id="comment" cols="80" rows="10"><?php print"{$chart['comments']}";?></textarea>
					<p><?php print $initResetComment;?></p>
				</div>
			</div>
		</div>
		
		<div id="topbar">
			<p>
				Logged-in as:&nbsp;&nbsp;<strong><?php print $_SESSION['wp-user']['user_login'];?></strong><br />
				Show tooltips:&nbsp;<input type="checkbox" name="bt" id="bt" checked="checked" />
			</p>
			<div id="save_exit">
				<input type="button" id="close_but" onclick="exitNoSave();" value="Exit without Saving" />
				&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="submit" id="save_but" value="Save &amp; Exit" />
				<input type="hidden" id="sessionID" name="sessionID" value="<?php print session_id();?>" />
				<input type="hidden" id="chartID" name="chartID" value="<?php print $chart_id;?>" />
			</div>
			<div id="chart_number">
				<h3 id="chart_no" onclick="checklist();" onmouseover="$('h3#chart_no').btOn();" onmouseout="$('h3#chart_no').btOff();" title="Click to open & close the checklist or on the checklist to close it!">Chart <?php print $chart_id;?> ▲</h3>
			</div>
		</div>
		
		<div id="response">
			<?php ?>
		</div>
    </form>
	</div>
</body>
</html>
