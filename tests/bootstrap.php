<?php

// First we need to load the composer autoloader so we can use WP Mock.
require_once __DIR__ . '/../vendor/autoload.php';

// Now call the bootstrap method of WP Mock.
WP_Mock::bootstrap();

/**
 * We need clarkson-core to define the main class, as it's not autoloaded.
 */
require_once __DIR__ . '/../clarkson-core.php';

require_once __DIR__ . '/custom_test_template.php';

/**
 * Needs to be initialised for the unit test, otherwise it fails on a
 * missing class.
 */
class WP_Block_Type{}

/**
 * Test classes for custom type castings.
 */
class user_test_role extends \Clarkson_User{}; //phpcs:ignore
class custom_test_tax extends \Clarkson_Term{}; //phpcs:ignore
class test_overwritten_object_creation extends \Clarkson_Object{}; //phpcs:ignore
