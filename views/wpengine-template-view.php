<?php 

get_header(); ?>

<div id="bordertop-wrapper">
	<div id="bordertop" class="container_12">
	</div>
</div>

<div id="home-wrapper">
	<div id="home" class="container_12">

<?php do_action('before_theme_content'); ?>

<?php if ( have_posts() ) : ?>

	<?php while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<div class="meta-head">
				<h1><?php the_title(); ?></h1>
			</div>
			
			<div class="meta-content">
				<?php the_content(); ?>
			</div>

			<footer class="meta-footer">
				<div id="comments" class="comments-area">

					<?php
					/**
					 * Display Comments / Responses
					 */
					$args = array(
						'post_id' => get_the_ID(), // use post_id, not post_ID
					);
					$comments = get_comments($args);
					$comments = array_reverse($comments);
					?>
					

					<?php if(!empty($comments)): ?>
					<ul>
						<?php foreach($comments as $comment): ?>
						<li>
							<p>Posted by <?php echo $comment->comment_author; ?> on <?php echo $comment->comment_date_gmt; ?></p>
							<p><?php echo $comment->comment_content; ?></p>
						</li>
						<?php endforeach; ?>
					</ul>
					<?php endif; ?>

					<?php
					/**
					 * Display Comment Form
					 */
					?>
					<form action="#" method="post">
						<input type="hidden" name="SupportFormType" id="SupportFormType" value="SubmitComment" />
						<input type="hidden" name="TicketId" id="TicketId" value="<?php echo get_the_ID(); ?>">
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

				</div><!-- #comments .comments-area -->
			</footer>


		</article>
	<?php endwhile; ?>

<?php else : ?>
	<!-- No Content Found -->
<?php endif; ?>

<?php do_action('after_theme_content'); ?>

	</div>
</div>

<?php get_footer(); ?>