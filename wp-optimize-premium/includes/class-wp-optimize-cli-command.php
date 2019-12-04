<?php

if (!defined('WPO_PLUGIN_MAIN_PATH')) die('No direct access allowed');

/**
 * Implements example command.
 */
class WP_Optimize_CLI_Command extends WP_CLI_Command {

	/**
	 * Handle wp-optimize command. Requires PHP 5.3+; but then, so does WP-CLI
	 *
	 * @param array $args 		command line params.
	 * @param array $assoc_args command line params in associative array.
	 */
	public function __invoke($args, $assoc_args) { // phpcs:ignore PHPCompatibility.FunctionNameRestrictions.NewMagicMethods.__invokeFound

		// change underscores to hypes in command.
		if (isset($args[0])) {
			$args[0] = str_replace('-', '_', $args[0]);
		}

		if (!empty($args) && is_callable(array($this, $args[0]))) {
			call_user_func(array($this, $args[0]), $assoc_args);
			return;
		}

		WP_CLI::log('usage: wp optimize <command> [--optimization-id=<optimization-id>] [--site-id=<site-id>] [--param1=value1] [--param2=value2] ...');
		WP_CLI::log("\n".__('These are common WP-Optimize commands used in various situations:', 'wp-optimize')."\n");

		$commands = array(
			'version' => __('Display version of WP-Optimize', 'wp-optimize'),
			'sites' => __('Display list of sites in Multisite mode.', 'wp-optimize'),
			'optimizations' => __('Display available optimizations', 'wp-optimize'),
			'do-optimization' => __('Do selected optimization', 'wp-optimize')
		);

		foreach ($commands as $command => $description) {
			WP_CLI::log(sprintf("     %-25s %s", $command, $description));
		}
	}

	/**
	 * Display WP-Optimize version.
	 */
	public function version() {
		WP_CLI::log(WPO_VERSION);
	}

	/**
	 * Display list of optimizations.
	 */
	public function optimizations() {
		$optimizer = WP_Optimize()->get_optimizer();
		$optimizations = $optimizer->sort_optimizations($optimizer->get_optimizations());

		foreach ($optimizations as $id => $optimization) {

			if (false === $optimization->display_in_optimizations_list()) continue;

			// This is an array, with attributes dom_id, activated, settings_label, info; all values are strings.
			$html = $optimization->get_settings_html();

			WP_CLI::log(sprintf("     %-25s %s", $id, $html['settings_label']));
		}
	}

	/**
	 * Display list of sites in Multisite mode.
	 */
	public function sites() {
		if (!is_multisite()) {
			WP_CLI::error(__('This command available only in Multisite mode.', 'wp-optimize'));
		}

		$sites = WP_Optimize()->get_sites();

		WP_CLI::log(sprintf("     %-15s %s", __('Site ID', 'wp-optimize'), __('Path', 'wp-optimize')));
		foreach ($sites as $site) {
			WP_CLI::log(sprintf("     %-15s %s", $site->blog_id, $site->domain.$site->path));
		}
	}

	/**
	 * Call do optimization command.
	 *
	 * @param array $assoc_args array with params for optimization, optimization_id item required.
	 */
	public function do_optimization($assoc_args) {

		if (!isset($assoc_args['optimization-id'])) {
			WP_CLI::error(__('Please, select optimization.', 'wp-optimize'));
			return;
		}

		if (isset($assoc_args['site-id'])) {
			$assoc_args['site_id'] = array_values(array_map('trim', explode(',', $assoc_args['site-id'])));
		}

		if (isset($assoc_args['include-ui'])) {
			$assoc_args['include_ui_elements'] = array_values(array_map('trim', explode(',', $assoc_args['include-ui'])));
		} else {
			$assoc_args['include_ui_elements'] = false;
		}

		// save posted parameters in data item to make them available in optimization.
		$assoc_args['data'] = $assoc_args;

		// get array with optimization ids.
		$optimizations_ids = array_values(array_map('trim', explode(',', $assoc_args['optimization-id'])));

		foreach ($optimizations_ids as $optimization_id) {
			$assoc_args['optimization_id'] = $optimization_id;
			$results = $this->get_commands()->do_optimization($assoc_args);

			if (is_wp_error($results)) {
				WP_CLI::error($results);
			} elseif (!empty($results['errors'])) {
				$message = implode("\n", $results['errors']);
				WP_CLI::error($message);
			} else {
				$message = implode("\n", $results['result']->output);
				WP_CLI::success($message);
			}
		}
	}

	/**
	 * Return instance of WP_Optimize_Commands.
	 *
	 * @return WP_Optimize_Commands
	 */
	private function get_commands() {
		// Other commands, available for any remote method.
		if (!class_exists('WP_Optimize_Commands')) include_once(WPO_PLUGIN_MAIN_PATH.'includes/class-commands.php');

		return new WP_Optimize_Commands();
	}
}

WP_CLI::add_command('optimize', 'WP_Optimize_CLI_Command');
