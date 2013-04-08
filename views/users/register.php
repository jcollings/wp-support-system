<?php

if(!FormHelper::is_complete()){

	echo FormHelper::create('Register', array(
		'title' => 'Registration Form',
		'desc' => 'Fill out the form below to register, Fields prefixed with * are manditory.',
	));

	if(isset($_GET['ref'])){
		echo FormHelper::hidden('ref', array('value' => $_GET['ref']));
	}

	echo '<div class="form_cols_2">';
	echo FormHelper::text('first_name', array('class' => 'two_col_left'));
	echo FormHelper::text('last_name', array('class' => 'two_col_right'));
	echo '</div>';

	echo FormHelper::text('email');

	echo '<div class="form_cols_2">';
	echo FormHelper::text('pass', array('class' => 'two_col_left'));
	echo FormHelper::text('pass_retry', array('class' => 'two_col_right'));
	echo '</div>';

	echo FormHelper::end('Register');
}else{
	echo '<p>Your account has been registered.</p>';
}
?>