<?php
/**
 * Integrates Clarkson and Gutenberg to allow for twig rendering of blocks.
 *
 * @since 0.4.0
 */

namespace Clarkson_Core\Gutenberg;

use WP_Block_Type;

/**
 * Intercepts 'the content' filter to allow overriding of the rendering functions
 * of Gutenberg blocks. This allows us to use twig for block rendering.
 */
class Block_Manager {
	/**
	 * Hook in as soon as we can, to replace blocks with the Clarkson-block equivalent.
	 * @internal
	 */
	public function init():void {
		if ( class_exists( '\WP_Block_Type_Registry' ) ) {
			add_filter( 'the_content', array( $this, 'intercept_gutenberg_rendering' ), 1 );
		}
	}

	/**
	 * Attempts to create a custom block from class from a block. Falls back
	 * to a default Clarkson Core block type.
	 *
	 * @param \WP_Block_type $block_type Gutenberg block to determine class for.
	 * @return string
	 */
	public function determine_block_type_class( $block_type ) {
		$class_name = '\\Gutenberg\\Blocks\\' . $this->sanitize_block_type_name( $block_type->name );

		/**
		 * Allows the theme to overwrite the class loaded by Clarkson Core.
		 *
		 * @hook clarkson_core_gutenberg_block_class
		 * @since 0.4.0
		 * @param {string} $class_name Name of a calculated class that Clarkson Core would load for this block.
		 * @param {WP_Block_type} $block_type The WordPress block that generates the content.
		 * @return {string} Class name of object to load for this block.
		 *
		 * @example
		 * // Change default block class.
		 * add_filter( 'clarkson_core_gutenberg_block_class', function( $class_name ){
		 *  if ( ! class_exists( $class_name ) ) {
		 *      return '\CustomDefaultBlock';
		 *  }
		 *  return $class_name;
		 * } );
		 */
		$class_name = apply_filters( 'clarkson_core_gutenberg_block_class', $class_name, $block_type );

		/**
		 * Allows the theme to overwrite the class loaded by Clarkson Core.
		 *
		 * @hook clarkson_core_gutenberg_block_class_{$name}
		 * @since 0.4.0
		 * @param {string} $class_name Name of a calculated class that Clarkson Core would load for this block.
		 * @param {WP_Block_type} $block_type The WordPress block that generates the content.
		 * @return {string} Classname of object to load for this block.
		 */
		$class_name = apply_filters( 'clarkson_core_gutenberg_block_class_' . $block_type->name, $class_name, $block_type );
		if ( class_exists( $class_name ) ) {
			return $class_name;
		}

		/**
		 * @psalm-var string
		 */
		$class_name = '\\Gutenberg\\Blocks\\base_block';
		if ( class_exists( $class_name ) ) {
			return $class_name;
		}

		return Block_Type::class;
	}

	/**
	 * Filters a block name into a valid class name.
	 *
	 * @param string $str Classname to be sanitized.
	 * @return string
	 */
	public function sanitize_block_type_name( $str ) {
		$str = trim( $str );

		// Replace - with _ .
		// Non-alpha and non-numeric characters become underscores.
		// We can't run `new ll-events()` because that's an invalid class name.
		$str = preg_replace( '/[^a-z0-9]+/i', '_', $str );

		// String to lowercase is require by post-type naming convention.
		// See https://codex.wordpress.org/Function_Reference/register_post_type#post_type.
		$str = strtolower( $str );

		return $str;
	}

	/**
	 * Replaces all registered non-core blocks with our intermediate class, so we
	 * can manipulate the rendering function.
	 *
	 * Does not manipulate the $content.
	 *
	 * @param string $content  The content string to be ouputted. Not manipulated
	 * at this stage.
	 * @return string
	 *
	 * @internal
	 */
	public function intercept_gutenberg_rendering( $content ) {
		$block_registry = \WP_Block_Type_Registry::get_instance();
		foreach ( $block_registry->get_all_registered() as $original_block ) {
			$block_type     = $this->determine_block_type_class( $original_block );
			$clarkson_block = new $block_type( $original_block->name, get_object_vars( $original_block ) );
			if ( $clarkson_block instanceof WP_Block_Type ) {
				$block_registry->unregister( $original_block );
				$block_registry->register( $clarkson_block );
			}
		}
		return $content;
	}
}
