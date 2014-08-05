<?php
class WT_Admin_CommentForm{

	public function __construct(){

		add_action('wt/process_admin_add_comment', array($this, 'process_form_add_comment'));
		
		add_action('wt_admin_comment_box', array($this, 'show_admin_ticket_message'), 5);
		add_action('wt_admin_comment_box', array($this, 'show_admin_ticket_comments'), 10);
		add_action('wt_admin_comment_box', array($this, 'show_admin_ticket_commentform'), 20);

		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
	}

	public function process_form_add_comment(){

		global $wptickets;

		// todo: make sure it only happens when the add comment button is pressed

		$ticket_id = isset($_POST['ticket_id']) && !empty($_POST['ticket_id']) ? $_POST['ticket_id'] : false ;
		$action = isset($_POST['wptickets-action-button']) && $_POST['wptickets-action-button'] == 'Add Comment' ? $_POST['wptickets-action-button'] : false;
		$message = isset($_POST['response']) && !empty($_POST['response']) ? $_POST['response'] : false ;
		$access = isset($_POST['access']) && in_array($_POST['access'], array('public', 'private', 'internal')) ? $_POST['access'] : 'public' ;
		$close_ticket = isset($_POST['close_ticket']) && $_POST['close_ticket'] == 1 ? 1 : 0;
		$user_id = 0;

		if(!$message || !$action)
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

			//todo close ticket if checkbox is selected
			if($close_ticket){
				$wptickets->tickets->close_ticket($ticket_id);
			}

			wp_redirect( admin_url('/post.php?post='.$ticket_id.'&action=edit' ) );
			exit;
		}
		return false;
	}

	public function show_admin_ticket_comments(){

		global $post, $wptickets, $comment;
		setup_postdata($post);
		
		// todo: gather and display ticket comments 
		$comments = $wptickets->tickets->get_comments($post->ID, array('type' => 'admin'));

		foreach($comments as $comment){
			require $wptickets->plugin_dir . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'editor' . DIRECTORY_SEPARATOR . 'comments.php';
		}
		// print_r($comments);

	}
	public function show_admin_ticket_commentform(){

		global $wptickets;

		// todo: display form to add comment
		require_once $wptickets->plugin_dir . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'editor' . DIRECTORY_SEPARATOR . 'commentform.php';
	}

	public function show_admin_ticket_message(){
		global $post, $wptickets;
		setup_postdata( $post );

		require_once $wptickets->plugin_dir . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'editor' . DIRECTORY_SEPARATOR . 'message.php';
	}

	public function admin_enqueue_scripts(){
		
		global $wptickets;
		wp_enqueue_style('support-admin-css', $wptickets->plugin_url . 'assets/css/admin.css');
	}
}
new WT_Admin_CommentForm();