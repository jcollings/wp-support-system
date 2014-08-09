<?php
/**
 * Email Notification Class
 *
 * Handle all sent emails from the support system
 * 
 * @since  0.0.1
 * @author James Collings <james@jclabs.co.uk>
 */
class WT_EmailNotification{

	public function __construct(){

		add_action('wt/after_ticket_create', array($this, 'after_ticket_create'), 10, 1);
		add_action('wt/after_comment_create', array($this, 'after_comment_create'), 10, 2);
	}

	/**
	 * Fetch list of admin emails to be notified
	 * 
	 * @param  int $ticket_id 
	 * @return array/string
	 */
	public function get_ticket_admin_emails($ticket_id){

		$privs = get_option('admin_priv');	// get list roles with ticket capabilities
		$user_roles = array('administrator');
		$users = array();
		$emails = array();

		if(isset($privs['admin_group']) && is_array($privs['admin_group'])){
			$user_roles = array_unique(array_merge($user_roles, $privs['admin_group']));
		}
		
		// get list of user ids for each role
		foreach($user_roles as $role){
			$users_query = new WP_User_Query(array(
				'role' => $role,
				'fields' => 'ID'
		    ));
		    $results = $users_query->get_results();
	        if ($results) $users = array_merge($users, $results);
		}

		// get each user email
		foreach($users as $user_id){
			$emails[] = get_the_author_meta('user_email', $user_id);
		}

		return $emails;
	}

	/**
	 * Load email template and set template variables {{var}}
	 * 
	 * @param string $template template location
	 * @param array  $vars
	 */
	public function set_email_template($template, $vars = array()){

		// fetch email template
		ob_start();
		wt_get_template_part( $template );
		$email_content = ob_get_contents();
		ob_clean();

		// process template vars
		if(is_array($vars) && !empty($vars)){

			foreach($vars as $key => $value){
				$email_content = preg_replace('/\{\{'.$key.'\}\}/', $value, $email_content);
			}
		}

		return $email_content;
	}

	/**
	 * Send out emails after a ticket has been created
	 * 
	 * @param  int $ticket_id 
	 * @return void
	 */
	public function after_ticket_create($ticket_id){

		$admin_emails = $this->get_ticket_admin_emails($ticket_id);
		$author_email = wt_get_ticket_author_meta($ticket_id, 'email');

		// send admin emails
		$template = $this->set_email_template( 'email/admin/new-ticket', array('test' => 'Admin'));
		wp_mail( $admin_emails, 'new ticket admin', $template);

		if(is_member_ticket($ticket_id)){

			// send member email
			$template = $this->set_email_template( 'email/member/new-ticket', array('test' => 'Member'));
			wp_mail( $author_email, 'new ticket public', $template);
		}else{

			$email_vars = array(
				'pass' => get_post_meta( $ticket_id, '_view_key', true ),
				'name' => get_post_meta( $ticket_id, '_user_name', true ),
			);

			// send public email
			$template = $this->set_email_template( 'email/public/new-ticket', $email_vars);
			wp_mail( $author_email, 'new ticket public', $template);
		}
	}

	/**
	 * Send out emails after a ticket comment has been created
	 * 
	 * @param  int $ticket_id 
	 * @return void
	 */
	public function after_comment_create($ticket_id, $comment_id){

		$admin_emails = $this->get_ticket_admin_emails($ticket_id);
		$author_email = wt_get_ticket_author_meta($ticket_id, 'email');
	
		// current comment author
		$comment_email = get_comment($comment_id)->comment_author_email;
		if(in_array($comment_email, $admin_emails)){

			// author posted
			if(is_member_ticket($ticket_id)){

				// send member email
				$template = $this->set_email_template( 'email/member/new-comment', array('test' => 'Member'));
				wp_mail( $author_email, 'new comment member', $template);
			}else{

				$email_vars = array(
					'pass' => get_post_meta( $ticket_id, '_view_key', true ),
					'name' => get_post_meta( $ticket_id, '_user_name', true ),
				);

				// send public email
				$template = $this->set_email_template( 'email/public/new-comment', $email_vars);
				wp_mail( $author_email, 'new comment public', $template);
			}
		}else{

			// non author, send admin emails
			$template = $this->set_email_template( 'email/admin/new-comment', array('test' => 'Admin'));
			wp_mail( $admin_emails, 'new comment admin', $template);
		}
	}

}

new WT_EmailNotification();