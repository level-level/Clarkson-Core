<?php
/*
 * Plugin Name: Clarkson Core
 * Version: 0.1.0
 * Plugin URI: Comming Soon!
 * Description: The Core of Clarkson, use Twig templating and Objects to render thingies
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
			if(is_string($theme_dir . "/{$dir}") && file_exists($theme_dir . "/{$dir}") ){
				$this->load_php_files_from_path( $theme_dir . "/{$dir}" );
			}
		}

	}

	private function load_php_files_from_path($path){
		$handle = opendir($path);
		while (false !== ($entry = readdir($handle))) {
			$fullPath = $path . "/$entry";
			if(strrchr($entry, '.') == '.php'){
				require_once($fullPath);
			}elseif($entry != '.' && $entry != '..'){
				if(filetype($fullPath) == 'dir'){
					$this->load_php_files_from_path($fullPath);
				}
			}
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

