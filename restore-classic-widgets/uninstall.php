<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}
global $wpdb;
$restore_classic_widgets_options = array(
    'restore_classic_widgets_pre_checkup_finished',
    'restore_classic_widgets_show_warnings',
);
foreach ( $restore_classic_widgets_options as $option_name ) {
    if ( is_multisite() ) {
        delete_site_option( $option_name );
    } else {
        delete_option( $option_name );
    }
}
if ( is_multisite() ) {
    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
    $original_blog_id = get_current_blog_id();
    foreach ( $blog_ids as $blog_id ) {
        switch_to_blog( $blog_id );
        foreach ( $restore_classic_widgets_options as $option_name ) {
            delete_option( $option_name );
        }
    }
    restore_current_blog(); // Switch back to the original blog.
}
$plugin_name = 'bill-catch-errors.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
if ( WP_Filesystem() ) {
    global $wp_filesystem;
    $mu_plugins_dir = WPMU_PLUGIN_DIR;
    $destination = $mu_plugins_dir . '/' . $plugin_name;
    if ( $wp_filesystem->exists( $destination ) ) {
        $wp_filesystem->delete( $destination );
    }
}
