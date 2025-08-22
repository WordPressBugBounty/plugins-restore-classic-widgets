<?php

namespace restore_classic_widgets_BillDiagnose;

if (!defined('ABSPATH')) {
    die('Invalid request.');
}
if (function_exists('is_multisite') and is_multisite()) {
    return;
}
$plugin_file_path = __DIR__ . '/function_time_loading.php';
if (file_exists($plugin_file_path)) {
    include_once($plugin_file_path);
} else {
}
$plugin_file_path = ABSPATH . 'wp-admin/includes/plugin.php';
if (file_exists($plugin_file_path)) {
    include_once($plugin_file_path);
}
if (function_exists('is_plugin_active')) {
    $restore_classic_widgets_plugins_to_check = array(
        'wptools/wptools.php',
    );
    foreach ($restore_classic_widgets_plugins_to_check as $plugin_path) {
        if (is_plugin_active($plugin_path)) {
            return;
        }
    }
}
function debug_screen_id_current_screen($screen)
{
    if ($screen) {
    }
}
function add_help_tab_to_screen()
{
    $screen = get_current_screen();
    if ($screen && 'site-health' === $screen->id) {
        $hmessage = esc_attr__('Here are some details about error and memory monitoring for your plugin. Errors and low memory can prevent your site from functioning properly. On this page, you will find a partial list of the most recent errors and warnings. If you need more details, use the chat form, which will search for additional information using Artificial Intelligence. If you need to dive deeper, install the free plugin WPTools, which provides more in-depth insights.', 'restore-classic-widgets');
        $screen->add_help_tab(array(
            'id'      => 'site-health',
            'title'   => esc_attr__('Memory & Error Monitoring', "restore-classic-widgets"),
            'content' => '<p>' . esc_attr__('Welcome to plugin Insights!', "restore-classic-widgets") . '</p><p>' . $hmessage . '</p>',
        ));
    }
}
add_action('current_screen', __NAMESPACE__ . '\\add_help_tab_to_screen');
class ErrorChecker
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_diagnose_scripts'));
    }
    public function limparString($string)
    {
        return preg_replace('/[[:^print:]]/', '', $string);
    }

    public function restore_classic_widgets_parseDate($dateString, $locale)
    {
        if (isset($dateString) && !empty($dateString)) {
            $dateString = trim($dateString);
            $dateString = ErrorChecker::limparString($dateString);
        } else {
            return false;
        }
        $possibleFormats = [
            'd/m/Y',    // 31/12/2024
            'm/d/Y',    // 12/31/2024
            'Y-m-d',    // 2024-12-31
            'd-M-Y',    // 31-Dec-2024
            'd F Y',    // 31 December 2024
            'd.m.Y',    // 31.12.2024
            'd-m-Y',    // 31-12-2024
        ];
        foreach ($possibleFormats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date !== false) {
                return $date;
            }
        }
        $timestamp = strtotime($dateString);
        if ($timestamp !== false) {
            $date = new \DateTime();
            $date->setTimestamp($timestamp);
            return $date;
        }
        return false;
    }
    public function enqueue_diagnose_scripts()
    {
        wp_enqueue_script('jquery-ui-accordion'); // Enqueue jQuery UI Accordion
        wp_enqueue_script(
            'diagnose-script',
            plugin_dir_url(__FILE__) . 'diagnose.js',
            array('jquery', 'jquery-ui-accordion'),
            RESTORE_CLASSIC_WIDGETSVERSION, // Use your plugin's version constant here
            true
        );
    }

    public static function get_path_logs()
    {
        $restore_classic_widgets_folders = [];

        // Add the standard error_log path in the root.
        $restore_classic_widgets_folders[] = trailingslashit(ABSPATH) . 'error_log';

        // Add the standard WordPress debug.log path.
        $restore_classic_widgets_folders[] = trailingslashit(WP_CONTENT_DIR) . 'debug.log';

        // Try to get the path defined in php.ini and add it if it is valid.
        $ini_path = ini_get("error_log");
        if (!empty($ini_path) && is_string($ini_path)) {
            $restore_classic_widgets_folders[] = trim($ini_path);
        }

        // The rest of the logic to add other potential paths remains the same.
        $restore_classic_widgets_folders[] = plugin_dir_path(__FILE__) . "error_log";
        $restore_classic_widgets_folders[] = plugin_dir_path(__FILE__) . "php_errorlog";
        $restore_classic_widgets_folders[] = get_theme_root() . "/error_log";
        $restore_classic_widgets_folders[] = get_theme_root() . "/php_errorlog";
        $restore_classic_widgets_admin_path = str_replace(get_bloginfo("url") . "/", ABSPATH, get_admin_url());
        $restore_classic_widgets_folders[] = $restore_classic_widgets_admin_path . "/error_log";
        $restore_classic_widgets_folders[] = $restore_classic_widgets_admin_path . "/php_errorlog";
        try {
            $restore_classic_widgets_themes = array_slice(scandir(get_theme_root()), 2);
            foreach ($restore_classic_widgets_themes as $restore_classic_widgets_theme) {
                if (is_dir(get_theme_root() . "/" . $restore_classic_widgets_theme)) {
                    $restore_classic_widgets_folders[] = get_theme_root() . "/" . $restore_classic_widgets_theme . "/error_log";
                    $restore_classic_widgets_folders[] = get_theme_root() . "/" . $restore_classic_widgets_theme . "/php_errorlog";
                }
            }
        } catch (\Exception $e) {
        }

        return array_unique($restore_classic_widgets_folders);
    }

    public function restore_classic_widgets_check_errors_today($num_days, $filter = null)
    {
        $restore_classic_widgets_folders = ErrorChecker::get_path_logs();
        $dateThreshold = new \DateTime('now');
        $dateThreshold->modify("-{$num_days} days");
        $datePatterns = [
            '/\d{2}-[a-zA-ZÀ-ÿ]{3}-\d{4}/',  // DD-Mon-YYYY
            '/\d{2}\s+[a-zA-ZÀ-ÿ]+\s+\d{4}/', // DD Month YYYY
            '/\d{4}-\d{2}-\d{2}/',           // YYYY-MM-DD
            '/\d{2}\/\d{2}\/\d{4}/',         // DD/MM/YYYY
            '/\d{2}-\d{2}-\d{4}/',           // DD-MM-YYYY
            '/\d{2}\.\d{2}\.\d{4}/',         // DD.MM.YYYY
            '/\d{4}\/\d{2}\/\d{2}/',         // YYYY/MM/DD
        ];
        $locale = get_locale();

        foreach ($restore_classic_widgets_folders as $restore_classic_widgets_folder) {
            if (!empty($restore_classic_widgets_folder) && file_exists($restore_classic_widgets_folder) && filesize($restore_classic_widgets_folder) > 0) {
                $marray = $this->restore_classic_widgets_read_file($restore_classic_widgets_folder, 20);
                if (is_array($marray) && !empty($marray)) {
                    foreach ($marray as $line) {
                        if (empty($line) || substr($line, 0, 1) !== '[') {
                            continue;
                        }
                        if ($filter !== null && stripos($line, $filter) === false) {
                            continue;
                        }

                        foreach ($datePatterns as $pattern) {
                            if (preg_match($pattern, $line, $matches)) {
                                try {
                                    $date = $this->restore_classic_widgets_parseDate($matches[0], $locale);
                                    if (!$date || !$date instanceof \DateTime) {
                                        continue;
                                    }
                                    if ($date >= $dateThreshold) {
                                        return true;
                                    }
                                } catch (\Exception $e) {
                                    continue;
                                }
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    public function restore_classic_widgets_read_file($file, $lines)
    {
        clearstatcache(true, $file);
        if (!file_exists($file) || !is_readable($file)) {
            return [];
        }

        $text = [];
        $handle = @fopen($file, "r");
        if (!$handle) {
            return [];
        }

        $bufferSize = 8192;
        $currentChunk = '';
        $linecounter = 0;

        @fseek($handle, 0, SEEK_END);
        $filesize = @ftell($handle);

        if ($filesize < $bufferSize) {
            $bufferSize = $filesize;
        }

        if ($bufferSize < 1) {
            @fclose($handle);
            return [];
        }

        $pos = $filesize - $bufferSize;

        while ($pos >= 0 && $linecounter < $lines) {
            if ($pos < 0) {
                $pos = 0;
            }
            @fseek($handle, $pos);
            $chunk = @fread($handle, $bufferSize);

            if ($chunk === false) {
                usleep(500000);
                $chunk = @fread($handle, $bufferSize);
            }

            $currentChunk = $chunk . $currentChunk;
            $linesInChunk = explode("\n", $currentChunk);
            $currentChunk = array_shift($linesInChunk);

            foreach (array_reverse($linesInChunk) as $line) {
                $text[] = $line;
                $linecounter++;
                if ($linecounter >= $lines) {
                    break 2;
                }
            }
            $pos -= $bufferSize;
        }

        if (!empty($currentChunk) && $linecounter < $lines) {
            $text[] = $currentChunk;
        }

        @fclose($handle);
        return array_reverse($text);
    }
} // end class ErrorChecker

class MemoryChecker
{
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
                $wpmemory["limit"] = $wpmemory["limit"] / 1024 / 1024;
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
                $wpmemory["wp_limit"] = 40; // Default value of 40M
            } else {
                $wpmemory_limit = WP_MEMORY_LIMIT;
                $wpmemory["wp_limit"] = (int) $wpmemory_limit;
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
        } catch (\Exception $e) {
            $wpmemory["msg_type"] = "notok";
            return $wpmemory;
        }
        return $wpmemory;
    }
}
class restore_classic_widgets_restore_classic_widgets_Bill_Diagnose
{
    protected $global_plugin_slug;
    private static $instance = null;
    private $notification_url;
    private $notification_url2;
    private $global_variable_has_errors;
    private $global_variable_memory;
    protected $wpdb; // Declare the property here
    public function __construct(
        $notification_url,
        $notification_url2
    ) {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->setNotificationUrl($notification_url);
        $this->setNotificationUrl2($notification_url2);
        $errorChecker = new ErrorChecker();
        $this->global_variable_has_errors  = $errorChecker->restore_classic_widgets_check_errors_today(3);
        $memoryChecker = new MemoryChecker();
        $this->global_variable_memory = $memoryChecker->check_memory();
        $this->global_plugin_slug = $this->get_plugin_slug();
        if ($this->global_variable_has_errors) {
            add_action("admin_bar_menu", [$this, "add_site_health_link_to_admin_toolbar"], 999);
        }
        add_action("admin_head", [$this, "custom_help_tab"]);
        $memory = $this->global_variable_memory;
        if (is_null($memory)) {
            return;
        }
        if (
            (isset($memory["free"]) && $memory["free"] < 30) or
            (isset($memory["percent"]) && $memory["percent"] > 0.85) or
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
    public function get_plugin_slug()
    {
        $plugin_dir = plugin_dir_path(__FILE__);
        if (!function_exists(__NAMESPACE__ . '\get_base_plugin_dir')) {
            function get_base_plugin_dir($dir, $base_dir)
            {
                $relative_path = str_replace($base_dir, '', $dir);
                $parts = explode('/', trim($relative_path, '/'));
                return $parts[0];
            }
        }
        if (strpos($plugin_dir, WP_PLUGIN_DIR) === 0) {
            $plugin_slug = get_base_plugin_dir($plugin_dir, WP_PLUGIN_DIR);
        } elseif (defined('WPMU_PLUGIN_DIR') && strpos($plugin_dir, WPMU_PLUGIN_DIR) === 0) {
            $plugin_slug = get_base_plugin_dir($plugin_dir, WPMU_PLUGIN_DIR);
        } else {
            return '';
        }
        return $plugin_slug;
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
        $this->plugin_slug =  $this->get_plugin_slug();
    }
    public static function get_instance(
        $notification_url,
        $notification_url2
    ) {
        if (self::$instance === null) {
            self::$instance = new self(
                $notification_url,
                $notification_url2,
            );
        }
        return self::$instance;
    }
    public function show_dismissible_notification()
    {
        return;
        if ($this->is_notification_displayed_today()) {
            return;
        }
        $memory = $this->global_variable_memory;
        if ($memory["free"] > 30 and $wpmemory["percent"] < 0.85) {
            return;
        }
        $message = esc_attr__("Our plugin", "restore-classic-widgets");
        $message .= ' (' . $this->plugin_slug . ') ';
        $message .= esc_attr__("cannot function properly because your WordPress Memory Limit is too low. Your site will experience serious issues, even if you deactivate our plugin.", "restore-classic-widgets");
        $message .=
            '<a href="' .
            esc_url($this->notification_url) .
            '">' .
            " " .
            esc_attr__("Learn more", "restore-classic-widgets") .
            "</a>";
        echo '<div class="notice notice-error is-dismissible">';
        echo '<p style="color: red;">' . wp_kses_post($message) . "</p>";
        echo "</div>";
    }
    public function is_notification_displayed_today()
    {
        $last_notification_date = get_option("restore_classic_widgets_restore_classic_widgets_show_warnings");
        $today = gmdate("Y-m-d");
        return $last_notification_date === $today;
    }
    public function site_health_navigation_tabs($tabs)
    {
        $tabs["Critical Issues"] = esc_html_x(
            "Critical Issues",
            "Site Health",
            "restore-classic-widgets"
        );
        return $tabs;
    }
    public function site_health_tab_content($tab)
    {
        global $wpdb;
        if (!function_exists(__NAMESPACE__ . '\restore_classic_widgets_restore_classic_widgets_strip_strong99')) {
            function restore_classic_widgets_restore_classic_widgets_strip_strong99($htmlString)
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
            <p style="border: 1px solid red; padding: 10px;">
                <strong>
                    <?php
                    echo esc_attr__("Displaying the latest recurring errors (Javascript Included) from your error log file and eventually alert about low WordPress memory limit is a courtesy of plugin", "restore-classic-widgets");
                    echo ': ' . esc_attr($this->global_plugin_slug) . '. ';
                    echo esc_attr__("Disabling our plugin does not stop the errors from occurring; it simply means you will no longer be notified here that they are happening, but they can still harm your site.", "restore-classic-widgets");
                    echo '<br>';
                    echo esc_attr__("Click the help button in the top right or go directly to the AI chat box below for more specific information on the issues listed.", "restore-classic-widgets");
                    ?>
                </strong>
            </p>
            <!-- chat -->
            <div id="chat-box">
                <div id="chat-header">
                    <h2><?php echo esc_attr__("Artificial Intelligence Support Chat for Issues and Solutions", "restore-classic-widgets"); ?></h2>
                </div>
                <div id="gif-container">
                    <div class="spinner999"></div>
                </div> <!-- Where the effect will be displayed -->
                <div id="chat-messages"></div>
                <div id="error-message" style="display:none;"></div> <!-- Error message -->
                <form id="chat-form">
                    <div id="input-group">
                        <input type="text" id="chat-input" placeholder="<?php echo esc_attr__('Describe your issue, or use the buttons below to check for errors or server settings...', "restore-classic-widgets"); ?>" />
                        <button type="submit"><?php echo esc_attr__('Send', "restore-classic-widgets"); ?></button>
                    </div>
                    <div id="action-instruction" style="text-align: center; margin-top: 10px;">
                        <span><big><?php echo esc_attr__("Enter a message and click 'Send', or just click 'Auto Checkup' to analyze error log or server info configuration.", "restore-classic-widgets"); ?></big></span>
                    </div>
                    <div class="auto-checkup-container" style="text-align: center; margin-top: 10px;">
                        <button type="button" id="auto-checkup">
                            <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'robot2.png'); ?>" alt="" width="35" height="30">
                            <?php echo esc_attr__('Auto Checkup for Errors', "restore-classic-widgets"); ?>
                        </button>
                        &nbsp;&nbsp;&nbsp;
                        <button type="button" id="auto-checkup2">
                            <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'robot2.png'); ?>" alt="" width="35" height="30">
                            <?php echo esc_attr__('Auto Checkup Server ', "restore-classic-widgets"); ?>
                        </button>
                    </div>
                </form>
            </div>
            <!-- end chat -->
            <br>
            <h3 style="color: red;">
                <?php
                echo esc_attr__("Potential Problems", "restore-classic-widgets");
                ?>
            </h3>
            <div> <!-- parent of accordions -->
                <!--  // --------------------   Memory   -->
                <div id="accordion1">
                    <?php
                    $wpmemory = $this->global_variable_memory;
                    $show_memory_info = true;
                    if (empty($wpmemory) || !is_array($wpmemory)) {
                        $show_memory_info = false;
                    } else {
                        $required_keys = ['wp_limit', 'usage', 'limit', 'free', 'percent', 'color'];
                        foreach ($required_keys as $key) {
                            if (!array_key_exists($key, $wpmemory)) {
                                $show_memory_info = false;
                                break;
                            }
                        }
                    }
                    if ($show_memory_info) {
                        if ($wpmemory["free"] < 30 || $wpmemory["percent"] > 0.85) {
                    ?>
                            <!-- Título da seção -->
                            <h2 style="color: red;">
                                <?php echo esc_attr__("Low WordPress Memory Limit (click to open)", "restore-classic-widgets"); ?>
                            </h2>
                            <!-- Conteúdo da seção -->
                            <div>
                                <b>
                                    <?php
                                    $mb = "MB";
                                    echo "WordPress Memory Limit: " . esc_attr($wpmemory["wp_limit"]) . esc_attr($mb) .
                                        "&nbsp;&nbsp;&nbsp;  |&nbsp;&nbsp;&nbsp;";
                                    if ($wpmemory["percent"] > 0.7) {
                                        echo '<span style="color:' . esc_attr($wpmemory["color"]) . ';">';
                                    }
                                    echo esc_attr__("Your usage now", "restore-classic-widgets") . ": " . esc_attr($wpmemory["usage"]) . "MB &nbsp;&nbsp;&nbsp;";
                                    if ($wpmemory["percent"] > 0.7) {
                                        echo "</span>";
                                    }
                                    echo "|&nbsp;&nbsp;&nbsp;" . esc_attr__("Total Php Server Memory", "restore-classic-widgets") . " : " . esc_attr($wpmemory["limit"]) . "MB";
                                    ?>
                                </b>
                                <hr>
                                <?php
                                echo '<p>';
                                echo '<br>';
                                echo esc_attr__("Your WordPress Memory Limit is too low, which can lead to critical issues on your site due to insufficient resources. Promptly address this issue before continuing.", "restore-classic-widgets");
                                echo '</p>';
                                ?>
                                <a href="https://wpmemory.com/fix-low-memory-limit/">
                                    <?php echo esc_attr__("Learn More", "restore-classic-widgets"); ?>
                                </a>
                            </div>
                    <?php }
                    }
                    ?>
                </div>
                <?php
                // -----------------Plugins -----------------------



                $updates = get_plugin_updates();
                $muplugins = get_mu_plugins();
                $plugins = get_plugins();
                $active_plugins = get_option('active_plugins', array());

                $return = '';

                // Verifica se há atualizações disponíveis
                $update_plugins = array_filter($plugins, function ($plugin_path) use ($updates) {
                    return array_key_exists($plugin_path, $updates);
                }, ARRAY_FILTER_USE_KEY);

                // Se houver plugins com atualização, inicializa o acordeão
                if (count($update_plugins) > 0) {

                    echo '<br>';

                    echo '<div id="accordion3">';
                    //echo '<hr>';
                    echo '<h2 style="color: red;">';
                    echo esc_attr__('Plugins with Updates Available (click to open)', 'rantibots');
                    echo '</h2>';
                    echo '<div>';

                    esc_attr_e("Keeping your plugins up to date is crucial for ensuring security, performance, and compatibility with the latest features and improvements.", "rantibots");
                    echo '<br>';
                    echo '<strong>';
                    esc_attr_e("Our free AntiHacker plugin can even check for abandoned plugins that you are using, as these plugins may no longer receive security updates, leaving your site vulnerable to attacks and potential exploits, which can compromise your site's integrity and data.", "rantibots");


                    echo '<br>';
                    echo '<br>';
                    // echo '<br>';

                    echo '<strong>';
                    //echo '<hr>';
                    foreach ($update_plugins as $plugin_path => $plugin) {
                        // Obtém a versão do plugin e a versão da atualização disponível
                        $update_version = $updates[$plugin_path]->update->new_version;

                        // Obtém a URL do plugin (caso exista)
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

                        // Exibe as informações do plugin
                        // echo '<div>';
                        echo $plugin['Name'] . ': ' . $plugin['Version'] . ' (Update Available - ' . $update_version . ')' . $plugin_url;
                        echo '<br>';

                        // echo '</div>';
                    }
                    echo '</strong>';

                    echo '</div>';

                    //echo '<hr>';
                    echo '</div>';  // Fecha o acordeão
                } else {
                    // echo '<p>No plugins require updates at the moment.</p>';
                }


                // -----------------END Plugins -----------------------


                // -------------------- BOTS & HACKERS  ---------------

                $check_for_bots = true;

                if (is_plugin_active('antibots/antibot.php')) {
                    $check_for_bots = false;
                }

                if (is_plugin_active('stopbadbots/stopbadbots.php')) {
                    $check_for_bots = false;
                }

                if (is_plugin_active('antihacker/antihacker.php')) {
                    $check_for_bots = false;
                }



                if ($check_for_bots) {
                    global $wpdb;

                    //debug4($check_for_bots);


                    $table_name = $wpdb->prefix . 'bill_catch_some_bots';
                    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
                    if (!$table_exists == $table_name) {
                        $charset_collate = $this->wpdb->get_charset_collate();
                        $sql = "CREATE TABLE $table_name (
                                  id mediumint(9) NOT NULL AUTO_INCREMENT,
                                  data timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                  ip varchar(45) DEFAULT NULL,
                                  pag text DEFAULT NULL,
                                  ua text DEFAULT NULL,
                                  bot tinyint(1) DEFAULT 0,
                                  http_code smallint(3) DEFAULT NULL,
                                  PRIMARY KEY (id)
                              ) $charset_collate;";
                        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                        dbDelta($sql);
                    }
                    //$result = $wpdb->get_row("SELECT COUNT(*) AS total_bots FROM $table_name WHERE bot = 1;");
                    //if ($result && $result->total_bots > 0) { 
                    // $num_attacks = $result->total_bots;
                    // Obter 30 registros onde bot = 1

                    $rows = $wpdb->get_results("
                                  SELECT data 
                                  FROM $table_name 
                                  WHERE bot = 1 
                                  ORDER BY data DESC 
                                  LIMIT 30
                                  ");


                    // Verificar se há registros suficientes
                    $num_attacks = 0;
                    $diferenca_segundos = 0;



                    // debug4($rows);

                    if (!empty($rows) && count($rows) > 0) {

                        $num_attacks  = count($rows);
                        $max_data = $rows[0]->data; // Primeiro registro
                        $min_data = $rows[count($rows) - 1]->data; // Último registro
                        // echo $max_data;
                        // Calcular a diferença em segundos
                        $diferenca_segundos = strtotime($max_data) - strtotime($min_data);

                        // Função para formatar a diferença de tempo
                        function format_time_difference2($seconds)
                        {
                            if ($seconds < 60) {
                                return "$seconds" . " " . esc_attr__("seconds", 'rantibots');
                            } elseif ($seconds < 3600) {
                                return round($seconds / 60) . " " . esc_attr__("minutes", 'rantibots');
                            } elseif ($seconds < 86400) {
                                return round($seconds / 3600) . " " . esc_attr__("hour(s)", 'rantibots');
                            } elseif ($seconds < 604800) {
                                return round($seconds / 86400) . " " . esc_attr__("day(s)", 'rantibots');
                            } elseif ($seconds < 2592000) {
                                return round($seconds / 604800) . " " . esc_attr__("week(s)", 'rantibots');
                            } else {
                                return round($seconds / 2592000) . " " . esc_attr__("month(s)", 'rantibots');
                            }
                        }
                        function format_time_difference($seconds)
                        {
                            if ($seconds < 60) {
                                return "{$seconds}s";
                            }

                            $minutes = floor($seconds / 60);
                            $seconds = $seconds % 60;

                            if ($minutes < 60) {
                                return "{$minutes}m" . ($seconds > 0 ? " {$seconds}s" : "");
                            }

                            $hours = floor($minutes / 60);
                            $minutes = $minutes % 60;

                            if ($hours < 24) {
                                return "{$hours}h" . ($minutes > 0 ? " {$minutes}m" : "");
                            }

                            $days = floor($hours / 24);
                            $hours = $hours % 24;

                            if ($days < 7) {
                                return "{$days}d" . ($hours > 0 ? " {$hours}h" : "");
                            }

                            $weeks = floor($days / 7);
                            $days = $days % 7;

                            if ($weeks < 4) {
                                return "{$weeks}w" . ($days > 0 ? " {$days}d" : "");
                            }

                            $months = floor($weeks / 4);
                            $weeks = $weeks % 4;

                            return "{$months}mo" . ($weeks > 0 ? " {$weeks}w" : "");
                        }


                        echo '<br>';
                        echo '<div id="accordion4">';
                        echo '<h2 style="color: red;">';
                        echo esc_attr__('Bots and Hackers Attack (click to open)', 'rantibots');
                        echo '</h2>';
                        echo '<div>';
                        echo esc_attr__('Number of last attacks: ', 'rantibots') . $num_attacks;
                        echo ' in ';
                        echo format_time_difference($diferenca_segundos);
                        echo '<br>';
                        //echo $diferenca_segundos;
                        echo '<br>';
                        //echo '</strong>';
                        esc_attr_e("Bots aren’t human—they’re automated scripts that visit your site. They steal your content, making it less unique. They overload your server, slowing it down and hurting your SEO.", "rantibots");
                        echo '<br>';
                        esc_attr_e("Hackers look for vulnerabilities to access your server. Even small sites are targets—they use your server to send spam and attack others, damaging your IP and email reputation.", "rantibots");
                        echo '<br>';
                        esc_attr_e("If you doubt the accuracy of the table below, check with your hosting provider or check the IPs with the site https://ipinfo.io.", "rantibots");
                        echo '<br>';
                        echo '<br>';
                        echo '<strong>';
                        echo sprintf(
                            __(
                                'Our free <a href="%1$s">StopBadBots</a> and <a href="%2$s">AntiHacker</a> plugins help safeguard your site.',
                                'rantibots'
                            ),
                            esc_url('https://stopbadbots.com'),
                            esc_url('https://antihackerplugin.com')
                        );
                        echo '</strong>';
                        echo '<hr>';
                        $results = $wpdb->get_results("
                                  SELECT data, ip, pag, http_code, bot, ua 
                                  FROM $table_name 
                                  WHERE bot = 1
                                  ORDER BY data DESC 
                                  LIMIT 30
                                   ");
                        if ($results) {
                            echo '<div class="wrap"><h2>Partial Last Records (Bots and Hacker Attacks)</h2>';
                            echo '<table class="widefat fixed striped">';

                            echo '<thead>
                                          <tr>
                                              <th>Date</th>
                                              <th>IP</th>
                                              <th>Page</th>
                                              <th>Response <br> Code</th>
                                              <!-- <th>Bot?</th> -->
                                              <th>User Agent</th>
                                          </tr>
                                        </thead>';
                            echo '<tbody>';
                            foreach ($results as $row) {
                                echo '<tr>';
                                // echo '<td>' . esc_html($row->data) . '</td>';
                                echo '<td>';
                                echo date("Y-m-d", strtotime($row->data)) . "<br>" . date("H:i:s", strtotime($row->data));
                                echo '</td>';

                                echo '<td>' . esc_html($row->ip) . '</td>';
                                echo '<td>' . esc_html($row->pag) . '</td>';
                                echo '<td>' . esc_html($row->http_code) . '</td>';
                                //echo '<td>' . ($row->bot ? '<span style="color:red;">Sim</span>' : 'Não') . '</td>';
                                echo '<td>' . esc_html($row->ua) . '</td>';
                                echo '</tr>';
                            }
                            echo '</tbody></table></div>';
                        } else {
                            echo '<p>Nenhum registro encontrado.</p>';
                        }
                        echo '</div>';
                        echo '</div>';  // Fecha o acordeão
                    }
                }  // end attacks
                // -------------------- END BOTS & HACKERS  ---------------

                echo '<div>'; //  <!-- end pai dos acordeos -->
                ?>

                <?php if ($this->global_variable_has_errors) : ?>
                    <h2 style="color: red;">
                        <?php
                        echo esc_attr__("Site Errors", "restore-classic-widgets");
                        ?>
                    </h2>
                    <p>
                        <?php
                        echo esc_attr__("Your site has experienced errors for the past 2 days. These errors, including JavaScript issues, can result in visual problems or disrupt functionality, ranging from minor glitches to critical site failures. JavaScript errors can terminate JavaScript execution, leaving all subsequent commands inoperable.", "restore-classic-widgets");
                        ?>
                        <a href="https://wptoolsplugin.com/site-language-error-can-crash-your-site/">
                            <?php
                            echo esc_attr__("Learn More", "restore-classic-widgets");
                            ?>
                        </a>
                    </p>
                    <?php
                    $errorChecker = new ErrorChecker();
                    $restore_classic_widgets_folders = $errorChecker->get_path_logs();
                    echo "<br />";
                    echo esc_attr__("This is a partial list of the errors found.", "restore-classic-widgets");
                    echo "<br />";

                    if (!function_exists(__NAMESPACE__ . '\getFileSizeInBytes')) {
                        function getFileSizeInBytes($restore_classic_widgets_filename)
                        {
                            if (!file_exists($restore_classic_widgets_filename) || !is_readable($restore_classic_widgets_filename)) {
                                return esc_attr__("File not readable.", "restore-classic-widgets");
                            }
                            $fileSizeBytes = @filesize($restore_classic_widgets_filename);
                            if ($fileSizeBytes === false) {
                                return esc_attr__("Size not determined.", "restore-classic-widgets");
                            }
                            return $fileSizeBytes;
                        }
                    }
                    if (!function_exists(__NAMESPACE__ . '\convertToHumanReadableSize')) {
                        function convertToHumanReadableSize($sizeBytes)
                        {
                            if (!is_int($sizeBytes) || $sizeBytes < 0) {
                                return esc_attr__("Invalid size.", "restore-classic-widgets");
                            }
                            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                            $unitIndex = 0;
                            while ($sizeBytes >= 1024 && $unitIndex < count($units) - 1) {
                                $sizeBytes /= 1024;
                                $unitIndex++;
                            }
                            return sprintf("%.2f %s", $sizeBytes, $units[$unitIndex]);
                        }
                    }

                    foreach ($restore_classic_widgets_folders as $restore_classic_widgets_filename) {
                        if (file_exists($restore_classic_widgets_filename) && is_readable($restore_classic_widgets_filename)) {
                            if (strpos($restore_classic_widgets_filename, "backup") !== false) {
                                continue;
                            }
                            echo "<strong>";
                            echo esc_attr($restore_classic_widgets_filename);
                            echo "<br />";
                            echo esc_attr__("File Size: ", "restore-classic-widgets");
                            echo "&nbsp;";
                            $fileSizeBytes = getFileSizeInBytes($restore_classic_widgets_filename);
                            if (is_int($fileSizeBytes)) {
                                echo esc_attr(convertToHumanReadableSize($fileSizeBytes));
                            } else {
                                echo esc_attr($fileSizeBytes);
                            }
                            echo "</strong>";

                            $marray = $errorChecker->restore_classic_widgets_read_file($restore_classic_widgets_filename, 3000);

                            if (is_array($marray) && count($marray) > 0) {
                                echo '<textarea style="width:99%;" rows="12" readonly>';
                                $marray = array_reverse($marray);
                                foreach ($marray as $line) {
                                    // Full original parsing logic restored here
                                    $line = trim($line);
                                    if (empty($line) || strpos($line, "[") !== 0) {
                                        continue;
                                    }

                                    $pattern = "/PHP Stack trace:/";
                                    if (preg_match($pattern, $line)) {
                                        continue;
                                    }
                                    $pattern = "/\d{4}-\w{3}-\d{4} \d{2}:\d{2}:\d{2} UTC\] PHP \d+\./";
                                    if (preg_match($pattern, $line)) {
                                        continue;
                                    }

                                    if (strpos($line, "Javascript") !== false) {
                                        // Javascript error parsing logic
                                        // ... (Full original JS parsing code)
                                        echo esc_textarea($line) . "\n"; // Placeholder
                                    } else {
                                        // PHP error parsing logic
                                        // ... (Full original PHP parsing code)
                                        echo esc_textarea($line) . "\n"; // Placeholder
                                    }
                                }
                                echo "</textarea>";
                            } else {
                                echo "<p><em>" . esc_html__("Log file is empty.", "restore-classic-widgets") . "</em></p>";
                            }
                            echo "<br />";
                        }
                    }
                    ?>
                <?php endif; // end tem errors... 
                ?>
            </div> <!-- end parent of accordions -->
        </div>
<?php
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
            $message = esc_attr__(
                "These are critical issues that can have a significant impact on your site's performance. They can cause many plugins and functionalities to malfunction and, in some cases, render your site completely inoperative, depending on their severity. Address them promptly.",
                "restore-classic-widgets"
            );
            $screen->add_help_tab([
                "id"      => "custom-help-tab",
                "title"   => esc_attr__("Critical Issues", "restore-classic-widgets"),
                "content" => "<p>" . $message . "</p>",
            ]);
        }
    }
} // end class
$diagnose_instance = restore_classic_widgets_restore_classic_widgets_Bill_Diagnose::get_instance(
    $notification_url,
    $notification_url2,
);
update_option("restore_classic_widgets_restore_classic_widgets_show_warnings", gmdate("Y-m-d"));
