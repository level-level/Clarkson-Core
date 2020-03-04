<?php

class ClarksonCoreObjectsTest extends \WP_Mock\Tools\TestCase {
	public function setUp():void {
		\WP_Mock::setUp();
	}

	public function tearDown():void {
		\WP_Mock::tearDown();
	}

	public function test_can_get_instance() {
		\WP_Mock::userFunction( 'get_template_directory', '/tmp/wp-content/themes/theme/' );
		$cc_templates = \Clarkson_Core_Objects::get_instance();
		$this->assertInstanceOf( \Clarkson_Core_Objects::class, $cc_templates );
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
		$this->assertContainsOnlyInstancesOf( \Clarkson_Object::class, $cc_objects->get_objects( array( $post ) ) );
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
		$this->assertInstanceOf( \Clarkson_Term::class, $cc_objects->get_term( $term ) );
	}

	/**
	 * @depends test_can_get_instance
	 */
	public function test_can_get_term_invalid( $cc_objects ) {
		\WP_Mock::userFunction( '_doing_it_wrong' );
		$this->assertFalse( $cc_objects->get_term( 'not_a_valid_term' ) );
	}

	/**
	 * @depends test_can_get_instance
	 */
	public function test_can_get_term_cast_to_custom_object( $cc_objects ) {
		$term           = Mockery::mock( '\WP_Term' );
		$term->term_id  = 1;
		$term->taxonomy = 'custom_test_tax';
		\WP_Mock::userFunction( 'get_term_by' )->andReturn( $term );
		\WP_Mock::userFunction( 'get_term' )->andReturn( $term );

		$cc                         = Clarkson_Core::get_instance();
		$cc->autoloader->taxonomies = array( 'custom_test_tax' );
		$this->assertInstanceOf( \custom_test_tax::class, $cc_objects->get_term( $term ) );
	}	

	/**
	 * @depends test_can_get_instance
	 */
	public function test_can_get_users( $cc_objects ) {
		$user        = Mockery::mock( '\WP_User' );
		$user->roles = array( 'administrator' );
		$this->assertContainsOnlyInstancesOf( \Clarkson_User::class, $cc_objects->get_users( array( $user ) ) );
	}

	/**
	 * @depends test_can_get_instance
	 */
	public function test_can_get_users_with_id_fallback( $cc_objects ) {
		$user        = Mockery::mock( '\WP_User' );
		$user->roles = array( 'administrator' );
		\WP_Mock::userFunction( '_doing_it_wrong' );
		\WP_Mock::userFunction( 'get_userdata' )->with( 1 )->andReturn( $user );
		$this->assertContainsOnlyInstancesOf( \Clarkson_User::class, $cc_objects->get_users( array( 1 ) ) );
	}

	/**
	 * @depends test_can_get_instance
	 */
	public function test_throw_get_users_with_invalid_user_id( $cc_objects ) {
		$this->expectException( '\Exception' );
		\WP_Mock::userFunction( '_doing_it_wrong' );
		\WP_Mock::userFunction( 'get_userdata' )->with( -1 )->andReturn( false );
		$this->assertContainsOnlyInstancesOf( \Clarkson_User::class, $cc_objects->get_users( array( -1 ) ) );
	}

	/**
	 * @depends test_can_get_instance
	 */
	public function test_can_get_user_with_id_fallback( $cc_objects ) {
		$user        = Mockery::mock( '\WP_User' );
		$user->roles = array( 'administrator' );
		\WP_Mock::userFunction( '_doing_it_wrong' );
		\WP_Mock::userFunction( 'get_userdata' )->with( 1 )->andReturn( $user );
		$this->assertInstanceOf( \Clarkson_User::class, $cc_objects->get_user( 1 ) );
	}

	/**
	 * @depends test_can_get_instance
	 */
	public function test_throw_get_user_with_invalid( $cc_objects ) {
		$this->expectException( '\Exception' );
		\WP_Mock::userFunction( '_doing_it_wrong' );
		\WP_Mock::userFunction( 'get_userdata' )->with( -1 )->andReturn( false );
		$this->assertInstanceOf( \Clarkson_User::class, $cc_objects->get_user( -1 ) );
	}

	/**
	 * @depends test_can_get_instance
	 */
	public function test_throw_get_user_with_empty( $cc_objects ) {
		$this->expectException( '\Exception' );
		\WP_Mock::userFunction( '_doing_it_wrong' );
		$this->assertInstanceOf( \Clarkson_User::class, $cc_objects->get_user( '' ) );
	}

	/**
	 * @depends test_can_get_instance
	 */
	public function test_casts_user_to_custom_object( $cc_objects ) {
		$user        = Mockery::mock( '\WP_User' );
		$user->roles = array( 'test_role' );

		$cc                         = Clarkson_Core::get_instance();
		$cc->autoloader->user_types = array( 'user_test_role' );
		$this->assertInstanceOf( \user_test_role::class, $cc_objects->get_user( $user ) );
	}
}
