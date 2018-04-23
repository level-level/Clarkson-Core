<?php

class Clarkson_Core_Objects {

	protected $objects = array();

	public function available_objects() {
		return $this->objects;
	}

	public function get_term( $term ) {

		if ( ! isset( $term->taxonomy ) || ! isset( $term->term_id ) ) {
			return;
		}

		$cc = Clarkson_Core::get_instance();
		$class_name = $cc->autoloader->sanitize_object_name( $term->taxonomy );

		if ( in_array( $class_name, $cc->autoloader->taxonomies ) && class_exists( $class_name ) ) {
			return new $class_name( $term->term_id, $term->taxonomy );
		}
		return Clarkson_Term::get_by_id( $term->term_id, $term->taxonomy );
	}

	public function get_users( $users_ids ) {
		$users = array();

		foreach ( $users_ids as $user_id ) {
			$users[] = $this->get_user( $user_id );
		}

		return $users;
	}


	public function get_user( $user_id ) {

		$cc = Clarkson_Core::get_instance();
		$user = get_userdata( $user_id );
		$class_name = false;

		if ( $user && $user->roles && count( $user->roles ) >= 1 ) {
			$class_name = $user->roles[0];
		}

		if ( $class_name && in_array( $class_name, $cc->autoloader->user_types ) && class_exists( $class_name ) ) {
			return new $class_name( $user_id );
		}
		return new Clarkson_User( $user_id );
	}

	public function get_objects( $posts_ids ) {
		$objects = array();

		foreach ( $posts_ids as $posts_id ) {
			$objects[] = $this->get_object( $posts_id );
		}

		return $objects;
	}

	public function get_object( $post_id ) {
		$cc = Clarkson_Core::get_instance();

		// defaults to post type
		$type = get_post_type( $post_id );

		// Check if post has a custom template, if so, overwrite value
		$page_template_slug = $cc->autoloader->get_template_filename( $post_id );

		if ( $page_template_slug && ! empty( $page_template_slug ) ) {
			$type = $page_template_slug;
		}

		$type = $cc->autoloader->sanitize_object_name( $type );
		$type = apply_filters( 'clarkson_object_type', $type );

		if ( ( in_array( $type, $cc->autoloader->post_types ) || in_array( $type, $cc->autoloader->extra ) ) && class_exists( $type ) ) {
			$object = new $type( $post_id );
		} else {
			$object = new Clarkson_Object( $post_id );
		}

		return $object;
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