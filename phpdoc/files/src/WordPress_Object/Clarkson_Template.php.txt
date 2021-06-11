<?php


/**
 * Clarkson Template.
 */

namespace Clarkson_Core\WordPress_Object;

use Clarkson_Core\Objects;

class Clarkson_Template {
	/**
	 * The string here is the template name used by `::get_many()`.
	 *
	 * @var string
	 */
	public static $type = '';

	/**
	 * @var \WP_Post
	 */
	protected $_post;

	/**
	 * @var Clarkson_Object|null
	 */
	private $_object;

	public function __construct( \WP_Post $post ) {
		$this->_post = $post;
	}

	public static function get( int $id ):?Clarkson_Template {
		$post = get_post( $id );
		if ( ! $post instanceof \WP_Post ) {
			return null;
		}
		return Objects::get_instance()->get_template( $post );
	}

	/**
	 * Get multiple templates, without pagination.
	 *
	 * @param array $args Post query arguments. {@link https://developer.wordpress.org/reference/classes/wp_query/#parameters}
	 * @param mixed $post_query The $post_query is passed by reference and will be filled with the WP_Query that produced these results.
	 *
	 * @return Clarkson_Template[]
	 *
	 * @example
	 * \Clarkson_Template::get_many( array( 'posts_per_page' => 5 ), $post_query );
	 */
	public static function get_many( array $args, &$post_query = null ):array {
		$args                 = wp_parse_args(
			$args,
			array(
				'post_type'  => 'any',
				'meta_query' => array(),
			)
		);
		$args['meta_query'][] = array(
			'key'   => '_wp_page_template',
			'value' => static::$type,
		);

		$args['no_found_rows'] = true;
		$args['fields']        = 'all';

		$query      = new \WP_Query( $args );
		$objects    = Objects::get_instance()->get_templates( $query->posts );
		$post_query = $query;
		return $objects;
	}

	/**
	 * Gets the first result from a `::get_many()` query.
	 *
	 * @param array $args Post query arguments. {@link https://developer.wordpress.org/reference/classes/wp_query/#parameters}
	 */
	public static function get_one( $args = array() ): ?Clarkson_Template {
		$args['posts_per_page'] = 1;
		$one                    = static::get_many( $args );
		return array_shift( $one );
	}

	/**
	 * Get the Clarkson object for this template.
	 */
	public function get_object():Clarkson_Object {
		if ( empty( $this->_object ) ) {
			$this->_object = Objects::get_instance()->get_object( $this->_post );
		}
		return $this->_object;
	}
}
