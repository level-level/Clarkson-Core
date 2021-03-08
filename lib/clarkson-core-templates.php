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
	 * Define Templates.
	 *
	 * @var array $templates Templates.
	 */
	protected $templates = array();

	/**
	 * The template context generator.
	 *
	 * This object can be used if you want to remove any of the default add_filters.
	 *
	 * @var \Clarkson_Core_Template_Context
	 */
	public $template_context;

	/**
	 * Define has_been_called
	 *
	 * @var bool $has_been_called Check if template rendering has already been called.
	 */
	protected $has_been_called = false;

	/**
	 * The twig environment.
	 *
	 * @var null|Twig_Environment $twig The reusable twig environment object.
	 */
	private $twig;

	/**
	 * Render template.
	 *
	 * @param string $path           Post meta _wp_page_template.
	 * @param array  $objects        Post objects.
	 * @param bool   $ignore_warning Ignore multiple render warning.
	 * @internal
	 */
	public function render( $path, $objects, $ignore_warning = false ) {
		global $wp_query;
		if ( is_page_template() && isset( $wp_query->post ) && isset( $wp_query->post->ID ) ) {
			$template_path = get_post_meta( $wp_query->post->ID, '_wp_page_template', true );
			// If this file doesn't exist just fallback on the default WordPress template hierarchy fallback method and first checking the child-theme and then the parent theme.
			if ( file_exists( $this->get_stylesheet_dir() . '/' . $template_path ) ) {
				$path = $template_path;
			} elseif ( file_exists( $this->get_template_dir() . '/' . $template_path ) ) {
				$path = $template_path;
			}
		}

		if ( isset( $wp_query->query_vars['json'] ) ) {
			if ( count( $objects ) === 1 && isset( $objects['objects'][0] ) ) {
				$objects = reset( $objects['objects'][0] );
			}
			$this->echo_json( $objects );
		} else {
			$this->echo_twig( $path, $objects, $ignore_warning );
		}
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
		global $wp_query;

		// Twig arguments.
		if ( ! $ignore_warning && $this->has_been_called ) {
			user_error( 'Template rendering has already been called. If you are trying to render a partial, include the file from the parent template for performance reasons. If you have a specific reason to render multiple times, set ignore_warning to true.', E_USER_NOTICE );
		}
		$this->has_been_called = true;

		$template_dirs = $this->get_templates_dirs();

		if ( is_page_template() && isset( $wp_query->post ) && isset( $wp_query->post->ID ) ) {
			// Use the default WordPress template hierarchy fallback method
			$template_file = str_replace( $template_dirs, '', $path );
		} else {
			// Use realpath to get the template file
			$realpath_template_dir   = realpath( $this->get_template_dir() );
			$realpath_stylesheet_dir = realpath( $this->get_stylesheet_dir() );
			$realpath_path           = realpath( $path );
			$template_file           = str_replace( array( $realpath_template_dir, $realpath_stylesheet_dir ), '', $realpath_path );
		}

		$twig = $this->get_twig_environment( $template_dirs );

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

	private function get_twig_environment( array $template_dirs ):Twig_Environment {
		if ( ! $this->twig ) {
			$debug     = ( defined( 'WP_DEBUG' ) ? constant( 'WP_DEBUG' ) : false );
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
			$this->twig = apply_filters( 'clarkson_twig_environment', $twig );
		}
		return $this->twig;
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
	 * Render Json.
	 *
	 * @param array $posts Post or posts.
	 *
	 * @see: https://github.com/level-level/Clarkson-Core/issues/125.
	 *
	 * @return mixed|string|void
	 * @deprecated Use the WordPress API instead.
	 */
	public function render_json( $posts ) {
		header( 'Content-Type: application/json' );

		// If single post then create new array.
		if ( ! is_array( $posts ) ) {
			$objects[] = $posts;
		} else {
			$objects = $posts;
		}

		$cc_objects = Clarkson_Core_Objects::get_instance();
		$objects    = $cc_objects->get_objects( $objects );
		return json_encode( $objects, JSON_PRETTY_PRINT );
	}


	/**
	 * Echo Json data.
	 *
	 * @see https://github.com/level-level/Clarkson-Core/issues/126,
	 * @param array $objects Posts.
	 * @deprecated Use the WordPress API instead.
	 */
	public function echo_json( $objects ) {
		echo $this->render_json( $objects );
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

		// Ignore template dir if it doesn't exist
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
		return apply_filters( 'clarkson_twig_template_dir', get_template_directory() . '/templates' );
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
		return apply_filters( 'clarkson_twig_stylesheet_dir', get_stylesheet_directory() . '/templates' );
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
		global $wp_query;
		$extension = pathinfo( $template, PATHINFO_EXTENSION );
		$type      = basename( $template );
		$type      = str_replace( ".{$extension}", '', $type );

		// Double check.
		if ( isset( $this->templates[ $type ] ) ) {
			$template  = $this->templates[ $type ];
			$extension = 'twig';
		}

		if ( 'twig' === $extension ) {
			/**
			 * Add and modify variables available during the template rendering.
			 *
			 * @hook clarkson_core_template_context
			 * @since 1.0.0
			 * @param {array} $context The context that will be passed onto the template.
			 * @param {\WP_Query} $wp_query The current query that is being rendered.
			 * @return {array} The context that will be passed onto the template.
			 *
			 * @example
			 * // It is possible to add custom variables to the twig context.
			 * add_filter( 'clarkson_core_template_context', function( $context, $wp_query ) {
			 *  if( $wp_query->is_tax ){
			 *   $context['tax_variable'] = true;
			 *  }
			 *  return $context
			 * } );
			 */
			$context  = apply_filters( 'clarkson_core_template_context', array(), $wp_query );
			$template = realpath( $template );
			$this->render( $template, $context, true );
		}

		return $template;
	}


	/**
	 * Add template.
	 *
	 * @param string $template Default template.
	 *
	 * @return string|array
	 * @internal
	 */
	public function add_template( $template ) {
		// Allow twig based on wp_query.
		global $wp_query;
		if ( isset( $wp_query->twig ) && file_exists( $wp_query->twig ) ) {
			return $wp_query->twig;
		}

		$queried_object = get_queried_object();

		// Check filter for current template.
		$filter = current_filter();
		$type   = str_replace( '_template', '', $filter );

		// Post Types.
		$post_type = get_post_type();
		if ( ! $post_type || empty( $post_type ) ) {

			/**
			 * Fix for archive pages with no posts on it.
			 * See https://github.com/level-level/Clarkson-Core/issues/90 & https://core.trac.wordpress.org/ticket/20647.
			 *
			 * Don't use query_vars 'post_type' because this could return an Array if multiple Post Types are set via pre_get_posts.
			 * We always want the main Queried Object 'name' to load that specific CPT template.
			 */

			if ( is_a( $queried_object, 'WP_Post_Type' ) && isset( $queried_object->name ) ) {
				$post_type = $queried_object->name;
			}
		}

		// Taxonomy Templates per Taxonomy type.
		if ( is_a( $queried_object, 'WP_Term' ) && isset( $queried_object->taxonomy ) ) {
			$post_type = $queried_object->taxonomy;
		}

		$templates = $this->templates;

		if ( isset( $templates[ "{$type}-{$post_type}" ] ) ) {
			return $templates[ "{$type}-{$post_type}" ];
		}
		if ( isset( $templates[ "{$type}" ] ) ) {
			return $templates[ "{$type}" ];
		}

		/**
		 * Major exception here:
		 *
		 * Fallback if $type is 'page' but the custom template file in _template.
		 * that isn't present on the disk anymore. Then $type is still 'page'.
		 * but it could fallback on singular.twig when that file is present.
		 *
		 * Of course only if there is a singular template.
		 */
		if ( 'page' === $type && ! isset( $templates[ "{$type}" ] ) && isset( $templates['singular'] ) ) {
			return $templates['singular'];
		}
		if ( isset( $templates['index'] ) ) {
			return $templates['index'];
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
		// Retrieve the cache list.
		// If it doesn't exist, or it's empty prepare an array.
		$theme = wp_get_theme();

		if ( method_exists( $theme, 'get_page_templates' ) ) {
			if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) { // 4.6 and older
				$templates = $theme->get_page_templates();
			} else { // 4.7+
				$templates = array();
			}
			if ( empty( $templates ) ) {
				$templates = array();
			}
		} else {
			$templates = array();
		}

		$templates      = array_merge( $templates, $choices );
		$page_templates = array();
		foreach ( $this->templates as $name => $path ) {
			$is_valid_template = false;

			/**
			 * IF
			 * Check if template matches of page-xyz.twig and skip page.twig
			 * ELSE IF
			 * Check for template-xyz.twig files and skip template.twig
			 *
			 * @since 0.2.1.
			 */
			if ( preg_match( '#^page-#i', $name ) === 1 && 'page' !== $name ) {
				$is_valid_template = true;
				$name              = str_replace( 'page-', '', $name );

				/**
				 * Allows turning of the warning for page- style naming, deprecated in 0.2.1.
				 *
				 * @hook clarkson_core_deprecated_warning_page_template
				 * @since 0.2.1
				 * @param {bool} WP_DEBUG WordPress debug mode constant.
				 * @return {bool} Whether to show or hide the deprecation warning.
				 *
				 * @example
				 * // Disable old style page warning.
				 * add_filter( 'clarkson_core_deprecated_warning_page_template', '__return_false' );
				 */
				/**
				 * @psalm-suppress UndefinedConstant
				 */
				$show_warning = apply_filters( 'clarkson_core_deprecated_warning_page_template', WP_DEBUG );
				if ( $show_warning ) {
					user_error( 'Deprecated template name ' . esc_html( $path ) . ' found. Use `template-' . esc_html( $name ) . '.twig` instead.', E_USER_DEPRECATED );
				}
			} elseif ( preg_match( '#^template-#i', $name ) === 1 && 'template' !== $name ) {
				$is_valid_template = true;
				$name              = str_replace( 'template-', '', $name );
			}

			if ( $is_valid_template ) {
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
	 * Adds our templates to the page dropdown for v4.6 and older.
	 *
	 * @param array $atts Attributes .
	 * @internal
	 */
	public function register_custom_templates( $atts ) {
		// Create the key used for the themes cache.
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );
		$templates = $this->get_templates();

		// New cache, therefore remove the old one.
		wp_cache_delete( $cache_key, 'themes' );

		// Add the modified cache to allow WordPress to pick it up for listing.
		// Available templates.
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );
		return $atts;
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
	private function add_template_filters() {
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
		$filters   = array();

		foreach ( $template_paths as $template_path ) {
			$templates = array_merge( $templates, $this->get_templates_from_path( $template_path ) );
		}
		foreach ( $templates as $template ) {
			$base      = basename( $template );
			$base      = str_replace( '.twig', '', $base );
			$type      = preg_replace( '|[^a-z0-9-]+|', '', $base );
			$base_type = preg_replace( '(-.*)', '', $type );
			if ( ! in_array( $base_type, $filters, true ) ) {
				add_filter( "{$base_type}_template", array( $this, 'add_template' ) );
				$filters[] = $base_type;
			}

			$this->templates[ $base ] = $template;
		}
	}

	/**
	 * Get templates from path.
	 *
	 * @param string $path File path.
	 *
	 * @return array
	 */
	private function get_templates_from_path( $path ) {
		$templates = array();
		if ( ! $path || ! is_string( $path ) || ! file_exists( $path ) ) {
			return $templates;
		}
		$files = glob( "{$path}/*.twig" );
		if ( empty( $files ) ) {
			return $templates;
		}
		foreach ( $files as $file_path ) {
			$templates[] = $file_path;
		}
		return $templates;
	}

	/**
	 * Singleto.
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

	/**
	 * Clarkson_Core_Templates constructor.
	 */
	protected function __construct() {
		require_once __DIR__ . '/clarkson-core-template-context.php';
		if ( ! class_exists( 'Clarkson_Core_Objects' ) ) {
			return;
		}

		$this->template_context = new Clarkson_Core_Template_Context();
		$this->template_context->register_hooks();

		$this->add_template_filters();
		add_action( 'template_include', array( $this, 'template_include' ) );
		add_filter( 'wp_insert_post_data', array( $this, 'register_custom_templates' ) );
		add_filter( 'acf/location/rule_values/page_template', array( $this, 'get_templates' ) );

		// Add a filter to the attributes metabox to inject template into the cache.
		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {
			// WP 4.6 and older.
			add_filter( 'page_attributes_dropdown_pages_args', array( $this, 'register_custom_templates' ) );
		} else {
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
