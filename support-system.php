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

	public function __construct(){
		$this->config =& Support_System_Singleton::getInstance();

		add_action('init', array($this, 'register_support_system'));
        add_action('plugins_loaded', array($this, 'plugins_loaded'));
        add_filter('post_class', array($this, 'system_body_class'));
		add_filter('body_class', array($this, 'system_body_class'));
		add_filter('query_vars', array($this, 'register_query_vars'));
	}

	public function plugins_loaded(){	
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

		register_taxonomy(  
			'support_groups',  
			'supportmessage',  
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

	function update_post_count($terms, $taxonomy)
	{
	    global $wpdb;
	    foreach ( (array) $terms as $term)
	    {
	        do_action( 'edit_term_taxonomy', $term, $taxonomy );

	        // Do stuff to get your count
	        $count = 15;

	        $wpdb->update( $wpdb->term_taxonomy, array( 'count' => $count ), array( 'term_taxonomy_id' => $term ) );
	        do_action( 'edited_term_taxonomy', $term, $taxonomy );
	    }
	}

	/**
	 * Add support class to body
	 */
	public function system_body_class($classes) {
		$query_var = get_query_var('support-action');

		if(!empty($query_var) || is_page(80))
			$classes[] = 'support-system';

		return $classes;
	}
}