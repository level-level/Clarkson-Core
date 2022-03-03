<?php
/**
 * Clarkson Object.
 */

namespace Clarkson_Core\WordPress_Object;

use Clarkson_Core\ClarksonWPException;
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
	public static string $type = 'post';

	/**
	 * The WordPress post object
	 */
	protected \WP_Post $post;

	/**
	 * Microcache for parsed post content.
	 */
	protected string $content = '';

	/**
	 * Microcache for parsed post excerpt.
	 */
	protected string $excerpt = '';

	public function __construct( \WP_Post $post ) {
		$this->post = $post;
	}

	/**
	 * Check if name is not a wp_post object property.
	 *
	 * @param string $name Post name to check.
	 *
	 * @throws \Exception  Error message.
	 */
	public function __get( string $name ) {
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
		return $this->post;
	}

	/**
	 * Clarkson Object for a WP_Post by ID.
	 *
	 * @param  int $id Post id.
	 *
	 * @return Clarkson_Object|null    Post data.
	 */
	public static function get( int $id ): ?Clarkson_Object {
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
	public static function get_many( array $args, &$post_query = null ): array {
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
	public static function get_one( array $args = array() ): ?Clarkson_Object {
		$args['posts_per_page'] = 1;
		$one                    = static::get_many( $args );
		return array_shift( $one );
	}

	/**
	 * Get the id of a post.
	 *
	 * @return int The post id.
	 */
	public function get_id(): int {
		return $this->post->ID;
	}

	public function get_template(): Clarkson_Template {
		return Objects::get_instance()->get_template( $this->post );
	}

	/**
	 * Get the parent of the post, if any.
	 *
	 * @return Clarkson_Object|null
	 */
	public function get_parent(): ?Clarkson_Object {
		if ( $this->post->post_parent ) {
			return self::get( $this->post->post_parent );
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
	public function get_children( array $args = array() ): array {
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
	public function get_attachments( array $args = array() ): array {
		$args['post_type'] = 'attachment';
		return $this->get_children( $args );
	}

	/**
	 * Check if the post has a thumbnail.
	 *
	 * @return bool
	 */
	public function has_thumbnail(): bool {
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
	public function get_thumbnail( $size = 'thumbnail', $attr = '' ): string {
		return get_the_post_thumbnail( $this->get_id(), $size, $attr );
	}

	/**
	 * Get the thumbnail id by post id.
	 *
	 * @return int The ID of the post, 0 on failure.
	 */
	public function get_thumbnail_id(): int {
		return (int) get_post_thumbnail_id( $this->get_id() );
	}

	/**
	 * Get the date the post was created in post_date_gmt format.
	 *
	 * @param  string $format PHP Date format. {@link https://www.php.net/manual/en/function.date.php}
	 *
	 * @return string
	 */
	public function get_date( $format = 'U' ): string {
		return gmdate( $format, strtotime( $this->post->post_date_gmt ) );
	}

	/**
	 * Get the date in localized format.
	 *
	 * @param string $format Date format. {@link https://wordpress.org/support/article/formatting-date-and-time/}
	 * @param bool   $gmt   Whether to convert to GMT for time.
	 *
	 * @return string
	 */
	public function get_date_i18n( string $format = 'U', bool $gmt = false ): string {
		return date_i18n( $format, strtotime( $this->post->post_date_gmt ), $gmt );
	}

	/**
	 * Set the post date/time of the post.
	 *
	 * @param int $time PHP timestamp.
	 */
	public function set_date( int $time ): void {
		$this->post->post_date = gmdate( 'Y-m-d H:i:s', $time );

		wp_update_post(
			array(
				'ID'        => $this->get_id(),
				'post_date' => $this->post->post_date,
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
	public function get_local_date( string $format = 'U' ): string {
		return gmdate( $format, strtotime( $this->post->post_date ) );
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
	public function get_meta( string $key, bool $single = false ) {
		return get_post_meta( $this->get_id(), $key, $single );
	}

	/**
	 * Update the post meta data.
	 *
	 * @param string       $key   Post meta key.
	 * @param string|array $value New post meta data.
	 *
	 * @return bool|int    Meta ID on success, false on failure
	 */
	public function update_meta( string $key, $value ) {
		return update_post_meta( $this->get_id(), $key, $value );
	}

	/**
	 * Add post meta data.
	 *
	 * @param string       $key   Post meta key.
	 * @param string|array $value New post meta data.
	 *
	 * @return bool|int    Meta ID on success, false on failure
	 */
	public function add_meta( string $key, $value ) {
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
	public function delete_meta( string $key, $value = null ): bool {
		return delete_post_meta( $this->get_id(), $key, $value );
	}

	/**
	 * Delete post.
	 */
	public function delete(): void {
		wp_delete_post( $this->get_id(), true );
	}

	/**
	 * Get the title of a post by id.
	 *
	 * @return string Escaped post title.
	 */
	public function get_title(): string {
		return get_the_title( $this->get_id() );
	}

	/**
	 * Get post name (slug) by id.
	 *
	 * @return string Post name.
	 */
	public function get_post_name(): string {
		return $this->post->post_name;
	}

	/**
	 * Get the post content.
	 *
	 * See: https://github.com/level-level/Clarkson-Core/issues/122
	 *
	 * @return string
	 */
	public function get_content(): string {
		global $post;
		if ( empty( $this->content ) ) {
			setup_postdata( $this->post );

			// Post stays empty when wp_query 404 is set, resulting in a warning from the_content.
			if ( null === $post ) {
				$post = $this->post;
			}

			ob_start();
			the_content();

			$this->content = ob_get_clean();
			wp_reset_postdata();
		}

		return $this->content;
	}

	/**
	 * Get the raw post content.
	 */
	public function get_raw_content(): string {
		return $this->post->post_content;
	}

	/**
	 * Get the post author id.
	 *
	 * The post author is returned as a string in WordPress for backward
	 * compatibility reasons that do not apply in this situation.
	 *
	 * @see https://core.trac.wordpress.org/ticket/25092
	 *
	 * @return int|null The Author ID if it exists
	 */
	public function get_author_id(): ?int {
		if ( $this->post->post_author ) {
			return (int) $this->post->post_author;
		}

		return null;
	}

	/**
	 * Get the post author object.
	 */
	public function get_author(): ?Clarkson_User {
		$author_id = $this->get_author_id();

		if ( $author_id ) {
			return Clarkson_User::get( $author_id );
		}

		return null;
	}

	/**
	 * Get the post permalink.
	 */
	public function get_permalink(): string {
		$permalink = get_permalink( $this->get_id() );

		if ( ! is_string( $permalink ) ) {
			throw new \Exception( 'Permalink requested on non-existing post. ' );
		}

		return $permalink;
	}

	/**
	 * Get the post excerpt.
	 *
	 * See the issue: https://github.com/level-level/Clarkson-Core/issues/122
	 */
	public function get_excerpt(): string {
		global $post;
		if ( empty( $this->excerpt ) ) {
			if ( ! empty( $post ) ) {
				$oldpost = clone $post;
			} else {
				$oldpost = null;
			}
			$post = $this->post; // Set post to what we are asking the excerpt for.
			setup_postdata( $this->post );
			ob_start();
			the_excerpt();
			$this->excerpt = ob_get_clean();
			wp_reset_postdata();
			$post = $oldpost; // Reset global post.
		}
		return $this->excerpt;
	}

	/**
	 * Get the post's post type.
	 */
	public function get_post_type(): string {
		return $this->post->post_type;
	}

	/**
	 * Get the post's post type object.
	 */
	public function get_post_type_object(): ?Clarkson_Post_Type {
		return Clarkson_Post_Type::get( $this->post->post_type );
	}

	/**
	 * Get the post's post status.
	 */
	public function get_status(): string {
		return $this->post->post_status;
	}

	/**
	 * Update the posts status.
	 *
	 * @param string $status New post status.
	 */
	public function set_status( string $status ): void {
		$this->post->post_status = $status;

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
	 * @throws \Exception
	 *
	 * @return int Comment ID
	 */
	public function add_comment( string $comment_text, int $user_id ): int {
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
	 * @throws ClarksonWPException
	 *
	 * @return Clarkson_Term[] List of post tags
	 */
	public function get_terms( string $taxonomy = 'post_tag', array $args = array() ): array {
		$terms = wp_get_post_terms( $this->get_id(), $taxonomy, $args );

		if ( $terms instanceof \WP_Error ) {
			throw new ClarksonWPException( $terms );
		}

		return Objects::get_instance()->get_terms( $terms );
	}

	/**
	 * Add a single term to a post.
	 *
	 * @param Clarkson_Term $term    Term data.
	 *
	 * @throws ClarksonWPException
	 *
	 * @return int[] Taxonomy ids of affected terms
	 */
	public function add_term( Clarkson_Term $term ): array {
		$object_terms = wp_set_object_terms( $this->get_id(), $term->get_id(), $term->get_taxonomy(), true );

		if ( $object_terms instanceof \WP_Error ) {
			throw new ClarksonWPException( $object_terms );
		}

		return $object_terms;
	}

	/**
	 * Bulk add terms to a post.
	 *
	 * @param string          $taxonomy Taxonomy name
	 * @param Clarkson_Term[] $terms    Terms
	 *
	 * @throws ClarksonWPException
	 *
	 * @return int[] Taxonomy ids of affected terms
	 */
	public function add_terms( string $taxonomy, array $terms ): array {
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

		$object_terms = wp_set_object_terms( $this->get_id(), $terms, $taxonomy, true );

		if ( $object_terms instanceof \WP_Error ) {
			throw new ClarksonWPException( $object_terms );
		}

		return $object_terms;
	}

	/**
	 * Reset terms.
	 * Will delete all terms for a given taxonomy.
	 * Adds all passed terms or overwrites existing terms.
	 *
	 * @param string          $taxonomy Taxonomy name
	 * @param Clarkson_Term[] $terms    Terms
	 *
	 * @throws ClarksonWPException
	 *
	 * @return int[] Taxonomy ids of affected terms
	 */
	public function reset_terms( string $taxonomy, array $terms ): array {
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

		$object_terms = wp_set_object_terms( $this->get_id(), $terms, $taxonomy, false );

		if ( $object_terms instanceof \WP_Error ) {
			throw new ClarksonWPException( $object_terms );
		}

		return $object_terms;
	}

	/**
	 * Remove a post term.
	 *
	 * @param Clarkson_Term $term
	 *
	 * @throws ClarksonWPException
	 *
	 * @return bool True on success
	 */
	public function remove_term( Clarkson_Term $term ): bool {
		$remove_object = wp_remove_object_terms( $this->get_id(), $term->get_id(), $term->get_taxonomy() );

		if ( $remove_object instanceof \WP_Error ) {
			throw new ClarksonWPException( $remove_object );
		}

		return $remove_object;
	}

	/**
	 * Is post associated with term?
	 */
	public function has_term( Clarkson_Term $term ): bool {
		return has_term( $term->get_slug(), $term->get_taxonomy(), $this->get_id() );
	}

	/**
	 * @inheritdoc
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
