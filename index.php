<?php
/*
Plugin Name: WPChill - Analytics plugin
Plugin URI: https://github.com/wpchill/wpchill-analytics
Description: This plugin loads the Analytics tracking code as well as manage the events being tracked.
Version: 1.0.0
Author: Cristian Raiber
Author URI: https://wpchill.cm
Text Domain: wpchill-analytics
Domain Path: /languages
*/

namespace WPChill\Analytics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPCHILL_ANALYTICS_VERSION', '1.0.0' );
define( 'WPCHILL_ANALYTICS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPCHILL_ANALYTICS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPCHILL_ANALYTICS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Require the autoloader.
require_once WPCHILL_ANALYTICS_PLUGIN_DIR . 'includes/autoloader.php';

/**
 * Main plugin class.
 *
 * @since 1.0.0
 */
class WPChillAnalytics {

	/**
	 * Plugin instance.
	 *
	 * @var WPChillAnalytics
	 */
	private static $instance = null;

	/**
	 * Get plugin instance.
	 *
	 * @return WPChillAnalytics
	 * @since 1.0.0
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize the plugin.
	 *
	 * @since 1.0.0
	 */
	private function init() {
		// Initialize plugin components.
		new Options();
		new Settings();
		new Manager();
	}

}

// Initialize the plugin.
WPChillAnalytics::get_instance();