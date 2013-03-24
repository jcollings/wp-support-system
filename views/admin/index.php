<?php 
function get_tickets($args = array()){
	
	$open = isset($args['open']) ? $args['open'] : 0;
	$today = isset($args['today']) ? $args['today'] : false;

	if($open != 0 && $open != 1)
		$open = 0;

	$args = array(
		'post_type' => 'SupportMessage',
		'meta_query' => array(
			array(
				'key' => '_answered',
				'value' => $open,
				'compare' => '=',
				'type' => 'INT'
			)
		),
		'order'		=> 'DESC',
		'orderby'	=> 'meta_value_num',
		'meta_key' 	=> '_importance',
		'posts_per_page' => -1
	);

	if($today == true){
		$today = getdate();
		$args['year'] = $today['year'];
		$args['monthnum'] = $today['mon'];
		$args['day'] = $today['mday'];
	}

	$open_tickets = new WP_Query($args);	
	return $open_tickets;
}

$open_tickets = get_tickets(array('open' => 0));
$closed_tickets = get_tickets(array('open' => 1));
$today_open_tickets = get_tickets(array('open' => 0, 'today' => true));
$today_closed_tickets = get_tickets(array('open' => 1, 'today' => true));

if(isset($_GET['status']) && $_GET['status'] == 'closed'){
	$tickets = $closed_tickets;
	$tab = 'closed';
}else{
	$tab = 'open';
	$tickets = $open_tickets;
}
?>
<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
	<h2>Support Tickets</h2>

	<ul class="subsubsub">
		<li class="open"><a href="admin.php?page=support-tickets" <?php if($tab == 'open'): ?>class="current"<?php endif; ?>>Open <span class="count">(<?php echo $open_tickets->post_count; ?>)</span></a> |</li>
		<li class="closed"><a href="admin.php?page=support-tickets&status=closed" <?php if($tab == 'closed'): ?>class="current"<?php endif; ?>>Closed <span class="count">(<?php echo $closed_tickets->post_count; ?>)</span></a></li>
	</ul>

<div id="poststuff" class="support_tickets">
	<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">

			<table class="wp-list-table widefat fixed">
			<?php if ( $tickets->have_posts() ) : ?>
			<thead>
				<th>Subject</th>
				<th>Group</th>
				<th>Content</th>
				<th>Created</th>
				<th>_</th>
			</thead>
			<tbody>
			<?php while ( $tickets->have_posts() ) : $tickets->the_post(); ?>
			<?php $priority = get_post_meta(get_the_ID(), '_importance', true);  ?>
			<tr class="<?php if($tab == 'open'){ echo 'priority-'.$priority; } ?>">
				<td><?php the_title(); ?></td>
				<td><?php 
				$post_terms = wp_get_post_terms( get_the_ID(), 'support_groups' );
				foreach($post_terms as $term){
					echo $term->name;
				}
				 ?></td>
				<td><?php echo get_the_support_content(); ?></td>
				<td><?php the_time('F j, Y g:i a');  ?></td>
				<td><a href="<?php echo site_url('/wp-admin/admin.php?page=support-tickets&action=view&id='.get_the_ID()); ?>">View</a> | <a href="<?php echo site_url('/wp-admin/admin.php?page=support-tickets&action=close&id='.get_the_ID()); ?>">Close</a></td>
			</tr>
			<?php endwhile; ?>
			</tbody>
			<?php endif; ?>
			</table>

			<?php /*
			<h2>Closed Tickets</h2>
			<table class="wp-list-table widefat fixed">
			<?php if ( $closed_tickets->have_posts() ) : ?>
			<thead>
				<th>Subject</th>
				<th>Author</th>
				<th>Importance</th>
				<th>Created</th>
				<th>_</th>
			</thead>
			<tbody>
			<?php while ( $closed_tickets->have_posts() ) : $closed_tickets->the_post(); ?>
			<tr>
				<td><?php the_title(); ?></td>
				<td><?php the_author(); ?></td>
				<td><?php 
				$priority = get_post_meta(get_the_ID(), '_importance', true);  
				switch($priority)
				{
					case 10:
					{
						echo 'High';
						break;
					}
					case 5:
					{
						echo 'Medium';
						break;
					}
					case 0:
					{
						echo 'Low';
						break;
					}
				}
				?></td>
				<td><?php the_time('F j, Y g:i a');  ?></td>
				<td><a href="<?php echo site_url('/wp-admin/admin.php?page=support-tickets&action=view&id='.get_the_ID()); ?>">View</a></td>
			</tr>
			<?php endwhile; ?>
			</tbody>
			<?php endif; ?>
			</table> */ ?>
		</div><!-- /#post-body-content -->
		<div id="postbox-container-1" class="postbox-container">

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
			</div>

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