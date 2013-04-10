<?php 
/**
 * Ticket View
 * 
 * Set variables and display the current view
 * 
 * @author James Collings <james@jclabs.co.uk>
 * @package Support System
 * @since 0.0.2
 */
class TicketView{

	private $config = null;

	/**
	 * Add Hooks
	 * 
	 * @param class &$config 
	 */
	function __construct(&$config){
		$this->config = $config;

		add_shortcode( 'support_system_login', array( $this, 'show_login' ));
		add_shortcode( 'support_system_register', array( $this, 'show_register' ));
		add_shortcode( 'support_system', array( $this, 'show_support_system' ));

		add_action( 'wp_enqueue_scripts', array($this, 'public_scripts' ));
		add_action( 'wp_enqueue_scripts', array($this, 'public_styles' ));
	}

	/**
     * Inject Javascript
     * 
     * @return void
     */
	public function public_scripts()
	{
		wp_enqueue_script('support-public-js', $this->config->plugin_url . 'assets/js/public.js');
	}

	/**
	 * Inject Stylesheets
	 * 
	 * @return void
	 */
	public function public_styles()
	{
		wp_enqueue_style( 'support-public-css', $this->config->plugin_url . 'assets/css/public.css');
	}

	/**
	 * Display Support System
	 * 
	 * Choose which page to display
	 * 
	 * @param  array  $atts 
	 * @return void
	 */
	function show_support_system($atts = array()){

		$action = get_query_var('support-action');

		switch($action){
			case 'login':
				return $this->show_login();
			break;
			case 'register':
				return $this->show_register();
			break;
			case 'view':
				return $this->show_ticket_view();
			break;
			case 'add':
				return $this->show_ticket_add();
			break;
			default:
				return $this->show_index();
			break;
		}

		
	}

	/**
	 * Display Add Ticket Page
	 * 
	 * @return string
	 */
	function show_ticket_add(){

		// show denied if not logged in
		if($this->config->require_account == 1 && !is_user_logged_in())
			return $this->show_denied();

		// set groups
		$groups = array();
		$terms = get_terms( 'support_groups', array('hide_empty' => false));
		foreach($terms as $term){
			if($term->term_id != $this->config->default_support_group)
				$groups[$term->term_id] = $term->name;
		}

		return $this->load_view('users/add-ticket', array('groups' => $groups));
	}

	/**
	 * Show Ticket Homepage
	 * 
	 * @return string
	 */
	function show_index(){

		// show denied if not logged in
		if($this->config->require_account == 1 && !is_user_logged_in())
			return $this->show_denied();

		return $this->load_view('users/index');	
	}

	/**
	 * Show Access Denied Page
	 * 
	 * @return void
	 */
	function show_denied(){
		return $this->load_view('users/denied');
	}

	/**
	 * Show Single Ticket Page
	 * 
	 * @return void
	 */
	function show_ticket_view(){

		// show denied if not logged in
		if($this->config->require_account == 1 && !is_user_logged_in())
			return $this->show_denied();

		return $this->load_view('users/view-ticket');
	}

	/**
	 * Show Login Page
	 * 
	 * @param  array  $atts 
	 * @return string
	 */
	function show_login($atts = array()){
		return $this->load_view('users/login');
	}

	/**
	 * Show Register Page
	 * @param  array  $atts 
	 * @return string
	 */
	function show_register($atts = array()){
		return $this->load_view('users/register');
	}

	/**
	 * Load View
	 * 
	 * return chosen file as string
	 * 
	 * @param  boolean $file 
	 * @param  array   $vars 
	 * @return string
	 */
	private function load_view($file = false, $vars = array()){

		foreach($vars as $a => $b){
			$$a = $b;
		}

		ob_start();
		include 'views/'.$file.'.php';
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

}

?>