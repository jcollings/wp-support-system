<?php
global $post;
$name = wt_get_ticket_author_meta($post->ID, 'name');
$email = wt_get_ticket_author_meta($post->ID, 'email');
?>
<div id="ticket-<?php the_ID(); ?>" class="wt-support-ticket single <?php wt_the_ticket_class(); ?>">

	<h1><?php the_title(); ?></h1>

	<div class="wt-support-comment">
		<div class="wt-comment-left"><?php echo get_avatar( $email, 50 ); ?></div>
		<div class="wt-comment-right">
			<p class="wt-comment-author"><?php echo $name; ?></p>
			<div class="wt-comment-content">
				<?php the_content(); ?>
			</div>
		</div>
	</div>

	<?php 
	/**
	 * Hooked: wt_show_ticket_comments - 10
	 */
	do_action('wt_ticket_comments'); ?>

	</ul>
	
	<?php 
	/**
	 * Hooked: wt_show_ticket_commentform - 10
	 */
	do_action('wt_after_ticket_comments'); ?>
</div>