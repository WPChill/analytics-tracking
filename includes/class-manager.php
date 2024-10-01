<?php

namespace WPChill\Analytics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Manager {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		$options = Options::getOptions();
		if ( $options['enabled'] && ! empty( $options['script_url'] ) && ! empty( $options['website_id'] ) ) {
			add_action( 'wp_footer', array( $this, 'render_script' ) );
			if ( isset( $options['track_comments'] ) && $options['track_comments'] ) {
				add_filter( 'comment_form_submit_button', array( $this, 'filter_comment_form_submit_button' ), 10, 2 );
			}
		}
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
		register_deactivation_hook( WPCHILL_ANALYTICS_PLUGIN_BASENAME, array( $this, 'deactivate' ) );
	}

	/**
	 * Deactivation callback.
	 */
	public static function deactivate() {
		Options::deleteOptions();
	}

	/**
	 * Filter comment submit button to add data attribute.
	 *
	 * @param string $submit_button The submit button.
	 * @param array $args The arguments.
	 *
	 * @return string
	 */
	public function filter_comment_form_submit_button( $submit_button, $args ) {
		return str_replace( '<button', '<button data-wpchill-event="comment"', $submit_button );
	}

	/**
	 * Render the analytics script.
	 */
	public function render_script() {
		$options = Options::getOptions();
		if ( $options['ignore_admins'] && current_user_can( 'manage_options' ) ) {
			return;
		}

		$script_attributes = array(
			'src'             => esc_url( $options['script_url'] ),
			'data-website-id' => esc_attr( $options['website_id'] ),
			'async'           => true,
			'defer'           => true
		);

		if ( isset( $options['do_not_track'] ) && $options['do_not_track'] ) {
			$script_attributes['data-do-not-track'] = 'true';
		}

		if ( isset( $options['auto_track'] ) && ! $options['auto_track'] ) {
			$script_attributes['data-auto-track'] = 'false';
		}

		if ( isset( $options['cache'] ) && $options['cache'] ) {
			$script_attributes['data-cache'] = 'true';
		}

		if ( ! empty( $options['host_url'] ) && isset( $options['use_host_url'] ) && $options['use_host_url'] ) {
			$script_attributes['data-host-url'] = esc_url( $options['host_url'] );
		}

		echo "<!-- WPChill Analytics -->\n";
		echo "<script";
		foreach ( $script_attributes as $attr => $value ) {
			if ( is_bool( $value ) ) {
				echo " $attr";
			} else {
				echo " $attr=\"$value\"";
			}
		}
		echo "></script>\n";
		echo "<!-- /WPChill Analytics -->\n";
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook The current admin page.
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( 'settings_page_wpchill-analytics' !== $hook ) {
			return;
		}

		wp_enqueue_script(
			'wpchill-analytics-admin',
			WPCHILL_ANALYTICS_PLUGIN_URL . 'assets/js/admin-custom-events.js',
			array(),
			WPCHILL_ANALYTICS_VERSION,
			true
		);
	}

	/**
	 * Enqueue frontend scripts.
	 */
	public function enqueue_frontend_scripts() {
		$options = Options::getOptions();

		if ( ! $options['enabled'] || empty( $options['custom_events'] ) ) {
			return;
		}

		wp_enqueue_script(
			'wpchill-analytics-frontend',
			WPCHILL_ANALYTICS_PLUGIN_URL . 'assets/js/frontend-custom-events.js',
			array(),
			WPCHILL_ANALYTICS_VERSION,
			true
		);

		wp_localize_script(
			'wpchill-analytics-frontend',
			'wpchillAnalyticsEvents',
			$options['custom_events']
		);
	}
}