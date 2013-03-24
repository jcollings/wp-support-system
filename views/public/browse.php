<?php
global $current_user;
$page_url = get_permalink();
$current_user = wp_get_current_user();
$page_url =  get_permalink(get_query_var( 'page_id' ));

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
?>

<p><a href="<?php the_permalink(get_query_var( 'page_id' )); ?>">Back</a></p>

<?php if ( $open_tickets->have_posts()) : ?>
	<div class="tickets">
		<h1>Open Tickets</h1>
	<?php while ( $open_tickets->have_posts() ) : $open_tickets->the_post(); ?>
		<?php 
		$ticket_id = get_the_ID(); 
		$params = array(
			'support-action' => 'view', 
			'id' => $ticket_id
		);
		$priority = get_post_meta(get_the_ID(), '_importance', true);  ?>

<div id="post-<?php the_ID(); ?>" class="support-ticket single">
	<div class="question ticket-<?php echo $priority; ?>">
		<div class="left">
			<div class="meta-head">
				<h1><a href="<?php echo add_query_arg($params, $page_url); ?>"><?php the_title(); ?></a></h1>
				<p class="desc">Posted on <?php the_time('F j, Y \a\t g:i a'); ?></p>
			</div>
			<div class="meta-content">
				<?php the_support_content(); ?>
			</div>
		</div>
		<div class="right">
			<div class="meta-info">
				<div class="img-wrapper">
					<?php echo get_avatar( get_the_author_email(), '96'); ?>
					<p><?php the_author(); ?></p>
				</div>
			</div>
		</div>
	</div>
</div>


	<?php endwhile; ?>
</div>
<?php else: ?>

<?php endif; ?>