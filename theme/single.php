
<?php
/**
 * @package WordPress
 * @subpackage Proba
 */
?>
<?php get_header(); ?>
<?php get_sidebar(); ?>

<div id="content-wrapper">

            
                        
            <div id="content">

              <a name="main-content" id="main-content"></a>
              			  
              
              <div id="content-inner">

                
                              
                                <div id="content-content">

<?php if (have_posts()) : ?>
<?php while (have_posts()) : the_post(); ?>


<div class="node odd sticky teaser node-type-story">
  
    <h2 class="title"><?php the_title(); ?></h2>
  
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








<div id="comments">
<?php if (function_exists('wp_list_comments')): ?>
<!-- WP 2.7 and above -->
<?php comments_template('', true); ?>

<?php else : ?>
<!-- WP 2.6 and below -->
<?php comments_template(); ?>
<?php endif; ?>
</div> <!-- Closes Comment -->

<div id="extrastuff">
<span id="rssleft"><?php comments_rss_link(__('<abbr title="Really Simple Syndication">RSS</abbr> feed for this post (comments)')); ?></span>

<span id="trackright"><?php if ( pings_open() ) : ?><a href="<?php trackback_url() ?>" rel="trackback"><?php _e('TrackBack <abbr title="Uniform Resource Identifier">URI</abbr>'); ?></a><?php endif; ?></span>
<div class="cleared"></div>
</div>

<?php endwhile; ?>

<?php else : ?>
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


<div class="cleared"></div>

  </div>
              </div><!-- /content-inner -->

            </div><!-- /content -->

                      </div><!-- /content-wrapper -->
					




<?php get_footer(); ?>


  





