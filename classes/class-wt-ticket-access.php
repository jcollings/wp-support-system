<?php
class WT_TicketAccess{

	public function __construct(){

		add_action('template_redirect', array($this, 'check_ticket_access'));

		add_action('wt/process_login_account', array($this, 'process_form_login_account'));
		add_action('wt/process_register_account', array($this, 'process_form_register_account'));
	}

	public function check_ticket_access(){

		global $post, $wp_query, $wptickets;

		// ticket archive, modify query
		if(is_post_type_archive( 'ticket' ) || ( !is_singular('ticket') && is_main_query() && get_query_var('post_type') == 'ticket' )){
			$wp_query = $wptickets->tickets->get_tickets(array('paged' => get_query_var('paged'), 'posts_per_page' => 10));
		}

		if(is_user_logged_in() || is_user_admin())
			return;

		if(is_singular( 'ticket' )){

			// public with key and email 
			if( ( $wptickets->allow_public && !is_user_logged_in() && $wptickets->session->check_access_code( $post->ID ) ) || wt_get_ticket_access() == 'public' ){
				return;
			}

			// redirect if not allowed
			$wp_query->set_404();
			status_header(404);
		}
	}

	/**
	 * Process Registration form
	 * @return void
	 */
	public function process_form_register_account(){
		global $wptickets;

		$email = isset($_POST['email']) && !empty($_POST['email']) ? $_POST['email'] : false;
		$password = isset($_POST['password']) && !empty($_POST['password']) ? $_POST['password'] : false;
		$first_name = isset($_POST['first_name']) && !empty($_POST['first_name']) ? $_POST['first_name'] : false;
		$surname = isset($_POST['surname']) && !empty($_POST['surname']) ? $_POST['surname'] : false;

		$user = wp_insert_user(array(
			'user_login'	=>	$email,
			'user_pass'	=>	$password,
			'first_name'	=>	$first_name,
			'last_name'	=>	$surname,
			'user_email'	=>	$email,
			'display_name'	=>	$first_name . ' ' . $surname,
			'nickname'	=>	$first_name . ' ' . $surname,
			'role'		=>	'member'
		));

		if ( !is_wp_error($user) ){
			wp_redirect(site_url('/support/'));
		}else{
			// error registering - $user->get_error_message()
			$wptickets->session->add_notification($user->get_error_message(), 'form_member_register');
		}

	}

	/**
	 * Process login form
	 * @return void
	 */
	public function process_form_login_account(){
		global $wptickets;

		$username = isset($_POST['email']) && !empty($_POST['email']) ? $_POST['email'] : false;
		$password = isset($_POST['password']) && !empty($_POST['password']) ? $_POST['password'] : false;

		$user = wp_signon( array(
			'user_login' => $username,
			'user_password' => $password,
			'remember' => true
		), false );

		if ( !is_wp_error($user) ){
			// logged in
			wp_redirect(site_url('/support/'));
			exit();
		}else{
			// Username and password don`t match
			$wptickets->session->add_notification('Username and password don\'t match', 'form_member_login');
		}
		
	}
}
new WT_TicketAccess();