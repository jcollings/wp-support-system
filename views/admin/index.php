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
	</ul>

<div id="poststuff" class="support_tickets">
	<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">
			<?php /*
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

			<?php if ( $tickets->have_posts() ) : ?>
			<ul class="support-ticket-index">
			<?php while ( $tickets->have_posts() ) : $tickets->the_post(); ?>
			<?php $priority = get_post_meta(get_the_ID(), '_importance', true);  ?>
			<li id="post-<?php the_ID(); ?>" class="support-ticket priority-<?php echo $priority; ?>">
				<a href="<?php echo site_url('/wp-admin/admin.php?page=support-tickets&action=view&id='.get_the_ID()); ?>">
				<div class="question">
					<div class="meta-info">
						<div class="img-wrapper">
							<?php echo get_avatar( get_the_author_email(), '50'); ?>
						</div>
					</div>
					<div class="meta-head">
						<p class="title"><?php the_title(); ?></p>
						<p class="desc">Group <?php 
							$post_terms = wp_get_post_terms( get_the_ID(), 'support_groups' );
							foreach($post_terms as $term){
								echo '<strong>'.$term->name.'</strong>';
							}
							?> / Updated on <strong><?php the_time('F j, Y \a\t g:i a'); ?></strong></p>
					</div>
					<div class="status">
						<p>Response Needed</p>
					</div>
				</div>
				</a>
			</li>

			<?php endwhile; ?>
			</ul>
			<?php endif; ?>




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