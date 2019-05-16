<?php

class Clarkson_Core_Gutenberg_Block_Type extends \WP_Block_Type {
	public function __construct( $block_type, $args = array() ) {
		parent::__construct( $block_type, $args );
		$this->original_render_callback = $this->render_callback;
		$this->render_callback          = array( $this, 'clarkson_render_callback' );
	}

	public function get_twig_template_path() {
		return get_stylesheet_directory() . '/templates/gutenberg/blocks/' . $this->name . '.twig';
	}

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
