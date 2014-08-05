<?php 
function is_support_ticket(){
	global $post;
	
	if(is_singular('ticket' )){
		return true;
	}

	return false;
}

function is_member_ticket($ticket_id = 0){
	global $post;

	if($ticket_id == 0){
		$ticket_id = $post->ID;
	}

	$post_author = intval(get_post_meta( $ticket_id, '_ticket_author', true ));

	if($post_author > 0){
		return true;
	}

	return false;

}