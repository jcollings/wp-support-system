<?php
/**
 * Manage assigning user roles and capabilities
 */
class WT_Admin_UserRoles{

	private $cleared = false;

	public function __construct(){
		add_filter( 'wt/settings_sections', array($this, 'add_settings_sections'),10,1);
		add_action('wt/output_settings_tabs', array($this, 'add_settings_tab'));
		add_filter( 'wt/settings_save', array($this, 'on_settings_save'));
	}

	public function add_settings_tab(){
		global $tabs;
		$tabs['user_ticket_roles'] = array(
			'title' => 'User Access'
		);
	}

	public function add_settings_sections($sections){

		global $wp_roles;
	    $all_roles = $wp_roles->roles;
	    $roles = array();
	    foreach($all_roles as $id => $role){

	    	// make sure administrator doesnt appear in list
	    	if($id == "administrator")
	    		continue;

	    	$roles[$id] = $role['name'];
	    }

		$fields = array(
    		array('type' => 'select', 'id' => 'admin_group', 'section' => 'admin_priv_section', 'setting_id' => 'admin_priv', 'label' => 'Groups', 'choices' => $roles, 'multiple' => true),
    	);
    	$sections['admin_priv_section'] = array(
			'section' => array('page' => 'user_ticket_roles', 'title' => 'Ticket Manage Privileges', 'description' => 'Setup extra groups and users who have ticket management privileges'),
			'fields' => $fields
		);
		$sections['member_priv_section'] = array(
			'section' => array('page' => 'user_ticket_roles', 'title' => 'Ticket Publish Privileges', 'description' => 'Setup which groups and users who can create tickets'),
			'fields' => array(
				array('type' => 'select', 'id' => 'member_group', 'section' => 'member_priv_section', 'setting_id' => 'publish_priv', 'label' => 'Groups', 'choices' => $roles, 'multiple' => true)
			)
		);

		return $sections;
	}

	public function on_settings_save($args){

		// clear priv's on first save
		if(!$this->cleared){
			$this->cleared = true;
			$this->clear_roles();
		}

		// add capabilities to selected admin groups
		if(isset($args['admin_group_check'])){

			// clear when empty
			$groups = isset($args['admin_group']) ? $args['admin_group'] : array();
			$this->update_admin_roles($groups);
		}

		// add capabilities to selected publish groups
		if(isset($args['member_group_check'])){

			// clear when empty
			$groups = isset($args['member_group']) ? $args['member_group'] : array();
			$this->update_member_roles($groups);
		}

		return $args;
	}

	private function clear_roles(){

		global $wp_roles;
	    $all_roles = $wp_roles->roles;
	    foreach($all_roles as $id => $r){

	    	// make sure administrator doesnt appear in list
	    	if($id == "administrator")
	    		continue;

	    	$role = get_role($id);
			$caps = array(
				'delete_ticket',
				'delete_tickets',
				'edit_others_tickets',
				'edit_published_tickets',
				'edit_ticket',
				'edit_tickets',
				'publish_tickets',
				'read_private_tickets',
				'read_ticket',
				'read_tickets',
				'manage_support_tickets',
			);

	    	foreach($caps as $cap){
				$role->remove_cap($cap);
			}
	    }

	}

	private function update_member_roles($role_list){

		global $wp_roles;
	    $all_roles = $wp_roles->roles;
	    foreach($all_roles as $id => $r){

	    	// make sure administrator doesnt appear in list
	    	if($id == "administrator")
	    		continue;

	    	$role = get_role($id);
			$caps = array(
				'edit_ticket',
				'read_ticket',
				'delete_ticket',
				'edit_tickets',
				'publish_tickets',
				'edit_published_tickets'
			);

	    	if(in_array($id, $role_list)){
	    		
	    		foreach($caps as $cap){
					$role->add_cap($cap);
				}
	    	}
	    }
	}

	private function update_admin_roles($role_list){

		global $wp_roles;
	    $all_roles = $wp_roles->roles;
	    foreach($all_roles as $id => $r){

	    	// make sure administrator doesnt appear in list
	    	if($id == "administrator")
	    		continue;

	    	$role = get_role($id);
			$caps = array(
				'edit_ticket',
				'read_ticket',
				'delete_ticket',
				'edit_others_tickets',
				'publish_tickets',
				'read_private_tickets',
				'edit_tickets',
				'manage_support_tickets'
			);

	    	if(in_array($id, $role_list)){
	    		
	    		foreach($caps as $cap){
					$role->add_cap($cap);
				}
	    	}
	    }
	}
}

new WT_Admin_UserRoles();