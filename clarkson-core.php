<?php
/**
 * Plugin Name:  Clarkson Core
 * Version:      1.0.0
 * Plugin URI:   http://wp-clarkson.com/core
 * Description:  A mu-plugin to write Object-Oriented code in combination with the Twig templating engine while keeping the WordPress Way of working in mind.
 * Author:       Level Level
 * Author URI:   https://www.level-level.com
 * License:      GPL v2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Text Domain: clarkson-core
 * Domain Path: /lang/
 */

namespace Clarkson_Core;

use Clarkson_Core\Gutenberg\Block_Manager;

/**
 * The main entry point, responsible for registering all objects and hooks.
 */
class Clarkson_Core {

	/**
	 * Container for autoloadable files.
	 *
	 * @var Autoloader
	 */
	public $autoloader;

	/**
	 * Initializes all neccessery objects. Is automatically called on 'init'.
	 *
	 * Should not be called manually. This method is public because it needs to be
	 * called by an action.
	 *
	 * @internal
	 */
	public function init():void {
		// Load post objects.
		Objects::get_instance();

		// Load template routing.
		Templates::get_instance();

		// Load template routing.
		$block_manager = new Block_Manager();
		$block_manager->init();
	}

	/**
	 * Define instance.
	 *
	 * @var null|Clarkson_Core
	 */
	protected static $instance = null;

	/**
	 * Setting up the class instance.
	 */
	public static function get_instance(): Clarkson_Core {
		if ( null === self::$instance ) {
			self::$instance = new Clarkson_Core();
		}

		return self::$instance;
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

		$this->autoloader = new Autoloader();
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