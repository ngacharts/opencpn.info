<div id="content-wrapper">

            
                        
            <div id="content">

              <a name="main-content" id="main-content"></a>
              			  					
              
              <div id="content-inner">

                
                              
                                <div id="content-content">
									


<div class="node odd sticky teaser node-type-story">
  
    <h2 class="title"><?php the_title(); ?></h2>
  
  <div class="content">
	<p style="margin-bottom: 10px;"><strong>Last updated: 2011-07-29 14:30 UTC</strong></p>
	<p style="margin-bottom: 10px;"><strong>Please also have a look at eventually existing <a href="#comments">comments at the bottom of the page</a> to avoid duplicated work.</strong></p>
	<?php
        global $charts, $count, $miss, $numpages, $page, $total;
        $present = $total - $miss;
    ?>
    <h4>Full-size chart images: <?php print $present .' / '. $total; ?></h4>
    <h4>The following chart images are missing:</h4>
	<ul>							
	<?php 
		foreach($charts as $k => $v) {
			if(!$v['status']) {
				print '<li><a style="display: inline-block; width: 4em; margin: 2px 10px 2px 0;" href="http://www.charts.noaa.gov/NGAViewer/'.$k.'/">'.$k.'</a>&nbsp;<a href="http://www.charts.noaa.gov/NGAViewer/'.$k.'.shtml">'.$v['title'].'</a></li>';
			}
		}
	?>
	</ul>
	
	<h4>The following charts must still be completed:</h4>
	<p>By having a look at the already available parts you'll see which are missing.</p>
<?php 
echo 'Page:&nbsp;';
for ($i = 1; $i <= $numpages; $i++)
	if ($i == $page)
		echo ('<b>'.$i.'</b>&nbsp;');
	else
		echo ('<a href=?page='.$i.'>'.$i.'</a>&nbsp;');
?>
	<table class="nga_chart">							
	<?php 
		#print_r($charts);
		foreach($charts as $k => $v) {
			if($v['status'] && $v['status'] < 255) {
				print '<tr style="border-top: 1px solid #CCCCCC;">
					<td style="width: 180px;"><a name="'.$k.'" id="'.$k.'" style="" href="http://www.charts.noaa.gov/NGAViewer/'.$k.'/"><strong>'.$k.'</strong></a></td>
					<td><a href="http://www.charts.noaa.gov/NGAViewer/'.$k.'.shtml">'.$v['title'].'</a><br/>Status: '.$v['status_text'].'</td>
					</tr>
					<tr>
					<td><a href="http://opencpn.info/nga/chartimages/thumbs/'.$k.'_zl2.jpg"><img style="max-width: 180px;" rel="slb_off" src="http://opencpn.info/nga/chartimages/thumbs/'.$k.'_zl0.jpg" /></a><br /><a href="http://msi.nga.mil/NGAPortal/msi/query_results.jsp?epi-content=null&beanID=null&viewID=query_results&MSI_queryType=NtMChartCorrections&MSI_generalFilterType=All&MSI_generalFilterValue=-999&MSI_additionalFilterType1=NumberList&MSI_additionalFilterType2=-999&MSI_additionalFilterValue1='.$k.'&MSI_additionalFilterValue2=-999&MSI_outputOptionType1=DisplaySet&MSI_outputOptionType2=-999&MSI_outputOptionValue1=HTML&MSI_outputOptionValue2=-999">Chart '.$k.' Corrections</a></td>
					<td>
						<table><colgroup><col width="130" /><col width="*" /><col width="80" /></colgroup>';
							if($v['maptiles']) print '<tr>
														<td>Map Tiles: </td>
														<td colspan="2"><a href="'.htmlspecialchars($v['maptiles']).'">'.htmlspecialchars(urldecode($v['maptiles'])).'</a></td>
													  </tr>';
							if($v['image_fullsize_filesize']) $size = round($v['image_fullsize_filesize'] / 1048576, 2);
							if($v['image_fullsize']) print '<tr>
																<td>Map Image (full-size): </td>
																<td><a href="'.htmlspecialchars(urldecode($v['image_fullsize'])).'">'.htmlspecialchars(urldecode($v['image_fullsize'])).'</a></td>
																<td>('.$size.' MB)</td>
															</tr>';
							if($v['image_fullsize_md5']) print '<tr>
																	<td>&nbsp;</td>
																	<td>md5: '.$v['image_fullsize_md5'].'</td>
																	<td>&nbsp;</td>
																</tr>';
							if(is_user_logged_in()) { // && ($_SESSION['wp-user']['id'] == 1 || $_SESSION['wp-user']['id'] == 26)
								if($v['status_id'] != 14) {
									print '<tr>
											<td colspan="3"><a id="edit_'.$k.'" class="cal_a" onclick="editChartData(this.id)" href="'.$PHP_SELF.'#'.$k.'">Click here to enter and/ or update the needed chart infos for producing the KAP file.</a></td>
											</tr>';
									if($v['status_id'] != 15) {
										print '<tr>
												<td colspan="3"><a onClick="w = window.open(\'http://opencpn.xtr.cz/nga-charts/gpxply.php?chart='.$k.'\', \'PLY_submission\', \'width=400,height=100,menubar=no,resizable=no,scrollbars=no,status=no\'); w.focus();" class="cal_a" onclick="editChartData(this.id)" href="#">Click here to submit the chart polygon as GPX.</a></td>
											</tr>';
									}
								}
								if($v['kap_generated']) {
									print '<tr>
											<td colspan="3"><a href="http://opencpn.xtr.cz/nga-kaps/'.$k.'.kap">Click here to download the KAP file.</a> ('.$v['kap_generated'].')</td>
										</tr>';
									print '<tr>
											<td colspan="3"><a href="http://opencpn.xtr.cz/nga-kaps/'.$k.'.gpx">Click here to download the GPX file with chart extents.</a> ('.$v['kap_generated'].')</td>
										</tr>';
								}
								if(is_user_logged_in())//if($_SESSION['wp-user']['id'] == 26)
								{
									print '<tr><td colspan="3">';
									$result = $wpdb->get_results("SELECT COUNT(*) AS cnt FROM ocpn_nga_kap WHERE bsb_type != 'BASE' AND active = 1 AND number = $k", ARRAY_A);

									if ($result)
										$total = (int)$result[0]['cnt'];
									if ($total > 0)
									{
										$result = $wpdb->get_results("SELECT * FROM ocpn_nga_kap WHERE bsb_type != 'BASE' AND active = 1 AND number = $k ORDER BY NU", ARRAY_A);
										print 'Insets/panels: <a href="#'.$k.'" onclick="editChartInsets('.$k.')">Click here to modify.</a>';
										print '<table>';
										print '<tr><th>Title</th><th>ID</th><th>Type</th><th>Scale</th><th>Shape</th><th>Calibrate</th><th>KAP</th><th>PLY</th></tr>';
										foreach($result as $ck => $cv)
										{
											$id = substr($cv['NU'], -1);
											if(is_numeric($id) || !$id)
												$id = '';
											if ($cv['cropped'])
												$shape = 'OK';
											else 
												$shape = 'TBD';
											print '<tr><td>'.$cv['title'].'</td><td>'.$id.'</td><td>'.$cv['bsb_type'].'</td><td>1:'.$cv['scale'].'</td><td>'.$shape.'</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
										}
										print '</tr>';
										print '</table>';
									}
									else
									{
										print 'No insets defined. If there should be some, <a href="#'.$k.'" onclick="editChartInsets('.$k.')">click here to define them.</a>';
									}
									print '</td></tr>';
								}
							}
					print '</table>
					</td>
					</tr>';
			}
		}
	?>
	</table>
<?php 
echo 'Page:&nbsp;';
for ($i = 1; $i <= $numpages; $i++)
	if ($i == $page)
		echo ($i.'&nbsp;');
	else
		echo ('<a href=?page='.$i.'>'.$i.'</a>&nbsp;');
?>	

  </div>

    <div class="links">
    <ul class="links inline"><li class="node_read_more first"><span class="readmore-item"><?php comments_popup_link('Leave a Comment', '1 Comment', '% Comments'); ?></span></li>
<li class="node_read_more last"><span class="readmore-item"><?php edit_post_link('Edit this entry?', ' ', ''); ?></span></li>

</ul>

 <?php the_tags('Tags: <ul class="inline"><li>', '</li><li>', '</li></ul>'); ?>
  </div>
  
  </div>


<div id="comments">
<?php comments_template('', true); ?>
</div> <!-- Closes Comment -->

<div id="extrastuff">
<span id="rssleft"><?php comments_rss_link(__('<abbr title="Really Simple Syndication">RSS</abbr> feed for this post (comments)')); ?></span>

<span id="trackright"><?php if ( pings_open() ) : ?><a href="<?php trackback_url() ?>" rel="trackback"><?php _e('TrackBack <abbr title="Uniform Resource Identifier">URI</abbr>'); ?></a><?php endif; ?></span>
<div class="cleared"></div>
</div>

<div class="cleared"></div>

  </div>
              </div><!-- /content-inner -->

            </div><!-- /content -->

                      </div><!-- /content-wrapper -->
