
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
  
  <div class="content">

    <?php the_content('(continue reading...)'); ?>
    <div class="linkpages"><?php wp_link_pages(); ?></div>
  </div>

    
    <div class="links">
    <ul class="links inline">
<li class="node_read_more last"><span class="readmore-item"><?php edit_post_link('Edit this entry?', ' ', ''); ?></span></li>

</ul>

 <?php the_tags('Tags: <ul class="inline"><li>', '</li><li>', '</li></ul>'); ?>
  </div>
  
  </div>






<?php endwhile; ?>

<?php else : ?>

<div class="topPost">
  <h2 class="topTitle"><a href="<?php the_permalink() ?>">Not Found</a></h2>
  <div class="topContent"><p>Sorry, but you are looking for something that isn't here. You can search again by using <a href="#searchform">this form</a>...</p></div>
</div> <!-- Closes topPost -->

<?php endif; ?>


<div class="cleared"></div>

  </div>
              </div><!-- /content-inner -->

            </div><!-- /content -->

                      </div><!-- /content-wrapper -->
					




<?php get_footer(); ?>


  





