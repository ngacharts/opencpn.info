

<div id="primary-menu-wrapper" class="clearfix">
              </div><!-- /primary-menu-wrapper -->

      <div id="preface">
              </div><!-- /preface -->

      <div id="main-wrapper">
        <div id="main" class="clearfix">

                    <div id="sidebar-first">
            <div class="block-wrapper even">
     <!-- see preprocess_block() -->
  <div class="rounded-block">
    <div class="rounded-block-top-left"></div>

    <div class="rounded-block-top-right"></div>
    <div class="rounded-outside">
      <div class="rounded-inside">
        <p class="rounded-topspace"></p>
        
       
                    
                

            <div class="menu">
          	<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('sidebar_left') ) : ?>
			
			<div class="block block-user">
                                <div class="block-icon pngfix"></div>
				  <h2 class="title block-title pngfix">Categories</h2>
 					<div class="content">
						<ul>
 	 					<?php wp_list_categories('show_count=0&title_li='); ?>
						</ul>
					</div>
  			   </div>
<div class="block block-user">
                                <div class="block-icon pngfix"></div>
				  <h2 class="title block-title pngfix">Archives</h2>
 					<div class="content">
 	 					<ul>
               <?php wp_get_archives('type=monthly&limit=&format=html&before=&after=&show_post_count=1'); ?>
             </ul>
					</div>
  			   </div>

<div class="block block-user">
                                <div class="block-icon pngfix"></div>
				  <h2 class="title block-title pngfix">BlogRoll</h2>
 					<div class="content">
 	 					<ul>
               <?php wp_list_bookmarks('categorize=0&title_before=&title_after=&title_li=&limit=5'); ?>
            <li><a href="http://www.mywordpressthemesite.com/">Free Wordpress Themes</a></li>
            <li><a href="http://phpweby.com/hostgator_coupon.php">hostgator coupon</a></li>
             </ul>
					</div>
  			   </div>
								
<?php endif; ?>
</div>         
        </div>
  
          <p class="rounded-bottomspace"></p>


    </div>
    <div class="rounded-block-bottom-left"></div>
    <div class="rounded-block-bottom-right"></div>
  </div><!-- /rounded-block -->
    
</div>





<!-- /end block.tpl.php -->
          </div><!-- /sidebar-first -->
