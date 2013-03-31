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
        $kb_serial  = get_option( 'ext_knowledgebase');
        $email_serial  = get_option( 'ext_email');

        $settings = array(
            'addons' => array(
                'knowledgebase' => $kb_serial['ext_knowledgebase'],
                'email' => $email_serial['ext_email']
            )
        );

        $this->config = $settings;
    }
}