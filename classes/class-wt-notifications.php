<?php
class WT_Admin_Notifications{

	public function __construct(){

		add_action('wt/after_ticket_create', array($this, 'reset_ticket_notifications'));
		add_action('wt/after_comment_create',array($this, 'reset_ticket_notifications'));
		add_action('wt/ticket_read', array($this, 'on_ticket_read'), 10 , 2);
	}

	/**
	 * Reset ticket notifications on the ticket
	 * 
	 * @param  int $ticket_id 
	 * @return void
	 */
	public function reset_ticket_notifications($ticket_id){

		// clear read
		// todo: get list of users who are in charge of this ticket
		$user_query = new WP_User_Query(array(
			'fields' => 'ID'
		));

		// clear all previous meta _ticket_read_{user_id}

		if ( ! empty( $user_query->results ) ) {
			foreach ( $user_query->results as $user ) {
				$this->update_ticket_notifications($ticket_id, $user, 0);
			}
		}
	}

	/**
	 * Set ticket notification list
	 * 
	 * @param  int $ticket_id 
	 * @param  array  $value     
	 * @return void
	 */
	private function update_ticket_notifications($ticket_id, $user_id = 0, $read = 0){
		
		$old_data = get_post_meta( $ticket_id, '_ticket_read_'.$user_id, true );
		if($old_data !== false){
			update_post_meta( $ticket_id, '_ticket_read_'.$user_id, $read, $old_data );
		}else{
			add_post_meta( $ticket_id, '_ticket_read_'.$user_id, $read, true );
		}	
	}

	/**
	 * On ticket read, mark ticket as read to the current user
	 * 
	 * @param  int $ticket_id 
	 * @param  int $user_id   
	 * @return void
	 */
	public function on_ticket_read($ticket_id, $user_id){

		$value = get_post_meta( $ticket_id, '_ticket_read_'.$user_id, true );
		if(!$value || $value === 0){
			$this->update_ticket_notifications($ticket_id, $user_id, 1);
		}
	}	
}
new WT_Admin_Notifications();