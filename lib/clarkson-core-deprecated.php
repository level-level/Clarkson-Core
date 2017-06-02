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

    public function get_theme_objects(){
        $objects = array();
        $theme_objects = array();

        // Load deprecated post-objects folder
        $theme_deprecated_objects_path = get_template_directory() . '/post-objects';
        if(is_dir($theme_deprecated_objects_path)){
            user_error("The {$theme_deprecated_objects_path} folder is deprecated. Please use 'wordpress-objects'.", E_USER_DEPRECATED);
            $theme_objects  = array_merge($this->get_objects_from_path( $theme_deprecated_objects_path ), $theme_objects);
        }

        // Theme overwrites plugins objects
        $theme_objects = apply_filters( 'clarkson_available_objects_paths', $theme_objects);

        // Load classes
        foreach( $theme_objects as $object_name=>$object_path){
            if( strpos( $object_name, '_tax_' ) !== false ) {
                $object_name = strtolower( $object_name );
            }

            if( in_array($object_name, $objects) )
            continue;

            include_once($object_path);
            $objects[] = $object_name;
        }
        return $objects;
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

    private function get_objects_from_path( $path ){
        $objects = array();

        if( !file_exists($path) )
            return $objects;

        $files = glob("{$path}/*.php");
        if( empty($files) )
            return $objects;

        foreach ( $files as $filepath){
            $path_parts = pathinfo($filepath);
            $class_name = $path_parts['filename'];
            $class_name = ucfirst($class_name);

            $objects[$class_name] = $filepath;
        }

        return $objects;
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


