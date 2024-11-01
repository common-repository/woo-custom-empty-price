<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * This file is responsible for cleaning up any data or options that were created by the plugin.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete the global options
delete_option( 'woo_custom_empty_price_global_options' );

// Delete the user meta data
$meta_type  = 'user';
$user_id    = 0; // This will be ignored, since we are deleting for all users.
$meta_keys  = [ 'use_custom_author_url', 'custom_author_url' ];
$meta_value = ''; // Also ignored. The meta will be deleted regardless of value.
$delete_all = true;

foreach ( $meta_keys as $meta_key ) {
    delete_metadata( $meta_type, $user_id, $meta_key, $meta_value, $delete_all );
}