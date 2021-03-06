<?php

use Clarkson_Core\Templates;

class ClarksonCoreTemplatesTest extends \WP_Mock\Tools\TestCase {
	public function setUp():void {
		\WP_Mock::setUp();
	}

	public function tearDown():void {
		\WP_Mock::tearDown();
	}

	public function test_can_get_instance() {
		\WP_Mock::userFunction( 'get_template_directory', '/tmp/wp-content/themes/theme/' );
		\WP_Mock::userFunction( 'get_stylesheet_directory', '/tmp/wp-content/themes/child-theme/' );
		\WP_Mock::userFunction( 'get_bloginfo' )->with( 'version' )->andReturn( '4.7' );
		$cc_templates = Templates::get_instance();
		$this->assertInstanceOf( Templates::class, $cc_templates );
		return $cc_templates;
	}
}
