<?php
if (!defined('ABSPATH')) {
    exit;
}
echo '<br>';
echo '<big>';
echo esc_attr__('If you need support, please, copy and paste the info below in our', 'restore-classic-widgets'); ?> &nbsp;
<a href="https://BillMinozzi.com/support"><?php echo esc_attr__('Support Site', 'restore-classic-widgets'); ?></a>
<br><br>
<?php
restore_classic_widgets_sysinfo_display();
echo '</big>';
function restore_classic_widgets_sysinfo_display()
{
    if (!current_user_can('activate_plugins')) {
    }
?>
    <textarea style="height:60vh" ; readonly="readonly" onclick="this.focus(); this.select()"><?php echo esc_attr(restore_classic_widgets_sysinfo_get()); ?></textarea>
<?php
}
function restore_classic_widgets_get_host()
{
    if (isset($_SERVER['SERVER_NAME'])) {
        $server_name = sanitize_text_field(wp_unslash($_SERVER['SERVER_NAME']));
    } else {
        $server_name = 'Unknow';
    }
    $host = 'DBH: ' . DB_HOST . ', SRV: ' . $server_name;
    return $host;
}
function restore_classic_widgets_sysinfo_get()
{
    global $wpdb;
    global $restore_classic_widgets_userAgentOri;
    $theme_data   = wp_get_theme();
    $theme        = $theme_data->Name . ' ' . $theme_data->Version;
    $parent_theme = $theme_data->Template;
    if (!empty($parent_theme)) {
        $parent_theme_data = wp_get_theme($parent_theme);
        $parent_theme      = $parent_theme_data->Name . ' ' . $parent_theme_data->Version;
    }
    $host = gethostname();
    if ($host === false) {
        $host = restore_classic_widgets_get_host();
    }
    $return  = '=== Begin System Info (Generated ' . gmdate('Y-m-d H:i:s') . ' UTC) ===' . "\n\n";
    $file_path_from_plugin_root = str_replace(WP_PLUGIN_DIR . '/', '', __DIR__);
    $path_array = explode('/', $file_path_from_plugin_root);
    $plugin_folder_name = reset($path_array);
    $return .= '-- Plugin' . "\n\n";
    $return .= 'Name:                  ' .  $plugin_folder_name . "\n";
    $return .= 'Version:                  ' . RESTORE_CLASSIC_WIDGETSVERSION;
    $return .= "\n\n";
    $return .= '-- Site Info' . "\n\n";
    $return .= 'Site URL:                 ' . site_url() . "\n";
    $return .= 'Home URL:                 ' . home_url() . "\n";
    $return .= 'Multisite:                ' . (is_multisite() ? 'Yes' : 'No') . "\n";
    if ($host) {
        $return .= "\n" . '-- Hosting Provider' . "\n\n";
        $return .= 'Host:                     ' . $host . "\n";
    }
    $return .= "\n" . '-- User Browser' . "\n\n";
    $return .= $restore_classic_widgets_userAgentOri; // $browser;
    $return .= "\n\n";
    $locale = get_locale();
    $return .= "\n" . '-- WordPress Configuration' . "\n\n";
    $return .= 'Version:                  ' . get_bloginfo('version') . "\n";
    $return .= 'Language:                 ' . (!empty($locale) ? $locale : 'en_US') . "\n";
    $return .= 'Permalink Structure:      ' . (get_option('permalink_structure') ? get_option('permalink_structure') : 'Default') . "\n";
    $return .= 'Active Theme:             ' . $theme . "\n";
    if ($parent_theme !== $theme) {
        $return .= 'Parent Theme:             ' . $parent_theme . "\n";
    }
    $return .= 'ABSPATH:                  ' . ABSPATH . "\n";
    $return .= 'Plugin Dir:                  ' . RESTORE_CLASSIC_WIDGETSVERSION . "\n";
    $return .= 'Table Prefix:             ' . 'Length: ' . strlen($wpdb->prefix) . '   Status: ' . (strlen($wpdb->prefix) > 16 ? 'ERROR: Too long' : 'Acceptable') . "\n";
    if (defined('WP_DEBUG'))
        $return .= 'WP_DEBUG:                 ' .  WP_DEBUG ? 'Enabled' : 'Disabled' . "\n";
    else
        $return .= 'WP_DEBUG:                 ' .  'Not Set\n';
    $return .= 'Memory Limit:             ' . WP_MEMORY_LIMIT . "\n";
    $return .= "\n" . '-- WordPress Active Theme' . "\n\n";
    $return .= 'Theme Name:             ' . $parent_theme . "\n";
    $updates = get_plugin_updates();
    $muplugins = get_mu_plugins();
    if (count($muplugins) > 0) {
        $return .= "\n" . '-- Must-Use Plugins' . "\n\n";
        foreach ($muplugins as $plugin => $plugin_data) {
            $return .= $plugin_data['Name'] . ': ' . $plugin_data['Version'] . "\n";
        }
    }
    $return .= "\n" . '-- WordPress Active Plugins' . "\n\n";
    $plugins = get_plugins();
    $active_plugins = get_option('active_plugins', array());
    foreach ($plugins as $plugin_path => $plugin) {
        if (!in_array($plugin_path, $active_plugins)) {
            continue;
        }
        $update = (array_key_exists($plugin_path, $updates)) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
        $plugin_url = '';
        if (!empty($plugin['PluginURI'])) {
            $plugin_url = $plugin['PluginURI'];
        } elseif (!empty($plugin['AuthorURI'])) {
            $plugin_url = $plugin['AuthorURI'];
        } elseif (!empty($plugin['Author'])) {
            $plugin_url = $plugin['Author'];
        }
        if ($plugin_url) {
            $plugin_url = "\n" . $plugin_url;
        }
        $return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . $plugin_url . "\n\n";
    }
    $return .= "\n" . '-- WordPress Inactive Plugins' . "\n\n";
    foreach ($plugins as $plugin_path => $plugin) {
        if (in_array($plugin_path, $active_plugins)) {
            continue;
        }
        $update = (array_key_exists($plugin_path, $updates)) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
        $plugin_url = '';
        if (!empty($plugin['PluginURI'])) {
            $plugin_url = $plugin['PluginURI'];
        } elseif (!empty($plugin['AuthorURI'])) {
            $plugin_url = $plugin['AuthorURI'];
        } elseif (!empty($plugin['Author'])) {
            $plugin_url = $plugin['Author'];
        }
        if ($plugin_url) {
            $plugin_url = "\n" . $plugin_url;
        }
        $return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . $plugin_url . "\n\n";
    }
    if (is_multisite()) {
        $return .= "\n" . '-- Network Active Plugins' . "\n\n";
        $plugins = wp_get_active_network_plugins();
        $active_plugins = get_site_option('active_sitewide_plugins', array());
        foreach ($plugins as $plugin_path) {
            $plugin_base = plugin_basename($plugin_path);
            if (!array_key_exists($plugin_base, $active_plugins)) {
                continue;
            }
            $update = (array_key_exists($plugin_path, $updates)) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
            $plugin  = get_plugin_data($plugin_path);
            $plugin_url = '';
            if (!empty($plugin['PluginURI'])) {
                $plugin_url = $plugin['PluginURI'];
            } elseif (!empty($plugin['AuthorURI'])) {
                $plugin_url = $plugin['AuthorURI'];
            } elseif (!empty($plugin['Author'])) {
                $plugin_url = $plugin['Author'];
            }
            if ($plugin_url) {
                $plugin_url = "\n" . $plugin_url;
            }
            $return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . $plugin_url . "\n\n";
        }
    }
    $return .= "\n" . '-- Webserver Configuration' . "\n\n";
    $return .= 'OS Type & Version:        ' . restore_classic_widgets_OSName();
    $return .= 'PHP Version:              ' . PHP_VERSION . "\n";
    $return .= 'MySQL Version:            ' . $wpdb->db_version() . "\n";
    $server_software = isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : 'N/A';
    $return .= 'Webserver Info:           ' . $server_software . "\n";
    $return .= "\n" . '-- PHP Configuration' . "\n\n";
    $return .= 'Memory Limit:             ' . ini_get('memory_limit') . "\n";
    $return .= 'Upload Max Size:          ' . ini_get('upload_max_filesize') . "\n";
    $return .= 'Post Max Size:            ' . ini_get('post_max_size') . "\n";
    $return .= 'Upload Max Filesize:      ' . ini_get('upload_max_filesize') . "\n";
    $return .= 'Time Limit:               ' . ini_get('max_execution_time') . "\n";
    $return .= 'Max Input Vars:           ' . ini_get('max_input_vars') . "\n";
    $return .= 'Display Errors:           ' . (ini_get('display_errors') ? 'On (' . ini_get('display_errors') . ')' : 'N/A') . "\n";
    $return .= "\n" . '-- PHP Extensions' . "\n\n";
    $return .= 'cURL:                     ' . (function_exists('curl_init') ? 'Supported' : 'Not Supported') . "\n";
    $return .= 'fsockopen:                ' . (function_exists('fsockopen') ? 'Supported' : 'Not Supported') . "\n";
    $return .= 'SOAP Client:              ' . (class_exists('SoapClient') ? 'Installed' : 'Not Installed') . "\n";
    $return .= 'Suhosin:                  ' . (extension_loaded('suhosin') ? 'Installed' : 'Not Installed') . "\n";
    $return .= "\n" . '=== End System Info ===';
    return $return;
}
function restore_classic_widgets_OSName()
{
    try {
        if (false == function_exists("shell_exec") || false == @is_readable("/etc/os-release")) {
            return false;
        }
        $os = shell_exec('cat /etc/os-release | grep "PRETTY_NAME"');
        return explode("=", $os)[1];
    } catch (Exception $e) {
        return false;
    }
}