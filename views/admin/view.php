<?php 
global $post;
$ticket_id = $_GET['id'];
$open_tickets = new WP_Query(array(
	'post_type' => 'SupportMessage',
	'p' => $ticket_id
));

?>
<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
	<h2>Support Tickets <a class="add-new-h2" href="<?php echo site_url('/wp-admin/admin.php?page=support-tickets&action=close&id='.$ticket_id); ?>">Close Ticket</a></h2>

<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">

			<?php if ( $open_tickets->have_posts() ) : ?>
			<?php while ( $open_tickets->have_posts() ) : $open_tickets->the_post(); ?>
			<!-- Content -->
			<?php $priority = get_post_meta(get_the_ID(), '_importance', true);  ?>

			<article id="post-<?php the_ID(); ?>" class="support-ticket single">
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
							'order' => 'ASC'
						));
						

						if($query->have_posts()): ?>
						<ul>
							<?php while($query->have_posts()): $query->the_post(); ?>
							<li>
								<div class="response">
									<div class="meta-head">
										<p class="desc">Posted by <?php the_author(); ?> on <?php the_time('F j, Y \a\t g:i a'); ?></p>
									</div>
									<div class="meta-content">
										<?php the_content(); ?>
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
							<input type="hidden" name="TicketId" id="TicketId" value="<?php echo $ticket_id; ?>">
							<div class="textarea">
								<?php
								$editor_id = 'SupportResponse';
								$settings =   array(
								    'wpautop' => false, // use wpautop?
								    'media_buttons' => false, // show insert/upload button(s)
								    'textarea_rows' => 10,
								    'teeny' => true, // output the minimal editor config used in Press This
								    'tinymce' => false
								);
								wp_editor( '', $editor_id, $settings); 
								?>
							</div>
							<div class="submit input">
								<input type="submit" value="Send" /> 
							</div>
						</form>

					</div><!-- #comments .comments-area -->
				</footer>


			</article>
			<!-- /Content -->
		<?php endwhile; ?>
		<?php endif; ?>


		</div><!-- /#post-body-content -->
		<div id="postbox-container-1" class="postbox-container">
		</div><!-- /postbox-container-1 -->
	</div><!-- /#post-body -->
</div><!-- /#poststuff -->	
</div>