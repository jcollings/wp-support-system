<?php
global $wptickets;

$tickets = $wptickets->tickets->get_tickets(array(
	'user_id' => get_current_user_id()
));

if($tickets->have_posts()): ?>

	<div class="tickets wptickets_col_1 first">
		<h2>My Tickets</h2>
		<?php while($tickets->have_posts()){
			$tickets->the_post();
			wt_get_template_part( 'content-ticket' );
		}
		wp_reset_postdata();
		?>
	</div>

<?php endif; ?>