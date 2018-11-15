<?php

class Clarkson_Core_Gutenberg_Block_Manager{
	public function init(){
		add_filter( 'the_content', array($this, 'intercept_gutenberg_rendering'), 1 );
	}

	protected function determine_block_type_class($block_type){
		$class_name = '\\Gutenberg\\Blocks\\' . $this->sanitize_block_type_name($block_type->name);
		if(class_exists($class_name)){
			return $class_name;
		}
		return '\Clarkson_Core_Gutenberg_Block_Type';
	}

	protected function sanitize_block_type_name($str){
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

	public function intercept_gutenberg_rendering($content){
		$block_registry = \WP_Block_Type_Registry::get_instance();
		foreach($block_registry->get_all_registered() as $original_block){
			$block_type = $this->determine_block_type_class($original_block);
			$clarkson_block = new $block_type($original_block->name, get_object_vars( $original_block ));
			$block_registry->unregister($original_block);
			$block_registry->register($clarkson_block);
		}
		return $content;
	}
}
