<?php
/**
 * Clarkson Archive.
 *
 * @package CLARKSON\Objects
 */

/**
 * Clarkson Archive class.
 */
class Clarkson_Archive_Object {

	/**
	 * Define $_post_type.
	 *
	 * @var WP_Post_Type|null
	 */
	protected $_type;

	/**
	 * Clarkson_Archive_Object constructor.
	 *
	 * @param string|null $type Post type.
	 *
	 * @throws Exception Error message.
	 */
	public function __construct( $type = null ) {
		if ( ! $type ) {
			throw new Exception( $type . ' empty' );
		}
		$this->_type = get_post_type_object( $type );
		if ( ! $this->_type ) {
			throw new Exception( 'Post type not found' );
		}
		if ( ! $this->_type->has_archive ) {
			throw new Exception( 'Post type has no archive' );
		}
	}

	/**
	 * Check if an existing field is requested.
	 *
	 * @param string $name Field to search by.
	 *
	 * @throws Exception Error message.
	 */
	public function __get( $name ) {
		throw new Exception( 'Trying to access WordPress Archive object properties while WordPress doesn\'t have an archive object.' );
	}

	/**
	 * Get the post type data.
	 *
	 * @return string|null
	 */
	public function get_title() {
		return get_the_archive_title();
	}

	/**
	 * Get the post type data.
	 *
	 * @return WP_Post_Type|null
	 */
	public function get_type() {
		return $this->_type;
	}

	/**
	 * Get the archive permalink.
	 *
	 * @return string|null Archive permalink.
	 */
	public function get_permalink() {
		$link = get_post_type_archive_link( $this->_type->name );
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
	public function get_feed_permalink( $feed = '' ) {
		$link = get_post_type_archive_feed_link( $this->_type->name, $feed );
		if ( $link ) {
			return $link;
		}
		return null;
	}

}
