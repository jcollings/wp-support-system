<?php
class WT_Admin_ActionBox{

	public function __construct(){

		// add_action('wp_loaded', array($this, 'process_action_box'));
		add_action('wt_admin_action_box', array($this, 'show_action_box'), 10);

		add_action('wt/process_admin_ticket_actions', array($this, 'process_action_box'));
	}

	public function process_action_box(){

		global $wptickets;

		$ticket_id = isset($_POST['ticket_id']) && intval($_POST['ticket_id']) ? $_POST['ticket_id'] : false;

		if(!$ticket_id)
			return;

		$author_id = get_current_user_id();

		$log = array();
		
		// update ticket department
		if(isset($_POST['update-ticket-department']) && !empty($_POST['update-ticket-department'])){

			$department = $_POST['update-ticket-department'];
			$department_list = wt_list_ticket_departments();
			$found = false;

			if($department_list){

				$department_label = '';

				// check to see if department exists
				foreach($department_list as $d){
					if($department == $d->slug){
						$department_label = $d->name;
						$found = true;
						break;
					}
				}

				// valid department
				if($found){
					wp_set_object_terms( $ticket_id, $department, 'department');

					// add to internal note message
					$log[] = "Department switched to ". $department_label;
				}
			}
		}

		// update ticket status
		if(isset($_POST['update-ticket-status']) && !empty($_POST['update-ticket-status'])){

			$ticket_status = $_POST['update-ticket-status'];
			$status_list = wt_list_ticket_status();
			$found = false;

			if($status_list){

				$status_label = '';

				// check to see if status exists
				foreach($status_list as $s){
					if($ticket_status == $s->slug){
						$status_label = $s->name;
						$found = true;
						break;
					}
				}

				// valid ticket status
				if($found){
					wp_set_object_terms( $ticket_id, $ticket_status, 'status');

					// add to internal note message
					$log[] = "Status switched to ". $status_label;
				}
			}
		}

		// update priority
		if(isset($_POST['update-ticket-priority']) && $_POST['update-ticket-priority']  != ''){

			$priority = intval($_POST['update-ticket-priority']);
			$priority_list = wt_list_ticket_priorities();

			// valid priority
			if(array_key_exists($priority, $priority_list)){

				// update ticket priority
				$old_meta = get_post_meta( $ticket_id, '_priority', true );
				if($old_meta){
					update_post_meta( $ticket_id, '_priority', $priority, $old_meta );
				}else{
					add_post_meta( $ticket_id, '_priority', $priority);
				}

				// add to internal note message
				$log[] = "Status switched to ". $priority_list[$priority];
			}
		}

		// concatinate all logs to one internal note
		if(!empty($log)){
			$wptickets->tickets->insert_internal_note( $ticket_id, implode(', ', $log) , $author_id );
		}
	}

	public function show_action_box(){
		?>
		<div class="misc-pub-section">
			Department: <br />
			<select name="update-ticket-department"><option value="">Choose One</option>
			<?php
			$departments = wt_list_ticket_departments();
			foreach($departments as $dept): ?>
			<option value="<?php echo $dept->slug; ?>"><?php echo $dept->name; ?></option>
			<?php endforeach; ?>
			</select>
		</div>
		<div class="misc-pub-section">
			Ticket Status: <br />
			<select name="update-ticket-status"><option value="">Choose One</option>
			<?php
			$ticket_status = wt_list_ticket_status();
			foreach($ticket_status as $status): ?>
			<option value="<?php echo $status->slug; ?>"><?php echo $status->name; ?></option>
			<?php endforeach; ?>
			</select>
		</div>
		<div class="misc-pub-section">
			Priority: <br />
			<select name="update-ticket-priority"><option value="">Choose One</option>
			<?php
			$priorities = wt_list_ticket_priorities();
			foreach($priorities as $key => $value): ?>
			<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
			<?php endforeach; ?>
			</select>
		</div>
		<?php
	}

}

new WT_Admin_ActionBox();