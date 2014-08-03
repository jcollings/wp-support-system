<?php
class WT_TicketAccess{

	public function __construct(){

		add_action('template_redirect', array($this, 'check_ticket_access'));
	}

	public function check_ticket_access(){

		global $post, $wp_query, $wptickets;

		if(is_user_logged_in() || is_user_admin())
			return;

		if(is_singular( 'ticket' )){

			// public with key and email 
			if( ( $wptickets->allow_public && !is_user_logged_in() && $wptickets->session->check_access_code( $post->ID ) ) || wt_get_ticket_access() == 'public' ){
				return;
			}

			// redirect if not allowed
			$wp_query->set_404();
			status_header(404);
		}

		// 
		if(is_post_type_archive( 'ticket' )){

			// load tikets into post data
			// todo: enable paging or move it to pre_get_posts hook
			$wp_query = $wptickets->tickets->get_tickets();
		}
	}
}
new WT_TicketAccess();