<?php
$form = new WP_Engine_Form('SubmitTicket');

$success = $form->complete();
if(!$success)
{
	print_r($form->errors());

	$output = $form->create();
	$output .= $form->text('SupportSubject', array('required' => true, 'label' => 'Subject'));
	$output .= $form->select('SupportImportance', array('required' => true, 'label' => 'Importance', 'options' => array(0 => 'low', 5 => 'medium', 10 => 'high')));
	$output .= $form->textarea('SupportMessage', array('required' => true, 'label' => 'Message'));
	$output .= $form->end('Submit Ticket');
}else{
	$output .= '<p>'.$success.'</p>';
}
?>