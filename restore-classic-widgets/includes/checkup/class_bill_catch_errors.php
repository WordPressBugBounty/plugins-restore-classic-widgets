<?php
if (!defined('ABSPATH')) {
	die('Invalid request.');
}
if (is_multisite())
	return;
if (!function_exists("restore_classic_widgets_is_action_registered")) {
	function restore_classic_widgets_is_action_registered($hook_name, $callback_function)
	{
		global $wp_filter;
		if (isset($wp_filter[$hook_name])) {
			foreach ($wp_filter[$hook_name] as $priority => $actions) {
				foreach ($actions as $action) {
					if (is_array($action['function']) && $action['function'][0] === $callback_function) {
						return true;
					}
				}
			}
		}
		return false;
	}
}
if (!restore_classic_widgets_is_action_registered('wp_ajax_restore_classic_widgets_get_js_errors', 'restore_classic_widgets_js_error_catched')) {
	add_action('wp_ajax_restore_classic_widgets_js_error_catched', 'restore_classic_widgets_js_error_catched');
	add_action('wp_ajax_nopriv_restore_classic_widgets_js_error_catched', 'restore_classic_widgets_js_error_catched');
}
if (!function_exists("restore_classic_widgets_js_error_catched")) {
	function restore_classic_widgets_js_error_catched()
	{
		if (isset($_REQUEST)) {
			if (!isset($_REQUEST['restore_classic_widgets_js_error_catched']))
				die("empty error");
			if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'bill-catch-js-errors')) {
				status_header(406, 'Invalid nonce');
				die();
			}
			$restore_classic_widgets_js_error_catched = sanitize_text_field(wp_unslash($_REQUEST['restore_classic_widgets_js_error_catched']));
			$restore_classic_widgets_js_error_catched = trim($restore_classic_widgets_js_error_catched);
			if (!empty($restore_classic_widgets_js_error_catched)) {
				$parts = explode(" | ", $restore_classic_widgets_js_error_catched);
				for ($i = 0; $i < count($parts); $i++) {
					$txt = 'Javascript ' . $parts[$i];
					add_option('restore_classic_widgets_javascript_error', time());
				}
				die('OK!!!');
			}
		}
		die('NOT OK!');
	}
}
class restore_classic_widgets_catch_errors
{
	public function __construct()
	{
		add_action('wp_head', array($this, 'add_restore_classic_widgets_javascript_to_header'));
		add_action('admin_head', array($this, 'add_restore_classic_widgets_javascript_to_header'));
	}
	public function add_restore_classic_widgets_javascript_to_header()
	{
		wp_enqueue_script(
			'bill-error-catcher',
			plugin_dir_url(__FILE__) . 'assets/js/error-catcher.js',
			array('jquery'), // <--- ADICIONA O JQUERY COMO DEPENDÊNCIA
			$plugin_version,
			true
		);
	}
	private function get_ajax_url()
	{
		return admin_url('admin-ajax.php');
	}
}
new restore_classic_widgets_catch_errors();