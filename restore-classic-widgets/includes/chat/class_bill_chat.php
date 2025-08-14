<?php
namespace restore_classic_widgets_BillChat;
if (!defined('ABSPATH')) {
    die('Invalid request.');
}
if (function_exists('is_multisite') && is_multisite()) {
    return;
}
class ChatPlugin
{
    public function __construct()
    {
        add_action('wp_ajax_restore_classic_widgets_chat_send_message', [$this, 'restore_classic_widgets_chat_send_message']);
        add_action('wp_ajax_restore_classic_widgets_chat_reset_messages', [$this, 'restore_classic_widgets_chat_reset_messages']);
        add_action('wp_ajax_restore_classic_widgets_chat_load_messages', [$this, 'restore_classic_widgets_chat_load_messages']);
        add_action('admin_init', [$this, 'chat_plugin_scripts']);
        add_action('admin_init', [$this, 'enqueue_chat_scripts']);
    }
    public function chat_plugin_scripts()
    {
        wp_enqueue_style(
            'chat-style',
            plugin_dir_url(__FILE__) . 'chat.css',
            array(), // Dependencies (empty array if none)
            RESTORE_CLASSIC_WIDGETSVERSION // Use your plugin's version constant for cache busting
        );
    }
    public function enqueue_chat_scripts()
    {
        wp_enqueue_script(
            'chat-script',
            plugin_dir_url(__FILE__) . 'chat.js',
            array('jquery'),
            RESTORE_CLASSIC_WIDGETSVERSION,
            true
        );
        wp_localize_script('chat-script', 'restore_classic_widgets_data', array(
            'ajax_url'                 => admin_url('admin-ajax.php'),
            'reset_nonce'              => wp_create_nonce('restore_classic_widgets_chat_reset_messages_nonce'), // Linha adicionada
            'reset_success'            => esc_attr__('Chat messages reset successfully.', 'restore-classic-widgets'),
            'reset_error'              => esc_attr__('Error resetting chat messages.', 'restore-classic-widgets'),
            'invalid_message'          => esc_attr__('Invalid message received:', 'restore-classic-widgets'),
            'invalid_response_format'  => esc_attr__('Invalid response format:', 'restore-classic-widgets'),
            'response_processing_error' => esc_attr__('Error processing server response:', 'restore-classic-widgets'),
            'not_json'                 => esc_attr__('Response is not valid JSON.', 'restore-classic-widgets'),
            'ajax_error'               => esc_attr__('AJAX request failed:', 'restore-classic-widgets'),
            'send_error'               => esc_attr__('Error sending the message. Please try again later.', 'restore-classic-widgets'),
            'empty_message_error'      => esc_attr__('Please enter a message!', 'restore-classic-widgets'),
        ));
    }
    public function restore_classic_widgets_chat_load_messages()
    {
        check_ajax_referer('restore_classic_widgets_chat_reset_messages_nonce', 'security');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('You do not have permission to perform this action.');
            wp_die();
        }
        if (ob_get_length()) {
            ob_clean();
        }
        $messages = get_option('chat_messages', []);
        $last_count = isset($_POST['last_count']) ? intval($_POST['last_count']) : 0;
        $new_messages = [];
        if (count($messages) > $last_count) {
            $new_messages = array_slice($messages, $last_count);
        }
        wp_send_json([
            'message_count' => count($messages),
            'messages' => array_map(function ($message) {
                return [
                    'text' => esc_html($message['text']),
                    'sender' => esc_html($message['sender'])
                ];
            }, $new_messages)
        ]);
        wp_die();
    }
    public function restore_classic_widgets_read_file($file, $lines)
    {
        clearstatcache(true, $file); // Clear cache to ensure current file state
        if (!file_exists($file) || !is_readable($file)) {
            return []; // Return empty array in case of error
        }
        $text = [];
        function get_last_n_lines_from_file(string $file, int $lines = 10): array
        {
            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once ABSPATH . '/wp-admin/includes/file.php';
                if (!WP_Filesystem()) {
                    return [];
                }
            }
            if (!$wp_filesystem->exists($file)) {
                return [];
            }
            $filesize = $wp_filesystem->size($file);
            if ($filesize === false) { // Check if size retrieval failed.
                return [];
            }
            $bufferSize = 8192; // 8KB read chunk.
            $text = []; // Array to store the lines.
            $linecounter = 0;
            $currentChunk = '';
            if ($filesize < $bufferSize) {
                $bufferSize = $filesize;
            }
            if ($bufferSize < 1) {
                return [];
            }
            $pos = $filesize - $bufferSize;
            while ($pos >= 0 && $linecounter < $lines) {
                if ($pos < 0) {
                    $pos = 0;
                }
                $chunk = $wp_filesystem->get_contents($file, false, null, $pos, $bufferSize);
                if ($chunk === false) {
                    usleep(500000); // Wait 0.5 seconds and retry (basic retry logic).
                    $chunk = $wp_filesystem->get_contents($file, false, null, $pos, $bufferSize);
                    if ($chunk === false) {
                        break; // If second attempt fails, exit loop.
                    }
                }
                $currentChunk = $chunk . $currentChunk;
                $linesInChunk = explode("\n", $currentChunk);
                $currentChunk = array_shift($linesInChunk);
                foreach (array_reverse($linesInChunk) as $line) {
                    $text[] = $line;
                    $linecounter++;
                    if ($linecounter >= $lines) {
                        break 2; // Break both foreach and while loops.
                    }
                }
                $pos -= $bufferSize;
            }
            if (!empty($currentChunk) && $linecounter < $lines) {
                $text[] = $currentChunk;
            }
            return $text;
        }
        return $text;
    }
    public function restore_classic_widgets_chat_call_chatgpt_api($data, $chatType, $chatVersion)
    {
        $restore_classic_widgets_chat_erros = '';
        try {
            function filter_log_content($content)
            {
                if (is_array($content)) {
                    $filteredArray = array_filter($content);
                    return empty($filteredArray) ? '' : $content;
                } elseif (is_object($content)) {
                    return '';
                } else {
                    return $content;
                }
            }
            $restore_classic_widgets_folders = ChatPlugin::get_path_logs();
            $log_type = "PHP Error Log";
            $restore_classic_widgets_chat_erros = "Log ($log_type) not found or not readable.";
            foreach ($restore_classic_widgets_folders as $restore_classic_widgets_folder) {
                if (!file_exists($restore_classic_widgets_folder) && !is_readable($restore_classic_widgets_folder)) {
                    continue;
                }
                $returned_restore_classic_widgets_chat_erros = $this->restore_classic_widgets_read_file($restore_classic_widgets_folder, 40);
                $returned_restore_classic_widgets_chat_erros = filter_log_content($returned_restore_classic_widgets_chat_erros);
                $returned_restore_classic_widgets_chat_erros = filter_log_content($returned_restore_classic_widgets_chat_erros);
                if (!empty($returned_restore_classic_widgets_chat_erros)) {
                    $restore_classic_widgets_chat_erros = $returned_restore_classic_widgets_chat_erros;
                    break;
                }
            }
        } catch (Exception $e) {
            $restore_classic_widgets_chat_erros = "An error occurred to read error logs: " . $e->getMessage();
        }
        $plugin_path = plugin_basename(__FILE__); // Retorna algo como "plugin-folder/plugin-file.php"
        $language = get_locale();
        $plugin_slug = explode('/', $plugin_path)[0]; // Pega apenas o primeiro diretório (a raiz)
        $domain = wp_parse_url(home_url(), PHP_URL_HOST);
        if (empty($restore_classic_widgets_chat_erros)) {
            $restore_classic_widgets_chat_erros = 'No errors found!';
        }
        $restore_classic_widgets_checkup = \restore_classic_widgets_sysinfo_get();
        $data2 = [
            'param1' => $data,
            'param2' => $restore_classic_widgets_checkup,
            'param3' => $restore_classic_widgets_chat_erros,
            'param4' => $language,
            'param5' => $plugin_slug,
            'param6' => $domain,
            'param7' => $chatType,
            'param8' => $chatVersion,
        ];
        $response = wp_remote_post('https://BillMinozzi.com/chat/api/api.php', [
            'timeout' => 60,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($data2),
        ]);
        if (is_wp_error($response)) {
            $error_message = sanitize_text_field($response->get_error_message());
        } else {
            $body = sanitize_text_field(wp_remote_retrieve_body($response));
            $data = json_decode($body, true);
        }
        if (isset($data['success']) && $data['success'] === true) {
            $message = $data['message'];
        } else {
            $message = esc_attr__("Error contacting the Artificial Intelligence (API). Please try again later.", 'restore-classic-widgets');
        }
        return $message;
    }
    public static function get_path_logs()
    {
        $restore_classic_widgets_folders = [];
        $error_log_path = ini_get("error_log");
        if (!empty($error_log_path)) {
            $error_log_path = trim($error_log_path);
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $error_log_path = trailingslashit(WP_CONTENT_DIR) . 'debug.log';
            } else {
                $error_log_path = trailingslashit(ABSPATH) . 'error_log';
            }
        }
        $restore_classic_widgets_folders[] = $error_log_path;
        $restore_classic_widgets_folders[] = WP_CONTENT_DIR . "/debug.log";
        $restore_classic_widgets_folders[] = restore_classic_widgets_dir_path(__FILE__) . "error_log";
        $restore_classic_widgets_folders[] = restore_classic_widgets_dir_path(__FILE__) . "php_errorlog";
        $restore_classic_widgets_folders[] = get_theme_root() . "/error_log";
        $restore_classic_widgets_folders[] = get_theme_root() . "/php_errorlog";
        $restore_classic_widgets_admin_path = str_replace(get_bloginfo("url") . "/", ABSPATH, get_admin_url());
        $restore_classic_widgets_folders[] = $restore_classic_widgets_admin_path . "/error_log";
        $restore_classic_widgets_folders[] = $restore_classic_widgets_admin_path . "/php_errorlog";
        try {
            $restore_classic_widgets_plugins = array_slice(scandir(restore_classic_widgets_dir_path(__FILE__)), 2);
            foreach ($restore_classic_widgets_plugins as $restore_classic_widgets_plugin) {
                $plugin_path = restore_classic_widgets_dir_path(__FILE__) . $restore_classic_widgets_plugin;
                if (is_dir($plugin_path)) {
                    $restore_classic_widgets_folders[] = $plugin_path . "/error_log";
                    $restore_classic_widgets_folders[] = $plugin_path . "/php_errorlog";
                }
            }
        } catch (Exception $e) {
        }
        try {
            $restore_classic_widgets_themes = array_slice(scandir(get_theme_root()), 2);
            foreach ($restore_classic_widgets_themes as $restore_classic_widgets_theme) {
                if (is_dir(get_theme_root() . "/" . $restore_classic_widgets_theme)) {
                    $restore_classic_widgets_folders[] = get_theme_root() . "/" . $restore_classic_widgets_theme . "/error_log";
                    $restore_classic_widgets_folders[] = get_theme_root() . "/" . $restore_classic_widgets_theme . "/php_errorlog";
                }
            }
        } catch (Exception $e) {
        }
        return $restore_classic_widgets_folders;
    }
    public function restore_classic_widgets_chat_send_message()
    {
        check_ajax_referer('restore_classic_widgets_chat_reset_messages_nonce', 'security');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('You do not have permission to perform this action.');
            wp_die();
        }
        $message = ''; // Initialize the variable to an empty string
        if (isset($_POST['message'])) {
            $message = sanitize_text_field(wp_unslash($_POST['message']));
        }
        $chatType = isset($_POST['chat_type']) ? sanitize_text_field(wp_unslash($_POST['chat_type'])) : 'default';
        if (empty($message)) {
            if ($chatType == 'auto-checkup') {
                $message = esc_attr("Auto Checkup for Erros button clicked...", 'restore-classic-widgets');
            } elseif ($chatType == 'auto-checkup2') {
                $message = esc_attr("Auto Checkup Server button clicked...", 'restore-classic-widgets');
            }
        }
        $chatVersion = isset($_POST['chat_version']) ? sanitize_text_field(wp_unslash($_POST['chat_version'])) : '1.00';
        $response_data = $this->restore_classic_widgets_chat_call_chatgpt_api($message, $chatType, $chatVersion);
        if (!empty($response_data)) {
            $output = $response_data;
            $resposta_formatada = $output;
        } else {
            $output = "Error to get response from AI source!";
            $output = esc_attr__("Error to get response from AI source!", 'restore-classic-widgets');
        }
        $messages = get_option('chat_messages', []);
        $messages[] = [
            'text' => $message,
            'sender' => 'user'
        ];
        $messages[] = [
            'text' => $resposta_formatada,
            'sender' => 'chatgpt'
        ];
        update_option('chat_messages', $messages);
        wp_die();
    }
    public function restore_classic_widgets_chat_reset_messages()
    {
        check_ajax_referer('restore_classic_widgets_chat_reset_messages_nonce', 'security');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('You do not have permission to perform this action.');
            wp_die();
        }
        update_option('chat_messages', []);
        wp_send_json_success('Chat messages have been reset.');
        wp_die();
    }
}
new ChatPlugin();