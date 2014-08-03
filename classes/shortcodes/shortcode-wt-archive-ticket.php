<?php
class WT_Shortcode_ArchiveTicket{

	public function __construct(){

		add_shortcode( 'wptickets_ticket_archive', array($this, 'output') );
	}

	public function output($atts){

		global $wptickets, $post, $wp_query;

		extract( shortcode_atts( array(
		), $atts ) );

		// set wp_query to temp query
		$temp_wp_query = $wp_query;

		// load tikets into post data
		$wp_query = $wptickets->tickets->get_tickets();

		// Output ticket archive
		wt_get_template_part( 'archive-ticket' );

		// reset to old wp_query
		$wp_query = $temp_wp_query;
		wp_reset_postdata();
	}
}
new WT_Shortcode_ArchiveTicket();