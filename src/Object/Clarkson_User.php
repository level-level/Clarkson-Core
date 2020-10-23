<?php
/**
 * Clarkson User.
 */

namespace Clarkson_Core\Object;

use Clarkson_Core\Objects;

/**
 * Object oriented wrapper for WP_User objects.
 */
class Clarkson_User {
	/**
	 * WordPress representation of this user object.
	 *
	 * @var \WP_User
	 */
	protected $_user;


	/**
	 * Current user.
	 *
	 * @var \WP_User $_current_user
	 */
	protected static $_current_user;

	/**
	 * Users.
	 *
	 * @var static[] $users
	 */
	protected static $users;

	/**
	 * Get the current logged in user.
	 *
	 * @return Clarkson_User User status.
	 * @throws \Exception  User is not logged in.
	 */
	public static function current_user() {
		if ( is_user_logged_in() ) {
			return static::get( get_current_user_id() );
		} else {
			throw new \Exception( 'User is not logged in' );
		}
	}


	/**
	 * Get user data by user id.
	 *
	 * @param  int $id User id.
	 * @return Clarkson_User
	 * @throws \Exception In case a requested user ID does not exist.
	 */
	public static function get( $id ) {
		$user = get_userdata( $id );
		if ( ! $user instanceof \WP_User ) {
			throw new \Exception( "User not found ($id)" );
		}

		return Objects::get_instance()->get_user( $user );
	}

	/**
	 * Clarkson_User constructor.
	 *
	 * @throws \Exception          User status.
	 */
	public function __construct( \WP_User $user ) {
		$this->_user = $user;
	}

	/**
	 * Check if this user is the currently logged in user.
	 *
	 * @return bool
	 */
	public function is_current_user() {
		return $this->get_id() === get_current_user_id();
	}

	/**
	 * Get the WordPress WP_User object.
	 *
	 * @return \WP_User
	 */
	public function get_user() {
		return $this->_user;
	}

	/**
	 * Get the ID of the user.
	 *
	 * @return int User id.
	 */
	public function get_id() {
		return $this->_user->ID;
	}

	/**
	 * Get the display name of the user.
	 *
	 * @return string User display name.
	 */
	public function get_display_name() {
		return $this->get_user()->display_name;
	}

	/**
	 * Get the user's first name.
	 *
	 * @return string User first name.
	 */
	public function get_display_first_name() {
		$parts = explode( ' ', $this->get_display_name() );
		return reset( $parts );
	}

	/**
	 * Get the user's last name.
	 *
	 * @return string User last name.
	 */
	public function get_display_last_name() {
		$parts = explode( ' ', $this->get_display_name() );

		if ( isset( $parts[1] ) ) {
			return $parts[1];
		}

		return '';
	}

	/**
	 * Get the user's email address.
	 *
	 * @return string Email address.
	 */
	public function get_email() {
		return $this->get_user()->user_email;
	}

	/**
	 * Get the user's login name.
	 *
	 * @return string Login name.
	 */
	public function get_login() {
		return $this->get_user()->user_login;
	}

	/**
	 * Get the user's role.
	 *
	 * @return string User role.
	 */
	public function get_role() {
		return reset( $this->get_user()->roles );
	}


	/**
	 * Get the user's user_meta data.
	 *
	 * See: https://github.com/level-level/Clarkson-Core/issues/124.
	 *
	 * @param string $key    Meta key.
	 * @param bool   $single If true return value of meta data field, if false return an array.
	 *
	 * @return array|string         Meta data.
	 */
	public function get_meta( $key = '', $single = false ) {
		return get_user_meta( $this->get_id(), $key, $single );
	}

	/**
	 * Update user meta data.
	 *
	 * See: https://github.com/level-level/Clarkson-Core/issues/124.
	 *
	 * @param string       $key    User meta key.
	 * @param array|string $value  User meta data.
	 *
	 * @return bool|int            Meta ID if the key didn't exist.
	 */
	public function update_meta( $key, $value ) {
		return update_user_meta( $this->get_id(), $key, $value );
	}

	/**
	 * Add a meta key=>value for the user.
	 *
	 * See: https://github.com/level-level/Clarkson-Core/issues/124.
	 *
	 * @param string       $key   User meta key.
	 * @param array|string $value User meta data.
	 *
	 * @return int|false
	 */
	public function add_meta( $key, $value ) {
		return add_user_meta( $this->get_id(), $key, $value );
	}

	/**
	 * Delete a meta key=>value for the user.
	 *
	 * See: https://github.com/level-level/Clarkson-Core/issues/124.
	 *
	 * @param string       $key   User meta key.
	 * @param array|string $value User meta data.
	 *
	 * @return bool
	 */
	public function delete_meta( $key, $value = '' ) {
		return delete_user_meta( $this->get_id(), $key, $value );
	}

}
