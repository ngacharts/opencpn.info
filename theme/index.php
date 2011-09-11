<?php
/**
 * @package WordPress
 * 
 */
?>
<?php get_header(); ?>


<?php get_sidebar(); ?>

<div id="content-wrapper">

            
                        
            <div id="content">

              <a name="main-content" id="main-content"></a>

              
              <div id="content-inner">

                
                              
                                <div id="content-content">
		
								
								 <?php if (have_posts()) : while (have_posts()) : the_post();?>


<div class="node odd sticky teaser node-type-story">
  
    <h2 class="title"><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>
  
  <div class="meta">
        <span class="submitted">by <?php the_author_posts_link(); ?> on <?php the_time('M.d, Y') ?>, under <?php the_category(', '); ?></span>
      </div>

  <div class="content">

    <?php the_content('(continue reading...)'); ?>
    <div class="linkpages"><?php wp_link_pages(); ?></div>
  </div>

    
    <div class="links">
    <ul class="links inline"><li class="node_read_more first"><span class="readmore-item"><?php comments_popup_link('Leave a Comment', '1 Comment', '% Comments'); ?></span></li>
<li class="node_read_more last"><span class="readmore-item"><?php edit_post_link('Edit this entry?', ' ', ''); ?></span></li>

</ul>

 <?php the_tags('Tags: <ul class="inline"><li>', '</li><li>', '</li></ul>'); ?>
  </div>
  
  </div>

	<?php endwhile; else: ?>

<div class="node odd sticky teaser node-type-story">
  
    <h2 class="title">Not Found</h2>
  
  <div class="meta">

      </div>

  <div class="content">

    <p>Sorry, but you are looking for something that isn't here.</p>
    
  </div>

 
  </div>
  
  </div>





	<?php endif; ?>
	<div id="nextprevious">
<div class="alignleft"><?php next_posts_link('&laquo; Older Entries') ?></div>
<div class="alignright"><?php previous_posts_link('Newer Entries &raquo;') ?></div>
<div class="cleared"></div>
</div>
                  
 <a href="<?php bloginfo('rss2_url'); ?>" class="feed-icon"><img src="<?php bloginfo('template_url'); ?>/images/rss.gif" alt="Syndicate content" title="<?php bloginfo('name'); ?>" width="40" height="16" /></a>
<!-- /#node-1 -->
                </div>
              </div><!-- /content-inner -->

            </div><!-- /content -->

                      </div><!-- /content-wrapper -->

  
  

 

<!-- end body -->
<?php get_footer(); ?>
