<?php
/**
 * Plugin Name: WP Support System
 * Plugin URI: http://jamescollings.co.uk/wordpress-plugins/wp-tickets
 * Description: A Support Ticket system for wordpress
 * Version: 0.0.1
 * Author: James Collings
 * Author URI: http://jamescollings.co.uk
 *
 * @package WPTickets
 * @author James Collings
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Main Plugin Class
 *
 * Load and setup all classes
 */
class WP_Tickets{

	public $version = '0.0.1';
	public $plugin_dir = false;
	public $plugin_url = false;

	public $allow_public = false;
	public $allow_archive = true;
	public $disable_css = false;

	public $ticket_priorities = array();
	public $ticket_default_priority = null;

	public function __construct(){

		// start session
		require_once 'classes/class-wt-session.php';
		$this->session = new WT_Session();

		$this->plugin_dir =  plugin_dir_path( __FILE__ );
		$this->plugin_url = plugins_url( '/', __FILE__ );

		$this->includes();

		add_action('init', array($this, 'init'));
		add_action( 'admin_init', array($this,'on_plugin_activation') );
		add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts' ));

		add_action( 'query_vars' , array( $this, 'register_query_vars' ) );

		do_action('wptickets_loaded');

		register_activation_hook( __FILE__, array( $this, 'activate_plugin' ) );
	}

	public function init(){

		do_action('before_wptickets_init');

		$this->tickets = new WT_TicketModel();

		$this->init_taxonomies();
		$this->init_post_types();

		$this->load_settings();

		do_action('wptickets_init');
	}

	public function includes(){

		if(is_admin()){
			// Admin Includes
			require 'admin/core-includes.php';
		}

		if( defined('DOING_AJAX')){
			// Ajax Includes
		}
		
		// core includes
		require_once 'functions-template.php';
		require_once 'functions-general.php';

		// shortcodes
		require_once 'classes/shortcodes/shortcode-wt-add-ticket.php';
		require_once 'classes/shortcodes/shortcode-wt-support-system.php';
		require_once 'classes/shortcodes/shortcode-wt-archive-ticket.php';

		// classes
		require_once 'classes/class-wt-ticketmodel.php';
		require_once 'classes/class-wt-ticket-access.php';
		require_once 'classes/class-wt-create-ticket.php';
		require_once 'classes/class-wt-ticket-priority.php';
		require_once 'classes/class-wt-ticket-comment.php';
		require_once 'classes/class-wt-email-notification.php';
		require_once 'classes/class-wt-notifications.php';

		// temp addons
		// require_once 'addons/wptickets-imap-email.php';
		// require_once 'addons/wptickets-knowledge-base.php';
	}

	public function init_taxonomies(){
		
		$labels = array(
			'name'					=> _x( 'Departments', 'Taxonomy plural name', 'wp-tickets' ),
			'singular_name'			=> _x( 'Department', 'Taxonomy singular name', 'wp-tickets' ),
			'search_items'			=> __( 'Search Departments', 'wp-tickets' ),
			'popular_items'			=> __( 'Popular Departments', 'wp-tickets' ),
			'all_items'				=> __( 'All Departments', 'wp-tickets' ),
			'parent_item'			=> __( 'Parent Department', 'wp-tickets' ),
			'parent_item_colon'		=> __( 'Parent Department', 'wp-tickets' ),
			'edit_item'				=> __( 'Edit Department', 'wp-tickets' ),
			'update_item'			=> __( 'Update Department', 'wp-tickets' ),
			'add_new_item'			=> __( 'Add New Department', 'wp-tickets' ),
			'new_item_name'			=> __( 'New Department Name', 'wp-tickets' ),
			'add_or_remove_items'	=> __( 'Add or remove Departments', 'wp-tickets' ),
			'choose_from_most_used'	=> __( 'Choose from most used wp-tickets', 'wp-tickets' ),
			'menu_name'				=> __( 'Departments', 'wp-tickets' ),
		);

		$args = array(
			'hierarchical'          => true,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'department' ),
			'capabilities' 			=> array(
				'manage_terms' => 'manage_ticket_departments', 
				'edit_terms' => 'edit_ticket_departments',
				'delete_terms' => 'delete_ticket_departments',
				'assign_terms' => 'assign_ticket_departments'
			)
		);

		register_taxonomy( 'department', 'ticket', $args );

		
		$labels = array(
			'name'					=> _x( 'Ticket Status', 'Taxonomy plural name', 'wp-tickets' ),
			'singular_name'			=> _x( 'Ticket Status', 'Taxonomy singular name', 'wp-tickets' ),
			'search_items'			=> __( 'Search Ticket Status', 'wp-tickets' ),
			'popular_items'			=> __( 'Popular Ticket Status', 'wp-tickets' ),
			'all_items'				=> __( 'All Ticket Status', 'wp-tickets' ),
			'parent_item'			=> __( 'Parent Ticket Status', 'wp-tickets' ),
			'parent_item_colon'		=> __( 'Parent Ticket Status', 'wp-tickets' ),
			'edit_item'				=> __( 'Edit Ticket Status', 'wp-tickets' ),
			'update_item'			=> __( 'Update Ticket Status', 'wp-tickets' ),
			'add_new_item'			=> __( 'Add New Ticket Status', 'wp-tickets' ),
			'new_item_name'			=> __( 'New Ticket Status Name', 'wp-tickets' ),
			'add_or_remove_items'	=> __( 'Add or remove Ticket Status', 'wp-tickets' ),
			'choose_from_most_used'	=> __( 'Choose from most used Ticket Status', 'wp-tickets' ),
			'menu_name'				=> __( 'Ticket Status', 'wp-tickets' ),
		);

		$args = array(
			'hierarchical'          => true,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'status' ),
			'capabilities' 			=> array(
				'manage_terms' => 'manage_ticket_status', 
				'edit_terms' => 'edit_ticket_status',
				'delete_terms' => 'delete_ticket_status',
				'assign_terms' => 'assign_ticket_status'
			)
		);

		register_taxonomy( 'status', 'ticket', $args );
	}

	public function init_post_types(){

		$labels = array(
			'name'                => __( 'Support Tickets', 'wp-tickets' ),
			'singular_name'       => __( 'Ticket', 'wp-tickets' ),
			'add_new'             => _x( 'Add New Ticket', 'wp-tickets', 'wp-tickets' ),
			'add_new_item'        => __( 'Add New Ticket', 'wp-tickets' ),
			'edit_item'           => __( 'View Ticket', 'wp-tickets' ),
			'new_item'            => __( 'New Ticket', 'wp-tickets' ),
			'view_item'           => __( 'View Ticket', 'wp-tickets' ),
			'search_items'        => __( 'Search Tickets', 'wp-tickets' ),
			'not_found'           => __( 'No Tickets found', 'wp-tickets' ),
			'not_found_in_trash'  => __( 'No Tickets found in Trash', 'wp-tickets' ),
			'parent_item_colon'   => __( 'Parent Ticket:', 'wp-tickets' ),
			'menu_name'           => __( 'Support Tickets', 'wp-tickets' ), // main menu renamed in wt-settings
		);

		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			'description'         => 'Support Tickets',
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => null,
			'menu_icon'           => null,
			'show_in_nav_menus'   => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => true,
			'has_archive'         => $this->allow_archive,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => array('slug' => 'tickets'),
			'capability_type'     => 'ticket',
			'map_meta_cap' => true,
			'supports'            => false 
		);

		register_post_type( 'ticket', $args );
	}

	public function register_query_vars($public_query_vars ){
        $public_query_vars[] = 'ticket-author';
        $public_query_vars[] = 'ticket-priority';
        return $public_query_vars;
	}

	public function enqueue_scripts(){
		if($this->disable_css)
			return;

		wp_enqueue_style('support-public-css', $this->plugin_url . 'assets/css/main.css');
	}

	public function load_settings(){
		$config = get_option('support_system_config');
		$this->allow_public = isset($config['require_account']) && $config['require_account'] == 0 ? true : false;
		$this->disable_css = isset($config['disable_css']) && $config['disable_css'] == 1 ? true : false; 

		// print_r($GLOBALS['wp_post_types']['ticket']);
	}

	/**
	 * On plugin activation
	 * @return void
	 */
	public function activate_plugin(){

		add_option( 'wt-activated-plugin', 'wt-tickets' );

		$role = get_role("administrator");
		$caps = array(
			'edit_ticket',
			'read_ticket',
			'delete_ticket',
			'edit_tickets',
			'edit_others_tickets',
			'publish_tickets',
			'read_private_tickets',
			'edit_tickets',
			'delete_tickets',
			'manage_ticket_departments',
			'edit_ticket_departments',
			'delete_ticket_departments',
			'assign_ticket_departments',
			'manage_ticket_status',
			'edit_ticket_status',
			'delete_ticket_status',
			'assign_ticket_status',
			'manage_support_tickets'
		);

		foreach($caps as $cap){
			$role->add_cap($cap);
		}
	}

	/**
	 * On plugin activation ready
	 * @return void
	 */
	public function on_plugin_activation(){

		if ( is_admin() && get_option( 'wt-activated-plugin' ) == 'wt-tickets' ) {

			$config = array();

			// check for department
			$count = get_terms('department', array('hide_empty' => false, 'fields' => 'count'));
			if($count == 0){

				// if no departments set default one
				wp_insert_term( 'General', 'department');
				$config['default_group'] = 'general';
			}

			// check for status
			$count = get_terms('status', array('hide_empty' => false, 'fields' => 'count'));
			if($count == 0){

				// insert new status and ammend settings
				$opened_id = wp_insert_term( 'Opened', 'status');
				$closed_id = wp_insert_term( 'Closed', 'status');
				$author_feedback_id = wp_insert_term( 'Awaiting Reply', 'status');
				$moderator_feedback_id = wp_insert_term( 'Replied', 'status');

				$config['ticket_open_status'] = 'opened';
				$config['ticket_close_status'] = 'closed';
				$config['ticket_responded_status'] = 'replied';
				$config['ticket_reply_status'] = 'awaiting-reply';
			}

			// save config
			if( get_option( 'support_system_config' ) !== false ){

				// update
				$old_support_system_config = get_option( 'support_system_config' );
				$config = array_merge($old_support_system_config, $config);

				update_option( 'support_system_config', $config);

			}else{

				// add new config
				add_option( 'support_system_config', $config);
			}

			// once installed remove activation flag
			delete_option( 'wt-activated-plugin' );
		}
	}
}

add_filter( 'show_post_locked_dialog', 'wpse_120179_close_dialog', 99, 3 );

function wpse_120179_close_dialog( $show, $post, $user )
{
    if( 'ticket' === $post->post_type )
        return FALSE;

    return $show;
}

$GLOBALS['wptickets'] = new WP_Tickets();