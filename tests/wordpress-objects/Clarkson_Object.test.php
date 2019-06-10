<?php

class ClarksonObjectTest extends \WP_Mock\Tools\TestCase {
	const POST_ID = 42;
	const TERM_ID = 103;

	public function setUp():void {
		\WP_Mock::setUp();
	}

	public function tearDown():void {
		\WP_Mock::tearDown();
	}

	public function test_can_construct_an_object() {
		$post     = Mockery::mock( '\WP_Post' );
		$post->ID = self::POST_ID;
		$object   = new \Clarkson_Object( $post );
		$this->assertInstanceOf( \Clarkson_Object::class, $object );
		return $object;
	}

	public function test_can_construct_an_object_with_id() {
		$this->expectException( '\PHPUnit\Framework\Error\Deprecated' );
		$object = new \Clarkson_Object( self::POST_ID );
	}

	/**
	 * @depends test_can_construct_an_object
	 */
	public function test_can_get_terms( $object ) {
		$term          = Mockery::mock( '\WP_Term' );
		$term->term_id = self::TERM_ID;
		\WP_Mock::userFunction( 'wp_get_post_terms' )->with( self::POST_ID, 'category', array() )->andReturn( array( $term ) );
		\WP_Mock::userFunction( 'is_wp_error', false );
		\WP_Mock::userFunction( 'get_term_by' )->with( 'id', self::TERM_ID, 'category' )->andReturn( $term );
		\WP_Mock::userFunction( 'get_term' )->with( self::TERM_ID, 'category' )->andReturn( $term );
		$this->assertContainsOnlyInstancesOf( \Clarkson_Term::class, $object->get_terms( 'category' ) );
	}
}
