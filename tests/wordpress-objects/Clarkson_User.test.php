<?php

class ClarksonUserTest extends \WP_Mock\Tools\TestCase {
	use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

	public function setUp():void {
		\WP_Mock::setUp();
	}

	public function tearDown():void {
		\WP_Mock::tearDown();
	}

	public function test_can_construct_a_user() {
		$user   = Mockery::mock( '\WP_User' );
		$object = new \Clarkson_User( $user );
		$this->assertInstanceOf( \Clarkson_User::class, $object );
		return $object;
	}

	public function test_can_construct_a_user_with_fallback() {
		$user        = Mockery::mock( '\WP_User' );
		$user->roles = array( 'administrator' );
		\WP_Mock::userFunction( '_doing_it_wrong' );
		\WP_Mock::userFunction( 'get_userdata' )->with( 1 )->andReturn( $user );
		$object = new \Clarkson_User( 1 );
		$this->assertInstanceOf( \Clarkson_User::class, $object );
	}

	public function test_throw_construct_user_with_invalid_id() {
		\WP_Mock::userFunction( '_doing_it_wrong' );
		\WP_Mock::userFunction( 'get_userdata' )->with( -1 )->andReturn( false );
		$this->expectException( '\Exception' );
		new \Clarkson_User( -1 );
	}

	public function test_throw_construct_user_with_empty() {
		$this->expectException( '\Exception' );
		new \Clarkson_User( '' );
	}
}
