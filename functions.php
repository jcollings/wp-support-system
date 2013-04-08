<?php

function support_url($params = array(), $page_id = 0){

	if ( get_option('permalink_structure') ) {
		// permalinks enabled
		$action = '';
		if(isset($params['support-action'])){
			$action = $params['support-action'];

			if($params['support-action'] == 'view' && isset($params['ticket_id'])){
				$action = 'view/'.$params['ticket_id'];
			}
		}
		return site_url('/support/'.$action);
	}else{
		return add_query_arg($params, get_permalink($page_id));
	}
}

function the_support_content(){
	$content = get_the_support_content();
	echo apply_filters( 'the_content', $content );
}

function get_the_support_content(){
	
	// allow admins to see post even if password protected
	if(post_password_required() && is_user_logged_in()){
		global $post;
		$content = $post->post_content;	
	}else{
		$content = get_the_content(); 	
	}
	
	// dont show email footer
	$content =  preg_replace('/--(-)?(\s)?[\n\r]+.*/s', '', $content);

	return $content;
}

/**
 * Parse Support System merge tags and return the result
 * @param  string $message
 * @param  int post_id
 * @return string
 */
function parse_support_tags($message, $post_id = false){

	$post = get_post( $post_id );

	$priority = get_post_meta( $post_id, '_importance', true);
	switch ($priority) {
		case 10:
			$priority = 'High';
			break;
		case 5:
			$priority = 'Medium';
			break;
		case 0:
			$priority = 'Low';
			break;
	}

	$author_id = $post->post_author;
	if($author_id > 0){
		$user_data = get_userdata( $author_id );
		$name = $user_data->data->user_nicename;	
	}else{
		$name = get_post_meta( $post_id, '_name', true );
	}

	$pattern = array(
		'/{message}/i',
		'/{ticket_id}/i',
		'/{name}/i',
		'/{priority}/i',
		'/{subject}/i',
	);
	$replacement = array(
		$post->post_content,
		$post_id,
		$name,
		$priority,
		$post->post_title
	);

	$message = preg_replace($pattern, $replacement, $message);

	return $message;
}
?>