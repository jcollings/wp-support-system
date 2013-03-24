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

    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }
}