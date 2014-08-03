<?php
/**
 * The Template for displaying all single posts
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

get_header(); ?>

<?php do_action('before_theme_content'); ?>

<?php if(have_posts()): ?>
	<?php while(have_posts()): the_post(); ?>

		<?php wt_get_template_part( 'content-single-ticket' ); ?>

	<?php endwhile; ?>
<?php endif; ?>

<?php do_action('after_theme_content'); ?>

<?php get_footer(); ?>