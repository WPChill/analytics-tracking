<?php
/**
 * Autoloader for WPChill Analytics plugin.
 *
 * @package WPChill\Analytics
 */

namespace WPChill\Analytics;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Autoloader
 *
 * @since 1.0.0
 */
class Autoloader {

	/**
	 * Autoloader constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		spl_autoload_register( array( $this, 'autoload' ) );
	}

	/**
	 * Autoload WPChill Analytics classes.
	 *
	 * @param string $class_name The name of the class to autoload.
	 *
	 * @since 1.0.0
	 */
	public function autoload( $class_name ) {
		// Check if the class is in our namespace.
		if ( 0 !== strpos( $class_name, 'WPChill\\Analytics\\' ) ) {
			return;
		}

		// Remove the namespace from the class name.
		$class_name = str_replace( 'WPChill\\Analytics\\', '', $class_name );

		// Convert the class name to a file path.
		$file_path = str_replace( '\\', DIRECTORY_SEPARATOR, $class_name );
		$file      = WPCHILL_ANALYTICS_PLUGIN_DIR . 'includes' . DIRECTORY_SEPARATOR . 'class-' .strtolower($file_path) . '.php';



		// If the file exists, require it.
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}

/**
 * Initialize the autoloader.
 *
 * @since 1.0.0
 */
new Autoloader();