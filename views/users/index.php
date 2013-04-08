<?php 
global $post;
global $current_user;

$page_url = get_permalink();
$current_user = wp_get_current_user();
$page_id = $post->ID;

if($current_user->ID == 0){
	?>
	<p>The support ticket system allows us to respond to your problems and enquiries as quickly as possible. When we make a response to your support ticket, you will be notified via email.</p>
	<p>Please check our knowledgebase before creating a ticket.</p>
	<p><a href="<?php echo support_url(array('support-action' => 'add'), $page_id); ?>">Submit Ticket</a></p>
	<?php 
}else{
	// count open tickets
	$open_tickets = new WP_Query(array(
		'post_type' => 'SupportMessage',
		'author' => $current_user->ID,
		'order'		=> 'DESC',
		'orderby'	=> 'meta_value_num',
		'meta_key' 	=> '_importance',
		'nopaging' => true
	));
	?>

	<p>The support ticket system allows us to respond to your problems and enquiries as quickly as possible. When we make a response to your support ticket, you will be notified via email.</p>
	<p>Please check our knowledgebase before creating a ticket.</p>
	<table width="100%">
		<tr>
			<?php if($open_tickets->post_count > 0): ?>
			<td><?php echo $open_tickets->post_count; ?> Open Tickets</td>
			<?php endif; ?>
			
			<td><a href="<?php echo support_url(array('support-action' => 'add'), $page_id); ?>">Submit Ticket</a></td>
		</tr>
	</table>

	<?php if($open_tickets->have_posts()): ?>
	<table>
		<tbody>
		<?php while($open_tickets->have_posts()): $open_tickets->the_post(); ?>
		<?php 
		$ticket_id = get_the_ID(); 
		$params = array(
			'support-action' => 'view', 
			'ticket_id' => $ticket_id
		);
		?>
			<tr>
				<td><?php the_time('d/m/Y h:i:s'); ?></td>
				<td><?php 
					$post_terms = wp_get_post_terms( get_the_ID(), 'support_groups' );
					foreach($post_terms as $term){
						echo '<strong>'.$term->name.'</strong>';
					}
					?></td>
				<td><a href="<?php echo support_url($params, $page_id); ?>"><?php the_title(); ?></a></td>
				<td><?php echo TicketModel::get_ticket_status(get_the_ID()); ?></td>
				<td><?php $priority = TicketModel::get_ticket_priority(get_the_ID()); echo $priority;  ?></td>
			</tr>
		<?php endwhile; ?>
		</tbody>
		<thead>
			<tr>
				<th>Date</th>
				<th>Department</th>
				<th>Subject</th>
				<th>Status</th>
				<th>Urgency</th>
			</tr>
		</thead>	
	</table>
	<?php endif; ?>
	<?php 
	wp_reset_postdata(); 
}
?>