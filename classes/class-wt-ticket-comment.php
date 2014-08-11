<?php
class WT_TicketComment{

	public function __construct(){
		
		add_action('pre_get_comments', array($this, 'wt_pre_get_comments'));
		add_action('wp_insert_comment', array($this, 'wt_insert_comment'), 10,2);
		add_action('wt_after_ticket_comments', array($this, 'wt_show_ticket_commentform'));
		add_action('wt_ticket_comments', array($this, 'wt_show_ticket_comments'));
		add_action('wt/after_comment_create', array( $this, 'set_ticket_status'), 10, 2);

		add_action('wt_before_get_comments', array($this, 'wt_before_get_comments'));
		add_action('wt_after_get_comments', array($this, 'wt_after_get_comments'));
	}

	/**
	 * Disable support comment block
	 * @return void
	 */
	function wt_before_get_comments(){
		remove_action('pre_get_comments', array($this, 'wt_pre_get_comments'));
	}

	/**
	 * Enable support comment block
	 * @return void
	 */
	function wt_after_get_comments(){
		add_action('pre_get_comments', array($this, 'wt_pre_get_comments'));
	}


	/**
	 * Hide support comments from all comment queries
	 * @return  void
	 */
	function wt_pre_get_comments($comments){

		$comments->meta_query->queries[] = array(
			'key' => '_support_comment', 
			'compare' => 'NOT EXISTS'
		);
	}

	/**
	 * Flag comment as support comment
	 * @return  void
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
		global $wptickets, $comment;

		// todo: show private/inernal comments if authorised
		if(wt_is_user_admin()){
			$comments = $wptickets->tickets->get_comments(get_the_ID(), array('type' => 'admin'));
		}elseif(wt_is_user_author()){
			$comments = $wptickets->tickets->get_comments(get_the_ID(), array('type' => 'author'));
		}else{
			$comments = $wptickets->tickets->get_comments(get_the_ID(), array('type' => 'public'));
		}

		
		
		foreach($comments as $comment){
			
			wt_get_template_part( 'single-ticket/comments' );
		}

		wp_reset_postdata();
	}

	/**
	 * Add ticket form
	 */
	function wt_show_ticket_commentform(){

		global $post, $wptickets;

		// hide comment form for non loggedin users who are not authorised
		if(!is_user_logged_in() && !$wptickets->session->check_access_code( $post->ID ))
			return;

		// todo: show comment form if is ticket author or admin
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

		// on internal note dont mark as responded
		if( !in_array( get_comment_meta( $comment_id, '_comment_access',true), array('public', 'private') ) ){
			return;
		}

		if($comment->user_id == 0){
			
			// public author
			wp_set_object_terms( $ticket_id, $config['ticket_reply_status'], 'status');

		}else{

			$ticket = $wptickets->tickets->get_ticket($ticket_id);
			if( $comment->user_id == intval( get_post_meta( $ticket_id, '_ticket_author', true ) ) ){
				
				// author
				wp_set_object_terms( $ticket_id, $config['ticket_reply_status'], 'status');
				
			}else{

				// admin
				wp_set_object_terms( $ticket_id, $config['ticket_responded_status'], 'status');
			}
		}
	}
}
new WT_TicketComment();