<?php

class Clarkson_Object implements \JsonSerializable {

	public static $type = 'post';

	protected $_post;

	protected static $posts;

	/**
* @param WP_Post $post
* @throws Exception
*/
	public function __construct( $post ) {
		if (is_a( $post, 'WP_Post' )) {
			$this->_post = $post;
		} else {
			trigger_error( "Deprecated __construct called with an ID. Use `::get($post)` instead.", E_USER_DEPRECATED );
			if ( empty( $post ) ) {
				throw new Exception( '$post empty' );
			}

			$this->_post = get_post( $post );
		}
	}

	public function __get( $name ) {
		if ( in_array( $name, array( 'post_name', 'post_title', 'ID', 'post_author', 'post_type', 'post_status' ) ) ) {
			throw new Exception( 'Trying to access wp_post object properties from Post object' );
		}
	}

	/**
* Get a post
*
* @param  int $id
* @return Post|null if not exists
*/
	public static function get( $id ) {
		if ( ! isset( static::$posts[ $id ] ) ) {
			$class = get_called_class();

			try {
				static::$posts[ $id ] = new $class( get_post( $id ) );
			} catch ( Exception $e ) {
				static::$posts[ $id ] = null;
			}

		}

		return static::$posts[ $id ];
	}

	/**
* Get many posts from a query
*
* @param  array $args
* @return Post[]
*/
	public static function get_many( $args ) {
		$args['post_type']     = static::$type;
		$args['no_found_rows'] = true;

		$query = new \WP_Query( $args );

		$class = get_called_class();

		/**
* PHP binds the closure to the "self" class, not "static", so
* "static" refers to the "self" inside the closure which isn't
* what we want.
*/
		return array_map( function( $post ) use ( $class ) {
			return new $class( $post );
		}, $query->posts );
	}

	/**
* Get an post from args
*
* @param  array $args
* @return Post
*/
	public static function get_one( $args ) {
		$args['posts_per_page'] = 1;
		return array_shift( ( static::get_many( $args ) ) );
	}

	public function _refresh_data() {
		clean_post_cache( $this->_post->ID );
		$this->_post = get_post( $this->_post->ID );
	}

	/**
* @return int Get the ID of the post
*/
	public function get_id() {
		return $this->_post->ID;
	}

	/**
* Get the parent of the post, if any
*
* @return Post|null
*/
	public function get_parent() {
		if ( $this->_post->post_parent ) {
			return self::get( $this->_post->post_parent );
		}

		return null;
	}

	/**
* Get the children of the post (if any)
*
* @return StdClass[]
*/
	public function get_children() {
		return get_children( 'post_parent=' . $this->get_id() );
	}

	/**
* Get the attachments for the post
*
* @return StdClass[]
*/
	public function get_attachments() {
		return get_children( 'post_type=attachment&post_parent=' . $this->get_id() );
	}

	/**
* Check if the post has a thumbnail
*
* @return bool
*/
	public function has_thumbnail() {
		return has_post_thumbnail( $this->get_id() );
	}

	/**
* Get the thumbnail HTML for the post
*
* @param array|string $size
* @return string
*/
	public function get_thumbnail( $size = 'thumbnail', $attr = '' ) {
		return get_the_post_thumbnail( $this->get_id(), $size, $attr );
	}

	public function get_thumbnail_id() {
		return get_post_thumbnail_id( $this->get_id() );
	}

	/**
* Get the date the post was created
*
* @param string $format
* @return string
*/
	public function get_date( $format = 'U' ) {
		return date( $format, strtotime( $this->_post->post_date_gmt ) );
	}

	/**
* Get the date in localized format
*
* @param string $format
* @param bool $gmt
* @return string
*/
	public function get_date_i18n( $format = 'U', $gmt = false ) {
		return date_i18n( $format, strtotime( $this->_post->post_date_gmt, $gmt ) );
	}

	/**
* Set the post date of the post
*
* @param int $time PHP timestamp
*/
	public function set_date( $time ) {
		$this->_post->post_data = date( 'Y-m-d H:i:s', $time );

		wp_update_post( array(
				'ID' => $this->get_id(),
				'post_date' => $this->_post->post_data,
		) );
	}

	/**
* Get the local date the post was created
*
* @param string $format
* @return string
*/
	public function get_local_date( $format = 'U' ) {
		return date( $format, strtotime( $this->_post->post_date ) );
	}

	public function get_meta( $key, $single = false ) {
		return get_post_meta( $this->get_id(), $key, $single );
	}

	public function update_meta( $key, $value ) {
		return update_post_meta( $this->get_id(), $key, $value );
	}

	public function add_meta( $key, $value ) {
		return add_post_meta( $this->get_id(), $key, $value );
	}

	public function delete_meta( $key, $value = null ) {
		return delete_post_meta( $this->get_id(), $key, $value );
	}

	public function delete() {
		wp_delete_post( $this->get_id(), true );
	}

	public function get_title() {
		return get_the_title( $this->get_id() );
	}

	public function get_post_name() {
		return $this->_post->post_name;
	}

	public function get_content() {
		if ( ! isset( $this->_content ) ) {
			setup_postdata( $this->_post );

			// Post stays empty when wp_query 404 is set, resulting in a warning from the_content
			global $post;
			if ( null === $post  ) {
				$post = $this->_post;
			}

			ob_start();
			the_content();

			$this->_content = ob_get_clean();
			wp_reset_postdata();
		}

		return $this->_content;
	}

	public function get_raw_content() {
		return $this->_post->post_content;
	}

	public function get_author_id() {
		if ( $this->_post->post_author ) {
			return $this->_post->post_author;
		}

		return null;
	}

	public function get_author() {

		if ( $this->_post->post_author ) {
			return Clarkson_User::get( $this->_post->post_author );
		}

		return null;
	}

	public function get_permalink() {
		return get_permalink( $this->get_id() );
	}

	public function get_excerpt() {
		if ( ! isset( $this->_excerpt ) ) {
			setup_postdata( $this->_post );

			ob_start();
			the_excerpt();

			$this->_excerpt = ob_get_clean();
			wp_reset_postdata();
		}

		return $this->_excerpt;

	}

	public function get_comment_count() {
	}

	public function get_post_type() {
		return $this->_post->post_type;
	}

	public function get_status() {
		return $this->_post->post_status;
	}

	public function set_status( $status ) {
		$this->_post->post_status = $status;

		wp_update_post( array(
			'ID' => $this->get_id(),
			'post_status' => $status,
		) );
	}

	/**
* @param $comment_text
* @param $user_id
* @return int
* @throws Exception
*/
	public function add_comment( $comment_text, $user_id ) {
		if ( empty( $comment_text ) || empty( $user_id ) ) {
			throw new Exception( 'Not enough data' );
		}

		$comment = array(
			'comment_post_ID' => $this->get_id(),
			'user_id' => $user_id,
			'comment_content' => esc_attr( $comment_text ),
		);

		$result = wp_insert_comment( $comment );

		if ( ! is_numeric( $result ) ) {
			throw new Exception( 'wp_insert_post failed: ' . $result );
		}

		return $result;
	}

	/**
* Retrieve the terms for a post.
*
* @param string $taxonomy Optional. The taxonomy for which to retrieve terms. Default 'post_tag'.
* @param array  $args     Optional. {@link wp_get_object_terms()} arguments. Default empty array.
* @return array List of post tags.
*/
	public function get_terms( $taxonomy, $args = array() ) {
		return array_map(
			function( $term ) use ( $taxonomy ) {
				try {
					if ( is_object( $term ) ) {
						// Check if there is a Custom Taxonomy class
						if ( class_exists( $taxonomy ) ) {
							return call_user_func( array( $taxonomy, 'get_by_id' ) , $term->term_id, $taxonomy );
						}
						// Else return a default Clarkson Term
						return Clarkson_Term::get_by_id( $term->term_id, $taxonomy );
					}
					return $term;
				} catch ( Exception $e ) {
					return null;
				}
			},
			wp_get_post_terms( $this->get_id(), $taxonomy, $args )
		);
	}

	/**
* Add a single term to a post.
*
* @param Term $term Term Object
* @return array|WP_Error Affected Term IDs.
*/
	public function add_term( $term ) {
		return wp_set_object_terms( $this->get_id(), $term->get_id(), $term->get_taxonomy(), true );
	}

	/**
* Bulk add terms to a post.
*
* @param string $taxonomy Taxonomy
* @param array  $terms    Term objects
* @return array
*/
	public function add_terms( $taxonomy, $terms ) {
		// Filter terms to ensure they are in the correct taxonomy
		$terms = array_filter( $terms, function( $term ) use ( $taxonomy ) {
			return $term->get_taxonomy() === $taxonomy;
		} );

		// get array of term IDs
		$terms = array_map( function( $term ) {
			return $term->get_id();
		}, $terms );

		return wp_set_object_terms( $this->get_id(), $terms, $taxonomy, true );
	}

	/**
* Reset terms.
* Will delete all terms for a given taxonomy.
* Adds all passed terms or  overwrite existing terms,
*
* @param  array        $terms Term
* @param  string|array $terms Tax
* @return array|WP_Error Affected Term IDs.
*/
	public function reset_terms( $taxonomy, $terms = array() ) {
		// Filter terms to ensure they are in the correct taxonomy
		$terms = array_filter( $terms, function( $term ) use ( $taxonomy ) {
			return $term->get_taxonomy() === $taxonomy;
		} );

		// get array of term IDs
		$terms = array_map( function( $term ) {
			return $term->get_id();
		}, $terms );

		return wp_set_object_terms( $this->get_id(), $terms, $taxonomy, false );
	}

	/**
* Remove a post term.
*
* @param  Term $term .
* @return bool|WP_Error True on success, false or WP_Error on failure.
*/
	public function remove_term( $term ) {
		return wp_remove_object_terms( $this->get_id(), $term->get_id(), $term->get_taxonomy() );
	}

	/**
* Is post associated with term?
*
* @param  Term $term
* @return boolean
*/
	public function has_term( $term ) {
		return has_term( $term->get_slug(), $term->get_taxonomy(), $this->get_id() );
	}

	/**
* Return a set of data when calling the /json endpoint
* If you want something else, then just overwrite it in your own WordPress object
*
* We can't just return $this->_post, because these values will only return raw unfilter data.
*/
	public function jsonSerialize() {
		$data['id'] 		= $this->get_id();
		$data['link'] 		= $this->get_permalink();
		$data['slug'] 		= $this->get_post_name();
		$data['date']		= $this->get_date( 'c' );
		$data['title'] 		= $this->get_title();
		$data['content'] 	= $this->get_content();
		$data['excerpt'] 	= $this->get_excerpt();
		$data['post_type'] 	= $this->get_post_type();
		$data['status'] 	= $this->get_status();
		$data['thumbnail'] 	= $this->get_thumbnail();

		$data['author']		= null;
		$author_id = $this->get_author_id();
		if ( ! empty( $author_id ) ) {
			$data['author'] = array(
				'id' => $author_id,
				'display_name' => $this->get_author()->get_display_name(),
			);
		}

		return $data;
	}

	public function get_json() {
		trigger_error( 'Deprecated directly calling get_json. Just json_encode the object itself, because the Clarkson_Object implements JsonSerializable.', E_USER_DEPRECATED );
		return $this->jsonSerialize();
	}

}
