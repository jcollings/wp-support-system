<?php global $wptickets; ?>

<div class="wptickets_col_1 last">
	<form class="form" method="post" action="#">

		<?php wt_the_notifications('form_member_login'); ?>

		<h2>Login </h2>

		<input type="hidden" name="wptickets-action" value="login_account" />	
			
		<div class="input text">
			<label>Email</label>
			<input type="text" name="email" />
		</div>

		<div class="input text">
			<label>Password</label>
			<input type="password" name="password" />
		</div>

		<div class="submit">
			<input type="submit" value="Login" />
		</div>

	</form>
</div>