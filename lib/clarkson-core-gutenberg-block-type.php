<?php

/**
 * This custom block overwrites the render callback to use twig files.
 * It is automatically injected into all registered blocks at render time.
 */
class Clarkson_Core_Gutenberg_Block_Type extends \WP_Block_Type {
	/**
	 * Replaces the original block render function, and saves the original render
	 * function in case we can't find a fitting twig file.
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
		return apply_filters( 'clarkson_core_gutenberg_block_template_' . $this->name, get_stylesheet_directory() . '/templates/partials/blocks/gb/' . $this->name . '.twig', $this );
	}

	/**
	 * Tries to find a twig file to use for rendering. If the twig file doesn't
	 * exists it falls back to the original render callback.
	 *
	 * @return string
	 */
	public function clarkson_render_callback( $attributes, $content ) {
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
			return (string) call_user_func( $this->original_render_callback, $attributes, $content );
		}
		return $content;
	}
}
