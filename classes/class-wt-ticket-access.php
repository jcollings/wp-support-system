<?php
class WT_TicketAccess{

	public function __construct(){

		add_action('template_redirect', array($this, 'check_ticket_access'));
	}

	public function check_ticket_access(){

		global $post, $wp_query, $wptickets;

		// ticket archive, modify query
		if(is_post_type_archive( 'ticket' ) || ( !is_singular('ticket') && is_main_query() && get_query_var('post_type') == 'ticket' )){
			$wp_query = $wptickets->tickets->get_tickets(array('paged' => get_query_var('paged'), 'posts_per_page' => 10));
		}

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
	}
}
new WT_TicketAccess();