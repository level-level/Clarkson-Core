<?php

namespace Clarkson_Core;

class ClarksonWPException extends \Exception {
	protected \WP_Error $wp_error;

	/**
	 * Construct an \Exception based on the \WP_Error class
	 */
	public function __construct( \WP_Error $wp_error, int $code = 0, \Throwable $previous = null ) {
		parent::__construct( $wp_error->get_error_message(), $code, $previous );
		$this->wp_error = $wp_error;
	}

	/**
	 * Returns the original \WP_Error object that was returned by WordPress.
	 */
	public function getWPError(): \WP_Error {
		return $this->wp_error;
	}
}
