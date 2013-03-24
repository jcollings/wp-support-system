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

<?php $priority = get_post_meta(get_the_ID(), '_importance', true);  ?>

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
					<?php echo get_avatar( get_the_author_email(), '96'); ?>
					<p><?php the_author(); ?></p>
				</div>
			</div>
		</div>
	</div>

	<footer class="meta-footer">
		<div id="comments" class="comments-area">
			<?php 
			$query = new WP_Query(array(
				'post_type' => 'st_comment',
				'post_parent' => get_the_ID(),
				'order' => 'ASC',
				'nopaging' => true,
			));
			

			if($query->have_posts()): ?>
			<ul>
				<?php while($query->have_posts()): $query->the_post(); ?>
				<li>
					<div class="response">
						<div class="left">
							<div class="meta-head">
								<h1><?php the_title(); ?></h1>
								<p class="desc">Posted by <?php the_author(); ?> on <?php the_time('F j, Y \a\t g:i a'); ?></p>
							</div>
							<div class="meta-content">
								<?php the_support_content(); ?>
							</div>
						</div>
						<div class="right">
							<div class="meta-info">
								<div class="img-wrapper">
									<?php echo get_avatar( get_the_author_email()); ?>
									<p><?php the_author(); ?></p>
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
	</div>
</footer>
</div>


	<?php endwhile; ?>
<?php endif; ?>