<?php 
$ticket_id = intval($_GET['id']);
$open_tickets = TicketModel::get_ticket($ticket_id);
$ticket = $open_tickets->post;
$department = '';
$post_terms = wp_get_post_terms( $ticket_id, 'support_groups' );

$status = get_post_meta( $ticket_id, '_answered', true);
$status_word = $status == 1 ? 'Closed' : 'Open';

foreach($post_terms as $term){
	$department .= $term->name;
}
?>
<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
	<h2>Support Tickets</h2>
	<ul class="subsubsub">
		<li><a href="admin.php?page=support-tickets">&larr; Back to Tickets</a></li>
	</ul>

<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-2">

	<?php if ( $open_tickets->have_posts() ) : ?>
		<?php while ( $open_tickets->have_posts() ) : $open_tickets->the_post();
			$priority = TicketModel::get_ticket_priority($ticket_id);
			$author_name = TicketModel::get_ticket_author($ticket_id);
			$author_email = TicketModel::get_ticket_email($ticket_id);
		?>

		

		<div id="post-body-content">

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
						$query = TicketModel::get_ticket_comments($ticket_id, array('st_comment', 'st_comment_internal'));
						if($query->have_posts()): ?>
						<ul>
							<?php while($query->have_posts()): $query->the_post(); ?>
							<?php
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
						echo FormHelper::create('AdminTicketComment', array(
							'title' => 'Add Response',
						));
						echo FormHelper::hidden('id', array('value' => $ticket_id));
						echo FormHelper::wysiwyg('response', array('label' => 'Message'));
						echo FormHelper::checkbox('note', array('label' => 'Internal Note'));
						echo FormHelper::checkbox('close', array('label' => 'Close ticket on reply'));
						echo FormHelper::end('Send');
						?>

					</div><!-- #comments .comments-area -->
				</footer>


			</article>
			<!-- /Content -->
		<?php endwhile; ?>
		<?php endif; ?>

		</div><!-- /#post-body-content -->

		<div id="postbox-container-1" class="postbox-container">
			<div id="postimagediv" class="postbox ">
				<h3 class="hndle"><span>Ticket Info</span></h3>
				<div class="inside">
					<?php
					switch($priority){
						case 10:
						$priority = 'high';
						break;
						case 5:
						$priority = 'medium';
						break;
						case 0:
						$priority = 'low';
						break;
					}
					?>
					<p><strong>Priority:</strong> <?php echo $priority; ?> - <a href="#edit-department" onclick="if(document.getElementById('edit-priority').style.display == 'block'){show = 'none';}else{ show='block';}document.getElementById('edit-priority').style.display=show; return false;">Edit</a></p>
					<div id="edit-priority" style="display:none;">
					<?php 
					echo FormHelper::create('TicketPriority');
					echo FormHelper::hidden('id', array('value' => $ticket_id));
					echo FormHelper::select('priority', array('options' => array('' => 'Choose Priority', 0 => 'low', 5 => 'Medium', 10 => 'High'), 'label' => false));
					echo FormHelper::end('Update');
					?>
					</div>
					<p><strong>Department:</strong> <?php echo $department; ?> - <a href="#edit-department" onclick="if(document.getElementById('edit-department').style.display == 'block'){show = 'none';}else{ show='block';}document.getElementById('edit-department').style.display=show; return false;">Edit</a></p>
					<div id="edit-department" style="display:none;">
					<?php 
					$terms = get_terms( 'support_groups', array('hide_empty' => 0 ) );
					$support_groups = array('' => 'Choose Departemnt');
					foreach($terms as $term){
						$support_groups[$term->term_id] = $term->name;
					}
					echo FormHelper::create('DepartmentTransfer');
					echo FormHelper::hidden('id', array('value' => $ticket_id));
					echo FormHelper::select('department', array('options' => $support_groups, 'label' => false));
					echo FormHelper::end('Update');
					?>
					</div>
					<p><strong>Status:</strong> <?php echo $status_word; ?> - <a href="#edit-status" onclick="if(document.getElementById('edit-status').style.display == 'block'){show = 'none';}else{ show='block';}document.getElementById('edit-status').style.display=show; return false;">Edit</a></p>
					<div id="edit-status" style="display:none;">
					<?php 
					echo FormHelper::create('StatusChange');
					echo FormHelper::hidden('id', array('value' => $ticket_id));
					echo FormHelper::select('status', array('options' => array(0=> 'Open', 1 => 'Close'), 'label' => false));
					echo FormHelper::end('Update');
					?>
					</div>
				</div>
			</div>

		</div><!-- /postbox-container-1 -->
		
	</div><!-- /#post-body -->
</div><!-- /#poststuff -->	
</div>