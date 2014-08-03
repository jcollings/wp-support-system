<div id="post-<?php the_ID(); ?>" class="support-ticket single">
	<div class="question">
		<div class="left">
			<div class="meta-head">
				<h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
				<p class="desc">Posted on <?php the_time('F j, Y \a\t g:i a'); ?></p>
				<p><strong>Access:</strong> <?php echo wt_get_ticket_access(); ?></p>
				<p><strong>Status:</strong> <?php echo wt_get_ticket_status(); ?></p>
			</div>
			<div class="meta-content">

				<?php the_content(); ?>
			</div>
		</div>
		<div class="right">
			<div class="meta-info">
				<div class="img-wrapper">
					<?php echo get_avatar( get_the_author_meta( 'email'), '96'); ?>
					<p><?php echo get_the_author(); ?></p>
				</div>
			</div>
		</div>
	</div>
</div>