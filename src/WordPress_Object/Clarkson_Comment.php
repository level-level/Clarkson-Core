<?php
/**
 * Clarkson Comment.
 */

namespace Clarkson_Core\WordPress_Object;

use Clarkson_Core\Objects;
use WP_Comment;
use WP_Post;

/**
 * Object oriented wrapper for WP_Comment objects.
 */
class Clarkson_Comment {

	/**
	 * The type of the comment.
	 *
	 * @var string
	 */
	public static $type = 'comment';

	/**
	 * WordPress representation of this comment object.
	 *
	 * @var WP_Comment
	 */
	protected $_comment;

	/**
	 * Get Clarkson Comment object by ID.
	 *
	 * @param int $id Comment ID.
	 * @return static|null
	 */
	public static function get( int $id ): ?Clarkson_Comment {
		$comment = WP_Comment::get_instance( $id );
		if ( $comment instanceof WP_Comment ) {
			return Objects::get_instance()->get_comment( $comment );
		}
		return null;
	}

	/**
	 * Get all available Clarkson Comment objects
	 *
	 * @return \Clarkson_Core\WordPress_Object\Clarkson_Comment[]
	 */
	public static function get_many( array $args = array() ): array {
		$args['fields'] = '';
		$args['type']   = static::$type;
		$comments       = get_comments( $args );
		return Objects::get_instance()->get_comments( $comments );
	}

	/**
	 * Get all available Clarkson Comment objects
	 *
	 * @return Clarkson_Comment|null
	 */
	public static function get_one( array $args = array() ): ?Clarkson_Comment {
		$args['number'] = 1;
		$comments       = static::get_many( $args );
		return array_shift( $comments );
	}

	/**
	 * Clarkson_Comment constructor.
	 */
	public function __construct( WP_Comment $comment ) {
		$this->_comment = $comment;
	}

	/**
	 * Get the WordPress WP_Comment object.
	 */
	public function get_comment(): WP_Comment {
		return $this->_comment;
	}

	/**
	 * Get comment meta
	 *
	 * @param string $key    Comment meta key.
	 * @param bool   $single Comment meta data.
	 *
	 * @link https://developer.wordpress.org/reference/functions/get_comment_meta/
	 *
	 * @return mixed
	 */
	public function get_meta( string $key, $single = false ) {
		return get_comment_meta( $this->get_id(), $key, $single );
	}

	/**
	 * Update the comment meta data.
	 *
	 * @param string       $key   Comment meta key.
	 * @param string|array $value New comment meta data.
	 * @param string|array $prev_value Optional. Previous value to check before updating.
	 *                     If specified, only update existing metadata entries with
	 *                     this value. Otherwise, update all entries. Default empty.
	 *
	 * @return bool|int
	 */
	public function update_meta( string $key, $value, $prev_value = '' ) {
		return update_comment_meta( $this->get_id(), $key, $value, $prev_value );
	}

	/**
	 * Add comment meta data.
	 *
	 * @param string       $key   Comment meta key.
	 * @param string|array $value New comment meta data.
	 *
	 * @return false|int
	 */
	public function add_meta( string $key, $value ) {
		return add_comment_meta( $this->get_id(), $key, $value );
	}

	/**
	 * Delete comment meta data.
	 *
	 * @param string $key    Comment meta key.
	 * @param null   $value  Null, as the value will be deleted.
	 *
	 * @return bool
	 */
	public function delete_meta( $key, $value = null ): bool {
		return delete_comment_meta( $this->get_id(), $key, $value );
	}

	/**
	 * Delete comment.
	 */
	public function delete(): void {
		wp_delete_comment( $this->get_id(), true );
	}

	/**
	 * Get the ID of the comment.
	 * Should be casted to integer, as in WordPress this is a numeric string for backward compatibility reasons.
	 */
	public function get_id(): int {
		return (int) $this->_comment->comment_ID;
	}

	/**
	 * Get the comment post id.
	 */
	public function get_post_id(): int {
		return (int) $this->_comment->comment_post_ID;
	}

	/**
	 * Get the comment post object.
	 */
	public function get_post(): ?Clarkson_Object {
		if ( ! $this->_comment->comment_post_ID ) {
			return null;
		}

		$post = get_post( (int) $this->_comment->comment_post_ID );
		if ( ! $post instanceof WP_Post ) {
			return null;
		}

		return Objects::get_instance()->get_object( $post );
	}

	/**
	 * Get the comment author name
	 */
	public function get_author(): string {
		return $this->_comment->comment_author;
	}

	/**
	 * Get the comment author email
	 */
	public function get_author_email(): string {
		return $this->_comment->comment_author_email;
	}

	/**
	 * Get the comment author url
	 */
	public function get_author_url(): string {
		return $this->_comment->comment_author_url;
	}

	/**
	 * Get the comment author IP
	 */
	public function get_author_ip(): string {
		return $this->_comment->comment_author_ip;
	}

	/**
	 * Get the date the comment was created.
	 *
	 * @param  string $format PHP Date format. {@link https://www.php.net/manual/en/function.date.php}
	 *
	 * @return string
	 */
	public function get_date( string $format = 'U' ) {
		return gmdate( $format, strtotime( $this->_comment->comment_date_gmt ) );
	}

	/**
	 * Get the date in localized format.
	 *
	 * @param string $format Date format. {@link https://wordpress.org/support/article/formatting-date-and-time/}
	 * @param bool   $gmt   Whether to convert to GMT for time.
	 *
	 * @return string
	 */
	public function get_date_i18n( string $format = 'U', $gmt = false ) {
		return date_i18n( $format, strtotime( $this->_comment->comment_date_gmt ), $gmt );
	}

	/**
	 * Get the local date the comment was created.
	 *
	 * @param string $format Date format.
	 *
	 * @return string
	 */
	public function get_local_date( string $format = 'U' ) {
		return gmdate( $format, strtotime( $this->_comment->comment_date ) );
	}

	/**
	 * Get the comment content.
	 */
	public function get_content( array $args = array() ): string {
		return get_comment_text( $this->_comment, $args );
	}

	/**
	 * Get the comment karma count.
	 */
	public function get_karma(): int {
		return (int) $this->_comment->comment_karma;
	}

	/**
	 * Get the comment status.
	 * Status might be 'trash', 'approved', 'unapproved', 'spam'. Null on failure.
	 */
	public function get_status(): ?string {
		$status = wp_get_comment_status( $this->_comment );
		if ( ! $status ) {
			return null;
		}

		return $status;
	}

	/**
	 * Get the comment author HTTP user agent.
	 */
	public function get_agent(): string {
		return $this->_comment->comment_agent;
	}

	/**
	 * Get the comment type.
	 */
	public function get_type(): string {
		return $this->_comment->comment_type;
	}

	/**
	 * Get the comment parent ID.
	 */
	public function get_parent_id(): int {
		return (int) $this->_comment->comment_parent;
	}

	/**
	 * Get the comment parent.
	 */
	public function get_parent(): ?Clarkson_Comment {
		$parent_id = $this->get_parent_id();
		if ( ! $parent_id ) {
			return null;
		}

		return static::get( $parent_id );
	}

	/**
	 * Get the comment author user id.
	 *
	 * @return int
	 */
	public function get_user_id(): int {
		return (int) $this->_comment->user_id;
	}

	/**
	 * Get the comment author user object.
	 */
	public function get_user(): ?Clarkson_User {
		if ( ! $this->_comment->user_id ) {
			return null;
		}

		$user = get_userdata( (int) $this->_comment->user_id );
		if ( ! $user ) {
			return null;
		}

		return Objects::get_instance()->get_user( $user );
	}
}
