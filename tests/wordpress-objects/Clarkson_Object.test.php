<?php

use Clarkson_Core\WordPress_Object\Clarkson_Object;
use Clarkson_Core\WordPress_Object\Clarkson_Term;

class ClarksonObjectTest extends \WP_Mock\Tools\TestCase {
	use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

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
		$object   = new Clarkson_Object( $post );
		$this->assertInstanceOf( Clarkson_Object::class, $object );
		return $object;
	}

	/**
	 * @depends test_can_construct_an_object
	 */
	public function test_can_get_terms( $object ) {
		$term           = Mockery::mock( '\WP_Term' );
		$term->term_id  = self::TERM_ID;
		$term->taxonomy = 'category';
		\WP_Mock::userFunction( 'wp_get_post_terms' )->with( self::POST_ID, 'category', array() )->andReturn( array( $term ) );
		\WP_Mock::userFunction( 'is_wp_error', false );
		\WP_Mock::userFunction( 'get_term_by' )->with( 'id', self::TERM_ID, 'category' )->andReturn( $term );
		\WP_Mock::userFunction( 'get_term' )->with( self::TERM_ID, 'category' )->andReturn( $term );
		$this->assertContainsOnlyInstancesOf( Clarkson_Term::class, $object->get_terms( 'category' ) );
	}

	/**
	 * @depends test_can_construct_an_object
	 */
	public function test_can_add_comment( $object ) {
		$user_id = 1;
		\WP_Mock::userFunction( 'wp_insert_comment' )->with(
			array(
				'comment_post_ID' => self::POST_ID,
				'user_id'         => $user_id,
				'comment_content' => 'example',
			)
		)->once()->andReturn( 1 );
		$object->add_comment( 'example', $user_id );
	}

	/**
	 * @depends test_can_construct_an_object
	 */
	public function test_add_comment_without_text_fails( $object ) {
		$user_id = 1;
		$this->expectException( '\Exception' );
		$object->add_comment( '', $user_id );
	}

	/**
	 * @depends test_can_construct_an_object
	 */
	public function test_add_comment_insert_fails( $object ) {
		$user_id = 1;
		$this->expectException( '\Exception' );
		\WP_Mock::userFunction( 'wp_insert_comment' )->with(
			array(
				'comment_post_ID' => self::POST_ID,
				'user_id'         => $user_id,
				'comment_content' => 'fails',
			)
		)->once()->andReturn( false );
		$object->add_comment( 'fails', $user_id );
	}

	/**
	 * @depends test_can_construct_an_object
	 */
	public function test_get_excerpt( $object ) {
		\WP_Mock::userFunction( 'setup_postdata' )->with( \Mockery::type( '\WP_Post' ) )->once();
		\WP_Mock::userFunction( 'the_excerpt' )->once()->andReturnUsing(
			function() {
				echo 'test';
			}
		);
		\WP_Mock::userFunction( 'wp_reset_postdata' )->once();
		$this->assertEquals( 'test', $object->get_excerpt() );
	}
}
