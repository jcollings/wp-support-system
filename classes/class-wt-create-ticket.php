<?php
class WT_CreateTicket{

	public function __construct(){
		add_action('wt/after_ticket_create', array($this, 'set_ticket_access'));
		add_action('wp_loaded', array($this, 'process_forms'));

		add_action('wt/process_add_ticket', array($this, 'process_form_add_ticket'));
		add_action('wt/process_add_comment', array($this, 'process_form_add_comment'));
		add_action('wt/process_public_view_ticket', array($this, 'process_form_public_view_ticket'));

		
	}


	public function set_ticket_access($ticket_id){
		// public or private
	}

	public function process_forms(){
		
		$action = isset($_POST['wptickets-action']) && !empty($_POST['wptickets-action']) ? $_POST['wptickets-action'] : false;

		if(!$action)
			return false;
		do_action('wt/process_'.$action);
	}

	public function process_form_add_ticket(){

		global $wptickets;

		$title = isset($_POST['subject']) && !empty($_POST['subject']) ? $_POST['subject'] : false ;
		$message = isset($_POST['message']) && !empty($_POST['message']) ? $_POST['message'] : false ;
		$email = isset($_POST['email']) && !empty($_POST['email']) ? $_POST['email'] : false ;
		$name = isset($_POST['name']) && !empty($_POST['name']) ? $_POST['name'] : false ;
		$department = isset($_POST['department']) && !empty($_POST['department']) ? $_POST['department'] : false ;
		$access = isset($_POST['ticket_access']) && !empty($_POST['ticket_access']) ? $_POST['ticket_access'] : false ;
		
		$user_id = 0;
		$args = array();

		if(is_user_logged_in()){
			$user_id = get_current_user_id();
		}

		if($user_id == 0){
			$args['user_email'] = $email;
			$args['user_name'] = $name;
		}

		if($access){
			$args['access'] = $access;
		}

		if($department){
			$args['department'] = $department;
		}

		if( ($title && $message && $user_id > 0)
			|| ($title && $message && $user_id == 0 && $email && $name)){
			
			// insert ticket
			$ticket_id = $wptickets->tickets->insert_ticket($title, $message, $user_id, $args);

			// redirect to newly created ticket
			wp_safe_redirect( get_permalink($ticket_id ) );
			exit();

		}else{
			$wptickets->session->add_notification('Please fill in all required fields', 'form_add_ticket');
		}
	}

	public function process_form_public_view_ticket(){

		global $wptickets;

		$ticket_email = isset($_POST['ticket_email']) && !empty($_POST['ticket_email']) ? $_POST['ticket_email'] : false ;
		$ticket_key = isset($_POST['ticket_key']) && !empty($_POST['ticket_key']) ? $_POST['ticket_key'] : false ;

		if(!$ticket_email || !$ticket_key){
			$wptickets->session->add_notification('Please fill in all required fields', 'form_view_ticket');
			return;
		}


		$ticket = $wptickets->tickets->get_public_ticket($ticket_email, $ticket_key);
		if($ticket){
			$ticket_id = $ticket->post->ID;
			$wptickets->session->set_access_code($ticket_email, $ticket_key);
			wp_redirect(get_permalink($ticket_id ));
			exit;
		}else{
			$wptickets->session->add_notification('No Ticket Exists', 'form_view_ticket');
		}
	}

	public function process_form_add_comment(){

		global $wptickets;

		$ticket_id = isset($_POST['ticket_id']) && !empty($_POST['ticket_id']) ? $_POST['ticket_id'] : false ;
		$message = isset($_POST['response']) && !empty($_POST['response']) ? $_POST['response'] : false ;
		$access = isset($_POST['access']) && $_POST['access'] == 'private' ? $_POST['access'] : 'public' ;
		$user_id = 0;

		if(!$message)
			return false;

		// failed to auth public
		if(!is_user_logged_in() && !$wptickets->session->check_access_code($ticket_id))
			return false;

		if(is_user_logged_in()){
			$user_id = get_current_user_id();
		}

		$args = array(
			'access' => $access
		);

		$result = $wptickets->tickets->insert_comment($ticket_id, $message, $user_id, $args);
		if($result){
			wp_redirect(get_permalink($ticket_id ));
			exit;
		}
		return false;
	}
}

new WT_CreateTicket();