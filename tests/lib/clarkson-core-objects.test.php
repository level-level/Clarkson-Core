<?php

use Clarkson_Core\WordPress_Object\Clarkson_Object;
use Clarkson_Core\WordPress_Object\Clarkson_Term;
use Clarkson_Core\WordPress_Object\Clarkson_User;
use Clarkson_Core\Objects;

class ClarksonCoreObjectsTest extends \WP_Mock\Tools\TestCase {
	public function setUp():void {
		\WP_Mock::setUp();
	}

	public function tearDown():void {
		\WP_Mock::tearDown();
	}

	public function test_can_get_instance() {
		\WP_Mock::userFunction( 'get_template_directory', '/tmp/wp-content/themes/theme/' );
		$cc_templates = Objects::get_instance();
		$this->assertInstanceOf( Objects::class, $cc_templates );
		return $cc_templates;
	}

	/**
	 * @depends test_can_get_instance
	 */
	public function test_can_get_objects( $cc_objects ) {
		$post     = Mockery::mock( '\WP_Post' );
		$post->ID = 1;
		\WP_Mock::userFunction( 'get_post_type', 'post' );
		\WP_Mock::userFunction( 'get_page_template_slug', '' );
		$this->assertContainsOnlyInstancesOf( Clarkson_Object::class, $cc_objects->get_objects( array( $post ) ) );
	}

	/**
	 * @depends test_can_get_instance
	 */
	public function test_can_get_object_with_class_creation_overwrite( $cc_objects ) {
		$post     = Mockery::mock( '\WP_Post' );
		$post->ID = 1;

		$creation_callback = function() use ( $post ) {
			return new \test_overwritten_object_creation( $post );
		};

		\WP_Mock::onFilter( 'clarkson_core_create_object_callback' )
		->with( false, '', 1 )
		->reply( $creation_callback );

		\WP_Mock::userFunction( 'get_post' )->andReturn( $post );
		\WP_Mock::userFunction( 'get_post_type', 'post' );
		\WP_Mock::userFunction( 'get_page_template_slug', '' );
		$this->assertInstanceOf( Clarkson_Object::class, $cc_objects->get_object( $post ) );
	}

	/**
	 * @depends test_can_get_instance
	 */
	public function test_can_get_term( $cc_objects ) {
		$term           = Mockery::mock( '\WP_Term' );
		$term->term_id  = 1;
		$term->taxonomy = 'category';
		\WP_Mock::userFunction( 'get_term_by' )->andReturn( $term );
		\WP_Mock::userFunction( 'get_term' )->andReturn( $term );
		$this->assertInstanceOf( Clarkson_Term::class, $cc_objects->get_term( $term ) );
	}

	/**
	 * @depends test_can_get_instance
	 */
	public function test_can_get_users( $cc_objects ) {
		$user        = Mockery::mock( '\WP_User' );
		$user->roles = array( 'administrator' );
		$this->assertContainsOnlyInstancesOf( Clarkson_User::class, $cc_objects->get_users( array( $user ) ) );
	}
}
