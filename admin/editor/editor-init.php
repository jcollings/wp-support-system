<?php

require 'class-wt-admin-commentform.php';
require 'class-wt-admin-actionbox.php';

add_action( 'add_meta_boxes', 'wt_add_meta_boxes' );
function wt_add_meta_boxes(){

	global $post;

	// publish meta box
	add_meta_box( 'wpticket-ticket-actions', __( 'Ticket Actions', 'wp-tickets' ), 'wt_ticket_actions_meta_box', 'ticket', 'side', 'high');

	if('auto-draft' !== $post->post_status){
		// ticket info meta box
		add_meta_box( 'wpticket-ticket-info', __( 'Ticket Info', 'wp-tickets' ), 'wt_ticket_info_meta_box', 'ticket', 'side', 'high');

		// ticket comment meta box
		add_meta_box( 'wpticket-ticket-comments', __( 'Ticket Thread', 'wp-tickets' ), 'wt_ticket_comment_meta_box', 'ticket', 'normal', 'high');
	}else{
		// publish meta box
		add_meta_box( 'wpticket-ticket-create', __( 'Ticket Create', 'wp-tickets' ), 'wt_ticket_create_meta_box', 'ticket', 'normal', 'high');
	}
}

add_action( 'admin_menu' , 'wt_remove_meta_boxes' );
function wt_remove_meta_boxes() {
	
	// remove department and status
	remove_meta_box( 'departmentdiv', 'ticket', 'side' );
	remove_meta_box( 'statusdiv', 'ticket', 'side' );

	remove_meta_box( 'commentsdiv', 'ticket', 'normal' );

	// remove slug
	remove_meta_box('slugdiv', 'ticket', 'normal' );

	remove_meta_box( 'submitdiv', 'ticket', 'side' );
}

/**
 * Modify publish box
 * 
 * @return void
 */
function wt_ticket_actions_meta_box(){
	global $post;

	// on ticket view
	do_action('wt/ticket_read', $post->ID, get_current_user_id());

	?>
	<style type="text/css">
	#wpticket-ticket-actions .inside{
		margin:0;
		padding:0;
	}
	</style>

	<form method="post" action="#">

		<input type="hidden" name="wptickets-action" value="admin_ticket_actions" />
		<input type="hidden" name="ticket_id" value="<?php the_ID(); ?>" />
		
		<?php
		/**
		 * Hooked:
		 *
		 * show_action_box 10
		 */
		do_action( 'wt_admin_action_box' );
		?>

		<div id="major-publishing-actions">

			<?php if(current_user_can( 'delete_tickets') && 'auto-draft' !== $post->post_status): ?>
			<div id="delete-action">
				<a href="<?php echo esc_url( get_delete_post_link($post->ID ) ); ?>" class="submitdelete deletion">Move to Trash</a>
			</div>
			<?php endif; ?>

			<div id="publishing-action">
				<span class="spinner"></span>
				<input type="submit" name="wptickets-action-button" value="<?php echo 'auto-draft' === $post->post_status ? esc_attr__( 'Create', '' ) : esc_attr__( 'Update', '' ); ?>" class="button button-primary" />
			</div>
			<div class="clear"></div>
		</div>
	</form>
	<?php
}

/**
 * Display info about the ticket
 * @return void
 */
function wt_ticket_info_meta_box(){
	global $post;
	setup_postdata($post);
	?>
	<div class="wpss-two-cols">
		<dl class="wpss-pull-left wpss-one-col wpss-dl">
			
			<dt>Subject:</dt>
			<dd><?php the_title(); ?></dd>

			<dt>Department:</dt>
			<dd><?php echo wt_get_ticket_department(); ?></dd>

			<dt>Status:</dt>
			<dd><?php echo wt_get_ticket_status(); ?></dd>

			<?php $priority = wt_get_ticket_priority(); ?>
			<dt>Priority:</dt>
			<dd><span class="wt-priority <?php echo strtolower($priority); ?>"><?php echo wt_get_ticket_priority(); ?></span></dd>

			<dt>Source</dt>
			<dd><?php echo wt_get_ticket_source($post->ID); ?></dd>

			<dt>Created:</dt>
			<dd><?php the_time('F j, Y \a\t g:i a'); ?></dd>

		</dl>

		<dl class="wpss-pull-right wpss-one-col wpss-dl">
			<dt>Author:</dt>
			<dd><?php echo wt_get_ticket_author_meta($post->ID, 'name'); ?></dd>

			<?php $email = wt_get_ticket_author_meta($post->ID, 'email'); ?>
			<dt>Email:</dt>
			<dd><a href="<?php echo admin_url('/edit.php?post_type=ticket&ticket-author='.urlencode($email)); ?>"><?php echo $email; ?></a></dd>

		</dl>
	</div>
	<?php
}

/**
 * Display ticket comments
 * @return void
 */
function wt_ticket_comment_meta_box(){

	/**
	 * Hooked:
	 *
	 * show_admin_ticket_message 5
	 * show_admin_ticket_comments 10
	 * show_admin_ticket_commentform 20
	 */
	do_action( 'wt_admin_comment_box' );
}

function wt_ticket_create_meta_box(){

	global $wptickets;
	require_once $wptickets->plugin_dir . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'editor' . DIRECTORY_SEPARATOR . 'new-ticket.php';
}