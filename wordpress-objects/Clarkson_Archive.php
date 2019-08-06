<?php
/**
 * Clarkson Archive.
 *
 * @package CLARKSON\Objects
 */

/**
 * Clarkson Archive class.
 */
class Clarkson_Archive {

	/**
	 * Define $_post_type.
	 *
	 * @var WP_Post_Type|null
	 */
	protected $_post_type;

	/**
	 * Define $archives.
	 *
	 * @var array
	 */
	protected static $archives;

	/**
	 * Clarkson_Archive constructor.
	 *
	 * @param WP_Post_Type|null $post_type Post type object.
	 *
	 * @throws Exception Error message.
	 */
	public function __construct( \WP_Post_Type $post_type = null ) {
		if ( ! $post_type ) {
			throw new Exception( 'Post type not found' );
		}
		if ( ! $post_type->has_archive ) {
			throw new Exception( 'Post type has no archive' );
		}

		$this->_post_type = $post_type;
	}

	/**
	 * Get archive by post type
	 *
	 * @param  string $post_type Post type name.
	 *
	 * @return Clarkson_Archive|null Archive object.
	 */
	public static function get( string $post_type ) {
		if ( ! isset( static::$archives[ $post_type ] ) ) {
			$class = get_called_class();

			try {
				if ( ! $post_type ) {
					throw new Exception( $post_type . ' empty' );
				}
				$post_type_object               = get_post_type_object( $post_type );
				static::$archives[ $post_type ] = new $class( $post_type_object );
			} catch ( Exception $e ) {
				static::$archives[ $post_type ] = null;
			}
		}

		return static::$archives[ $post_type ];
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
	 * Get the post type archive title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		$title = apply_filters( 'post_type_archive_title', $this->_post_type->labels->name, $this->_post_type->name );
		return apply_filters( 'get_the_archive_title', $title );
	}

	/**
	 * Get the post type data.
	 *
	 * @return WP_Post_Type|null
	 */
	public function get_post_type() {
		return $this->_post_type;
	}

	/**
	 * Get the archive permalink.
	 *
	 * @return string|null Archive permalink.
	 */
	public function get_permalink() {
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
	public function get_feed_permalink( string $feed = '' ) {
		$link = get_post_type_archive_feed_link( $this->_post_type->name, $feed );
		if ( $link ) {
			return $link;
		}
		return null;
	}

}
