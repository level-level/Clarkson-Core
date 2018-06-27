<?php
/**
 * Clarkson Term.
 *
 * @package CLARKSON\Objects
 */

/**
 * Clarkson Term class.
 */
class Clarkson_Term {

	/**
	 * Define $_term.
	 *
	 * @var array|null|WP_Error|WP_Term
	 */
	public $_term;

	/**
	 * Clarkson_Term provides extra functions to retrieve term and taxonomy data.
	 *
	 * @var null
	 */
	protected static $taxonomy = null;

	/**
	 * Get term by name.
	 *
	 * @param string      $name     Term name.
	 * @param null|string $taxonomy Taxonomy.
	 *
	 * @return bool|object          Term object or false.
	 */
	public static function get_by_name( $name, $taxonomy = null ) {
		$term  = get_term_by( 'name', $name, $taxonomy ? $taxonomy : static::$taxonomy );
		$class = get_called_class();
		return new $class( $term->term_id, $taxonomy ? $taxonomy : static::$taxonomy );
	}

	/**
	 * Get term by slug.
	 *
	 * @param string      $slug     Term slug.
	 * @param null|string $taxonomy Taxonomy.
	 *
	 * @return bool|object          Term object or false.
	 */
	public static function get_by_slug( $slug, $taxonomy = null ) {
		$term  = get_term_by( 'slug', $slug, $taxonomy ? $taxonomy : static::$taxonomy );
		$class = get_called_class();
		return new $class( $term->term_id, $taxonomy ? $taxonomy : static::$taxonomy );
	}

	/**
	 * Get term by id.
	 *
	 * @param int         $term_id  Term id.
	 * @param null|string $taxonomy Taxonomy.
	 *
	 * @return bool|object          Term object or false.
	 */
	public static function get_by_id( $term_id, $taxonomy = null ) {
		$term  = get_term_by( 'id', $term_id, $taxonomy ? $taxonomy : static::$taxonomy );
		$class = get_called_class();
		return new $class( $term->term_id, $taxonomy ? $taxonomy : static::$taxonomy );
	}

	/**
	 * Clarkson_Term constructor.
	 *
	 * @param int         $term_id  Term id.
	 * @param null|string $taxonomy Taxonomy.
	 *
	 * @throws Exception  Error message.
	 */
	public function __construct( $term_id, $taxonomy = null ) {
		$taxonomy = $taxonomy ? $taxonomy : static::$taxonomy;
		if ( empty( $term_id ) || ! $taxonomy ) {
			throw new Exception( $term_id . ' or ' . $taxonomy . ' empty' );
		}
		$this->_term = get_term( (int) $term_id, $taxonomy );
		if ( ! $this->_term ) {
			throw new Exception( 'Term not found' );
		}
	}

	/**
	 * Check if an existing field is requested.
	 *
	 * @param string $name Field to search by.
	 *
	 * @throws Exception       Error message.
	 */
	public function __get( $name ) {
		if ( in_array( $name, array( 'term_id', 'name', 'slug', 'taxonomy' ), true ) ) {
			throw new Exception( 'Trying to access wp_term object properties from Term object' );
		}
	}

	/**
	 * Check if this term was used in the global $wp_query.
	 *
	 * @return bool
	 */
	public function is_queried_object() {
		global $wp_query;
		$term_or_taxonomy = $this->_term;

		// Check if $this->_term is a string or an object.
		if ( is_string( $term_or_taxonomy ) ) {
			if ( $wp_query->tax_query ) {
				foreach ( $wp_query->tax_query->queries as $query ) {
					if ( $query['taxonomy'] === $term_or_taxonomy ) {
						return true;
					}
				}
			}

			if ( ! empty( $wp_query->_post_parent_query ) ) {
				foreach ( $wp_query->_post_parent_query->tax_query->queries as $query ) {
					if ( $query['taxonomy'] === $term_or_taxonomy ) {
						return true;
					}
				}
			}
		} elseif ( is_object( $term_or_taxonomy ) ) {
			foreach ( $wp_query->tax_query->queries as $query ) {
				if ( 'slug' === $query['field'] && in_array( $term_or_taxonomy->slug, $query['terms'], true ) ) {
					return true;
				}

				if ( in_array( $term_or_taxonomy->term_id, $query['terms'], true ) ) {
					return true;
				}
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
	 *
	 * @return null|object Term parent object.
	 */
	public function get_parent() {
		if ( $this->_term->parent ) {
			$class = get_called_class();
			return new $class( $this->_term->parent, $this->get_taxonomy() );
		}
		return null;
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
	 * @return int|WP_Error|bool
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
	 * @return bool|int|WP_Error
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
	public function set_name( $name ) {
		wp_update_term(
			$this->get_id(), $this->get_taxonomy(), array(
				'name' => $name,
			)
		);
	}

	/**
	 * Get the term data.
	 *
	 * @return array|null|WP_Error|WP_Term
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
	 * @return string|WP_Error Term permalink.
	 */
	public function get_permalink() {
		return get_term_link( $this->get_term(), $this->get_taxonomy() );
	}

}
