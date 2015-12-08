<?php
class Clarkson_Core_Templates {

	protected $templates = array();

	public function render($path, $objects){
		global $wp_query;

		if( is_page_template() && isset( $wp_query->post) && isset( $wp_query->post->ID ) ){
			$template_path = get_post_meta( $wp_query->post->ID, '_wp_page_template', true );

			// If this file doesn't exist just fallback on the default WordPress template hierarchy fallback method
			if( file_exists( $this->get_template_dir() . '/' . $template_path ) ){
				$path = $template_path;
			}
		}

		if( isset( $wp_query->query_vars['json'] ) ) {
			if( count($objects) === 1 && isset( $objects[0]) ){
				$objects = $objects[0];
			}
			$this->echo_json($objects);
		}
		else {
			$this->echo_twig($path, $objects);
		}
		exit();
	}


	public function render_twig($path, $objects){
		// TWIG ARGS
		$template_dir  = $this->get_template_dir();
		$template_file = str_replace( $template_dir, '', $path );
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

		$context_args = apply_filters('clarkson_context_args', $objects );

		return $twig->render( $template_file, $context_args );
	}


	public function echo_twig( $template_file, $objects ){
		echo $this->render_twig( $template_file, $objects );
	}


	public function render_json($objects){
		header('Content-Type: application/json');
		return json_encode($objects, JSON_PRETTY_PRINT);
	}


	public function echo_json( $objects ){
		echo $this->render_json( $objects );
	}

	public function get_template_dir(){
		return apply_filters( 'clarkson_twig_template_dir', get_template_directory() . '/templates' );
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
		// Allow twig based on wp_query
		global $wp_query;
		if( isset( $wp_query->twig) && file_exists($wp_query->twig) ){
			return $wp_query->twig;
		}
		// Check filter for current template
		$filter = current_filter();
		$type   = str_replace('_template', '', $filter);

		$post_type = get_post_type();
		$templates = $this->templates;

		if( isset( $templates["{$type}-{$post_type}"] ) ){
			return $templates["{$type}-{$post_type}"];
		}
		if( isset( $templates["{$type}"] ) ){
			return $templates["{$type}"];
		}
		// Fallback if type is page but there is no page template.
		// Offcourse only if there is a singular template
		if( ! isset( $templates["{$type}"], $templates["page"] ) && $templates["singular"] ){
			return $templates["singular"];
		}
		if( isset( $templates['index']) ){
			return $templates['index'];
		}

		return $template;
	}

	public function get_templates( $choices = array() ){

		// Retrieve the cache list.
		// If it doesn't exist, or it's empty prepare an array
		$theme = wp_get_theme();

		if ( !method_exists($theme, 'get_page_templates') || empty( $theme->get_page_templates() ) ) {
			$templates = array();
		} else{
			$templates = $theme->get_page_templates();
		}

		$templates = array_merge( $templates, $choices );
		$page_templates = array();
		foreach($this->templates as $name => $path){
			if( strpos($name,'page') !== false && $name !== 'page'){
				$name = str_replace('page-', '', $name);
				$name = str_replace('-', ' ', $name);
				$name = ucwords($name);

				$page_templates[ basename($path) ] = $name;
			}
		}

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge( $templates, $page_templates );
		return $templates;
	}

	public function register_custom_templates($atts){
		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );
		$templates = $this->get_templates();
		// New cache, therefore remove the old one
		wp_cache_delete( $cache_key , 'themes');
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
		$filters = array();

		foreach( $template_paths as $template_path ){
			$templates = array_merge($templates, $this->get_templates_from_path($template_path) );
		}
		foreach( $templates as $template ){
			$base = basename($template);
			$base = str_replace( '.twig', '', $base );
			$type = preg_replace( '|[^a-z0-9-]+|', '', $base );
			$base_type = preg_replace('(-.*)', '', $type);

			if( !in_array($base_type, $filters)){
				add_filter("{$base_type}_template", array($this, 'add_template'));
				$filters[] = $base_type;
			}


			$this->templates[$base] = $template;
		}
	}

	private function get_templates_from_path($path){
		$templates = array();
		if( !$path || !is_string($path) || !file_exists($path) ) {
			return $templates;
		}
		$files = glob("{$path}/*.twig");
		if( empty($files) ) {
			return $templates;
		}
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
		add_filter( 'acf/location/rule_values/page_template', 	array( $this, 'get_templates' ) );

	}

	private function __clone()
	{
	}

	private function __wakeup()
	{
	}
}
