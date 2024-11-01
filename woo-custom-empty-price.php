<?php
/**
 * Plugin Name:     Woo Custom Empty Price
 * Plugin URI:      https://poodleplugins.com/
 * Description:     Customise what is shown on a product when it has no price set. Show a call to action, text or custom HTML.
 * Version:         2.0.0
 * Author:          Poodle Plugins
 * Author URI:      https://poodleplugins.com
 * License:         GPL-3.0+
 * License URI:     https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:     woo-custom-empty-price
 * Domain Path:     /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Load plugin translations
 */
function woo_custom_empty_price_load_textdomain() {
    load_plugin_textdomain(
        'woo-custom-empty-price',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages/'
    );
}
add_action( 'plugins_loaded', 'woo_custom_empty_price_load_textdomain' );

// Include the necessary files
require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-custom-empty-price.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-custom-empty-price-core.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-custom-empty-price-settings.php';

// Create an instance of the main plugin class
$woo_custom_empty_price = new Woo_Custom_Empty_Price();
