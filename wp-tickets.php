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
		add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts' ));

		do_action('wptickets_loaded');
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
		);

		register_taxonomy( 'status', 'ticket', $args );
	}

	public function init_post_types(){

		$labels = array(
			'name'                => __( 'Tickets', 'wp-tickets' ),
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
			'menu_name'           => __( 'Tickets', 'wp-tickets' ),
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
			'capability_type'     => 'post',
			'supports'            => false 

			// array(
			// 	'title', 'editor', 'comments'
			// 	'title', 'editor', 'author', 'thumbnail',
			// 	'excerpt','custom-fields', 'trackbacks', 'comments',
			// 	'revisions', 'page-attributes', 'post-formats'
			// )
		);

		register_post_type( 'ticket', $args );
	}

	public function enqueue_scripts(){
		if($this->disable_css)
			return;

		wp_enqueue_style('support-public-css', $this->plugin_url . 'assets/css/single-ticket.css');
	}

	public function load_settings(){
		$config = get_option('support_system_config');
		$this->allow_public = $config['require_account'] == 0 ? true : false;
		$this->disable_css = isset($config['disable_css']) && $config['disable_css'] == 1 ? true : false; 
	}
}

$GLOBALS['wptickets'] = new WP_Tickets();