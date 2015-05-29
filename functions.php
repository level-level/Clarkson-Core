<?php
if ( file_exists( $composer_autoload = __DIR__ . '/core/vendor/autoload.php' ) || 
	 file_exists( $composer_autoload = WP_CONTENT_DIR.'/core/vendor/autoload.php' ) 
) {
	require_once $composer_autoload;
	
	YallaYalla\Base::get_instance();

	// You're normal functions will be loaded from the functions directory

}else{
	echo "Theme will not work, try running composer install first";
}

