<?php

class Clarkson_Core_Autoloader{
	public $post_types = array('post'=>'post', 'page'=>'page');
	public $taxonomies = array();
	public $user_types = array();

	public function __construct(){
		spl_autoload_register(array($this, 'load_wordpress_object'), true, true);
		add_action( 'registered_post_type', array($this, 'registered_post_type'), 10, 1 );
		add_action( 'registered_taxonomy', array($this, 'registered_taxonomy'), 10, 1 );

		$filepath = realpath(get_template_directory()). "/wordpress-objects/User.php";
		if(file_exists($filepath)){
			$this->$user_types['user'] = 'user';
		}
	}

	protected function clean_name($name){
		return str_replace('-', '_', strtolower($name));
	}

	public function registered_post_type($post_type){
		$this->post_types[$this->clean_name($post_type)] = $this->clean_name($post_type);
	}

	public function registered_taxonomy($taxonomy){
		$this->taxonomies[$this->clean_name($taxonomy)] = $this->clean_name($taxonomy);
	}

	protected function load_wordpress_object($classname){
		$type = $this->clean_name($classname);
		if(!in_array($type, $this->post_types) && !in_array($type, $this->taxonomies)){
			return;
		}
		$filename = "{$classname}.php";
		$filepath = realpath(get_template_directory()). "/wordpress-objects/{$filename}";
		if(file_exists($filepath)){
			include_once($filepath);
		}
	}
}