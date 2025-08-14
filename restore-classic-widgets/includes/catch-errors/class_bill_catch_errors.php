<?php
namespace restore_classic_widgets_BillCatchErrors;
if (!defined("ABSPATH")) {
    die("Invalid request.");
}
if (function_exists('is_multisite') and is_multisite()) {
    return;
}
if (file_exists(WPMU_PLUGIN_DIR . '/bill-catch-errors.php')) {
    return;
}
$plugin_file_path1 = ABSPATH . 'wp-admin/includes/plugin.php';
if (file_exists($plugin_file_path1)) {
    include_once($plugin_file_path1);
}
add_action("wp_ajax_restore_classic_widgets_minozzi_js_error_catched", "restore_classic_widgets_BillCatchErrors\\restore_classic_widgets_minozzi_js_error_catched");
add_action("wp_ajax_nopriv_restore_classic_widgets_minozzi_js_error_catched", "restore_classic_widgets_BillCatchErrors\\restore_classic_widgets_minozzi_js_error_catched");
function restore_classic_widgets_minozzi_js_error_catched()
{
    if (!isset($_REQUEST) || !isset($_REQUEST["restore_classic_widgets_js_error_catched"])) {
        die("empty error");
    }
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), "bill-catch-js-errors")) {
        status_header(406, "Invalid nonce");
        die();
    }
    $restore_classic_widgets_js_error_catched = sanitize_text_field(wp_unslash($_REQUEST["restore_classic_widgets_js_error_catched"]));
    $restore_classic_widgets_js_error_catched = trim($restore_classic_widgets_js_error_catched);
    if (empty($restore_classic_widgets_js_error_catched)) {
        die("empty error");
    }
    global $wp_filesystem;
    require_once ABSPATH . 'wp-admin/includes/file.php';
    if (!WP_Filesystem()) {
        wp_die('WordPress Filesystem API could not be initialized.');
    }
    $errors = explode(" | ", $restore_classic_widgets_js_error_catched);
    $logFile = ini_get("error_log");
    if (!empty($logFile)) {
        $logFile = trim($logFile);
    }
    if (empty($logFile)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $logFile = trailingslashit(WP_CONTENT_DIR) . 'debug.log';
        } else {
            $logFile = trailingslashit(ABSPATH) . 'error_log';
        }
    }
    $dir = dirname($logFile);
    if (!$wp_filesystem->exists($dir)) {
        if (!$wp_filesystem->mkdir($dir, 0755)) {
            wp_die("Folder doesn't exist and unable to create: " .  esc_html($dir));
        }
    }
    if (!$wp_filesystem->is_writable($dir) || !$wp_filesystem->is_readable($dir)) {
        if (!$wp_filesystem->chmod($dir, 0755)) {
            wp_die("Log file directory does not have adequate permissions: " . esc_html($dir));
        }
        if (!$wp_filesystem->is_writable($dir) || !$wp_filesystem->is_readable($dir)) {
            wp_die("Log file directory does not have adequate permissions (2): " . esc_html($dir));
        }
    }
    foreach ($errors as $error) {
        $parts = explode(" - ", $error);
        if (count($parts) < 3) {
            continue;
        }
        $errorMessage = $parts[0];
        $errorURL = $parts[1];
        $errorLine = $parts[2];
        $logMessage = "Javascript " . $errorMessage . " - " . $errorURL . " - " . $errorLine;
        $formattedMessage = "[" . gmdate('Y-m-d H:i:s') . "] - " . $logMessage;
        $formattedMessage .= PHP_EOL;
        $existing_content = '';
        if ($wp_filesystem->exists($logFile)) {
            $existing_content = $wp_filesystem->get_contents($logFile);
        }
        $r = $wp_filesystem->put_contents($logFile, $existing_content . $formattedMessage, FS_CHMOD_FILE);
        if (false === $r) {
            $timestamp_string = strval(time());
            update_option('restore_classic_widgets_minozzi_error_log_status', $timestamp_string);
        }
    }
    die("OK!");
}
class restore_classic_widgets_restore_classic_widgets_catch_errors
{
    public function __construct()
    {
        add_action("wp_enqueue_scripts", [$this, "enqueue_error_catcher_script"]);
        add_action("admin_enqueue_scripts", [$this, "enqueue_error_catcher_script"]);
    }
    public function enqueue_error_catcher_script()
    {
        $script_handle = 'bill-error-catcher';
        wp_register_script(
            $script_handle,
            plugin_dir_url(__FILE__) . 'assets/js/bill-catch-errors.js', // Caminho para o arquivo JS
            [], // Dependências (nenhuma neste caso)
            '1.0.0', // Versão do seu script
            true // Carregar no rodapé (melhor para performance)
        );
        $nonce = wp_create_nonce("bill-catch-js-errors");
        $ajax_url = admin_url("admin-ajax.php");
        $data_for_js = [
            'ajaxurl' => $ajax_url,
            'nonce'   => $nonce,
        ];
        wp_localize_script($script_handle, 'restore_classic_widgets_error_data', $data_for_js);
        wp_enqueue_script($script_handle);
    }
}
new restore_classic_widgets_restore_classic_widgets_catch_errors();