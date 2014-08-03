<?php

require 'class-wt-admin-commentform.php';

add_action( 'add_meta_boxes', 'wt_meta_boxes' );
function wt_meta_boxes(){

	// publish meta box
	add_meta_box( 'wpticket-ticket-actions', __( 'Ticket Actions', 'wp-tickets' ), 'wt_ticket_actions_meta_box', 'ticket', 'side', 'high');

	// ticket info meta box
	add_meta_box( 'wpticket-ticket-info', __( 'Ticket Info', 'wp-tickets' ), 'wt_ticket_info_meta_box', 'ticket', 'normal', 'high');

	// ticket internal notes meta box
	// add_meta_box( 'wpticket-ticket-internal-comments', __( 'Internal Comments', 'wp-tickets' ), 'wt_ticket_internal_comment_meta_box', 'ticket', 'normal', 'high');

	// ticket comment meta box
	add_meta_box( 'wpticket-ticket-comments', __( 'Comments', 'wp-tickets' ), 'wt_ticket_comment_meta_box', 'ticket', 'normal', 'high');
}

/**
 * Modify publish box
 * 
 * @return void
 */
function wt_ticket_actions_meta_box(){
	global $post;

	// todo: display real information here
	?>
	<style type="text/css">
	#submitdiv, .add-new-h2{
		display: none;
	}
	#wpticket-ticket-actions .inside{
		margin:0;
		padding:0;
	}
	</style>

	<div class="misc-pub-section misc-pub-visibility" id="Status">
		Department: <strong><?php echo wt_get_ticket_department(); ?></strong>
	</div>
	<div class="misc-pub-section misc-pub-visibility" id="Status">
		Ticket Status: <strong><?php echo wt_get_ticket_status(); ?></strong>
	</div>
	<div class="misc-pub-section misc-pub-visibility" id="Status">
		Priority: <strong><?php echo wt_get_ticket_priority(); ?></strong>
	</div>

	<div id="major-publishing-actions">
		<div id="delete-action">
			<a class="submitdelete deletion" href="<?php echo esc_url( get_delete_post_link( $post->ID ) ); ?>">Move to Trash</a>
		</div>

		<div id="publishing-action">
			<span class="spinner"></span>
			<input name="original_publish" type="hidden" id="original_publish" value="Publish">
			<input type="submit" name="publish" id="publish" class="button button-primary" value="Save Ticket" accesskey="p">
		</div>
		<div class="clear"></div>
	</div>
	<?php
}

/**
 * Display info about the ticket
 * @return void
 */
function wt_ticket_info_meta_box(){
	global $post;
	setup_postdata($post);

	// todo: gather and display ticket information
	?>
	<p><strong>Subject:</strong> <?php the_title(); ?></p>
	<p><strong>Author:</strong> James Collings</p>
	<p><strong>Submitted:</strong> <?php the_date(); ?></p>
	<p><strong>Priority:</strong> <?php echo wt_get_ticket_priority(); ?></p>
	<p><strong>Status:</strong> <?php echo wt_get_ticket_status(); ?></p>
	<p><strong>Department:</strong> <?php echo wt_get_ticket_department(); ?></p>
	<hr/>
	<p><strong>Message:</strong> </p>
	<?php
	
	the_content();
}

/**
 * Display ticket comments
 * @return void
 */
function wt_ticket_comment_meta_box(){

	/**
	 * Hooked:
	 *
	 * show_admin_ticket_comments 10
	 * show_admin_ticket_commentform 20
	 */
	do_action( 'wt_admin_comment_box' );	
}

/**
 * Display ticket internal notes
 * @return void
 */
function wt_ticket_internal_comment_meta_box(){
	global $post;

	// todo: gather and display ticket internal comments
}
