<?php
$pagesize = 50;
$page = 1;
if (isset($_GET['page']))
	$page = $_GET['page'];
$start = ($page - 1);
if ($page < 0)
	$page = 0;
$start = $start * $pagesize;

$result = $wpdb->get_results("SELECT COUNT(*) AS cnt FROM ocpn_nga_charts A JOIN ocpn_nga_charts_links B ON A.number = B.number LEFT JOIN ocpn_nga_charts_with_params C ON (A.number = C.number) WHERE A.number <= 9999", ARRAY_A);

if ($result)
	$total = (int)$result[0]['cnt'];

$numpages = ceil($total / $pagesize);

/*
Template Name: NGA Charts Status Region misc
*/
#$result = $wpdb->get_results("SELECT * FROM ocpn_nga_charts A JOIN ocpn_nga_charts_links B ON A.number = B.number WHERE A.number >= 0 AND A.number <= 9999 ORDER BY A.number ASC", ARRAY_A);
$result = $wpdb->get_results("SELECT * FROM ocpn_nga_charts A JOIN ocpn_nga_charts_links B ON A.number = B.number LEFT JOIN ocpn_nga_charts_with_params C ON (A.number = C.number) WHERE A.number >= 0 AND A.number <= 9999 ORDER BY A.number ASC LIMIT $start, $pagesize", ARRAY_A);


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
$region = 'Misc.';
?>
<?php get_header(); ?>
<?php get_sidebar(); ?>
<?php get_template_part("nga-charts-status") ?>
<?php get_footer(); ?>
