<?php

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
		$cc_templates = \Clarkson_Core_Templates::get_instance();
		$this->assertInstanceOf( \Clarkson_Core_Templates::class, $cc_templates );
		return $cc_templates;
	}

	/**
	 * @depends test_can_get_instance
	 */
	public function test_can_add_template( $cc_templates ) {
		$term           = Mockery::mock( '\WP_Term' );
		$term->taxonomy = 'category';
		\WP_Mock::userFunction( 'get_queried_object' )->andReturn( $term );
		\WP_Mock::userFunction( 'current_filter', 'category_template' );
		\WP_Mock::userFunction( 'get_post_type', null );
		$this->assertEquals( 'template.twig', $cc_templates->add_template( 'template.twig' ) );
	}

	/**
	 * @depends test_can_get_instance
	 */
	public function test_can_get_templates( $cc_templates ) {
		$theme = Mockery::mock( '\WP_Theme' );
		\WP_Mock::userFunction( 'wp_cache_get' );
		\WP_Mock::userFunction( 'wp_get_theme' )->andReturn( $theme );
		\WP_Mock::userFunction( 'wp_cache_set' );
		$this->assertEquals( array( 'page-alt' ), $cc_templates->get_templates( array( 'page-alt' ) ) );
	}
}
