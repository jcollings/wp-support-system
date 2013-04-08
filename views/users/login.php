<?php

if(!FormHelper::is_complete()){

	echo FormHelper::create('Login', array(
		'title' => 'Login Form',
		'desc' => 'Fill out the form below to login, Fields prefixed with * are manditory.',
	));

	if(isset($_GET['ref'])){
		echo FormHelper::hidden('ref', array('value' => $_GET['ref']));
	}

	echo FormHelper::text('name', array('label' => 'Email Address'));
	echo FormHelper::password('password', array('label' => 'Password'));
	echo FormHelper::end('Login');
}else{
	echo '<p>Your form has been submitted</p>';
}
?>