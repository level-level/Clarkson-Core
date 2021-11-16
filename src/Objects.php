<?php
/**
 * Clarkson Core Objects.
 */

namespace Clarkson_Core;

use Clarkson_Core\WordPress_Object\Clarkson_Object;
use Clarkson_Core\WordPress_Object\Clarkson_Post_Type;
use Clarkson_Core\WordPress_Object\Clarkson_Role;
use Clarkson_Core\WordPress_Object\Clarkson_Template;
use Clarkson_Core\WordPress_Object\Clarkson_Term;
use Clarkson_Core\WordPress_Object\Clarkson_User;
use DomainException;

/**
 * This class is used to convert WordPress posts, terms and users into Clarkson
 * Objects.
 */
class Objects {
	const OBJECT_CLASS_NAMESPACE = '\\Clarkson_Core\\WordPress_Object\\';
	/**
	 * Convert WP_Term object to a Clarkson Object.
	 *
	 * @param \WP_Term[] $terms array of \WP_Term objects.
	 *
	 * @return Clarkson_Term[]
	 */
	public function get_terms( array $terms ): array {
		$term_objects = array();

		foreach ( $terms as $term ) {
			$term_objects[] = $this->get_term( $term );
		}

		return $term_objects;
	}

	/**
	 * Get term data.
	 *
	 * @param \WP_Term $term The term.
	 *
	 * @return Clarkson_Term
	 */
	public function get_term( \WP_Term $term ): Clarkson_Term {
		$cc    = Clarkson_Core::get_instance();
		$types = array(
			self::OBJECT_CLASS_NAMESPACE . $cc->autoloader->sanitize_object_name( $term->taxonomy ),
			self::OBJECT_CLASS_NAMESPACE . 'base_term',
		);

		/**
		 * Allows the theme to overwrite classes to look for when creating an object.
		 *
		 * @hook clarkson_term_types
		 * @since 1.0.0
		 * @param {array} $typse Sanitized class names to load.
		 * @param {WP_Term} $term Term which we are trying to convert into an object.
		 * @return {array} Class names to search for.
		 *
		 * @example
		 * // load a different class instead of what Clarkson Core calculates.
		 * add_filter( 'clarkson_term_types', function( $types, $term ) {
		 *  if ( $term->taxonomy === 'gm_category' ){
		 *      array_unshift($types, self::OBJECT_CLASS_NAMESPACE . 'custom_taxonomy_class';
		 *  }
		 *  return $types;
		 * }, 10, 2 );
		 */
		$types = apply_filters( 'clarkson_term_types', $types, $term );

		foreach ( $types as $type ) {
			if ( class_exists( $type ) ) {
				$term_object = new $type( $term );
				if ( $term_object instanceof Clarkson_Term ) {
					return $term_object;
				}
			}
		}

		return new Clarkson_Term( $term );
	}

	/**
	 * Convert WP_User object to a Clarkson Object.
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
		/**
		 * @psalm-var string
		 */
		$type = self::OBJECT_CLASS_NAMESPACE . 'user';

		/**
		 * Allows the theme to overwrite class that is going to be used to create a user.
		 *
		 * @hook clarkson_user_type
		 * @since 1.0.0
		 * @param {null|string} $type Sanitized class name.
		 * @param {WP_User} $user Sanitized class name.
		 * @return {null|string} Class name of user to be created.
		 *
		 * @example
		 * // load a different class instead of what Clarkson Core calculates.
		 * add_filter( 'clarkson_user_type', function( $type, $user ) {
		 *  if ( user_can( $user, 'read' ) ){
		 *      $type = self::OBJECT_CLASS_NAMESPACE . 'custom_user_class';
		 *  }
		 *  return $type;
		 * }, 10, 2 );
		 */
		$type = apply_filters( 'clarkson_user_type', $type, $user );

		if ( class_exists( $type ) ) {
			$user_object = new $type( $user );
			if ( $user_object instanceof Clarkson_User ) {
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

		$types = array(
			self::OBJECT_CLASS_NAMESPACE . 'base_object',
			Clarkson_Object::class,
		);

		$type = get_post_type( $post );
		if ( ! empty( $type ) ) {
			$type = $cc->autoloader->sanitize_object_name( $type );
			array_unshift( $types, self::OBJECT_CLASS_NAMESPACE . $type );
		}

		$class_to_load = null;
		foreach ( $types as $type ) {
			if ( class_exists( $type ) ) {
				$class_to_load = $type;
				break;
			}
		}

		/**
		 * Allows the theme to overwrite class that is going to be used to create an object.
		 *
		 * @hook clarkson_object_type
		 * @since 0.1.1
		 * @param {null|string} $type Sanitized class name.
		 * @param {WP_Post} $post Sanitized class name.
		 * @return {null|string} Class name of object to be created.
		 *
		 * @example
		 * // load a different class instead of what Clarkson Core calculates.
		 * add_filter( 'clarkson_object_type', function( $type, $post ) {
		 *  if ( get_post_type( $post ) === 'gm_event' ){
		 *      $type = self::OBJECT_CLASS_NAMESPACE . 'custom_event_class';
		 *  }
		 *  return $type;
		 * }, 10, 2 );
		 */
		$class_to_load = apply_filters( 'clarkson_object_type', $class_to_load, $post );

		if ( null === $class_to_load ) {
			throw new DomainException( sprintf( 'No valid Clarkson Object was loaded. Tried: %s.', implode( ', ', $types ) ) );
		}

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
		 *  if ( $type === '\Clarkson_Core\Object\shop_order' ){
		 *      $callback = 'wc_get_order'; // wc_get_order is a callable function when Woocommerce is enabled.
		 *  }
		 *  return $type;
		 * } );
		 */
		$object_creation_callback = apply_filters( 'clarkson_core_create_object_callback', false, $class_to_load, $post->ID );
		if ( is_callable( $object_creation_callback ) ) {
			$clarkson_object = call_user_func_array( $object_creation_callback, array( $post->ID ) );
			if ( $clarkson_object instanceof Clarkson_Object ) {
				return $clarkson_object;
			}
		}

		$clarkson_object = new $class_to_load( $post );
		if ( $clarkson_object instanceof Clarkson_Object ) {
			return $clarkson_object;
		}
		throw new DomainException( sprintf( 'No valid Clarkson Object was loaded. Tried to find %s. Make sure it extends Clarkson_Object.', $class_to_load ) );
	}

	/**
	 * Get an array of roles converted from their corresponding WordPress role class.
	 *
	 * @param \WP_Role[] $roles Array of WordPress role objects.
	 *
	 * @return Clarkson_Role[] $objects Array of post objects.
	 */
	public function get_roles( array $roles ): array {
		$clarkson_roles = array();

		foreach ( $roles as $role ) {
			$clarkson_roles[] = $this->get_role( $role );
		}

		return $clarkson_roles;
	}

	/**
	 * Get Clarkson role object by WordPress role object.
	 */
	public function get_role( \WP_Role $role ): Clarkson_Role {
		$cc = Clarkson_Core::get_instance();

		$class_name = 'role_' . $role->name;
		$class_name = self::OBJECT_CLASS_NAMESPACE . $cc->autoloader->sanitize_object_name( $class_name );

		/**
		 * Allows the theme to overwrite class that is going to be used to create a role object.
		 *
		 * @hook clarkson_role_class
		 * @since 1.0.0
		 * @param {null|string} $type Sanitized class name.
		 * @param {WP_Role} $role Sanitized class name.
		 * @return {null|string} Class name of role to be created.
		 *
		 * @example
		 * // load a different class instead of what Clarkson Core calculates.
		 * add_filter( 'clarkson_role_class', function( $type, $role ) {
		 *  if ( $role->name === 'example' ){
		 *      $type = self::OBJECT_CLASS_NAMESPACE . 'custom_role_class';
		 *  }
		 *  return $type;
		 * }, 10, 2 );
		 */
		$class_name = apply_filters( 'clarkson_role_class', $class_name, $role );
		if ( class_exists( $class_name ) ) {
			$object = new $class_name( $role );
			if ( $object instanceof Clarkson_Role ) {
				return $object;
			}
		}

		/**
		 * @psalm-var string
		 */
		$class_name = self::OBJECT_CLASS_NAMESPACE . 'base_role';
		if ( class_exists( $class_name ) ) {
			$object = new $class_name( $role );
			if ( $object instanceof Clarkson_Role ) {
				return $object;
			}
		}

		return new Clarkson_Role( $role );
	}

	public function get_template( \WP_Post $post ): Clarkson_Template {
		$template = get_page_template_slug( $post );
		$template = pathinfo( (string) $template, PATHINFO_FILENAME );

		$template = Clarkson_Core::get_instance()->autoloader->sanitize_object_name( $template );
		if ( empty( $template ) ) {
			$template = 'template_default';
		}

		$class_name = self::OBJECT_CLASS_NAMESPACE . $template;
		/**
		 * Allows the theme to overwrite class that is going to be used to create a template object.
		 *
		 * @hook clarkson_template_class
		 * @since 1.0.0
		 * @param {null|string} $type Sanitized class name.
		 * @param {WP_Post} $post The WordPress post to load a template for.
		 * @return {null|string} Class name of template to be created.
		 *
		 * @example
		 * // load a different class instead of what Clarkson Core calculates.
		 * add_filter( 'clarkson_template_class', function( $class, $post ) {
		 *  if ( $post->ID === 15 ){
		 *      $class = self::OBJECT_CLASS_NAMESPACE . 'custom_template_class';
		 *  }
		 *  return $class;
		 * }, 10, 2 );
		 */
		$class_name = apply_filters( 'clarkson_template_class', $class_name, $post );
		if ( class_exists( $class_name ) ) {
			$object = new $class_name( $post );
			if ( $object instanceof Clarkson_Template ) {
				return $object;
			}
		}

		/**
		 * @psalm-var string
		 */
		$class_name = self::OBJECT_CLASS_NAMESPACE . 'base_template';
		if ( class_exists( $class_name ) ) {
			$object = new $class_name( $post );
			if ( $object instanceof Clarkson_Template ) {
				return $object;
			}
		}

		return new Clarkson_Template( $post );
	}

	/**
	 * Get an array of posts converted to their corresponding WordPress template class.
	 *
	 * @param \WP_Post[] $posts Posts.
	 *
	 * @return Clarkson_Template[] $objects Array of post templates.
	 */
	public function get_templates( array $posts ): array {
		$objects = array();

		foreach ( $posts as $post ) {
			$objects[] = $this->get_template( $post );
		}

		return $objects;
	}

	/**
	 * Get an array of post types converted from their corresponding WordPress post type class.
	 *
	 * @param \WP_Post_Type[] $post_types WordPress post type objects
	 *
	 * @return Clarkson_Post_Type[] $objects Array of Clarkson post type objects.
	 */
	public function get_post_types( array $post_types ): array {
		$objects = array();

		foreach ( $post_types as $post_type ) {
			$objects[] = $this->get_post_type( $post_type );
		}

		return $objects;
	}

	/**
	 * Get post type object by post type.
	 */
	public function get_post_type( \WP_Post_Type $post_type ):Clarkson_Post_Type {
		$cc = Clarkson_Core::get_instance();

		$class_name = 'post_type_' . $post_type->name;
		$class_name = self::OBJECT_CLASS_NAMESPACE . $cc->autoloader->sanitize_object_name( $class_name );

		/**
		 * Allows the theme to overwrite class that is going to be used to create a post_type object.
		 *
		 * @hook clarkson_post_type_class
		 * @since 1.0.0
		 * @param {null|string} $type Sanitized class name.
		 * @param {WP_Post_Type} $post_type The original post type object.
		 * @return {null|string} Class name of post_type to be created.
		 *
		 * @example
		 * // load a different class instead of what Clarkson Core calculates.
		 * add_filter( 'clarkson_post_type_class', function( $type, $post_type ) {
		 *  if ( $post_type->name === 'example' ){
		 *      $type = self::OBJECT_CLASS_NAMESPACE . 'custom_post_type_class';
		 *  }
		 *  return $type;
		 * }, 10, 2 );
		 */
		$class_name = apply_filters( 'clarkson_post_type_class', $class_name, $post_type );
		if ( class_exists( $class_name ) ) {
			$object = new $class_name( $post_type );
			if ( $object instanceof Clarkson_Post_Type ) {
				return $object;
			}
		}

		/**
		 * @psalm-var string
		 */
		$class_name = self::OBJECT_CLASS_NAMESPACE . 'base_post_type';
		if ( class_exists( $class_name ) ) {
			$object = new $class_name( $post_type );
			if ( $object instanceof Clarkson_Post_Type ) {
				return $object;
			}
		}

		return new Clarkson_Post_Type( $post_type );
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
	public function __wakeup() {
	}
}
