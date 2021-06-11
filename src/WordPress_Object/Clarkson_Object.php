<?php
/**
 * Clarkson Object.
 */

namespace Clarkson_Core\WordPress_Object;

use Clarkson_Core\Objects;

/**
 * Object oriented implementation of WordPress post objects.
 */
class Clarkson_Object implements \JsonSerializable {

	/**
	 * The string here is the post type name used by `::get_many()`.
	 *
	 * @var string
	 */
	public static $type = 'post';

	/**
	 * Define $_post.
	 *
	 * @var \WP_Post
	 */
	protected $_post;

	/**
	 * Microcache for parsed post content.
	 *
	 * @var null|string
	 */
	protected $_content;

	/**
	 * Microcache for parsed post excerpt.
	 *
	 * @var null|string
	 */
	protected $_excerpt;

	/**
	 * Clarkson_Object constructor.
	 *
	 * @param \WP_Post $post Post object.
	 */
	public function __construct( \WP_Post $post ) {
		$this->_post = $post;
	}

	/**
	 * Check if name is not a wp_post object property.
	 *
	 * @param string $name Post name to check.
	 *
	 * @throws \Exception Error message.
	 */
	public function __get( $name ) {
		if ( in_array( $name, array( 'post_name', 'post_title', 'ID', 'post_author', 'post_type', 'post_status' ), true ) ) {
			throw new \Exception( 'Trying to access wp_post object properties from Post object' );
		}
	}

	/**
	 * Get the WordPress post object.
	 *
	 * @return \WP_Post The post object.
	 */
	public function get_object(): \WP_Post {
		return $this->_post;
	}

	/**
	 * Clarkson Object for a WP_Post by ID.
	 *
	 * @param  int $id Post id.
	 *
	 * @return Clarkson_Object|null    Post data.
	 */
	public static function get( $id ) {
		$post = get_post( $id );
		if ( ! $post instanceof \WP_Post ) {
			return null;
		}
		return Objects::get_instance()->get_object( $post );
	}

	/**
	 * Get multiple posts, without pagination.
	 *
	 * @param array $args Post query arguments. {@link https://developer.wordpress.org/reference/classes/wp_query/#parameters}
	 * @param mixed $post_query The $post_query is passed by reference and will be filled with the WP_Query that produced these results.
	 *
	 * @return Clarkson_Object[]
	 *
	 * @example
	 * \Clarkson_Object::get_many( array( 'posts_per_page' => 5 ), $post_query );
	 */
	public static function get_many( $args, &$post_query = null ):array {
		$args['post_type']     = static::$type;
		$args['no_found_rows'] = true;
		$args['fields']        = 'all';

		$query      = new \WP_Query( $args );
		$objects    = Objects::get_instance()->get_objects( $query->posts );
		$post_query = $query;
		return $objects;
	}

	/**
	 * Gets the first result from a `::get_many()` query.
	 *
	 * @param array $args Post query arguments. {@link https://developer.wordpress.org/reference/classes/wp_query/#parameters}
	 */
	public static function get_one( $args = array() ):?Clarkson_Object {
		$args['posts_per_page'] = 1;
		$one                    = static::get_many( $args );
		return array_shift( $one );
	}

	/**
	 * Get the id of a post.
	 *
	 * @return int The post id.
	 */
	public function get_id() {
		return $this->_post->ID;
	}

	public function get_template():Clarkson_Template {
		return Objects::get_instance()->get_template( $this->_post );
	}

	/**
	 * Get the parent of the post, if any.
	 *
	 * @return Clarkson_Object|null
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
	 * @param array $args Post arguments
	 *
	 * @return Clarkson_Object[] Children
	 */
	public function get_children( array $args = array() ) {
		$args['post_parent'] = $this->get_id();
		$children            = get_children( $args );
		return Objects::get_instance()->get_objects( $children );
	}

	/**
	 * Get the attachments for the post.
	 *
	 * See issue: https://github.com/level-level/Clarkson-Core/issues/121
	 *
	 * @param array $args Post arguments
	 *
	 * @return Clarkson_Object[] Attachments.
	 */
	public function get_attachments( array $args = array() ) {
		$args['post_type'] = 'attachment';
		return $this->get_children( $args );
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
	 * @return int The ID of the post, 0 on failure.
	 */
	public function get_thumbnail_id() {
		return (int) get_post_thumbnail_id( $this->get_id() );
	}

	/**
	 * Get the date the post was created in post_date_gmt format.
	 *
	 * @param  string $format PHP Date format. {@link https://www.php.net/manual/en/function.date.php}
	 *
	 * @return string
	 */
	public function get_date( $format = 'U' ) {
		return gmdate( $format, strtotime( $this->_post->post_date_gmt ) );
	}

	/**
	 * Get the date in localized format.
	 *
	 * @param string $format Date format. {@link https://wordpress.org/support/article/formatting-date-and-time/}
	 * @param bool   $gmt   Whether to convert to GMT for time.
	 *
	 * @return string
	 */
	public function get_date_i18n( $format = 'U', $gmt = false ) {
		return date_i18n( $format, strtotime( $this->_post->post_date_gmt ), $gmt );
	}

	/**
	 * Set the post date/time of the post.
	 *
	 * @param int $time PHP timestamp.
	 */
	public function set_date( $time ): void {
		$this->_post->post_date = gmdate( 'Y-m-d H:i:s', $time );

		wp_update_post(
			array(
				'ID'        => $this->get_id(),
				'post_date' => $this->_post->post_date,
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
		return gmdate( $format, strtotime( $this->_post->post_date ) );
	}

	/**
	 * Proxy for get_post_meta.
	 *
	 * @param string $key    Post meta key.
	 * @param bool   $single Post meta data.
	 *
	 * @link https://developer.wordpress.org/reference/functions/get_post_meta/
	 *
	 * @return mixed
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
	public function delete():void {
		wp_delete_post( $this->get_id(), true );
	}

	/**
	 * Get the title of a post by id.
	 *
	 * @return string Escaped post title.
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
		global $post;
		if ( ! isset( $this->_content ) ) {
			setup_postdata( $this->_post );

			// Post stays empty when wp_query 404 is set, resulting in a warning from the_content.
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
	 * Get the post author object.
	 */
	public function get_author(): ?Clarkson_User {

		if ( $this->_post->post_author ) {
			return Clarkson_User::get( (int) $this->_post->post_author );
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
		global $post;
		if ( ! isset( $this->_excerpt ) ) {
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
	 * Get the post's post type.
	 */
	public function get_post_type(): string {
		return $this->_post->post_type;
	}

	/**
	 * Get the post's post type object.
	 */
	public function get_post_type_object(): ?Clarkson_Post_Type {
		return Clarkson_Post_Type::get( $this->_post->post_type );
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
	 * Update the posts status.
	 *
	 * @param string $status New post status.
	 */
	public function set_status( $status ): void {
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
	 * @return int
	 *
	 * @throws \Exception Error message.
	 */
	public function add_comment( $comment_text, $user_id ) {
		if ( empty( $comment_text ) || empty( $user_id ) ) {
			throw new \Exception( 'Not enough data' );
		}

		$comment = array(
			'comment_post_ID' => $this->get_id(),
			'user_id'         => $user_id,
			'comment_content' => esc_attr( $comment_text ),
		);

		$result = wp_insert_comment( $comment );

		if ( false === $result ) {
			throw new \Exception( 'wp_insert_comment failed: ' . $comment_text );
		}

		return $result;
	}

	/**
	 * Retrieve the terms for a post.
	 *
	 * @param string $taxonomy Optional. The taxonomy for which to retrieve terms. Default 'post_tag'.
	 * @param array  $args     Optional. {@link wp_get_object_terms()} arguments. Default empty array.
	 *
	 * @return Clarkson_Term[]|\WP_Error List of post tags or a WP_Error.
	 */
	public function get_terms( $taxonomy, $args = array() ) {
		$terms = wp_get_post_terms( $this->get_id(), $taxonomy, $args );

		if ( $terms instanceof \WP_Error ) {
			user_error( esc_html( $terms->get_error_message() ) );
			return $terms;
		}

		return Objects::get_instance()->get_terms( $terms );
	}

	/**
	 * Add a single term to a post.
	 *
	 * @param Clarkson_Term $term    Term data.
	 *
	 * @return array|\WP_Error Affected Term IDs.
	 */
	public function add_term( $term ) {
		return wp_set_object_terms( $this->get_id(), $term->get_id(), $term->get_taxonomy(), true );
	}

	/**
	 * Bulk add terms to a post.
	 *
	 * @param string           $taxonomy Taxonomy.
	 * @param Clarkson_Term[] $terms    Terms.
	 * @var   Clarkson_Term   $term     Term objects.
	 *
	 * @return array|\WP_Error            Terms array.
	 */
	public function add_terms( $taxonomy, $terms ) {
		// Filter terms to ensure they are in the correct taxonomy.
		$terms = array_filter(
			$terms,
			function( $term ) use ( $taxonomy ) {
				return $term->get_taxonomy() === $taxonomy;
			}
		);

		// get array of term IDs.
		$terms = array_map(
			function( $term ) {
					return $term->get_id();
			},
			$terms
		);

		return wp_set_object_terms( $this->get_id(), $terms, $taxonomy, true );
	}

	/**
	 * Reset terms.
	 * Will delete all terms for a given taxonomy.
	 * Adds all passed terms or overwrites existing terms.
	 *
	 * @param string           $taxonomy Taxonomy.
	 * @param Clarkson_Term[] $terms    Terms.
	 * @var   Clarkson_Term   $term     Term objects.
	 *
	 * @return array|\WP_Error             Affected Term IDs.
	 */
	public function reset_terms( $taxonomy, $terms ) {
		// Filter terms to ensure they are in the correct taxonomy.
		$terms = array_filter(
			$terms,
			function( $term ) use ( $taxonomy ) {
				return $term->get_taxonomy() === $taxonomy;
			}
		);

		// get array of term IDs.
		$terms = array_map(
			function( $term ) {
					return $term->get_id();
			},
			$terms
		);

		return wp_set_object_terms( $this->get_id(), $terms, $taxonomy, false );
	}

	/**
	 * Remove a post term.
	 *
	 * @param  Clarkson_Term $term Post term.
	 *
	 * @return bool|\WP_Error        True on success, false or WP_Error on failure.
	 */
	public function remove_term( $term ) {
		return wp_remove_object_terms( $this->get_id(), $term->get_id(), $term->get_taxonomy() );
	}

	/**
	 * Is post associated with term?
	 *
	 * @param  Clarkson_Term $term Post term.
	 * @return boolean
	 */
	public function has_term( $term ) {
		return has_term( $term->get_slug(), $term->get_taxonomy(), $this->get_id() );
	}

	/**
	 * Return a set of data to use for json output.
	 *
	 * We can't just return $this->_post, because these values will only return raw unfiltered data.
	 */
	public function jsonSerialize() {
		$data              = array();
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

		$author = $this->get_author();
		if ( $author instanceof Clarkson_User ) {
			$data['author'] = array(
				'id'           => $author->get_id(),
				'display_name' => $author->get_display_name(),
			);
		}

		return $data;
	}
}
