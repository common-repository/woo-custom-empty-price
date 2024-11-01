<?php
/**
 * Woo Custom Empty Price class
 *
 * The main class that initializes the plugin and its components.
 *
 * @package Woo_Custom_Empty_Price
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Woo_Custom_Empty_Price class
 */
class Woo_Custom_Empty_Price {

    /**
     * Constructor
     *
     * Sets up the necessary action hooks for initializing the plugin.
     */
    public function __construct() {
        add_action( 'plugins_loaded', array( $this, 'init' ) );
    }

    /**
     * Initialize the plugin
     *
     * Sets up the default global options, initializes the plugin components,
     * and creates instances of the necessary classes.
     */
    public function init() {
        // Set default global options
        $this->set_default_global_options();

        // Initialize core
        $core = new Woo_Custom_Empty_Price_Core();

        // Initialize settings
        $settings = new Woo_Custom_Empty_Price_Settings();
    }

    /**
     * Set default global options
     *
     * Sets the default values for the global plugin options if they don't exist.
     */
    private function set_default_global_options() {
        $default_options = array(
            'restock_date_format'       => 'days',
            'date_format'               => 'F j, Y',
            'global_backorder_message'  => '',
            'global_outofstock_message' => '',
        );

        $global_options = get_option( 'woo_custom_empty_price_settings', array() );
        $updated_options = array_merge( $default_options, $global_options );

        update_option( 'woo_custom_empty_price_settings', $updated_options );
    }
}