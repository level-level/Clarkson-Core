<?php
/**
 * Allows for extension of Gutenberg blocks and overwriting rendering functions.
 *
 * @package CLARKSON\Lib
 * @since 0.4.0
 */

/**
 * This custom block overwrites the render callback to use twig files.
 * It is automatically injected into all registered blocks at render time.
 */
class Clarkson_Core_Gutenberg_Block_Type extends \WP_Block_Type {
	/**
	 * Replaces the original block render function, and saves the original render
	 * function in case we can't find a fitting twig file.
	 *
	 * @param string       $block_type Block type name including namespace.
	 * @param array|string $args       Optional. Array or string of arguments for registering a block type.
	 */
	public function __construct( $block_type, $args = array() ) {
		parent::__construct( $block_type, $args );
		$this->original_render_callback = $this->render_callback;
		$this->render_callback          = array( $this, 'clarkson_render_callback' );
	}

	/**
	 * Allows filtering of the path where this blocks twig file is found.
	 *
	 * @return string
	 */
	public function get_twig_template_path() {
		/**
		 * Allows theme to overwrite the directory in which Clarkson Core automatically loads twig templates for blocks.
		 *
		 * @hook clarkson_core_gutenberg_block_template_directory
		 * @since 0.4.0
		 * @param {string} '/templates/partials/blocks/gb/' Directory that will be used to automatically load gutenberg block templates.
		 * @return {string} Directory to use for autoloading twig templates.
		 *
		 * @example
		 * // Overwrite the Gutenberg block template directory.
		 * add_filter(
		 *  'clarkson_core_gutenberg_block_template_directory',
		 *  function() {
		 *      return '/templates/gutenberg/blocks/';
		 *  }
		 * );
		 */
		$block_directory = apply_filters( 'clarkson_core_gutenberg_block_template_directory', '/templates/partials/blocks/gb/' );

		/**
		 * Allows theme to overwrite the twig templates used for a specific block.
		 *
		 * @hook clarkson_core_gutenberg_block_template_{$name}
		 * @since 0.4.0
		 * @param {string} $template Original twig template that Clarkson Core is going to load for the block.
		 * @return {string} Path to twig template used to render a block.
		 *
		 * @example
		 * // Overwrite a specific block template.
		 * add_filter(
		 *  'clarkson_core_gutenberg_block_template_org/events',
		 *  function() {
		 *      return get_stylesheet_directory() . '/templates/gutenberg/alternative-event-template.twig';
		 *  }
		 * );
		 */
		return apply_filters( 'clarkson_core_gutenberg_block_template_' . $this->name, get_stylesheet_directory() . $block_directory . $this->name . '.twig', $this );
	}

	/**
	 * Tries to find a twig file to use for rendering. If the twig file doesn't
	 * exists it falls back to the original render callback.
	 * @param array  $attributes Block attributes.
	 * @param string $content    Block content.
	 * @return string Rendered block type output.
	 */
	public function clarkson_render_callback( $attributes, $content, $block = null, $post_id = 0 ) {
		if ( file_exists( $this->get_twig_template_path() ) ) {
			$cc_template              = Clarkson_Core_Templates::get_instance();
			$this->content_attributes = $attributes;
			return (string) $cc_template->render_twig(
				$this->get_twig_template_path(),
				array(
					'data'    => $attributes,
					'content' => $content,
					'block'   => $this,
				),
				true
			);
		}
		if ( is_callable( $this->original_render_callback ) ) {
			return (string) call_user_func( $this->original_render_callback, $attributes, $content, $block, $post_id );
		}
		return $content;
	}
}
