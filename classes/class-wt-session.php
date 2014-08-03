<?php
class WT_Session{

	private $temp = array();

	public function __construct(){

		add_action('init', array($this, 'start_session'), 1);
		add_action('wp_logout', array($this, 'end_session'));
		add_action('wp_login', array($this, 'end_session'));
	}

	public function start_session(){
		if(!session_id()) {
	        session_start();
	    }
	}

	public function end_session(){
		session_destroy();
	}

	public function set_access_code($email = null, $key = null){

		if(!$email || !$key)
			return false;

		$_SESSION['user_ticket_key'] = $key;
		$_SESSION['user_ticket_email'] = $email;
	}

	public function check_access_code($ticket_id = 0){
		global $wptickets;

		if(!$ticket_id)
			return false;

		if(!isset($_SESSION['user_ticket_key']) || !isset($_SESSION['user_ticket_email']))
			return false;

		$key = $_SESSION['user_ticket_key'];
		$email = $_SESSION['user_ticket_email'];

		if($wptickets->tickets->allowed_access($ticket_id, $email, $key))
			return true;

		return false;
	}

	public function add_notification($message = '', $section = 'core', $session = false){

		if($session){

			// store variable in session
			if(!isset($_SESSION['notifications'][$section])){
				$_SESSION['notifications'][$section] = array();
			}

			$_SESSION['notifications'][$section][] = $message;

		}else{

			// store in variable
			if(!isset($this->temp['notifications'][$section])){
				$this->temp['notifications'][$section] = array();
			}

			$this->temp['notifications'][$section][] = $message;
		}
	}

	public function get_notifications($section = 'core', $session = false){

		if($session){
			
			// get from session
			if(isset($_SESSION['notifications'][$section])){
				$notifications = $_SESSION['notifications'][$section];
				unset($_SESSION['notifications'][$section]);
				return $notifications;
			}

		}else{

			// get from variable
			if(isset($this->temp['notifications'][$section])){
				$notifications = $this->temp['notifications'][$section];
				unset($this->temp['notifications'][$section]);
				return $notifications;
			}

		}
		
		return false;	
	}
}