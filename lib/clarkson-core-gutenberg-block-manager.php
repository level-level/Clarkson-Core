<?php

/**
 * Intercepts 'the content' filter to allow overriding of the rendering functions
 * of Gutenberg blocks. This allows us to use twig for block rendering.
 */
class Clarkson_Core_Gutenberg_Block_Manager {
	/**
	 * Hook in as soon as we can, to replace blocks with the Clarkson-block equivalent.
	 */
	public function init() {
		add_filter( 'the_content', array( $this, 'intercept_gutenberg_rendering' ), 1 );
	}

	/**
	 * Attempts to create a custom block from class from a block. Falls back
	 * to a default Clarkson Core block type.
	 *
	 * @return string
	 */
	protected function determine_block_type_class( $block_type ) {
		$class_name = '\\Gutenberg\\Blocks\\' . $this->sanitize_block_type_name( $block_type->name );
		$class_name = apply_filters( 'clarkson_core_gutenberg_block_class', $class_name, $block_type );
		$class_name = apply_filters( 'clarkson_core_gutenberg_block_class_' . $block_type->name, $class_name, $block_type );
		if ( class_exists( $class_name ) ) {
			return $class_name;
		}
		return '\Clarkson_Core_Gutenberg_Block_Type';
	}

	/**
	 * Filters a block name into a valid class name.
	 *
	 * @return string
	 */
	protected function sanitize_block_type_name( $str ) {
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
	 * @return string
	 */
	public function intercept_gutenberg_rendering( $content ) {
		$block_registry = \WP_Block_Type_Registry::get_instance();
		foreach ( $block_registry->get_all_registered() as $original_block ) {
			$block_type     = $this->determine_block_type_class( $original_block );
			$clarkson_block = new $block_type( $original_block->name, get_object_vars( $original_block ) );
			$block_registry->unregister( $original_block );
			$block_registry->register( $clarkson_block );
		}
		return $content;
	}
}
