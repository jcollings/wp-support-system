<div class="form wt_form_wrapper">
	<form method="post" action="#">

		<input type="hidden" name="wptickets-action" value="admin_add_comment" />
		<input type="hidden" name="ticket_id" value="<?php the_ID(); ?>" />

		<div class="input select">
			<label>Response Type</label>
			<select name="access">
				<option value="private">Private</option>
				<option value="internal">Internal</option>
				<option value="public" selected="selected">Public</option>
			</select>
		</div>

		<div class="input checkbox">
			<label>Close ticket on reply:</label>
			<input type="checkbox" name="close_ticket" value="1" />
		</div>

		<div class="input textarea">
			<?php 
			$settings  = array(
				'wpautop'       => false, // use wpautop?
				'media_buttons' => false, // show insert/upload button(s)
				'textarea_rows' => 10,
				'teeny'         => false, // output the minimal editor config used in Press This
				'tinymce'       => true
			);
			wp_editor( '', 'response', $settings);
			?>
		</div>

		<div class="submit">
			<input type="submit" name="wptickets-action-button" value="Add Comment" class="button button-primary" />
		</div>
		<div style="clear:both;"></div>
	</form>
</div>