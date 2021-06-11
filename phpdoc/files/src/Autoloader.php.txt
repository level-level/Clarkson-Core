<?php
/**
 * Clarkson Core Autoloader.
 */

namespace Clarkson_Core;

/**
 * Autoloader class.
 * Registers post types and taxonomies, users and WP Objects.
 * @internal
 */
class Autoloader {
	/**
	 * Define the prefix for the custom user Object classes.
	 *
	 * @var string $user_objectname_prefix Classname prefix.
	 * @internal
	 */
	public $user_objectname_prefix = 'user_';

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
}
