<?php
class WT_Shortcode_AddTicket{

	public function __construct(){

		add_shortcode( 'wptickets_ticket_form', array($this, 'output') );
	}

	public function output($atts){

		extract( shortcode_atts( array(
		), $atts ) );

		// Display Ticket form
		wt_get_template_part( 'system/form-create-ticket' );

	}
}
new WT_Shortcode_AddTicket();