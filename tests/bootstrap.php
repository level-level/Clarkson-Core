<?php

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

if ( false !== getenv( 'WP_PLUGIN_DIR' ) ) {
  define( 'WP_PLUGIN_DIR', getenv( 'WP_PLUGIN_DIR' ) );
}

if ( false !== getenv( 'PLUGIN_DIR' ) ) {
	define( 'CLARKSON_CORE_DIR', getenv( 'PLUGIN_DIR' ) );
}



require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/clarkson-core.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';
