<?php
class WT_Shortcode_SupportSystem{

	public function __construct(){

		add_shortcode( 'wptickets_system', array($this, 'output') );

		// public system hooks
		add_action( 'wt/system_public_page', array($this, 'public_open_ticket_module'), 10);
		add_action( 'wt/system_public_page', array($this, 'public_view_ticket_module'), 20);

		// member system hooks
		add_action( 'wt/system_member_page', array($this, 'member_register_module'), 10);
		add_action( 'wt/system_member_page', array($this, 'member_login_module'), 20);

		//  account system hooks
		add_action( 'wt/system_account_page', array($this, 'account_add_ticket_module'), 20);
		add_action('wt/system_account_page', array($this, 'account_ticket_archive_module'), 10);
	}

	public function output($atts){

		extract( shortcode_atts( array(
			'cols' => 2
		), $atts ) );

		global $wptickets;

		if($cols == 2){
			echo '<div class="wptickets_cols_2">';
		}

		if(is_user_logged_in()){

			// Member Tickets (member):
			// Modules: [Account Tickets, Create Ticket]
			wt_get_template_part( 'system/home-account' );	

		}elseif(!is_user_logged_in() && $wptickets->allow_public){

			// Public Tickets
			// Modules: [create ticket, view ticket]
			wt_get_template_part( 'system/home-public' );

		}elseif(!is_user_logged_in() && !$wptickets->allow_public){
			
			// Member Tickets (non-member):
			// Modules: [Register Account, Login]
			wt_get_template_part( 'system/home-member' );

		}

		if($cols == 2){
			echo '</div>';
		}
	}

	public function public_open_ticket_module(){
		
		wt_get_template_part( 'system/public/module-open-ticket' );
	}

	public function public_view_ticket_module(){
		
		wt_get_template_part( 'system/public/module-view-ticket' );
	}

	public function member_register_module(){

		wt_get_template_part( 'system/member/module-register' );
	}

	public function member_login_module(){
		
		wt_get_template_part( 'system/member/module-login' );
	}

	public function account_add_ticket_module(){
		
		wt_get_template_part( 'system/account/module-add-ticket' );
	}

	public function account_ticket_archive_module(){
		
		wt_get_template_part( 'system/account/module-ticket-archive' );		
	}
}
new WT_Shortcode_SupportSystem();