<?php

class Strong_Testimonials_Settings_Sanitizer {
	/**
	 * Holds the class object.
	 *
	 * @since 2.5.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Get the instance of the class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) || ! ( self::$instance instanceof Strong_Testimonials_Settings_Sanitizer ) ) {
			self::$instance = new Strong_Testimonials_Settings_Sanitizer();
		}

		return self::$instance;
	}

	public function text( $value ) {
		return sanitize_text_field( $value );
	}

	public function url( $value ) {
		return esc_url_raw( $value );
	}

	public function number( $value ) {
		return absint( $value );
	}

	public function bool( $value ) {
		return rest_sanitize_boolean( $value );
	}

	public function text_array( $value ) {
		return array_map( 'sanitize_text_field', $value );
	}

	public function number_array( $value ) {
		return array_map( 'absint', $value );
	}

	public function bool_array( $value ) {
		return array_map( 'rest_sanitize_boolean', $value );
	}

	public function url_array( $value ) {
		return array_map( 'esc_url_raw', $value );
	}

	public function kses( $value ) {
		return wp_kses_post( $value );
	}

	public function float( $value ) {
		return floatval( $value );
	}

	/**
	 * Convert a newline-separated string of IP addresses to an array.
	 * Keeps backward compatibility with code that expects restrict_ip as array.
	 */
	public function ip_list( $value ) {
		if ( empty( $value ) ) {
			return array();
		}
		$lines = preg_split( '/\r\n|\r|\n/', (string) $value );
		return array_values( array_filter( array_map( 'sanitize_text_field', $lines ) ) );
	}
}
