<?php
namespace YallaYalla;

use YallaYalla\Utils\Helper;
use YallaYalla\Loader;
use YallaYalla\Debug;
use YallaYalla\Template;
use WP_Filesystem;

/**
 *	The Base Class
 * 
 */
class Base
{
	protected $instance = null;

	public static function get_instance()
		{
		static $instance = null;
		if (null === $instance) {
			$instance = new Base();
		}

		return $instance;
	}

	// Autoload optional files
	private function autoload(){
		$helper = Helper::get_instance();

		$dirs = array(
			'functions',
			'post-types',
			'taxonomies'
		);

		$dirs = apply_filters( 'yalla_autoload_dirs', $dirs);

		$theme_path = get_template_directory();

		foreach($dirs as $dir){
			$helper->autoload_path( "{$theme_path}/{$dir}" );
		}
	}

	public function setup_theme(){
		$helper = Helper::get_instance();
		$config = $helper->get_manifest();

		$config->config->browsersyncUrl = get_bloginfo('url');
		
		$helper->set_manifest($config);	
	}

	/**
	* Protected constructor to prevent creating a new instance of the
	* *Singleton* via the `new` operator from outside of this class.
	*/
	protected function __construct()
	{
		Loader::get_instance();   // Register Objects
		Template::get_instance(); // Register Templates
		Debug::get_instance();    // Register Debug Bar
		
		$this->autoload();

		add_action("after_switch_theme", array($this,"setup_theme") );
	}

	/**
	* Private clone method to prevent cloning of the instance of the
	* *Singleton* instance.
	*
	* @return void
	*/
	private function __clone()
	{
	}

	/**
	* Private unserialize method to prevent unserializing of the *Singleton*
	* instance.
	*
	* @return void
	*/
	private function __wakeup()
	{
	}

}