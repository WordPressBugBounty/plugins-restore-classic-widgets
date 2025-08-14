<?php
if ( ! defined( 'ABSPATH' ) ) exit; 
class class_restore_classic_widgets_show_plugins
{
    public function __construct()
    {
        add_action("admin_enqueue_scripts", [$this, "enqueue_scripts"]);
    }
    public function restore_classic_widgets_plugin_installed($slug)
    {
        $all_plugins = get_plugins();
        foreach ($all_plugins as $key => $value) {
            $plugin_file = $key;
            $slash_position = strpos($plugin_file, '/');
            $folder = substr($plugin_file, 0, $slash_position);
            if ($slug == $folder) {
                return true;
            }
        }
        return false;
    }
    public function enqueue_scripts()
    {
        $plugin_path = trailingslashit(dirname(plugin_basename(__FILE__)));
        $parts = explode('/', rtrim($plugin_path, '/')); // Divide a string em partes usando '/' como delimitador
        $plugin_slug = reset($parts); // Obtém o primeiro elemento da lista
        $plugin_url = plugins_url() . '/' . $plugin_slug;
        wp_enqueue_style(
            'more-more',
            $plugin_url . '/includes/more/more.css',
            array(),             // Dependencies (empty array if none)
            RESTORE_CLASSIC_WIDGETSVERSION // Explicitly set version for cache busting
        );
        wp_register_script(
            'more-more-js',
            $plugin_url . '/includes/more/more.js',
            array('jquery'),     // Dependencies
            RESTORE_CLASSIC_WIDGETSVERSION, // Explicitly set version for cache busting
            true                 // Load in the footer (common for scripts)
        );
        wp_enqueue_script('more-more-js'); // Enqueue the registered script
        wp_register_script(
            "bill-js-toast24",
            $plugin_url . "/assets/js/jquery.toast.js",
            array(),             // Corrected: Dependencies should be an array (empty if none)
            RESTORE_CLASSIC_WIDGETSVERSION, // Explicitly set version for cache busting
            true                 // Load in the footer (common for UI scripts like this)
        );
        wp_enqueue_script("bill-js-toast24");
    }
    public function restore_classic_widgets_show_plugins()
    {
        $plugins_to_install = [];
        $plugins_to_install[0]["Name"] = "Anti Hacker Plugin";
        $plugins_to_install[0]["Description"] =
            "Cyber Attack Protection. Firewall, Malware Scanner, Login Protect, block user enumeration and TOR, disable Json WordPress Rest API, xml-rpc (xmlrpc) & Pingback and more security tools...";
        $plugins_to_install[0]["image"] =
            "https://ps.w.org/antihacker/assets/icon-256x256.gif?rev=2524575";
        $plugins_to_install[0]["slug"] = "antihacker";
        $plugins_to_install[1]["Name"] = "Stop Bad Bots";
        $plugins_to_install[1]["Description"] =
            "Stop Bad Bots, Block SPAM bots, Crawlers and spiders also from botnets. Save bandwidth, avoid server overload and content steal. Blocks also by IP. Visitor Analytics with Separated Bots";
        $plugins_to_install[1]["image"] =
            "https://ps.w.org/stopbadbots/assets/icon-256x256.gif?rev=2524815";
        $plugins_to_install[1]["slug"] = "stopbadbots";
        $plugins_to_install[2]["Name"] = "WP Tools";
        $plugins_to_install[2]["Description"] =
            "Enhanced: Unlock Over 47 Essential Tools! Your Ultimate Swiss Army Knife for Elevating Your Website to the Next Level. Also, check for errors, including JavaScript errors. Page Lad Report.";
        $plugins_to_install[2]["image"] =
            "https://ps.w.org/wptools/assets/icon-256x256.gif?rev=2526088";
        $plugins_to_install[2]["slug"] = "wptools";
        $plugins_to_install[3]["Name"] = "reCAPTCHA For All";
        $plugins_to_install[3]["Description"] = "Protect ALL Selected Pages of your site against bots (spam, hackers, fake users and other types of automated abuse)
	  with Cloudflare Turnstile or invisible reCaptcha V3 (Google). You can also block visitors from China.";
        $plugins_to_install[3]["image"] =
            "https://ps.w.org/recaptcha-for-all/assets/icon-256x256.gif?rev=2544899";
        $plugins_to_install[3]["slug"] = "recaptcha-for-all";
        $plugins_to_install[4]["Name"] = "WP Memory";
        $plugins_to_install[4]["Description"] =
            "Check High Memory Usage, Memory Limit, PHP Memory, show result in Site Health Page and help to fix php low memory limit. In-page Memory Usage Report.";
        $plugins_to_install[4]["image"] =
            "https://ps.w.org/wp-memory/assets/icon-256x256.gif?rev=2525936";
        $plugins_to_install[4]["slug"] = "wp-memory";
        $plugins_to_install[5]["Name"] = "Database Backup";
        $plugins_to_install[5]["Description"] =
            "Quick and Easy Database Backup with a Single Click. Verify Tables and Schedule Automatic Backups.";
        $plugins_to_install[5]["image"] =
            "https://ps.w.org/database-backup/assets/icon-256x256.gif?rev=2862571";
        $plugins_to_install[5]["slug"] = "database-backup";
        $plugins_to_install[6]["Name"] = "Database Restore Bigdump";
        $plugins_to_install[6]["Description"] =
            "Database Restore with BigDump script. The ideal solution for restoring very large databases securely.";
        $plugins_to_install[6]["image"] =
            "https://ps.w.org/bigdump-restore/assets/icon-256x256.gif?rev=2872393";
        $plugins_to_install[6]["slug"] = "bigdump-restore";
        $plugins_to_install[7]["Name"] = "Easy Update URLs";
        $plugins_to_install[7]["Description"] =
            "Fix your URLs at database after cloning or moving sites.";
        $plugins_to_install[7]["image"] =
            "https://ps.w.org/easy-update-urls/assets/icon-256x256.gif?rev=2866408";
        $plugins_to_install[7]["slug"] = "easy-update-urls";
        $plugins_to_install[8]["Name"] = "S3 Cloud Contabo";
        $plugins_to_install[8]["Description"] =
            "Connect you with your Contabo S3-compatible Object Storage.";
        $plugins_to_install[8]["image"] =
            "https://ps.w.org/s3cloud/assets/icon-256x256.gif?rev=2855916";
        $plugins_to_install[8]["slug"] = "s3cloud";
        $plugins_to_install[9]["Name"] = "Tools for S3 AWS Amazon";
        $plugins_to_install[9]["Description"] =
            "Connect you with your Amazon S3-compatible Object Storage.";
        $plugins_to_install[9]["image"] =
            "https://ps.w.org/toolsfors3/assets/icon-256x256.gif?rev=2862487";
        $plugins_to_install[9]["slug"] = "toolsfors3";
        $plugins_to_install[10]["Name"] = "Hide Site Title";
        $plugins_to_install[10]["Description"] =
            "The Hide Site Title Remover plugin allows you to easily remove titles from your WordPress posts and pages, without affecting menus or titles in the admin area.";
        $plugins_to_install[10]["image"] =
            "https://ps.w.org/hide-site-title/assets/icon-256x256.gif?rev=2862487";
        $plugins_to_install[10]["slug"] = "hide-site-title";
        $plugins_to_install[11]["Name"] = "Disable WordPress Sitemap";
        $plugins_to_install[11]["Description"] =
            "The sitemap is automatically created by WordPress from version 5.5. This plugin offers you the option to disable it, allowing you to use another SEO plugin to generate it if desired.";
        $plugins_to_install[11]["image"] =
            "https://ps.w.org/disable-wp-sitemap/assets/icon-256x256.gif?rev=2862487";
        $plugins_to_install[11]["slug"] = "disable-wp-sitemap";
?>
        <div style="padding-right:20px;">
            <br>
            <div id="bill-wrap-install-modal" class="bill-wrap-install-modal" style="display:none">
                <h3>Please wait</h3>
                <big>
                    <h4>
                        Installing plugin <div id="billpluginslugModal">...</div>
                    </h4>
                </big>
                <img src="/wp-admin/images/wpspin_light-2x.gif" id="billimagewaitfbl" style="display:none;margin-left:0px;margin-top:0px;" />
                <br />
            </div>
            <h2>Enhance: Free, Convenient Plugin Suite by the Same Author. Instant Installation: A Single Click on the Install Button.</h2>
            <table style="margin-right:20px; border-spacing: 0 25px; " class="widefat" cellspacing="0" id="restore_classic_widgets_class_install-more-plugins-table">
                <tbody class="restore_classic_widgets_class_install-more-plugins-body">
                    <?php
                    $counter = 0;
                    $total = count($plugins_to_install);
                    for ($i = 0; $i < $total; $i++) {
                        if ($counter % 2 == 0) {
                            echo '<tr style="background:#f6f6f1;">';
                        }
                        ++$counter;
                        if ($counter % 2 == 1) {
                            echo '<td style="max-width:140px; max-height:140px; padding-left: 40px;" >';
                        } else {
                            echo '<td style="max-width:140px; max-height:140px;" >';
                        }
                        echo '<img style="width:100px;" src="' .
                            esc_url($plugins_to_install[$i]["image"]) .
                            '">';
                        echo "</td>";
                        echo '<td style="width:40%;">';
                        echo "<h3>" . esc_attr($plugins_to_install[$i]["Name"]) . "</h3>";
                        echo esc_attr($plugins_to_install[$i]["Description"]);
                        echo "<br>";
                        echo "</td>";
                        echo '<td style="max-width:140px; max-height:140px;" >';
                        if ($this->restore_classic_widgets_plugin_installed($plugins_to_install[$i]["slug"])) {
                            echo '<a href="#" class="button activate-now">Installed</a>';
                        } else {
                            echo '<a href="#" id="_' .
                                esc_attr($plugins_to_install[$i]["slug"]) .
                                '"class="button button-primary bill-install-now-24">Install</a>';
                        }
                        echo "</td>";
                        if ($counter % 2 == 1) {
                            echo '<td style="width; 100px; border-left: 1px solid gray;">';
                            echo "</td>";
                        }
                        if ($counter % 2 == 0) {
                            echo "</tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
            <?php wp_nonce_field('restore_classic_widgets_install_plugin_class', 'nonce_install');
            $plugin_path = trailingslashit(dirname(plugin_basename(__FILE__)));
            $parts = explode('/', rtrim($plugin_path, '/')); // Divide a string em partes usando '/' como delimitador
            $plugin_slug = reset($parts); // Obtém o primeiro elemento da lista
            ?>
            <input type="hidden" name="main_slug" id="main_slug" value="<?php echo esc_attr($plugin_slug); ?>">
            <center><big>
                    <a href="https://profiles.wordpress.org/sminozzi/#content-plugins" target="_blank">Discover All Plugins</a>
                    &nbsp;&nbsp;
                    <a href="https://profiles.wordpress.org/sminozzi/#content-themes" target="_blank">Discover All Themes</a>
                </big> </center>
        </div>
<?php
    }
} // end class
$plugin_displayer = new class_restore_classic_widgets_show_plugins();
function restore_classic_widgets_install_plugin()
{
    if (!isset($_POST["nonce"])) {
            wp_die("nonce not set");
    }
    else{    
        $nonce = sanitize_text_field(wp_unslash($_POST["nonce"]));
        if (!wp_verify_nonce($nonce, "restore_classic_widgets_install_plugin_class") and !wp_verify_nonce($nonce, "restore_classic_widgets_install")) {
            wp_die("invalid nonce");
        }
    } 
    if (isset($_POST["slug"])) {
        $slug = sanitize_text_field(wp_unslash($_POST["slug"]));
    } else {
        echo "Fail error (-5)";
        wp_die();
    }
    if (
        $slug != "database-backup" &&
        $slug != "bigdump-restore" &&
        $slug != "easy-update-urls" &&
        $slug != "s3cloud" &&
        $slug != "toolsfors3" &&
        $slug != "antihacker" &&
        $slug != "toolstruthsocial" &&
        $slug != "stopbadbots" &&
        $slug != "wptools" &&
        $slug != "recaptcha-for-all" &&
        $slug != "wp-memory" &&
        $slug != "disable-wp-sitemap" &&
        $slug != "hide-site-title"
    ) {
        wp_die("wrong slug");
    }
    $plugin["source"] = "repo"; // $_GET['plugin_source']; // Plugin source.
    require_once ABSPATH . "wp-admin/includes/plugin-install.php"; // Need for plugins_api.
    require_once ABSPATH . "wp-admin/includes/class-wp-upgrader.php"; // Need for upgrade classes.
    $api = plugins_api("plugin_information", [
        "slug" => $slug,
        "fields" => ["sections" => false],
    ]);
    if (is_wp_error($api)) {
        echo "Fail error (-1)";
        wp_die();
    } else {
        if (isset($api->download_link)) {
            $plugin["source"] = $api->download_link;
            $source = $api->download_link;
        } else {
            echo "Fail error (-2)";
            wp_die();
        }
        $nonce = "install-plugin_" . $api->slug;
        $plugin = $slug;
        class restore_classic_widgets_install_QuietSkin extends \WP_Upgrader_Skin
        {
            public function feedback($string, ...$args)
            {
            }
            public function header()
            {
            }
            public function footer()
            {
            }
        }
        $skin = new restore_classic_widgets_install_QuietSkin(["api" => $api]);
        $upgrader = new Plugin_Upgrader($skin);
        try {
            $upgrader->install($source);
            $all_plugins = get_plugins();
            foreach ($all_plugins as $key => $value) {
                $plugin_file = $key;
                $slash_position = strpos($plugin_file, "/");
                $folder = substr($plugin_file, 0, $slash_position);
                if ($slug == $folder) {
                    $result = activate_plugin(
                        ABSPATH . "wp-content/plugins/" . $plugin_file
                    );
                    if (is_wp_error($result)) {
                        echo "Fail error (-3)";
                        wp_die();
                    }
                } // if matched
            }
        } catch (Exception $e) {
            echo "Fail error (-4)";
            wp_die();
        }
    } // activation
    wp_die("OK");
}
add_action("wp_ajax_restore_classic_widgets_install_plugin", "restore_classic_widgets_install_plugin");