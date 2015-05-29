<?php

use YallaYalla\Utils\Helper;

class YallaAssets{
	protected $instance = null;
	protected $version = null;

	protected function __construct(){
		$helper = Helper::get_instance();
		$config = $helper->get_manifest();

		$this->version = $config->version;

		add_action( 'admin_init', 		  array($this, 'editor_style'), 100 );
		add_action( 'wp_enqueue_scripts', array($this, 'enque_assets'), 100 );
	}

	public static function get_instance()
		{
		static $instance = null;
		if (null === $instance) {
			$instance = new YallaAssets();
		}

		return $instance;
	}

	public function editor_style(){
		add_editor_style( get_template_directory_uri() . '/dist/styles/editor-style.css' );
	}

	public function enque_assets(){
		wp_enqueue_style('yalla_main', get_template_directory_uri() . '/dist/styles/main.css', array(), $this->version);
		wp_enqueue_script('yalla_main', get_template_directory_uri() . '/dist/scripts/main.js', array(), $this->version, true);
	}


}

YallaAssets::get_instance();