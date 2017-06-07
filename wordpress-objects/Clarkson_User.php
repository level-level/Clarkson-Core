<?php

class Clarkson_User {

	protected static $_current_user;
	protected static $users;

	/**
	 * Get the current loged in user
	 * @static
	 * @return User
	 * @throws Exception on not loggged in
	 */
	public static function current_user() {
		if ( is_user_logged_in() ) {
			return static::get( get_current_user_id() );
		} else {
			throw new Exception( 'User is not logged in' );
		}
	}


	public static function get( $id ) {
		if ( ! isset( static::$users[ $id ] ) ) {
			$class = get_called_class();
			static::$users[ $id ] = new $class( $id );
		}

		return static::$users[ $id ];
	}

	/**
	 * @param int $user_id
	 */
	public function __construct( $user_id ) {
		if ( empty( $user_id ) ) {
			throw new Exception( '$user_id empty' );
		}

		$this->_id = $user_id;

		if ( ! $this->get_user() ) {
			throw new Exception( '$user_id does not exist' );
		}
	}

	/**
	 * Check if this user is the currently logged in user
	 * @return bool
	 */
	public function is_current_user() {
		return $this->get_id() == get_current_user_id();
	}

	/**
	 * Get the WordPress WP_User object
	 * @return WP_User
	 */
	public function get_user() {
		if ( ! isset( $this->_user ) ) {
			$this->_user = new WP_User( $this->_id );
		}

		if ( ! $this->_user->ID ) {
			unset( $this->_user );
			return null;
		}

		return $this->_user;
	}

	/**
	 * Get the ID of the user
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->_id;
	}

	/**
	 * Get the display name of the user
	 *
	 * @return string
	 */
	public function get_display_name() {
		return $this->get_user()->display_name;
	}

	/**
	 * Get the first name for the user for displaying (privacy allows it)
	 * @return string
	 */
	public function get_display_first_name() {
		return reset( explode( ' ', $this->get_display_name() ) );
	}

	/**
	 * Get the last name for the user for displaying (privacy allows it)
	 * @return string
	 */
	public function get_display_last_name() {
		$parts = explode( ' ', $this->get_display_name() );

		if ( isset( $parts[1] ) ) {
			return $parts[1];
		}

		return '';
	}

	/**
	 * Get the email address of the user
	 *
	 * @return string
	 */
	public function get_email() {
		return $this->get_user()->user_email;
	}

	/**
	 * Get the login name for the user
	 *
	 * @return string
	 */
	public function get_login() {
		return $this->get_user()->user_login;
	}

	/**
	 * Get the role for the user
	 *
	 * @return string
	 */
	public function get_role() {
		return reset( $this->get_user()->roles );
	}

	/**
	 * Get the notification setting for the user
	 *
	 * @param string $setting
	 * @return mixed
	 */
	public function get_notification_setting( $setting ) {
		return $this->get_user()->user_notification_settings[ $setting ];
	}

	/**
	 * Get meta for the user
	 *
	 * @param string $key
	 * @param bool $single
	 * @return mixed
	 */
	public function get_meta( $key = '', $single = false ) {
		return get_user_meta( $this->get_id(), $key, $single );
	}

	/**
	 * Update a meta key for the user
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return bool
	 */
	public function update_meta( $key, $value ) {
		return update_user_meta( $this->get_id(), $key, $value );
	}

	/**
	 * Add a meta key=>value for the user
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return bool
	 */
	public function add_meta( $key, $value ) {
		return add_user_meta( $this->get_id(), $key, $value );
	}

	/**
	 * Delete a meta key=>value for the user
	 *
	 * @param string $key
	 * @param mixed $value optional
	 * @return bool
	 */
	public function delete_meta( $key, $value = '' ) {
		return delete_user_meta( $this->get_id(), $key, $value );
	}
}