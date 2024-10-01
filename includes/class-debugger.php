<?php

/**
 * Class responsible for logging debug messages with optional type prefixes in a WordPress environment.
 */

namespace WPChill\Analytics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class responsible for logging debug messages with an optional prefix.
 *
 * Debugger::logWithType('ERROR', 'Failed to save options');
 * Debugger::setPrefix('[MyPlugin] ');
 */
class Debugger {
	/**
	 * Prefix for all debug messages.
	 *
	 * @var string
	 */
	private static $prefix = '[WPChill Analytics] ';

	/**
	 * Log a debug message.
	 *
	 * @param mixed $message The message to log.
	 *
	 * @return void
	 */
	public static function log( $message ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
			if ( is_array( $message ) || is_object( $message ) ) {
				error_log( self::$prefix . print_r( $message, true ) );
			} else {
				error_log( self::$prefix . $message );
			}
		}
	}

	/**
	 * Log a debug message with a specific type prefix.
	 *
	 * Debugger::logWithType('ERROR', 'Failed to save options');
	 *
	 * @param string $type The type of log message (e.g., 'ERROR', 'WARNING').
	 * @param mixed $message The message to log.
	 *
	 * @return void
	 */
	public static function logWithType( $type, $message ) {
		$typePrefix = "[{$type}] ";
		self::log( $typePrefix . $message );
	}

	/**
	 * Set a custom prefix for debug messages.
	 *
	 *  Debugger::setPrefix('[MyPlugin] ');
	 *
	 * @param string $prefix The prefix to use.
	 *
	 * @return void
	 */
	public static function setPrefix( $prefix ) {
		self::$prefix = $prefix;
	}
}