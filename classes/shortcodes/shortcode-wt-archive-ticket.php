<?php
class WT_Shortcode_ArchiveTicket{

	public function __construct(){

		add_shortcode( 'wptickets_ticket_archive', array($this, 'output') );
	}

	public function output($atts){

		global $wptickets, $post, $wp_query, $wpss_counter;
		$wpss_counter = 0;

		extract( shortcode_atts( array(
		), $atts ) );


		// set wp_query to temp query
		$temp_wp_query = $wp_query;

		// load tikets into post data
		$wp_query = $wptickets->tickets->get_tickets(array('paged' => get_query_var('paged'), 'posts_per_page' => 10));

		// Output ticket archive
		if(have_posts()){
			while(have_posts()){
				the_post();
				$wpss_counter++;

				wt_get_template_part( 'content-ticket' );
			}
		}

		wt_pagination();

		// reset to old wp_query
		$wp_query = $temp_wp_query;
		wp_reset_postdata();
	}
}
new WT_Shortcode_ArchiveTicket();