<?php

namespace YallaYalla;

class Debug{
	protected $instance = null;
	protected $panel = null;

	public static function get_instance()
		{
		static $instance = null;
		if (null === $instance) {
			$instance = new Debug();
		}

		return $instance;
	}

	protected function __construct(){

		if( !class_exists('Debug_Bar') || ! is_super_admin() || ! is_admin_bar_showing() || $this->is_wp_login() )
			return;

		add_filter( 'yalla_template_vars', array($this, 'get_template_vars') );
		add_filter( 'debug_bar_panels', array($this, 'add_debug_panel') );
	}

	public function get_template_vars($vars){
		$this->panel->set_template_vars($vars);
		return $vars;
	}

	public function add_debug_panel($panels){
		include_once( __DIR__ . '/Debug/YallaDebugPanel.php');
		$this->panel = new \YallaDebugPanel;
		
		$panels[] = $this->panel;
		return $panels;
	}
}