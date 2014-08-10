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

	// list of emails and their template paths
	private $_emails = array(
		'admin_new' => 'email/admin/new-ticket',
		'admin_update' => 'email/admin/new-comment',
		'admin_assign' => 'email/admin/assign-ticket',
		'member_new' => 'email/member/new-ticket',
		'member_update' => 'email/member/new-comment',
		'public_new' => 'email/public/new-ticket',
		'public_update' => 'email/public/new-comment'
	);

	public function __construct(){

		add_action('wt/after_ticket_create', array($this, 'after_ticket_create'), 10, 1);
		add_action('wt/after_comment_create', array($this, 'after_comment_create'), 10, 2);

		// notifications
		$notification_config = get_option('notification_override');

		// admin message
		if(isset($notification_config['override_admin']) && $notification_config['override_admin'] == 'yes'){
			// custom message
		}else{
			// template
		}

		// member message
		if(isset($notification_config['override_member']) && $notification_config['override_member'] == 'yes'){
			// custom message
		}else{
			// template
		}

		// public message
		if(isset($notification_config['override_public']) && $notification_config['override_public'] == 'yes'){
			// custom message
		}else{
			// template
		}
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
	 * Load email template
	 * 
	 * @param string $template template location
	 * @param array  $vars
	 */
	public function get_email_content($template, $vars = array()){

		$template = isset($this->_emails[$template]) ? $this->_emails[$template] : false;

		if(!$template)
			return false;

		// todo: check to see if override and load message from database instead

		// fetch email template
		ob_start();
		wt_get_template_part( $template );
		$email_content = ob_get_contents();
		ob_clean();

		// process template vars
		return $this->parse_message_vars($email_content, $vars);
	}

	/**
	 * Set template variables {{var}} in email message
	 * 
	 * @param  string $message
	 * @param  array $vars
	 * @return string
	 */
	private function parse_message_vars($message, $vars = array()){

		if(is_array($vars) && !empty($vars)){

			foreach($vars as $key => $value){
				$message = preg_replace('/\{\{'.$key.'\}\}/', $value, $message);
			}
		}

		return $message;
	}

	/**
	 * Load template to insert email content into
	 * 
	 * @param string $content 
	 * @param string $subject
	 */
	private function set_template_content($content, $subject = ''){

		// load template from file
		ob_start();
		wt_get_template_part( 'email/template' );
		$email_content = ob_get_contents();
		ob_clean();

		// enter content and title into template
		$pattern = array('/\{\{TEMPLATE_TITLE\}\}/', '/\{\{TEMPLATE_CONTENT\}\}/');
		$replace = array($subject, $content);
		$email_content = preg_replace($pattern, $replace, $email_content);

		return $email_content;
	}

	/**
	 * Set email to html format
	 */
	public function set_html_content_type() {
		return 'text/html';
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

		// add html email header
		add_filter( 'wp_mail_content_type', array($this, 'set_html_content_type') );

		// send admin emails
		$email_subject = 'new ticket admin';
		$email_message = $this->get_email_content( 'admin_new', array('test' => 'Admin'));
		$template = $this->set_template_content($email_message, $email_subject);
		wp_mail( $admin_emails, $email_subject, $template);

		if(is_member_ticket($ticket_id)){

			// send member email
			$email_subject = 'new ticket public';
			$email_message = $this->get_email_content( 'member_new', array('test' => 'Member'));
			$template = $this->set_template_content($email_message, $email_subject);
			wp_mail( $author_email, $email_subject, $template);
		}else{

			$email_vars = array(
				'pass' => get_post_meta( $ticket_id, '_view_key', true ),
				'name' => get_post_meta( $ticket_id, '_user_name', true ),
			);

			// send public email
			$email_subject = 'new ticket public';
			$email_message = $this->get_email_content( 'public_new', $email_vars);
			$template = $this->set_template_content($email_message, $email_subject);
			wp_mail( $author_email, $email_subject, $template);
		}

		// remove html email header filter
		remove_filter( 'wp_mail_content_type', array($this, 'set_html_content_type') );
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

		// add html email header
		add_filter( 'wp_mail_content_type', array($this, 'set_html_content_type') );
	
		// current comment author
		$comment_email = get_comment($comment_id)->comment_author_email;
		if(in_array($comment_email, $admin_emails)){

			// author posted
			if(is_member_ticket($ticket_id)){

				// send member email
				$email_subject = 'new comment member';
				$email_message = $this->get_email_content( 'member_update', array('test' => 'Member'));
				$template = $this->set_template_content($email_message, $email_subject);
				wp_mail( $author_email, $email_subject, $template);
			}else{

				$email_vars = array(
					'pass' => get_post_meta( $ticket_id, '_view_key', true ),
					'name' => get_post_meta( $ticket_id, '_user_name', true ),
				);

				// send public email
				$email_subject = 'new comment public';
				$email_message = $this->get_email_content( 'public_update', $email_vars);
				$template = $this->set_template_content($email_message, $email_subject);
				wp_mail( $author_email, $email_subject, $template);
			}
		}else{

			// non author, send admin emails
			$email_subject = 'new comment admin';
			$email_message = $this->get_email_content( 'admin_update', array('test' => 'Admin'));
			$template = $this->set_template_content($email_message, $email_subject);
			wp_mail( $admin_emails, $email_subject, $template);
		}

		// remove html email header filter
		remove_filter( 'wp_mail_content_type', array($this, 'set_html_content_type') );
	}

}

new WT_EmailNotification();