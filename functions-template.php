<?php 

add_action('get_template_part_content-ticket', 'wt_get_ticket_content');
function wt_get_ticket_content($test){

}

function wt_get_template_part($template){

	global $wptickets;

	$located = $wptickets->plugin_dir . 'templates/'.$template.'.php';
	// echo  get_stylesheet_directory() . '/wptickets/'.$template.'.php';
	$template_file = get_stylesheet_directory() . '/wptickets/'.$template.'.php';
	if(is_file($template_file)){
		$located = $template_file;
	}

	return load_template( $located, false );
}

add_filter( 'template_include', 'wt_get_template');
function wt_get_template($template = ''){

	global $post;
	global $wptickets;

	if(is_post_type_archive( 'ticket' )){
		return $wptickets->plugin_dir . 'templates/archive-ticket.php';
	}elseif(is_support_ticket()){
		return $wptickets->plugin_dir . 'templates/single-ticket.php';
	}

	return $template;
}

function wt_get_add_ticket_link(){
	$config = get_option('support_system_config');
	return get_permalink($config['add_ticket_page']);
}

function wt_the_notifications($section = null, $session = null){
	global $wptickets;

	$notifications = $wptickets->session->get_notifications($section, $session);
	if($notifications){

		// allow notification customisation
		$vars = apply_filters( 'wt/output_notification', array(
			'container' => 'div',
			'container_class' => '',
		));

		$container = isset($vars['container']) && !empty($vars['container']) ? $vars['container'] : 'div';
		$class = isset($vars['container_class']) && !empty($vars['container_class']) ? $vars['container_class'] : '';

		$wrapper_before = '<'.$container.' class="wt-notification '.$class.'">';
		$wrapper_after = '</'.$container.'>';

		echo $wrapper_before;

		foreach($notifications as $notification){
			
			echo '<p>'. $notification . '</p>';
		}

		echo $wrapper_after;
	}
}

/**
 * Ticket Functions
 */
function wt_the_ticket_class(){
	global $post;
	$classes = array();

	$classes[] = 'wt-'.wt_get_ticket_access();
	$classes[] = 'wt-'.strtolower(wt_get_ticket_priority(get_the_ID()));
	echo implode(' ', $classes);
}

function wt_get_ticket_access(){
	global $post;

	$access = get_post_meta(get_the_ID(), '_ticket_access', true);
	return $access ? $access : 'private';
}

function wt_get_ticket_status(){
	global $post;

	$status = wp_get_post_terms( $post->ID, 'status');
	if($status){
		return $status[0]->name;
	}
	
	// return default status
	return false;
}

function wt_get_ticket_priority($ticket_id = 0){

	global $post, $wptickets;

	if($ticket_id == 0){
		$ticket_id = $post->ID;
	}

	$priorities = wt_list_ticket_priorities();
	$ticket_priority = get_post_meta( $ticket_id, '_priority', true);

	if(!$ticket_priority)
		$ticket_priority = apply_filters( 'wt/set_default_ticket_priority', $priorities );

	return $priorities[$ticket_priority];
}

function wt_list_ticket_priorities(){
	$priorities = apply_filters( 'wt/list_ticket_priorities', array() );
	ksort($priorities);
	return $priorities;
}

function wt_list_ticket_departments(){
	return get_terms( 'department', array('hide_empty' => false) );
}

function wt_list_ticket_status(){
	return get_terms( 'status', array('hide_empty' => false) );
}

function wt_get_ticket_department(){
	global $post;
	$output = array();
	$terms = wp_get_post_terms( $post->ID, 'department');

	foreach($terms as $term){
		$output[] = $term->name;
	}
	
	return implode(', ', $output);
}

function wt_get_ticket_author_meta($ticket_id, $key = ''){

	switch($key){
		case 'email':

			if(is_member_ticket($ticket_id)){

				// get member email
				$author_id = intval(get_post_meta( $ticket_id, '_ticket_author', true ));
				return get_the_author_meta('user_email', $author_id);

			}else{

				// get public email
				return get_post_meta( $ticket_id, '_user_email', true );
			}
		break;
		case 'name':

			if(is_member_ticket($ticket_id)){

				// get member email
				$author_id = intval(get_post_meta( $ticket_id, '_ticket_author', true ));
				return get_the_author_meta('user_nicename', $author_id);

			}else{

				// get public email
				return get_post_meta( $ticket_id, '_user_name', true );
			}
		break;
	}

	return false;
}

function wt_get_ticket_source($ticket_id){
	return 'web';
}


/**
 * Comment Functions
 */
function wt_the_comment_class(){

	global $post;
	global $comment;
	
	$classes = array();

	$comment_class = '';
	if($comment->user_id == $post->post_author){
		$classes[] = 'author';
	}

	$classes[] = 'wt-comment-'.wt_get_comment_access();

	echo implode(' ', $classes);
}

function wt_get_comment_access(){
	global $comment;

	$access = get_comment_meta( get_comment_id(), '_comment_access', true ); 
	return $access ? $access : 'public';
}
?>