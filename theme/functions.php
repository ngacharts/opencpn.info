<?php
function my_init_session() {
	if (!session_id()) session_start();
}
add_action('init', 'my_init_session', 1);

function my_init_method() {
    #wp_deregister_script( 'jquery' );
    #wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js');
    
	wp_register_script( 'jquery_drf', 'http://opencpn.info/wp-content/themes/acquiamarina/js/my_drf.js');
	wp_register_script( 'jquery_storage', 'http://opencpn.info/wp-content/themes/acquiamarina/js/my_storage.js');
	wp_register_script( 'jquery_cookie', 'http://opencpn.info/nga/js/jquery.cookie.js');
	wp_register_script( 'jquery_json2', 'http://opencpn.info/nga/js/json2.min.js');
	
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery_cookie' );
	wp_enqueue_script( 'jquery_json2' );
	wp_enqueue_script( 'jquery_drf' );
	wp_enqueue_script( 'jquery_storage' );
}    
 
add_action('init', 'my_init_method');


if ( function_exists('register_sidebar') )

    register_sidebar(array(
	'name'=>'sidebar_left',
        'before_widget' => '<div class="block block-user">
                                <div class="block-icon pngfix"></div>
',
        'after_widget' => '</div></div>
	
',
        'before_title' => '<h2 class="title block-title pngfix">',
        'after_title' => '</h2><div class="content">',
    ));


add_action('admin_menu', 'add_welcome_interface');


function add_welcome_interface() {
  add_theme_page('welcome', 'Theme Options', '8', 'functions', 'editoptions');
  }

function editoptions() {
  ?>
  <div class='wrap'>
  <h2>Theme Options</h2>
  <form method="post" action="options.php">
  <?php wp_nonce_field('update-options') ?>
  <p><strong>Greeting Heading:</strong></p>
  <p><input type="text" name="greeting" value="<?php echo get_option('greeting'); ?>" /></p>
  <p><strong>Welcome Message:</strong></p>
  <p><textarea name="welcomemessage" cols="100%" rows="10"><?php echo get_option('welcomemessage'); ?></textarea></p>
  <p><strong>Please enter the name of your FeedBurner feed below: </strong>(What's the name of your feed? Well, for instance, in the following - http://feeds.feedburner.com/NAME - NAME would be your feed :))</p>
  <p><input type="text" name="feedname" value="<?php echo get_option('feedname'); ?>" /></p>
  <p><input type="submit" name="Submit" value="Update Options" /></p>
  <input type="hidden" name="action" value="update" />
  <input type="hidden" name="page_options" value="feedname,greeting,welcomemessage" />
  </form>
  </div>
  <?php
  }


function mytheme_comment($comment, $args, $depth) {
   $GLOBALS['comment'] = $comment; ?>
   <li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
     <div id="comment-<?php comment_ID(); ?>">
			<a class="gravatar">
			<?php echo get_avatar($comment,$size='60'); ?>
			</a>

			<div class="commentbody">
			<cite><?php comment_author_link() ?></cite> 
			<?php if ($comment->comment_approved == '0') : ?>
			<em>Your comment is awaiting moderation.</em>
			<?php endif; ?>
			<br />
			<small class="commentmetadata"><a href="#comment-<?php comment_ID() ?>" title=""><?php comment_date('F jS, Y') ?> on <?php comment_time() ?></a> <?php edit_comment_link('edit','&nbsp;&nbsp;',''); ?></small>

			<?php comment_text() ?>
			</div><div class="cleared"></div>

      <div class="reply">
         <?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
      </div>
     </div>
<?php
        }



function mytheme_ping($comment, $args, $depth) {
   $GLOBALS['comment'] = $comment; ?>
   <li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
     <div id="comment-<?php comment_ID(); ?>">
			<div class="commentbody">
			<cite><?php comment_author_link() ?></cite> 
			<?php if ($comment->comment_approved == '0') : ?>
			<em>Your comment is awaiting moderation.</em>
			<?php endif; ?>
			<br />
			<small class="commentmetadata"><a href="#comment-<?php comment_ID() ?>" title=""><?php comment_date('F jS, Y') ?> on <?php comment_time() ?></a> <?php edit_comment_link('edit','&nbsp;&nbsp;',''); ?></small>

			<?php comment_text() ?>
			</div>
     </div>
<?php
        }



?>