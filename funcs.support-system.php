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
		'post_type' => 'SupportMessage',
		'post_title' => $title,
		'post_content' => $message,
		'post_status' => 'private',
		'post_author' => $user_id
	);

	$result = wp_insert_post($post);
	if($result > 0)
	{
		wp_set_post_terms( $result, $args['group'], 'support_groups');
		add_post_meta($result, '_read', 0);			// set flag to not read
		add_post_meta($result, '_answered', 0);		// set flag to not answered
		add_post_meta($result, '_importance', $importance);	// set importance of message
		return $result;
	}else
	{
		return false;
	}
}

function get_ticket_author_id($id = false){
	if(!$id)
		return false;

	$data = get_post($id);
	if($data)
		return $author_id = $data->post_author;

	return false;
}


function insert_support_comment($id, $message, $author_id){
	$time = current_time('mysql');

	$result = wp_insert_post(array(
		'post_parent' => $id,
		'post_content' => $message,
		'post_type' => 'st_comment',
		'post_date' => $time,
		'post_author' => $author_id,
		'post_status' => 'publish'
	));

	if ( current_user_can( 'manage_options' ) ) {
		
		$headers = 'From: Support System <jcollings89@gmail.com>' . "\r\n";

		// mail user of response
		$data = get_post($id);
		$user_id = $data->post_author;
		$user = get_userdata( $user_id );
		wp_mail( $user->user_email, 'Re:'.$data->post_title, 'Response: '.$message, $headers);
		
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
