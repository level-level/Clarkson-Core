<?php
namespace YallaYalla\Utils;

class Helper{

	protected $instance = null;

	public static function get_instance()
		{
		static $instance = null;
		if (null === $instance) {
			$instance = new Helper();
		}

		return $instance;
	}

	public function autoload_path($path){
		if( !file_exists($path) )
			return;

		$files = glob("{$path}/*.php");

		if( empty($files) )
			return;

		foreach ( $files as $filepath){
			require_once $filepath;
		}
	}

	public function get_manifest(){
		$template_path = get_template_directory();
		$config_path = "{$template_path}/source/manifest.json";

		if( !file_exists($config_path) ){
			return;
		}

		$config_file = file_get_contents($config_path);
		$config = json_decode($config_file);

		return $config;
	}

	public function set_manifest($config){
		
		if( empty($config) )
			return;

		$template_path = get_template_directory();
		$config_path = "{$template_path}/source/manifest.json";

		$config_json = json_encode($config, JSON_PRETTY_PRINT);
		
		file_put_contents($config_path, $config_json);
	}

}