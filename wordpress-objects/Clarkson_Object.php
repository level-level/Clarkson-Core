<?php
/**
 * Clarkson Object.
 *
 * @package CLARKSON\Objects
 */

/**
 * Class Clarkson_Object.
 */
class Clarkson_Object implements \JsonSerializable {

	/**
	 * Set type is 'post'.
	 *
	 * @var string
	 */
	public static $type = 'post';

	/**
	 * Define $_post.
	 *
	 * @var array|null|WP_Post
	 */
	protected $_post;

	/**
	 * Define $posts.
	 *
	 * @var $posts
	 */
	protected static $posts;

	/**
	 * Clarkson_Object constructor.
	 *
	 * @param WP_Post $post Post object.
	 *
	 * @throws Exception    Error message.
	 */
	public function __construct( $post ) {
		if ( is_a( $post, 'WP_Post' ) ) {
			$this->_post = $post;
		} else {
			user_error( esc_html__( "Deprecated __construct called with an ID. Use '::get(post)' instead.", 'clarkson-core' ), E_USER_DEPRECATED );

			if ( empty( $post ) ) {
				throw new Exception( '$post empty' );
			}

			$this->_post = get_post( $post );
		}
	}

	/**
	 * Check if name is not a wp_post object property.
	 *
	 * @param string $name Post name to check.
	 *
	 * @throws Exception Error message.
	 */
	public function __get( $name ) {
		if ( in_array( $name, array( 'post_name', 'post_title', 'ID', 'post_author', 'post_type', 'post_status' ), true ) ) {
			throw new Exception( 'Trying to access wp_post object properties from Post object' );
		}
	}

	/**
	 * Get post data by id.
	 *
	 * @param  int $id Post id.
	 *
	 * @return array|null    Post data.
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
	 * Get multiple posts, without pagination.
	 *
	 * @param array $args Post arguments.
	 *
	 * @return array      Post data.
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
		return array_map(
			function( $post ) use ( $class ) {
					return new $class( $post );
			}, $query->posts
		);
	}

	/**
	 * Get one post.
	 *
	 * @param array $args Post arguments.
	 *
	 * @return array      Post data.
	 */
	public static function get_one( $args = array() ) {
		$args['posts_per_page'] = 1;
		$one                    = static::get_many( $args );
		return array_shift( $one );
	}

	/**
	 * Refresh post data, clear cache.
	 */
	public function _refresh_data() {
		clean_post_cache( $this->_post->ID );
		$this->_post = get_post( $this->_post->ID );
	}

	/**
	 * Get the id of a post.
	 *
	 * @return int The post id.
	 */
	public function get_id() {
		return $this->_post->ID;
	}

	/**
	 * Get the parent of the post, if any.
	 *
	 * @return array|null Parent.
	 */
	public function get_parent() {
		if ( $this->_post->post_parent ) {
			return self::get( $this->_post->post_parent );
		}

		return null;
	}

	/**
	 * Get the children of the post by id.
	 *
	 * See issue: https://github.com/level-level/Clarkson-Core/issues/121
	 *
	 * @return array Children
	 */
	public function get_children() {
		return get_children( 'post_parent=' . $this->get_id() );
	}

	/**
	 * Get the attachments for the post.
	 *
	 * See issue: https://github.com/level-level/Clarkson-Core/issues/121
	 *
	 * @return array Attachments.
	 */
	public function get_attachments() {
		return get_children( 'post_type=attachment&post_parent=' . $this->get_id() );
	}

	/**
	 * Check if the post has a thumbnail.
	 *
	 * @return bool
	 */
	public function has_thumbnail() {
		return has_post_thumbnail( $this->get_id() );
	}

	/**
	 * Get the thumbnail HTML for the post.
	 *
	 * @param array|string $size Thumbnail size.
	 * @param array|string $attr Thumbnail attributes.
	 *
	 * @return string
	 */
	public function get_thumbnail( $size = 'thumbnail', $attr = '' ) {
		return get_the_post_thumbnail( $this->get_id(), $size, $attr );
	}

	/**
	 * Get the thumbnail id by post id.
	 *
	 * @return string The ID of the post, or an empty string on failure.
	 */
	public function get_thumbnail_id() {
		return get_post_thumbnail_id( $this->get_id() );
	}

	/**
	 * Get the date the post was created in post_date_gmt format.
	 *
	 * @param  string $format Date format.
	 *
	 * @return string
	 */
	public function get_date( $format = 'U' ) {
		return date( $format, strtotime( $this->_post->post_date_gmt ) );
	}

	/**
	 * Get the date in localized format.
	 *
	 * @param string $format Date format.
	 * @param bool   $gmt   Whether to convert to GMT for time.
	 *
	 * @return string
	 */
	public function get_date_i18n( $format = 'U', $gmt = false ) {
		return date_i18n( $format, strtotime( $this->_post->post_date_gmt, $gmt ) );
	}

	/**
	 * Set the post date/time of the post.
	 *
	 * @param int $time PHP timestamp.
	 */
	public function set_date( $time ) {
		$this->_post->post_data = date( 'Y-m-d H:i:s', $time );

		wp_update_post(
			array(
				'ID'        => $this->get_id(),
				'post_date' => $this->_post->post_data,
			)
		);
	}

	/**
	 * Get the local date the post was created.
	 *
	 * @param string $format Date format.
	 *
	 * @return string
	 */
	public function get_local_date( $format = 'U' ) {
		return date( $format, strtotime( $this->_post->post_date ) );
	}

	/**
	 * Get the post meta data by meta key.
	 *
	 * @param string $key    Post meta key.
	 * @param bool   $single Post meta data.
	 *
	 * @return array|string
	 */
	public function get_meta( $key, $single = false ) {
		return get_post_meta( $this->get_id(), $key, $single );
	}

	/**
	 * Update the post meta data.
	 *
	 * @param string       $key   Post meta key.
	 * @param string|array $value New post meta data.
	 *
	 * @return bool|int
	 */
	public function update_meta( $key, $value ) {
		return update_post_meta( $this->get_id(), $key, $value );
	}

	/**
	 * Add post meta data.
	 *
	 * @param string       $key   Post meta key.
	 * @param string|array $value New post meta data.
	 *
	 * @return false|int
	 */
	public function add_meta( $key, $value ) {
		return add_post_meta( $this->get_id(), $key, $value );
	}

	/**
	 * Delete post meta data.
	 *
	 * @param string $key    Post meta key.
	 * @param null   $value  Null, as the value will be deleted.
	 *
	 * @return bool
	 */
	public function delete_meta( $key, $value = null ) {
		return delete_post_meta( $this->get_id(), $key, $value );
	}

	/**
	 * Delete post.
	 */
	public function delete() {
		wp_delete_post( $this->get_id(), true );
	}

	/**
	 * Get the title of a post  by id.
	 *
	 * @return string Post title.
	 */
	public function get_title() {
		return get_the_title( $this->get_id() );
	}

	/**
	 * Get post name (slug) by id.
	 *
	 * @return string Post name.
	 */
	public function get_post_name() {
		return $this->_post->post_name;
	}

	/**
	 * Get the post content.
	 *
	 * See: https://github.com/level-level/Clarkson-Core/issues/122
	 *
	 * @return string
	 */
	public function get_content() {
		if ( ! isset( $this->_content ) ) {
			setup_postdata( $this->_post );

			// Post stays empty when wp_query 404 is set, resulting in a warning from the_content.
			global $post;
			if ( null === $post ) {
				$post = $this->_post;
			}

			ob_start();
			the_content();

			$this->_content = ob_get_clean();
			wp_reset_postdata();
		}

		return $this->_content;
	}

	/**
	 * Get the raw post content.
	 *
	 * @return string
	 */
	public function get_raw_content() {
		return $this->_post->post_content;
	}

	/**
	 * Get the post author id.
	 *
	 * @return null|string
	 */
	public function get_author_id() {
		if ( $this->_post->post_author ) {
			return $this->_post->post_author;
		}

		return null;
	}

	/**
	 * Get the post author data by id.
	 *
	 * @return null|object
	 */
	public function get_author() {

		if ( $this->_post->post_author ) {
			return Clarkson_User::get( $this->_post->post_author );
		}

		return null;
	}

	/**
	 * Get the post permalink.
	 *
	 * @return false|string Post url.
	 */
	public function get_permalink() {
		return get_permalink( $this->get_id() );
	}

	/**
	 * Get the post excerpt.
	 *
	 * See the issue: https://github.com/level-level/Clarkson-Core/issues/122
	 *
	 * @return string
	 */
	public function get_excerpt() {
		if ( ! isset( $this->_excerpt ) ) {
			global $post;
			if ( ! empty( $post ) ) {
				$oldpost = clone $post;
			} else {
				$oldpost = null;
			}
			$post = $this->_post; // Set post to what we are asking the excerpt for.
			setup_postdata( $this->_post );
			ob_start();
			the_excerpt();
			$this->_excerpt = ob_get_clean();
			wp_reset_postdata();
			$post = $oldpost; // Reset global post.
		}
		return $this->_excerpt;
	}

	/**
	 * Get document count.
	 */
	public function get_comment_count() {
	}

	/**
	 * Get the post's post type.
	 *
	 * @return string
	 */
	public function get_post_type() {
		return $this->_post->post_type;
	}

	/**
	 * Get the post's post status.
	 *
	 * @return string
	 */
	public function get_status() {
		return $this->_post->post_status;
	}

	/**
	 * Update the post's post status.
	 *
	 * @param string $status New post status.
	 */
	public function set_status( $status ) {
		$this->_post->post_status = $status;

		wp_update_post(
			array(
				'ID'          => $this->get_id(),
				'post_status' => $status,
			)
		);
	}

	/**
	 * Add post comment.
	 *
	 * @param string $comment_text Comment text.
	 * @param int    $user_id      User id.
	 *
	 * @return false|int
	 *
	 * @throws Exception Error message.
	 */
	public function add_comment( $comment_text, $user_id ) {
		if ( empty( $comment_text ) || empty( $user_id ) ) {
			throw new Exception( 'Not enough data' );
		}

		$comment = array(
			'comment_post_ID' => $this->get_id(),
			'user_id'         => $user_id,
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
	 *
	 * @return array|WP_Error  List of post tags or a WP_Error.
	 */
	public function get_terms( $taxonomy, $args = array() ) {
		$cc         = Clarkson_Core::get_instance();
		$class_name = $cc->autoloader->sanitize_object_name( $taxonomy );

		$terms = wp_get_post_terms( $this->get_id(), $taxonomy, $args );

		if ( is_wp_error( $terms ) ) {
			user_error( esc_html( $terms->get_error_message() ) );
			return $terms;
		}

		return array_map(
			function( $term ) use ( $taxonomy, $class_name ) {
				try {
					if ( is_object( $term ) ) {
						// Check if there is a Custom Taxonomy class.
						if ( class_exists( $class_name ) ) {
							return call_user_func( array( $class_name, 'get_by_id' ), $term->term_id, $taxonomy );
						}
						// Else return a default Clarkson Term.
						return Clarkson_Term::get_by_id( $term->term_id, $taxonomy );
					}
					return $term;
				} catch ( Exception $e ) {
					return null;
				}
			},
			$terms,
			array( $class_name )
		);
	}

	/**
	 * Add a single term to a post.
	 *
	 * @param object $term    Term data.
	 *
	 * @return array|WP_Error Affected Term IDs.
	 */
	public function add_term( $term ) {
		return wp_set_object_terms( $this->get_id(), $term->get_id(), $term->get_taxonomy(), true );
	}

	/**
	 * Bulk add terms to a post.
	 *
	 * @param string           $taxonomy Taxonomy.
	 * @param \Clarkson_Term[] $terms    Terms.
	 * @var   \Clarkson_Term   $term     Term objects.
	 *
	 * @return array|WP_Error            Terms array.
	 */
	public function add_terms( $taxonomy, $terms ) {
		// Filter terms to ensure they are in the correct taxonomy.
		$terms = array_filter(
			$terms, function( $term ) use ( $taxonomy ) {
				return $term->get_taxonomy() === $taxonomy;
			}
		);

		// get array of term IDs.
		$terms = array_map(
			function( $term ) {
					return $term->get_id();
			}, $terms
		);

		return wp_set_object_terms( $this->get_id(), $terms, $taxonomy, true );
	}

	/**
	 * Reset terms.
	 * Will delete all terms for a given taxonomy.
	 * Adds all passed terms or overwrites existing terms.
	 *
	 * @param string           $taxonomy Taxonomy.
	 * @param \Clarkson_Term[] $terms    Terms.
	 * @var   \Clarkson_Term   $term     Term objects.
	 *
	 * @return array|WP_Error             Affected Term IDs.
	 */
	public function reset_terms( $taxonomy, $terms ) {
		// Filter terms to ensure they are in the correct taxonomy.
		$terms = array_filter(
			$terms, function( $term ) use ( $taxonomy ) {
				return $term->get_taxonomy() === $taxonomy;
			}
		);

		// get array of term IDs.
		$terms = array_map(
			function( $term ) {
					return $term->get_id();
			}, $terms
		);

		return wp_set_object_terms( $this->get_id(), $terms, $taxonomy, false );
	}

	/**
	 * Remove a post term.
	 *
	 * @param  \Clarkson_Term $term Post term.
	 *
	 * @return bool|WP_Error        True on success, false or WP_Error on failure.
	 */
	public function remove_term( $term ) {
		return wp_remove_object_terms( $this->get_id(), $term->get_id(), $term->get_taxonomy() );
	}

	/**
	 * Is post associated with term?
	 *
	 * @param  \Clarkson_Term $term Post term.
	 * @return boolean
	 */
	public function has_term( $term ) {
		return has_term( $term->get_slug(), $term->get_taxonomy(), $this->get_id() );
	}

	/**
	 * Return a set of data when calling the /json endpoint.
	 * If you want something else, then just overwrite it in your own WordPress object.
	 *
	 * We can't just return $this->_post, because these values will only return raw unfiltered data.
	 */
	public function jsonSerialize() {
		$data['id']        = $this->get_id();
		$data['link']      = $this->get_permalink();
		$data['slug']      = $this->get_post_name();
		$data['date']      = $this->get_date( 'c' );
		$data['title']     = $this->get_title();
		$data['content']   = $this->get_content();
		$data['excerpt']   = $this->get_excerpt();
		$data['post_type'] = $this->get_post_type();
		$data['status']    = $this->get_status();
		$data['thumbnail'] = $this->get_thumbnail();

		$data['author'] = null;

		$author_id = $this->get_author_id();
		if ( ! empty( $author_id ) ) {
			$data['author'] = array(
				'id'           => $author_id,
				'display_name' => $this->get_author()->get_display_name(),
			);
		}

		return $data;
	}

	/**
	 * Create serialized json file.
	 *
	 * @return mixed
	 */
	public function get_json() {
		user_error( 'Deprecated directly calling get_json. Just json_encode the object itself, because the Clarkson_Object implements JsonSerializable.', E_USER_DEPRECATED );
		return $this->jsonSerialize();
	}

}
