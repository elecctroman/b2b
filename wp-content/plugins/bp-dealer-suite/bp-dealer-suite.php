<?php
/**
 * Plugin Name:       BusinessPlus Dealer Suite
 * Plugin URI:        https://example.com/businessplus-dealer-suite
 * Description:       Dealer/Wholesale management suite for WooCommerce with dynamic pricing, bonus tiers, loss protection and REST API.
 * Version:           1.0.0
 * Requires PHP:      8.1
 * Requires at least: 6.5
 * Requires Plugins:  woocommerce
 * Author:            BusinessPlus
 * Author URI:        https://example.com
 * Text Domain:       bp-dealer-suite
 * Domain Path:       /languages
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'BP_DEALER_SUITE_FILE' ) ) {
    define( 'BP_DEALER_SUITE_FILE', __FILE__ );
}

if ( ! defined( 'BP_DEALER_SUITE_PATH' ) ) {
    define( 'BP_DEALER_SUITE_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'BP_DEALER_SUITE_URL' ) ) {
    define( 'BP_DEALER_SUITE_URL', plugin_dir_url( __FILE__ ) );
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

spl_autoload_register(
    static function ( $class ) {
        $prefix   = 'BP\\DealerSuite\\';
        $base_dir = __DIR__ . '/src/';

        if ( 0 !== strpos( $class, $prefix ) ) {
            return;
        }

        $relative_class = substr( $class, strlen( $prefix ) );
        $relative_path  = str_replace( '\\', '/', $relative_class );
        $file           = $base_dir . $relative_path . '.php';

        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }
);

register_activation_hook( __FILE__, static function () {
    $bootstrap = \BP\DealerSuite\Infrastructure\Bootstrap::instance();
    $bootstrap->activate();
} );

register_deactivation_hook( __FILE__, static function () {
    $bootstrap = \BP\DealerSuite\Infrastructure\Bootstrap::instance();
    $bootstrap->deactivate();
} );

add_action( 'plugins_loaded', static function () {
    if ( ! did_action( 'woocommerce_loaded' ) ) {
        add_action( 'admin_notices', static function () {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'BusinessPlus Dealer Suite requires WooCommerce to be active.', 'bp-dealer-suite' ) . '</p></div>';
        } );
        return;
    }

    load_plugin_textdomain( 'bp-dealer-suite', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

    $bootstrap = \BP\DealerSuite\Infrastructure\Bootstrap::instance();
    $bootstrap->init();
} );
