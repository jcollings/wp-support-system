<?php
/**
 * Register and display custom ticket columns
 *
 * Add new columns to ticket post type, and make taxonomies sortable
 * @author James Collings <james@jclabs.co.uk>
 * @since 0.0.2
 * @todo: Add filter drop downs, modify bulk edit forms
 */

class WT_Admin_TicketArchive{

	public function __construct(){

		add_filter('manage_ticket_posts_columns', array($this, 'add_ticket_column_heading'));
		add_action('manage_ticket_posts_custom_column', array($this, 'add_ticket_column_content'), 10, 2);
		add_filter( 'manage_edit-ticket_sortable_columns', array($this, 'sortable_ticket_column') );
	}

	/**
	 * Add custom column headings
	 * 
	 * @param array $data
	 */
	public function add_ticket_column_heading($data){

		$data['priority'] = 'Priority';
		return $data;
	}

	/**
	 * Add custom column content
	 * 
	 * @param string $column    
	 * @param int $ticket_id
	 */
	public function add_ticket_column_content($column, $ticket_id){

		switch($column){
			case 'priority':
				echo wt_get_ticket_priority($ticket_id);
			break;
		}
	}

	/**
	 * Register ticket columns as sortable
	 * 
	 * @param  array $columns
	 * @return array
	 */
	public function sortable_ticket_column( $columns ) {
	    
	    $columns['priority'] = 'priority';
	    $columns['taxonomy-department'] = 'taxonomy-department';
	    $columns['taxonomy-status'] = 'taxonomy-status';
	    return $columns;
	}
}

new WT_Admin_TicketArchive();
