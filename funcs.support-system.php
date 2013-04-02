<?php

/**
 * Retrieve comments for post
 * @param  integer $id ticket_id
 * @return array()
 */
function support_ticket_get_comments($id = 0){
	if($id == 0)
		$id = get_the_ID();

	$query = new WP_Query(array(
		'post_type' => 'st_comment',
		'post_parent' => $id,
		'order' => 'ASC'
	));

	$results = $query->posts;

	wp_reset_postdata();

	return $results;
}

function open_support_ticket($title = '', $message = '', $user_id = 0, $args = array()){
	
	$importance = isset($args['importance']) ? $args['importance'] : 0;

	$post = array(
		'post_type' => 'supportmessage',
		'post_title' => $title,
		'post_content' => $message,
		'post_status' => 'publish',
		'post_author' => $user_id,
		'tax_input' => array('support_groups' => array($args['group']))
	);

	$result = wp_insert_post($post);
	if($result > 0){
		add_post_meta($result, '_read', 0);			// set flag to not read
		add_post_meta($result, '_answered', 0);		// set flag to not answered
		add_post_meta($result, '_importance', $importance);	// set importance of message
		return $result;
	}else{
		return false;
	}
}

function close_support_ticket($id = false){

	if(!$id)
		return false;
	echo 'closed';
	return update_post_meta($id, '_answered', 1);

}

function get_ticket_author_id($id = false){
	if(!$id)
		return false;

	$data = get_post($id);
	if($data)
		return $author_id = $data->post_author;

	return false;
}


function insert_support_comment($id, $message, $author_id, $type = 'response'){
	$time = current_time('mysql');

	$args = array(
		'post_parent' => $id,
		'post_content' => $message,
		'post_type' => 'st_comment',
		'post_date' => $time,
		'post_author' => $author_id,
		'post_status' => 'publish'
	);

	if($type == 'internal'){
		$args['post_type'] = 'st_comment_internal';
	}

	$result = wp_insert_post($args);


	if ( current_user_can( 'manage_options' ) ) {
		
		$headers = 'From: Support System <jcollings89@gmail.com>' . "\r\n";

		// mail user of response
		$data = get_post($id);
		$user_id = $data->post_author;
		$user = get_userdata( $user_id );
		if($type != 'internal'){
			wp_mail( $user->user_email, 'Re:'.$data->post_title, 'Response: '.$message, $headers);	
		}
	}

	return $result;
}

function the_support_content(){
	$content = get_the_support_content();
	echo apply_filters( 'the_content', $content );
}

function get_the_support_content(){
	$content = get_the_content(); 
	$content =  preg_replace('/--(-)?(\s)?[\n\r]+.*/s', '', $content);
	return $content;
}

function support_plugin_url($dir = ''){
	return plugin_dir_url( __FILE__ ) . $dir;
}

function get_tickets($args = array()){
	
	$open = isset($args['open']) ? $args['open'] : 0;
	$today = isset($args['today']) ? $args['today'] : false;
	$group = isset($args['group']) ? $args['group'] : false;

	if($open != 0 && $open != 1)
		$open = 0;

	$args = array(
		'post_type' => 'SupportMessage',
		'meta_query' => array(
			array(
				'key' => '_answered',
				'value' => $open,
				'compare' => '=',
				'type' => 'INT'
			)
		),
		'order'		=> 'DESC',
		'orderby'	=> 'meta_value_num',
		'meta_key' 	=> '_importance',
		'posts_per_page' => -1
	);

	if($group){
		$args['support_groups'] = $group;
	}

	if($today == true){
		$today = getdate();
		$args['year'] = $today['year'];
		$args['monthnum'] = $today['mon'];
		$args['day'] = $today['mday'];
	}

	$open_tickets = new WP_Query($args);	
	return $open_tickets;
}

function count_group_tickets($taxonomy = ''){
	$args = array(
		'post_type' => 'SupportMessage',
		'support_groups' => $taxonomy,
		'meta_query' => array(
			array(
				'key' => '_answered',
				'value' => 0,
				'compare' => '=',
				'type' => 'INT'
			)
		)
	);
	$query = new WP_Query($args);
	wp_reset_postdata();
	return $query->post_count;
}

function get_ticket($id = 0){
	$ticket = new WP_Query(array(
		'post_type' => 'SupportMessage',
		'p' => $id
	));

	return $ticket->post;
}

function get_latest_comment($ticket_id = 0){
	$query = new WP_Query(array(
		'post_type' => 'st_comment',
		'post_parent' => $ticket_id,
		'orderby' => 'date',
		'posts_per_page' => 1

	));

	if($query->post_count == 0)
		return false;

	return $query->post;
}

function get_ticket_status($post_id = 0){

	$ticket = get_ticket($post_id);
	$response = get_latest_comment($post_id);

	if(get_post_meta( $post_id, '_answered', true) == 1){
		return 'Ticket Closed';
	}

	if(!$response)
		return 'Awaiting Response';

	if($ticket->post_author == $response->post_author){
		// same so must be waiting on action
		return 'Awaiting Response';
	}else{
		return 'Response Send';
	}
}