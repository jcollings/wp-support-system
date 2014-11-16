<div class="wrap">
    <div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
	<h2>Create Ticket</h2>

	<p>Fill out the form below to add a new support ticket</p>

	<form action="#" method="post">

		<?php wt_the_notifications('form_add_ticket'); ?>

		<input type="hidden" name="wptickets-action" value="add_ticket" />	

		<?php 
		/**
		 * Show unregistered fields
		 */
		if(wt_is_user_admin()): ?>
			
			<?php $users = wt_get_members('list'); ?>
			<div class="input select">
				<label>Member</label>
				<select name="user_id" id="user_id">
					<?php foreach($users as $user_id =>$user): ?>
						<option value="<?php echo $user_id; ?>"><?php echo $user; ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="input text">
				<label>Full Name</label>
				<input type="text" name="name" />	
			</div>		

			<div class="input text">
				<label>Email</label>
				<input type="text" name="email" />	
			</div>	

			<?php $priorities = wt_list_ticket_priorities(); ?>
			<div class="input select">
				<label>Priority</label>
				<select name="priority" id="priority">
					<?php foreach($priorities as $key =>$label): ?>
						<option value="<?php echo $key; ?>"><?php echo $label; ?></option>
					<?php endforeach; ?>
				</select>
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
			<?php 
				$settings  = array(
					'wpautop'       => false, // use wpautop?
					'media_buttons' => false, // show insert/upload button(s)
					'textarea_rows' => 10,
					'teeny'         => false, // output the minimal editor config used in Press This
					'tinymce'       => true
				);
				wp_editor( '', 'message', $settings);
				?>
		</div>
		
		<div class="submit">
			<input type="submit" value="Add Ticket" />
		</div>
	</form>
</div>

