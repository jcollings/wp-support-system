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
				<h1>My Support Tickets</h1>
			</div>
			
			<div class="meta-content">
				<?php the_content(); ?>
			</div>
		</article>
	<?php endwhile; ?>

<?php else : ?>
	<!-- No Content Found -->
<?php endif; ?>

<?php do_action('after_theme_content'); ?>

	</div>
</div>

<?php get_footer(); ?>