<?php

class Clarkson_Core_Autoloader {
	public $post_types = array();
	public $taxonomies = array();
	public $user_types = array();
	public $extra 	   = array();

	public function __construct() {
		add_action( 'registered_post_type', array( $this, 'registered_post_type' ), 10, 1 );
		add_action( 'registered_taxonomy', array( $this, 'registered_taxonomy' ), 10, 1 );
		add_action( 'wp', array( $this, 'load_template_objects' ) ); // could also run on 'wp' but this is already
		spl_autoload_register( array( $this, 'load_wordpress_objects' ), true, true );

		$filepath = realpath( get_template_directory() ) . '/wordpress-objects/User.php';
		if ( file_exists( $filepath ) ) {
			$this->user_types['user'] = 'user';
		}
	}

	/**
	 * Prepares object names
	 */
	public function sanitize_object_name( $str ) {
		$str = trim( $str );

		// Replace - with _
		// non-alpha and non-numeric characters become underscores
		// We can't run `new ll-events()` because that's an invalid classname.
		$str = preg_replace( '/[^a-z0-9]+/i', '_', $str );

		// String to lowercase is require by post-type namingconvention :
		// https://codex.wordpress.org/Function_Reference/register_post_type#post_type
		$str = strtolower( $str );

		return $str;
	}

	/**
	 * Fill $post_type variable with all registered CPT's,
	 * every time a post type is registered via WordPress after the "registered_post_type" action.
	 *
	 * This also means reserved ones like page, post, attachment, revision, nav_menu_item,
	 * custom_css and customize_changeset.
	 */
	public function registered_post_type( $post_type ) {
		$this->post_types[ $this->sanitize_object_name( $post_type ) ] = $this->sanitize_object_name( $post_type );
	}

	/**
	 * Fill $taxonomies variable with all registered Taxonomies,
	 * every time a taxonomy is registered via WordPress after the "registered_taxonomy" action.
	 */
	public function registered_taxonomy( $taxonomy ) {
		$this->taxonomies[ $this->sanitize_object_name( $taxonomy ) ] = $this->sanitize_object_name( $taxonomy );
	}

	/**
	 * Returns page template filename without extension. Returns an empty string when the default page template is in use. Returns false if the post is not a page.
	 */
	public function get_template_filename( $post_id ) {
		$page_template_slug = get_page_template_slug( $post_id );
		$filename = '';

		if ( ! empty( $page_template_slug ) ) {
			$pathinfo = pathinfo( $page_template_slug );
			$filename = array_key_exists( 'filename', $pathinfo ) ? $pathinfo['filename'] : '';
		}

		return $filename;
	}

	/**
	 * Fill $extra variable with the current custom template
	 */
	public function load_template_objects() {
		$template_name = $this->get_template_filename( get_queried_object_id() );

		if ( $template_name && ! empty( $template_name ) ) {
			$template_name = $this->sanitize_object_name( $template_name );
			$this->extra[ $template_name ] = $template_name;
		}
	}

	/**
	 * Load all files of each WordPress object
	 */
	protected function load_wordpress_objects( $classname ) {
		$type = $this->sanitize_object_name( $classname );

		// This is faster than a class_exists check
		if ( ! in_array( $type, $this->post_types ) && ! in_array( $type, $this->taxonomies ) && ! in_array( $type, $this->extra ) ) {
			return;
		}

		$filename = "{$classname}.php";

		// Load child theme first
		$filepath = realpath( get_stylesheet_directory() ) . "/wordpress-objects/{$filename}";
		if (file_exists( $filepath )) {
			include_once( $filepath );
			return;
		}

		// If not exists then load normal / parent theme
		$filepath = realpath( get_template_directory() ) . "/wordpress-objects/{$filename}";
		if (file_exists( $filepath )) {
			include_once( $filepath );
			return;
		}
	}
}