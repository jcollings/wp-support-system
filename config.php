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
    public $notifications = array();
    public $serials = array();
    public $config = array();

    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static;
            static::$instance->set_defaults();
            static::$instance->load_settings();
        }
        return static::$instance;
    }

    private function set_defaults(){

        $this->notifications = array(
            'user' => array(
                'msg_title' => 'Support Ticket #{ticket_id} has been sent',
                'msg_body' => 'Hi {name},
We will try and get your problem sorted soon as, but in the mean time have you checked our knowledgebase.
Regards
Theme Dev Team'
            ),
            'admin' => array(
                'msg_title' => '{priority} - Ticket #{ticket_id}',
                'msg_body' => '{name} has raised a new support ticket.
Subject: {subject}
Message: {message}'
            )
        );
    }

    private function load_settings(){

        // check if serials exist
        $serials = get_option('serials');
        if(isset($serials) && !empty($serials)){
            $this->serials = $serials;
        }

        // check if user notifications exist
        $user_notifications = get_option('notification_user');
        if(isset($user_notifications) && !empty($user_notifications)){
            $this->notifications['user'] = $user_notifications;
        }

        // check if admin notification exist
        $admin_notifications = get_option('notification_admin');
        if(isset($admin_notifications) && !empty($admin_notifications)){
            $this->notifications['admin'] = $admin_notifications;
        }

        // check if an account is required to submit a ticket
        $config = get_option('support_system_config');
        if(!empty($config))
            $this->require_account = $config['require_account'];
    }
}