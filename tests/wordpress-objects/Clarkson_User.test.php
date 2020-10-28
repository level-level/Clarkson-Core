<?php

use Clarkson_Core\WordPress_Object\Clarkson_User;

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
		$object = new Clarkson_User( $user );
		$this->assertInstanceOf( Clarkson_User::class, $object );
		return $object;
	}
}
