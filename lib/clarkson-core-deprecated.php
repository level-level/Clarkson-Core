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
            $instance = new Clarkson_Core_Deprecated();
        }

        return $instance;
    }
}


