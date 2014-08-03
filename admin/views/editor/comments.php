<li id="comment-<?php comment_id(); ?>" class="support-comment <?php wt_the_comment_class(); ?>">
	<div class="response">
		<div class="left">
			<div class="meta-head">
				<p class="desc">Posted on <?php echo get_comment_date( 'F j, Y \a\t g:i a'); ?></p>
			</div>
			<div class="meta-content">
				<p><?php echo wt_get_comment_access(); ?>:</p>
				<?php comment_text(); ?>
			</div>
		</div>
		<div class="right">
			<div class="meta-info">
				<div class="img-wrapper">
					<?php echo get_avatar(  get_comment_author_email(), 50 ); ?>
					<p><?php comment_author(); ?></p>
				</div>
			</div>
		</div>
	</div>
	<hr />
</li>