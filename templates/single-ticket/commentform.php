<div class="form form_wrapper">
	<h2>Reply</h2>
	<form method="post" action="#">

		<input type="hidden" name="wptickets-action" value="add_comment" />
		<input type="hidden" name="ticket_id" value="<?php the_ID(); ?>" />

		<div class="input checkbox">
			<label>Make Response Private</label>
			<input type="checkbox" name="access" value="private" />
		</div>

		<div class="input textarea">
			<!-- <textarea name="response"></textarea> -->
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
			<input type="submit" value="Add Comment" />
		</div>
	</form>
</div>