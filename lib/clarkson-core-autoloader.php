<?php

class Clarkson_Core_Autoloader{
  public $post_types = array('post'=>'post', 'page'=>'page');

  public function __construct(){
    spl_autoload_register(array($this, 'load_wordpress_object'), true, true);
    add_action( 'registered_post_type', array($this, 'register_post_type'), 10, 2 );
  }

  public function register_post_type($post_type, $args){
    $this->post_types[$post_type] = $post_type;
  }

  protected function load_wordpress_object($classname){
    $filename = "{$classname}.php";
    $filepath = realpath(get_template_directory()). "/wordpress-objects/{$filename}";
    if(file_exists($filepath)){
      include_once($filepath);
    }
  }
}