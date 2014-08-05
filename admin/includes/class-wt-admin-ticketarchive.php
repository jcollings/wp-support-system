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
		add_filter( 'pre_get_posts' , array($this, 'sort_ticket_columns'));
		add_action( 'restrict_manage_posts' , array($this,  'wt_restrict_manage_tickets') );

		add_filter( 'views_edit-ticket' , array($this, 'append_tick_view_list'));
	}

	/**
	 * Add custom column headings
	 * 
	 * @param array $data
	 */
	public function add_ticket_column_heading($data){
		$data['ticket-author'] = 'Author';
		$data['priority'] = 'Priority';
		$data['date-modified'] = 'Last Modified';
		unset($data['date']);
		return $data;
	}

	/**
	 * Add custom column content
	 * 
	 * @param string $column    
	 * @param int $ticket_id
	 */
	public function add_ticket_column_content($column, $ticket_id){

		global $wptickets;

		switch($column){
			
			case 'priority':

				echo wt_get_ticket_priority($ticket_id);
			break;
			
			case 'date-modified':

				// get last comment date, or last modified date
				$comments = $wptickets->tickets->get_comments($ticket_id, array('limit' => 1, 'order' => 'DESC', 'type' => 'admin'));
				if( !empty( $comments ) ){
					echo date('F j, Y \a\t g:i a', strtotime($comments[0]->comment_date));
				}else{
					the_time( 'F j, Y \a\t g:i a', $ticket_id );
				}
			break;
			case 'ticket-author':
				echo wt_get_ticket_author_meta($ticket_id, 'email');
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

	/**
	 * Output ticket status where post_status used to be
	 * 
	 * @param  array $list
	 * @return array
	 */
	public function append_tick_view_list($list){
		
		// todo: add class to active status
		$output = array();
		$query_var = get_query_var('status' );
		$class = empty($query_var) ? ' class="current"' : '';

		// count number of posts in total
		$args = array(
		    'post_type' => 'ticket',
		    'numberposts' => -1
		);
		$num = count( get_posts( $args ) );
		$output['all'] = '<a href="' . admin_url('/edit.php?post_type=ticket') . '" ' . $class . '>All (' . $num . ')</a>';

		$status = wt_list_ticket_status();
		if($status){
			foreach($status as $ticket_status){

				// todo: has to be a better way to do this
				$args = array(
				    'post_type' => 'ticket',
				    'status' => $ticket_status->slug,
				    'numberposts' => -1
				);
				$num = count( get_posts( $args ) );
				$class = $query_var == $ticket_status->slug ? ' class="current"' : '';

				if($num > 0){
					$output[$ticket_status->slug] = '<a href="' . admin_url('/edit.php?post_type=ticket&status=' . $ticket_status->slug) . '" ' . $class . '>'.$ticket_status->name . ' (' . $num . ')</a>';	
				}else{
					$output[$ticket_status->slug] = $ticket_status->name . ' (0)';
				}

				
			}
		}

		return $output;
	}

	/**
	 * Order custom columns
	 * 
	 * @param  object $query 
	 * @return object
	 */
	public function sort_ticket_columns($query){

		if($query->is_main_query() && $query->is_post_type_archive( 'ticket' )){

			// todo: overdue tickets of x days should appear at the top of the list as they should have a higher priority
			// order by meta_key _priority by default or if orderby priority has been chosen
			if((isset($query->query['orderby']) && $query->query['orderby'] == 'priority') || !isset($query->query['orderby'])){
				$query->set('orderby', 'meta_value_num');
				$query->set('meta_key', '_priority');
				$query->set('meta_value_num', '_priority');
			}

			$priority = get_query_var('ticket-priority' );
			if($priority){

				// todo: stop reuse of meta_query merge
				$old_meta_query = $query->get('meta_query');
				if(!is_array($old_meta_query)){
					$old_meta_query = array();
				}

				$query->set('meta_query', array_merge(array(
					array(
						'key' => '_priority',
						'value' => intval($priority),
						'type' => 'NUMERIC',
						'compare' => '='
					)
				), $old_meta_query));
			}

			$author = get_query_var( 'ticket-author' );
			if($author){

				// check to see if author has 
				$author_query = new WP_User_Query(array(
					'search' => $author,
					'search_columns' => array('user_email')
				));

				// todo: stop reuse of meta_query merge
				$old_meta_query = $query->get('meta_query');
				if(!is_array($old_meta_query)){
					$old_meta_query = array();
				}

				if( !empty($author_query->results) ){

					$query->set('meta_query',  array_merge(array(
						array(
							'key' => '_ticket_author',
							'value' => $author,
						)
					), $old_meta_query));

				}else{
					
					$query->set('meta_query',  array_merge(array(
						array(
							'key' => '_user_email',
							'value' => $author,
						)
					), $old_meta_query));
				}
			}
		}

		return $query;
	}

	public function wt_restrict_manage_tickets(){

		global $wptickets;

		$screen = get_current_screen();

		// if ticket archive screen
		if($screen->id == 'edit-ticket'){
			?>

			<?php 
			$priorities = wt_list_ticket_priorities();
			$departments = wt_list_ticket_departments();
			$emails = $wptickets->tickets->get_ticket_user_list();
			?>
			<select name="department" id="ticket-departments"><option value="">All Departments</option><?php
			foreach($departments as $dept){
				echo "<option value='".$dept->slug."' " . selected( get_query_var('department' ) , $dept->slug ).">".$dept->name."</option>";
			}
			?></select>

			<select name="ticket-author" id="ticket-authors"><option value="">All Emails</option><?php 
			foreach($emails as $email => $name){
				echo "<option value='".$email."' " . selected( get_query_var('ticket-author' ) , $email ).">" . $email . "</option>";
			} 
			?></select>
			
			<select name="ticket-priority" id="ticket-priorities"><option value="">All Priorities</option><?php
			foreach($priorities as $key => $priority){
				echo "<option value='".$key."' " . selected( get_query_var('ticket-priority' ) , $key ).">".$priority."</option>";
			} 
			?></select>
			<?php
		}
	}
}

new WT_Admin_TicketArchive();
