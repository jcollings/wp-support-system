<?php
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

	// Get params and render view.
	switch($view)
	{

		case 'register':
		{
			$form = new WP_Engine_Form('Register');
			$success = $form->complete();
			if(!$success)
			{
				print_r($form->errors());
				

				$output = $form->create();
				$output .= $form->text('user_login', array('required' => true, 'label' => 'Username'));
				$output .= $form->password('user_pass', array('required' => true, 'label' => 'Password'));
				$output .= $form->password('user_pass2', array('required' => true, 'label' => 'Re-Type Password'));
				$output .= $form->text('user_email', array('required' => true, 'label' => 'Email'));
				$output .= $form->text('user_first_name', array('required' => true, 'label' => 'First Name'));
				$output .= $form->text('user_last_name', array('required' => true, 'label' => 'Last Name'));
				$output .= $form->text('user_purchase_code', array('required' => true, 'label' => 'Purchase Code'));
				$output .= $form->end('Register');

				return $output;
			}else{
				return '<p>'.$success.'</p>';
			}
			break;
		}
		case 'login':
		{
			$form = new WP_Engine_Form('Login');

			$output = $form->create();
			$output .= $form->text('user_email', array('required' => true, 'label' => 'Username'));
			$output .= $form->password('user_pass', array('required' => true, 'label' => 'Password'));
			$output .= $form->end('Login');

			return $output;
			break;
		}
		case 'view':
		case 'add':
		case 'index':
		case 'browse':
		{
			if (!current_user_can( 'read' )){
				$view = 'denied';
			}

			ob_start();
			include 'views/public/'.$view.'.php';
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