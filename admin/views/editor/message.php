<div id="ticket-<?php echo $post->ID; ?>" class="admin-support-comment support-message">
		
	<div class="comment-left">

		<!-- Author Info -->
		<div class="author-info">
			<div class="img-wrapper">
				<?php echo get_avatar(  wt_get_ticket_author_meta($post->ID, 'email'), 50 ); ?>
			</div>
		</div>

	</div>
	<div class="comment-right">

		<!-- Comment meta info -->
		<div class="meta-head">
			<p class="desc">Posted by <?php echo wt_get_ticket_author_meta($post->ID, 'name') ?> on <?php echo the_time( 'F j, Y \a\t g:i a'); ?> - (<?php echo wt_get_ticket_access(); ?>)</p>
		</div>

		<!-- Comment Message -->
		<div class="meta-content">
			<?php the_content(); ?>
		</div>

	</div>

</div>