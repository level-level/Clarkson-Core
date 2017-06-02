<?php

class Clarkson_Core_Autoloader{
	public $post_types = array();
	public $taxonomies = array();
	public $user_types = array();

	public function __construct(){
		spl_autoload_register(array($this, 'load_wordpress_object'), true, true);
		add_action( 'registered_post_type', array($this, 'registered_post_type'), 10, 1 );
		add_action( 'registered_taxonomy', array($this, 'registered_taxonomy'), 10, 1 );

		$filepath = realpath(get_template_directory()). "/wordpress-objects/User.php";
		if(file_exists($filepath)){
			$this->user_types['user'] = 'user';
		}
	}

	/**
	 * Prepares object names
	 */
	public function sanitize_object_name( $str ){
		$str = trim($str);

		// Replace - with _
		// non-alpha and non-numeric characters become underscores
 		// We can't run `new ll-events()` because that's an invalid classname.
		$str = preg_replace('/[^a-z0-9]+/ig', '_', $str);

		// String to lowercase is require by post-type namingconvention :
		// https://codex.wordpress.org/Function_Reference/register_post_type#post_type
		$str = strtolower( $str );

		return $str;
	}

	/**
	 * Fill $post_type variable with all registered CPT's
	 *
	 * This also means reserved ones like page, post, attachment, revision, nav_menu_item,
	 * custom_css and customize_changeset.
	 */
	public function registered_post_type($post_type){
		$this->post_types[$this->sanitize_object_name($post_type)] = $this->sanitize_object_name($post_type);
	}

	/**
	 * Fill $taxonomies variable with all registered Taxonomies
	 */
	public function registered_taxonomy($taxonomy){
		$this->taxonomies[$this->sanitize_object_name($taxonomy)] = $this->sanitize_object_name($taxonomy);
	}

	protected function load_wordpress_object($classname){
		$type = $this->sanitize_object_name($classname);

		if(!in_array($type, $this->post_types) && !in_array($type, $this->taxonomies)){
			return;
		}
		$filename = "{$classname}.php";

		// Load child theme first
		$filepath = realpath(get_stylesheet_directory()). "/wordpress-objects/{$filename}";
		if(file_exists($filepath)){
			include_once($filepath);
			return;
		}

		// If not exists then load normal / parent theme
		$filepath = realpath(get_template_directory()). "/wordpress-objects/{$filename}";
		if(file_exists($filepath)){
			include_once($filepath);
			return;
		}
	}
}
