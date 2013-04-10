<?php 
/**
 * Ticket Model
 * 
 * Core class to interact with the database
 * 
 * @author James Collings <james@jclabs.co.uk>
 * @package Support System
 * @since 0.0.2
 */
class TicketModel{

	static $config;

	/**
	 * Setup the config
	 * 
	 * Called to setup the global config
	 * 
	 * @param  Class &$config 
	 * @return void
	 */
	static function init(&$config){
		self::$config = $config;
	}


	/**
	 * Get Ticket
	 * 
	 * Get support ticket by id
	 * 
	 * @param  integer $ticket_id 
	 * @return WP_Query
	 */
	static function get_ticket($ticket_id = 0){
		$ticket = new WP_Query(array(
			'post_type' => 'supportmessage',
			'p' => $ticket_id
		));

		return $ticket;
	}

	/**
	 * Get Tickets
	 * 
	 * Get a list of support tickets, based on the passed arguments
	 * 
	 * @param  array  $args 
	 * @return WP_Query
	 */
	static function get_tickets($args = array()){
		$open = isset($args['open']) ? $args['open'] : 0;
		$today = isset($args['today']) ? $args['today'] : false;
		$group = isset($args['group']) ? $args['group'] : false;

		if($open != 0 && $open != 1)
			$open = 0;

		$args = array(
			'post_type' => 'supportmessage',
			'meta_query' => array(
				array(
					'key' => '_answered',
					'value' => $open,
					'compare' => '=',
					'type' => 'INT'
				)
			),
			'order'		=> 'DESC',
			'orderby'	=> 'meta_value_num',
			'meta_key' 	=> '_importance',
			'nopaging' => true
		);

		if($group){
			$args['tax_query'] = array(
				array('taxonomy' => 'support_groups',
				'field' => 'slug',
				'terms' => $group)
			);
		}

		if($today == true){
			$today = getdate();
			$args['year'] = $today['year'];
			$args['monthnum'] = $today['mon'];
			$args['day'] = $today['mday'];
		}

		$tickets = new WP_Query($args);	
		return $tickets;
	}

	/**
	 * Check if registered member
	 * 
	 * @param  string $email 
	 * @return boolean
	 */
	static function is_registered_member($email = ''){

		if(!is_email($email))
			return false;

		if(get_user_by('email', $email))
			return true;

		return false;
	}

	/**
	 * Count Group Tickets
	 * 
	 * Count open tickets for the specified taxonomy
	 * 
	 * @param  string $taxonomy 
	 * @return int
	 */
	static function count_group_tickets($taxonomy = '', $answered = 0){
		$args = array(
			'post_type' => 'supportmessage',
			'meta_query' => array(
				array(
					'key' => '_answered',
					'value' => $answered,
					'compare' => '=',
					'type' => 'INT'
				)
			),
			'nopaging' => true
		);

		if(!empty($taxonomy)){
			$args['tax_query'] = array(
				array('taxonomy' => 'support_groups',
				'field' => 'slug',
				'terms' => $taxonomy)
			);
		}
		$query = new WP_Query($args);
		return $query->post_count;
	}

	/**
	 * Insert Ticket
	 * 
	 * Add a new support ticket
	 * 
	 * @param  string  $title   
	 * @param  string  $message 
	 * @param  integer $user_id 
	 * @param  array   $args    
	 * @return boolean
	 */
	static function insert_ticket($title = '', $message = '', $user_id = 0, $args = array()){

		$importance = isset($args['importance']) ? $args['importance'] : 0;
		$password = '';

		if($user_id == 0)
			$password = wp_generate_password();

		$post = array(
			'post_type' => 'supportmessage',
			'post_title' => $title,
			'post_content' => $message,
			'post_status' => 'publish',
			'post_author' => $user_id,
			'post_password' => $password
		);

		$result = wp_insert_post($post);

		// add to taxonomy manually
		if(intval($args['group']) > 0){
			wp_set_post_terms( $result, $args['group'], 'support_groups');
		}else{
			if(self::$config->default_support_group > 0)
				wp_set_post_terms( $result, self::$config->default_support_group, 'support_groups');
		}

		if($result > 0){
			add_post_meta($result, '_read', 0);			// set flag to not read
			add_post_meta($result, '_answered', 0);		// set flag to not answered
			add_post_meta($result, '_importance', $importance);	// set importance of message
			

			if($user_id == 0){
				add_post_meta( $result, '_name', $args['user_name']);	// set public name
				add_post_meta( $result, '_email', $args['user_email']);	// set public email
			}

			TicketNotification::new_ticket_alert($result);
			return $result;
		}else{
			return false;
		}

	}

	/**
	 * Insert Comment
	 * 
	 * Add new response or note to a support ticket
	 * 
	 * @param  int $ticket_id 
	 * @param  string $message   
	 * @param  int $author_id 
	 * @param  string $type      
	 * @return boolean
	 */
	static function insert_comment($ticket_id, $message, $author_id = 0, $type = 'response'){
		$time = current_time('mysql');

		$args = array(
			'post_parent' => $ticket_id,
			'post_content' => $message,
			'post_type' => 'st_comment',
			'post_date' => $time,
			'post_author' => $author_id,
			'post_status' => 'publish'
		);

		if($type == 'internal'){
			$args['post_type'] = 'st_comment_internal';
		}

		$result = wp_insert_post($args);
		if($author_id == 0){
			add_post_meta( $result, '_name', get_post_meta( $ticket_id, '_name', true ));	// set public name
			add_post_meta( $result, '_email', get_post_meta( $ticket_id, '_email', true ));	// set public email
		}
		return $result;
	}

	/**
	 * Close Support Ticket
	 * 
	 * Mark the ticket as answered
	 * 
	 * @param  boolean $id 
	 * @return boolean
	 */
	static function close_support_ticket($id = false){

		if(!$id)
			return false;
		return update_post_meta($id, '_answered', 1);

	}

	/**
	 * Get Ticket Status
	 * 
	 * get the status of the current ticket 
	 * 
	 * @param  int $ticket_id 
	 * @return string
	 */
	static function get_ticket_status($ticket_id){
		$ticket = self::get_ticket($ticket_id);
		$ticket = $ticket->post;
		$response = self::get_latest_comment($ticket_id);

		if(get_post_meta( $ticket_id, '_answered', true) == 1){
			return 'Ticket Closed';
		}

		if(!$response)
			return 'Awaiting Response';

		if($ticket->post_author == $response->post_author){
			// same so must be waiting on action
			return 'Awaiting Response';
		}else{
			return 'Response Sent';
		}

	}

	/**
	 * Get Ticket Comments
	 * 
	 * Get a list of selected ticket comments
	 * 
	 * @param  int $ticket_id
	 * @param  string $type      
	 * @return WP_Query
	 */
	static function get_ticket_comments($ticket_id, $type = 'st_comment'){
		$query = new WP_Query(array(
			'post_type' => $type,
			'post_parent' => $ticket_id,
			'order' => 'ASC',
			'nopaging' => true,
		));
		return $query;
	}

	
	/**
	 * Get Latest Comment
	 * 
	 * @param  integer $ticket_id 
	 * @return array
	 */
	static function get_latest_comment($ticket_id = 0){
		$query = new WP_Query(array(
			'post_type' => 'st_comment',
			'post_parent' => $ticket_id,
			'orderby' => 'date',
			'posts_per_page' => 1

		));

		if($query->post_count == 0)
			return false;

		return $query->post;
	}

	/**
	 * Get Tickets Priority
	 * 
	 * @param  int $ticket_id 
	 * @return int
	 */
	static function get_ticket_priority($ticket_id){
		return get_post_meta($ticket_id, '_importance', true);
	}

	/**
	 * Get Ticket Author
	 * 
	 * Return ticket author name
	 * 
	 * @return string
	 */
	static function get_ticket_author(){
		global $post;
		$ticket_id = get_the_ID();
		$author_id = get_the_author_meta( 'ID' );
		if($author_id > 0){
			$name = get_the_author();
		}else{
			$name = get_post_meta( $ticket_id, '_name', true);
		}
		return $name;
	}

	/**
	 * Get Ticket Email
	 * 
	 * Return ticket author email
	 * 
	 * @param  boolean $ticket_id
	 * @return string
	 */
	static function get_ticket_email($ticket_id = false){
		global $post;

		if($ticket_id == false && !is_null($post) && $post->ID == $ticket_id ){
			$ticket_id = get_the_ID();
			$author_id = get_the_author_meta( 'ID' );
		}else{
			$ticket = get_post( $ticket_id );
			$author_id = $ticket->post_author;
		}

		if($author_id > 0){
			$email = get_the_author_meta( 'email' , $author_id);
		}else{
			$email = get_post_meta( $ticket_id, '_email', true);
		}
		
		return $email;
	}
}

?>