<?php
/*
Plugin Name: Restore Classic Widgets
Description: Description: Restore and enable the previous classic widgets settings screens and disables the Gutenberg block editor from managing widgets. No expiration date.
Version: 4.39
Text Domain: restore-classic-widgets
Domain Path: /language
Author: Bill Minozzi
Author URI: http://billminozzi.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
$bill_debug = false;
//$bill_debug = true;
//// debug2();
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

//
//
//
//
//

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


$restore_classic_widgets_is_admin = restore_classic_widgets_check_wordpress_logged_in_cookie();
//
//
//
//
//
//
// language
function restore_classic_widgets_localization_init()
{
    $path = RESTORECLASSICPATH . 'language/';
    $locale = apply_filters('plugin_locale', determine_locale(), 'restore-classic-widgets');

    // Log the selected locale
    // debug2("x restore_classic_widgets_localization_init: Locale detected: $locale");

    // Full path of the specific translation file (e.g., es_AR.mo)
    $specific_translation_path = $path . "restore-classic-widgets-$locale.mo";
    $specific_translation_loaded = false;

    // Check if the specific translation file exists and try to load it
    if (file_exists($specific_translation_path)) {
        $specific_translation_loaded = load_textdomain('restore-classic-widgets', $specific_translation_path);
        // debug2("x Specific translation loaded: $specific_translation_path");
    } else {
        // debug2("x Specific translation file not found: $specific_translation_path");
    }

    // List of languages that should have a fallback to a specific locale
    $fallback_locales = [
        'de' => 'de_DE',  // German
        'fr' => 'fr_FR',  // French
        'it' => 'it_IT',  // Italian
        'es' => 'es_ES',  // Spanish
        'pt' => 'pt_BR',  // Portuguese (fallback to Brazil)
        'nl' => 'nl_NL'   // Dutch (fallback to Netherlands)
    ];

    // If the specific translation was not loaded, try to fallback to the generic version
    if (!$specific_translation_loaded) {
        $language = explode('_', $locale)[0];  // Get only the language code, ignoring the country (e.g., es from es_AR)
        // debug2("No specific translation found for $locale. Trying fallback for language: $language");

        if (array_key_exists($language, $fallback_locales)) {
            // Full path of the generic fallback translation file (e.g., es_ES.mo)
            $fallback_translation_path = $path . "restore-classic-widgets-{$fallback_locales[$language]}.mo";

            // Check if the fallback generic file exists and try to load it
            if (file_exists($fallback_translation_path)) {
                load_textdomain('restore-classic-widgets', $fallback_translation_path);
                // debug2("Fallback translation loaded: $fallback_translation_path");
            } else {
                // debug2("Fallback translation file not found: $fallback_translation_path");
            }
        } else {
            // debug2("No fallback available for language: $language");
        }
    }


    // Log when the plugin is loaded
    load_plugin_textdomain('restore-classic-widgets', false, plugin_basename(RESTORECLASSICPATH) . '/language/');
    // debug2("Plugin text domain loaded.");
}
if ($restore_classic_widgets_is_admin) {
    add_action('plugins_loaded', 'restore_classic_widgets_localization_init');
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




function restore_classic_widgets_load_chat()
{
    global $restore_classic_widgets_is_admin;
    if ($restore_classic_widgets_is_admin and current_user_can("manage_options")) {
        // ob_start();
        //debug2();

        if (! class_exists('restore_classic_widgets_BillChat\ChatPlugin')) {
            require_once dirname(__FILE__) . "/includes/chat/class_bill_chat.php";
        }
    }
}
add_action('wp_loaded', 'restore_classic_widgets_load_chat');

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
