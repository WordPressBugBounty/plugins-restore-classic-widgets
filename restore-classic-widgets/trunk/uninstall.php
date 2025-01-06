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
?>
