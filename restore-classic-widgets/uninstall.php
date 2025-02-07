<?php
/**
 * @author William Sergio Minossi
 * @copyright 2016 - 2024
 */
// If uninstall is not called from WordPress, exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}
$restore_classic_widgets_options = array(
    'bill_pre_checkup_finished',
    'bill_show_warnings'
);
foreach ($restore_classic_widgets_options as $option_name) {
    if (is_multisite()) {
        // Apaga a opção no site atual em uma instalação multisite
        delete_site_option($option_name);
    } else {
        // Apaga a opção no site único
        delete_option($option_name);
    }
}

$plugin_name = 'bill-catch-errors.php'; // Name of the plugin file to be removed

// Retrieve all must-use plugins
$wp_mu_plugins = get_mu_plugins();

// MU-Plugins directory
$mu_plugins_dir = WPMU_PLUGIN_DIR;

if (isset($wp_mu_plugins[$plugin_name])) {
    // Get the plugin's destination path
    $destination = $mu_plugins_dir . '/' . $plugin_name;

    // Attempt to remove the plugin
    if (!unlink($destination)) {
        // Log the error if the file could not be deleted
        error_log("Error removing the plugin file from the MU-Plugins directory: $destination");
    } else {
        // Optionally, log success if the plugin is removed successfully
        // error_log("Successfully removed the plugin file: $destination");
    }
}
?>
