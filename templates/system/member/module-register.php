<?php global $wptickets; ?>

<div class="wptickets_col_1 first">
	<form class="form" method="post">

		<?php wt_the_notifications('form_member_register'); ?>

		<h2>Register Account</h2>

		<input type="hidden" name="wptickets-action" value="register_account" />	

		<div class="input text">
			<label>First Name</label>
			<input type="text" name="first_name" />
		</div>

		<div class="input text">
			<label>Surname</label>
			<input type="text" name="surname" />
		</div>
			
		<div class="input text">
			<label>Email</label>
			<input type="text" name="email" />
		</div>

		<div class="input text">
			<label>Password</label>
			<input type="password" name="password" />
		</div>

		<div class="input text">
			<label>Password (Re-Type)</label>
			<input type="password" name="password_check" />
		</div>

		<div class="submit">
			<input type="submit" value="Register" />
		</div>

	</form>
</div>