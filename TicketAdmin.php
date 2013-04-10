<?php 
/**
 * Ticket Admin
 * 
 * Handles all administration functions
 * 
 * @author James Collings <james@jclabs.co.uk>
 * @package Support System
 * @since 0.0.2
 */
class TicketAdmin{

	private $config = null;
	private $settings_optgroup = 'wp-support-system';
	private $settings_sections = array();
	
	/**
	 * Setup Hooks
	 * 
	 * @return void
	 */
	public function __construct(&$config){
		$this->config = $config;

		if(is_admin()){
			add_action( 'init', array( $this, 'setup_forms'));
			add_action('wp_loaded', array($this, 'process_forms'));
		}
		add_action( 'admin_menu', array( $this, 'register_menu_pages' ) );
		add_action( 'admin_menu', array($this, 'add_user_menu_notifications'));
		add_action( 'admin_print_scripts', array($this, 'admin_scripts' ));
		add_action( 'admin_print_styles', array($this, 'admin_styles' ));
		add_filter( 'plugin_action_links_support-system/support-system.php', array($this, 'settings_link'));
		add_action( 'admin_init', array($this, 'register_settings' ));
		add_action( 'wp_dashboard_setup', array($this, 'add_dashboard_widget' ));
	}

	/**
	 * Setup Form Validation Rules
	 * 
	 * @return void
	 */
	function setup_forms(){
		$forms = array(
			'AdminTicketComment' => array(
				'validation' => array(
					'response' => array(
						'rule' => array('required'),
						'message' => 'This Field is required'
					)
				)
			)
		);
		$this->config->forms = $forms;

	}

	/**
	 * Process Submitted Forms
	 * 
	 * Load up the correct function for submitting the form
	 * 
	 * @return void
	 */
	function process_forms(){
		FormHelper::init($this->config->forms);

		if(isset($_POST['ticket_form_action'])){
			
			switch($_POST['ticket_form_action']){
				case 'AdminTicketComment':
					$this->process_response_form();
				break;
				case 'DepartmentTransfer':
					$this->process_department_transfer_form();
				break;
				case 'TicketPriority':
					$this->process_priority_form();
				break;
				case 'StatusChange':
					$this->process_status_form();
				break;
			}

		}
	}

	/**
	 * Process Status Form
	 * 
	 * Ticket view form to change the status of the ticket
	 * 
	 * @return void
	 */
	function process_status_form(){
		FormHelper::process_form('StatusChange');

		if(FormHelper::is_complete()){
			$ticket_id = $_POST['ticket_id'];
			$status = $_POST['ticket_status'];
			update_post_meta( $ticket_id, '_answered', intval($status));
		}
	}

	/**
	 * Process Department Transfer
	 * 
	 * Ticket view form to change ticket department form
	 * 
	 * @return void
	 */
	function process_department_transfer_form(){
		FormHelper::process_form('DepartmentTransfer');

		if(FormHelper::is_complete()){
			$ticket_id = $_POST['ticket_id'];
			$departemnt_id = $_POST['ticket_department'];
			wp_set_post_terms( $ticket_id, $departemnt_id, 'support_groups');
		}
	}

	/**
	 * Process Priority Form
	 * 
	 * Ticket view form to change the tickets priority
	 * 
	 * @return void
	 */
	function process_priority_form(){
		FormHelper::process_form('TicketPriority');

		if(FormHelper::is_complete()){
			$ticket_id = $_POST['ticket_id'];
			$priority = $_POST['ticket_priority'];
			update_post_meta( $ticket_id, '_importance', intval($priority));
		}
	}

	/**
	 * Process Response Form
	 * 
	 * Add new response, internal note to a ticket.
	 * 
	 * @return void
	 */
	private function process_response_form(){
		global $current_user;

		FormHelper::process_form('AdminTicketComment');

		if(FormHelper::is_complete()){

			$ticket_id = $_POST['ticket_id'];
			$message = $_POST['ticket_response'];
			$author_id = $current_user->ID;
			$type = 'response';

			if(isset($_POST['ticket_note']) && $_POST['ticket_note'] == 1){
				$type = 'internal';
			}

			$comment_id = TicketModel::insert_comment($ticket_id, $message, $author_id, $type);
			
			if($type == 'response')
				TicketNotification::new_comment_alert($ticket_id, $comment_id);

			if(isset($_POST['ticket_close']) && $_POST['ticket_close'] == 1){
				TicketModel::close_support_ticket($ticket_id);
			}
		}

	}

	/**
	 * Add new dashboard widget
	 * 
	 * @return void
	 */
	public function add_dashboard_widget(){
		wp_add_dashboard_widget('support_tickets', 'Open Support Tickets', array($this, 'setup_dashboard_widget'));
	}

	/**
	 * Output dashboard widget
	 * 
	 * @return void
	 */
	public function setup_dashboard_widget(){

		$terms = get_terms( 'support_groups', array('hide_empty' => false) ); 
		?>
		<a href="admin.php?page=support-tickets">All</a> <span class="count">(<?php echo TicketModel::count_group_tickets(); ?>)</span>
		<?php
		foreach($terms as $term){ 
			?>
			<p><a href="admin.php?page=support-tickets&group=<?php echo $term->slug; ?>"><?php echo $term->name; ?></a> <span class="count">(<?php echo TicketModel::count_group_tickets($term->slug); ?>)</span></p>
			<?php
		}
		?>
		<a href="admin.php?page=support-tickets">Closed</a> <span class="count">(<?php echo TicketModel::count_group_tickets('',1); ?>)</span>
		<?php
	}

	/**
	 * Register Administration Menu
	 * 
	 * Allow for addonds to hook onto the action and add a menu
	 * @return void
	 */
	public function register_menu_pages(){
		add_object_page( 'Support Tickets', 'Support', 'add_users', 'support-tickets', array($this, 'admin_page'));
		add_submenu_page('support-tickets', 'Departments', 'Departments', 'add_users', 'edit-tags.php?taxonomy=support_groups');

		// allow addons to hook into the meny creation
		do_action('support_system-menu_output');
		add_submenu_page('support-tickets', 'Settings', 'Settings', 'add_users', 'support-ticket-settings', array($this, 'admin_settings_page'));
		
	}

	/**
     * Add settings link
     * 
     * Display settings link to the left of activate and deactivate plugin
     * 
     * @param  array $args links 
     * @return arrary
     */
    function settings_link($args){
        array_unshift($args, '<a href="admin.php?page=support-ticket-settings">Settings</a>');
        return $args;
    }

	/**
     * Inject Javascript
     * 
     * @return void
     */
	function admin_scripts()
	{
		wp_enqueue_script('support-admin-js', $this->config->plugin_url . 'assets/js/admin.js');
	}

	/**
	 * Inject Stylesheets
	 * 
	 * @return void
	 */
	function admin_styles()
	{
		wp_enqueue_style( 'support-admin-css', $this->config->plugin_url . 'assets/css/admin.css');
	}

	/**
	 * Load Admin Settings Page
	 * 
	 * Load view form settings page, allow addons to add new tabs
	 * 
	 * @return void
	 */
	public function admin_settings_page()
	{
		global $tabs;
		
		$tabs = array(
		    'base_settings' => array(
		        'title' => 'General Settings'
		    ),
		    'notification_settings' => array(
		    	'title' => 'Notification Messages'
		    )
		);

		// hook to extends setting tabs
		do_action('support_system-menu_output_action', $tabs);

		// include view file
		include 'views/admin/settings.php';
	}

	/**
	 * General Section Callback
	 * 
	 * @param  array $args passed arguments
	 * @return void
	 */
	public function section_callback($args = ''){
		echo '<p>'.$this->settings_sections[$args['id']]['section']['description'].'</p>';
	}

	/**
     * Register Plugin Settings
     * 
     * @return void
     */
    public function register_settings()
    {
    	$this->load_settings_api();

    	foreach($this->settings_sections as $section => $options){

    		//register settings
    		foreach($options['fields'] as $field){
    			// register_setting($this->settings_optgroup, $field['setting_id'], array($this, 'save_setting'));
    			register_setting($options['section']['page'], $field['setting_id'], array($this, 'save_setting'));
    		}

    		// register section
    		add_settings_section($section, $options['section']['title'], array($this, 'section_callback'), $options['section']['page']);

    		//register fields
    		foreach($options['fields'] as $field){
    			$args = array(
		            'type' => $field['type'],
		            'field_id' => $field['id'],
		            'section_id' => $field['section'],
		            'setting_id' => $field['setting_id'],
		        );

		        if(isset($field['value'])){
		        	$args['value'] = $field['value'];
		        }

		        if(isset($field['multiple'])){
		        	$args['multiple'] = $field['multiple'];
		        }

		        if(isset($field['choices'])){
		        	$args['choices'] = $field['choices'];
		        }

    			add_settings_field($field['id'], $field['label'], array($this, 'field_callback'), $options['section']['page'], $field['section'], $args);
    		}
    	}
    }

    /**
     * Validate Save Settings
     * 
     * @param  array
     * @return array
     */
    public function save_setting($args){

    	if(isset($args['support_ticket_edit'])){
    		$this->setup_ticket_roles($args['support_ticket_edit']);
    	}

    	return $args;
    }

    /**
     * Clear and setup roles
     * 
     * @param  array  $selected_roles list of roles manage support tickets
     * @return void
     */
    private function setup_ticket_roles($selected_roles = array()){

    	// clear all roles for support ticket
    	$roles = get_editable_roles();
    	foreach($roles as $key => $r){
   
    		$role = get_role( $key );
    		if(array_key_exists('manage_support_tickets', $r['capabilities'])){
    			$role->remove_cap( 'manage_support_tickets' );
    		} 		
    	}

    	// add cap to selected roles
    	if(is_array($selected_roles) && !empty($selected_roles)){
    		foreach($selected_roles as $r){
	    		$role = get_role($r);
	    		
	    		$role->add_cap( 'manage_support_tickets' );		
    		}
    	}else{
    		$role = get_role( 'administrator' );
    		$role->add_cap( 'manage_support_tickets' );
    	}
    }

    /**
     * Load Support System Settings
     * 
     * Setup settings to be outputted via wordpress Settings API
     * 
     * @return void
     */
    private function load_settings_api(){

    	$terms = get_terms( 'support_groups', array('hide_empty' => 0) );
    	$support_groups = array('' => 'Select a Term');
    	
    	foreach($terms as $term){
    		$support_groups[$term->term_id] = $term->name; 
    	}

    	$site_pages = get_pages();
    	$pages = array();
    	foreach($site_pages as $page){
    		$pages[$page->ID] = $page->post_title;
    	}
    	

    	$sections = array(
    		'base_section' => array(
    			'section' => array('page' => 'base_settings', 'title' => 'General Settings', 'description' => 'General Settings Description'),
    			'fields' => array(
    				array('type' => 'select', 'id' => 'support_page', 'section' => 'base_section', 'setting_id' => 'support_system_config', 'label' => 'Support System Page', 'choices' => $pages, 'value' => $this->config->support_page),
    				array('type' => 'select', 'id' => 'default_group', 'section' => 'base_section', 'setting_id' => 'support_system_config', 'label' => 'Default Unassigned Group', 'choices' => $support_groups, 'value' => $this->config->default_support_group),
		    		array('type' => 'select', 'id' => 'require_account', 'section' => 'base_section', 'setting_id' => 'support_system_config', 'label' => 'Require Wordpress Account', 'choices' => array('No', 'Yes'), 'value' => $this->config->require_account),
		    		array('type' => 'text', 'id' => 'login', 'section' => 'base_section', 'setting_id' => 'url_redirect', 'label' => 'Login Url'),
		    		array('type' => 'text', 'id' => 'register', 'section' => 'base_section', 'setting_id' => 'url_redirect', 'label' => 'Register Url'),
		    		array('type' => 'text', 'id' => 'email_domain', 'section' => 'base_section', 'setting_id' => 'support_system_config', 'label' => 'Email Domain', 'value' => $this->config->email_domain)
		    	)
    		),
    		'notification_user' => array(
    			'section' => array('page' => 'notification_settings', 'title' => 'User Notification', 'description' => 'Confirmation email sent to user once a ticket has been submitted.'),
    			'fields' => array(
    				array('type' => 'text', 'id' => 'msg_title', 'section' => 'notification_user', 'setting_id' => 'notification_user', 'label' => 'Response Subject', 'value' => $this->config->notifications['user']['msg_title']),
    				array('type' => 'textarea', 'id' => 'msg_body', 'section' => 'notification_user', 'setting_id' => 'notification_user', 'label' => 'Response Message', 'value' => $this->config->notifications['user']['msg_body']),
    			)
    		),
    		'notification_admin' => array(
    			'section' => array('page' => 'notification_settings', 'title' => 'Admin Notification', 'description' => 'Notification email sent to admins once a ticket has been submitted.'),
    			'fields' => array(
    				array('type' => 'text', 'id' => 'msg_title', 'section' => 'notification_admin', 'setting_id' => 'notification_admin', 'label' => 'Response Subject', 'value' => $this->config->notifications['admin']['msg_title']),
    				array('type' => 'textarea', 'id' => 'msg_body', 'section' => 'notification_admin', 'setting_id' => 'notification_admin', 'label' => 'Response Message', 'value' => $this->config->notifications['admin']['msg_body']),
    			)
    		)
    	);

    	$sections = array_merge($sections, apply_filters( 'support_system-settings_sections', $sections));
    	$this->settings_sections = $sections;
    }

    /**
     * Generate the output for all settings fields
     * 
     * @param  array $args options for each field
     * @return void
     */
    public function field_callback($args)
    {
    	$value = '';
        $multiple = false;
        extract($args);
        $options = get_option($setting_id);
        $value = isset($options[$field_id]) ? $options[$field_id] : $value;
        switch($args['type'])
        {
            case 'text':
            {
                ?>
                <input class='text' type='text' id='<?php echo $setting_id; ?>-<?php echo $field_id; ?>' name='<?php echo $setting_id; ?>[<?php echo $field_id; ?>]' value='<?php echo $value; ?>' />
                <?php
                break;
            }
            case 'textarea':
            {
                ?>
                <textarea id='<?php echo $setting_id; ?>-<?php echo $field_id; ?>' name='<?php echo $setting_id; ?>[<?php echo $field_id; ?>]'><?php echo $value; ?></textarea>
                <?php
                break;
            }
            case 'select':
            {
                ?>
                <select id="<?php echo $setting_id; ?>" name="<?php echo $setting_id; ?>[<?php echo $field_id; ?>]<?php if($multiple === true): ?>[]<?php endif; ?>" <?php if($multiple === true): ?>multiple<?php endif; ?>>
                <?php
                foreach($choices as $id => $name):?>
                    <?php if(isset($value) && ((is_array($value) && in_array($id,$value)) || (!is_array($value) && $value == $id))): ?>
                    <option value="<?php echo $id; ?>" selected="selected"><?php echo $name; ?></option>
                    <?php else: ?>
                    <option value="<?php echo $id; ?>"><?php echo $name; ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
                </select>
                <?php
                break;
            }
            case 'upload':
            {
                ?>
                <input class='file' type='file' id='<?php echo $setting_id; ?>' name='<?php echo $setting_id; ?>[<?php echo $field_id; ?>]'  />
                <?php
                break;
            }
            case 'password':
            {
                ?>
                <input class='text' type='password' id='<?php echo $setting_id; ?>' name='<?php echo $setting_id; ?>[<?php echo $field_id; ?>]' value='<?php echo $value; ?>' />
                <?php
                break;
            }
        }
    }

    /**
     * Add Menu Notifications
     * 
     * Add ammount of open tickets to the Support Menu Item
     * 
     * @return void
     */
	public function add_user_menu_notifications() {
		global $menu;

		$open_tickets = TicketModel::get_tickets(array('open' => 0));

		$count = $open_tickets->post_count;
		if($count > 0){
			foreach($menu as $key => $item){
				if($item[2] == 'support-tickets'){
					$menu[$key][0] .= "<span class='update-plugins count-1'><span class='plugin-count'>".$count."</span></span>";
				}
			}
		}
	}

	/**
	 * Display Correct Admin Page
	 * 
	 * @return void
	 */
	public function admin_page()
	{
		$page = isset($_GET['action']) ? $_GET['action'] : 'index';
		switch($page)
		{
			case 'view':
			{
				include 'views/admin/view.php';
				break;
			}
			case 'index':
			default:
			{
				include 'views/admin/index.php';
				break;
			}
		}
	}
}
?>