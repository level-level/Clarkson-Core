<?php
/**
 * Clarkson Core Objects.
 *
 * @package CLARKSON\Lib
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
			$class_name = $cc->autoloader->user_objectname_prefix . $user->roles[0];
		}

		if ( $class_name && in_array( $class_name, $cc->autoloader->user_types, true ) && class_exists( $class_name ) ) {
			$object = new $class_name( $user_id );
		} else {
			$object = new Clarkson_User( $user_id );
		}

		return $object;

	}

	/**
	 * Get an array of posts converted to their corresponding WordPress object class.
	 *
	 * @param array $posts Posts.
	 *
	 * @return array $objects Array of post objects.
	 */
	public function get_objects( $posts ) {
		$objects = array();

		if ( empty( $posts ) ) {
			return $objects;
		}

		foreach ( $posts as $post ) {
			$object = $this->get_object( $post );
			if ( ! empty( $object ) ) {
				$objects[] = $this->get_object( $post );
			}
		}

		return $objects;
	}

	/**
	 * Get post that's converted to their corresponding WordPress object class.
	 *
	 * @param object $post Post.
	 *
	 * @return object Clarkson Post object.
	 */
	public function get_object( $post ) {
		if ( ! $post instanceof WP_Post && is_int( (int) $post ) ) {
			user_error( 'Deprecated calling of get_object with an ID. Use a `WP_Post` instead.', E_USER_DEPRECATED );
			$post = get_post( $post );
		}

		$cc = Clarkson_Core::get_instance();

		// defaults to post type.
		$type = get_post_type( $post );

		// Check if post has a custom template, if so, overwrite value.
		$page_template_slug = $cc->autoloader->get_template_filename( $post->ID );

		if ( $page_template_slug && ! empty( $page_template_slug ) ) {
			$type = $page_template_slug;
		}

		$type = $cc->autoloader->sanitize_object_name( $type );
		$type = apply_filters( 'clarkson_object_type', $type );

		// This filter allows to control object creation before Clarkson Core determines the correct class to use. For example by calling "wc_get_product".
		$object_creation_callback = apply_filters( 'clarkson_core_create_object_callback', false, $type, $post->ID );
		if ( ! empty( $object_creation_callback ) ) {
			return $object_creation_callback( $post->ID );
		}

		if ( ( in_array( $type, $cc->autoloader->post_types, true ) || in_array( $type, $cc->autoloader->extra, true ) ) && class_exists( $type ) ) {
			$object = new $type( $post );
		} else {
			$object = new Clarkson_Object( $post );
		}

		return $object;
	}

	/**
	 * Register objects.
	 */
	private function register_objects() {
		$objects = array(
			'Clarkson_Archive' => '',
			'Clarkson_Object'  => '',
			'Clarkson_Term'    => '',
			'Clarkson_User'    => '',
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
