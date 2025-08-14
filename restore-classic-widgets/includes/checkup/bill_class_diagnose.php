<?php
if (!defined('ABSPATH')) {
    die('Invalid request.');
}
if (is_multisite())
    return;
include_once(ABSPATH . 'wp-admin/includes/plugin.php');
if (function_exists('is_plugin_active')) {
    $restore_classic_widgets_plugins_to_check = array(
        'antihacker/antihacker.php',
        'wp-memory/wpmemory.php',
        'wptools/wptools.php',
        'stopbadbots/stopbadbots.php'
    );
    foreach ($restore_classic_widgets_plugins_to_check as $plugin_path) {
        if (is_plugin_active($plugin_path))
            return;
    }
}
class restore_classic_widgets_Class_Diagnose
{
    private static $instance = null;
    private $notification_url;
    private $notification_url2;
    private $plugin_text_domain;
    private $plugin_slug;
    private $global_variable_has_errors;
    private $global_variable_memory;
    private $global_plugin_slug;
    public function __construct(
        $notification_url,
        $notification_url2,
        $plugin_text_domain,
        $plugin_slug
    ) {
        $this->setNotificationUrl($notification_url);
        $this->setNotificationUrl2($notification_url2);
        $this->setPluginTextDomain($plugin_text_domain);
        $this->setPluginSlug($plugin_slug);
        $this->global_variable_has_errors = $this->restore_classic_widgets_check_errors_today();
        $this->global_variable_memory = $this->check_memory();
        $this->global_plugin_slug = $plugin_slug;
        add_action("admin_notices", [$this, "show_dismissible_notification"]);
        add_action("admin_notices", [$this, "show_dismissible_notification2"]);
        if ($this->global_variable_has_errors)
            add_action("admin_bar_menu", [$this, "add_site_health_link_to_admin_toolbar"], 999);
        add_action("admin_head", [$this, "custom_help_tab"]);
        $memory = $this->global_variable_memory;
        if (
            $memory["free"] < 30 or
            $memory["percent"] > 85 or
            $this->global_variable_has_errors
        ) {
            add_filter("site_health_navigation_tabs", [
                $this,
                "site_health_navigation_tabs",
            ]);
            add_action("site_health_tab_content", [
                $this,
                "site_health_tab_content",
            ]);
        }
    }
    public function setNotificationUrl($notification_url)
    {
        $this->notification_url = $notification_url;
    }
    public function setNotificationUrl2($notification_url2)
    {
        $this->notification_url2 = $notification_url2;
    }
    public function setPluginTextDomain($plugin_text_domain)
    {
        $this->plugin_text_domain = $plugin_text_domain;
    }
    public function setPluginSlug($plugin_slug)
    {
        $this->plugin_slug = $plugin_slug;
    }
    public static function get_instance(
        $notification_url,
        $notification_url2,
        $plugin_text_domain,
        $plugin_slug
    ) {
        if (self::$instance === null) {
            self::$instance = new self(
                $notification_url,
                $notification_url2,
                $plugin_text_domain,
                $plugin_slug
            );
        }
        return self::$instance;
    }
    public function check_memory()
    {
        try {
            if (!function_exists('ini_get')) {
                $wpmemory["msg_type"] = "notok";
                return $wpmemory;
            } else {
                $wpmemory["limit"] = (int) ini_get("memory_limit");
            }
            if (!is_numeric($wpmemory["limit"])) {
                $wpmemory["msg_type"] = "notok";
                return $wpmemory;
            }
            if ($wpmemory["limit"] > 9999999) {
                $wpmemory["wp_limit"] =
                    $wpmemory["wp_limit"] / 1024 / 1024;
            }
            if (!function_exists('memory_get_usage')) {
                $wpmemory["msg_type"] = "notok";
                return $wpmemory;
            } else {
                $wpmemory["usage"] = memory_get_usage();
            }
            if ($wpmemory["usage"] < 1) {
                $wpmemory["msg_type"] = "notok";
                return $wpmemory;
            } else {
                $wpmemory["usage"] = round($wpmemory["usage"] / 1024 / 1024, 0);
            }
            if (!is_numeric($wpmemory["usage"])) {
                $wpmemory["msg_type"] = "notok";
                return $wpmemory;
            }
            if (!defined("WP_MEMORY_LIMIT")) {
                $wpmemory["wp_limit"] = 40;
            } else {
                $wp_memory_limit = WP_MEMORY_LIMIT;
                $wpmemory["wp_limit"] = (int) $wp_memory_limit;
            }
            $wpmemory["percent"] = $wpmemory["usage"] / $wpmemory["wp_limit"];
            $wpmemory["color"] = "font-weight:normal;";
            if ($wpmemory["percent"] > 0.7) {
                $wpmemory["color"] = "font-weight:bold;color:#E66F00";
            }
            if ($wpmemory["percent"] > 0.85) {
                $wpmemory["color"] = "font-weight:bold;color:red";
            }
            $wpmemory["free"] = $wpmemory["wp_limit"] - $wpmemory["usage"];
            $wpmemory["msg_type"] = "ok";
        } catch (Exception $e) {
            $wpmemory["msg_type"] = "notok";
            return $wpmemory;
        }
        return $wpmemory;
    }
    public function restore_classic_widgets_check_errors_today()
    {
        $restore_classic_widgets_count = 0;
        $restore_classic_widgets_themePath = get_theme_root();
        $error_log_path = trim(ini_get("error_log"));
        if (
            !is_null($error_log_path) and
            $error_log_path != trim(ABSPATH . "error_log")
        ) {
            $restore_classic_widgets_folders = [
                $error_log_path,
                ABSPATH . "error_log",
                ABSPATH . "php_errorlog",
                restore_classic_widgets_dir_path(__FILE__) . "/error_log",
                restore_classic_widgets_dir_path(__FILE__) . "/php_errorlog",
                $restore_classic_widgets_themePath . "/error_log",
                $restore_classic_widgets_themePath . "/php_errorlog",
            ];
        } else {
            $restore_classic_widgets_folders = [
                ABSPATH . "error_log",
                ABSPATH . "php_errorlog",
                restore_classic_widgets_dir_path(__FILE__) . "/error_log",
                restore_classic_widgets_dir_path(__FILE__) . "/php_errorlog",
                $restore_classic_widgets_themePath . "/error_log",
                $restore_classic_widgets_themePath . "/php_errorlog",
            ];
        }
        $restore_classic_widgets_admin_path = str_replace(
            get_bloginfo("url") . "/",
            ABSPATH,
            get_admin_url()
        );
        array_push($restore_classic_widgets_folders, $restore_classic_widgets_admin_path . "/error_log");
        array_push($restore_classic_widgets_folders, $restore_classic_widgets_admin_path . "/php_errorlog");
        $restore_classic_widgets_plugins = array_slice(scandir(restore_classic_widgets_dir_path(__FILE__)), 2);
        foreach ($restore_classic_widgets_plugins as $restore_classic_widgets_plugin) {
            if (is_dir(restore_classic_widgets_dir_path(__FILE__) . "/" . $restore_classic_widgets_plugin)) {
                array_push(
                    $restore_classic_widgets_folders,
                    restore_classic_widgets_dir_path(__FILE__) . "/" . $restore_classic_widgets_plugin . "/error_log"
                );
                array_push(
                    $restore_classic_widgets_folders,
                    restore_classic_widgets_dir_path(__FILE__) . "/" . $restore_classic_widgets_plugin . "/php_errorlog"
                );
            }
        }
        $restore_classic_widgets_themes = array_slice(scandir($restore_classic_widgets_themePath), 2);
        foreach ($restore_classic_widgets_themes as $restore_classic_widgets_theme) {
            if (is_dir($restore_classic_widgets_themePath . "/" . $restore_classic_widgets_theme)) {
                array_push(
                    $restore_classic_widgets_folders,
                    $restore_classic_widgets_themePath . "/" . $restore_classic_widgets_theme . "/error_log"
                );
                array_push(
                    $restore_classic_widgets_folders,
                    $restore_classic_widgets_themePath . "/" . $restore_classic_widgets_theme . "/php_errorlog"
                );
            }
        }
        foreach ($restore_classic_widgets_folders as $restore_classic_widgets_folder) {
            if (trim(empty($restore_classic_widgets_folder))) {
                continue;
            }
            foreach (glob($restore_classic_widgets_folder) as $restore_classic_widgets_filename) {
                if (strpos($restore_classic_widgets_filename, "backup") != true) {
                    $restore_classic_widgets_count++;
                    $marray = $this->restore_classic_widgets_read_file($restore_classic_widgets_filename, 20);
                    if (gettype($marray) != "array" or count($marray) < 1) {
                        continue;
                    }
                    if (count($marray) > 0) {
                        for ($i = 0; $i < count($marray); $i++) {
                            if (
                                substr($marray[$i], 0, 1) != "[" or
                                empty($marray[$i])
                            ) {
                                continue;
                            }
                            $pos = strpos($marray[$i], " ");
                            $string = trim(substr($marray[$i], 1, $pos));
                            if (empty($string)) {
                                continue;
                            }
                            $last_date = strtotime($string);
                            if (time() - $last_date < 60 * 60 * 24 * 2) {
                                return true;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }
    public function show_dismissible_notification()
    {
        if ($this->is_notification_displayed_today()) {
            return;
        }
        $memory = $this->global_variable_memory;
        if ($memory["free"] > 30 and $wpmemory["percent"] < 85) {
            return;
        }
        $message = __("Our plugin", 'restore-classic-widgets');
        $message .= ' (' . $this->plugin_slug . ') ';
        $message .= __("cannot function properly because your WordPress Memory Limit is too low. Your site will experience serious issues, even if you deactivate our plugin.", 'restore-classic-widgets');
        $message .=
            '<a href="' .
            esc_url($this->notification_url) .
            '">' .
            " " .
            __("Learn more", 'restore-classic-widgets') .
            "</a>";
        echo '<div class="notice notice-error is-dismissible">';
        echo '<p style="color: red;">' . wp_kses_post($message) . "</p>"; // This line is already secure
        echo "</div>";
    }
    public function show_dismissible_notification2()
    {
        if ($this->is_notification_displayed_today()) {
            return;
        }
        if ($this->global_variable_has_errors) {
            $message = __("Your site has errors.", 'restore-classic-widgets');
            $message .= __("Our plugin", 'restore-classic-widgets');
            $message .= ' (' . $this->plugin_slug . ') ';
            $message .= __("can't function as intended. Errors, including JavaScript errors, may lead to visual problems or disrupt functionality, from minor glitches to critical site failures. Promptly address these issues before continuing.", 'restore-classic-widgets');
            $message .=
                '<a href="' .
                esc_url($this->notification_url2) .
                '">' .
                " " .
                __("Learn more", 'restore-classic-widgets') .
                "</a>";
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p style="color: red;">' . wp_kses_post($message) . "</p>"; // This line is already secure
            echo "</div>";
        }
    }
    public function is_notification_displayed_today()
    {
        $last_notification_date = get_option("restore_classic_widgets_show_warnings");
        $today = gmdate("Y-m-d");
        return $last_notification_date === $today;
    }
    public function site_health_navigation_tabs($tabs)
    {
        $tabs["Critical Issues"] = esc_html_x(
            "Critical Issues",
            "Site Health",
            'restore-classic-widgets'
        );
        return $tabs;
    }
    public function site_health_tab_content($tab)
    {
        if (!function_exists('restore_classic_widgets_strip_strong99')) {
            function restore_classic_widgets_strip_strong99($htmlString)
            {
                $textWithoutStrongTags = preg_replace(
                    "/<strong>(.*?)<\/strong>/i",
                    '$1',
                    $htmlString
                );
                return $textWithoutStrongTags;
            }
        }
        if ("Critical Issues" !== $tab) {
            return;
        } ?>
        <div class="wrap health-check-body, privacy-settings-body">
            <p style="border: 1px solid #000; padding: 10px;">
                <strong>
                    <?php
                    echo esc_html__("Displaying the latest recurring errors from your error log file and eventually alert about low WordPress memory limit is a courtesy of plugin", 'restore-classic-widgets');
                    echo ': ' . esc_html($this->global_plugin_slug) . '. ';
                    echo esc_html__("Disabling our plugin does not stop the errors from occurring; 
                it simply means you will no longer be notified here that they are happening, but they can still harm your site.", 'restore-classic-widgets');
                    ?>
                </strong>
            </p>
            <h3 style="color: red;">
                <?php
                echo esc_html__("Potential Problems", 'restore-classic-widgets');
                ?>
            </h3>
            <?php
            $memory = $this->global_variable_memory;
            $wpmemory = $memory;
            if ($memory["free"] < 30 or $wpmemory["percent"] > 85) { ?>
                <h2 style="color: red;">
                    <?php echo esc_html__("Low WordPress Memory Limit", 'restore-classic-widgets'); ?>
                </h2>
                <?php
                $mb = "MB";
                echo "<b>";
                echo "WordPress Memory Limit: " .
                    esc_html($wpmemory["wp_limit"]) .
                    esc_html($mb) .
                    "     |   ";
                $perc = $wpmemory["usage"] / $wpmemory["wp_limit"];
                if ($perc > 0.7) {
                    echo '<span style="color:' . esc_attr($wpmemory["color"]) . ';">';
                }
                echo esc_html__("Your usage now", 'restore-classic-widgets') .
                    ": " .
                    esc_html($wpmemory["usage"]) .
                    "MB    ";
                if ($perc > 0.7) {
                    echo "</span>";
                }
                echo "|   " .
                    esc_html__("Total Php Server Memory", 'restore-classic-widgets') .
                    " : " .
                    esc_html($wpmemory["limit"]) .
                    "MB";
                echo "</b>";
                echo "</center>";
                echo "<hr>";
                $free = $wpmemory["wp_limit"] - $wpmemory["usage"];
                echo '<p>';
                echo esc_html__("Your WordPress Memory Limit is too low, which can lead to critical issues on your site due to insufficient resources. Promptly address this issue before continuing.", 'restore-classic-widgets');
                echo '</b>';
                ?>
                </b>
                <a href="https://wpmemory.com/fix-low-memory-limit/">
                    <?php
                    echo esc_html__("Learn More", 'restore-classic-widgets');
                    ?>
                </a>
                </p>
                <br>
            <?php }
            ?>
            <?php
            if ($this->global_variable_has_errors) { ?>
                <h2 style="color: red;">
                    <?php
                    echo esc_html__("Site Errors", 'restore-classic-widgets');
                    ?>
                </h2>
                <p>
                    <?php
                    echo esc_html__("Your site has experienced errors for the past 2 days. These errors, including JavaScript issues, can result in visual problems or disrupt functionality, ranging from minor glitches to critical site failures. JavaScript errors can terminate JavaScript execution, leaving all subsequent commands inoperable.", 'restore-classic-widgets');
                    ?>
                    <a href="https://wptoolsplugin.com/site-language-error-can-crash-your-site/">
                        <?php
                        echo esc_html__("Learn More", 'restore-classic-widgets');
                        ?>
                    </a>
                </p>
    <?php
                $restore_classic_widgets_count = 0;
                define("restore_classic_widgets_dir_path(__FILE__)", restore_classic_widgets_dir_path(__FILE__));
                $restore_classic_widgets_themePath = get_theme_root();
                $error_log_path = trim(ini_get("error_log"));
                if (
                    !is_null($error_log_path) and
                    $error_log_path != trim(ABSPATH . "error_log")
                ) {
                    $restore_classic_widgets_folders = [
                        $error_log_path,
                        ABSPATH . "error_log",
                        ABSPATH . "php_errorlog",
                        restore_classic_widgets_dir_path(__FILE__) . "/error_log",
                        restore_classic_widgets_dir_path(__FILE__) . "/php_errorlog",
                        $restore_classic_widgets_themePath . "/error_log",
                        $restore_classic_widgets_themePath . "/php_errorlog",
                    ];
                } else {
                    $restore_classic_widgets_folders = [
                        ABSPATH . "error_log",
                        ABSPATH . "php_errorlog",
                        restore_classic_widgets_dir_path(__FILE__) . "/error_log",
                        restore_classic_widgets_dir_path(__FILE__) . "/php_errorlog",
                        $restore_classic_widgets_themePath . "/error_log",
                        $restore_classic_widgets_themePath . "/php_errorlog",
                    ];
                }
                $restore_classic_widgets_admin_path = str_replace(
                    get_bloginfo("url") . "/",
                    ABSPATH,
                    get_admin_url()
                );
                array_push($restore_classic_widgets_folders, $restore_classic_widgets_admin_path . "/error_log");
                array_push($restore_classic_widgets_folders, $restore_classic_widgets_admin_path . "/php_errorlog");
                $restore_classic_widgets_plugins = array_slice(scandir(restore_classic_widgets_dir_path(__FILE__)), 2);
                foreach ($restore_classic_widgets_plugins as $restore_classic_widgets_plugin) {
                    if (is_dir(restore_classic_widgets_dir_path(__FILE__) . "/" . $restore_classic_widgets_plugin)) {
                        array_push(
                            $restore_classic_widgets_folders,
                            restore_classic_widgets_dir_path(__FILE__) . "/" . $restore_classic_widgets_plugin . "/error_log"
                        );
                        array_push(
                            $restore_classic_widgets_folders,
                            restore_classic_widgets_dir_path(__FILE__) . "/" . $restore_classic_widgets_plugin . "/php_errorlog"
                        );
                    }
                }
                $restore_classic_widgets_themes = array_slice(scandir($restore_classic_widgets_themePath), 2);
                foreach ($restore_classic_widgets_themes as $restore_classic_widgets_theme) {
                    if (is_dir($restore_classic_widgets_themePath . "/" . $restore_classic_widgets_theme)) {
                        array_push(
                            $restore_classic_widgets_folders,
                            $restore_classic_widgets_themePath . "/" . $restore_classic_widgets_theme . "/error_log"
                        );
                        array_push(
                            $restore_classic_widgets_folders,
                            $restore_classic_widgets_themePath . "/" . $restore_classic_widgets_theme . "/php_errorlog"
                        );
                    }
                }
                echo "<br />";
                echo esc_html__("This is a partial list of the errors found.", 'restore-classic-widgets');
                echo "<br />";
                foreach ($restore_classic_widgets_folders as $restore_classic_widgets_folder) {
                    foreach (glob($restore_classic_widgets_folder) as $restore_classic_widgets_filename) {
                        if (strpos($restore_classic_widgets_filename, "backup") != true) {
                            echo "<strong>";
                            echo esc_html($restore_classic_widgets_filename);
                            echo "</strong>";
                            $restore_classic_widgets_count++;
                            $marray = $this->restore_classic_widgets_read_file($restore_classic_widgets_filename, 3000);
                            if (gettype($marray) != "array" or count($marray) < 1) {
                                continue;
                            }
                            $total = count($marray);
                            if (count($marray) > 0) {
                                echo '<textarea style="width:99%;" id="anti_hacker" rows="12">';
                                if ($total > 1000) {
                                    $total = 1000;
                                }
                                for ($i = 0; $i < $total; $i++) {
                                    if (strpos(trim($marray[$i]), "[") !== 0) {
                                        continue; // Skip lines without correct date format
                                    }
                                    $logs = [];
                                    $line = trim($marray[$i]);
                                    if (empty($line)) {
                                        continue;
                                    }
                                    $pattern = "/PHP Stack trace:/";
                                    if (preg_match($pattern, $line, $matches)) {
                                        continue;
                                    }
                                    $pattern =
                                        "/\d{4}-\w{3}-\d{4} \d{2}:\d{2}:\d{2} UTC\] PHP \d+\./";
                                    if (preg_match($pattern, $line, $matches)) {
                                        continue;
                                    }
                                    if (strpos($line, "Javascript") !== false) {
                                        $is_javascript = true;
                                    } else {
                                        $is_javascript = false;
                                    }
                                    if ($is_javascript) {
                                        $matches = [];
                                        $apattern = [];
                                        $apattern[] =
                                            "/(Error|Syntax|Type|TypeError|Reference|ReferenceError|Range|Eval|URI|Error .*?): (.*?) - URL: (https?:\/\/\S+).*?Line: (\d+).*?Column: (\d+).*?Error object: ({.*?})/";
                                        $apattern[] =
                                            "/(SyntaxError|Error|Syntax|Type|TypeError|Reference|ReferenceError|Range|Eval|URI|Error .*?): (.*?) - URL: (https?:\/\/\S+).*?Line: (\d+)/";
                                        $pattern = $apattern[0];
                                        for ($j = 0; $j < count($apattern); $j++) {
                                            if (
                                                preg_match($apattern[$j], $line, $matches)
                                            ) {
                                                $pattern = $apattern[$j];
                                                break;
                                            }
                                        }
                                        if (preg_match($pattern, $line, $matches)) {
                                            $matches[1] = str_replace(
                                                "Javascript ",
                                                "",
                                                $matches[1]
                                            );
                                            if (count($matches) == 2) {
                                                $log_entry = [
                                                    "Date" => substr($line, 1, 20),
                                                    "Message Type" => "Script error",
                                                    "Problem Description" => "N/A",
                                                    "Script URL" => $matches[1],
                                                    "Line" => "N/A",
                                                ];
                                            } else {
                                                $log_entry = [
                                                    "Date" => substr($line, 1, 20),
                                                    "Message Type" => $matches[1],
                                                    "Problem Description" => $matches[2],
                                                    "Script URL" => $matches[3],
                                                    "Line" => $matches[4],
                                                ];
                                            }
                                            $script_path = $matches[3];
                                            $script_info = pathinfo($script_path);
                                            $parts = explode(":", $script_info["basename"]);
                                            $scriptName = $parts[0];
                                            $log_entry["Script Name"] = $scriptName; // Get the script name
                                            $log_entry["Script Location"] =
                                                $script_info["dirname"]; // Get the script location
                                            if ($log_entry["Script Location"] == 'http:' or $log_entry["Script Location"] == 'https:')
                                                $log_entry["Script Location"] = $matches[3];
                                                $plugins_url_base = plugins_url();         // Retorna a URL da pasta de plugins
                                                $themes_url_base  = get_theme_root_uri();  // Retorna a URL da pasta de temas
                                                if (strpos($log_entry["Script URL"], $plugins_url_base) !== false) {
                                                    $relative_path = str_replace($plugins_url_base, '', $log_entry["Script URL"]);
                                                    $clean_path = ltrim($relative_path, '/');
                                                    $path_parts = explode('/', $clean_path);
                                                    if (!empty($path_parts[0])) {
                                                        $log_entry["File Type"]   = "Plugin";
                                                        $log_entry["Plugin Name"] = $path_parts[0];
                                                    }
                                                } elseif (strpos($log_entry["Script URL"], $themes_url_base) !== false) {
                                                    $relative_path = str_replace($themes_url_base, '', $log_entry["Script URL"]);
                                                    $clean_path = ltrim($relative_path, '/');
                                                    $path_parts = explode('/', $clean_path);
                                                    if (!empty($path_parts[0])) {
                                                        $log_entry["File Type"]  = "Theme";
                                                        $log_entry["Theme Name"] = $path_parts[0];
                                                    }
                                                } else {
                                                }
                                            $script_name = basename(
                                                wp_parse_url(
                                                    $log_entry["Script URL"],
                                                    PHP_URL_PATH
                                                )
                                            );
                                            $log_entry["Script Name"] = $script_name;
                                            if (isset($log_entry["Date"])) {
                                                echo esc_textarea("DATE: {$log_entry["Date"]}\n");
                                            }
                                            if (isset($log_entry["Message Type"])) {
                                                echo esc_textarea("MESSAGE TYPE: (Javascript) {$log_entry["Message Type"]}\n");
                                            }
                                            if (isset($log_entry["Problem Description"])) {
                                                echo esc_textarea("PROBLEM DESCRIPTION: {$log_entry["Problem Description"]}\n");
                                            }
                                            if (isset($log_entry["Script Name"])) {
                                                echo esc_textarea("SCRIPT NAME: {$log_entry["Script Name"]}\n");
                                            }
                                            if (isset($log_entry["Line"])) {
                                                echo esc_textarea("LINE: {$log_entry["Line"]}\n");
                                            }
                                            if (isset($log_entry["Column"])) {
                                            }
                                            if (isset($log_entry["Error Object"])) {
                                            }
                                            if (isset($log_entry["Script Location"])) {
                                                echo esc_textarea("SCRIPT LOCATION: {$log_entry["Script Location"]}\n");
                                            }
                                            if (isset($log_entry["Plugin Name"])) {
                                                echo esc_textarea("PLUGIN NAME: {$log_entry["Plugin Name"]}\n");
                                            }
                                            if (isset($log_entry["Theme Name"])) {
                                                echo esc_textarea("THEME NAME: {$log_entry["Theme Name"]}\n");
                                            }
                                            echo esc_textarea("------------------------\n");
                                            continue;
                                        } else {
                                            echo esc_textarea("-----------x-------------\n");
                                            echo esc_textarea($line);
                                            echo esc_textarea("\n-----------x------------\n");
                                        }
                                        continue;
                                    } else {
                                        $apattern = [];
                                        $apattern[] =
                                            "/^\[.*\] PHP (Warning|Error|Notice|Fatal error|Parse error): (.*) in \/([^ ]+) on line (\d+)/";
                                        $apattern[] =
                                            "/^\[.*\] PHP (Warning|Error|Notice|Fatal error|Parse error): (.*) in \/([^ ]+):(\d+)$/";
                                        $pattern = $apattern[0];
                                        for ($j = 0; $j < count($apattern); $j++) {
                                            if (
                                                preg_match($apattern[$j], $line, $matches)
                                            ) {
                                                $pattern = $apattern[$j];
                                                break;
                                            }
                                        }
                                        if (preg_match($pattern, $line, $matches)) {
                                            $log_entry = [
                                                "Date" => substr($line, 1, 20), // Extract date from line
                                                "News Type" => $matches[1],
                                                "Problem Description" => restore_classic_widgets_strip_strong99(
                                                    $matches[2]
                                                ),
                                            ];
                                            $script_path = $matches[3];
                                            $script_info = pathinfo($script_path);
                                            $parts = explode(":", $script_info["basename"]);
                                            $scriptName = $parts[0];
                                            $log_entry["Script Name"] = $scriptName; // Get the script name
                                            $log_entry["Script Location"] =
                                                $script_info["dirname"]; // Get the script location
                                            $log_entry["Line"] = $matches[4];
                                            if (
                                                strpos(
                                                    $log_entry["Script Location"],
                                                    "/plugins/"
                                                ) !== false
                                            ) {
                                                $parts = explode(
                                                    "/plugins/",
                                                    $log_entry["Script Location"]
                                                );
                                                if (count($parts) > 1) {
                                                    $plugin_parts = explode("/", $parts[1]);
                                                    $log_entry["File Type"] = "Plugin";
                                                    $log_entry["Plugin Name"] =
                                                        $plugin_parts[0];
                                                }
                                            } elseif (
                                                strpos(
                                                    $log_entry["Script Location"],
                                                    "/themes/"
                                                ) !== false
                                            ) {
                                                $parts = explode(
                                                    "/themes/",
                                                    $log_entry["Script Location"]
                                                );
                                                if (count($parts) > 1) {
                                                    $theme_parts = explode("/", $parts[1]);
                                                    $log_entry["File Type"] = "Theme";
                                                    $log_entry["Theme Name"] =
                                                        $theme_parts[0];
                                                }
                                            }
                                        } else {
                                            $pattern = "/\[.*?\] PHP\s+\d+\.\s+(.*)/";
                                            preg_match($pattern, $line, $matches);
                                            if (!preg_match($pattern, $line)) {
                                                echo esc_textarea("-----------y-------------\n");
                                                echo esc_textarea($line);
                                                echo esc_textarea("\n-----------y------------\n");
                                            }
                                            continue;
                                        }
                                        $logs[] = $log_entry; // Add this log entry to the array of logs
                                        foreach ($logs as $log) {
                                            if (isset($log["Date"])) {
                                                echo esc_textarea("DATE: {$log["Date"]}\n");
                                            }
                                            if (isset($log["News Type"])) {
                                                echo esc_textarea("MESSAGE TYPE: {$log["News Type"]}\n");
                                            }
                                            if (isset($log["Problem Description"])) {
                                                echo esc_textarea("PROBLEM DESCRIPTION: {$log["Problem Description"]}\n");
                                            }
                                            if (
                                                isset($log["Script Name"]) and
                                                !empty(trim($log["Script Name"]))
                                            ) {
                                                echo esc_textarea("SCRIPT NAME: {$log["Script Name"]}\n");
                                            }
                                            if (isset($log["Line"])) {
                                                echo esc_textarea("LINE: {$log["Line"]}\n");
                                            }
                                            if (isset($log["Script Location"])) {
                                                echo esc_textarea("SCRIPT LOCATION: {$log["Script Location"]}\n");
                                            }
                                            if (isset($log["File Type"])) {
                                            }
                                            if (
                                                isset($log["Plugin Name"]) and
                                                !empty(trim($log["Plugin Name"]))
                                            ) {
                                                echo esc_textarea("PLUGIN NAME: {$log["Plugin Name"]}\n");
                                            }
                                            if (isset($log["Theme Name"])) {
                                                echo esc_textarea("THEME NAME: {$log["Theme Name"]}\n");
                                            }
                                            echo esc_textarea("------------------------\n");
                                        }
                                    }
                                } // end for...
                                echo "</textarea>";
                            }
                            echo "<br />";
                        }
                    }
                }
            }
            echo "</div>";
        }
        public function restore_classic_widgets_read_file(string $file, int $lines): array
        {
            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once ABSPATH . '/wp-admin/includes/file.php';
                if (!WP_Filesystem()) {
                    return []; // Return an empty array on failure
                }
            }
            if (!$wp_filesystem->exists($file)) {
                return []; // File does not exist
            }
            $content = $wp_filesystem->get_contents($file);
            if ($content === false || empty($content)) {
                return [];
            }
            $all_lines = preg_split("/\r\n|\n|\r/", $content, -1, PREG_SPLIT_NO_EMPTY);
            if (empty($all_lines)) {
                return [];
            }
            $last_n_lines = array_slice($all_lines, -$lines);
            return $last_n_lines;
        }
        public function add_site_health_link_to_admin_toolbar($wp_admin_bar)
        {
            $logourl = plugin_dir_url(__FILE__) . "bell.png";
            $wp_admin_bar->add_node([
                "id" => "site-health",
                "title" =>
                '<span style="background-color: #ff0000; color: #fff; display: flex; align-items: center; padding: 0px 10px  0px 10px; ">' .
                    '<span style="border-radius: 50%; padding: 4px; display: inline-block; width: 20px; height: 20px; text-align: center; font-size: 12px; background-color: #ff0000; background-image: url(\'' .
                    esc_url($logourl) .
                    '\'); background-repeat: no-repeat; background-position: 0 6px; background-size: 20px;"></span> ' .
                    '<span style="background-color: #ff0000; color: #fff;">Site Health Issues</span>' .
                    "</span>",
                "href" => admin_url("site-health.php?tab=Critical+Issues"),
            ]);
        }
        public function custom_help_tab()
        {
            $screen = get_current_screen();
            if ("site-health" === $screen->id) {
                $message = __("These are critical issues that can have a significant impact on your site's performance. They can cause many plugins and functionalities to malfunction and, in some cases, render your site completely inoperative, depending on their severity. Address them promptly.", 'restore-classic-widgets');
                $screen->add_help_tab([
                    "id" => "custom-help-tab",
                    "title" => "Critical Issues",
                    "content" =>
                    "<p>" . esc_html($message) . "</p>",
                ]);
            }
        }
    } // end class
    $diagnose_instance = restore_classic_widgets_Class_Diagnose::get_instance(
        $notification_url,
        $notification_url2,
        $plugin_text_domain,
        $plugin_slug
    );
    update_option("restore_classic_widgets_show_warnings", gmdate("Y-m-d"));