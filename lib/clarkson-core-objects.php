<?php

class Clarkson_Core_Objects {

	protected $objects = array();

	public function available_objects() {
		return $this->objects;
	}

	public function get_term( $term ) {
		if ( ! isset( $term->taxonomy ) || ! isset( $term->term_id )) {
			return;
		}

		$cc = Clarkson_Core::get_instance();
		$class_name = $cc->autoloader->sanitize_object_name( $term->taxonomy );

		if ( in_array( $class_name, $cc->autoloader->taxonomies ) && class_exists( $class_name ) ) {
			return new $class_name($term->term_id, $term->taxonomy);
		}
		return Clarkson_Term::get_by_id( $term->term_id, $term->taxonomy );
	}

	public function get_users( $users_ids ) {
		$users = array();

		foreach ( $users_ids as $users_id ) {
			$users[] = $this->get_user( $users_id );
		}

		return $users;
	}

	public function get_user( $users_id ) {
		$cc = Clarkson_Core::get_instance();
		if ( in_array( 'user', $cc->autoloader->user_types ) && class_exists( 'User' ) ) {
			return new User( $users_id );
		}
		return new Clarkson_User( $users_id );
	}

	/**
	 * Get a array of post converted to their respectievelijke WordPress object class
	 */
	public function get_objects( $posts ) {
		$objects = array();

		foreach ( $posts as $post ) {
			$objects[] = $this->get_object( $post );
		}

		return $objects;
	}

	/**
	 * Get post that's converted to their respectievelijke WordPress object class
	 */
	public function get_object( $post ) {
		if( ! $post instanceof WP_Post && is_int( (int)$post ) ){
			trigger_error( "Deprecated calling of get_object with an ID. Use a `WP_Post` instead.", E_USER_DEPRECATED );
			$post = get_post( $post );
		}
		$cc = Clarkson_Core::get_instance();

		$type = get_post_type( $post );
		$type = $cc->autoloader->sanitize_object_name( $type );
		$type = apply_filters( 'clarkson_object_type', $type );

		if ( in_array( $type, $cc->autoloader->post_types ) && class_exists( $type ) ) {
			return new $type( $post );
		}

		return new Clarkson_Object( $post );
	}

	private function register_objects() {
		$objects = array(
			'Clarkson_Object' => '',
			'Clarkson_Term' => '',
			'Clarkson_User' => '',
		);

		$deprecated = Clarkson_Core_Deprecated::get_instance();
		$deprecated_objects = $deprecated->get_theme_objects();
		$objects = array_merge( $objects, $deprecated_objects );

		$objects = apply_filters( 'clarkson_available_objects', $objects );

		$this->objects = $objects;
	}


	// Singleton
	protected $instance = null;

	public static function get_instance() {
		static $instance = null;

		if (null === $instance) {
			$instance = new Clarkson_Core_Objects();
		}

		return $instance;
	}

	protected function __construct() {
		$this->register_objects();
	}

	private function __clone() {
	}
	private function __wakeup() {
	}
}