<?php 
global $post;
$open_tickets = new WP_Query(array(
	'post_type' => 'SupportMessage',
	'p' => $_GET['id']
));
?>

<?php if ( $open_tickets->have_posts() ) : ?>
	<?php while ( $open_tickets->have_posts() ) : $open_tickets->the_post(); ?>
		<?php $ticket_id = get_the_ID(); ?>
		<div class="support_ticket_question">
			<div class="support_ticket_meta-head">
				<h1><?php the_title(); ?></h1>
			</div>
			<div class="support_ticket_meta-content">
				<?php the_support_content(); ?>
			</div>
		</div>

		<?php 
		// support_ticket_get_comments();
		$query = new WP_Query(array(
			'post_type' => 'st_comment',
			'post_parent' => get_the_ID(),
			'order' => 'ASC',
			'nopaging' => true,
			// 'posts_per_page' => -1
		));
		

		if($query->have_posts()): ?>
		<ul class="support_ticket_comments">
			<?php while($query->have_posts()): $query->the_post(); ?>
			<li class="support_ticket_comment">
				<div class="support_ticket_comment-head">
					<p>Posted by <?php echo the_author(); ?> on <?php the_time(); ?></p>
				</div>
				<div class="support_ticket_comment-content">
					<?php the_support_content(); ?>
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
		?>
		<form action="#" method="post">
			<input type="hidden" name="SupportFormType" id="SupportFormType" value="SubmitComment" />
			<input type="hidden" name="TicketId" id="TicketId" value="<?php echo $ticket_id ?>">
			<div class="textarea">
				<?php
				$editor_id = 'SupportResponse';
				$settings =   array(
				    'wpautop' => false, // use wpautop?
				    'media_buttons' => false, // show insert/upload button(s)
				    'textarea_rows' => 10,
				    'teeny' => true, // output the minimal editor config used in Press This
				);
				wp_editor( '', $editor_id, $settings); 
				?>
			</div>
			<div class="submit input">
				<input type="submit" value="Send" /> 
			</div>
		</form>

	<?php endwhile; ?>
<?php endif; ?>
