<?php 

/**
 * Retrieve knowledge base articles from the database
 * @param  integer $limit  posts per page
 * @param  integer $offset offset used for pagination
 * @return WP_Query Object
 */
function get_knowledgebase($limit = 10, $offset = 0){
	$query = new WP_Query(array(
		'post_type' => 'knowledgebase_posts',
		'post_per_page' => $limit,
		'offset' => $offset
	));

	return $query;
}

function search_knowledgebase($search = ''){
	$query = new WP_Query(array(
		'post_type' => 'knowledgebase_posts',
		's' => $search
 	));

	return $query;
}

function like_post($post_id = false){

	if(!$post_id)
		return false;

	set_post_status($post_id, 'like');
}

function unlike_post($post_id = false){

	if(!$post_id)
		return false;

	set_post_status($post_id, 'unlike');
}

function set_post_status($post_id , $status = 'like'){

	if(!$post_id)
		return false;

	switch($status){
		case 'like':
		$meta_key = 'like';
		break;
		case 'unlike':
		$meta_key = 'unlike';
		break;
	}

	$likes = get_post_meta( $post_id, $meta_key, true );

	if(!$likes){
		return add_post_meta( $post_id, $meta_key, 1);
	}else{
		$likes++;
		return update_post_meta( $post_id, $meta_key, $likes);
	}
}

$like = unlike_post(139);

?>