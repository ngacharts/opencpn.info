<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<title><?php wp_title(''); ?> <?php if ( !(is_404()) && (is_single()) or (is_page()) or (is_archive()) ) { ?> at <?php } ?> <?php bloginfo('name'); ?></title>
<meta name="generator" content="WordPress <?php bloginfo('version'); ?>" /> <!-- leave this for stats -->
<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>" />
<link rel="alternate" type="application/atom+xml" title="Atom 0.3" href="<?php bloginfo('atom_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<?php wp_get_archives('type=monthly&format=link'); ?>
<?php wp_head(); ?>

</head>



  <body class="logged-in front layout-first-main">
    <div id="page" class="clearfix">
      
              <div id="skip">
          <a href="#main-content">Skip to Main Content</a>
        </div>

            <div id="header-wrapper">
        <div id="header" class="clearfix">
          
                
          <div id="header-first">
             
            <div id="logo">
              <a href="<?php bloginfo('url'); ?>" title="Home"><img src="<?php bloginfo('template_url'); ?>/logo.png" alt="Home" /></a>
            </div>
                                    <h1><a href="<?php bloginfo('url'); ?>" title="Home"><?php bloginfo('name'); ?></a></h1>
                                  </div><!-- /header-first -->

  
          <div id="header-middle">
		  
                      </div><!-- /header-middle -->
      
          <div id="header-last">
		  
                      </div><!-- /header-last -->
      
        </div><!-- /header -->
		
      </div><!-- /header-wrapper -->
	 <div class="clearfix">
                <div id="primary-menu">
          <ul class="menu">
		   <li><a href="<?php bloginfo('url'); ?>" title="Home">Home</a></li><?php wp_list_pages('title_li=&depth=1&wrapper_tag=whatever');  ?>
</ul>        </div><!-- /primary_menu -->
              </div><!-- /primary-menu-wrapper -->

	  
