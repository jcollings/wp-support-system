<?php
$form = new WP_Engine_Form('SubmitTicket');
$success = $form->complete();
if(!$success)
{
	$options = array('Please Select a Group');
	$terms = get_terms( 'support_groups', array('hide_empty' => false));
	foreach($terms as $term){
		$options[$term->term_id] = $term->name;
	}

	$output = "<h1>Submit Support Ticket</h1>";
	$output .= "<p>Please fill out the form below, with your problem. A member of our staf will try to get back to you as quickly as possible.</p>";
	$output .= $form->create();
	$output .= $form->text('SupportSubject', array('required' => true, 'label' => 'Subject'));
	$output .= $form->textarea('SupportMessage', array('required' => true, 'label' => 'Message'));
	$output .= '<div class="form_cols_2">';
	$output .= $form->select('SupportGroup', array('required' => true, 'label' => 'Sector', 'options' => $options, 'class' => 'two_col_left'));
	$output .= $form->select('SupportImportance', array('required' => true, 'label' => 'Importance', 'options' => array(0 => 'low', 5 => 'medium', 10 => 'high') , 'class' => 'two_col_right'));
	$output .= '</div>';
	$output .= $form->end('Submit Ticket');
}else{
	$output = "<h1>Submit Support Submitted</h1>";
	$output .= '<p>'.$success.'</p>';
}
?>