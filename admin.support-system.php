<?php 

class Admin_Support_System{

	private $settings_optgroup = 'wp-support-system';
	
	public function __construct(){
		$this->init_hooks();
	}

	private function init_hooks(){
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
		add_submenu_page('support-tickets', 'Addons', 'Addons', 'add_users', 'support-ticket-addons', array($this, 'admin_addon_page' ));
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
		wp_enqueue_script('support-admin-js', plugins_url( 'support-system/assets/js/admin.js'));
	}

	/**
	 * Inject Stylesheets
	 * @return void
	 */
	function admin_styles()
	{
		wp_enqueue_style( 'support-admin-css', plugins_url( 'support-system/assets/css/admin.css'));
	}

	public function admin_addon_page(){
		include 'views/admin/addons.php';	
	}

	public function admin_settings_page()
	{
		include 'views/admin/settings.php';
	}

	public function section_base(){
		?>
		<!-- <p>General Settings</p> -->
		<?php
	}

	public function section_email(){
		?>
		<p>Enter your imap email settings, to enable email email support tickets.</p>
		<?php
	}

	/**
     * Register Plugin Settings
     * @return void
     */
    public function register_settings()
    {
        // register_setting($option_group, $option_name, $sanitize_callback = '')
        register_setting($this->settings_optgroup, 'email_imap_host');
        register_setting($this->settings_optgroup, 'email_imap_port');
        register_setting($this->settings_optgroup, 'email_username');
        register_setting($this->settings_optgroup, 'email_password');

        // add_settings_section($id, $title, $callback, $page)
        add_settings_section('base_section', 'General Settings', array($this, 'section_base'), 'base_settings');
        add_settings_section('email_section', 'Email Support Settings', array($this, 'section_email'), 'email_settings');


        // add_settings_field($id, $title, $callback, $page, $section = 'default', $args = array
        add_settings_field('imap_host', 'IMAP Host', array($this, 'field_callback'), 'email_settings', 'email_section', array(
            'type' => 'text',
            'field_id' => 'imap_host',
            'section_id' => 'email_section',
            'setting_id' => 'email_imap_host'
        ));
        add_settings_field('imap_port', 'IMAP Port', array($this, 'field_callback'), 'email_settings', 'email_section', array(
            'type' => 'text',
            'field_id' => 'imap_port',
            'section_id' => 'email_section',
            'setting_id' => 'email_imap_port'
        ));
        add_settings_field('imap_username', 'Email Address', array($this, 'field_callback'), 'email_settings', 'email_section', array(
            'type' => 'text',
            'field_id' => 'imap_username',
            'section_id' => 'email_section',
            'setting_id' => 'email_username'
        ));
        add_settings_field('imap_password', 'Password', array($this, 'field_callback'), 'email_settings', 'email_section', array(
            'type' => 'password',
            'field_id' => 'imap_password',
            'section_id' => 'email_section',
            'setting_id' => 'email_password'
        ));
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