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

		if(isset($args['user_id']) && intval($args['user_id']) > 0){
			$query['author'] = intval($args['user_id']);
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

		switch($type){
			case 'admin':
				// admin [internal, private, public]
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

		return get_comments($comment_args);
	}

	public function insert_ticket($title, $message, $user_id = 0, $args = array()){

		global $wptickets;
		
		// set ticket department
		$department = isset($args['department']) ? $args['department'] : null;
		$department = apply_filters( 'wt/set_ticket_department', $department );

		// set ticket status
		$ticket_status = isset($args['status']) ? $args['status'] : 'opened';
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
			'comment_content' => $content
		);

		if(!$author_id){
			$commentdata['comment_author_email'] = get_post_meta( $ticket_id, '_user_email', true );
			$commentdata['comment_author'] = get_post_meta( $ticket_id, '_user_name', true );
		}else{
			$commentdata['user_id'] = $author_id;
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
}