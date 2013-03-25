<?php 
/*
Plugin Name: WP Support System
Plugin URI: http://www.jamescollings.co.uk
Description: Support System Plugin
Version: 0.0.1
Author: James Collings
Author URI: http://www.jamescollings.co.uk
 */

define('WP_SUPPORT_SYSTEM_DIR', plugin_dir_path(__FILE__));
define('WP_SUPPORT_SYSTEM_TEMPLATE_DIR', WP_SUPPORT_SYSTEM_DIR . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR);
define('WP_SUPPORT_SYSTEM_ASSETS_DIR', WP_SUPPORT_SYSTEM_DIR . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR );

require_once 'config.php';

require_once 'helpers/form_helper.php';

if(is_admin())
	require_once 'admin.support-system.php';

require_once 'user.support-system.php';
require_once 'funcs.support-system.php';
require_once 'shortcodes.support-system.php';

require_once 'addons/email.support-system.php';
// require_once 'addons/knowledgebase.support-system.php';

global $wpengine_support;
$wpengine_support = new WP_Engine_Support_System();

class WP_Engine_Support_System
{
	private $config = null;
	private $name = 'supportmessage'; 
	private $fields = array(
		'SubmitTicket' => array('SupportSubject', 'SupportMessage', 'SupportGroup'),
		'Register' => array('user_login', 'user_first_name', 'user_last_name', 'user_email', 'user_pass', 'user_pass2'),
		'Login' => array('user_email', 'user_pass')
	);

	public function __construct(){
		$this->config =& Support_System_Singleton::getInstance();
		add_action('init', array($this, 'init'));
        add_action('plugins_loaded', array($this, 'plugins_loaded'));
	}

	public function init(){
		$this->register_support_system();
		$this->process_forms();
	}

	public function plugins_loaded(){
		add_filter('query_vars', array($this, 'register_query_vars'));
	}

	public function register_query_vars($public_query_vars) {
		$public_query_vars[] = 'support-action';
		return $public_query_vars;
	}

	public function register_support_system() {

		$result = add_role('member', 'Member', array(
		    'read' => true, // True allows that capability
		    'edit_posts' => true,
		    'delete_posts' => false, // Use false to explicitly deny,
		    'view_member_content' => true
		));

		register_post_type( $this->name, 
			array(
				'capability_type' => 'post',
				'rewrite' => array('slug' => 'support-tickets'),
				'query_var' => 'support-tickets',
				'has_archive' => true,
				'show_in_nav_menus' => true,
				// 'show_in_menu' => 'support-tickets',
				'labels' => array(
					'name' => __('Support Messages'),
				    'singular_name' => __('Support Message'),
				    'add_new' => _x('Add New', 'Support Message'),
				    'add_new_item' => __('Add Support Message'),
				    'edit_item' => __('Edit Event'),
				    'new_item' => __('New Support Message'),
				    'all_items' => __('All Support Message'),
				    'view_item' => __('View Support Message'),
				    'search_items' => __('Search Support Messages'),
				    'not_found' =>  __('No Support Messages found'),
				    'not_found_in_trash' => __('No Support Messages found in Trash'), 
				    'parent_item_colon' => '',
				    'menu_name' => __('Support Messages')
				),
				'taxonomies' => 'support_groups',
				// 'show_ui' => true,
				'public' => false,
			)
		);
		// register_taxonomy( $taxonomy, $object_type, $args = array )
		register_taxonomy(  
			'support_groups',  
			$this->name,  
			array(  
				'label' => 'Support Ticket Groups',  
				'public' => true,
		        'show_in_nav_menus' => true,
		        'show_ui' => true,
		        'show_tagcloud' => false,
		        'show_admin_column' => true,
		        'hierarchical' => true,
		        'rewrite' => true,
		        'query_var' => 'support_groups'
			)  
		); 

		register_post_type( 'st_comment', 
			array(
				'capability_type' => 'post',
				// 'rewrite' => array('slug' => ''),
				'query_var' => false,
				'has_archive' => false,
				'show_in_nav_menus' => false,
				'labels' => array(
					'name' => __('Support Messages'),
				    'singular_name' => __('Support Message'),
				    'add_new' => _x('Add New', 'Support Message'),
				    'add_new_item' => __('Add Support Message'),
				    'edit_item' => __('Edit Event'),
				    'new_item' => __('New Support Message'),
				    'all_items' => __('All Support Message'),
				    'view_item' => __('View Support Message'),
				    'search_items' => __('Search Support Messages'),
				    'not_found' =>  __('No Support Messages found'),
				    'not_found_in_trash' => __('No Support Messages found in Trash'), 
				    'parent_item_colon' => '',
				    'menu_name' => __('Support Messages')
				),
				'show_ui' => false,
				'public' => false,
			)
		);
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

				if(!empty($errors)){
					$this->setError('Login', 'Errors have occured', $errors);
					return;
				}

				$creds = array();
				$creds['user_login'] = $_POST['user_email'];
				$creds['user_password'] = $_POST['user_pass'];
				$creds['remember'] = true;
				$user = wp_signon( $creds, false );

				if ( !is_wp_error($user) ){
					if(isset($_POST['prev_ref']) && !empty($_POST['prev_ref']))
						wp_redirect(site_url($_POST['prev_ref']));	
					else
						wp_redirect(site_url('/'));
					exit();
				}else{
					$this->setError('Login', $user->get_error_message());
				}
			break;
			case 'Register':
				// http://marketplace.envato.com/api/v3/{USERNAME}/{API-KEY}/verify-purchase:{purchase_code}.json
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

				$user_id = wp_insert_user(array(
						'user_login'	=>	$_POST['user_login'],
						'user_pass'	=>	$_POST['user_pass'],
						'first_name'	=>	$_POST['user_first_name'],
						'last_name'	=>	$_POST['user_last_name'],
						'user_email'	=>	$_POST['user_email'],
						'display_name'	=>	$_POST['user_first_name'] . ' ' . $_POST['user_last_name'],
						'nickname'	=>	$_POST['user_first_name'] . ' ' . $_POST['user_last_name'],
						'role'		=>	'member'
				));

				if ( !is_wp_error($user_id) ){
					set_transient('RegisterSuccess_'.$current_user->ID, 'Your ticket has been raised.', 60);
					return $user_id;
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
				$group = intval($_POST['SupportGroup']);
				$result = open_support_ticket($_POST['SupportSubject'], $_POST['SupportMessage'], $user_id, array(
					'importance' => $importance,
					'group' => $group
				));

				if($result){
					set_transient('SubmitTicketSuccess_'.$user_id, 'Your ticket has been raised.', 60);
					return $result;
				}else{
					$this->setError('SubmitTicket', 'An Error occured when submitting your ticket, please try again in a couple of minutes');
				}
			break;
		}
	}
}




/**
 * Add support class to body
 */
add_filter('post_class', 'support_system_body_class');
add_filter('body_class','support_system_body_class');
function support_system_body_class($classes) {
	$query_var = get_query_var('support-action');

	if(!empty($query_var) || is_page(80))
		$classes[] = 'support-system';

	return $classes;
}