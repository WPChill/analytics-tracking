<?php

namespace WPChill\Analytics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Options {
	/**
	 * Default options for the WPChill Analytics plugin.
	 *
	 * @var array
	 */
	private static $defaultOptions = [
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
		$savedOptions = get_option( 'wpchill_analytics_options', [] );

		return self::sanitizeOptions( $savedOptions );
	}

	/**
	 * Update WPChill Analytics options.
	 *
	 * @param array $newOptions An array of options to update.
	 *
	 * @return bool Whether the update was successful.
	 */
	public static function updateOptions( $newOptions ): bool {
		$sanitizedOptions = self::sanitizeOptions( $newOptions );

		return update_option( 'wpchill_analytics_options', $sanitizedOptions );
	}

	/**
	 * Sanitize and merge options with defaults.
	 *
	 * @param mixed $options The options to sanitize.
	 *
	 * @return array The sanitized options.
	 */
	private static function sanitizeOptions( $options ): array {
		// If $options is a string, try to unserialize it
		if ( is_string( $options ) ) {
			$unserialized = @unserialize( $options );
			if ( $unserialized !== false ) {
				$options = $unserialized;
			} else {
				$options = [];
			}
		}

		// Ensure $options is an array
		if ( ! is_array( $options ) ) {
			$options = [];
		}

		$mergedOptions = wp_parse_args( $options, self::$defaultOptions );

		// Ensure boolean values are actually booleans
		$booleanKeys = [
			'enabled',
			'use_host_url',
			'ignore_admins',
			'auto_track',
			'do_not_track',
			'cache',
			'track_comments'
		];
		foreach ( $booleanKeys as $key ) {
			$mergedOptions[ $key ] = (bool) $mergedOptions[ $key ];
		}

		// Sanitize string values
		$stringKeys = [ 'script_url', 'host_url', 'website_id' ];
		foreach ( $stringKeys as $key ) {
			$mergedOptions[ $key ] = sanitize_text_field( $mergedOptions[ $key ] );
		}

		// Sanitize custom events
		if ( isset( $mergedOptions['custom_events'] ) && is_array( $mergedOptions['custom_events'] ) ) {
			$sanitizedEvents = [];
			foreach ( $mergedOptions['custom_events'] as $event ) {
				if ( isset( $event['selector'] ) && isset( $event['name'] ) ) {
					$sanitizedEvents[] = [
						'selector' => sanitize_text_field( $event['selector'] ),
						'name'     => sanitize_text_field( $event['name'] )
					];
				}
			}
			$mergedOptions['custom_events'] = $sanitizedEvents;
		} else {
			$mergedOptions['custom_events'] = [];
		}

		return $mergedOptions;
	}

	/**
	 * Get a specific WPChill Analytics option.
	 *
	 * @param string $optionName The name of the option to retrieve.
	 * @param mixed $default Optional. Default value to return if the option does not exist.
	 *
	 * @return mixed The option value.
	 */
	public static function getOption( string $optionName, $default = null ) {
		$options = self::getOptions();

		return isset( $options[ $optionName ] ) ? $options[ $optionName ] : $default;
	}

	/**
	 * Delete all WPChill Analytics options.
	 *
	 * @return bool Whether the deletion was successful.
	 */
	public static function deleteOptions(): bool {
		return delete_option( 'wpchill_analytics_options' );
	}

	/**
	 * Get the default options for WPChill Analytics.
	 *
	 * @return array The default options.
	 */
	public static function getDefaultOptions(): array {
		return self::$defaultOptions;
	}
}