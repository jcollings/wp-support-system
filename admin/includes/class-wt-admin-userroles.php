<?php
/**
 * Manage assigning user roles and capabilities
 */
class WT_Admin_UserRoles{

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
			'section' => array('page' => 'user_ticket_roles', 'title' => 'Ticket Admin Privileges', 'description' => 'Set groups and users who have ticket administrative privileges'),
			'fields' => $fields
		);

		$sections['admin_departments_section'] = array(
			'section' => array('page' => 'user_ticket_roles', 'title' => 'Department Privileges', 'description' => 'Set groups and users who have access to specific departments'),
			'fields' => $fields
		);

		return $sections;
	}

	public function on_settings_save($args){

		// add capabilities to selected admin groups
		if(isset($args['admin_group'])){

			$this->update_admin_roles($args['admin_group']);
		}

		return $args;
	}

	private function update_admin_roles($role_list){

		$cap = 'manage_support_tickets';

		global $wp_roles;
	    $all_roles = $wp_roles->roles;
	    foreach($all_roles as $id => $r){

	    	// make sure administrator doesnt appear in list
	    	if($id == "administrator")
	    		continue;

	    	$role = get_role($id);

	    	if(in_array($id, $role_list)){
	    		$role->add_cap($cap);	
	    	}else{
	    		$role->remove_cap($cap);
	    	}
	    	
	    }
	}
}

new WT_Admin_UserRoles();