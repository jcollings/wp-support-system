<div id="ticket-<?php the_ID(); ?>" class="wt-support-comment single <?php wt_the_ticket_class(); ?>">
	<div class="wt-comment-left">
		<?php echo get_avatar( wt_get_ticket_author_meta($post->ID, 'email'), '50'); ?>
	</div>
	<div class="wt-comment-right">
		<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<div class="wt-meta-head">
			<p class="access"><strong>Access:</strong> <?php echo wt_get_ticket_access(); ?></p>
			<p class="status"><strong>Status:</strong> <?php echo wt_get_ticket_status(); ?></p>
			<p class="department"><strong>Dept:</strong> <?php echo wt_get_ticket_department(); ?></p>
		</div>
	</div>
</div>