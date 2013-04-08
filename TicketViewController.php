<?php 
/**
 * Ticket View Controller
 * 
 * Processes frontend froms.
 * 
 * @author James Collings <james@jclabs.co.uk>
 * @package Support System
 * @since 0.0.2
 */
class TicketViewController{

	private $config = null;

	/**
	 * Initiate hooks and load config
	 * 
	 * @param class &$config
	 */
	function __construct(&$config){
		$this->config = $config;
		add_action( 'init', array( $this, 'setup_forms'));
		add_action('wp_loaded', array($this, 'process_forms'));
	}

	/**
	 * Setup Form Validation Rules
	 * 
	 * @return void
	 */
	function setup_forms(){
		$forms = array(
			'SubmitTicket' => array(
				'validation' => array(
					'message' => array(
						'rule' => array('required'),
						'message' => 'This Field is required'
					),
					'subject' => array(
						'rule' => array('required'),
						'message' => 'This Field is required'
					)
				)
			),
			'SubmitTicketComment' => array(
				'validation' => array(
					'response' => array(
						'rule' => array('required'),
						'message' => 'This Field is required'
					)
				)
			),
			'Login' => array(
				'validation' => array(
					'name' => array(
						'rule' => array('required'),
						'message' => 'This Field is required'
					),
					'password' => array(
						'rule' => array('required'),
						'message' => 'This Field is required'
					)
				)
			),
			'Register' => array(
				'validation' => array(
					'first_name' => array(
						'rule' => array('required'),
						'message' => 'This Field is required'
					),
					'last_name' => array(
						'rule' => array('required'),
						'message' => 'This Field is required'
					),
					'email' => array(
						array(
							'rule' => array('required'),
							'message' => 'This Field is required'
						),
						array(
							'rule' => array('email'),
							'message' => 'Please Enter a valid email'
						),
					),
					'pass' => array(
						array(
							'rule' => array('required'),
							'message' => 'This Field is required'
						),
						array(
							'rule' => array('min_length', 5),
							'message' => 'Please Enter a password with 5 or more characters'
						)
					),
					'pass_retry' => array(
						array(
							'rule' => array('match', 'pass'),
							'message' => 'Passwords do not match'
						)
					),
				)
			)
		);

		// extra fields when user is not a member
		if(!is_user_logged_in()){
			$forms['SubmitTicket']['validation']['name'] = array(
				'rule' => array('required'),
				'message' => 'This Field is required'
			);
			$forms['SubmitTicket']['validation']['email'] = array(
				array(
					'rule' => array('required'),
					'message' => 'This Field is required'
				),
				array(
					'rule' => array('email'),
					'message' => 'Please enter a valid email address'
				),
			);
		}

		$this->config->forms = $forms;

	}

	/**
	 * Process Submitted Forms
	 * 
	 * @return void
	 */
	function process_forms(){
		FormHelper::init($this->config->forms);

		if(isset($_POST['ticket_form_action'])){
			
			switch($_POST['ticket_form_action']){
				case 'SubmitTicket':
					$this->process_ticket_form();
				break;
				case 'SubmitTicketComment':
					$this->process_response_form();
				break;
				case 'Login':
					$this->process_login_form();
				break;
				case 'Register':
					$this->process_register_form();
				break;
			}

		}
	}

	/**
	 * Process Ticket Form
	 * 
	 * @return void
	 */
	private function process_ticket_form(){
		global $current_user;

		FormHelper::process_form('SubmitTicket');

		if(FormHelper::is_complete()){

			$user_id =  $current_user->ID;
			$importance = intval($_POST['ticket_priority']);
			$group = $_POST['ticket_group'];
			$args = array('importance' => $importance,'group' => $group);

			// if is public 
			if($user_id == 0 && !empty($_POST['ticket_name']) && !empty($_POST['ticket_email'])){
				$args['user_name'] = $_POST['ticket_name'];
				$args['user_email'] = $_POST['ticket_email'];
			}

			$result = TicketModel::insert_ticket($_POST['ticket_subject'], $_POST['ticket_message'], $user_id, $args);
		}

	}

	/**
	 * Process Response Form
	 * 
	 * @return void
	 */
	private function process_response_form(){
		global $current_user;

		FormHelper::process_form('SubmitTicketComment');

		if(FormHelper::is_complete()){

			$ticketId = $_POST['ticket_id'];
			$message = $_POST['ticket_response'];
			$author_id = $current_user->ID;
			TicketModel::insert_comment($ticketId, $message, $author_id);
		}

	}

	/**
	 * Process Login Form
	 * 
	 * @return void
	 */
	private function process_login_form(){
		FormHelper::process_form('Login');
		if(FormHelper::is_complete()){
			$username = $_POST['ticket_name'];
			$password = $_POST['ticket_password'];

			$user = wp_signon( array(
				'user_login' => $username,
				'user_password' => $password,
				'remember' => true
			), false );

			if ( !is_wp_error($user) ){
				if(isset($_POST['ticket_ref']) && !empty($_POST['ticket_ref']))
					wp_redirect($_POST['ticket_ref']);	
				else
					wp_redirect(site_url('/'));
				exit();
			}else{
				FormHelper::set_error('Username and password don`t match');
			}
		}
	}

	/**
	 * Process Register Form
	 * 
	 * @return void
	 */
	private function process_register_form(){
		FormHelper::process_form('Register');
		if(FormHelper::is_complete()){

			$user = wp_insert_user(array(
					'user_login'	=>	$_POST['ticket_email'],
					'user_pass'	=>	$_POST['ticket_pass'],
					'first_name'	=>	$_POST['ticket_first_name'],
					'last_name'	=>	$_POST['ticket_last_name'],
					'user_email'	=>	$_POST['ticket_email'],
					'display_name'	=>	$_POST['ticket_first_name'] . ' ' . $_POST['ticket_last_name'],
					'nickname'	=>	$_POST['ticket_first_name'] . ' ' . $_POST['ticket_last_name'],
					'role'		=>	'member'
			));

			if ( !is_wp_error($user) ){
				if(isset($_POST['ticket_ref']) && !empty($_POST['ticket_ref']))
					wp_redirect($_POST['ticket_ref']);	
				else
					wp_redirect(site_url('/'));
				exit();
			}else{
				FormHelper::set_error($user->get_error_message());
			}
		}
	}

}
?>