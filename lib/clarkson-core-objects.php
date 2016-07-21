<?php

class Clarkson_Core_Objects {

	protected $objects = array();

	public function available_objects(){
		return $this->objects;
	}

	public function get_term($term){
		if( !isset($term->taxonomy) || !isset($term->term_id))
			return;

		$taxonomy = strtolower($term->taxonomy);

		if( in_array($taxonomy, $this->objects) ){
			return new $taxonomy($term->term_id, $taxonomy);
		}else{
			return Clarkson_Term::get_by_id($term->term_id, $taxonomy);
		}
	}

	public function get_users($users_ids){
		$users = array();

		foreach ( $users_ids as $users_id ) {
			$users[] = $this->get_user($users_id);
		}

		return $users ;
	}

	public function get_user($users_id){
		if( in_array('User', $this->objects) ){
			return new User($users_id);
		}elseif( in_array('Clarkson_User', $this->objects) ){
			return new Clarkson_User($users_id);
		}
	}

	public function get_objects( $posts_ids )
	{
		$objects = array();

		foreach ( $posts_ids as $posts_id ) {
			$objects[] = $this->get_object($posts_id);
		}

		return $objects ;
	}

	public function get_object($post_id){
		$type = get_post_type( $post_id);
		$object_name = $this->camel_case($type);

		if( !in_array($object_name, $this->objects) ){
			if( in_array('Clarkson_Object', $this->objects) ){
				return new Clarkson_Object($post_id);
			}else{
				return $post_id;
			}
		}

		return new $object_name($post_id);
	}

	private function camel_case($str)
	{
		// non-alpha and non-numeric characters become underscores
		$str = preg_replace('/[^a-z0-9]+/i', '_', $str);
		$str = trim($str);
		// uppercase the first character of each word
		$str = ucwords($str);
		$str = str_replace(" ", "_", $str);

		return $str;
	}

	private function register_objects(){
		$plugin_path = dirname(__DIR__);
		$objects = array();

		$core_objects_path  = $plugin_path. '/post-objects';
		$core_objects = $this->get_objects_from_path( $core_objects_path  );

		foreach( $core_objects as $object_name=>$object_path){
			include_once($object_path);
			$objects[] = $object_name;
		}

		$theme_objects_path = get_template_directory() . '/post-objects';
		$theme_objects  = $this->get_objects_from_path( $theme_objects_path );


		// Theme overwrites plugins objects
		$theme_objects = apply_filters( 'clarkson_available_objects_paths', $theme_objects);

		if( isset($theme_objects['Page']) ){
			include_once($theme_objects['Page']);
			$objects[] = 'Page';
		}

		if( isset($theme_objects['Post']) ){
			include_once($theme_objects['Post']);
			$objects[] = 'Post';
		}

		// Load classes
		foreach( $theme_objects as $object_name=>$object_path){
			if( strpos( $object_name, '_tax_' ) !== false ) {
				$object_name = strtolower( $object_name );
			}

			if( in_array($object_name, $objects) )
				continue;

			include_once($object_path);
			$objects[] = $object_name;
		}

		$objects = apply_filters( 'clarkson_available_objects', $objects);

		$this->objects = $objects;
	}

	private function get_objects_from_path( $path )
	{
		$objects = array();

		if( !file_exists($path) )
			return $objects;

		$files = glob("{$path}/*.php");
		if( empty($files) )
			return $objects;

		foreach ( $files as $filepath){
			$path_parts = pathinfo($filepath);
			$class_name = $path_parts['filename'];
			$class_name = ucfirst($class_name);

			$objects[$class_name] = $filepath;
		}

		return $objects;
	}


	// Singleton
	protected $instance = null;

	public static function get_instance()
	{
		static $instance = null;

		if (null === $instance) {
			$instance = new Clarkson_Core_Objects();
		}

		return $instance;
	}

	protected function __construct()
	{
		$this->register_objects();
	}

	private function __clone()
	{
	}
	private function __wakeup()
	{
	}
}