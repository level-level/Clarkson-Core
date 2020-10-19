<?php
/**
 * Handle renamed filters.
 *
 * Based on Mikey Jolley's example in WooCommerce.
 * https://mikejolley.com/2013/12/15/deprecating-plugin-functions-hooks-woocommmerce/.
 * and
 * https://github.com/woocommerce/woocommerce/blob/master/includes/wc-deprecated-functions.php.
 *
 * @package CLARKSON\Lib
 * @since 0.1.6
 */

/**
 * Class Clarkson_Core_Deprecated.
 * @internal
 */
class Clarkson_Core_Deprecated {

	/**
	 * Map Deprecated filters.
	 *
	 * @var array $map_deprecated_filters Map Deprecated filters.
	 */
	protected $map_deprecated_filters;

	/**
	 * Clarkson_Core_Deprecated constructor.
	 */
	public function __construct() {
		$this->set_map_deprecated_filters();
		foreach ( $this->map_deprecated_filters as $new => $old ) {
			add_filter( $new, array( $this, 'deprecated_filter_mapping' ) );
		}
	}

	/**
	 * Get theme objects.
	 *
	 * @return array
	 */
	public function get_theme_objects() {
		$objects       = array();
		$theme_objects = array();

		// Load deprecated post-objects folder.
		$theme_deprecated_objects_path = get_template_directory() . '/post-objects';
		if ( is_dir( $theme_deprecated_objects_path ) ) {
			_doing_it_wrong( __METHOD__, 'The ' . esc_html( $theme_deprecated_objects_path ) . " folder is deprecated. Please use 'wordpress-objects'.", '0.2.0' );
			$theme_objects = array_merge( $this->get_objects_from_path( $theme_deprecated_objects_path ), $theme_objects );
		}

		/**
		 * @hook clarkson_available_objects_paths
		 * @deprecated This method of loading objects is no longer used, unless the clarkson_core_autoload_theme_pre_020 filter is enabled.
		 * @since 0.1.0
		 * @param {string[]} $theme_objects Objects available in Clarkson Core to be loaded.
		 * @return {string[]} Files that should be included to make the object available to Clarkson_Core_Objects.
		 */
		$theme_objects = apply_filters( 'clarkson_available_objects_paths', $theme_objects );

		// Load classes.
		foreach ( $theme_objects as $object_name => $object_path ) {
			if ( strpos( $object_name, '_tax_' ) !== false ) {
				$object_name = strtolower( $object_name );
			}

			if ( in_array( $object_name, $objects, true ) ) {
				continue;
			}

			include_once $object_path;
			$objects[] = $object_name;
		}
		return $objects;
	}

	/**
	 * Get map deprecated filters.
	 *
	 * @return array Deprecated filters.
	 * @internal
	 */
	public function get_map_deprecated_filters() {
		return array();
	}

	/**
	 * Set map deprecated filters.
	 * @internal
	 */
	public function set_map_deprecated_filters() {
		$this->map_deprecated_filters = $this->get_map_deprecated_filters();
	}

	/**
	 * Deprecated filter mapping.
	 *
	 * @param object $data  Deprecated_filters.
	 * @param string $arg_1 First argument (not used).
	 * @param string $arg_2 Second argument (not used).
	 * @param string $arg_3 Third argument (not used).
	 *
	 * @return object
	 *
	 * @internal
	 */
	public function deprecated_filter_mapping( $data, $arg_1 = '', $arg_2 = '', $arg_3 = '' ) {
		$map_deprecated_filters = $this->get_map_deprecated_filters();

		$filter = current_filter();

		if ( isset( $map_deprecated_filters[ $filter ] ) ) {
			if ( has_filter( $map_deprecated_filters[ $filter ] ) ) {
				$data = apply_filters( $map_deprecated_filters[ $filter ], $data, $arg_1, $arg_2, $arg_3 );
				if ( ! defined( 'DOING_AJAX' ) ) {
					$dep_function = 'The ' . $map_deprecated_filters[ $filter ] . ' filter';
					_deprecated_function( esc_html( $dep_function ), '', esc_html( $filter ) );
				}
			}
		}

		return $data;
	}

	/**
	 * Get objects from path.
	 *
	 * @param string $path File path.
	 *
	 * @return array       Objects.
	 */
	private function get_objects_from_path( $path ) {
		$objects = array();

		if ( ! file_exists( $path ) ) {
			return $objects;
		}

		$files = glob( "{$path}/*.php" );
		if ( empty( $files ) ) {
			return $objects;
		}

		foreach ( $files as $filepath ) {
			$path_parts = pathinfo( $filepath );
			$class_name = $path_parts['filename'];
			$class_name = ucfirst( $class_name );

			$objects[ $class_name ] = $filepath;
		}

		return $objects;
	}

	/**
	 * Singleton.
	 *
	 * @var \Clarkson_Core_Deprecated $instance Clarkson_Core_Deprecated.
	 */
	protected $instance;

	/**
	 * Get Instance.
	 *
	 * @return Clarkson_Core_Deprecated
	 */
	public static function get_instance() {
		static $instance = null;

		if ( null === $instance ) {
			$instance = new Clarkson_Core_Deprecated();
		}

		return $instance;
	}
}
