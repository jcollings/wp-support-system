<?php
/**
 * Show unregistered fields
 */
if(wt_is_user_admin()): ?>

	<?php $users = wt_get_members('list'); ?>
	<div class="input select">
		<label>Member</label>
		<select name="user_id" id="user_id">
			<option value="">Guest</option>
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

<?php endif; ?>

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