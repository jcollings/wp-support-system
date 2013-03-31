<?php 

add_shortcode( 'knowledgebase', 'output_knowledgebase' );

/**
 * Output the knowledebase via shortcode
 * @return void
 */
function output_knowledgebase(){
	
	$search  = '';

	if(isset($_POST['search']) && !empty($_POST['search'])){
		$search = $_POST['search'];
		$posts = search_knowledgebase($search);
	}else{
		$posts = get_knowledgebase();
	}

	$output = '';

	$output .= '<div class="input search stkb-searchbox">
	<form method="post">
	<input type="text" name="search" value="'.$search.'" />
	<input type="submit" value="search" />
	</form>
	</div>';

	$link = add_query_arg('id', get_the_ID());

	if($posts->have_posts()){
		$output .= '<ul class="stkb-index">';
		while($posts->have_posts()){
			$output .= '<li>';
			$posts->the_post();

			

			$output .= '<h2><a href="'.get_permalink().'">' . get_the_title() . '</a></h2>';
			$output .= get_the_content();

			$output .= '<a href="'.add_query_arg('action', 'like', $link).'">Like</a> <a href="'.add_query_arg('action', 'unlike', $link).'">Un Like</a>';

			$output .= '</li>';
		}
		$output .= '</ul>';

	}else{
		if(!empty($search)){
			$output .= 'No Articles have been found.';
		}
	}
	wp_reset_postdata();
	return $output;
}
