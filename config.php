<?php 
/**
 * Singleton Config Class
 */
class Support_System_Singleton
{
    protected static $instance = null;

    protected function __construct(){}
    protected function __clone(){}

    public $settings_api = array(
        'optgroup' => 'wp-support-system',
        'tabs' => array(),
        'sections' => array(),
        'fields' => array()
    );

    public $require_account = 1; // 0 = public ||  1 = members
    public $config = array();

    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static;
            static::$instance->load_settings();
        }
        return static::$instance;
    }

    private function load_settings(){
        $serials = get_option('serials');
        $notifications['user'] = get_option('notification_user');
        $notifications['admin'] = get_option('notification_admin');
        $config = get_option('support_system_config');

        if(!empty($config))
            $this->require_account = $config['require_account'];
        
        $settings = array(
            'addons' => array(
                'knowledgebase' => $serials['ext_knowledgebase'],
                'email' => $serials['ext_email']
            ),
            'notifications' => $notifications
        );

        $this->config = $settings;
    }
}