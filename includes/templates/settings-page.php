<?php
/**
 * Template for the WPChill Analytics settings page.
 *
 * @var array $tabs
 * @var string $active_tab
 * @var string $page_title
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html( $page_title ); ?></h1>

    <h2 class="nav-tab-wrapper">
		<?php foreach ( $tabs as $tab_key => $tab_caption ) : ?>
            <a href="?page=wpchill-analytics&tab=<?php echo esc_attr( $tab_key ); ?>"
               class="nav-tab <?php echo $active_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
				<?php echo esc_html( $tab_caption ); ?>
            </a>
		<?php endforeach; ?>
    </h2>

    <form action="options.php" method="post">
		<?php
		settings_fields( 'wpchill_analytics_options' );
		do_settings_sections( 'wpchill_analytics_' . $active_tab ); ?>
        <input type="hidden" name="tab" value="<?php echo esc_attr( $active_tab ); ?>">

		<?php submit_button(); ?>
    </form>
</div>