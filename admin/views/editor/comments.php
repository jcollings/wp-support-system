<div id="comment-<?php comment_id(); ?>" class="admin-support-comment <?php wt_the_comment_class(); ?>">
		
	<div class="comment-left">

		<!-- Author Info -->
		<div class="author-info">
			<div class="img-wrapper">
				<?php echo get_avatar(  get_comment_author_email(), 50 ); ?>
			</div>
		</div>

	</div>
	<div class="comment-right">

		<!-- Comment meta info -->
		<div class="meta-head">
			<p class="desc">Posted by <?php comment_author(); ?> on <?php echo get_comment_date( 'F j, Y \a\t g:i a'); ?> - (<?php echo wt_get_comment_access(); ?>)</p>
		</div>

		<!-- Comment Message -->
		<div class="meta-content">
			<?php comment_text(); ?>
		</div>

	</div>

</div>