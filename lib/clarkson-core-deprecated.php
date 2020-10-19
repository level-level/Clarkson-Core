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
