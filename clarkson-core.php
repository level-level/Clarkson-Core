<?php
/**
 * Plugin Name:  Clarkson Core
 * Version:      0.2.0
 * Plugin URI:   http://wp-clarkson.com/core
 * Description:  A mu-plugin to write Object-Oriented code in combination with the Twig templating engine while keeping the WordPress Way of working in mind.
 * Author:       Level Level
 * Author URI:   https://www.level-level.com
 * License:      GPL v2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Text Domain: clarkson-core
 * Domain Path: /lang/
 *
 * @package CLARKSON\Main
 * @author Level Level
 */

/**
 * Class Clarkson_Core.
 */
class Clarkson_Core {

	/**
	 * Init.
	 */
	public function init() {
		// Deprecated functions and filters.
		if ( class_exists( 'Clarkson_Core_Deprecated' ) ) {
			Clarkson_Core_Deprecated::get_instance();
		}

		// Load post objects.
		if ( class_exists( 'Clarkson_Core_Objects' ) ) {
			Clarkson_Core_Objects::get_instance();
		}

		// Load template routing.
		if ( class_exists( 'Clarkson_Core_Templates' ) ) {
			Clarkson_Core_Templates::get_instance();
		}

	}

	/**
	 * Define instance.
	 *
	 * @var Clarkson_Core
	 */
	protected $instance = null;

	/**
	 * Setting up the class instance.
	 *
	 * @return Clarkson_Core|null
	 */
	public static function get_instance() {
		static $instance = null;

		if ( null === $instance ) {
			$instance = new Clarkson_Core();
		}

		return $instance;
	}

	/**
	 * Clarkson_Core constructor.
	 */
	protected function __construct() {
		// Load vendor files.
		$autoload_file = __DIR__ . '/vendor/autoload.php';

		if ( file_exists( $autoload_file ) ) {
			require_once $autoload_file;
		}

		add_action( 'init', array( $this, 'init' ) );

		if ( ! class_exists( 'Clarkson_Core_Autoloader' ) ) {
			return;
		}

		$this->autoloader = new Clarkson_Core_Autoloader();

		if ( apply_filters( 'clarkson_core_autoload_theme_pre_020', false ) ) {
			// Autoload theme files the way CC ^0.1.0 did (triggers deprecated warning).
			$deprecated = Clarkson_Core_Deprecated::get_instance();
			$deprecated->auto_load_theme();
		}

	}

	/**
	 * Clone.
	 */
	private function __clone() {
	}

	/**
	 * Wakeup.
	 */
	private function __wakeup() {
	}

}

add_action( 'plugins_loaded', array( 'Clarkson_Core', 'get_instance' ) );
