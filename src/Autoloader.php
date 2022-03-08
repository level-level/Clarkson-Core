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
	 * @internal
	 */
	public string $user_objectname_prefix = 'user_';

	/**
	 * Changes object names into valid classnames.
	 *
	 * A post type with a name 'll-events' can not be a valid classname in PHP.
	 * Any none alphanumeric character is changed into an `_` and the complete
	 * name is changed to lowercase.
	 */
	public function sanitize_object_name( string $str ): string {
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
	 */
	public function get_template_filename( int $post_id ): string {
		$page_template_slug = get_page_template_slug( $post_id );
		$filename           = '';

		if ( ! empty( $page_template_slug ) ) {
			$filename = pathinfo( $page_template_slug, PATHINFO_FILENAME );
		}
		return $filename;
	}
}
