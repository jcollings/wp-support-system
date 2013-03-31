<?php
/**
 * @todo edd checks to see if new ticket or response to a ticket
 */

include_once 'api.php';
include_once 'shortcodes.php';

$Knowledgebase_Support_System = new Knowledgebase_Support_System();

class Knowledgebase_Support_System{

	private $config = null;


	public function __construct(){
		$this->config =& Support_System_Singleton::getInstance();

		add_action('init', array($this, 'init'));
        add_action('plugins_loaded', array($this, 'plugins_loaded'));
	}

	public function init(){
		register_post_type( 'knowledgebase_posts', 
			array(
				'capability_type' => 'page',
				'query_var' => 'knowledgebase',
				'has_archive' => false,
				'show_in_nav_menus' => true,
				'show_in_menu' => false,
				'labels' => array(
					'name' => __('Knowledgebase')
				),
				'show_ui' => true,
				'public' => true,
			)
		);
		register_taxonomy(  
			'knowledgebase_cats',  
			'knowledgebase_posts',  
			array(  
				'label' => 'Knowledgebase Categories',  
				'public' => true,
		        'show_in_nav_menus' => true,
		        'show_ui' => true,
		        'show_tagcloud' => false,
		        'show_admin_column' => true,
		        'hierarchical' => true,
		        'rewrite' => true,
		        'query_var' => 'knowledgebase_cats'
			)  
		); 
	}

	public function plugins_loaded(){

		// amend admin menu
		add_action( 'support_system-menu_output', array( $this, 'register_menu_pages' ) );

		// setup settings
		add_action( 'support_system-menu_output_action', array($this, 'add_settings_tab'));
		add_filter( 'support_system-settings_sections', array($this, 'add_settings_sections'),10,1);

		add_action( 'parent_file', array($this, 'knowledgebase_cats_menu_highlight'));
	}

	public function knowledgebase_cats_menu_highlight($parent_file) {
		global $current_screen;
		$taxonomy = $current_screen->taxonomy;
		if ($taxonomy == 'knowledgebase_cats' || $current_screen->post_type == 'knowledgebase_posts')
			$parent_file = 'support-tickets';
		return $parent_file;
	}

	public function register_menu_pages(){
		add_submenu_page('support-tickets', 'Knowledgebase', 'Knowledgebase', 'add_users', 'edit.php?post_type=knowledgebase_posts');
		add_submenu_page('support-tickets', 'Knowledgebase Categories', 'Categories', 'add_users', 'edit-tags.php?taxonomy=knowledgebase_cats');
	}

	public function add_settings_tab(){
		global $tabs;
		$tabs['knowledgebase'] = array(
			'title' => 'Knowledgebase'
		);
	}

	public function add_settings_sections($sections){
		$fields = array(
    		array('type' => 'text', 'id' => 'kb_title', 'section' => 'knowledgebase', 'setting_id' => 'knowledgebase_title', 'label' => 'Title')
    	);
    	$sections['knowledgebase'] = array(
			'section' => array('page' => 'knowledgebase', 'title' => 'Knowledgebase Settings', 'description' => 'Knowledgebase Description'),
			'fields' => $fields
		);
		return $sections;
	}

}