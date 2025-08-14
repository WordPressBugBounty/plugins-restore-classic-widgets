<?php
if (!defined("ABSPATH")) {
    exit();
} // Exit if accessed directly
if (!function_exists('restore_classic_widget_enqueue_scripts_with_nonce')) {
    function restore_classic_widget_enqueue_scripts_with_nonce()
    {
        wp_enqueue_script(
            'restore_classic_widget-loading-time-admin-js',
            plugin_dir_url(__FILE__) . 'loading-time.js',
            array('jquery'),
            RESTORE_CLASSIC_WIDGETSVERSION, // Use your plugin's version constant here for cache busting
            true
        );
        $nonce = wp_create_nonce('restore_classic_widget-loading-time-nonce');
        wp_localize_script('restore_classic_widget-loading-time-admin-js', 'restore_classic_widget_ajax_object', array('ajax_nonce' => $nonce));
        do_action('restore_classic_widget_enqueue_additional_scripts');
    }
}
add_action('wp_enqueue_scripts', 'restore_classic_widget_enqueue_scripts_with_nonce');
if (!function_exists('restore_classic_widget_register_loading_time')) {
    function restore_classic_widget_register_loading_time()
    {
        global $wpdb;
        if (!isset($_POST['nonce'])) {
            wp_send_json_error('Invalid request: Nonce not provided.');
            wp_die();
        }
        $nonce = sanitize_text_field(wp_unslash($_POST['nonce']));
        if (!wp_verify_nonce($nonce, 'restore_classic_widget-loading-time-nonce')) {
            wp_send_json_error('Invalid nonce.');
            wp_die();
        }
        if (
            isset($_POST['page_url'])
            && isset($_POST['loading_time'])
        ) {
            $page_url = sanitize_text_field(wp_unslash($_POST['page_url']));
            $loading_time = sanitize_text_field(wp_unslash($_POST['loading_time']));
            $table_name = $wpdb->prefix . 'restore_classic_widget_page_load_times';
            if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) != $table_name) {
                $charset_collate = $wpdb->get_charset_collate();
                $sql = "CREATE TABLE $table_name (
id INT PRIMARY KEY AUTO_INCREMENT,
page_url VARCHAR(255) NOT NULL,
load_time FLOAT NOT NULL,
timestamp DATETIME NOT NULL
) $charset_collate;";
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);
            }
            $data = array(
                'page_url' => $page_url,
                'load_time' => $loading_time,
                'timestamp' => current_time('mysql', 1) // Uses current WordPress time
            );
            $wpdb->insert($table_name, $data);
            wp_send_json_success('Success'); // You can send any desired success response
        } else {
            wp_send_json_error('Invalid or missing data.');
        }
        wp_die(); // End the execution of the WordPress AJAX script
    }
}
add_action('wp_ajax_restore_classic_widget_register_loading_time', 'restore_classic_widget_register_loading_time');
add_action('wp_ajax_nopriv_restore_classic_widget_register_loading_time', 'restore_classic_widget_register_loading_time');