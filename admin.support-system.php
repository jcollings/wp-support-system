<?php 

class Admin_Support_System{

	private $config = null;
	private $settings_optgroup = null;
	private $settings_sections = array();
	
	public function __construct(){
		$this->config =& Support_System_Singleton::getInstance();

		// set class config
		$this->settings_optgroup = $this->config->settings_api['optgroup'];
		add_action('plugins_loaded', array($this, 'plugins_loaded'));
	}

	public function plugins_loaded(){
		add_action( 'admin_menu', array( $this, 'register_menu_pages' ) );
		add_action( 'admin_menu', array($this, 'add_user_menu_notifications'));
		add_action( 'admin_head', array($this, 'admin_forms'));
		add_action( 'parent_file', array($this, 'support_groups_menu_highlight'));
		add_action( 'admin_print_scripts', array($this, 'admin_scripts' ));
		add_action( 'admin_print_styles', array($this, 'admin_styles' ));
		add_filter('plugin_action_links_support-system/support-system.php', array($this, 'settings_link'));
		add_action( 'admin_init', array($this, 'register_settings' ));
	}

	public function register_menu_pages(){
		add_object_page( 'Support Tickets', 'WP Tickets', 'add_users', 'support-tickets', array($this, 'admin_page'));
		add_submenu_page('support-tickets', 'Support Groups', 'Support Groups', 'add_users', 'edit-tags.php?taxonomy=support_groups');

		// allow addons to hook into the meny creation
		do_action('support_system-menu_output');
		
		add_submenu_page('support-tickets', 'Settings', 'Settings', 'add_users', 'support-ticket-settings', array($this, 'admin_settings_page'));
		
	}

	public function support_groups_menu_highlight($parent_file) {
		global $current_screen;
		$taxonomy = $current_screen->taxonomy;
		if ($taxonomy == 'support_groups')
			$parent_file = 'support-tickets';
		return $parent_file;
	}

	/**
     * Add settings link to plugins table
     * @param  array $args links 
     * @return arrary
     */
    function settings_link($args){
        array_unshift($args, '<a href="admin.php?page=support-ticket-settings">Settings</a>');
        return $args;
    }

	/**
     * Inject Javascript
     * @return void
     */
	function admin_scripts()
	{
		wp_enqueue_script('support-admin-js', support_plugin_url( 'assets/js/admin.js'));
	}

	/**
	 * Inject Stylesheets
	 * @return void
	 */
	function admin_styles()
	{
		wp_enqueue_style( 'support-admin-css', support_plugin_url( 'assets/css/admin.css'));
	}

	public function admin_settings_page()
	{
		global $tabs;
		
		$tabs = array(
		    'base_settings' => array(
		        'title' => 'General Settings'
		    )
		);

		// hook to extends setting tabs
		do_action('support_system-menu_output_action', $tabs);

		// include view file
		include 'views/admin/settings.php';
	}

	/**
	 * General Section Callback
	 * @param  array $args passed arguments
	 * @return void
	 */
	public function section_callback($args = ''){
		echo '<p>'.$this->settings_sections[$args['id']]['section']['description'].'</p>';
	}

	/**
     * Register Plugin Settings
     * @return void
     */
    public function register_settings()
    {
    	$this->load_settings_api();

    	foreach($this->settings_sections as $section => $options){

    		//register settings
    		foreach($options['fields'] as $field){
    			register_setting($this->settings_optgroup, $field['setting_id'], array($this, 'save_setting'));
    		}

    		// register section
    		add_settings_section($section, $options['section']['title'], array($this, 'section_callback'), $options['section']['page']);

    		//register fields
    		foreach($options['fields'] as $field){
    			$args = array(
		            'type' => $field['type'],
		            'field_id' => $field['id'],
		            'section_id' => $field['section'],
		            'setting_id' => $field['setting_id']
		        );

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
     * @param  array
     * @return array
     */
    public function save_setting($args){

    	if(isset($args['support_ticket_edit'])){
    		$this->setup_ticket_roles($args['support_ticket_edit']);
    	}

    }

    /**
     * Clear and setup roles
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

    private function load_settings_api(){
    	$roles = get_editable_roles();
    	$roles_sorted = array();
    	foreach($roles as $key => $role){
    		$roles_sorted[$key] = $role['name'];
    	}

    	$fields = array(
    		array('type' => 'text', 'id' => 'login_url', 'section' => 'base_section', 'setting_id' => 'support_login_url', 'label' => 'Login Url'),
    		array('type' => 'text', 'id' => 'register_url', 'section' => 'base_section', 'setting_id' => 'support_register_url', 'label' => 'Register Url'),
    		array('type' => 'select', 'id' => 'register_role', 'section' => 'base_section', 'setting_id' => 'support_register_role', 'label' => 'Register Role', 'choices' => $roles_sorted),
    		array('type' => 'select', 'id' => 'support_ticket_edit', 'section' => 'base_section', 'setting_id' => 'support_ticket_add', 'multiple' => true, 'label' => 'Access Roles', 'choices' => $roles_sorted),
    	);

    	$sections = array(
    		'base_section' => array(
    			'section' => array('page' => 'base_settings', 'title' => 'General Settings', 'description' => 'General Settings Description'),
    			'fields' => $fields
    		)
    	);



    	$sections = array_merge($sections, apply_filters( 'support_system-settings_sections', $sections));
    	$this->settings_sections = $sections;
    }

    /**
     * Generate the output for all settings fields
     * @param  array $args options for each field
     * @return void
     */
    public function field_callback($args)
    {
        $multiple = false;
        extract($args);
        $options = get_option($setting_id);
        switch($args['type'])
        {
            case 'text':
            {
                $value = isset($options[$field_id]) ? $options[$field_id] : '';
                ?>
                <input class='text' type='text' id='<?php echo $setting_id; ?>' name='<?php echo $setting_id; ?>[<?php echo $field_id; ?>]' value='<?php echo $value; ?>' />
                <?php
                break;
            }
            case 'select':
            {
                    ?>
                    <select id="<?php echo $setting_id; ?>" name="<?php echo $setting_id; ?>[<?php echo $field_id; ?>][]" <?php if($multiple === true): ?>multiple<?php endif; ?>>
                    <?php
                    foreach($choices as $id => $name):?>
                        <?php if(isset($options[$field_id]) && is_array($options[$field_id]) && in_array($id,$options[$field_id])): ?>
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
                $value = isset($options[$field_id]) ? $options[$field_id] : '';
                ?>
                <input class='text' type='password' id='<?php echo $setting_id; ?>' name='<?php echo $setting_id; ?>[<?php echo $field_id; ?>]' value='<?php echo $value; ?>' />
                <?php
                break;
            }
        }
    }

	private function insert_support_comment($id, $message, $author_id){
		$time = current_time('mysql');

		return wp_insert_post(array(
			'post_parent' => $id,
			'post_content' => $message,
			'post_type' => 'st_comment',
			'post_date' => $time,
			'post_author' => $author_id,
			'post_status' => 'publish'
		));
	}

	public function add_user_menu_notifications() {
		global $menu;

		$open_tickets = new WP_Query(array(
			'post_type' => 'SupportMessage',
			'meta_query' => array(
				array(
					'key' => '_answered',
					'value' => 0,
					'compare' => '=',
					'type' => 'INT'
				)
			),
			'order'		=> 'DESC',
			'orderby'	=> 'meta_value_num',
			'meta_key' 	=> '_importance',
			'posts_per_page' => -1
		));
		$count = $open_tickets->post_count;
		if($count > 0){
			foreach($menu as $key => $item){
				if($item[2] == 'support-tickets'){
					$menu[$key][0] .= "<span class='update-plugins count-1'><span class='plugin-count'>".$count."</span></span>";
				}
			}
		}
	}

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
			case 'close':
			{
				update_post_meta($_GET['id'], '_answered', 1);
			}
			case 'index':
			default:
			{
				include 'views/admin/index.php';
				break;
			}
		}
	}

	public function admin_forms()
	{
		// not correct page
		if(!isset($_GET['page']) || $_GET['page'] != 'support-tickets')
			return;

		// no published form content
		if(!isset($_POST['SupportFormType']))
			return;

		global $current_user;
		$current_user = wp_get_current_user();

		switch($_POST['SupportFormType'])
		{
			case 'SubmitComment':
			{
				$ticketId = $_POST['TicketId'];
				$message = $_POST['SupportResponse'];
				$author_id = $current_user->ID;

				if(empty($message))
				{
					set_transient('LoginError_'.$author_id, 'Please enter a message.', 60);
					return;
				}

				insert_support_comment($ticketId, $message, $author_id);
				break;
			}
			default:
			{
				break;
			}
		}
	}

}

$Admin_Support_System = new Admin_Support_System();
?>