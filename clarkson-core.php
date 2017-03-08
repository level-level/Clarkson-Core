<?php
/*
 * Plugin Name: Clarkson Core
 * Version: 0.1.10
 * Plugin URI: http://wp-clarkson.com/core
 * Description: A plugin to write Object-Oriented code in combination with the Twig templating engine while keeping the WordPress Way of working in mind.
 * Author: Level Level
 * Author URI: http://www.level-level.com
 * Requires at least: 4.0
 * Tested up to: 4.7.2
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

		$this->autoloader = new Clarkson_Core_Autoloader();

		$deprecated = Clarkson_Core_Deprecated::get_instance();
		if( $autoload_theme = apply_filters('clarkson_core_autoload_theme', false ) ){
			// trigger deprecated warning
			// Auto load theme files
			$deprecated->auto_load_theme();
		}

		add_action('init', array($this, 'init') );
	}

	private function __clone()
	{
	}
	private function __wakeup()
	{
	}
}

add_action('plugins_loaded', array('Clarkson_Core', 'get_instance'));
