<?php

namespace WPChill\Analytics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {
	private $tabs;
	private $active_tab;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
		$this->init_tabs();
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
	}

	/**
	 * Initialize tabs.
	 */
	private function init_tabs() {
		$this->tabs       = array(
			'general'  => __( 'General', 'wpchill-analytics' ),
			'advanced' => __( 'Advanced', 'wpchill-analytics' ),
			'tracking' => __( 'Tracking', 'wpchill-analytics' ),
		);
		$this->active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';
	}

	/**
	 * Add settings page to the menu.
	 */
	public function add_settings_page() {
		add_menu_page(
			__( 'WPChill Analytics Settings', 'wpchill-analytics' ),
			__( 'WPChill Analytics', 'wpchill-analytics' ),
			'manage_options',
			'wpchill-analytics',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings.
	 */
	public function register_settings() {
		register_setting( 'wpchill_analytics_options', 'wpchill_analytics_options', array(
			$this,
			'validate_options'
		) );

		$settings = array(
			'general'  => array(
				'enabled'    => __( 'Enable Analytics', 'wpchill-analytics' ),
				'script_url' => __( 'Script URL', 'wpchill-analytics' ),
				'website_id' => __( 'Website ID', 'wpchill-analytics' ),
			),
			'advanced' => array(
				'host_url'     => __( 'Host URL', 'wpchill-analytics' ),
				'use_host_url' => __( 'Use Host URL', 'wpchill-analytics' ),
				'cache'        => __( 'Enable Caching', 'wpchill-analytics' ),
			),
			'tracking' => array(
				'ignore_admins'  => __( 'Ignore Admins', 'wpchill-analytics' ),
				'auto_track'     => __( 'Auto Track', 'wpchill-analytics' ),
				'track_comments' => __( 'Track Comments', 'wpchill-analytics' ),
				'do_not_track'   => __( 'Respect Do Not Track', 'wpchill-analytics' ),
				'custom_events'  => __( 'Custom Events', 'wpchill-analytics' ),
			),
		);

		foreach ( $settings as $tab => $fields ) {
			add_settings_section(
				"wpchill_analytics_{$tab}",
				$this->tabs[ $tab ],
				null,
				"wpchill_analytics_{$tab}"
			);

			foreach ( $fields as $field_id => $field_title ) {
				add_settings_field(
					$field_id,
					$field_title,
					array( $this, 'render_field' ),
					"wpchill_analytics_{$tab}",
					"wpchill_analytics_{$tab}",
					array( 'field' => $field_id )
				);
			}
		}
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$options = Options::getCheckboxFields();

		$template_file = WPCHILL_ANALYTICS_PLUGIN_DIR . '/includes/templates/settings-page.php';

		if ( file_exists( $template_file ) ) {
			$tabs       = $this->tabs;
			$active_tab = $this->active_tab;
			$page_title = get_admin_page_title();

			include $template_file;
		} else {
			wp_die( sprintf(
			/* translators: %s: template file path */
				__( 'Error: Unable to locate the settings page template at %s.', 'wpchill-analytics' ),
				'<code>' . esc_html( $template_file ) . '</code>'
			) );
		}
	}

	/**
	 * Render individual setting fields.
	 *
	 * @param array $args Arguments passed to the method.
	 */
	public function render_field( $args ) {
		$options = Options::getOptions();
		$field   = $args['field'];
		$value   = isset( $options[ $field ] ) ? $options[ $field ] : '';

		switch ( $field ) {
			case 'enabled':
			case 'use_host_url':
			case 'ignore_admins':
			case 'auto_track':
			case 'track_comments':
			case 'do_not_track':
			case 'cache':
				echo '<label for="wpchill_analytics_' . esc_attr( $field ) . '">';
				echo '<input type="checkbox" id="wpchill_analytics_' . esc_attr( $field ) . '" name="wpchill_analytics_options[' . esc_attr( $field ) . ']" value="1" ' . checked( 1, $value, false ) . '/>';
				echo ' ' . $this->get_field_description( $field );
				echo '</label>';
				break;
			case 'script_url':
			case 'host_url':
				echo '<input type="url" id="wpchill_analytics_' . esc_attr( $field ) . '" name="wpchill_analytics_options[' . esc_attr( $field ) . ']" value="' . esc_url( $value ) . '" class="regular-text"/>';
				echo '<p class="description">' . $this->get_field_description( $field ) . '</p>';
				break;
			case 'custom_events':
				$this->render_custom_events_field();
				break;
			default:
				echo '<input type="text" id="wpchill_analytics_' . esc_attr( $field ) . '" name="wpchill_analytics_options[' . esc_attr( $field ) . ']" value="' . esc_attr( $value ) . '" class="regular-text"/>';
				echo '<p class="description">' . $this->get_field_description( $field ) . '</p>';
				break;
		}
	}

	/**
	 * Render custom events field.
	 */
	public function render_custom_events_field() {
		$options       = Options::getOptions();
		$custom_events = isset( $options['custom_events'] ) ? $options['custom_events'] : array();

		?>
        <div id="wpchill-analytics-custom-events">
			<?php
			if ( ! empty( $custom_events ) ) {
				foreach ( $custom_events as $index => $event ) {
					$this->render_custom_event_row( $index, $event );
				}
			}
			?>
        </div>
        <button type="button" class="button"
                id="add-custom-event"><?php _e( 'Add Custom Event', 'wpchill-analytics' ); ?></button>
        <script type="text/template" id="custom-event-template">
			<?php $this->render_custom_event_row( '{{index}}', array( 'selector' => '', 'event' => '' ) ); ?>
        </script>
        <p class="description"><?php _e( 'Add custom events to track specific user interactions.', 'wpchill-analytics' ); ?></p>
		<?php
	}

	/**
	 * Render a single custom event row.
	 *
	 * @param string $index
	 * @param array $event
	 */
	private function render_custom_event_row( $index, $event ) {
		$selector = isset( $event['selector'] ) ? $event['selector'] : '';
		$name     = isset( $event['name'] ) ? $event['name'] : ( isset( $event['event'] ) ? $event['event'] : '' );
		?>
        <div class="custom-event-row">
            <input type="text"
                   name="wpchill_analytics_options[custom_events][<?php echo esc_attr( $index ); ?>][selector]"
                   value="<?php echo esc_attr( $selector ); ?>"
                   placeholder="<?php esc_attr_e( 'CSS Selector or ID', 'wpchill-analytics' ); ?>"/>
            <input type="text" name="wpchill_analytics_options[custom_events][<?php echo esc_attr( $index ); ?>][name]"
                   value="<?php echo esc_attr( $name ); ?>"
                   placeholder="<?php esc_attr_e( 'Event Name', 'wpchill-analytics' ); ?>"/>
            <button type="button"
                    class="button remove-custom-event"><?php esc_html_e( 'Remove', 'wpchill-analytics' ); ?></button>
        </div>
		<?php
	}

	/**
	 * Get the description for a field.
	 *
	 * @param string $field The field ID.
	 *
	 * @return string The field description.
	 */
	private function get_field_description( $field ) {
		$descriptions = array(
			'enabled'        => __( 'Enable WPChill Analytics on your site.', 'wpchill-analytics' ),
			'script_url'     => __( 'Enter the URL of the analytics script.', 'wpchill-analytics' ),
			'website_id'     => __( 'Enter your unique website ID provided by the analytics service.', 'wpchill-analytics' ),
			'host_url'       => __( 'Enter the host URL if different from the script URL.', 'wpchill-analytics' ),
			'use_host_url'   => __( 'Use the host URL for data collection instead of the script URL.', 'wpchill-analytics' ),
			'ignore_admins'  => __( 'Do not track visits from admin users.', 'wpchill-analytics' ),
			'auto_track'     => __( 'Automatically track page views.', 'wpchill-analytics' ),
			'track_comments' => __( 'Track comment submissions.', 'wpchill-analytics' ),
			'do_not_track'   => __( 'Respect the Do Not Track browser setting.', 'wpchill-analytics' ),
			'cache'          => __( 'Enable caching for better performance.', 'wpchill-analytics' ),
			'custom_events'  => __( 'Configure custom events to track specific user interactions.', 'wpchill-analytics' ),
		);

		return isset( $descriptions[ $field ] ) ? $descriptions[ $field ] : '';
	}

	/**
	 * Validate and sanitize incoming options.
	 *
	 * @param array $input Array of input options to validate.
	 *
	 * @return array Sanitized array of options.
	 */
	public function validate_options( $input ) {
		Debugger::log( 'Validating options' );
		Debugger::log( 'Input received: ' . print_r( $input, true ) );

		$existing_options = Options::getOptions();
		$checkbox_fields  = Options::getCheckboxFields();

		// Preserve existing checkbox values
		foreach ( $checkbox_fields as $field ) {
			if ( ! isset( $input[ $field ] ) ) {
				// If the field is not in the input, preserve its existing value
				$input[ $field ] = isset( $existing_options[ $field ] ) ? $existing_options[ $field ] : false;
			} else {
				// If it is in the input, ensure it's boolean
				$input[ $field ] = (bool) $input[ $field ];
			}
		}

		// Merge input with existing options, prioritizing input values
		$options_to_save = wp_parse_args( $input, $existing_options );

		// Sanitize options using the Options class method
		$sanitized_options = Options::sanitizeOptions( $options_to_save );

		Debugger::log( 'Sanitized options: ' . print_r( $sanitized_options, true ) );

		return $sanitized_options;
	}
}