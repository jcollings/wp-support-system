<?php 
global $post;
global $current_user;
$page_url = get_permalink();
$current_user = wp_get_current_user();

// count open tickets
$open_tickets = new WP_Query(array(
	'post_type' => 'SupportMessage',
	'meta_query' => array(
		array(
			'key' => '_answered',
			'value' => 0,
			'compare' => '=',
			'type' => 'INT'
		)
	),
	'author' => $current_user->ID,
	'order'		=> 'DESC',
	'orderby'	=> 'meta_value_num',
	'meta_key' 	=> '_importance'
));
wp_reset_postdata();

// get all users tickets
$all_tickets = new WP_Query(array(
	'post_type' => 'SupportMessage',
	'author' => $current_user->ID,
	'order'		=> 'DESC',
	'orderby'	=> 'meta_value_num',
	'meta_key' 	=> '_importance'
));
?>



<p>The support ticket system allows us to respond to your problems and enquiries as quickly as possible. When we make a response to your support ticket, you will be notified via email.</p>
<p>Please check our knowledgebase before creating a ticket.</p>
<table width="100%">
	<tr>
		<td><?php echo $open_tickets->post_count; ?> Open Tickets</td>
		<td><a href="<?php echo add_query_arg('support-action', 'add'); ?>">Submit Ticket</a></td>
	</tr>
</table>

<br />

<table width="100%">
	<?php if ( $open_tickets->have_posts() ) : ?>
	<thead>
		<th>Date</th>
		<th>Subject</th>
		<th>Status</th>
		<th>Urgancy</th>
	</thead>
	<tbody>
	<?php while ( $open_tickets->have_posts() ) : $open_tickets->the_post(); ?>
	<tr>
		<td><?php the_time('F j, Y g:i a');  ?></td>
		<?php 
		$params = array(
			'support-action' => 'view', 
			'id' => get_the_ID()
		);
		?>
		<td><a href="<?php echo add_query_arg($params, $page_url); ?>"><?php the_title(); ?></a></td>
		<td><?php if(get_post_meta(get_the_ID(), '_answered', true) == 0): ?>Open<?php else: ?>Closed<?php endif; ?></td>
		<td><?php echo get_post_meta(get_the_ID(), '_importance', true); ?></td>
	</tr>
	<?php endwhile; ?>
	</tbody>
	<?php endif; ?>
</table>
<?php wp_reset_postdata(); ?>