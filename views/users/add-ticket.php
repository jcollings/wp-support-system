<?php 
global $current_user;
$current_user = wp_get_current_user();

if(!FormHelper::is_complete()){

	echo FormHelper::create('SubmitTicket', array(
		'title' => 'Submit Support Ticket',
		'desc' => 'Please fill out the form below, with your problem. A member of our staf will try to get back to you as quickly as possible.',
	));

	if($current_user->ID == 0){
		echo FormHelper::text('name');
		echo FormHelper::text('email');
	}
	echo FormHelper::text('subject');
	echo FormHelper::textarea('message');
	?>
	<div class="form_cols_2">
	<?php
	if(count($groups) > 1){
		echo FormHelper::select('group', array('options' => $groups, 'empty' => true, 'class' => 'two_col_left'));
	}else{
		$key = 0;
		foreach($groups as $k => $g){
			$key = $k;
		}	
		echo FormHelper::hidden('group', array('value' => $key, 'class' => 'two_col_left'));
	}
	?>
	<?php 
	if(count($groups) > 1){
		echo FormHelper::select('priority', array('options' => array(0 => 'low', 5 => 'medium', 10 => 'high'), 'empty' => true, 'default' => 5, 'class' => 'two_col_right'));
	}else{
		echo FormHelper::select('priority', array('options' => array(0 => 'low', 5 => 'medium', 10 => 'high'), 'empty' => true, 'default' => 5, 'class' => 'two_col_left'));	
	}
	?>
	</div>
	<?php echo FormHelper::end('Send Ticket');
}else{
	echo '<p>Your form has been submitted</p>';
}
?>