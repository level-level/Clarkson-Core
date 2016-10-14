<?php
/*
 * Plugin Name: Clarkson Core
 * Version: 0.1.6
 * Plugin URI: http://wp-clarkson.com/core
 * Description: A plugin to write Object-Oriented code in combination with the Twig templating engine while keeping the WordPress Way of working in mind.
 * Author: Level Level
 * Author URI: http://www.level-level.com
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: wordpress-plugin-template
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Level Level
 * @since 0.1.0
 */

class Clarkson_Core {

	public function init(){

		// Load lib
		$this->load_php_files_from_path( __DIR__ . '/lib' );

		// Deprecated functions and filters
		if( class_exists('Clarkson_Core_Deprecated') ){
			Clarkson_Core_Deprecated::get_instance();
		}

		// Load post objects
		if( class_exists('Clarkson_Core_Objects') ){
			Clarkson_Core_Objects::get_instance();
		}

		// Load template routing
		if( class_exists('Clarkson_Core_Templates') ){
			Clarkson_Core_Templates::get_instance();
		}

	}

	public function auto_load_theme(){
		$dirs = array(
			'functions',
			'post-types', // Default location of WP-CLI export
			'taxonomies'  // Default location of WP-CLI export
		);

		$dirs = apply_filters('clarkson_core_autoload_dirs', $dirs);

		// Current Theme Dir
		$theme_dir = get_template_directory();

		foreach($dirs as $dir){
			$this->load_php_files_from_path( $theme_dir . "/{$dir}" );
		}

	}

	private function load_php_files_from_path($path = false){

		if( !$path || !is_string($path) || !file_exists($path) )
			return;

		$files = glob("{$path}/*.php");
		$dirs = array_filter(glob("{$path}/*", GLOB_ONLYDIR), 'is_dir');

		foreach($dirs as $dir){
			$this->load_php_files_from_path($dir);
		}

		if( empty($files) )
			return;

		foreach ( $files as $filepath){
			require_once $filepath;
		}

	}

	// Singleton
	protected $instance = null;

	public static function get_instance()
	{
		static $instance = null;

		if (null === $instance) {
			$instance = new Clarkson_Core();
		}

		return $instance;
	}

	protected function __construct()
	{
		// Load vendor files
		$autoload_file = __DIR__ . '/vendor/autoload.php';

		if( file_exists($autoload_file) ){
			require_once($autoload_file);
		}

		add_action('init', array($this, 'init') );

		// Auto load theme files
		$this->auto_load_theme();

	}

	private function __clone()
	{
	}
	private function __wakeup()
	{
	}
}

add_action('plugins_loaded', array('Clarkson_Core', 'get_instance'));
