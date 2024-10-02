<?php

namespace WPChill\Analytics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Options {
	/**
	 * Option name in the WordPress options table.
	 *
	 * @var string
	 */
	private static $option_name = 'wpchill_analytics_options';

	/**
	 * Default options for the WPChill Analytics plugin.
	 *
	 * @var array
	 */
	private static $default_options = [
		'enabled'        => false,
		'script_url'     => '',
		'host_url'       => '',
		'website_id'     => '',
		'use_host_url'   => false,
		'ignore_admins'  => true,
		'auto_track'     => true,
		'do_not_track'   => true,
		'cache'          => false,
		'track_comments' => false,
		'custom_events'  => [],
	];

	/**
	 * Get the WPChill Analytics options, merging saved options with defaults.
	 *
	 * @return array The complete set of WPChill Analytics options.
	 */
	public static function getOptions(): array {
		$saved_options = get_option( self::$option_name, self::$default_options );

		return wp_parse_args( $saved_options, self::$default_options );
	}

	/**
	 * Update WPChill Analytics options.
	 *
	 * @param array $new_options An array of options to update.
	 *
	 * @return bool Whether the update was successful.
	 */
	public static function updateOptions( $new_options ): bool {
		$existing_options  = self::getOptions();
		$options_to_save   = wp_parse_args( $new_options, $existing_options );
		$sanitized_options = self::sanitizeOptions( $options_to_save );

		return update_option( self::$option_name, $sanitized_options, true ); // true for autoload
	}

	/**
	 * Sanitize and validate options.
	 *
	 * @param array $options The options to sanitize.
	 *
	 * @return array The sanitized options.
	 */
	public static function sanitizeOptions( $options ): array {
		$sanitized = [];

		$checkbox_fields = [
			'enabled',
			'use_host_url',
			'ignore_admins',
			'auto_track',
			'do_not_track',
			'cache',
			'track_comments'
		];
		$url_fields      = [ 'script_url', 'host_url' ];
		$text_fields     = [ 'website_id' ];

		foreach ( $checkbox_fields as $field ) {
			$sanitized[ $field ] = isset( $options[ $field ] ) ? (bool) $options[ $field ] : false;
		}

		foreach ( $url_fields as $field ) {
			$sanitized[ $field ] = isset( $options[ $field ] ) ? esc_url_raw( $options[ $field ] ) : '';
		}

		foreach ( $text_fields as $field ) {
			$sanitized[ $field ] = isset( $options[ $field ] ) ? sanitize_text_field( $options[ $field ] ) : '';
		}

		if ( isset( $options['custom_events'] ) && is_array( $options['custom_events'] ) ) {
			$sanitized['custom_events'] = [];
			foreach ( $options['custom_events'] as $event ) {
				if ( isset( $event['selector'] ) && isset( $event['name'] ) ) {
					$sanitized['custom_events'][] = [
						'selector' => sanitize_text_field( $event['selector'] ),
						'name'     => sanitize_text_field( $event['name'] )
					];
				}
			}
		} else {
			$sanitized['custom_events'] = [];
		}

		return $sanitized;
	}

	/**
	 * Get a specific WPChill Analytics option.
	 *
	 * @param string $option_name The name of the option to retrieve.
	 * @param mixed $default Optional. Default value to return if the option does not exist.
	 *
	 * @return mixed The option value.
	 */
	public static function getOption( string $option_name, $default = null ) {
		$options = self::getOptions();

		return isset( $options[ $option_name ] ) ? $options[ $option_name ] : $default;
	}

	/**
	 * Delete all WPChill Analytics options.
	 *
	 * @return bool Whether the deletion was successful.
	 */
	public static function deleteOptions(): bool {
		return delete_option( self::$option_name );
	}

	/**
	 * Get the default options for WPChill Analytics.
	 *
	 * @return array The default options.
	 */
	public static function getDefaultOptions(): array {
		return self::$default_options;
	}
}