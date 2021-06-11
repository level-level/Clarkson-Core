<?php
/**
 * Clarkson Term.
 */

namespace Clarkson_Core\WordPress_Object;

use Clarkson_Core\Objects;

/**
 * Object oriented wrapper for WP_Term objects.
 */
class Clarkson_Term {

	/**
	 * Define $_term.
	 *
	 * @var \WP_Term
	 * @internal
	 */
	public $_term;

	/**
	 * Clarkson_Term provides extra functions to retrieve term and taxonomy data.
	 *
	 * @var string
	 */
	protected static $taxonomy = '';

	/**
	 * Get term by name.
	 *
	 * @param string      $name     Term name.
	 * @param null|string $taxonomy Taxonomy.
	 */
	public static function get_by_name( $name, $taxonomy = null ): ?Clarkson_Term {
		$taxonomy = $taxonomy ? $taxonomy : static::$taxonomy;
		$term     = get_term_by( 'name', $name, $taxonomy );
		if ( ! $term instanceof \WP_Term ) {
			return null;
		}
		return \Clarkson_Core\Objects::get_instance()->get_term( $term );
	}

	/**
	 * Get term by slug.
	 *
	 * @param string      $slug     Term slug.
	 * @param null|string $taxonomy Taxonomy.
	 *
	 * @return null|Clarkson_Term Term object.
	 */
	public static function get_by_slug( $slug, $taxonomy = null ): ?Clarkson_Term {
		$taxonomy = $taxonomy ? $taxonomy : static::$taxonomy;
		$term     = get_term_by( 'slug', $slug, $taxonomy );
		if ( ! $term instanceof \WP_Term ) {
			return null;
		}
		return \Clarkson_Core\Objects::get_instance()->get_term( $term );
	}

	/**
	 * Get term by id.
	 *
	 * @param int         $term_id  Term id.
	 * @param null|string $taxonomy Taxonomy.
	 */
	public static function get_by_id( $term_id, $taxonomy = null ): ?Clarkson_Term {
		$taxonomy = $taxonomy ? $taxonomy : static::$taxonomy;
		$term     = get_term_by( 'id', $term_id, $taxonomy );
		if ( ! $term instanceof \WP_Term ) {
			return null;
		}
		return \Clarkson_Core\Objects::get_instance()->get_term( $term );
	}

	/**
	 * Alias of `get_by_id()`.
	 */
	public static function get( int $term_id ): ?Clarkson_Term {
		return static::get_by_id( $term_id );
	}

	/**
	 * Get multiple terms as Clarkson Objects.
	 *
	 * @param array $args Term query arguments. {@link https://developer.wordpress.org/reference/classes/WP_Term_Query/__construct/}
	 * @param mixed $term_query The $term_query is passed by reference and will be filled with the WP_Term_Query that produced these results.
	 *
	 * @return Clarkson_Term[]
	 *
	 * @example
	 * \Clarkson_Term::get_many( array( 'number' => 5 ), $term_query );
	 */
	public static function get_many( array $args, &$term_query = null ):array {
		$args['taxonomy'] = static::$taxonomy;
		$args['fields']   = 'all';

		$query      = new \WP_Term_Query( $args );
		$objects    = Objects::get_instance()->get_terms( $query->get_terms() );
		$term_query = $query;
		return $objects;
	}

	/**
	 * Gets the first result from a `::get_many()` query.
	 *
	 * @param array $args Term query arguments. {@link https://developer.wordpress.org/reference/classes/WP_Term_Query/__construct/}
	 */
	public static function get_one( array $args = array() ):?Clarkson_Term {
		$args['number'] = 1;
		$one            = static::get_many( $args );
		return array_shift( $one );
	}

	/**
	 * Clarkson_Term constructor.
	 */
	public function __construct( \WP_Term $term ) {
		$this->_term = $term;
	}

	/**
	 * Check if an existing field is requested.
	 *
	 * @param string $name Field to search by.
	 *
	 * @throws \Exception       Error message.
	 */
	public function __get( $name ) {
		if ( in_array( $name, array( 'term_id', 'name', 'slug', 'taxonomy' ), true ) ) {
			throw new \Exception( 'Trying to access wp_term object properties from Term object' );
		}
	}

	/**
	 * Check if this term was used in the global $wp_query.
	 *
	 * @return bool
	 */
	public function is_queried_object() {
		global $wp_query;

		foreach ( $wp_query->tax_query->queries as $query ) {
			if ( 'slug' === $query['field'] && in_array( $this->_term->slug, $query['terms'], true ) ) {
				return true;
			}

			if ( in_array( $this->_term->term_id, $query['terms'], true ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get the id of the term.
	 *
	 * @return int Term id.
	 */
	public function get_id() {
		return $this->_term->term_id;
	}

	/**
	 * Get the term parent.
	 */
	public function get_parent(): ?Clarkson_Term {
		$parent = null;
		if ( $this->_term->parent ) {
			try {
				$parent = static::get_by_id( $this->_term->parent, $this->get_taxonomy() );
			} catch ( \Exception $e ) {
				$parent = null;
			}
		}
		return $parent;
	}

	/**
	 * Get the taxonomy.
	 *
	 * @return string Taxonomy.
	 */
	public function get_taxonomy() {
		return $this->_term->taxonomy;
	}

	/**
	 * Get term meta data by id.
	 *
	 * @param string $key     Meta key.
	 * @param bool   $single  Single or array.
	 *
	 * @return string|array          Meta data.
	 */
	public function get_meta( $key, $single = false ) {
		return get_term_meta( $this->get_id(), $key, $single );
	}

	/**
	 * Update meta data.
	 *
	 * @param string $key   Meta key.
	 * @param mixed  $value New meta data.
	 *
	 * @return int|\WP_Error|bool
	 */
	public function update_meta( $key, $value ) {
		return update_term_meta( $this->get_id(), $key, $value );
	}

	/**
	 * Update meta data.
	 *
	 * @param string $key   Meta key.
	 * @param mixed  $value New meta data.
	 *
	 * @return bool|int|\WP_Error
	 */
	public function add_meta( $key, $value ) {
		return add_term_meta( $this->get_id(), $key, $value );
	}

	/**
	 * Delete meta data.
	 *
	 * @param string $key   Meta key.
	 * @param null   $value Meta value = null because to be deleted.
	 *
	 * @return bool
	 */
	public function delete_meta( $key, $value = null ) {
		return delete_term_meta( $this->get_id(), $key, $value );
	}

	/**
	 * Get the term slug.
	 *
	 * @return string Term slug.
	 */
	public function get_slug() {
		return $this->_term->slug;
	}

	/**
	 * Get the term name.
	 *
	 * @return string Term name.
	 */
	public function get_name() {
		return $this->_term->name;
	}

	/**
	 * Get the term description.
	 *
	 * @return string Term description.
	 */
	public function get_description() {
		return $this->_term->description;
	}

	/**
	 * Update term name.
	 *
	 * @param string $name New term name.
	 */
	public function set_name( $name ):void {
		wp_update_term(
			$this->get_id(),
			$this->get_taxonomy(),
			array(
				'name' => $name,
			)
		);
	}

	/**
	 * Get the term data.
	 *
	 * @return \WP_Term
	 */
	public function get_term() {
		return $this->_term;
	}

	/**
	 * Get the taxonomy id.
	 *
	 * @return int Taxonomy id.
	 */
	public function get_term_taxonomy_id() {
		return $this->_term->term_taxonomy_id;
	}

	/**
	 * Get the term permalink.
	 *
	 * @return string|\WP_Error Term permalink.
	 */
	public function get_permalink() {
		return get_term_link( $this->get_term(), $this->get_taxonomy() );
	}

}
