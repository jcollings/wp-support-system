<?php 

class User_Support_System{

	private $config = null;
	private $fields = array(
		'SubmitTicket' => array('SupportSubject', 'SupportMessage', 'SupportGroup'),
		'Register' => array('user_first_name', 'user_last_name', 'user_email', 'user_pass', 'user_pass2'),
		'Login' => array('user_email', 'user_pass')
	);
	
	public function __construct(){
		$this->config =& Support_System_Singleton::getInstance();

		// add_action('init', array($this, 'init'));
		add_action('wp_loaded', array($this, 'process_forms'));
		add_action( 'wp_enqueue_scripts', array($this, 'public_scripts' ));
		add_action( 'wp_enqueue_scripts', array($this, 'public_styles' ));
	}

	/**
     * Inject Javascript
     * @return void
     */
	public function public_scripts()
	{
		wp_enqueue_script('support-public-js', support_plugin_url( 'assets/js/public.js'));
	}

	/**
	 * Inject Stylesheets
	 * @return void
	 */
	public function public_styles()
	{
		wp_enqueue_style( 'support-public-css', support_plugin_url( 'assets/css/public.css'));
	}

	public function process_forms(){

		if(!isset($_POST['SupportFormType']) || empty($_POST['SupportFormType']))
			return;

		global $current_user;
		$current_user = wp_get_current_user();

		$errors = array();

		switch($_POST['SupportFormType']){
			case 'SubmitComment':
				$ticketId = $_POST['TicketId'];
				$message = $_POST['SupportResponse'];
				$author_id = $current_user->ID;

				if(empty($message)){
					$this->setError('SubmitComment', 'Please enter a message');
					return;
				}

				insert_support_comment($ticketId, $message, $author_id);
			break;
			case 'Login':
				foreach($this->fields['Login'] as $field){
					if(empty($_POST[$field])){
						$errors[$field] = 'required';
					}
				}

				// all fileds filled out
				if(!empty($errors)){
					$this->setError('Login', 'Please fill in all the manditory fields marked with a *', $errors);
					return;
				}

				// validate email
				if(!is_email( $_POST['user_email'] )){
					$this->setError('Login', 'Please enter a valid email address');
					return;
				}

				$user = wp_signon( array(
					'user_login' => $_POST['user_email'],
					'user_password' => $_POST['user_pass'],
					'remember' => true
				), false );

				if ( !is_wp_error($user) ){
					if(isset($_POST['prev_ref']) && !empty($_POST['prev_ref']))
						wp_redirect(site_url($_POST['prev_ref']));	
					else
						wp_redirect(site_url('/'));
					exit();
				}else{
					$this->setError('Login', 'Username and password don`t match');
					$_POST['user_pass'] = '';
				}
			break;
			case 'Register':
				foreach($this->fields['Register'] as $field){
					if(empty($_POST[$field])){
						$errors[$field] = 'required';
					}
				}

				if(!empty($errors)){
					set_transient('RegisterField_'.$current_user->ID, $errors, 60);
					set_transient('RegisterValues_'.$current_user->ID, $_POST, 60);
					$this->setError('Register', 'Errors have occured');
					return;
				}

				$user = wp_insert_user(array(
						'user_login'	=>	$_POST['user_email'],
						'user_pass'	=>	$_POST['user_pass'],
						'first_name'	=>	$_POST['user_first_name'],
						'last_name'	=>	$_POST['user_last_name'],
						'user_email'	=>	$_POST['user_email'],
						'display_name'	=>	$_POST['user_first_name'] . ' ' . $_POST['user_last_name'],
						'nickname'	=>	$_POST['user_first_name'] . ' ' . $_POST['user_last_name'],
						'role'		=>	'member'
				));

				if ( !is_wp_error($user) ){
					set_transient('RegisterSuccess_'.$current_user->ID, 'Your Account has been created.', 60);
					return $user;
				}else{
					$this->setError('Register', $user->get_error_message());
				}
			break;
			case 'SubmitTicket':
				foreach($this->fields['SubmitTicket'] as $field){
					if(empty($_POST[$field])){
						$errors[$field] = 'required';
					}
				}

				if(!empty($errors)){
					$this->setError('SubmitTicket', 'Please fill out all required fields', $errors);
					return;
				}

				$user_id =  $current_user->ID;
				$importance = intval($_POST['SupportImportance']);
				$group = $_POST['SupportGroup'];
				$args = array(
					'importance' => $importance,
					'group' => $group
				);

				// if is public 
				if($user_id == 0 && !empty($_POST['SupportUserName']) && !empty($_POST['SupportUserEmail'])){
					$args['user_name'] = $_POST['SupportUserName'];
					$args['user_email'] = $_POST['SupportUserEmail'];
				}

				$result = open_support_ticket($_POST['SupportSubject'], $_POST['SupportMessage'], $user_id, $args);
				if($result){
					set_transient('SubmitTicketSuccess_'.$user_id, 'Your ticket has been raised.', 60);
					return $result;
				}else{
					$this->setError('SubmitTicket', 'An Error occured when submitting your ticket, please try again in a couple of minutes');
				}
			break;
		}
	}

	/**
	 * Set transient errors
	 * @param string $formId  id of current form
	 * @param string $message error message
	 * @param array  $errors  array of all dodgy fields and indevidual errors
	 */
	private function setError($formId, $message, $errors = array()){
		global $current_user;
		$current_user = wp_get_current_user();
		if(!empty($errors))
			set_transient($formId.'Field_'.$current_user->ID, $errors, 60);

		return set_transient($formId.'Error_'.$current_user->ID, $message, 60);
	}

}

if(!is_admin())
	$User_Support_System = new User_Support_System();
?>