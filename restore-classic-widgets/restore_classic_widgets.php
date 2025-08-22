<?php
/*
Plugin Name: Restore and Enable Classic Widgets No Expiration
Description: Description: Restore and enable the previous classic widgets settings screens and disables the Gutenberg block editor from managing widgets. No expiration date.
Version: 4.81
Text Domain: restore-classic-widgets
Domain Path: /language
Author: Bill Minozzi
Author URI: http://billminozzi.com
Requires at least: 6.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
if (!defined('ABSPATH')) {
    die('We\'re sorry, but you can not directly access this file.');
}
$restore_classic_widgets_debug = false;
$restore_classic_widgets_plugin_data = get_file_data(__FILE__, array('Version' => 'Version'), false);
$restore_classic_widgets_plugin_version = $restore_classic_widgets_plugin_data['Version'];
define('RESTORE_CLASSIC_WIDGETSPATH', plugin_dir_path(__file__));
define('RESTORE_CLASSIC_WIDGETSURL', plugin_dir_url(__file__));
define('RESTORE_CLASSIC_WIDGETSVERSION', $restore_classic_widgets_plugin_version);
$restore_classic_widgets_images =  RESTORE_CLASSIC_WIDGETSURL . 'assets/images/';
define('RESTORE_CLASSIC_WIDGETSIMAGES', $restore_classic_widgets_images);
$restore_classic_widgets_is_admin = restore_classic_widgets_check_wordpress_logged_in_cookie();
add_filter('gutenberg_use_widgets_block_editor', '__return_false');
add_filter('use_widgets_block_editor', '__return_false');
function restore_classic_widgets_check_wordpress_logged_in_cookie()
{
    try {
        if (!function_exists("wp_get_current_user")) {
            return false;
        }

        if (current_user_can('manage_options')) {
            return true;
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}
$restore_classic_widgets_is_admin = restore_classic_widgets_check_wordpress_logged_in_cookie();
if ($restore_classic_widgets_is_admin) {
}
function restore_classic_widgets_add_admstylesheet()
{
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-dialog');
    $wpmemory_jqueryurl = RESTORE_CLASSIC_WIDGETSURL . 'assets/css/jquery-ui.css';
    wp_register_style('bill-jquery-ui', $wpmemory_jqueryurl, array(), '1.12.1', 'all');
    wp_enqueue_style('bill-jquery-ui');
    wp_enqueue_script('jquery-migrate');
}
add_action('admin_enqueue_scripts', 'restore_classic_widgets_add_admstylesheet');
if ($restore_classic_widgets_is_admin) {
    add_action('admin_init', 'restore_classic_widgets_add_admstylesheet');
}
function restore_classic_widgets_restore_classic_widgets_more()
{
    global $restore_classic_widgets_is_admin;
    if ($restore_classic_widgets_is_admin and current_user_can("manage_options")) {
        $declared_classes = get_declared_classes();
        foreach ($declared_classes as $class_name) {
            if (strpos($class_name, "restore_classic_widgets_show_more_plugins") !== false) {
            }
        }
        require_once dirname(__FILE__) . "/includes/more-tools/class_restore_classic_widgets_more.php";
    }
}
function restore_classic_widgets_new_more_plugins()
{
    echo '<h2>More Tools</h2>';
    $plugin = new restore_classic_widgets_restore_classic_widgets_show_more_plugins();
    $plugin->restore_classic_widgets_show_plugins();
}
function restore_classic_widget_row_meta($links, $file)
{
    if (strpos($file, 'restore_classic_widgets.php') !== false) {
        if (is_multisite())
            $url = admin_url() . "plugin-install.php?s=sminozzi&tab=search&type=author";
        else
            $url = admin_url() . "admin.php?page=restore_classic_widgets_new_more_plugins";
        $new_links['Pro'] = '<a href="' . $url . '" target="_blank"><b><font color="#FF6600">Click To see more FREE plugins from same author</font></b></a>';
        $links = array_merge($links, $new_links);
    }
    return $links;
}
function restore_classic_widgets_load_chat()
{
    if (current_user_can("manage_options")) {
        require_once(RESTORE_CLASSIC_WIDGETSPATH . 'functions/function_sysinfo.php');
        if (!class_exists('restore_classic_widgets_BillChat\ChatPlugin')) {
            require_once dirname(__FILE__) . "/includes/chat/class_bill_chat.php";
        }
    }
}
add_action('init', 'restore_classic_widgets_load_chat');


function restore_classic_widgets_restore_classic_widgets_hooking_diagnose()
{
    if (current_user_can("manage_options")) {
        require_once(RESTORE_CLASSIC_WIDGETSPATH . 'functions/function_sysinfo.php');
        $declared_classes = get_declared_classes();



        /*
        foreach ($declared_classes as $class_name) {
            if (strpos($class_name, "_Diagnose") !== false) {
                return;
            }
        }
        */

        // --- CORREÇÃO PRINCIPAL: VERIFICAÇÃO PRECISA DE SUFIXO DE CLASSE (COMPATÍVEL COM PHP < 8.0) ---


        // 2. Percorre a lista de classes.
        foreach ($declared_classes as $class_name) {

            // 3. Verifica se o nome da classe TERMINA com "_Diagnose".
            $suffix = 'Bill_Diagnose';
            $does_end_with = (substr($class_name, -strlen($suffix)) === $suffix);

            if ($does_end_with) {
                return; // Uma classe de diagnóstico principal já foi carregada, não faça nada.
            }
        }


        $plugin_slug = 'restore-classic-widgets';
        $plugin_text_domain = $plugin_slug;
        $notification_url = "https://wpmemory.com/fix-low-memory-limit/";
        $notification_url2 =
            "https://wptoolsplugin.com/site-language-error-can-crash-your-site/";
        require_once dirname(__FILE__) . "/includes/diagnose/class_bill_diagnose.php";
    }
}
add_action('init', 'restore_classic_widgets_restore_classic_widgets_hooking_diagnose');


function restore_classic_widgets_restore_classic_widgets_hooking_catch_errors()
{
    global $restore_classic_widgets_plugin_slug;
    if (current_user_can("manage_options")) {
        $declared_classes = get_declared_classes();
        foreach ($declared_classes as $class_name) {
            if (strpos($class_name, "_catch_errors") !== false) {
                return;
            }
        }
        $restore_classic_widgets_plugin_slug = 'restore-classic-widgets';
        require_once dirname(__FILE__) . "/includes/catch-errors/class_bill_catch_errors.php";
    }
}
add_action('init', 'restore_classic_widgets_restore_classic_widgets_hooking_catch_errors');

/*
function restore_classic_widgets_restore_classic_widgets_install()
{
    global $restore_classic_widgets_is_admin;
    if ($restore_classic_widgets_is_admin and current_user_can("manage_options")) {
        $declared_classes = get_declared_classes();
        foreach ($declared_classes as $class_name) {
            if (strpos($class_name, "restore_classic_widgets_Class_Plugins_Install") !== false) {
                return;
            }
        }
        $plugin_slug = 'restore_classic_widgets';
        $plugin_text_domain = $plugin_slug;
        $notification_url = "https://wpmemory.com/fix-low-memory-limit/";
        $notification_url2 =
            "https://wptoolsplugin.com/site-language-error-can-crash-your-site/";
        $logo = RESTORE_CLASSIC_WIDGETSIMAGES . '/logo.png';
        $plugin_adm_url = admin_url();
        require_once dirname(__FILE__) . "/includes/install-checkup/class_restore_classic_widgets_install.php";
    }
}
*/
//
//
