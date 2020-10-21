<?php
/**
 * Clarkson Core Objects.
 *
 * @package CLARKSON\Lib
 */

namespace Clarkson_Core;

use Clarkson_Core\Object\Clarkson_Object;
use Clarkson_Core\Object\Clarkson_Term;
use Clarkson_Core\Object\Clarkson_User;

/**
 * This class is used to convert WordPress posts, terms and users into Clarkson
 * Objects.
 */
class Objects {
	/**
	 * Get term data.
	 *
	 * @param \WP_Term $term The term.
	 *
	 * @return Clarkson_Term
	 */
	public function get_term( \WP_Term $term ): Clarkson_Term {
		$cc         = Clarkson_Core::get_instance();
		$class_name = $cc->autoloader->sanitize_object_name( $term->taxonomy );

		if ( in_array( $class_name, $cc->autoloader->taxonomies, true ) && class_exists( $class_name ) ) {
			$term_object = new $class_name( $term );
			if($term_object instanceof Clarkson_Term){
				return $term_object;
			}
		}
		return new Clarkson_Term( $term );
	}

	/**
	 * Get users by id.
	 *
	 * @param \WP_User[] $users array of \WP_User objects.
	 *
	 * @return Clarkson_User[]
	 */
	public function get_users( array $users ): array {
		$user_objects = array();

		foreach ( $users as $user ) {
			$user_objects[] = $this->get_user( $user );
		}

		return $user_objects;
	}

	/**
	 * Get user by user id.
	 *
	 * @param \WP_User $user WP_User object.
	 *
	 * @return Clarkson_User
	 */
	public function get_user( \WP_User $user ): Clarkson_User {
		$cc         = Clarkson_Core::get_instance();
		$class_name = false;

		if ( $user->roles && count( $user->roles ) >= 1 ) {
			// get the first role in the array.
			$roles      = array_reverse( $user->roles );
			$role       = array_pop( $roles );
			$class_name = $cc->autoloader->user_objectname_prefix . $role;
		}

		if ( $class_name && in_array( $class_name, $cc->autoloader->user_types, true ) && class_exists( $class_name ) ) {
			$user_object = new $class_name( $user );
			if($user_object instanceof Clarkson_User){
				return $user_object;
			}
		}
		return new Clarkson_User( $user );
	}

	/**
	 * Get an array of posts converted to their corresponding WordPress object class.
	 *
	 * @param \WP_Post[] $posts Posts.
	 *
	 * @return Clarkson_Object[] $objects Array of post objects.
	 */
	public function get_objects( array $posts ): array {
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
	 * @return Clarkson_Object Clarkson Post object.
	 */
	public function get_object( \WP_Post $post ): Clarkson_Object {
		$cc = Clarkson_Core::get_instance();

		// defaults to post type.
		$type = get_post_type( $post );

		// Check if post has a custom template, if so, overwrite value.
		$page_template_slug = $cc->autoloader->get_template_filename( $post->ID );

		if ( ! empty( $page_template_slug ) ) {
			$type = $page_template_slug;
		}

		if(empty($type)){
			$type = '';
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
		$object_creation_callback = apply_filters( 'clarkson_core_create_object_callback', false, $type, $post->ID );
		if ( is_callable( $object_creation_callback ) ) {
			$clarkson_object = call_user_func_array( $object_creation_callback, array( $post->ID ) );
			if($clarkson_object instanceof Clarkson_Object){
				return $clarkson_object;
			}
		}

		if ( ( in_array( $type, $cc->autoloader->post_types, true ) || in_array( $type, $cc->autoloader->extra, true ) ) && class_exists( $type ) ) {
			$clarkson_object = new $type( $post );
			if($clarkson_object instanceof Clarkson_Object){
				return $clarkson_object;
			}
		}
		return new Clarkson_Object( $post );
	}

	/**
	 * Singleton.
	 *
	 * @var null|\Clarkson_Core\Objects $instance The Clarkson core objects.
	 */
	protected static $instance;

	/**
	 * Get the instance.
	 *
	 * @return \Clarkson_Core\Objects
	 */
	public static function get_instance(): \Clarkson_Core\Objects {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
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
