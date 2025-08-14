<?php
namespace  restore_classic_widgets {
    if (!defined("ABSPATH")) {
        exit(); // Exit if accessed directly
    }
    $restore_classic_widgets_debug = true;
    $restore_classic_widgets_debug = false;
    if (function_exists('is_multisite') and is_multisite()) {
        return;
    }
    if (__NAMESPACE__ == "HideSiteTitle") {
        define(__NAMESPACE__ . "\PRODCLASS", "stopbadbots");
        define(__NAMESPACE__ . "\VERSION", HIDE_SITE_TITLE_VERSION);
        define(__NAMESPACE__ . "\PRODUCTNAME", "Hide Site Title Plugin");
        $admin_url = admin_url('tools.php?page=stopbadbots&active_tab=3');
        define(__NAMESPACE__ . "\PAGE", $admin_url);
        define(__NAMESPACE__ . "\OPTIN", "wp_tools_optin");
        define(__NAMESPACE__ . "\LAST", "wp_tools_last_feedback");
        define(__NAMESPACE__ . "\URL", HIDE_SITE_TITLE_URL);
    }
    if (__NAMESPACE__ == "StopBadBots") {
        define(__NAMESPACE__ . "\PRODCLASS", "stopbadbots");
        define(__NAMESPACE__ . "\VERSION", STOPBADBOTSVERSION);
        define(__NAMESPACE__ . "\PRODUCTNAME", "Stop Bad Bots Plugin");
        $admin_url = admin_url('admin.php?page=stop_bad_bots_plugin&tab=more');
        define(__NAMESPACE__ . "\PAGE", $admin_url);
        define(__NAMESPACE__ . "\URL", STOPBADBOTSURL);
        define(__NAMESPACE__ . "\LAST", "stopbadbots_last_feedback");
    }
    if (__NAMESPACE__ == "RecaptchaForAll_last_feedback") {
        define(__NAMESPACE__ . "\PRODCLASS", "recaptcha-for-all");
        define(__NAMESPACE__ . "\VERSION", RECAPTCHA_FOR_ALLVERSION);
        define(__NAMESPACE__ . "\PRODUCTNAME", "Recaptcha For All Plugin");
        $admin_url = admin_url('tools.php?page=recaptcha_for_all_admin_page&tab=tools');
        define(__NAMESPACE__ . "\PAGE", $admin_url);
        define(__NAMESPACE__ . "\URL", RECAPTCHA_FOR_ALLURL);
        define(__NAMESPACE__ . "\LAST", "recaptcha_for_all_last_feedback");
    }
    if (__NAMESPACE__ == "restore_classic_widgets") {
        define(__NAMESPACE__ . "\PRODCLASS", "restore-classic-widgets");
        define(__NAMESPACE__ . "\VERSION", RESTORE_CLASSIC_WIDGETSVERSION);
        define(__NAMESPACE__ . "\PRODUCTNAME", "Restore Classic Widgets Plugin");
        $admin_url = admin_url('tools.php?page=restore_classic_widgets_new_more_plugins');
        define(__NAMESPACE__ . "\PAGE", $admin_url);
        define(__NAMESPACE__ . "\URL", RESTORE_CLASSIC_WIDGETSURL);
        define(__NAMESPACE__ . "\LAST", "restore_classic_widget_last_feedback");
    }
    if (__NAMESPACE__ == "CarDealer_last_feedback") {
        define(__NAMESPACE__ . "\PRODCLASS", "cardealer");
        define(__NAMESPACE__ . "\VERSION", CARDEALERVERSION);
        define(__NAMESPACE__ . "\PRODUCTNAME", "Car Dealer Plugin");
        $admin_url = admin_url('admin.php?page=car_dealer_plugin&tab=tools&customize_changeset_uuid=');
        define(__NAMESPACE__ . "\PAGE", $admin_url);
        define(__NAMESPACE__ . "\URL", CARDEALERURL);
        define(__NAMESPACE__ . "\LAST", "cardealer_last_feedback");
    }
    if (__NAMESPACE__ == "wpmemory_last_feedback") {
        define(__NAMESPACE__ . "\PRODCLASS", "wp_memory");
        define(__NAMESPACE__ . "\VERSION", WPMEMORYVERSION);
        define(__NAMESPACE__ . "\PLUGINHOME", "https://wpmemory.com");
        define(__NAMESPACE__ . "\PRODUCTNAME", "WP Memory Plugin");
        define(__NAMESPACE__ . "\LANGUAGE", "wp-memory");
        define(__NAMESPACE__ . "\PAGE", "settings");
        define(__NAMESPACE__ . "\OPTIN", "wp_memor_optin");
        define(__NAMESPACE__ . "\LAST", "wp_memory_last_feedback");
        define(__NAMESPACE__ . "\URL", WPMEMORYURL);
    }
    if (__NAMESPACE__ == "wptools_last_feedback") {
        define(__NAMESPACE__ . "\PRODCLASS", "wptools");
        define(__NAMESPACE__ . "\VERSION", WPTOOLSVERSION);
        define(__NAMESPACE__ . "\PRODUCTNAME", "WP Tools Plugin");
        define(__NAMESPACE__ . "\LANGUAGE", "wptools");
        define(__NAMESPACE__ . "\PAGE", "settings");
        define(__NAMESPACE__ . "\OPTIN", "wp_tools_optin");
        define(__NAMESPACE__ . "\LAST", "wp_tools_last_feedback");
        define(__NAMESPACE__ . "\URL", WPTOOLSURL);
    }
    if ($restore_classic_widgets_debug)
        update_option(LAST, '1');
    $last_feedback =  sanitize_text_field(get_option(LAST, "1"));
    $last_feedback =  intval($last_feedback);
    if ($last_feedback === '0' || !is_numeric($last_feedback)) {
        $last_feedback = time() - (2 * 24 * 3600); // 2 days ago in seconds
    } else {
    }
    if ($last_feedback < 2) {
        $delta = 0;
        $last_feedback = time();
    } else {
        $delta = (1 * 24 * 3600);
    }
    if ($last_feedback + $delta <= time()) {
        define(__NAMESPACE__ . "\WPMSHOW", true);
    } else {
        define(__NAMESPACE__ . "\WPMSHOW", false);
        return;
    }
    class restore_classic_widgets_mConfig
    {
        protected static $namespace = __NAMESPACE__;
        protected static $restore_classic_widgets_plugin_url = URL;
        protected static $restore_classic_widgets_class = PRODCLASS;
        protected static $restore_classic_widgets_prod_veersion = VERSION;
        protected static $plugin_slug;
        function __construct()
        {
            add_action("load-plugins.php", [__CLASS__, "init"]);
            add_action("wp_ajax_restore_classic_widgets_feedback", [__CLASS__, "feedback"]);
        }
        public static function get_plugin_slug()
        {
            if (isset(self::$plugin_slug)) {
                return self::$plugin_slug;
            }
            $plugin_dir = restore_classic_widgets_dir_path(__FILE__);
            if (strpos($plugin_dir, WP_PLUGIN_DIR) === 0) {
                $relative_path = str_replace(WP_PLUGIN_DIR, '', $plugin_dir);
            } elseif (strpos($plugin_dir, WPMU_PLUGIN_DIR) === 0) {
                $relative_path = str_replace(WPMU_PLUGIN_DIR, '', $plugin_dir);
            } else {
                return ''; // Não está em um diretório reconhecido de plugins
            }
            $relative_path = ltrim($relative_path, '/');
            $path_parts = explode('/', $relative_path);
            self::$plugin_slug = $path_parts[0];
            return self::$plugin_slug;
        }
        public static function init()
        {
            add_action("admin_notices", [__CLASS__, "message"]);
            add_action("admin_head", [__CLASS__, "register"]);
            add_action("admin_footer", [__CLASS__, "enqueue"]);
        }
        public static function register()
        {
            wp_enqueue_style(
                PRODCLASS,
                URL . "includes/feedback-last/feedback-last.css",
                array(),             // Dependencies (empty array if none)
                RESTORE_CLASSIC_WIDGETSVERSION // Use your plugin's version constant for cache busting
            );
            if (WPMSHOW) {
                wp_register_script(
                    PRODCLASS,
                    URL . "includes/feedback-last/feedback-last.js",
                    ["jquery"],
                    RESTORE_CLASSIC_WIDGETSVERSION, // Use your plugin's main version constant for cache busting
                    true
                );
            }
        }
        public static function enqueue()
        {
            wp_enqueue_style(PRODCLASS);
            wp_enqueue_script(PRODCLASS);
        }
        public static function message()
        {
            if (!update_option(LAST, time())) {
                add_option(LAST, time());
            }
            $slug = self::$plugin_slug;
?>
            <div class="<?php echo esc_attr(
                            PRODCLASS
                        ); ?>-wrap-deactivate" style="display:none">
                <div class="bill-vote-gravatar">
                    <a href="https://profiles.wordpress.org/sminozzi" target="_blank">
                        <img src="<?php echo esc_url(RESTORE_CLASSIC_WIDGETSURL . 'assets/images/' . 'bill.jpg'); ?>" alt="Bill Minozzi" width="70" height="70">
                    </a>
                </div>
                <div class="bill-vote-message">
                    <?php
                    echo '<h2 style="color:blue;">';
                    echo esc_attr(PRODUCTNAME) . " - ";
                    echo esc_attr__("We're sorry to hear that you're leaving.", 'restore-classic-widgets');
                    echo "</h2>";
                    esc_attr_e("Hello,", 'restore-classic-widgets');
                    echo "<br />";
                    echo "<br />";
                    ?>
                    <?php esc_attr_e("Thank you for using our products. Before you deactivate, we'd like to offer a few options to improve your experience:", 'restore-classic-widgets'); ?>
                    <br><br>
                    <strong>
                        1. <?php esc_attr_e("Explore more plugins - Discover our other free plugins and themes.", 'restore-classic-widgets'); ?>
                        <br>
                        2. <?php esc_attr_e("Support - Need help? Visit our support page.", 'restore-classic-widgets'); ?>
                        <br>
                        3. <?php esc_attr_e("Cancel deactivation - Changed your mind? Keep using this plugin.", 'restore-classic-widgets'); ?>
                        <br>
                        4. <?php esc_attr_e("Deactivate - Proceed with deactivation.", 'restore-classic-widgets'); ?>
                    </strong>
                    <br><br>
                    <?php esc_attr_e("Trusted by over 50,000 users, our 20+ free plugins and 6 themes can supercharge your site's security, functionality, and backups.", 'restore-classic-widgets'); ?>
                    <br><br>
                    <?php esc_attr_e("Best regards!", 'restore-classic-widgets'); ?>
                    <br><br>
                    Bill Minozzi<br />
                    Plugin Developer
                    ` <br /> <br />
                </div>
                <br>
                <div class="bill-minozzi-button-group">
                    <a href="<?php echo esc_url(PAGE); ?>" class="button button-primary <?php echo esc_attr(PRODCLASS); ?>-close-submit_lf discover-plugins-btn">
                        <?php esc_attr_e("Discover New FREE Plugins", 'restore-classic-widgets'); ?>
                    </a>
                    <a href="https://BillMinozzi.com/dove/" class="button button-primary <?php echo esc_attr(PRODCLASS); ?>-close-dialog_lf support-page-btn">
                        <?php esc_attr_e("Support Page", 'restore-classic-widgets'); ?>
                    </a>
                    <a href="#" class="button <?php echo esc_attr(PRODCLASS); ?>-close-dialog_lf cancel-btn_feedback">
                        <?php esc_attr_e("Cancel", 'restore-classic-widgets'); ?>
                    </a>
                    <a href="#" class="button <?php echo esc_attr(PRODCLASS); ?>-deactivate_lf deactivate-btn">
                        <?php esc_attr_e("Just Deactivate", 'restore-classic-widgets'); ?>
                    </a>
                </div>
                <br><br>
            </div>
<?php
        }
    } //end class
    new restore_classic_widgets_mConfig();
    $stringtime = strval(time());
    if (!update_option(LAST, $stringtime)) {
        add_option(LAST, $stringtime);
    }
} // End Namespace ...
?>