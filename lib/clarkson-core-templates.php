<?php
class Clarkson_Core_Templates {

	protected $templates = array();
	protected $hasBeenCalled = false;

	public function render($path, $objects, $ignore_warning = false){
		global $wp_query;

		if( is_page_template() && isset( $wp_query->post) && isset( $wp_query->post->ID ) ){
			$template_path = get_post_meta( $wp_query->post->ID, '_wp_page_template', true );

			// If this file doesn't exist just fallback on the default WordPress template hierarchy fallback method and first checking the child-theme and then the parent theme
			if( file_exists( $this->get_stylesheet_dir() . '/' . $template_path ) ){
				$path = $template_path;
			} elseif ( file_exists( $this->get_template_dir() . '/' . $template_path ) ){
				$path = $template_path;
			}
		}

		if( isset( $wp_query->query_vars['json'] ) ) {
			if( count($objects) === 1 && isset( $objects['objects'][0]) ){
				$objects = reset( $objects['objects'][0] );
			}
			$this->echo_json($objects);
		}
		else {
			$this->echo_twig($path, $objects, $ignore_warning);
		}
		exit();
	}


	public function render_twig($path, $objects, $ignore_warning = false){
		// TWIG ARGS
		if(!$ignore_warning && $this->hasBeenCalled){
			user_error("Template rendering has already been called. If you are trying to render a partial, include the file from the parent template for performance reasons. If you have a specific reason to render multiple times, set ignore_warning to true.", E_USER_NOTICE);
		}
		$this->hasBeenCalled = true;

		$template_dirs  = $this->get_templates_dirs();
		$template_file = str_replace( array( $this->get_template_dir(), $this->get_stylesheet_dir() ), '', $path); // Retreive only the path to the template file, relative from the yourtheme/templates directory

		$debug 		= ( defined('WP_DEBUG') ? WP_DEBUG : false );
		$twig_args 	= array(
			'debug' => $debug
		);

		$twig_args = apply_filters( 'clarkson_twig_args', $twig_args);
		$twig_fs = new Twig_Loader_Filesystem($template_dirs);
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


	public function echo_twig( $template_file, $objects, $ignore_warning = false ){
		echo $this->render_twig( $template_file, $objects, $ignore_warning );
	}

	public function render_json( $posts ){
		header('Content-Type: application/json');

		// If single post then create new array
		if( ! is_array( $posts ) ){
			$objects[] = $posts;
		} else {
			$objects = $posts;
		}

		$cc_objects = Clarkson_Core_Objects::get_instance();
		$objects = $cc_objects->get_objects( $objects );
		return json_encode($objects, JSON_PRETTY_PRINT);;
	}


	public function echo_json( $objects ){
		echo $this->render_json( $objects );
	}

	/**
	 * Get the template directories where the Twig files are located in.
	 *
	 * This takes notices of the child / parent hierarchy, so that's why the child theme gets searched first and then the parent theme, just like the regular WordPress templating hierarchy.
	 */
	public function get_templates_dirs(){
		$template_dirs = array(
			$this->get_stylesheet_dir(),
			$this->get_template_dir(),
		);

		// if no child-theme is used, then these two above are the same
		$template_dirs = array_unique( $template_dirs );
		return apply_filters( 'clarkson_twig_template_dirs', $template_dirs );
	}

	/**
	 * Filter the main or parent theme directory
	 */
	public function get_template_dir(){
		return apply_filters( 'clarkson_twig_template_dir', get_template_directory() . '/templates' );
	}

	/**
	 * Filter the child theme directory
	 */
	public function get_stylesheet_dir(){
		return apply_filters( 'clarkson_twig_stylesheet_dir', get_stylesheet_directory() . '/templates' );
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

			$page_vars = array();

			if( is_author() ){
				$page_vars['user'] = $object_loader->get_users($posts);
			}elseif( is_tax() ){
				$term = get_queried_object();
				// Custom Taxonomy Templates per Taxonomy type
				if( is_a($term, 'WP_Term') ){
					$page_vars['term'] = $object_loader->get_term($term);
				}
			} elseif( is_search() ){
				global $wp_query;
				$objects['found_posts'] = $wp_query->get('filtered_found_posts') ? $wp_query->get('filtered_found_posts') : $wp_query->found_posts;
			}
			$page_vars['objects'] = $object_loader->get_objects($posts);

			// Render it
			$this->render($template, $page_vars );
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
		$term = get_queried_object();

		// Custom Taxonomy Templates per Taxonomy type
		if( is_a($term, 'WP_Term') && isset( $term->taxonomy) ){
			$post_type = $term->taxonomy;
		}

		$templates = $this->templates;

		if( isset( $templates["{$type}-{$post_type}"] ) ){
			return $templates["{$type}-{$post_type}"];
		}
		if( isset( $templates["{$type}"] ) ){
			return $templates["{$type}"];
		}

		/**
		 * Major exception here:
		 * Fallback if $type is 'page' but the custom template file in _template
		 * that isn't present on the disk anymore. Then $type is still 'page'
		 * but it has could fallback on singular.twig if present.
		 * This is default WordPress behaviour... so first after commiting, I'm
		 * going to delete this :)
		 * Offcourse only if there is a singular template
		 */
		if( 'page' == $type && ! isset( $templates["{$type}"] ) && isset( $templates["singular"] ) ){
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

		if ( method_exists($theme, 'get_page_templates') ){
			if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) { // 4.6 and older
				$templates = $theme->get_page_templates();
			} else { // 4.7+
				$templates = array();
			}
			if(empty($templates)){
				$templates = array();
			}
		}else{
			$templates = array();
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

	/**
	 * Adds our templates to the page dropdown for v4.6 and older
	 */
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

	/**
	 * Adds our templates to the page dropdown for v4.7+
	 */
	public function add_new_template( $posts_templates ) {
		$posts_templates = $this->get_templates( $posts_templates );
		return $posts_templates;
	}

	private function add_template_filters(){
		// Get template files
		$template_paths = $this->get_templates_dirs();

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
		add_filter( 'wp_insert_post_data',  array( $this, 'register_custom_templates' )  );
		add_filter( 'acf/location/rule_values/page_template', 	array( $this, 'get_templates' ) );

		// Add a filter to the attributes metabox to inject template into the cache.
		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) { // 4.6 and older
			add_filter( 'page_attributes_dropdown_pages_args', array( $this, 'register_custom_templates' )  );
		} else { // Add a filter to the wp 4.7 version attributes metabox
			add_filter( 'theme_page_templates', array( $this, 'add_new_template' ) );
		}


	}

	private function __clone()
	{
	}

	private function __wakeup()
	{
	}
}
