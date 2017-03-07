<?php

class Clarkson_Core_Objects {

	protected $objects = array();

	public function available_objects(){
		return $this->objects;
	}

	public function get_term($term){
		if( !isset($term->taxonomy) || !isset($term->term_id))
			return;

		$cc = Clarkson_Core::get_instance();
		$taxonomy = $cc->autoloader->clean_name($term->taxonomy);

		if( in_array($taxonomy, $cc->autoloader->taxonomies) ){
			$object_name = $this->camel_case($taxonomy);
			return new $object_name($term->term_id, $term->taxonomy);
		}
		return Clarkson_Term::get_by_id($term->term_id, $taxonomy);
	}

	public function get_users($users_ids){
		$users = array();

		foreach ( $users_ids as $users_id ) {
			$users[] = $this->get_user($users_id);
		}

		return $users;
	}

	public function get_user($users_id){
		$cc = Clarkson_Core::get_instance();
		if( in_array('user', $cc->autoloader->user_types) ){
			return new User($users_id);
		}
		return new Clarkson_User($users_id);
	}

	public function get_objects( $posts_ids )
	{
		$objects = array();

		foreach ( $posts_ids as $posts_id ) {
			$objects[] = $this->get_object($posts_id);
		}

		return $objects;
	}

	public function get_object($post_id){
		$type = get_post_type( $post_id);
		$type = apply_filters( 'clarkson_object_type', $type );
		$object_name = $this->camel_case($type);
		$cc = Clarkson_Core::get_instance();
		if( !in_array($type, $cc->autoloader->post_types) ){
			return new Clarkson_Object($post_id);
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
		$objects = array("Clarkson_Object"=>"", "Clarkson_Term"=>"", "Clarkson_User"=>"");

		$deprecated = Clarkson_Core_Deprecated::get_instance();
		$deprecated_objects = $deprecated->get_theme_objects();
		$objects = array_merge($objects, $deprecated_objects);

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