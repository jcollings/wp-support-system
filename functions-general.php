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

	$post_author = 0;

	if($ticket_id == 0){
		$post_author = $post->post_author;
	}else{

		$ticket = get_post($ticket_id);
		$post_author = $ticket->post_author;
	}

	if($post_author > 0){
		return true;
	}

	return false;

}