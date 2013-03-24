<?php 

class User_Support_System{

	private $config = null;
	
	public function __construct(){
		$this->config =& Support_System_Singleton::getInstance();
		$this->init_hooks();
	}

	private function init_hooks(){
		add_action( 'wp_enqueue_scripts', array($this, 'public_scripts' ));
		add_action( 'wp_enqueue_scripts', array($this, 'public_styles' ));
	}

	/**
     * Inject Javascript
     * @return void
     */
	public function public_scripts()
	{
		wp_enqueue_script('support-public-js', support_plugin_url( 'assets/js/public.js'));
	}

	/**
	 * Inject Stylesheets
	 * @return void
	 */
	public function public_styles()
	{
		wp_enqueue_style( 'support-public-css', support_plugin_url( 'assets/css/public.css'));
	}

}

$User_Support_System = new User_Support_System();
?>