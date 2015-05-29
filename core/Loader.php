<?php
namespace YallaYalla;

class Loader
{
	protected $post_objects = array();
	protected $instance = null;

	public static function get_instance()
		{
		static $instance = null;
		if (null === $instance) {
			$instance = new Loader();
		}

		return $instance;
	}

	protected function __construct(){

		$this->register_objects();
	}

	public function get_objects( $posts )
	{
		$objects = array();

		foreach ( $posts as $post ) {
			$objects[] = $this->get_object($post);
		}

		return $objects ;
	}

	public function get_object($post){
		$object_name = $this->camel_case($post->post_type);

		if( !in_array($object_name, $this->post_objects) ){
			if( in_array('Post', $this->post_objects) ){
				return new Post($post);
			}else{
				return $post;
			}
		}

		return new $object_name($post->ID);
	}

	private function register_objects()
	{
		$default_objects_path  = __DIR__ . '/vendor/humanmade/wordpress-objects';
		$extended_objects_path = get_template_directory() . '/objects';

		$default_classes   = $this->get_objects_from_path($default_objects_path); 
		$extended_classes = $this->get_objects_from_path($extended_objects_path);

		$object_paths = $extended_classes + $default_classes ;
		$object_paths = apply_filters( 'yalla_available_objects_paths', $object_paths);

		$objects = array();
		// Load classes
		foreach( $object_paths as $object_name=>$object_path){
			include_once($object_path);
			$objects[] = $object_name;
		}

		$objects = apply_filters( 'yalla_available_objects', $objects);

		$this->post_objects = $objects;
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

			// Replacement so that humanmade post objects work
			if( $class_name === 'wordpress-objects')
				continue;

			$class_name = preg_replace('/wordpress-objects\./', '', $class_name);
			$class_name = ucfirst($class_name);

			$objects[$class_name] = $filepath;
		}

		return $objects;
	}

	public static function camel_case($str)
	{
		// non-alpha and non-numeric characters become spaces
		$str = preg_replace('/[^a-z0-9]+/i', ' ', $str);
		$str = trim($str);
		// uppercase the first character of each word
		$str = ucwords($str);
		$str = str_replace(" ", "", $str);

		return $str;
	}
	
}