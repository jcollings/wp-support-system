<?php 
/**
 * Notification System
 * 
 * Supprt system notification class, warns the relevent users of events.
 * 
 * @author James Collings <james@jclabs.co.uk>
 * @package Support System
 * @since 0.0.2
 */
class TicketNotification{

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
	 * Notify all of new ticket
	 * 
	 * @param  int $ticket_id 
	 * @return void
	 */
	static function new_ticket_alert($ticket_id){
		$emails = array();

		$post_terms = wp_get_post_terms( $ticket_id, 'support_groups' );
		foreach($post_terms as $term){
			$option = get_option( 'support_groups_'. $term->term_id );
			foreach($option['notification_emails'] as $email){
				if(is_email($email)){
					$emails[] = $email;
				}
			}
		}

		// send Admin emails
		$subject = parse_support_tags(self::$config->notifications['admin']['msg_title'], $ticket_id);
		$message = parse_support_tags(self::$config->notifications['admin']['msg_body'], $ticket_id);
		wp_mail( $emails, $subject, $message);

		// send user emails
		$email = TicketModel::get_ticket_email($ticket_id);
		$subject = parse_support_tags(self::$config->notifications['user']['msg_title'], $ticket_id);
		$message = parse_support_tags(self::$config->notifications['user']['msg_body'], $ticket_id);

		$ticket = get_post( $ticket_id);
		if($ticket->post_author == 0){
			$message .= "\n To view this ticket <a href='".site_url( '?page_id='.$_GET['page_id'].'&support-action=view&id='.$ticket->ID, $scheme = null )."'>click here</a>, and enter the password: ".$ticket->post_password;
		}

		
		wp_mail( $email, $subject, $message);	
	}

	/**
	 * Notify users of new comment
	 * 
	 * @param  int $ticket_id  
	 * @param  int $comment_id 
	 * @return void
	 */
	static function new_comment_alert($ticket_id, $comment_id){

		// send user emails
		$email = TicketModel::get_ticket_email($ticket_id);
		
		// comment
		$comment = get_post($comment_id);
		$message = $comment->post_content;

		// ticket
		$ticket = get_post($comment_id);
		$subject = $ticket->post_title;
		
		$headers = 'From: Support System <'.$ticket_id.'@'.self::$config->email_domain.'>' . "\r\n";
		wp_mail( $email, 'Re:'.$subject, 'Response: '.$message, $headers);
	}
}
?>