<?php
/**
 * Clarkson Core Objects class.
 *
 */

/**
 * Clarkson Core Objects class.
 */
class Clarkson_Core_Objects {

	/**
	 * Clarkson Core Objects array.
	 *
	 * @var array $objects Clarkson_Core_Objects.
	 */
	protected $objects = array();

	/**
	 * Available objects.
	 *
	 * @return array Available objects.
	 */
	public function available_objects() {
		return $this->objects;
	}

	/**
	 * Get term data.
	 *
	 * @param object $term The term.
	 *
	 * @return bool|object
	 */
	public function get_term( $term ) {

		if ( ! isset( $term->taxonomy ) || ! isset( $term->term_id ) ) {
			return false;
		}

		$cc         = Clarkson_Core::get_instance();
		$class_name = $cc->autoloader->sanitize_object_name( $term->taxonomy );

		if ( in_array( $class_name, $cc->autoloader->taxonomies, true ) && class_exists( $class_name ) ) {
			return new $class_name( $term->term_id, $term->taxonomy );
		}
		return Clarkson_Term::get_by_id( $term->term_id, $term->taxonomy );
	}

	/**
	 * Get users by id.
	 *
	 * @param array $users_ids User ids.
	 *
	 * @return array
	 */
	public function get_users( $users_ids ) {
		$users = array();

		foreach ( $users_ids as $user_id ) {
			$user = $this->get_user( $user_id );
			if ( $user ) {
				$users[] = $user;
			}
		}

		return $users;
	}


	/**
	 * Get user by user id.
	 *
	 * @param integer $user_id User id.
	 *
	 * @return bool|object
	 */
	public function get_user( $user_id ) {

		if ( empty( $user_id ) ) {
			return false;
		}
		$cc         = Clarkson_Core::get_instance();
		$user       = get_userdata( $user_id );
		$class_name = false;

		if ( $user && $user->roles && count( $user->roles ) >= 1 ) {
			$class_name = $user->roles[0];
		}

		if ( $class_name && in_array( $class_name, $cc->autoloader->user_types, true ) && class_exists( $class_name ) ) {
			$object = new $class_name( $user_id );
		} else {
			$object = new Clarkson_User( $user_id );
		}

		return $object;

	}

	/**
	 * Get objects by post id.
	 *
	 * @param array $posts_ids Post ids.
	 *
	 * @return array $objects Array of objects.
	 */
	public function get_objects( $posts_ids ) {
		$objects = array();

		if ( empty( $posts_ids ) ) {
			return $objects;
		}

		foreach ( $posts_ids as $posts_id ) {
			$object = $this->get_object( $posts_id );
			if ( ! empty( $object ) ) {
				$objects[] = $object;
			}
		}

		return $objects;
	}

	/**
	 * Get object by id.
	 *
	 * @param integer $post_id Post id.
	 *
	 * @return object
	 */
	public function get_object( $post_id ) {
		$cc = Clarkson_Core::get_instance();

		// defaults to post type.
		$type = get_post_type( $post_id );

		// Check if post has a custom template, if so, overwrite value.
		$page_template_slug = $cc->autoloader->get_template_filename( $post_id );

		if ( $page_template_slug && ! empty( $page_template_slug ) ) {
			$type = $page_template_slug;
		}

		$type = $cc->autoloader->sanitize_object_name( $type );
		$type = apply_filters( 'clarkson_object_type', $type );

		if ( ( in_array( $type, $cc->autoloader->post_types, true ) || in_array( $type, $cc->autoloader->extra, true ) ) && class_exists( $type ) ) {
			$object = new $type( $post_id );
		} else {
			$object = new Clarkson_Object( $post_id );
		}

		return $object;
	}

	/**
	 * Register objects.
	 */
	private function register_objects() {
		$objects = array(
			'Clarkson_Object' => '',
			'Clarkson_Term'   => '',
			'Clarkson_User'   => '',
		);

		$deprecated         = Clarkson_Core_Deprecated::get_instance();
		$deprecated_objects = $deprecated->get_theme_objects();
		$objects            = array_merge( $objects, $deprecated_objects );

		$objects = apply_filters( 'clarkson_available_objects', $objects );

		$this->objects = $objects;
	}


	/**
	 * Singleton.
	 *
	 * @var object $instance The Clarkson core objects.
	 */
	protected $instance = null;

	/**
	 * Get the instance.
	 *
	 * @return Clarkson_Core_Objects|null
	 */
	public static function get_instance() {
		static $instance = null;

		if ( null === $instance ) {
			$instance = new Clarkson_Core_Objects();
		}

		return $instance;
	}

	/**
	 * Clarkson_Core_Objects constructor.
	 */
	protected function __construct() {
		$this->register_objects();
	}

	/**
	 *  Clone.
	 */
	private function __clone() {
	}

	/**
	 * Wakeup.
	 */
	private function __wakeup() {
	}

}
