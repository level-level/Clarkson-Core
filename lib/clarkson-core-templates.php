<?php
/**
 * Clarkson Core Templates.
 *
 * @package CLARKSON\Lib
 */

/**
 * Allows rendering of specific templates with Twig.
 */
class Clarkson_Core_Templates {
	/**
	 * The template types, are all seperate `get_query_template` can be called with.
	 *
	 * @see https://developer.wordpress.org/reference/functions/get_query_template/ The code defines the possible `type` values.
	 */
	private const TEMPLATE_TYPES = array(
		'index',
		'404',
		'archive',
		'author',
		'category',
		'tag',
		'taxonomy',
		'date',
		'embed',
		'home',
		'frontpage',
		'privacypolicy',
		'page',
		'paged',
		'search',
		'single',
		'singular',
		'attachment',
	);

	/**
	 * Define has_been_called
	 *
	 * @var bool $has_been_called Check if template rendering has already been called.
	 */
	protected $has_been_called = false;

	/**
	 * Render template.
	 *
	 * @param string $path           Post meta _wp_page_template.
	 * @param array  $objects        Post objects.
	 * @param bool   $ignore_warning Ignore multiple render warning.
	 * @internal
	 */
	public function render( $path, $objects, $ignore_warning = false ) {
		$this->echo_twig( $path, $objects, $ignore_warning );
		exit();
	}

	/**
	 * Render template using Twig.
	 *
	 * @param string $path           Post meta _wp_page_template.
	 * @param array  $objects        Post objects.
	 * @param bool   $ignore_warning Ignore multiple render warning.
	 *
	 * @return string
	 */
	public function render_twig( $path, $objects, $ignore_warning = false ) {
		// Twig arguments.
		if ( ! $ignore_warning && $this->has_been_called ) {
			user_error( 'Template rendering has already been called. If you are trying to render a partial, include the file from the parent template for performance reasons. If you have a specific reason to render multiple times, set ignore_warning to true.', E_USER_NOTICE );
		}
		$this->has_been_called = true;

		$template_dirs = $this->get_templates_dirs();
		$template_file = str_replace( $template_dirs, '', $path ); // Retrieve only the path to the template file, relative from the yourtheme/templates directory.

		$debug     = ( defined( 'WP_DEBUG' ) ? WP_DEBUG : false );
		$twig_args = array(
			'debug' => $debug,
		);

		/**
		 * Allows manipulation of the twig envirionment settings.
		 *
		 * @hook clarkson_twig_args
		 * @since 0.1.0
		 * @param {array} $twig_args Default options to use when instantiating a twig environment.
		 * @return {array} Options to pass to the twig environment
		 * @see https://twig.symfony.com/doc/2.x/api.html#environment-options
		 *
		 * @example
		 * // Enable caching in the twig environment.
		 * add_filter( 'clarkson_twig_args', function( $twig_args ) {
		 *  $twig_args['cache'] = get_stylesheet_directory() . '/dist/template_cache/';
		 *  return $twig_args;
		 * } );
		 */
		$twig_args = apply_filters( 'clarkson_twig_args', $twig_args );
		$twig_fs   = new Twig_Loader_Filesystem( $template_dirs );
		$twig      = new Twig_Environment( $twig_fs, $twig_args );

		$twig->addExtension( new Clarkson_Core_Twig_Extension() );
		$twig->addExtension( new Twig_Extensions_Extension_I18n() );
		$twig->addExtension( new Twig_Extensions_Extension_Text() );
		$twig->addExtension( new Twig_Extensions_Extension_Array() );
		$twig->addExtension( new Twig_Extensions_Extension_Date() );

		if ( $debug ) {
			$twig->addExtension( new Twig_Extension_Debug() );
		}

		/**
		 * Allows themes and plugins to edit the Clarkson Twig environment.
		 *
		 * @hook clarkson_twig_environment
		 * @since 1.0.0
		 * @param {Twig_Environment} $twig Twig environment.
		 * @return {Twig_Environment} Twig environment.
		 *
		 * @example
		 * // We can add custom twig extensions.
		 * add_filter( 'clarkson_twig_environment', function( $twig ) {
		 *  $twig->addExtension( new \Custom_Twig_Extension() );
		 *  return $twig;
		 * } );
		 */
		$twig = apply_filters( 'clarkson_twig_environment', $twig );

		/**
		 * Context variables that are available in twig templates.
		 *
		 * @hook clarkson_context_args
		 * @since 0.1.0
		 * @param {array} $context Available variables for the twig template.
		 * @return {array} Available variables for the twig template.
		 *
		 * @example
		 * // We can make the WooCommerce cart available on each template.
		 * add_filter( 'clarkson_context_args', function( $context ) {
		 *  $context['cart'] = wc()->cart;
		 *  return $context;
		 * } );
		 */
		$context_args = apply_filters( 'clarkson_context_args', $objects );
		return $twig->render( $template_file, $context_args );
	}


	/**
	 * Echo template.
	 *
	 * see: https://github.com/level-level/Clarkson-Core/issues/126.
	 *
	 * @param string $template_file  Post meta _wp_page_template.
	 * @param array  $objects        Post objects.
	 * @param bool   $ignore_warning Ignore multiple render warning.
	 */
	public function echo_twig( $template_file, $objects, $ignore_warning = false ) {
		echo $this->render_twig( $template_file, $objects, $ignore_warning );
	}

	/**
	 * Get the template directories where the Twig files are located in.
	 *
	 * This takes notices of the child / parent hierarchy, so that's why the child theme gets searched first and then the parent theme, just like the regular WordPress templating hierarchy.
	 */
	public function get_templates_dirs() {
		$template_dirs = array(
			$this->get_stylesheet_dir(),
			$this->get_template_dir(),
		);

		/**
		 * Add/modify directories that contain twig templates that Clarkson should look for.
		 *
		 * @hook clarkson_twig_template_dirs
		 * @since 0.1.8
		 * @param {array} $template_dirs Available variables for the twig template.
		 * @return {array} Template directories to look through.
		 *
		 * @example
		 * // We can add a specific new directory to load templates from to twig.
		 * add_filter( 'clarkson_twig_template_dirs', function( $template_dirs ) {
		 *  $template_dirs[] = get_stylesheet_directory() . '/admin_templates';
		 *  return $template_dirs;
		 * } );
		 */
		$template_dirs = apply_filters( 'clarkson_twig_template_dirs', $template_dirs );

		// if no child-theme is used, then these two above are the same.
		$template_dirs = array_unique( $template_dirs );

		// Ingore template dir if it doesn't exist
		$template_dirs = array_filter(
			$template_dirs,
			function( $template_dir ) {
				return file_exists( $template_dir );
			}
		);

		return $template_dirs;
	}

	/**
	 * Retrieves the parent theme directory Clarkson Core is using to find templates.
	 */
	public function get_template_dir() {
		/**
		 * Modify the template directory path.
		 *
		 * @hook clarkson_twig_template_dir
		 * @since 0.1.0
		 * @param {string} $template_dir Path to the  template directory.
		 * @return {string} Path to template directory.
		 *
		 * @example
		 * // It is possible to customise the place twig looks for templates.
		 * add_filter( 'clarkson_twig_template_dir', function( $template_dir ) {
		 *  return get_template_directory() . '/twig_templates';
		 * } );
		 */
		return realpath( apply_filters( 'clarkson_twig_template_dir', get_template_directory() . '/templates' ) );
	}

	/**
	 * Gets the stylesheet directory Clarkson Core is using to find twig templates.
	 */
	public function get_stylesheet_dir() {
		/**
		 * Modify the template directory path for the stylesheet directory.
		 *
		 * @hook clarkson_twig_stylesheet_dir
		 * @since 0.1.8
		 * @param {string} $stylesheet_dir Path to the stylesheet template directory.
		 * @return {string} Path to template directory.
		 *
		 * @example
		 * // It is possible to customise the place twig looks for templates.
		 * add_filter( 'clarkson_twig_stylesheet_dir', function() {
		 *  return get_stylesheet_directory() . '/twig_templates';
		 * } );
		 */
		return realpath( apply_filters( 'clarkson_twig_stylesheet_dir', get_stylesheet_directory() . '/templates' ) );
	}

	/**
	 * Check template to include.
	 *
	 * @param string $template the template.
	 *
	 * @return string $template the checked template.
	 * @internal
	 */
	public function template_include( $template ) {
		$extension = pathinfo( $template, PATHINFO_EXTENSION );

		if ( 'twig' === $extension ) {
			// Get template vars.
			global $posts;
			$object_loader = Clarkson_Core_Objects::get_instance();

			$page_vars = array();

			if ( is_author() ) {
				$page_vars['user'] = $object_loader->get_user( get_user_by( 'id', get_queried_object_id() ) );
			} elseif ( is_tax() ) {
				$term = get_queried_object();
				// Custom Taxonomy Templates per Taxonomy type.
				if ( is_a( $term, 'WP_Term' ) ) {
					$page_vars['term'] = $object_loader->get_term( $term );
				}
			} elseif ( is_search() ) {
				global $wp_query;
				$page_vars['found_posts'] = $wp_query->get( 'filtered_found_posts' ) ? $wp_query->get( 'filtered_found_posts' ) : $wp_query->found_posts;
			}
			$page_vars['objects'] = $object_loader->get_objects( $posts );
			$template             = realpath( $template );

			$this->render( $template, $page_vars, true );
		}

		return $template;
	}

	/**
	 * Get templates.
	 *
	 * @param array $choices Choices.
	 *
	 * @return array|bool|mixed
	 * @internal
	 */
	public function get_templates( $choices = array() ) {
		$templates = wp_cache_get( 'templates', 'clarkson_core' );
		if ( $templates ) {
			return $templates;
		}
		$templates      = $choices;
		$page_templates = array();
		foreach ( $this->get_template_files() as $name => $path ) {
			if ( preg_match( '#^template-#i', $name ) === 1 ) {
				$name                                = str_replace( 'template-', '', $name );
				$name                                = str_replace( '-', ' ', $name );
				$name                                = ucwords( $name );
				$page_templates[ basename( $path ) ] = $name;
			}
		}

		// Now add our template to the list of templates by merging our templates with the existing templates array from the cache.
		$templates = array_merge( $templates, $page_templates );
		wp_cache_set( 'templates', $templates, 'clarkson_core' );
		return $templates;
	}

	/**
	 * Adds our templates to the page dropdown for WP v4.7+.
	 *
	 * @param array  $posts_templates Templates array.
	 * @param string $theme           Theme.
	 * @param object $post            Post.
	 * @param string $post_type       Post type.
	 *
	 * @return array
	 * @internal
	 */
	public function add_new_template( $posts_templates, $theme, $post, $post_type ) {
		$custom_posts_templates = $this->get_templates();
		foreach ( $custom_posts_templates as $path => $name ) {
			$filename = basename( $path );

			/**
			 * Manipulate which post_types get a template in the template dropdown.
			 *
			 * @hook clarkson_core_templates_types_for_{$name}
			 * @since 0.2.1
			 * @param {string[]} $post_types Which post types the template can be chosen on. By default showns only on 'page'.
			 * @return {string[]} Post type slugs that show the template in the template dropdown.
			 *
			 * @example
			 * // Show a custom template in the 'post' post-type template dropdown.
			 * add_filter( 'clarkson_core_templates_types_for_template-sponsored_post.twig', function($post_types){
			 *  $post_types[] = 'post';
			 *  return $post_types;
			 * } );
			 */
			$show_on_post_types = apply_filters( 'clarkson_core_templates_types_for_' . $filename, array( 'page' ) );
			if ( in_array( $post_type, $show_on_post_types, true ) ) {
				$posts_templates[ $path ] = $name;
			}
		}
		return $posts_templates;
	}

	/**
	 * Add template filters.
	 */
	private function get_template_files() {
		// Get template files.
		$template_paths = $this->get_templates_dirs();

		/**
		 * @hook clarkson_core_template_paths
		 * @since 0.1.0
		 * @param {string[]} $template_paths Template paths (stylesheet, parent theme template directory).
		 * @return {string[]} Template paths that twig will use to load page templates.
		 */
		apply_filters( 'clarkson_core_template_paths', $template_paths );

		$templates = array();

		foreach ( $template_paths as $template_path ) {
			$templates = array_merge( $templates, $this->get_templates_from_path( $template_path ) );
		}

		foreach ( $templates as $template ) {
			$base               = basename( $template );
			$base               = str_replace( '.twig', '', $base );
			$templates[ $base ] = $template;
		}
		return $templates;
	}

	/**
	 * Get templates from path.
	 *
	 * @param string $path File path.
	 *
	 * @return array
	 */
	private function get_templates_from_path( $path ) {
		if ( ! $path || ! is_string( $path ) || ! file_exists( $path ) ) {
			return array();
		}
		$files = glob( "{$path}/template-*.twig" );
		if ( empty( $files ) ) {
			return array();
		}
		return $files;
	}

	/**
	 * Singleton.
	 *
	 * @var null instance Clarkson_Core_Templates.
	 */
	protected $instance = null;

	/**
	 * Get instance.
	 *
	 * @return Clarkson_Core_Templates
	 */
	public static function get_instance() {
		static $instance = null;
		if ( null === $instance ) {
			$instance = new Clarkson_Core_Templates();
		}
		return $instance;
	}

	public function add_twig_to_template_hierarchy( array $original_templates ) {
		$templates = array();

		$directories = array_unique(
			array(
				str_replace( get_stylesheet_directory(), '', $this->get_stylesheet_dir() ),
				str_replace( get_template_directory(), '', $this->get_template_dir() ),
			)
		);

		foreach ( $original_templates as $template ) {
			$pathinfo = pathinfo( $template );
			foreach ( $directories as $directory ) {
				$twig_template = $pathinfo['dirname'] . $directory . '/' . $pathinfo['filename'] . '.twig';
				$templates[]   = $twig_template;
			}
			$templates[] = $template;
		}
		return $templates;
	}

	/**
	 * Clarkson_Core_Templates constructor.
	 */
	protected function __construct() {
		if ( ! class_exists( 'Clarkson_Core_Objects' ) ) {
			return;
		}
		foreach ( self::TEMPLATE_TYPES as $template_type ) {
			add_filter( $template_type . '_template_hierarchy', array( $this, 'add_twig_to_template_hierarchy' ), 999 );
		}

		add_action( 'template_include', array( $this, 'template_include' ) );
		add_filter( 'acf/location/rule_values/page_template', array( $this, 'get_templates' ) );

		// Add a filter to the WP 4.7 version attributes meta box.
		// Add filters for all post_types.
		add_action(
			'wp_loaded',
			function() {
				$custom_post_types = get_post_types(
					array(
						'public'   => false,
						'_builtin' => false,
					),
					'names',
					'or'
				);

				$builtin_post_types = get_post_types(
					array(
						'public'   => false,
						'_builtin' => true,
					),
					'names',
					'or'
				);

				$post_types = array_merge( $custom_post_types, $builtin_post_types );

				foreach ( $post_types as $post_type ) {
					add_filter( 'theme_' . $post_type . '_templates', array( $this, 'add_new_template' ), 10, 4 );
				}
			}
		);
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
