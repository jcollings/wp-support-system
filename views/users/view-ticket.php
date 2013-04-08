<?php 
global $post;
$open_tickets = TicketModel::get_ticket(get_query_var( 'ticket_id' ));
?>

<p><a href="<?php the_permalink(get_query_var( 'page_id' )); ?>">Back to Open Tickets</a></p>

<?php if ( $open_tickets->have_posts() && $open_tickets->post_count == 1 ) : ?>
	<?php while ( $open_tickets->have_posts() ) : $open_tickets->the_post(); ?>

<?php 
// if password protected show the content to display password box
if ( post_password_required() ){
	the_content();
	return;
}

/**
 * Remove Private/Protected from title
 */
add_filter( 'the_title', 'my_title_function' );
function my_title_function($title){
	$title = preg_replace( array('#Protected:#', '#Private:#'), '', $title);
	return $title;
}

$ticket_id = get_the_ID();
$priority = TicketModel::get_ticket_priority($ticket_id);
$author_name = TicketModel::get_ticket_author($ticket_id);
$author_email = TicketModel::get_ticket_email($ticket_id);

?>
<div id="post-<?php the_ID(); ?>" class="support-ticket single">
	<div class="question">
		<div class="left">
			<div class="meta-head">
				<h1><?php the_title(); ?></h1>
				<p class="desc">Posted on <?php the_time('F j, Y \a\t g:i a'); ?></p>
			</div>
			<div class="meta-content">
				<?php the_support_content(); ?>
			</div>
		</div>
		<div class="right">
			<div class="meta-info">
				<div class="img-wrapper">
					<?php echo get_avatar( $author_email, '96'); ?>
					<p><?php echo $author_name; ?></p>
				</div>
			</div>
		</div>
	</div>

	<footer class="meta-footer">
		<div id="comments" class="comments-area">
			<?php 
			$query = TicketModel::get_ticket_comments($ticket_id);
			

			if($query->have_posts()): ?>
			<ul>
				<?php while($query->have_posts()): $query->the_post();
				$response_id = get_the_ID();
				$author_name = TicketModel::get_ticket_author($response_id);
				$author_email = TicketModel::get_ticket_email($response_id);
				?>
				<li>
					<div class="response">
						<div class="left">
							<div class="meta-head">
								<h1><?php the_title(); ?></h1>
								<p class="desc">Posted on <?php the_time('F j, Y \a\t g:i a'); ?></p>
							</div>
							<div class="meta-content">
								<?php the_support_content(); ?>
							</div>
						</div>
						<div class="right">
							<div class="meta-info">
								<div class="img-wrapper">
									<?php echo get_avatar( $author_email ); ?>
									<p><?php echo $author_name; ?></p>
								</div>
							</div>
						</div>
					</div>
				</li>
				<?php endwhile; ?>
			</ul>
			<?php endif; ?>
			<?php wp_reset_postdata(); ?>

			<?php
			/**
			 * Display Comment Form
			 */
			echo FormHelper::create('SubmitTicketComment', array(
				'title' => 'Add Response',
			));
			echo FormHelper::hidden('id', array('value' => $ticket_id));
			echo FormHelper::wysiwyg('response', array('label' => 'Message'));
			echo FormHelper::end('Send');
			?>
		</div>
	</footer>
</div>


	<?php endwhile; ?>
<?php else: ?>

<?php endif; ?>