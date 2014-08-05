<div class="form form_wrapper">
	<form method="post" action="#">

		<input type="hidden" name="wptickets-action" value="admin_add_comment" />
		<input type="hidden" name="ticket_id" value="<?php the_ID(); ?>" />

		<div class="input checkbox">
			<label>Make Response Private</label>
			<input type="radio" name="access" value="private" />
		</div>

		<div class="input checkbox">
			<label>Internal Note (Staff Only)</label>
			<input type="radio" name="access" value="internal" />
		</div>

		<div class="input checkbox">
			<label>Public Response</label>
			<input type="radio" name="access" value="public" checked="checked" />
		</div>

		<div class="input checkbox">
			<label>Close ticket on reply</label>
			<input type="checkbox" name="close_ticket" value="1" />
		</div>

		<div class="input textarea">
			<label>Response</label>
			<textarea name="response"></textarea>
		</div>

		<div class="submit">
			<input type="submit" name="wptickets-action-button" value="Add Comment" class="button button-primary" />
		</div>
	</form>
</div>