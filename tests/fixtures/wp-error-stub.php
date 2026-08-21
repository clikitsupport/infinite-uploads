<?php
/**
 * Minimal WP_Error stand-in for unit tests.
 *
 * Global namespace on purpose — source files type-check against the core
 * \WP_Error. Mirrors only the subset of the core API the code under test
 * and its assertions use.
 *
 * @package ClikIT\InfiniteUploads\Tests
 */

declare( strict_types=1 );

if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		public $errors     = [];
		public $error_data = [];

		public function __construct( $code = '', $message = '', $data = '' ) {
			if ( '' === $code ) {
				return;
			}
			$this->errors[ $code ][] = $message;
			if ( '' !== $data ) {
				$this->error_data[ $code ] = $data;
			}
		}

		public function get_error_code() {
			$codes = array_keys( $this->errors );

			return $codes ? $codes[0] : '';
		}

		public function get_error_message( $code = '' ) {
			if ( '' === $code ) {
				$code = $this->get_error_code();
			}

			return isset( $this->errors[ $code ] ) ? $this->errors[ $code ][0] : '';
		}
	}
}
