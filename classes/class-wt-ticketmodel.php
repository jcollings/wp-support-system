<?php
class WT_TicketModel{

	public $ID = null;

	public function get_tickets($args = array()){

		// todo: sort out get ticket arguments
		$query = array(
			'post_type' => 'ticket',
			'p' => intval($this->ID)
		);

		// todo: setup access permissions
		if(is_user_logged_in()){

		}else{
			$query['meta_query'] = array(
				array(
					'key' => '_ticket_access',
					'value' => 'public'
				)
			);
		}

		if(isset($args['paged']) && intval($args['paged']) > 0){
			$query['paged'] = intval($args['paged']);
		}

		if(isset($args['posts_per_page'])){
			$query['posts_per_page'] = $args['posts_per_page'];
		}

		if(isset($args['user_id']) && intval($args['user_id']) > 0){
			$query['author'] = intval($args['user_id']);
		}

		if(isset($args['status'])){

			$query['tax_query'] = array();
			$query['tax_query'][] = array('taxonomy' => 'status', 'field' => 'slug', 'terms' => $args['status']);
		}

		$tickets = new WP_Query($query);
		return $tickets;
	}

	public function get_ticket($ticket_id, $args = array()){

		if($ticket_id){
			$this->ID = $ticket_id;
		}

		if(!$this->ID){
			return false;
		}

		$ticket = new WP_Query(array(
			'post_type' => 'ticket',
			'p' => intval($this->ID)
		));

		return $ticket;
	}

	public function get_comments($ticket_id = null, $args = array()){

		// todo: get internal comments, comments without internal comments, public or private, or all comments
		if($ticket_id){
			$this->ID = $ticket_id;
		}

		if(!$this->ID){
			return false;
		}

		$comment_args = array(
			'post_id' => $this->ID,
			'order' => 'ASC',
		);

		$type = isset($args['type']) ? $args['type'] : 'public';

		// order
		if(isset($args['order'])){
			$comment_args['order'] = $args['order'];
		}

		// if(isset($args['limit'])){
		// 	$comment_args['limit'] = $args['limit'];
		// }

		switch($type){
			case 'admin':
				// admin [internal, private, public]
				$comment_args['meta_query'] = array(
					'relation' => 'OR',
					array('key' => '_comment_access', 'value' => 'internal'),
					array('key' => '_comment_access', 'value' => 'public'),
					array('key' => '_comment_access', 'value' => 'private')
				);
			break;
			case 'author':
				// author [private, public]
				$comment_args['meta_query'] = array(
					'relation' => 'OR',
					array('key' => '_comment_access', 'value' => 'public'),
					array('key' => '_comment_access', 'value' => 'private')
				);
			break;
			case 'internal':
				// private  [private]
				$comment_args['meta_query'] = array(
					array('key' => '_comment_access', 'value' => 'internal')
				);
			break;
			case 'private':
				// private  [private]
				$comment_args['meta_query'] = array(
					array('key' => '_comment_access', 'value' => 'private')
				);
			break;
			case 'public':
			default:
				// public  [public]
				$comment_args['meta_query'] = array(
					array('key' => '_comment_access', 'value' => 'public')
				);
			break;
		}

		// enable support comments to be displayed
		do_action('wt_before_get_comments');
		
		$result = get_comments($comment_args);

		// disable support comments from other comment queries
		do_action('wt_after_get_comments');

		return $result;
	}

	public function insert_ticket($title, $message, $user_id = 0, $args = array()){

		global $wptickets;

		$config = get_option('support_system_config');
		
		// set ticket department
		$department = isset($args['department']) ? $args['department'] : intval($config['default_group']);
		$department = apply_filters( 'wt/set_ticket_department', $department );

		// set ticket status
		$ticket_status = isset($args['status']) ? $args['status'] : intval($config['ticket_open_status']);
		$ticket_status = apply_filters( 'wt/set_ticket_status', $ticket_status );

		// set ticket access
		$ticket_access = isset($args['access']) ? $args['access'] : 'private';
		$ticket_access = apply_filters( 'wt/set_ticket_access', $ticket_access );

		$post = array(
			'post_type' => 'ticket',
			'post_title' => $title,
			'post_content' => $message,
			'post_status' => 'publish',
			'post_author' => $user_id,
		);

		$ticket_id = wp_insert_post($post);

		if($ticket_id > 0){

			add_post_meta( $ticket_id, '_ticket_access', $ticket_access);

			// save author as _ticket_author to stop it being replaced
			add_post_meta( $ticket_id, '_ticket_author', $user_id);

			// save author ip
			add_post_meta( $ticket_id, '_ticket_author_ip', $this->get_user_ip());

			if($user_id == 0){
				$key = substr(md5(time()), 0, 10);
				add_post_meta( $ticket_id, '_view_key', $key);
				add_post_meta( $ticket_id, '_user_email', $args['user_email']);
				add_post_meta( $ticket_id, '_user_name', $args['user_name']);

				$wptickets->session->set_access_code($args['user_email'], $key);
			}
			
			if($department)
				wp_set_object_terms( $ticket_id, $department, 'department');

			if($ticket_status)
				wp_set_object_terms( $ticket_id, $ticket_status, 'status');

			do_action('wt/after_ticket_create', $ticket_id);
			return $ticket_id;
		}

		return false;
	}

	public function insert_comment($ticket_id = null, $content = null, $author_id = 0, $args = array()){

		if(empty($ticket_id) || empty($content))
			return false;

		$commentdata = array(
			'comment_post_ID' => $ticket_id,
			'comment_content' => $content,
			'comment_author_IP' => $this->get_user_ip()
		);

		

		if(!$author_id){
			$commentdata['comment_author_email'] = get_post_meta( $ticket_id, '_user_email', true );
			$commentdata['comment_author'] = get_post_meta( $ticket_id, '_user_name', true );
		}else{
			$commentdata['user_id'] = $author_id;
			$commentdata['comment_author_email'] = get_the_author_meta( 'user_email', $author_id );
			$commentdata['comment_author'] = get_the_author_meta( 'user_nicename', $author_id );
		}

		if($comment_id = wp_insert_comment($commentdata)){

			if(isset($args['access'])){
				$access = $args['access'];
			}else{
				$access = 'public';
			}
			add_comment_meta( $comment_id, '_comment_access', $access);

			do_action('wt/after_comment_create', $ticket_id, $comment_id);
			return true;
		}

		return false;
	}

	private function get_user_ip(){

		$user_ip = '';

		// get commenters IP
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			
			//check ip from share internet
			$user_ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			
			//to check ip is pass from proxy
			$user_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			
			$user_ip = $_SERVER['REMOTE_ADDR'];
		}

		return $user_ip;
	}

	public function insert_internal_note($ticket_id = null, $content = null, $author_id = 0){

		if(empty($ticket_id) || empty($content))
			return false;

		$commentdata = array(
			'comment_post_ID' => $ticket_id,
			'comment_content' => $content
		);

		if(!$author_id){
			$commentdata['comment_author_email'] = get_post_meta( $ticket_id, '_user_email', true );
			$commentdata['comment_author'] = get_post_meta( $ticket_id, '_user_name', true );
		}else{
			$commentdata['user_id'] = $author_id;
		}

		if($comment_id = wp_insert_comment($commentdata)){

			add_comment_meta( $comment_id, '_comment_access', 'internal');
			return true;
		}

		return false;
	}

	public function allowed_access($ticket_id, $email = '', $key = ''){

		$query = array(
			'post_type' => 'ticket',
			'p' => intval($ticket_id),
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => '_user_email',
					'value' => $email
				),
				array(
					'key' => '_view_key',
					'value' => $key
				),
			)
		);

		$ticket = new WP_Query($query);
		if($ticket->have_posts() && $ticket->post_count == 1)
			return true;

		return false;
	}

	public function get_public_ticket($email, $key){

		$query = array(
			'post_type' => 'ticket',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => '_user_email',
					'value' => $email
				),
				array(
					'key' => '_view_key',
					'value' => $key
				),
			)
		);

		$ticket = new WP_Query($query);
		if($ticket->have_posts() && $ticket->post_count == 1)
			return $ticket;

		return false;
	}

	/**
	 * Get a list of all user name and emails
	 * 
	 * @return array
	 */
	public function get_ticket_user_list(){
		
		$tickets = new WP_Query(array(
			// 'author' => 0,
			'posts_per_page' => -1,
			'post_type' => 'ticket'
		));

		$data = array();

		while($tickets->have_posts()){
			$tickets->the_post();

			$ticket_id = get_the_id();
			
			$email = wt_get_ticket_author_meta($ticket_id, 'email');
			$name = wt_get_ticket_author_meta($ticket_id, 'name');

			if(!empty($email)){
				$data[$email] = $name;
			}
		}

		wp_reset_postdata();

		return $data;
	}

	/**
	 * Mark ticket as closed
	 * 
	 * @param  integer $ticket_id 
	 * @return boolean
	 */
	public function close_ticket($ticket_id = 0){

		$config = get_option('support_system_config');	// load config

		if($ticket_id == 0)
			return false;

		$result = wp_set_object_terms( $ticket_id, intval($config['ticket_close_status']), 'status');
		if(is_wp_error($result)){
			return false;
		}

		do_action('wt/after_ticket_close', $ticket_id);
		return true;
	}

	
	/**
	 * check to see if user/current user has read the ticket
	 * 
	 * @param  int  $ticket_id 
	 * @param  int $user_id   
	 * @return boolean
	 */
	public function is_ticket_read($ticket_id, $user_id = 0){

		if($user_id == 0){
			$user_id = get_current_user_id();
		}

		$read = get_post_meta( $ticket_id, '_ticket_read_'.$user_id, true );
		
		if($read !== '' && $read !== 1){
			print_r($read);
			return false;
		}

		return true;
	}

	/**
	 * Count unread messages
	 * 
	 * @return int
	 */
	public function count_unread_messages($user = 0){

		if($user_id == 0){
			$user_id = get_current_user_id();
		}

		$query = array(
			'fields' => 'ids',
			'post_type' => 'ticket',
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => '_ticket_read_'. $user_id,
					'value' => 1,
					'compare' => '!=',
					'type' => 'NUMERIC'
				)
			)
		);

		$tickets = new WP_Query($query);
		if($tickets->have_posts()){
			return $tickets->post_count;
		}
		return 0;
	}
}