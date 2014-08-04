<form action="#" method="post">

	<h2>Create Support Ticket</h2>

	<?php wt_the_notifications('form_add_ticket'); ?>

	<input type="hidden" name="wptickets-action" value="add_ticket" />	

	<?php 
	/**
	 * Show unregistered fields
	 */
	if(!is_user_logged_in()): ?>
		<div class="input text">
			<label>Full Name</label>
			<input type="text" name="name" />	
		</div>		

		<div class="input text">
			<label>Email</label>
			<input type="text" name="email" />	
		</div>		
	<?php endif; ?>

	<div class="input">
		<label>Department</label>
		<select name="department">
			<option value="">Choose One</option>
			<?php
			$departments = wt_list_ticket_departments();
			foreach($departments as $dept): ?>
			<option value="<?php echo $dept->slug; ?>"><?php echo $dept->name; ?></option>
			<?php endforeach; ?>
		</select>
	</div>

	<div class="input">
		<label>Ticket Access</label>
		<select name="ticket_access">
			<option value="public">Public</option>
			<option value="private">Private</option>
		</select>
	</div>

	<div class="input text">
		<label>Subject</label>
		<input type="text" name="subject" />	
	</div>

	<div class="input textarea">
		<label>Message</label>
		<textarea name="message"></textarea>
	</div>
	
	<div class="submit">
		<input type="submit" value="Add Ticket" />
	</div>
</form>