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
			echo $filepath;
			require_once $filepath;
		}
	}

}