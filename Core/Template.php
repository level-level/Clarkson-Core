<?php
namespace YallaYalla;

use YallaYalla\Loader;
use YallaYalla\Twig\WordpressExtension;

/**
 *	The Base Class
 * 
 */
class Template
{
	protected $instance = null;
	private $template_dir;

	public static function get_instance()
		{
		static $instance = null;
		if (null === $instance) {
			$instance = new Template();
		}

		return $instance;
	}

	// Display a template
	public function render()
	{
		global $wp_query;
		global $yalla_template_vars;

		$template_dir  = $this->template_dir;
		$template_file = $this->get_template_file();
		$template_vars = array();
		

		// TWIG ARGS
		$debug 		= ( defined('WP_DEBUG') ? WP_DEBUG : false);
		$twig_args 	= array(
			'debug' => $debug
		);

		$twig_args = apply_filters( 'yalla_twig_args', $twig_args);

		$twig_fs = new \Twig_Loader_Filesystem($template_dir);
		$twig 	 = new \Twig_Environment($twig_fs, $twig_args);
		$twig->addExtension(new WordpressExtension());

		if( $debug){
			$twig->addExtension(new \Twig_Extension_Debug());
		}

		// TEMPLATE VARS
		$loader = Loader::get_instance();

		if( $wp_query->have_posts() ){
			$template_vars['objects'] = $loader->get_objects($wp_query->posts);
		}

		$yalla_template_vars = apply_filters( 'yalla_template_vars', $template_vars);


		echo $twig->render( $template_file, $yalla_template_vars );

	}

	private function get_template_file(){
		global $template, $wp_query;

	// Custom Templates
		if( is_page_template() ){
			$template_name = get_post_meta( $wp_query->post->ID, '_wp_page_template', true );
			$template = $this->get_template_path($template_name);
			
			if( $template )
				return $template;
		}

	// Homepage
		if( function_exists('is_frontpage') && is_frontpage() && $template = $this->get_template_path('frontpage') ){
		}
		else if( is_home() && $template = $this->get_template_path('home') ){
		}
	// 404
		else if( is_404() && $template = $this->get_template_path('404') ){
		}
	// Pages & Singles
		else if( is_page() 			&& $template = $this->get_template_path('page') ){
		}
		else if( is_single() 		&& $template = $this->get_template_path('single') ){
		}
		else if( is_attachment() 	&& $template = $this->get_template_path('attachment') ){
		}
		else if( is_author() 		&& $template = $this->get_template_path('author') ){
		}
		else if( is_singular() 		&& $template = $this->get_template_path('single') ){
		}
	// Archives
		else if( is_search() 		&& $template = $this->get_template_path('search') ){
		}
		else if( is_archive() 		&& $template = $this->get_template_path('archive') ){
		}
		else if( is_category() 		&& $template = $this->get_template_path('category') ){
		}
		else if( is_date() 			&& $template = $this->get_template_path('date') ){
		}
		else if( is_day() 			&& $template = $this->get_template_path('day') ){
		}
		else if( is_month() 		&& $template = $this->get_template_path('month') ){
		}
		else if( is_year() 			&& $template = $this->get_template_path('year') ){
		}
		else if( is_time() 			&& $template = $this->get_template_path('time') ){
		}
	// Fallback
		else if( $template = $this->get_template_path('base') ){
		}
		else{
			trigger_error("Could not find a valid template", E_USER_ERROR);
			exit();
		}

		return $template;
	}

	private function get_template_path($templateName){
		$dir = $this->template_dir;
		$type = get_post_type();

		$template = false;

		if( file_exists( "{$dir}{$templateName}-{$type}.twig" ) ){
			$template = "{$templateName}-{$type}.twig";
		}elseif( file_exists( "{$dir}{$templateName}.twig" ) ){
			$template = "{$templateName}.twig";
		}

		return $template;
	}

	public function register_custom_templates($atts){
		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		// Retrieve the cache list. 
		// If it doesn't exist, or it's empty prepare an array
		$theme = wp_get_theme();
		$templates = $theme->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		} 

		// New cache, therefore remove the old one
		wp_cache_delete( $cache_key , 'themes');

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge( $templates, $this->templates );

		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;
	}

	/**
	* Protected constructor to prevent creating a new instance of the
	* *Singleton* via the `new` operator from outside of this class.
	*/
	protected function __construct()
	{
		$this->template_dir = get_template_directory() . '/templates/';

		// Add a filter to the attributes metabox to inject template into the cache.
		add_filter( 'page_attributes_dropdown_pages_args', array( $this, 'register_custom_templates' )  );
		add_filter( 'wp_insert_post_data',  array( $this, 'register_custom_templates' )  );
	}

	/**
	* Private clone method to prevent cloning of the instance of the
	* *Singleton* instance.
	*
	* @return void
	*/
	private function __clone()
	{
	}

	/**
	* Private unserialize method to prevent unserializing of the *Singleton*
	* instance.
	*
	* @return void
	*/
	private function __wakeup()
	{
	}

}