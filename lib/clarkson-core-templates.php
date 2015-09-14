<?php

class Clarkson_Core_Templates {

	protected $templates = array();

	public function render($path, $objects){
		global $wp_query;

		if( is_page_template() && isset( $wp_query->post) && isset( $wp_query->post->ID ) ){
			$path = get_post_meta( $wp_query->post->ID, '_wp_page_template', true );
		}

		if( isset( $wp_query->query_vars['json'] ) )
		{
			if( count($objects) === 1 && isset( $objects[0]) ){
				$objects = $objects[0];
			}
			
			$this->render_json($objects);
		}
		else
		{
			$this->render_twig($path, $objects);
		}

		exit();
	}

	private function render_twig($path, $objects){
		// TWIG ARGS
		$template_dir  = dirname($path);
		$template_file = basename($path);
		
		$debug 		= ( defined('WP_DEBUG') ? WP_DEBUG : false );

		$twig_args 	= array(
			'debug' => $debug
		);

		$twig_args = apply_filters( 'clarkson_twig_args', $twig_args);

		$twig_fs = new Twig_Loader_Filesystem($template_dir);
		$twig 	 = new Twig_Environment($twig_fs, $twig_args);

		$twig->addExtension( new Clarkson_Core_Twig_Extension()    );
		$twig->addExtension( new Twig_Extensions_Extension_I18n()  );
		$twig->addExtension( new Twig_Extensions_Extension_Text()  );
		$twig->addExtension( new Twig_Extensions_Extension_Array() );
		$twig->addExtension( new Twig_Extensions_Extension_Date()  );

		if( $debug){
			$twig->addExtension(new Twig_Extension_Debug());
		}

		echo $twig->render( $template_file, array('objects' => $objects) );
	}

	private function render_json($objects){
		header('Content-Type: application/json');

		echo json_encode($objects, JSON_PRETTY_PRINT);
	}

	public function template_include($template){
		$extension = pathinfo($template, PATHINFO_EXTENSION);
		
		$type = basename($template);
		$type = str_replace(".{$extension}", '', $type);

		// Doublecheck 
		if( isset($this->templates[$type]) ){ 
			$template = $this->templates[$type];
			$extension = 'twig';
		}

		if( $extension === 'twig' ){
			// Get template vars
			global $posts;

			$object_loader = Clarkson_Core_Objects::get_instance();

			if( is_author() ){
				$objects = $object_loader->get_users($posts);
			}else{
				$objects = $object_loader->get_objects($posts);	
			}
			
			// Render it
			$this->render($template, array( 'objects' => $objects ) );
		}

		return $template;
	}

	public function add_template($template){
		$filter = current_filter();
		$type   = str_replace('_template', '', $filter);

		if( isset( $this->templates[$type] ) ){
			return $this->templates[$type];
		}

		return $template;
	}

	public function register_custom_templates($atts){
		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		// Retrieve the cache list. 
		// If it doesn't exist, or it's empty prepare an array
		$theme = wp_get_theme();

		if ( !method_exists($theme, 'get_page_templates') || empty( $theme->get_page_templates() ) ) {
			$templates = array();
		}else{
			$templates = $theme->get_page_templates();
		}

		$page_templates = array();

		foreach($this->templates as $name => $path){
			if( strpos($name,'page') !== false && $name !== 'page'){
				$name = str_replace('page-', '', $name);
				$name = str_replace('-', ' ', $name);
				$name = ucwords($name);
				//var_dump($name, $path);
				$page_templates[$path] = $name;
			}
		}

		// New cache, therefore remove the old one
		wp_cache_delete( $cache_key , 'themes');

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge( $templates, $page_templates );

		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;
	}

	private function add_template_filters(){
		// Get template files
		$theme_dir = get_template_directory();

		$template_paths = array(
			$theme_dir . '/templates'
		);

		apply_filters('clarkson_core_template_paths', $template_paths);

		$templates = array();

		foreach( $template_paths as $template_path ){
			$templates = array_merge($templates, $this->get_templates_from_path($template_path) );
		}

		foreach( $templates as $template ){
			$base = basename($template);
			$base = str_replace( '.twig', '', $base );
			$type = preg_replace( '|[^a-z0-9-]+|', '', $base );

			add_filter("{$type}_template", array($this, 'add_template'));

			$this->templates[$base] = $template;
		}
	}

	private function get_templates_from_path($path){
		$templates = array();

		if( !$path || !is_string($path) || !file_exists($path) )
			return $templates;

		$files = glob("{$path}/*.twig");

		if( empty($files) )
			return $templates;
		

		foreach ($files as $file_path) {
			$templates[] = $file_path;
		}

		return $templates;
	}

	// Singleton
	protected $instance = null;

	public static function get_instance()
	{
		static $instance = null;

		if (null === $instance) {
			$instance = new Clarkson_Core_Templates();
		}
		
		return $instance;
	}

	protected function __construct()
	{
		if( !class_exists('Clarkson_Core_Objects') )
			return;

		$this->add_template_filters();

		add_action('template_include', array($this, 'template_include') );

		add_filter( 'page_attributes_dropdown_pages_args', array( $this, 'register_custom_templates' )  );
		add_filter( 'wp_insert_post_data',  array( $this, 'register_custom_templates' )  );
	}

	private function __clone()
	{
	}
	private function __wakeup()
	{
	}
}
