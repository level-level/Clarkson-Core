<?php

// First we need to load the composer autoloader so we can use WP Mock
require_once __DIR__ . '/../vendor/autoload.php';

// Now call the bootstrap method of WP Mock
WP_Mock::bootstrap();

/**
 * Needs to be initialised for the unit test, otherwise it fails on a
 * missing class.
 */
class WP_Block_Type{}
