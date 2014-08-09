<div class="wrap">
    <div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
	<h2>About WP Tickets</h2>

	<?php 

	$privs = get_option('admin_priv');
	$user_roles = array('administrator');
	$users = array();
	$emails = array();

	if(isset($privs['admin_group']) && is_array($privs['admin_group'])){
		$user_roles = array_unique(array_merge($user_roles, $privs['admin_group']));
	}
	
	foreach($user_roles as $role){
		$users_query = new WP_User_Query(array(
			'role' => $role,
			'fields' => 'ID'
	    ));
	    $results = $users_query->get_results();
        if ($results) $users = array_merge($users, $results);
	}

	foreach($users as $user_id){
		$emails[] = get_the_author_meta('user_email', $user_id);
	}

	print_r($emails);

    ?>
</div>