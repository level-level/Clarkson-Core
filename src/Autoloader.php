<?php
/**
 * Clarkson Core Autoloader.
 *
 * @package CLARKSON\Lib
 */

namespace Clarkson_Core;

/**
 * Autoloader class.
 * Registers post types and taxonomies, users and WP Objects.
 * @internal
 */
class Autoloader {
	/**
	 * Define Post types.
	 *
	 * @var array $post_types Post types.
	 * @internal
	 */
	public $post_types = array();

	/**
	 * Define Taxonomies.
	 *
	 * @var array $taxonomies Taxonomies.
	 * @internal
	 */
	public $taxonomies = array();

	/**
	 * Define User types.
	 *
	 * @var array $user_types User types.
	 * @internal
	 */
	public $user_types = array();

	/**
	 * Define the prefix for the custom user Object classes.
	 *
	 * @var string $user_objectname_prefix Classname prefix.
	 * @internal
	 */
	public $user_objectname_prefix = 'user_';


	/**
	 * Define Extra.
	 *
	 * @var array $extra Extra.
	 * @internal
	 */
	public $extra = array();


	/**
	 * Clarkson_Core_Autoloader constructor.
	 */
	public function __construct() {
		add_action( 'registered_post_type', array( $this, 'registered_post_type' ), 10, 1 );
		add_action( 'registered_taxonomy', array( $this, 'registered_taxonomy' ), 10, 1 );
		add_action( 'init', array( $this, 'register_user_types' ), 10, 1 );
		add_action( 'wp', array( $this, 'load_template_objects' ) );
		spl_autoload_register( array( $this, 'load_wordpress_objects' ), true, true );
	}

	/**
	 * Changes object names into valid classnames.
	 *
	 * A post type with a name 'll-events' can not be a valid classname in PHP.
	 * Any none alphanumeric character is changed into an `_` and the complete
	 * name is changed to lowercase.
	 *
	 * @param string $str  Object name.
	 *
	 * @return string Sanitized object name.
	 */
	public function sanitize_object_name( $str ):string {

		$str = trim( $str );

		// Replace - with _ .
		// Non-alpha and non-numeric characters become underscores.
		// We can't run `new ll-events()` because that's an invalid class name.
		$str = preg_replace( '/[^a-z0-9]+/i', '_', $str );

		// String to lowercase is require by post-type naming convention.
		// See https://codex.wordpress.org/Function_Reference/register_post_type#post_type.
		$str = strtolower( $str );

		return $str;
	}

	/**
	 * Fill $post_type variable with all registered CPT's, every time a post type is registered via WordPress after the "registered_post_type" action.
	 *
	 * This also means reserved ones like page, post, attachment, revision, nav_menu_item, custom_css and customize_changeset.
	 *
	 * @param string $post_type Post type.
	 * @internal
	 */
	public function registered_post_type( $post_type ):void {
		$this->post_types[ $this->sanitize_object_name( $post_type ) ] = $this->sanitize_object_name( $post_type );
	}

	/**
	 * Fill $taxonomies variable with all registered Taxonomies, every time a taxonomy is registered via WordPress after the "registered_taxonomy" action.
	 *
	 * @param string $taxonomy Taxonomy name.
	 * @internal
	 */
	public function registered_taxonomy( $taxonomy ):void {
		$this->taxonomies[ $this->sanitize_object_name( $taxonomy ) ] = $this->sanitize_object_name( $taxonomy );
	}


	/**
	 * Returns the page template filename without extension.
	 * Returns an empty string when the default page template is in use.
	 * Returns false if the post is not a page.
	 *
	 * @param integer $post_id Post id.
	 *
	 * @return string
	 */
	public function get_template_filename( $post_id ):string {
		$page_template_slug = get_page_template_slug( $post_id );
		$filename           = '';

		if ( ! empty( $page_template_slug ) ) {
			$pathinfo = pathinfo( $page_template_slug );
			$filename = array_key_exists( 'filename', $pathinfo ) ? (string) $pathinfo['filename'] : '';
		}
		return $filename;
	}

	/**
	 * Fill $extra variable with the current custom template
	 * @internal
	 */
	public function load_template_objects():void {
		$template_name = $this->get_template_filename( get_queried_object_id() );

		if ( $template_name && ! empty( $template_name ) ) {
			$template_name                 = $this->sanitize_object_name( $template_name );
			$this->extra[ $template_name ] = $template_name;
		}

		/**
		 * Adds available templates to the known available templates list.
		 *
		 * @hook clarkson_core_available_templates
		 * @since 0.4.1
		 * @param {string[]} $this->extra Templates automatically found by Clarkson Core.
		 * @return {string[]} Templates that should be available as objects to Clarkson_Core_Objects.
		 * @see https://github.com/level-level/Clarkson-Core/issues/161
		 *
		 * @example
		 * // Use custom templates in the theme.
		 * add_filter(
		 *  'clarkson_core_available_templates',
		 *  function() {
		 *      return array(
		 *          'template_landingpage',
		 *          'template_frontpage',
		 *      );
		 *  }
		 * );
		 */
		$this->extra = apply_filters( 'clarkson_core_available_templates', $this->extra );
	}

	/**
	 * Register user types.
	 * @internal
	 */
	public function register_user_types():void {
		global $wp_roles;

		// Default is 'user'.
		$this->user_types[ $this->sanitize_object_name( 'user' ) ] = $this->sanitize_object_name( 'user' );

		// Now register user role based classes.
		// We prefix every role with user_ so we within the wordpress-objects directory all types will be grouped together
		foreach ( $wp_roles->roles as $key => $role ) {
			$class_name                      = $this->sanitize_object_name( $this->user_objectname_prefix . $key );
			$this->user_types[ $class_name ] = $class_name;
		}
	}

	/**
	 * Load all files of each WordPress object.
	 *
	 * @param string $classname Class name.
	 * @internal
	 */
	protected function load_wordpress_objects( $classname ):void {
		$type = $this->sanitize_object_name( $classname );

		// This is faster than a class_exists check.
		if ( ! in_array( $type, $this->post_types, true ) && ! in_array( $type, $this->taxonomies, true ) && ! in_array( $type, $this->extra, true ) && ! in_array( $type, $this->user_types, true ) ) {
			return;
		}

		$filename = "{$classname}.php";

		// Load child theme first.
		$filepath = realpath( get_stylesheet_directory() ) . "/wordpress-objects/{$filename}";
		if ( file_exists( $filepath ) ) {
			include_once $filepath;
			return;
		}

		// If not exists then load normal / parent theme.
		$filepath = realpath( get_template_directory() ) . "/wordpress-objects/{$filename}";
		if ( file_exists( $filepath ) ) {
			include_once $filepath;
			return;
		}
	}
}
