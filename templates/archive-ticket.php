<?php
/**
 * The Template for displaying all single posts
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

// @todo: restict access to current users tickets if not have support priviliges or ticket is private

global $wpss_counter;
$wpss_counter = 0;

get_header(); ?>

<?php do_action('before_theme_content'); ?>

<div class="primary site-content">

<?php  if(is_archive()): ?>
	<h1 class="entry-title">Support Tickets</h1>
<?php endif; ?>

<?php if(have_posts()): ?>
	<?php while(have_posts()): the_post(); ?>
		<?php $wpss_counter++ ?>
		<?php wt_get_template_part( 'content-ticket' ); ?>

	<?php endwhile; ?>
	
<?php endif; ?>

<?php wt_pagination(); ?>

</div>

<?php get_sidebar(); ?>

<?php do_action('after_theme_content'); ?>

<?php get_footer(); ?>