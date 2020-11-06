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
	 * Get role data by role name.
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
