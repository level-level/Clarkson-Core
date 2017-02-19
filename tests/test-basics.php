<?php

class BasicsTest extends WP_UnitTestCase {

	var $ct;

	function __construct(){
		parent::__construct();
		$this->init();
		$this->ct = Clarkson_Core_Templates::get_instance();
	}

	function init(){
		// Register a dir where to search for Twig templates
		add_filter('clarkson_twig_template_dir', function(){
			return [ plugin_dir_path( __FILE__ ) . 'data/templates' ];
		});
	}

	function test_sample() {

		$base_render = $this->ct->render_twig( 'base.twig', []);
		$this->assertTrue( strpos( $base_render, '<title>Base</title>' ) !== false );

	}
}

