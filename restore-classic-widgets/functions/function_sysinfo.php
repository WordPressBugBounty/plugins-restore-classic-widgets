<?php
if (!defined('ABSPATH'))  exit;
function restore_classic_widgets_sysinfo_get()
{
    global $wpdb;
    global $wp_filesystem;
    if (!function_exists('WP_Filesystem')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    WP_Filesystem();
    $restore_classic_widgets_userAgentOri = restore_classic_widgets_get_ua2();
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
    $return  = '=== Begin System Info v 2.1a (Generated ' . gmdate('Y-m-d H:i:s') . ') ===' . "\n\n";
    $return  = '\nPrompt_Version: 1.0.1\n';
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
    $return .= '\n--- BEGIN SERVER HARDWARE DATA ---\n';
    try {
        $restore_classic_widgets_cpu_info = restore_classic_widgets_get_full_cpu_info();
        $cpu_section_written = false;
        if (!empty($restore_classic_widgets_cpu_info['cores']) && $restore_classic_widgets_cpu_info['cores'] !== 'Unknown') {
            if (!$cpu_section_written) {
                $return .= "\n-- CPU Information\n\n";
                $cpu_section_written = true;
            }
            $return .= 'Number of Cores:          ' . $restore_classic_widgets_cpu_info['cores'] . "\n";
        }
        if (!empty($restore_classic_widgets_cpu_info['architecture']) && $restore_classic_widgets_cpu_info['architecture'] !== 'Unknown') {
            if (!$cpu_section_written) {
                $return .= "\n-- CPU Information\n\n";
                $cpu_section_written = true;
            }
            $return .= 'Architecture:             ' . $restore_classic_widgets_cpu_info['architecture'] . "\n";
        }
        if (!empty($restore_classic_widgets_cpu_info['model']) && $restore_classic_widgets_cpu_info['model'] !== 'Unknown') {
            if (!$cpu_section_written) {
                $return .= "\n-- CPU Information\n\n";
                $cpu_section_written = true;
            }
            $return .= 'Model:                    ' . $restore_classic_widgets_cpu_info['model'] . "\n";
        }
        $restore_classic_widgets_load = restore_classic_widgets_get_load_average();
        $restore_classic_widgets_cores = is_numeric($restore_classic_widgets_cpu_info['cores']) ? (int)$restore_classic_widgets_cpu_info['cores'] : 1;
        if (!empty($restore_classic_widgets_load)) {
            $return .= "\n-- System Load Averages\n\n";
            foreach (['1min', '5min', '15min'] as $interval) {
                $value = $restore_classic_widgets_load[$interval] ?? null;
                $percent = restore_classic_widgets_calculate_load_percentage($value, $restore_classic_widgets_cores);
                $display_value = $value !== null ? $value : 'N/A';
                $display_percent = $percent !== null ? $percent . '%' : 'N/A';
                $return .= 'Load Average (' . $interval . '):     ' . $display_value . ' (' . $display_percent . ")\n";
            }
        }
    } catch (Exception $e) {
    }
    $return .= '\n--- END SERVER HARDWARE DATA ---\n';
    $return .= "\n" . '-- User Browser' . "\n\n";
    $return .= $restore_classic_widgets_userAgentOri; // $browser;
    $return .= "\n\n";
    $locale = get_locale();
    $return .= "\n" . '-- WordPress Configuration' . "\n\n";
    $return .= 'Version:                  ' . get_bloginfo('version') . "\n";
    $return .= 'Language:                 ' . (!empty($locale) ? $locale : 'en_US') . "\n";
    $return .= 'Permalink Structure:      ' . (get_option('permalink_structure') ? get_option('permalink_structure') : 'Default') . "\n";
    if ($parent_theme !== $theme) {
    }
    $return .= 'ABSPATH:                  ' . ABSPATH . "\n";
    $return .= 'Plugin Dir:                  ' . RESTORE_CLASSIC_WIDGETSPATH . "\n";
    $return .= 'Table Prefix:             ' . 'Length: ' . strlen($wpdb->prefix) . '   Status: ' . (strlen($wpdb->prefix) > 16 ? 'ERROR: Too long' : 'Acceptable') . "\n";
    if (defined('WP_DEBUG')) {
        $return .= 'WP_DEBUG:                 ' . (WP_DEBUG ? 'Enabled' : 'Disabled');
    } else
        $return .= 'WP_DEBUG:   
	              ' .  'Not Set\n';
    $return .= "\n";
    $return .= "\n";
    $return .= 'WP Memory Limit:             ' . WP_MEMORY_LIMIT . "\n";
    $return .= "\n" . '--PHP Error Log Configuration' . "\n\n";
    $return .= 'PHP default Error Log Place:          ' . "\n";
    $error_log_path = ABSPATH . 'error_log'; // Consistent use of single quotes
    $errorLogPath = ini_get('error_log');
    if ($errorLogPath) {
        $return .= "Error Log is defined in PHP: " . $errorLogPath . "\n";
        try {
            if ($wp_filesystem->exists($errorLogPath)) { // Changed for WP_Filesystem API
                $return .= " (exists)\n";
                $return .= 'Size:                     ' . size_format($wp_filesystem->size($errorLogPath)) . "\n"; // Changed for WP_Filesystem API
                $return .= 'Readable:                 ' . ($wp_filesystem->is_readable($errorLogPath) ? 'Yes' : 'No') . "\n"; // Changed for WP_Filesystem API
                $return .= 'Writable:                 ' . ($wp_filesystem->is_writable($errorLogPath) ? 'Yes' : 'No') . "\n"; // Changed for WP_Filesystem API
            } else {
                $return .= " (does not exist)\n"; // Adicionado mensagem para indicar que o arquivo não existe
                $return .= 'Size:                     N/A' . "\n";
                $return .= 'Readable:                 N/A' . "\n";
                $return .= 'Writable:                 N/A' . "\n";
            }
        } catch (Exception $e) {
            $return .= 'Error checking error log path: ' . $e->getMessage() . "\n";
        }
    } else {
        $return .= "Error log not defined on PHP file ini\n";
    }
    $return .= "\n";
    $return .= 'Root Place:                     ' . ($wp_filesystem->exists($error_log_path) ? 'Exists. (' . $error_log_path . ')'  : 'Does Not Exist') . "\n"; // Changed for WP_Filesystem API
    try {
        if ($wp_filesystem->exists($error_log_path)) { // Changed for WP_Filesystem API
            $return .= 'Size:                         ' . size_format($wp_filesystem->size($error_log_path)) . "\n"; // Changed for WP_Filesystem API
            $return .= 'Readable:                     ' . ($wp_filesystem->is_readable($error_log_path) ? 'Yes' : 'No') . "\n";  // Changed for WP_Filesystem API
            $return .= 'Writable:                     ' . ($wp_filesystem->is_writable($error_log_path) ? 'Yes' : 'No') . "\n"; // Changed for WP_Filesystem API
        } else {
            $return .= 'Size:                         N/A' . "\n";
            $return .= 'Readable:                     N/A' . "\n";
            $return .= 'Writable:                     N/A' . "\n";
        }
    } catch (Exception $e) {
        $return .= 'Error checking error log path: ' . $e->getMessage() . "\n";
    }
    $return .= "\n" . '-- Error Handler Information' . "\n\n";
    try {
        if (function_exists('set_error_handler')) {
            $return .= 'set_error_handler Exists:   Yes' . "\n";
        } else {
            $return .= 'set_error_handler() Exists:   No' . "\n";
        }
    } catch (Exception $e) {
        $return .= 'Error checking error handler functions: ' . $e->getMessage() . "\n";
    }
    $return .= "\n" . '-- WordPress Debug Log Configuration' . "\n\n";
    $debug_log_path = WP_CONTENT_DIR . '/debug.log'; // Default path
    if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG !== true && is_string(WP_DEBUG_LOG)) {
        $debug_log_path = WP_DEBUG_LOG; // Override if it is defined and it is a string path.
    }
    $return .= 'Debug Log Path:             ' . $debug_log_path . "\n";
    try {
        if ($wp_filesystem->exists($debug_log_path)) { // Changed for WP_Filesystem API
            $return .= 'File Exists:                  Yes' . "\n";
            try {
                $fileSize = $wp_filesystem->size($debug_log_path); // Changed for WP_Filesystem API
                $return .= 'Size:                         ' . size_format($fileSize) . "\n";
            } catch (Exception $e) {
                $return .= 'Size:                         Error getting file size: ' . $e->getMessage() . "\n";
            }
            $return .= 'Readable:                     ' . ($wp_filesystem->is_readable($debug_log_path) ? 'Yes' : 'No') . "\n"; // Changed for WP_Filesystem API
            $return .= 'Writable:                     ' . ($wp_filesystem->is_writable($debug_log_path) ? 'Yes' : 'No') . "\n"; // Changed for WP_Filesystem API
            $isDebugEnabled = defined('WP_DEBUG') && WP_DEBUG;
            $isLogEnabled = defined('WP_DEBUG_LOG') && WP_DEBUG_LOG;
            $return .= 'WP_DEBUG Enabled:            ' . ($isDebugEnabled ? 'Yes' : 'No') . "\n";
            $return .= 'WP_DEBUG_LOG Enabled:        ' . ($isLogEnabled ? 'Yes' : 'No') . "\n";
            if ($isDebugEnabled && $isLogEnabled) {
                $return .= 'Debug Logging Active:       Yes' . "\n";
            } elseif ($isDebugEnabled) {
                $return .= 'Debug Logging Active:       No (Logging to file is disabled)' . "\n";
            } else {
                $return .= 'Debug Logging Active:       No (WP_DEBUG is disabled)' . "\n";
            }
        } else {
            $return .= 'File Exists:                  No' . "\n";
            $return .= 'Size:                         N/A' . "\n";
            $return .= 'Readable:                     N/A' . "\n";
            $return .= 'Writable:                     N/A' . "\n";
            $return .= 'WP_DEBUG Enabled:            ' . (defined('WP_DEBUG') && WP_DEBUG ? 'Yes' : 'No') . "\n";
            $return .= 'WP_DEBUG_LOG Enabled:        ' . (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ? 'Yes' : 'No') . "\n";
            $return .= 'Debug Logging Active:       No (File does not exist)' . "\n";
        }
    } catch (Exception $e) {
        $return .= 'Error checking debug log file: ' . $e->getMessage() . "\n";
    }
    $return .= 'WP_Query Debug: ' . (defined('WP_QUERY_DEBUG') && WP_QUERY_DEBUG ? 'Yes' : 'No') . "\n";
    $return .= "\n" . '-- Additional Debugging Constants' . "\n\n";
    $return .= 'SCRIPT_DEBUG:                ' . (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? 'Yes' : 'No') . "\n";
    $return .= 'SAVEQUERIES:                 ' . (defined('SAVEQUERIES') && SAVEQUERIES ? 'Yes' : 'No') . "\n";
    $return .= 'WP_DEBUG_DISPLAY:            ' . (defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY ? 'Yes' : 'No') . "\n";
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
    $return .= "\n" . '-- WordPress Active Theme' . "\n\n";
    $current_theme = wp_get_theme(); // Pega o tema ativo
    $themes = wp_get_themes(); // Pega todos os temas instalados
    $updates = get_site_transient('update_themes'); // Pega informações de atualizações
    $update = (isset($updates->response[$current_theme->get_stylesheet()]))
        ? ' (needs update - ' . $updates->response[$current_theme->get_stylesheet()]['new_version'] . ')'
        : '';
    $theme_url = '';
    if ($current_theme->get('ThemeURI')) {
        $theme_url = $current_theme->get('ThemeURI');
    } elseif ($current_theme->get('AuthorURI')) {
        $theme_url = $current_theme->get('AuthorURI');
    } elseif ($current_theme->get('Author')) {
        $theme_url = $current_theme->get('Author');
    }
    if ($theme_url) {
        $theme_url = "\n" . $theme_url;
    }
    $return .= $current_theme->get('Name') . ': ' . $current_theme->get('Version') . $update . $theme_url . "\n\n";
    $return .= "\n" . '-- WordPress Inactive Themes' . "\n\n";
    foreach ($themes as $theme) {
        if ($theme->get_stylesheet() === $current_theme->get_stylesheet()) {
            continue; // Pula o tema ativo
        }
        $update = (isset($updates->response[$theme->get_stylesheet()]))
            ? ' (needs update - ' . $updates->response[$theme->get_stylesheet()]['new_version'] . ')'
            : '';
        $theme_url = '';
        if ($theme->get('ThemeURI')) {
            $theme_url = $theme->get('ThemeURI');
        } elseif ($theme->get('AuthorURI')) {
            $theme_url = $theme->get('AuthorURI');
        } elseif ($theme->get('Author')) {
            $theme_url = $theme->get('Author');
        }
        if ($theme_url) {
            $theme_url = "\n" . $theme_url;
        }
        $return .= $theme->get('Name') . ': ' . $theme->get('Version') . $update . $theme_url . "\n\n";
    }
    if (is_multisite()) {
        $return .= "\n" . '-- Network Enabled Themes' . "\n\n";
        $network_themes = get_site_option('allowedthemes'); // Temas permitidos na rede
        foreach ($themes as $theme) {
            if (!isset($network_themes[$theme->get_stylesheet()]) || $network_themes[$theme->get_stylesheet()] !== true) {
                continue;
            }
            if ($theme->get_stylesheet() === $current_theme->get_stylesheet()) {
                continue; // Pula se já foi listado como ativo
            }
            $update = (isset($updates->response[$theme->get_stylesheet()]))
                ? ' (needs update - ' . $updates->response[$theme->get_stylesheet()]['new_version'] . ')'
                : '';
            $theme_url = '';
            if ($theme->get('ThemeURI')) {
                $theme_url = $theme->get('ThemeURI');
            } elseif ($theme->get('AuthorURI')) {
                $theme_url = $theme->get('AuthorURI');
            } elseif ($theme->get('Author')) {
                $theme_url = $theme->get('Author');
            }
            if ($theme_url) {
                $theme_url = "\n" . $theme_url;
            }
            $return .= $theme->get('Name') . ': ' . $theme->get('Version') . $update . $theme_url . "\n\n";
        }
    }
    $return .= "\n" . '-- Webserver Configuration' . "\n\n";
    $return .= 'OS Type & Version:        ' . restore_classic_widgets_OSName();
    $return .= 'PHP Version:              ' . PHP_VERSION . "\n";
    $return .= 'MySQL Version:            ' . $wpdb->db_version() . "\n";
    $server_software = isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : 'N/A';
    $return .= 'Webserver Info:           ' . $server_software . "\n";
    $return .= "\n" . '-- PHP Configuration' . "\n\n";
    $return .= 'PHP Memory Limit:             ' . ini_get('memory_limit') . "\n";
    $return .= 'Upload Max Size:          ' . ini_get('upload_max_filesize') . "\n";
    $return .= 'Post Max Size:            ' . ini_get('post_max_size') . "\n";
    $return .= 'Upload Max Filesize:      ' . ini_get('upload_max_filesize') . "\n";
    $return .= 'Time Limit:               ' . ini_get('max_execution_time') . "\n";
    $return .= 'Max Input Vars:           ' . ini_get('max_input_vars') . "\n";
    $return .= 'Display Errors:           ' . (ini_get('display_errors') ? 'On (' . ini_get('display_errors') . ')' : 'N/A') . "\n";
    $return .= 'Log Errors:           ' . (ini_get('log_errors') ? 'On (' . ini_get('log_errors') . ')' : 'N/A') . "\n";
    try {
        $return .= 'Error Reporting:          ' . 'N/A (Function disabled for security)' . "\n";
    } catch (Exception $e) {
        $return .= 'Error Reporting: Fail to get error_reporting(): ' . $e . '\n';
    }
    $return .= 'Fopen:                     ' . (function_exists('fopen') ? 'Supported' : 'Not Supported') . "\n";
    $return .= 'Fseek:                     ' . (function_exists('fseek') ? 'Supported' : 'Not Supported') . "\n";
    $return .= 'Ftell:                     ' . (function_exists('ftell') ? 'Supported' : 'Not Supported') . "\n";
    $return .= 'Fread:                     ' . (function_exists('fread') ? 'Supported' : 'Not Supported') . "\n";
    $return .= "\n" . '-- PHP Extensions' . "\n\n";
    $return .= 'cURL:                     ' . (function_exists('curl_init') ? 'Supported' : 'Not Supported') . "\n";
    $return .= 'fsockopen:                ' . (function_exists('fsockopen') ? 'Supported' : 'Not Supported') . "\n";
    $return .= 'SOAP Client:              ' . (class_exists('SoapClient') ? 'Installed' : 'Not Installed') . "\n";
    $return .= 'Suhosin:                  ' . (extension_loaded('suhosin') ? 'Installed' : 'Not Installed') . "\n";
    $return .= 'SplFileObject:            ' . (class_exists('SplFileObject') ? 'Installed' : 'Not Installed') . "\n";
    $return .= 'Imageclick:               ' . (extension_loaded('imagick') ? 'Installed' : 'Not Installed') . "\n";
    $return .= "\n" . '=== End System Info v 2.1a  ===';
    return $return;
}
function restore_classic_widgets_readable_error_reporting($level)
{
    $error_levels = [
        E_ALL => 'E_ALL',
        E_ERROR => 'E_ERROR',
        E_WARNING => 'E_WARNING',
        E_PARSE => 'E_PARSE',
        E_NOTICE => 'E_NOTICE',
        E_CORE_ERROR => 'E_CORE_ERROR',
        E_CORE_WARNING => 'E_CORE_WARNING',
        E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING => 'E_COMPILE_WARNING',
        E_USER_ERROR => 'E_USER_ERROR',
        E_USER_WARNING => 'E_USER_WARNING',
        E_USER_NOTICE => 'E_USER_NOTICE',
        E_STRICT => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_DEPRECATED => 'E_DEPRECATED',
        E_USER_DEPRECATED => 'E_USER_DEPRECATED',
    ];
    $active_errors = [];
    foreach ($error_levels as $level_value => $level_name) {
        if ($level & $level_value) {
            $active_errors[] = $level_name;
        }
    }
    return empty($active_errors) ? 'N/A' : implode(' | ', $active_errors);
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
function restore_classic_widgets_get_ua2()
{
    if (!isset($_SERVER['HTTP_USER_AGENT'])) {
        return '';
    }
    $ua = sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT']));
    if (!empty($ua))
        return trim($ua);
    else
        return "";
}
function restore_classic_widgets_get_load_average()
{
    try {
        if (function_exists('sys_getloadavg')) {
            $restore_classic_widgets_load = sys_getloadavg();
            if ($restore_classic_widgets_load !== false && is_array($restore_classic_widgets_load)) {
                return [
                    '1min'  => $restore_classic_widgets_load[0],
                    '5min'  => $restore_classic_widgets_load[1],
                    '15min' => $restore_classic_widgets_load[2],
                ];
            }
        }
        return restore_classic_widgets_get_load_average_from_proc();
    } catch (Exception $e) {
        return [
            '1min'  => null,
            '5min'  => null,
            '15min' => null,
        ];
    }
}
function restore_classic_widgets_get_load_average_from_proc()
{
    try {
        global $wp_filesystem;
        if (!$wp_filesystem) {
            return [
                '1min'  => null,
                '5min'  => null,
                '15min' => null,
            ];
        }
        if ($wp_filesystem->exists('/proc/loadavg')) {
            $restore_classic_widgets_contents = $wp_filesystem->get_contents('/proc/loadavg');
            if ($restore_classic_widgets_contents !== false) {
                $restore_classic_widgets_parts = explode(' ', trim($restore_classic_widgets_contents));
                if (count($restore_classic_widgets_parts) >= 3) {
                    return [
                        '1min'  => (float) $restore_classic_widgets_parts[0],
                        '5min'  => (float) $restore_classic_widgets_parts[1],
                        '15min' => (float) $restore_classic_widgets_parts[2],
                    ];
                }
            }
        }
        return [
            '1min'  => null,
            '5min'  => null,
            '15min' => null,
        ];
    } catch (Exception $e) {
        return [
            '1min'  => null,
            '5min'  => null,
            '15min' => null,
        ];
    }
}
function restore_classic_widgets_get_full_cpu_info()
{
    $restore_classic_widgets_info = [
        'cores' => null,
        'architecture' => null,
        'model' => null,
    ];
    try {
        global $wp_filesystem;
        if (!$wp_filesystem) {
            WP_Filesystem(); // Try to initialize if not already.
        }
        $restore_classic_widgets_info['cores'] = 'Unknown';
        try {
            $restore_classic_widgets_info['architecture'] = php_uname('m') ?: 'Unknown';
        } catch (Exception $e) {
            $restore_classic_widgets_info['architecture'] = 'Unknown';
        }
        $cpu_model_found = false;
        if ($wp_filesystem && $wp_filesystem->exists('/proc/cpuinfo') && $wp_filesystem->is_readable('/proc/cpuinfo')) {
            try {
                $restore_classic_widgets_cpuinfo = $wp_filesystem->get_contents('/proc/cpuinfo');
                if ($restore_classic_widgets_cpuinfo !== false && preg_match('/model name\s+:\s+(.+)/', $restore_classic_widgets_cpuinfo, $matches)) {
                    $restore_classic_widgets_info['model'] = trim($matches[1]);
                    $cpu_model_found = true;
                }
            } catch (Exception $e) {
            }
        }
        if (!$cpu_model_found && function_exists('shell_exec')) {
            $lscpu_output = @shell_exec('lscpu 2>/dev/null');
            if (!empty($lscpu_output) && preg_match('/Model name:\s+(.+)/', $lscpu_output, $matches)) {
                $restore_classic_widgets_info['model'] = trim($matches[1]);
                $cpu_model_found = true;
            }
        }
        if (!$cpu_model_found && function_exists('exec')) {
            $output = [];
            @exec('lscpu 2>/dev/null', $output);
            if (!empty($output)) {
                foreach ($output as $line) {
                    if (stripos($line, 'Model name:') === 0) {
                        $restore_classic_widgets_info['model'] = trim(substr($line, strpos($line, ':') + 1));
                        $cpu_model_found = true;
                        break;
                    }
                }
            }
        }
        if (!$cpu_model_found && function_exists('shell_exec') && stripos(PHP_OS, 'Darwin') === 0) {
            $sysctl_output = @shell_exec("sysctl -n machdep.cpu.brand_string");
            if (!empty($sysctl_output)) {
                $restore_classic_widgets_info['model'] = trim($sysctl_output);
                $cpu_model_found = true;
            }
        }
        if (!$cpu_model_found && function_exists('shell_exec') && stripos(PHP_OS, 'WIN') === 0) {
            $wmic_output = @shell_exec("wmic cpu get Name /format:list");
            if (!empty($wmic_output) && preg_match('/Name=(.+)/i', $wmic_output, $matches)) {
                $restore_classic_widgets_info['model'] = trim($matches[1]);
                $cpu_model_found = true;
            }
        }
        if (!$cpu_model_found) {
            $restore_classic_widgets_info['model'] = 'Unknown';
        }
        return $restore_classic_widgets_info;
    } catch (Exception $e) {
        return $restore_classic_widgets_info;
    }
}
function restore_classic_widgets_calculate_load_percentage($load, $cores)
{
    try {
        if ($cores <= 0 || $load === null) {
            return null;
        }
        return round(($load / $cores) * 100, 2);
    } catch (Exception $e) {
        return null;
    }
}