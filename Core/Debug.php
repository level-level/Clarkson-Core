<?php

namespace YallaYalla;

class Debug{
	protected $instance = null;

	public static function get_instance()
		{
		static $instance = null;
		if (null === $instance) {
			$instance = new Debug();
		}

		return $instance;
	}

	protected function __construct(){
		add_filter( 'debug_bar_panels', array($this, 'add_debug_panel') );
	}

	public function add_debug_panel($panels){
		include_once(__DIR__ . '/Debug/YallaDebugPanel.php');
		
		$panel = new \YallaDebugPanel;
		$panels[] = $panel;
		return $panels;
	}
}