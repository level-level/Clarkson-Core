<?php
/**
 * Clarkson Core Objects.
 *
 * @package CLARKSON\Lib
 */

/**
 * This class is used to convert WordPress posts, terms and users into Clarkson
 * Objects.
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
	 * @deprecated
	 * @internal
	 * @return array Available objects.
	 */
	public function available_objects() {
		return $this->objects;
	}

	/**
	 * Get term data.
	 *
	 * @param \WP_Term $term The term.
	 *
	 * @return \Clarkson_Term
	 */
	public function get_term( $term ) {
		if ( ! $term instanceof \WP_Term ) {
			_doing_it_wrong( __METHOD__, 'You must call this function with a valid \WP_Term object.', '0.1.0' );
			throw new Exception( 'You must call this function with a valid \WP_Term object.' );
		}

		$cc         = Clarkson_Core::get_instance();
		$class_name = $cc->autoloader->sanitize_object_name( $term->taxonomy );

		if ( in_array( $class_name, $cc->autoloader->taxonomies, true ) && class_exists( $class_name ) ) {
			return new $class_name( $term );
		}
		return new \Clarkson_Term( $term );
	}

	/**
	 * Get users by id.
	 *
	 * @param \WP_User[]|int[] $users WP_User object (or deprecated user id).
	 *
	 * @return \Clarkson_User[]
	 */
	public function get_users( $users ) {
		$user_objects = array();

		foreach ( $users as $user ) {
			if ( ! $user instanceof \WP_User ) {
				_doing_it_wrong( __METHOD__, 'Deprecated get_user called with an ID. Supply a \WP_User object or use \'Clarkson_User::get(user_id)\' instead.', '0.5.0' );
				$user_id = $user;
				$user    = get_userdata( $user_id );
				if ( ! $user instanceof \WP_User ) {
					throw new Exception( $user_id . ' does not exist' );
				}
			}
			$user_objects[] = $this->get_user( $user );
		}

		return $user_objects;
	}

	/**
	 * Get user by user id.
	 *
	 * @param \WP_User|integer $user WP_User object (or deprecated User id).
	 *
	 * @return \Clarkson_User
	 */
	public function get_user( $user ) {
		if ( ! $user instanceof \WP_User ) {
			_doing_it_wrong( __METHOD__, 'Deprecated get_user called with an ID. Supply a \WP_User object or use \'Clarkson_User::get(user_id)\' instead.', '0.5.0' );
			$user_id = $user;
			if ( empty( $user_id ) ) {
				throw new Exception( $user_id . ' does not exist' );
			}
			$user = get_userdata( $user_id );
			if ( ! $user instanceof \WP_User ) {
				throw new Exception( $user_id . ' does not exist' );
			}
		}
		$cc         = Clarkson_Core::get_instance();
		$class_name = false;

		if ( $user->roles && count( $user->roles ) >= 1 ) {
			// get the first role in the array.
			$roles      = array_reverse( $user->roles );
			$role       = array_pop( $roles );
			$class_name = $cc->autoloader->user_objectname_prefix . $role;
		}

		if ( $class_name && in_array( $class_name, $cc->autoloader->user_types, true ) && class_exists( $class_name ) ) {
			return new $class_name( $user );
		}
		return new Clarkson_User( $user );
	}

	/**
	 * Get an array of posts converted to their corresponding WordPress object class.
	 *
	 * @param \WP_Post[] $posts Posts.
	 *
	 * @return \Clarkson_Object[] $objects Array of post objects.
	 */
	public function get_objects( $posts ) {
		$objects = array();

		foreach ( $posts as $post ) {
			$objects[] = $this->get_object( $post );
		}

		return $objects;
	}

	/**
	 * Get post that's converted to their corresponding WordPress object class.
	 *
	 * @param \WP_Post $post Post.
	 *
	 * @return \Clarkson_Object Clarkson Post object.
	 */
	public function get_object( $post ) {
		if ( ! $post instanceof WP_Post && is_int( (int) $post ) ) {
			_doing_it_wrong( __METHOD__, 'Deprecated calling of get_object with an ID. Use a `WP_Post` instead', '0.5.0' );
			$post = get_post( $post );
		}

		$cc = Clarkson_Core::get_instance();

		// defaults to post type.
		$type = get_post_type( $post );

		// Check if post has a custom template, if so, overwrite value.
		$page_template_slug = $cc->autoloader->get_template_filename( $post->ID );

		if ( ! empty( $page_template_slug ) ) {
			$type = $page_template_slug;
		}

		$type = $cc->autoloader->sanitize_object_name( $type );

		/**
		 * Allows the theme to overwrite class that is going to be used to create an object.
		 *
		 * @hook clarkson_object_type
		 * @since 0.1.1
		 * @param {string} $type Sanitized class name.
		 * @return {string} Class name of object to be created.
		 *
		 * @example
		 * // load a different class instead of what Clarkson Core calculates.
		 * add_filter( 'clarkson_object_type', function( $type ) {
		 *  if ( $type === 'gm_event' ){
		 *      $type = 'custom_event_class';
		 *  }
		 *  return $type;
		 * } );
		 */
		$type = apply_filters( 'clarkson_object_type', $type );

		/**
		 * Allows to control object creation before Clarkson Core determines the correct class to use. For example by calling "wc_get_product".
		 *
		 * @hook clarkson_core_create_object_callback
		 * @since 0.3.1
		 * @param {callable|bool} false Callable that determines how the class should be instantiated.
		 * False means no custom object creation will be used.
		 * @param $type {string} Sanitized class name of what Clarkson Core would load as an object.
		 * @param $post_id {int|string} Post ID that an object is being created for.
		 * @return {string} Class name of object to be created.
		 * @see https://github.com/level-level/Clarkson-Core/issues/131
		 *
		 * @example
		 * // Use a different object factory then the default one provided by Clarkson Core.
		 * add_filter( 'clarkson_core_create_object_callback', function( $callback, $type, $post_id ) {
		 *  if ( $type === 'shop_order' ){
		 *      $callback = 'wc_get_order'; // wc_get_order is a callable function when Woocommerce is enabled.
		 *  }
		 *  return $type;
		 * } );
		 */
		//
		$object_creation_callback = apply_filters( 'clarkson_core_create_object_callback', false, $type, $post->ID );
		if ( ! empty( $object_creation_callback ) ) {
			return $object_creation_callback( $post->ID );
		}

		if ( ( in_array( $type, $cc->autoloader->post_types, true ) || in_array( $type, $cc->autoloader->extra, true ) ) && class_exists( $type ) ) {
			return new $type( $post );
		}
		return new Clarkson_Object( $post );
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

		/**
		 * Default available objects, that are loaded into Clarkson as basic neccesities.
		 *
		 * @hook clarkson_available_objects
		 * @since 0.1.0
		 * @deprecated Clarkson Core >= 0.2.0 autoloads the neccessery objects.
		 * @param {string[]} $objects Class names to load.
		 * @return {string} Class names for Clarkson Core to load by default.
		 */
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
	 * @return Clarkson_Core_Objects
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
	 * Clone.
	 * @codeCoverageIgnore
	 */
	private function __clone() {
	}

	/**
	 * Wakeup.
	 * @codeCoverageIgnore
	 */
	private function __wakeup() {
	}

}
