<?php
/**
 * Clarkson Taxonomy.
 */

namespace Clarkson_Core\WordPress_Object;

use Clarkson_Core\Objects;
use WP_Taxonomy;

/**
 * Clarkson Taxonomy class.
 */
class Clarkson_Taxonomy {

	/**
	 * @var WP_Taxonomy
	 */
	protected $_taxonomy;

	public function __construct( WP_Taxonomy $taxonomy ) {
		$this->_taxonomy = $taxonomy;
	}

	/**
	 * Get taxonomy object by taxonomy name
	 */
	public static function get( string $taxonomy ): ?Clarkson_Taxonomy {
		$taxonomy_object = get_taxonomy( $taxonomy );
		if ( ! $taxonomy_object instanceof WP_Taxonomy ) {
			return null;
		}
		return Objects::get_instance()->get_taxonomy( $taxonomy_object );
	}

	/**
	 * Get all available Clarkson Taxonomy objects
	 *
	 * @see https://developer.wordpress.org/reference/functions/get_taxonomies/
	 *
	 * @param array $args Taxonomy arguments
	 * @param string $operator One of 'and', 'or', or 'not'
	 *
	 * @return \Clarkson_Core\WordPress_Object\Clarkson_Taxonomy[]
	 */
	public static function get_many( array $args = array(), string $operator = 'and' ): array {
		$taxonomy_objects = get_taxonomies( $args, 'objects', $operator );
		$taxonomy_objects = array_filter(
			$taxonomy_objects,
			function( $taxonomy_object ) {
				return $taxonomy_object instanceof WP_Taxonomy;
			}
		);
		return Objects::get_instance()->get_taxonomies( $taxonomy_objects );
	}

	/**
	 * Proxy requested properties to Taxonomy if it doesn't exist in Clarkson Taxonomy
	 *
	 * @param string $name Field to search by.
	 * @throws \Exception Error message.
	 */
	public function __get( string $name ) {
		if ( property_exists( $this->_taxonomy, $name ) ) {
			return $this->_taxonomy->$name;
		}
		throw new \Exception( 'Object property does not exist in both Clarkson_Taxonomy and WP_Taxonomy.' );
	}

	/**
	 * Exists check for __get function
	 *
	 * @param string $name Field to search by.
	 * @return boolean property exists
	 */
	public function __isset( string $name ): bool {
		return property_exists( $this->_taxonomy, $name );
	}

	/**
	 * Get the taxonomy data.
	 */
	public function get_taxonomy(): WP_Taxonomy {
		return $this->_taxonomy;
	}

	/**
	 * Get the taxonomy title.
	 */
	public function get_title(): string {
		$title = $this->_taxonomy->labels->singular_name;
		return $title;
	}

	/**
	 * Get multiple terms.
	 *
	 * @param array $args Tax query arguments. {@link https://developer.wordpress.org/reference/classes/wp_term_query/__construct/#parameters}
	 *
	 * @return Clarkson_Term[]
	 *
	 * @example
	 * Clarkson_Taxonomy->get_terms( array( 'posts_per_page' => 5 ) );
	 */
	public function get_terms( $args ) {
		$args['taxonomy'] = $this->_taxonomy->name;
		$args['fields']   = 'all';

		$terms = get_terms( $args );
		return Objects::get_instance()->get_objects( is_array( $terms ) ? $terms : array() );
	}

	/**
	 * Get the post type names that support this taxonomy.
	 *
	 * @return string[]
	 */
	public function get_supported_post_types(): array {
		global $wp_taxonomies;
		return ( is_array( $wp_taxonomies[ $this->_taxonomy->name ] ) ) ? $wp_taxonomies[ $this->_taxonomy->name ]->object_type : array();
	}
}
