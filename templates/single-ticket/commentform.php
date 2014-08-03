<div class="form form_wrapper">
	<?php //comment_form(); ?>
	<form method="post" action="#">

		<input type="hidden" name="wptickets-action" value="add_comment" />
		<input type="hidden" name="ticket_id" value="<?php the_ID(); ?>" />

		<div class="input checkbox">
			<label>Make Response Private</label>
			<input type="checkbox" name="access" value="private" />
		</div>

		<div class="input textarea">
			<label>Response</label>
			<textarea name="response"></textarea>
		</div>

		<div class="submit">
			<input type="submit" value="Add Comment" />
		</div>
	</form>
</div>