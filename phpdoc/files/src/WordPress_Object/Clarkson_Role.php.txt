<?php
/**
 * Clarkson Role.
 */

namespace Clarkson_Core\WordPress_Object;

use Clarkson_Core\Objects;

/**
 * Object oriented wrapper for WP_Role objects.
 */
class Clarkson_Role {
	/**
	 * WordPress representation of this role object.
	 *
	 * @var \WP_Role
	 */
	protected $_role;

	/**
	 * Get Clarkson Role object by role name.
	 *
	 * @param string $name Role name.
	 */
	public static function get_by_name( $name ): ?Clarkson_Role {
		$role = wp_roles()->get_role( $name );
		if ( ! $role instanceof \WP_Role ) {
			return null;
		}

		return Objects::get_instance()->get_role( $role );
	}

	/**
	 * Get Clarkson Role object by role name.
	 *
	 * @param string $name Role name.
	 */
	public static function get( $name ): ?Clarkson_Role {
		return static::get_by_name( $name );
	}

	/**
	 * Get all available Clarkson Role objects
	 *
	 * @return \Clarkson_Core\WordPress_Object\Clarkson_Role[]
	 */
	public static function get_many(): array {
		return Objects::get_instance()->get_roles( wp_roles()->role_objects );
	}

	/**
	 * Clarkson_Role constructor.
	 */
	public function __construct( \WP_Role $role ) {
		$this->_role = $role;
	}

	/**
	 * Get the WordPress WP_Role object.
	 */
	public function get_role(): \WP_Role {
		return $this->_role;
	}
}
