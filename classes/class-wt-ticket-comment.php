<?php
class WT_TicketComment{

	public function __construct(){
		add_action('pre_get_comments', array($this, 'pre_get_comments'));
		add_action('wp_insert_comment', array($this, 'wt_insert_comment'), 10,2);
		add_action('wt_after_ticket_comments', array($this, 'wt_show_ticket_commentform'));
		add_action('wt_ticket_comments', array($this, 'wt_show_ticket_comments'));
		add_action('wt/after_comment_create', array( $this, 'set_ticket_status'), 10, 2);
	}


	/**
	 * Hide support omments from comments.php
	 */
	function pre_get_comments($comments){

		if(!is_admin() || (is_admin() && defined('DOING_AJAX')))
			return;

		$screen = get_current_screen();
		
		if($screen->id == 'edit-comments' || $screen->id == 'dashboard'){
			
			// add code to hide comments from main comment stream
			$comments->meta_query->queries[] = array(
				'key' => '_support_comment', 
				'compare' => 'NOT EXISTS'
			);
		}
	}

	/**
	 * Flag comment as support comment
	 */
	function wt_insert_comment($comment_id, $comment, $args = array()){
		global $post;

		if(defined('DOING_AJAX') || !$post){
			$q = new WP_Query(array( 'p' => intval($comment->comment_post_ID), 'post_type' => 'ticket'));
			
			if(!$q->have_posts())
				return;
		}

		if((isset($post->post_type) && $post->post_type == 'ticket')
			|| (isset($q->post->post_type) && $q->post->post_type == 'ticket')){

			// hide support comment from main comment list
			if($comment->comment_approved != 1){
				wp_update_comment(array(
					'comment_ID' => $comment_id,
					'comment_approved' => 1
				));
			}

			add_comment_meta( $comment_id, '_support_comment', 1 );
		}
	}

	
	function wt_show_ticket_comments(){
		global $wptickets;
		global $comment;

		$comments = $wptickets->tickets->get_comments(get_the_ID(), array('type' => 'public'));
		
		foreach($comments as $comment){
			
			wt_get_template_part( 'single-ticket/comments' );
		}

		wp_reset_postdata();
	}

	/**
	 * Add ticket form
	 */
	function wt_show_ticket_commentform(){
		wt_get_template_part( 'single-ticket/commentform' );
	}

	/**
	 * Set ticket status once a support comment has been submitted
	 * 
	 * @param int $ticket_id  
	 * @param int $comment_id 
	 */
	function set_ticket_status($ticket_id, $comment_id){

		global $wptickets;

		$config = get_option('support_system_config');	// load config
		$comment = get_comment($comment_id);			// load comment details

		if($comment->user_id == 0){
			
			// public author
			wp_set_object_terms( $ticket_id, intval($config['ticket_reply_status']), 'status');

		}else{

			$ticket = $wptickets->tickets->get_ticket($ticket_id);
			if($comment->user_id == $ticket->post_author){
				
				// author
				wp_set_object_terms( $ticket_id, intval($config['ticket_reply_status']), 'status');
				
			}else{

				// admin
				wp_set_object_terms( $ticket_id, intval($config['ticket_responded_status']), 'status');
			}
		}
	}
}
new WT_TicketComment();