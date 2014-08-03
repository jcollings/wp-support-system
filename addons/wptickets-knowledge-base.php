<?php

class WT_KnowledgeBase{

	public function __construct(){

		add_shortcode( 'wptickets_knowledgebase', array($this, 'shortcode_knowledgebase') );
		add_action('wptickets_init', array($this, 'plugins_loaded'));
	}

	public function plugins_loaded(){

		$this->init_post_type();
	}

	public function init_post_type(){
		$labels = array(
			'name'                => __( 'Articles', 'wp-tickets' ),
			'singular_name'       => __( 'Article', 'wp-tickets' ),
			'add_new'             => _x( 'Add New Article', 'wp-tickets', 'wp-tickets' ),
			'add_new_item'        => __( 'Add New Article', 'wp-tickets' ),
			'edit_item'           => __( 'Edit Article', 'wp-tickets' ),
			'new_item'            => __( 'New Article', 'wp-tickets' ),
			'view_item'           => __( 'View Article', 'wp-tickets' ),
			'search_items'        => __( 'Search Articles', 'wp-tickets' ),
			'not_found'           => __( 'No Articles found', 'wp-tickets' ),
			'not_found_in_trash'  => __( 'No Articles found in Trash', 'wp-tickets' ),
			'parent_item_colon'   => __( 'Parent Article:', 'wp-tickets' ),
			'menu_name'           => __( 'Knowledgebase', 'wp-tickets' ),
		);

		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			'description'         => 'Support Knowledgebase',
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=ticket',
			'show_in_admin_bar'   => false,
			'menu_position'       => null,
			'menu_icon'           => null,
			'show_in_nav_menus'   => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => true,
			'has_archive'         => true,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => array('slug' => 'knowledgebase'),
			'capability_type'     => 'post',
			'supports'            => array(
				'title', 'editor', 'comments'
			)
		);

		register_post_type( 'knowledgebase', $args );
	}

	public function shortcode_knowledgebase(){

	}
}
new WT_KnowledgeBase();