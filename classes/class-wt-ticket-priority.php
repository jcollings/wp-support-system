<?php
class WT_TicketPriority{

	public $priority = array(
		1 => 'Low',
		50 => 'Medium',
		100 => 'High'
	);

	public $default_priority = 50;

	public function __construct(){

		add_action( 'wt/after_ticket_create', array($this, 'after_ticket_create'), 10, 1);
		add_filter( 'wt/list_ticket_priorities', array($this, 'list_ticket_priorities'), 1);
		add_filter( 'wt/set_default_ticket_priority', array($this, 'set_default_ticket_priority'), 1);

		// settings
		add_filter( 'wt/settings_sections', array($this, 'add_settings_sections'),10,1);
	}

	public function after_ticket_create($ticket_id){

		$config = get_option('support_system_config');
		$priority = $this->default_priority;
		if(isset($config['ticket_default_priority'])){
			$priority = $config['ticket_default_priority'];
		}

		add_post_meta( $ticket_id, '_priority', $priority);
	}

	public function list_ticket_priorities($priorities = array()){
		return $this->priority;
	}

	public function set_default_ticket_priority($priorities = array()){

		$config = get_option('support_system_config');

		if(isset($config['ticket_default_priority'])){
			return $config['ticket_default_priority'];
		}

		return $this->default_priority;
	}

	public function add_settings_sections($sections){

		$sections['ticket_section']['fields'][] = array('type' => 'select', 'id' => 'ticket_default_priority', 'section' => 'ticket_section', 'setting_id' => 'support_system_config', 'label' => 'Ticket Default Priority', 'choices' => wt_list_ticket_priorities(), 'value' => '');
		return $sections;
	}
}

new WT_TicketPriority();