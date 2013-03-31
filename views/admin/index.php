<?php 
$open_tickets = get_tickets(array('open' => 0));
$closed_tickets = get_tickets(array('open' => 1));
$today_open_tickets = get_tickets(array('open' => 0, 'today' => true));
$today_closed_tickets = get_tickets(array('open' => 1, 'today' => true));

if(isset($_GET['status']) && $_GET['status'] == 'closed'){
	$tickets = $closed_tickets;
	$tab = 'closed';
}elseif(isset($_GET['group']) && !empty($_GET['group'])){
	$tickets = get_tickets(array('open' => 0, 'group' => $_GET['group']));
	$tab = $_GET['group'];
}else{
	$tab = 'open';
	$tickets = $open_tickets;
}
?>
<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
	<h2>Support Tickets</h2>

	<ul class="subsubsub">
		<li class="all"><a href="admin.php?page=support-tickets" <?php if($tab == 'open'): ?>class="current"<?php endif; ?>>All <span class="count">(<?php echo $open_tickets->post_count; ?>)</span></a> |</li>
		<?php 
		$terms = get_terms( 'support_groups', array('hide_empty' => false) ); 
		foreach($terms as $term): ?>
		<li class="support-group"><a href="admin.php?page=support-tickets&group=<?php echo $term->slug; ?>" <?php if($tab == $term->slug): ?>class="current"<?php endif; ?>><?php echo $term->name; ?> <span class="count">(<?php echo count_group_tickets($term->slug); ?>)</span></a> |</li>
		<?php endforeach; ?>
		<li class="close"><a href="admin.php?page=support-tickets&status=closed" <?php if($tab == 'closed'): ?>class="current"<?php endif; ?>>Closed <span class="count">(<?php echo $open_tickets->post_count; ?>)</span></a></li>
	</ul>

<div id="poststuff" class="support_tickets">
	<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">
			
			<table class="wp-list-table widefat fixed">
			<?php if ( $tickets->have_posts() ) : ?>
			<thead>
				<th width="70">Date</th>
				<th>Department</th>
				<th>Subject</th>
				<th>Status</th>
				<th width="55">Urgency</th>
			</thead>
			<tbody>
			<?php while ( $tickets->have_posts() ) : $tickets->the_post(); ?>
			<?php $priority = get_post_meta(get_the_ID(), '_importance', true);  ?>
			<tr class="priority-<?php echo $priority; ?>">
				<td><?php the_time('h:i:s\<\b\r \/\>d/m/Y'); ?></td>
				<td><?php 
				$post_terms = wp_get_post_terms( get_the_ID(), 'support_groups' );
				foreach($post_terms as $term){
					echo $term->name;
				}
				 ?></td>
				<td><a href="<?php echo site_url('/wp-admin/admin.php?page=support-tickets&action=view&id='.get_the_ID()); ?>"><?php echo the_title(); ?></a></td>
				<td><?php echo get_ticket_status(get_the_ID()); ?></td>
				<td><?php $priority = get_post_meta(get_the_ID(), '_importance', true); //echo $priority;  ?></td>
			</tr>
			<?php endwhile; ?>
			</tbody>
			<?php endif; ?>
			</table>

		</div><!-- /#post-body-content -->
		<div id="postbox-container-1" class="postbox-container">
			<?php /*
			<div id="postimagediv" class="postbox ">
				<h3 class="hndle"><span>Todays Progress</span></h3>
				<div class="inside">
					<?php
					$today_open = $today_open_tickets->post_count;
					$today_closed = $today_closed_tickets->post_count; 
					$today_total = $today_open + $today_closed; 
					?>
					<table width="100%">
						<tr>
							<td>Open Tickets: <?php echo $today_open; ?></td>
							<td>Closed Tickets: <?php echo $today_closed; ?></td>
						</tr>
						<tr>
							<td>Progress: <?php echo round(($today_closed / $today_total) * 100,0); ?>%</td>
						</tr>
					</table>
				</div>
			</div> */ ?>

			<div id="postimagediv" class="postbox ">
				<h3 class="hndle"><span>Total Progress</span></h3>
				<div class="inside">
					<?php 
					$open = $open_tickets->post_count; 
					$closed = $closed_tickets->post_count;
					$total = $open + $closed; 
					?>
					<table width="100%">
						<tr>
							<td>Open Tickets: <?php echo $open; ?></td>
							<td>Closed Tickets: <?php echo $closed; ?></td>
						</tr>
						<tr>
							<td>Progress: <?php echo round(($closed / $total) * 100,0); ?>%</td>
						</tr>
					</table>
				</div>
			</div>

		</div><!-- /postbox-container-1 -->
	</div><!-- /#post-body -->
</div><!-- /#poststuff -->	
</div>