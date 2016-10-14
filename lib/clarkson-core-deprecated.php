<?php

/**
 * Handle renamed filters.
 *
 * Based on Mikey Jolley's example in WooCommerce
 * https://mikejolley.com/2013/12/15/deprecating-plugin-functions-hooks-woocommmerce/
 * and
 * https://github.com/woocommerce/woocommerce/blob/master/includes/wc-deprecated-functions.php
 *
 * @since 0.1.6
 */

class Clarkson_Core_Deprecated {
    var $map_deprecated_filters;

    public function __construct(){
        $this->set_map_deprecated_filters();
        foreach ( $this->map_deprecated_filters as $new => $old ) {
            add_filter( $new, array( $this, 'deprecated_filter_mapping' ) );
        }
    }

    public function get_map_deprecated_filters(){
        return array(
            'clarkson_twig_functions' => 'yalla_twig_functions'
        );
    }

    public function set_map_deprecated_filters(){
        $this->map_deprecated_filters = $this->get_map_deprecated_filters();
    }

    public function deprecated_filter_mapping( $data, $arg_1 = '', $arg_2 = '', $arg_3 = '' ) {
        $map_deprecated_filters = $this->get_map_deprecated_filters();

        $filter = current_filter();

        if ( isset( $map_deprecated_filters[ $filter ] ) ) {
            if ( has_filter( $map_deprecated_filters[ $filter ] ) ) {
                $data = apply_filters( $map_deprecated_filters[ $filter ], $data, $arg_1, $arg_2, $arg_3 );
                if ( ! defined( 'DOING_AJAX' ) ) {
                    _deprecated_function( 'The ' . $map_deprecated_filters[ $filter ] . ' filter', '', $filter );
                }
            }
        }

        return $data;
    }

    // Singleton
    protected $instance = null;

    public static function get_instance()
    {
        static $instance = null;

        if (null === $instance) {
            $instance = new Clarkson_Core_Deprecated();
        }

        return $instance;
    }
}


