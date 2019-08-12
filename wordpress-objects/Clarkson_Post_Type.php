<?php
/**
 * Clarkson Post Type.
 *
 * @package CLARKSON\Objects
 */

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
	public static function get( string $post_type ): Clarkson_Post_Type {
		$class            = get_called_class();
		$post_type_object = get_post_type_object( $post_type );
		return new $class( $post_type_object );
	}

	/**
	 * Check if an existing field is requested.
	 *
	 * @param string $name Field to search by.
	 *
	 * @throws Exception Error message.
	 */
	public function __get( string $name ) {
		throw new Exception( 'Trying to access WordPress Archive object properties while WordPress doesn\'t have an archive object.' );
	}

	/**
	 * Get the post type title.
	 */
	public function get_title(): string {
		$title = $this->_post_type->labels->singular_name;
		return apply_filters( 'the_title', $title );
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
