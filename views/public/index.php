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
?>

<p>The support ticket system allows us to respond to your problems and enquiries as quickly as possible. When we make a response to your support ticket, you will be notified via email.</p>
<p>Please check our knowledgebase before creating a ticket.</p>
<table width="100%">
	<tr>
		<?php if($open_tickets->post_count > 0): ?>
		<td><a href="<?php echo add_query_arg('support-action', 'browse'); ?>"><?php echo $open_tickets->post_count; ?> Open Tickets</a></td>
		<?php else: ?>
		<td><?php echo $open_tickets->post_count; ?> Open Tickets</td>
		<?php endif; ?>
		<td><a href="<?php echo add_query_arg('support-action', 'add'); ?>">Submit Ticket</a></td>
	</tr>
</table>