<?php
global $current_user;
$current_user = wp_get_current_user();

$output = '<p><a href="' .get_permalink(get_query_var( 'page_id' )). '">Back to Open Tickets</a></p>';
$form = new WP_Engine_Form('SubmitTicket');
$success = $form->complete();
if(!$success)
{
	$options = array('Please Select a Group');
	$terms = get_terms( 'support_groups', array('hide_empty' => false));
	foreach($terms as $term){
		$options[$term->term_id] = $term->name;
	}

	$errors = $form->errors();

	$output .= '<div class="form_wrapper">';
	$output .= '<div class="form_header">';
	$output .= "<h1>Submit Support Ticket</h1>";
	$output .= "<p>Please fill out the form below, with your problem. A member of our staf will try to get back to you as quickly as possible.</p>";
	$output .= "<p>Fields prefixed with * are have to be filled out.</p>";
	$output .= '</div>';

	if(isset($errors['message']) && !empty($errors['message'])){
		$output .= '<div class="error_msg warn"><p>' . $errors['message']. '</p></div>';
	}

	$output .= $form->create();
	if($current_user->ID == 0){
		$output .= $form->text('SupportUserName', array('required' => true, 'label' => 'Name'));	
		$output .= $form->text('SupportUserEmail', array('required' => true, 'label' => 'Email'));
	}

	$output .= $form->text('SupportSubject', array('required' => true, 'label' => 'Subject'));
	$output .= $form->textarea('SupportMessage', array('required' => true, 'label' => 'Message'));
	$output .= '<div class="form_cols_2">';
	$output .= $form->select('SupportGroup', array('required' => true, 'label' => 'Sector', 'options' => $options, 'class' => 'two_col_left'));
	$output .= $form->select('SupportImportance', array('required' => false, 'label' => 'Importance', 'options' => array(0 => 'low', 5 => 'medium', 10 => 'high') , 'class' => 'two_col_right'));
	$output .= '</div>';
	$output .= $form->end('Submit Ticket');
	$output .= '</div>';
}else{
	$output .= '<div class="form_header">';
	$output .= "<h1>Submit Support Submitted</h1>";
	$output .= '</div>';
	$output .= '<div class="error_msg success"><p>' . $success. '</p></div>';
}
?>