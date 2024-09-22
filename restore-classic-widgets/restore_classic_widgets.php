<?php
/*
Plugin Name: Restore Classic Widgets
Description: Description: Restore and enable the previous classic widgets settings screens and disables the Gutenberg block editor from managing widgets. No expiration date.
Version: 4.31
Text Domain: restore-classic-widgets
Domain Path: /language
Author: Bill Minozzi
Author URI: http://billminozzi.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
$bill_debug = false;
//$bill_debug = true;
// debug2();
//

// Make sure the file is not directly accessible.
if (!defined('ABSPATH')) {
    die('We\'re sorry, but you can not directly access this file.');
}
$restoreclassic_plugin_data = get_file_data(__FILE__, array('Version' => 'Version'), false);
$restoreclassic_plugin_version = $restoreclassic_plugin_data['Version'];
define('RESTORECLASSICPATH', plugin_dir_path(__file__));
define('RESTORECLASSICURL', plugin_dir_url(__file__));
define('RESTORECLASSICVERSION', $restoreclassic_plugin_version);
$restore_classic_widgets_images =  RESTORECLASSICURL . 'assets/images/';
define('RESTORECLASSICIMAGES', $restore_classic_widgets_images);

$restore_classic_widgets_is_admin = restore_classic_widgets_check_wordpress_logged_in_cookie();


add_filter('gutenberg_use_widgets_block_editor', '__return_false');
add_filter('use_widgets_block_editor', '__return_false');

// -----------------------------


function restore_classic_widgets_check_wordpress_logged_in_cookie()
{
    // Percorre todos os cookies definidos
    foreach ($_COOKIE as $key => $value) {
        // Verifica se algum cookie começa com 'wordpress_logged_in_'
        if (strpos($key, 'wordpress_logged_in_') === 0) {
            // Cookie encontrado
            return true;
        }
    }
    // Cookie não encontrado
    return false;
}

function restore_classic_widgets_bill_more()
{
    global $restore_classic_widgets_is_admin;
    //if (function_exists('is_admin') && function_exists('current_user_can')) {
    if ($restore_classic_widgets_is_admin and current_user_can("manage_options")) {
        $declared_classes = get_declared_classes();
        foreach ($declared_classes as $class_name) {
            if (strpos($class_name, "Bill_show_more_plugins") !== false) {
                //return;
            }
        }
        require_once dirname(__FILE__) . "/includes/more-tools/class_bill_more.php";
    }
    //}
}
add_action("init", "restore_classic_widgets_bill_more", 5);



// Function to display the content of Tab 3 (More Tools)
function restore_classic_widgets_new_more_plugins()
{
    echo '<h2>More Tools</h2>';
    //$plugin = new \restore_classic_widgets_BillMore\Bill_show_more_plugins();
    $plugin = new restore_classic_widgets_Bill_show_more_plugins();
    $plugin->bill_show_plugins();
}

add_action('admin_menu', 'restore_classic_widget_init', 10);



function restore_classic_widget_init()
{
    add_management_page(
        'More Useful Tools',
        '<font color="#FF6600">More Useful Tools</font>', // string $menu_title
        'manage_options',
        'restore_classic_widgets_new_more_plugins', // slug
        'restore_classic_widgets_new_more_plugins',
        1
    );
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
add_filter('plugin_row_meta', 'restore_classic_widget_row_meta', 10, 2);



// -------------------------------------


function restore_classic_widgets_bill_hooking_diagnose()
{
    global $restore_classic_widgets_is_admin;
    // if (function_exists('is_admin') && function_exists('current_user_can')) {
    if ($restore_classic_widgets_is_admin and current_user_can("manage_options")) {
        $declared_classes = get_declared_classes();
        foreach ($declared_classes as $class_name) {
            if (strpos($class_name, "Bill_Diagnose") !== false) {
                return;
            }
        }
        $plugin_slug = 'restore-classic-widgets';
        $plugin_text_domain = $plugin_slug;
        $notification_url = "https://wpmemory.com/fix-low-memory-limit/";
        $notification_url2 =
            "https://wptoolsplugin.com/site-language-error-can-crash-your-site/";
        require_once dirname(__FILE__) . "/includes/diagnose/class_bill_diagnose.php";
    }
    //} 
}
add_action("init", "restore_classic_widgets_bill_hooking_diagnose", 10);
//
//



function restore_classic_widgets_bill_hooking_catch_errors()
{
    global $restore_classic_widgets_plugin_slug;
    global $restore_classic_widgets_is_admin;

    $declared_classes = get_declared_classes();
    foreach ($declared_classes as $class_name) {
        if (strpos($class_name, "bill_catch_errors") !== false) {
            return;
        }
    }
    $restore_classic_widgets_plugin_slug = 'restore-classic-widgets';
    require_once dirname(__FILE__) . "/includes/catch-errors/class_bill_catch_errors.php";
}
add_action("init", "restore_classic_widgets_bill_hooking_catch_errors", 15);





// ------------------------

function restore_classic_widgets_load_feedback()
{
    global $restore_classic_widgets_is_admin;
    //if (function_exists('is_admin') && function_exists('current_user_can')) {
    if ($restore_classic_widgets_is_admin and current_user_can("manage_options")) {
        // ob_start();
        //
        require_once dirname(__FILE__) . "/includes/feedback-last/feedback-last.php";
        // ob_end_clean();
        //
    }
    //}
    //
}
add_action('wp_loaded', 'restore_classic_widgets_load_feedback', 10);


// ------------------------

function restore_classic_widgets_bill_install()
{
    global $restore_classic_widgets_is_admin;
    if ($restore_classic_widgets_is_admin and current_user_can("manage_options")) {
        $declared_classes = get_declared_classes();
        foreach ($declared_classes as $class_name) {
            if (strpos($class_name, "Bill_Class_Plugins_Install") !== false) {
                return;
            }
        }
        if (!function_exists('bill_install_ajaxurl')) {
            function bill_install_ajaxurl()
            {
                echo '<script type="text/javascript">
					var ajaxurl = "' .
                    esc_attr(admin_url("admin-ajax.php")) .
                    '";
					</script>';
            }
        }
        // ob_start();
        $plugin_slug = 'restore_classic_widgets';
        $plugin_text_domain = $plugin_slug;
        $notification_url = "https://wpmemory.com/fix-low-memory-limit/";
        $notification_url2 =
            "https://wptoolsplugin.com/site-language-error-can-crash-your-site/";
        $logo = RESTORECLASSICIMAGES . '/logo.png';
        //$plugin_adm_url = admin_url('tools.php?page=stopbadbots_new_more_plugins');
        $plugin_adm_url = admin_url();
        require_once dirname(__FILE__) . "/includes/install-checkup/class_bill_install.php";
        // ob_end_clean();
    }
}
add_action('wp_loaded', 'restore_classic_widgets_bill_install', 15);
