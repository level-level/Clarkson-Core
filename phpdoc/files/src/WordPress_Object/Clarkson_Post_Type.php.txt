<?php
/**
 * Clarkson Post Type.
 */

namespace Clarkson_Core\WordPress_Object;

use Clarkson_Core\Objects;
use WP_Post_Type;

/**
 * Clarkson Post Type class.
 */
class Clarkson_Post_Type {

	/**
	 * @var \WP_Post_Type
	 */
	protected $_post_type;

	/**
	 * @var array
	 */
	protected static $post_types;

	public function __construct( \WP_Post_Type $post_type ) {
		$this->_post_type = $post_type;
	}

	/**
	 * Get post type object by post type name
	 */
	public static function get( string $post_type ): ?Clarkson_Post_Type {
		$post_type_object = get_post_type_object( $post_type );
		if ( ! $post_type_object instanceof \WP_Post_Type ) {
			return null;
		}
		return Objects::get_instance()->get_post_type( $post_type_object );
	}

	/**
	 * Get all available Clarkson Post Type objects
	 *
	 * @see https://developer.wordpress.org/reference/functions/get_post_types/
	 *
	 * @param array $args Post type arguments
	 * @param string $operator One of 'and', 'or', or 'not'
	 *
	 * @return \Clarkson_Core\WordPress_Object\Clarkson_Post_Type[]
	 */
	public static function get_many( array $args = array(), string $operator = 'and' ): array {
		$post_type_objects = get_post_types( $args, 'objects', $operator );
		$post_type_objects = array_filter(
			$post_type_objects,
			function( $post_type_object ) {
				return $post_type_object instanceof WP_Post_Type;
			}
		);
		return Objects::get_instance()->get_post_types( $post_type_objects );
	}

	/**
	 * Proxy requested properties to WP Post Type if it doesn't exist in Clarkson Post Type
	 *
	 * @param string $name Field to search by.
	 * @throws \Exception Error message.
	 */
	public function __get( string $name ) {
		if ( property_exists( $this->_post_type, $name ) ) {
			return $this->_post_type->$name;
		}
		throw new \Exception( 'Object property does not exist in both Clarkson_Post_Type and WP_Post_Type.' );
	}

	/**
	 * Get multiple posts, without pagination.
	 *
	 * @param array $args Post query arguments. {@link https://developer.wordpress.org/reference/classes/wp_query/#parameters}
	 *
	 * @return Clarkson_Object[]
	 *
	 * @example
	 * Clarkson_Post_type->get_posts( array( 'posts_per_page' => 5 ) );
	 */
	public function get_posts( $args ) {
		$args['post_type']     = $this->_post_type->name;
		$args['no_found_rows'] = true;
		$args['fields']        = 'all';

		$query = new \WP_Query( $args );
		return Objects::get_instance()->get_objects( $query->posts );
	}

	/**
	 * Exists check for __get function
	 *
	 * @param string $name Field to search by.
	 * @return boolean property exists
	 */
	public function __isset( string $name ): bool {
		return property_exists( $this->_post_type, $name );
	}

	/**
	 * Get the post type title.
	 */
	public function get_title(): string {
		$title = $this->_post_type->labels->singular_name;
		return $title;
	}

	/**
	 * Get the post type data.
	 */
	public function get_post_type(): \WP_Post_Type {
		return $this->_post_type;
	}

	/**
	 * Get the post type archive title.
	 */
	public function get_archive_title(): string {
		$title = apply_filters( 'post_type_archive_title', $this->_post_type->labels->name, $this->_post_type->name );
		return apply_filters( 'get_the_archive_title', $title );
	}

	/**
	 * Get the archive permalink.
	 *
	 * @return string|null Archive permalink.
	 */
	public function get_archive_permalink(): ?string {
		$link = get_post_type_archive_link( $this->_post_type->name );
		if ( $link ) {
			return $link;
		}
		return null;
	}

	/**
	 * Get the archive feed permalink.
	 *
	 * @return string|null Archive feed permalink.
	 */
	public function get_archive_feed_permalink( string $feed = '' ): ?string {
		$link = get_post_type_archive_feed_link( $this->_post_type->name, $feed );
		if ( $link ) {
			return $link;
		}
		return null;
	}

}
