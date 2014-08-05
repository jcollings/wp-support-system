<?php
global $post;
$name = wt_get_ticket_author_meta($post->ID, 'name');
$email = wt_get_ticket_author_meta($post->ID, 'email');
?>
<div id="post-<?php the_ID(); ?>" class="support-ticket single <?php wt_the_ticket_class(); ?>">
	<div class="question">
		<div class="left">
			<div class="meta-head">
				<h1><?php the_title(); ?></h1>
				<p class="desc">Posted on <?php the_time('F j, Y \a\t g:i a'); ?></p>
			</div>
			<div class="meta-content">
				<p><strong>Access:</strong> <?php echo wt_get_ticket_access(); ?></p>
				<p><strong>Status:</strong> <?php echo wt_get_ticket_status(); ?></p>
				<?php the_content(); ?>
			</div>
		</div>
		<div class="right">
			<div class="meta-info">
				<div class="img-wrapper">
					<?php echo get_avatar( $email, '96'); ?>
					<p><?php echo $name; ?></p>
				</div>
			</div>
		</div>
	</div>

	<footer class="meta-footer">
		<div id="comments" class="comments-area">
			<ul>
			
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
	</footer>
</div>