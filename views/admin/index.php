<?php 
// http://igeneralforums.com/misc.php?page=imgurimagegrabbercrazyedition
global $post;
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
	'order'		=> 'DESC',
	'orderby'	=> 'meta_value_num',
	'meta_key' 	=> '_importance',
	'posts_per_page' => -1
));

$closed_tickets = new WP_Query(array(
	'post_type' => 'SupportMessage',
	'meta_query' => array(
		array(
			'key' => '_answered',
			'value' => 1,
			'compare' => '=',
			'type' => 'INT'
		)
	),
	'order'		=> 'ASC',
	'orderby'	=> 'meta_value_num',
	'meta_key' 	=> '_importance',
	'posts_per_page' => -1
));
?>
<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
	<h2>Support Tickets</h2>

<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">

			<table class="wp-list-table widefat fixed">
			<?php if ( $open_tickets->have_posts() ) : ?>
			<thead>
				<th>Subject</th>
				<th>Author</th>
				<th>Content</th>
				<th>Created</th>
				<th>_</th>
			</thead>
			<tbody>
			<?php while ( $open_tickets->have_posts() ) : $open_tickets->the_post(); ?>
			<?php $priority = get_post_meta(get_the_ID(), '_importance', true);  ?>
			<tr class="<?php echo 'priority-'.$priority; ?>">
				<td><?php the_title(); ?></td>
				<td><?php the_author(); ?></td>
				<td><?php echo get_the_support_content(); ?></td>
				<td><?php the_time('F j, Y g:i a');  ?></td>
				<td><a href="<?php echo site_url('/wp-admin/admin.php?page=support-tickets&action=view&id='.get_the_ID()); ?>">View</a> | <a href="<?php echo site_url('/wp-admin/admin.php?page=support-tickets&action=close&id='.get_the_ID()); ?>">Close</a></td>
			</tr>
			<?php endwhile; ?>
			</tbody>
			<?php endif; ?>
			</table>

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
			</table>
		</div><!-- /#post-body-content -->
		<div id="postbox-container-1" class="postbox-container">

			<div id="postimagediv" class="postbox ">
				<h3 class="hndle"><span>Overview</span></h3>
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