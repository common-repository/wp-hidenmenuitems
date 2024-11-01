<?php
/**
 * Plugin Name:  WP-HidenMenuItems
 * Description:  Hides menu items, pages or posts for all but selected site visitors.
 * Author:       Oleg Klenitsky
 * Author URI:   https://www.adminkov.bcr.by/
 * Plugin URI:   https://wordpress.org/plugins/wp-MenuItemsHiden/
 * Contributors: adminkov, innavoronich
 * Version:      1.0.0
 * Text Domain:  hmi
 * Domain Path:  /languages/
 * Initiation:   Dedicated to brother Klenitsky Igor.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

require_once __DIR__ . '/class-structure-menu.php';

/**
 * Localization of plugin.
 */
function hmi_textdomain() {
	load_plugin_textdomain( 'hmi', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'hmi_textdomain' );

/**
 * Register javascripts, css.
 */
function hmi_js_css() {
	$_request_uri = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING );
	if ( $_request_uri ) {
		$pos = strpos( $_request_uri, 'hiden-menu-items' );
		if ( $pos ) {
			$hmi_ajax_url = admin_url( 'admin-ajax.php' );
			$hmi_nonce    = wp_create_nonce( 'hmi' );
			?>
			<script>
				var hmi_ajax_url = '<?php echo esc_html( $hmi_ajax_url ); ?>';
				var hmi_nonce    = '<?php echo esc_html( $hmi_nonce ); ?>';
			</script>
			<?php
			wp_enqueue_script( 'hmi-js', plugins_url( '/js/ajax-menu-items-hiden.js', __FILE__ ), array(), 'v.1.0.0', false );
			wp_enqueue_style( 'hmi-css', plugins_url( '/css/menu-items-hiden.css', __FILE__ ), false, 'v.1.0.0', 'all' );
		}
	}
}
add_action( 'admin_enqueue_scripts', 'hmi_js_css' );

if ( ! class_exists( 'HMI_Main' ) ) {
	/**
	 * Create an administrative control panel for the plugin. Hide items menu.
	 */
	require_once __DIR__ . '/class-menu-items-hiden.php';

	$hmi_core = new HMI_Main();
}

/**
 * Ajax.
 */
require_once __DIR__ . '/includes/ajax-menu-items-hiden.php';
add_action('wp_ajax_hmi', 'hmi_menu_items_ajax');
