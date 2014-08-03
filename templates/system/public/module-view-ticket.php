<?php global $wptickets; ?>

<div class="wptickets_col_1 last">
	<h3>Check Ticket Status</h3>
	<p>We provide archives and history of all your support requests complete with responses. </p>

	<form method="post" action="#">

		<?php wt_the_notifications('form_view_ticket'); ?>

		<input type="hidden" name="wptickets-action" value="public_view_ticket" />	

		<div class="input text">
			<label>Email</label>
			<input type="text" name="ticket_email" />
		</div>
		<div class="input text">
			<label>Access Key</label>
			<input type="text" name="ticket_key" />
		</div>

		<div class="submit">
			<input type="submit" value="View Ticket" />
		</div>
	</form>

	<?php
	/**
	 * Hooked
	 */
	do_action('wt/public_view_ticket_form'); ?>
</div>