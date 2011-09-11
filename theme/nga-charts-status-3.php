<?php
/*
Template Name: NGA Charts Status Region 3
*/
#$result = $wpdb->get_results("SELECT * FROM ocpn_nga_charts A JOIN ocpn_nga_charts_links B ON A.number = B.number WHERE A.number >= 30000 AND A.number <= 39999 ORDER BY A.number ASC", ARRAY_A);
$result = $wpdb->get_results("SELECT * FROM ocpn_nga_charts A JOIN ocpn_nga_charts_links B ON A.number = B.number LEFT JOIN ocpn_nga_charts_with_params C ON (A.number = C.number) WHERE A.number >= 30000 AND A.number <= 39999 ORDER BY A.number ASC", ARRAY_A);

if($result) {
	$charts = array();
    $count = 0;
    $miss = 0;
	foreach($result as $k => $v) {
		$index = $v['number'];
		$charts[$index] = array();
		foreach($v as $key => $val) {
			if($key != 'number') $charts[$index][$key] = $val;
            if($key == 'status' && !$val) $miss++;
		}
        $count++;
	}
}
$region = 3;
?>
<?php get_header(); ?>
<?php get_sidebar(); ?>
<?php get_template_part("nga-charts-status") ?>
<?php get_footer(); ?>