<?php
class WT_Admin_CommentForm{

	public function __construct(){

		add_action('wt/process_admin_add_comment', array($this, 'process_form_add_comment'));
		add_action('wt_admin_comment_box', array($this, 'show_ticket_comments'), 10);
		add_action('wt_admin_comment_box', array($this, 'show_ticket_commentform'), 20);
	}

	public function process_form_add_comment(){

		global $wptickets;

		$ticket_id = isset($_POST['ticket_id']) && !empty($_POST['ticket_id']) ? $_POST['ticket_id'] : false ;
		$message = isset($_POST['response']) && !empty($_POST['response']) ? $_POST['response'] : false ;
		$access = isset($_POST['access']) && $_POST['access'] == 'private' ? $_POST['access'] : 'public' ;
		$user_id = 0;

		if(!$message)
			return false;

		// failed to auth public
		if(!is_user_logged_in() && !$wptickets->session->check_access_code($ticket_id))
			return false;

		if(is_user_logged_in()){
			$user_id = get_current_user_id();
		}

		$args = array(
			'access' => $access
		);

		$result = $wptickets->tickets->insert_comment($ticket_id, $message, $user_id, $args);
		if($result){

			wp_redirect( admin_url('/post.php?post='.$ticket_id.'&action=edit' ) );
			exit;
		}
		return false;
	}

	public function show_admin_ticket_comments(){

		global $post, $wptickets;
		setup_postdata($post);
		
		// todo: gather and display ticket comments 
		$comments = $wptickets->tickets->get_comments($post->ID);
		print_r($comments);

	}
	public function show_admin_ticket_commentform(){

		global $wptickets;

		// todo: display form to add comment
		require_once $wptickets->plugin_dir . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'editor' . DIRECTORY_SEPARATOR . 'commentform.php';
	}
}
new WT_Admin_CommentForm();