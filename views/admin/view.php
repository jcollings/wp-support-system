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
	<h2>Support Tickets</h2>

<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">

			<?php if ( $open_tickets->have_posts() ) : ?>
			<?php while ( $open_tickets->have_posts() ) : $open_tickets->the_post();

$ticket_id = get_the_ID();
$author_id = get_the_author_meta( 'ID' );
$priority = get_post_meta(get_the_ID(), '_importance', true);

if($author_id > 0){
	// member ticket
	$author_name = get_the_author();
	$author_email = get_the_author_meta( 'email' );
}else{
	// public ticket
	$author_name = get_post_meta( get_the_ID(), '_name', true);
	$author_email = get_post_meta( get_the_ID(), '_email', true);
}
?>

			<!-- Content -->
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
								<?php echo get_avatar( $author_email, '96'); ?>
								<p><?php echo $author_name; ?></p>
							</div>
						</div>
					</div>
				</div>

				<footer class="meta-footer">
					<div id="comments" class="comments-area">
						<?php 
						$query = new WP_Query(array(
							'post_type' => array('st_comment', 'st_comment_internal'),
							'post_parent' => get_the_ID(),
							'order' => 'ASC'
						));
						

						if($query->have_posts()): ?>
						<ul>
							<?php while($query->have_posts()): $query->the_post(); ?>
							<?php
							$author_id = get_the_author_meta( 'ID' );
							if($author_id > 0){
								// member ticket
								$author_name = get_the_author();
								$author_email = get_the_author_meta( 'email' );
							}else{
								// public ticket
								$author_name = get_post_meta( get_the_ID(), '_name', true);
								$author_email = get_post_meta( get_the_ID(), '_email', true);
							}
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
												<?php echo get_avatar( $author_email); ?>
												<p><?php echo $author_name; ?></p>
											</div>
										</div>
									</div>
								</div>
								<!--<div class="actions">
									<ul>
										<li><a href="#">Response Pending</a></li>
									</ul>
								</div>-->
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
						<div class="form">
							<form action="#" method="post">
								<h2>Add Response:</h2>
								<input type="hidden" name="SupportFormType" id="SupportFormType" value="SubmitComment" />
								<input type="hidden" name="TicketId" id="TicketId" value="<?php echo $ticket_id ?>">
								<div class="textarea">
									<label>Message:</label>
									<?php 
									$editor_id = 'SupportResponse';
									$settings =   array(
									    'wpautop' => false, // use wpautop?
									    'media_buttons' => false, // show insert/upload button(s)
									    'textarea_rows' => 10,
									    'teeny' => false, // output the minimal editor config used in Press This
									    'tinymce' => false
									);
									wp_editor( '', $editor_id, $settings);  
									?>
								</div>
								<div class="input support-checkbox">
									<input type="checkbox" name="SupportInternalNote" value="1" />
									<label>Internal Note</label>
								</div>
								<div class="input support-checkbox">
									<input type="checkbox" name="SupportCloseTicket" value="1" />
									<label>Close ticket on reply</label>
								</div>
								<div class="submit input">
									<input type="submit" value="Send" /> 
								</div>
							</form>
						</div>

					</div><!-- #comments .comments-area -->
				</footer>


			</article>
			<!-- /Content -->
		<?php endwhile; ?>
		<?php endif; ?>


		</div><!-- /#post-body-content -->
		
		<div id="postbox-container-1" class="postbox-container">
			<div id="postimagediv" class="postbox ">
				<h3 class="hndle"><span>Transfer Department</span></h3>
				<div class="inside">
					<table width="100%">
						<tr>
							<?php $terms = get_terms( 'support_groups' ); ?>
							<td>
								<select>
									<option>Choose Department</option>
									<?php foreach($terms as $term): ?>
									<option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
									<?php endforeach; ?>
								</select>
							</td>
							<td><input type="submit" value="Go" /></td>
						</tr>
					</table>
				</div>
			</div>

		</div><!-- /postbox-container-1 -->
	</div><!-- /#post-body -->
</div><!-- /#poststuff -->	
</div>