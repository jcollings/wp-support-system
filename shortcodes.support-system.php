<?php

add_shortcode( 'support_system_login', 'support_system_login' );
function support_system_login($atts = array()){

	if(is_user_logged_in()){
		// redirect
	}

	$form = support_system_login_form($atts);
	return $form;

}

add_shortcode( 'support_system_register', 'support_system_register' );
function support_system_register($atts = array()){

	if(is_user_logged_in()){
		// redirect
	}

	$form = support_system_register_form($atts);
	return $form;
}

add_shortcode( 'support_system', 'support_system_2' );
function support_system_2(){

}

function support_system_login_form($atts = array()){
	
	extract( shortcode_atts( array(
		'title' => 'Login Form',
		'message' => 'Fill out the form below to login, Fields prefixed with * are manditory.'
	), $atts ) );

	$form = new WP_Engine_Form('Login');

	$errors = $form->errors();

	$output = '<div class="form_wrapper">';
	$output .= '<div class="form_header">';
	$output .= "<h1>$title</h1>";
	$output .= "<p>$message</p>";
	$output .= '</div>';

	if(isset($errors['message']) && !empty($errors['message'])){
		$output .= '<div class="error_msg warn"><p>' . $errors['message']. '</p></div>';
	}

	// $errors = $form->errors();

	$output .= $form->create();
	$output .= $form->text('user_email', array('required' => true, 'label' => 'Username'));
	$output .= $form->password('user_pass', array('required' => true, 'label' => 'Password'));
	$output .= $form->end('Login');
	$output .= '</div>';
	return $output;
}

function support_system_register_form($atts = array()){

	extract( shortcode_atts( array(
		'title' => 'Registration Form',
		'message' => 'Fill out the form below to login, Fields prefixed with * are manditory.'
	), $atts ) );

	$form = new WP_Engine_Form('Register');

	$errors = $form->errors();

	$output = '<div class="form_wrapper">';

	// display form header
	if($title !== 'false' || $message !== 'false'){
		$output .= '<div class="form_header">';

		if($title !== 'false')
			$output .= "<h1>$title</h1>";
		
		if($message !== 'false')
			$output .= "<p>$message</p>";
		
		$output .= '</div>';
	}

	if(isset($errors['message']) && !empty($errors['message'])){
		$output .= '<div class="error_msg warn"><p>' . $errors['message']. '</p></div>';
	}

	$success = $form->complete();
	if(!$success)
	{
		$errors = $form->errors();
		
		$output .= $form->create();
		$output .= '<div class="form_cols_2">';
		$output .= $form->text('user_first_name', array('required' => true, 'label' => 'First Name', 'class' => 'two_col_left'));
		$output .= $form->text('user_last_name', array('required' => true, 'label' => 'Last Name', 'class' => 'two_col_right'));
		$output .= '</div>';

		$output .= $form->text('user_email', array('required' => true, 'label' => 'Email'));
		
		$output .= '<div class="form_cols_2">';
		$output .= $form->password('user_pass', array('required' => true, 'label' => 'Password', 'class' => 'two_col_left'));
		$output .= $form->password('user_pass2', array('required' => true, 'label' => 'Re-Type Password', 'class' => 'two_col_right'));
		$output .= '</div>';
		
		
		$output .= $form->end('Register');
	}else{
		$login_url = get_option('support_login_url');
		$login = isset($login_url['login_url']) && !empty($login_url['login_url']) ? $login_url['login_url'] : add_query_arg('support-action', 'login');
		$output .= '<div class="error_msg success"><p>' . $success. ', please <a href="'.$login.'">click here</a> to login.</p></div>';
	}
	$output .= '</div>';
	return $output;
}

function support_system_form($name = ''){
	
	// load form helper
	$output = '';

	switch($name){
		case 'login':
			$output .= support_system_login_form();
		break;
		case 'register':
			$output .= support_system_register_form();
		break;
		case 'create_ticket':
		break;
	}

	return $output;
}


// get form shortcode
add_shortcode('supportSystem', 'support_system');
function support_system( $atts , $content = null )
{
	$view = get_query_var('support-action');
	
	if(empty($view))
		$view = 'index';
	
	$output = '';
	extract( shortcode_atts( array(
	), $atts ) );

	$config =& Support_System_Singleton::getInstance();

	// Get params and render view.
	switch($view)
	{

		case 'register':
		{
			$output .= support_system_register_form();
			break;
		}
		case 'login':
		{
			$output .= support_system_login_form();
			break;
		}
		case 'view':
		case 'add':
		case 'index':
		case 'browse':
		{
			if ($config->require_account == 1 && !current_user_can( 'read' )){
				$view = 'denied';
			}

			ob_start();
			include 'views/member/'.$view.'.php';
			$myvar = ob_get_contents();
			ob_end_clean();
			$output .= $myvar;
			break;
		}
		default:
		{
			break;
		}
	}
	return $output;
}