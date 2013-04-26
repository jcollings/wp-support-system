<?php  
/*
	Plugin Name: WP Support System V2
	Plugin URI: http://www.jamescollings.co.uk
	Description: Support System Plugin V2
	Version: 0.0.2
	Author: James Collings
	Author URI: http://www.jamescollings.co.uk
 */

/*  Copyright 2013  James Collings  (email : james@jclabs.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

$SupportSystem = new SupportSystem();

class SupportSystem{

	var $version = '0.0.2';
	var $plugin_dir = false;
	var $plugin_url = false;
	var $support_page = 4;
	var $require_account = 1;
	var $default_support_group = 0;
	var $ticket_expiry = 7;
	var $email_domain = false;
	var $notifications = array(
		'user' => array(
			'msg_title' => 'Support Ticket #{ticket_id} has been sent',
			'msg_body' => 'Hi {name},
We will try and get your problem sorted soon as, but in the mean time have you checked our knowledgebase.
Regards
Theme Dev Team'
		),
		'admin' => array(
			'msg_title' => '{priority} - Ticket #{ticket_id}',
			'msg_body' => '{name} has raised a new support ticket.
Subject: {subject}
Message: {message}'
		)
	);

	function __construct(){

		$this->plugin_dir =  plugin_dir_path( __FILE__ );
		$this->plugin_url = plugins_url( '/', __FILE__ );
		$this->load_modules();
		$this->load_settings();

		add_action('init', array($this, 'register'));
		add_action('query_vars', array($this, 'register_query_vars') );
		add_filter('generate_rewrite_rules', array($this, 'rewrite_rules'));
	}

	/**
	 * Load Libraries
	 * 
	 * Setup and load all modules for the plugin, passing this class as the config
	 * 
	 * @return void
	 */
	function load_modules(){

		include 'functions.php';
		include 'TicketModel.php';
		include 'helpers/form.php';
		include 'TicketNotification.php';
		include 'TicketEmail.php';

		// Load Ticket Admin
		include 'TicketAdmin.php';
		$TicketAdmin = new TicketAdmin($this);

		// Load Department Admin
		include 'DepartmentAdmin.php';
		$DepartmentAdmin = new DepartmentAdmin($this);

		// Load Ticket View Controller
		include 'TicketViewController.php';
		$TicketViewController = new TicketViewController($this);

		// Load Ticket View
		include 'TicketView.php';
		$TicketView = new TicketView($this);

		TicketNotification::init($this);
		TicketModel::init($this);
		TicketEmail::init($this);
	}

	/**
	 * Load Settings
	 * 
	 * Get saved settings from database, or used defaults.
	 * 
	 * @return void
	 */
	function load_settings(){
		
		// check if user notifications exist
        $user_notifications = get_option('notification_user');
        if(isset($user_notifications) && !empty($user_notifications)){
            $this->notifications['user'] = $user_notifications;
        }

        // check if admin notification exist
        $admin_notifications = get_option('notification_admin');
        if(isset($admin_notifications) && !empty($admin_notifications)){
            $this->notifications['admin'] = $admin_notifications;
        }

        // check if an account is required to submit a ticket
        $config = get_option('support_system_config');
        if(!empty($config)){
        	if(isset($config['require_account']))
	            $this->require_account = $config['require_account'];
            if(isset($config['default_group']))
	            $this->default_support_group = $config['default_group'];
            if(isset($config['support_page']))
		        $this->support_page = $config['support_page'];
            if(isset($config['email_domain']))
           		$this->email_domain = $config['email_domain'];
           	if(isset($config['ticket_expiry']))
           		$this->ticket_expiry = $config['ticket_expiry'];
        }

        // setup email domain
        if(empty($this->email_domain)){
	        $site_url = str_replace(array(
	        	'https://www.', 
	        	'http://www.',
	        	'https://',
	        	'http://'
	        ), '', site_url( '/'));
	        $parts = explode('/', $site_url);
			$this->email_domain = $parts[0];
		}

	}

	/**
	 * Register Post Types
	 * 
	 * @return void
	 */
	function register() {

		$result = add_role('member', 'Member', array(
		    'read' => true, // True allows that capability
		    'edit_posts' => true,
		    'delete_posts' => false, // Use false to explicitly deny,
		    'view_member_content' => true
		));

		register_post_type( 'supportmessage', 
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
				'update_count_callback' => array($this, 'update_post_count'),
				'taxonomies' => array('support_groups'),
				// 'show_ui' => true,
				'public' => false,
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

		register_post_type( 'st_comment_internal', 
			array(
				'capability_type' => 'post',
				// 'rewrite' => array('slug' => ''),
				'query_var' => false,
				'has_archive' => false,
				'show_in_nav_menus' => false,
				'labels' => array(
					'name' => __('Support Messages'),
				),
				'show_ui' => false,
				'public' => false,
			)
		);
	}

	/**
	 * Register Query Vars
	 * 
	 * @param  array $public_query_vars 
	 * @return array
	 */
	function register_query_vars($public_query_vars) {
		$public_query_vars[] = 'support-action';
		$public_query_vars[] = 'ticket_id';
		return $public_query_vars;
	}

	/**
	 * Rewrite Rules
	 * 
	 * @param  array $wp_rewrite 
	 * @return array
	 */
	function rewrite_rules($wp_rewrite) {
		$wp_rewrite->rules = array_merge(
			array(
				'^support/([^/]+)/?$' => 'index.php?page_id='.$this->support_page.'&support-action=$matches[1]',
				'^support/view/([^/]+)/?$' => 'index.php?page_id='.$this->support_page.'&support-action=view&ticket_id=$matches[1]',
			), $wp_rewrite->rules );
		return $wp_rewrite;
	}

	/**
	 * Setup plugin on activation
	 * 
	 * @return void
	 */
	function activate(){
	}

	/**
	 * Deactivate Plugin
	 * 
	 * @return void
	 */
	function deactivate(){
	}

}
?>