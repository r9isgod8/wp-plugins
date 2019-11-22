<?php
/*
Plugin Name: NinjaFirewall (WP Edition)
Plugin URI: https://nintechnet.com/
Description: A true Web Application Firewall to protect and secure WordPress.
Version: 4.0.5
Author: The Ninja Technologies Network
Author URI: https://nintechnet.com/
License: GPLv3 or later
Network: true
Text Domain: ninjafirewall
Domain Path: /languages
*/

/*
 +---------------------------------------------------------------------+
 | NinjaFirewall (WP Edition)                                          |
 |                                                                     |
 | (c) NinTechNet - https://nintechnet.com/                            |
 +---------------------------------------------------------------------+
*/
define( 'NFW_ENGINE_VERSION', '4.0.5' );
/*
 +---------------------------------------------------------------------+
 | This program is free software: you can redistribute it and/or       |
 | modify it under the terms of the GNU General Public License as      |
 | published by the Free Software Foundation, either version 3 of      |
 | the License, or (at your option) any later version.                 |
 |                                                                     |
 | This program is distributed in the hope that it will be useful,     |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of      |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the       |
 | GNU General Public License for more details.                        |
 +---------------------------------------------------------------------+
*/

if (! defined( 'ABSPATH' ) ) { die( 'Forbidden' ); }

/* ------------------------------------------------------------------ */

// Load (force) our translation files.
$nf_locale = array( 'fr_FR' );
$this_locale = get_locale();
if ( in_array( $this_locale, $nf_locale ) ) {
	if ( file_exists( __DIR__ . "/languages/ninjafirewall-{$this_locale}.mo" ) ) {
		unload_textdomain( 'ninjafirewall' );
		load_textdomain( 'ninjafirewall', __DIR__ . "/languages/ninjafirewall-{$this_locale}.mo" );
	}
}
/* ------------------------------------------------------------------ */

$null = __('A true Web Application Firewall to protect and secure WordPress.', 'ninjafirewall');
define('NFW_NULL_BYTE', 2);
define('NFW_SCAN_BOTS', 531);
define('NFW_ASCII_CTRL', 500);
define('NFW_DOC_ROOT', 510);
define('NFW_WRAPPERS', 520);
define('NFW_OBJECTS', 525);
define('NFW_LOOPBACK', 540);
$message = '<br /><br /><br /><br /><center>' .
		sprintf( __('Sorry %s, your request cannot be processed.', 'ninjafirewall'), '<b>%%REM_ADDRESS%%</b>') .
		'<br />' . __('For security reasons, it was blocked and logged.', 'ninjafirewall') .
		'<br /><br />%%NINJA_LOGO%%<br /><br />' .
		__('If you believe this was an error please contact the<br />webmaster and enclose the following incident ID:', 'ninjafirewall') .
		'<br /><br />[ <b>#%%NUM_INCIDENT%%</b> ]</center>';
define( 'NFW_DEFAULT_MSG', $message );
$err_fw = array(
	1	=> __('Cannot find WordPress configuration file', 'ninjafirewall'),
	2	=>	__('Cannot read WordPress configuration file', 'ninjafirewall'),
	3	=>	__('Cannot retrieve WordPress database credentials', 'ninjafirewall'),
	4	=>	__('Cannot connect to WordPress database', 'ninjafirewall'),
	5	=>	__('Cannot retrieve user options from database (#2)', 'ninjafirewall'),
	6	=>	__('Cannot retrieve user options from database (#3)', 'ninjafirewall'),
	7	=>	__('Cannot retrieve user rules from database (#2)', 'ninjafirewall'),
	8	=>	__('Cannot retrieve user rules from database (#3)', 'ninjafirewall'),
	9	=>	__('The firewall has been disabled from the <a href="admin.php?page=nfsubopt">administration console</a>', 'ninjafirewall'),
	10	=> __('Unable to communicate with the firewall. Please check your settings', 'ninjafirewall'),
	11	=>	__('Cannot retrieve user options from database (#1)', 'ninjafirewall'),
	12	=>	__('Cannot retrieve user rules from database (#1)', 'ninjafirewall'),
	13 => sprintf( __("The firewall cannot access its log and cache folders. If you changed the name of WordPress %s or %s folders, you must define NinjaFirewall's built-in %s constant (see %s for more info)", 'ninjafirewall'), '<code>/wp-content/</code>', '<code>/plugins/</code>', '<code>NFW_LOG_DIR</code>', "<a href='https://nintechnet.com/ninjafirewall/wp-edition/help/?htninja' target='_blank'>Path to NinjaFirewall's log and cache directory</a>"),
	14 => __('The PHP msqli extension is missing or not loaded.', 'ninjafirewall'),
	15	=>	__('Cannot retrieve user options from database (#4)', 'ninjafirewall'),
	16	=>	__('Cannot retrieve user rules from database (#4)', 'ninjafirewall'),

);

if (! defined('NFW_LOG_DIR') ) {
	define('NFW_LOG_DIR', WP_CONTENT_DIR);
}
if (! empty($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] != '/' ) {
	$_SERVER['DOCUMENT_ROOT'] = rtrim( $_SERVER['DOCUMENT_ROOT'] , '/' );
}
/* ------------------------------------------------------------------ */

require plugin_dir_path(__FILE__) .'lib/utils.php';
require plugin_dir_path(__FILE__) .'lib/events.php';

if (! defined( 'NFW_REMOTE_ADDR') ) {
	nfw_select_ip();
}

add_action( 'nfwgccron', 'nfw_garbage_collector' );

/* ------------------------------------------------------------------ */			//s1:h0

function nfw_activate() {

	// Warn if the user does not have the 'unfiltered_html' capability:
	if (! current_user_can( 'unfiltered_html' ) ) {
		exit( __('You do not have "unfiltered_html" capability. Please enable it in order to run NinjaFirewall (or make sure you do not have "DISALLOW_UNFILTERED_HTML" in your wp-config.php script).', 'ninjafirewall'));
	}

	nf_not_allowed( 'block', __LINE__ );

	global $wp_version;
	if ( version_compare( $wp_version, '3.3', '<' ) ) {
		exit( sprintf( __('NinjaFirewall requires WordPress 3.3 or greater but your current version is %s.', 'ninjafirewall'), $wp_version) );
	}

	if ( version_compare( PHP_VERSION, '5.3.0', '<' ) ) {
		exit( sprintf( __('NinjaFirewall requires PHP 5.3 or greater but your current version is %s.', 'ninjafirewall'), PHP_VERSION) );
	}

	if (! function_exists('mysqli_connect') ) {
		exit( sprintf( __('NinjaFirewall requires the PHP %s extension.', 'ninjafirewall'), '<code>mysqli</code>') );
	}

	if ( ini_get( 'safe_mode' ) ) {
		exit( __('You have SAFE_MODE enabled. Please disable it, it is deprecated as of PHP 5.3.0 (see http://php.net/safe-mode).', 'ninjafirewall'));
	}

	if ( ( is_multisite() ) && (! current_user_can( 'manage_network' ) ) ) {
		exit( __('You are not allowed to activate NinjaFirewall.', 'ninjafirewall') );
	}

	if ( PATH_SEPARATOR == ';' ) {
		exit( __('NinjaFirewall is not compatible with Microsoft Windows.', 'ninjafirewall') );
	}

	if (! $nfw_options = nfw_get_option( 'nfw_options' ) ) {
		// First time we're running: download the security rules
		// and populate the options:
		require_once __DIR__ .'/lib/install_default.php';
		nfw_load_default_conf();
		// Reload them
		$nfw_options = nfw_get_option( 'nfw_options' );
	}

	$nfw_options['enabled'] = 1;
	nfw_update_option( 'nfw_options', $nfw_options);

	$res = nfw_enable_wpwaf();
	if (! empty( $res ) ){
		exit( $res );
	}

	// Re-enable scheduled scan, if needed
	if (! empty($nfw_options['sched_scan']) ) {
		if ($nfw_options['sched_scan'] == 1) {
			$schedtype = 'hourly';
		} elseif ($nfw_options['sched_scan'] == 2) {
			$schedtype = 'twicedaily';
		} else {
			$schedtype = 'daily';
		}
		if ( wp_next_scheduled('nfscanevent') ) {
			wp_clear_scheduled_hook('nfscanevent');
		}
		wp_schedule_event( time() + 3600, $schedtype, 'nfscanevent');
	}
	// Re-enable auto updates, if needed
	if (! empty($nfw_options['enable_updates']) ) {
		if ($nfw_options['sched_updates'] == 1) {
			$schedtype = 'hourly';
		} elseif ($nfw_options['sched_updates'] == 2) {
			$schedtype = 'twicedaily';
		} else {
			$schedtype = 'daily';
		}
		if ( wp_next_scheduled('nfsecupdates') ) {
			wp_clear_scheduled_hook('nfsecupdates');
		}
		wp_schedule_event( time() + 15, $schedtype, 'nfsecupdates');
	}
	// Re-enable daily report, if needed
	if (! empty($nfw_options['a_52']) ) {
		if ( wp_next_scheduled('nfdailyreport') ) {
			wp_clear_scheduled_hook('nfdailyreport');
		}
		nfw_get_blogtimezone();
		wp_schedule_event( strtotime( date('Y-m-d 00:00:05', strtotime("+1 day")) ), 'daily', 'nfdailyreport');
	}
	// Re-enable the garbage collector:
	wp_schedule_event( time() + 1800, 'hourly', 'nfwgccron' );

	// Re-enable brute-force protection
	if ( file_exists( NFW_LOG_DIR . '/nfwlog/cache/bf_conf_off.php' ) ) {
		rename(NFW_LOG_DIR . '/nfwlog/cache/bf_conf_off.php', NFW_LOG_DIR . '/nfwlog/cache/bf_conf.php');
	}
}

register_activation_hook( __FILE__, 'nfw_activate' );

/* ------------------------------------------------------------------ */

function nfw_deactivate() {

	nf_not_allowed( 'block', __LINE__ );

	$nfw_options = nfw_get_option( 'nfw_options' );
	$nfw_options['enabled'] = 0;

	nfw_disable_wpwaf();

	if ( wp_next_scheduled('nfwgccron') ) {
		wp_clear_scheduled_hook('nfwgccron');
	}
	if ( wp_next_scheduled('nfscanevent') ) {
		wp_clear_scheduled_hook('nfscanevent');
	}
	if ( wp_next_scheduled('nfsecupdates') ) {
		wp_clear_scheduled_hook('nfsecupdates');
	}
	if ( wp_next_scheduled('nfdailyreport') ) {
		wp_clear_scheduled_hook('nfdailyreport');
	}
	if ( file_exists( NFW_LOG_DIR . '/nfwlog/cache/bf_conf.php' ) ) {
		rename(NFW_LOG_DIR . '/nfwlog/cache/bf_conf.php', NFW_LOG_DIR . '/nfwlog/cache/bf_conf_off.php');
	}

	nfw_update_option( 'nfw_options', $nfw_options);

}

register_deactivation_hook( __FILE__, 'nfw_deactivate' );

/* ------------------------------------------------------------------ */
// Load script/style files

function nfw_load_ext( $hook ) {

	// Load the external JS script and CSS:
	// -Single site: to the admin only.
	// -Multi-site: to the superadmin and from the main network admin screen only.
	// -All: only if this is a NinjaFirewall menu page
	if (! current_user_can('activate_plugins') || ! is_main_site() ) { return; }
	if ( stripos( $hook, 'ninjafirewall' ) === false ) { return; }

	if ( strpos ( $hook, 'nfsubwplus' ) !== false ) {
		// Load thickbox JS and CSS (WP only)
		$extra_js = array( 'jquery', 'thickbox' );
		$extra_css = array( 'thickbox' );
	} else {
		$extra_js = array( 'jquery' );
		$extra_css = null;
	}

	wp_enqueue_script(
		'nfw_javascript',
		plugin_dir_url( __FILE__ ) . 'static/ninjafirewall.js',
		$extra_js,
		NFW_ENGINE_VERSION
	);

	// Load Chart.js if we are viewing the statistics page:
	if ( strpos( $hook, 'NinjaFirewall' ) !== false ) {
		wp_enqueue_script(
			'nfw_charts',
			plugin_dir_url( __FILE__ ) . 'static/chart.min.js',
			array( 'jquery' ),
			NFW_ENGINE_VERSION,
			false
		);
	}

	wp_enqueue_style(
		'nfw_style',
		plugin_dir_url( __FILE__ ) .'static/ninjafirewall.css',
		$extra_css,
		NFW_ENGINE_VERSION,
		false
	);

	// Javascript i18n:
	$nfw_js_array = array(

		// Generic
		'restore_default' =>
			esc_js( __('All fields will be restored to their default values and any changes you made will be lost. Continue?', 'ninjafirewall') ),

		// Full WAF/WordPress WAF
		'missing_nonce' =>
			esc_js( __('Missing security nonce, try to reload the page.', 'ninjafirewall') ),
		'missing_httpserver' =>
			esc_js( __('Please select the HTTP server in the list.', 'ninjafirewall') ),

		// Firewall Options
		'restore_warning' =>
			esc_js( __('This action will restore the selected configuration file and will override all your current firewall options, policies and rules. Continue?', 'ninjafirewall') ),

		// Firewall Policies
		'warn_sanitise' =>
			esc_js( __('Any character that is not a letter [a-zA-Z], a digit [0-9], a dot [.], a hyphen [-] or an underscore [_] will be removed from the filename and replaced with the substitution character. Continue?', 'ninjafirewall') ),
		'ssl_warning' =>
			esc_js( __('Ensure that you can access your admin console over HTTPS before enabling this option, otherwise you will lock yourself out of your site. Continue?', 'ninjafirewall') ),

		// File Check
		'del_snapshot' =>
			esc_js( __('Delete the current snapshot ?', 'ninjafirewall') ),

		// Login Protection
		'invalid_char' =>
			esc_js( __('Invalid character.', 'ninjafirewall') ),
		'no_admin' =>
			esc_js( __('"admin" is not acceptable, please choose another user name.', 'ninjafirewall') ),
		'max_char' =>
			esc_js( __('Please enter max 1024 character only.', 'ninjafirewall') ),
		'select_when' =>
			esc_js( __('Select when to enable the login protection.', 'ninjafirewall') ),
		'missing_auth' =>
			esc_js( __('Enter a name and a password for the HTTP authentication.', 'ninjafirewall') ),

		// Firewall Log
		'invalid_key' =>
			esc_js( __('Your public key is not valid.', 'ninjafirewall') ),

		// Live Log
		'no_traffic' =>
			esc_js( __('No traffic yet, please wait', 'ninjafirewall') ),
		'seconds' =>
			' ' . esc_js( __('seconds...', 'ninjafirewall') ),
		'err_unexpected' =>
			esc_js( __('Error: Live Log did not receive the expected response from your server:', 'ninjafirewall') ),
		'error_404' =>
			esc_js( __('Error: URL does not seem to exist (404 Not Found):', 'ninjafirewall') ),
		'log_not_found' =>
			esc_js( __('Error: Cannot find your log file. Try to reload this page.', 'ninjafirewall') ),
		'http_error' =>
			esc_js( __('Error: The HTTP server returned the following error code:', 'ninjafirewall') ),
	);

	wp_localize_script( 'nfw_javascript', 'nfwi18n', $nfw_js_array );
}

add_action( 'admin_enqueue_scripts', 'nfw_load_ext' );

/* ------------------------------------------------------------------ */

function nfw_admin_init() {

	// We must make sure that the current PHP session is always
	// updated even for whitelisted non-admin users:
	nfw_session_start();

	$nfw_options = nfw_get_option( 'nfw_options' );
	$nfw_rules = nfw_get_option( 'nfw_rules' );

	// Post-update adjustment:
	require plugin_dir_path(__FILE__) . 'lib/init_update.php';

	if ( current_user_can( 'edit_pages' ) ) {
		$_SESSION['nfw_user_can'] = 'edit_pages';
	} elseif ( current_user_can( 'edit_posts' ) ) {
		$_SESSION['nfw_user_can'] = 'edit_posts';
	}

	// --------------------------------------------
	// Anything below requires admin authentication
	// --------------------------------------------

	if ( nf_not_allowed(0, __LINE__) ) { return; }

	// Create our unique PID
	$nfw_pid = NFW_LOG_DIR .'/nfwlog/cache/.pid';
	if (! file_exists( $nfw_pid ) ) {
		file_put_contents( $nfw_pid, uniqid('', true) );
	}

	// Update fallback loader if needed
	if ( wp_doing_ajax() == false ) {
		nfw_enable_wpwaf();
	}

	// Export configuration:
	if ( isset($_POST['nf_export']) ) {
		if ( empty($_POST['nfwnonce']) || ! wp_verify_nonce($_POST['nfwnonce'], 'options_save') ) {
			wp_nonce_ays('options_save');
		}
		$nfwbfd_log = NFW_LOG_DIR . '/nfwlog/cache/bf_conf.php';
		if ( file_exists($nfwbfd_log) ) {
			$bd_data = json_encode( file_get_contents($nfwbfd_log) );
		} else {
			$bd_data = '';
		}
		$data = json_encode($nfw_options) . "\n:-:\n" . json_encode($nfw_rules) . "\n:-:\n" . $bd_data;
		header('Content-Type: text/plain');
		header('Content-Length: '. strlen( $data ) );
		header('Content-Disposition: attachment; filename="nfwp.' . NFW_ENGINE_VERSION . '.dat"');
		echo $data;
		exit;
	}

	// Download File Check modified files list:
	if ( isset($_POST['dlmods']) ) {
		if ( empty($_POST['nfwnonce']) || ! wp_verify_nonce($_POST['nfwnonce'], 'filecheck_save') ) {
			wp_nonce_ays('filecheck_save');
		}
		if (file_exists(NFW_LOG_DIR . '/nfwlog/cache/nfilecheck_diff.php') ) {
			$download_file = NFW_LOG_DIR . '/nfwlog/cache/nfilecheck_diff.php';
		} elseif (file_exists(NFW_LOG_DIR . '/nfwlog/cache/nfilecheck_diff.php.php') ) {
			$download_file = NFW_LOG_DIR . '/nfwlog/cache/nfilecheck_diff.php.php';
		} else {
			wp_nonce_ays('filecheck_save');
		}
		$stat = stat($download_file);
		$data = '== NinjaFirewall File Check (diff)'. "\n";
		$data.= '== ' . site_url() . "\n";
		$data.= '== ' . date_i18n('M d, Y @ H:i:s O', $stat['ctime']) . "\n\n";
		$data.= '[+] = ' . __('New file', 'ninjafirewall') .
					'      [!] = ' . __('Modified file', 'ninjafirewall') .
					'      [-] = ' . __('Deleted file', 'ninjafirewall') .
					"\n\n";
		$fh = fopen($download_file, 'r');
		while (! feof($fh) ) {
			$res = explode('::', fgets($fh) );
			if ( empty($res[1]) ) { continue; }
			if ($res[1] == 'N') {
				$data .= '[+] ' . $res[0] . "\n";
			} elseif ($res[1] == 'D') {
				$data .= '[-] ' . $res[0] . "\n";
			} elseif ($res[1] == 'M') {
				$data .= '[!] ' . $res[0] . "\n";
			}
		}
		fclose($fh);
		$data .= "\n== EOF\n";

		header('Content-Type: text/plain');
		header('Content-Length: '. strlen( $data ) );
		header('Content-Disposition: attachment; filename="'. $_SERVER['SERVER_NAME'] .'_diff.txt"');
		echo $data;
		exit;
	}

	// Download File Check snapshot:
	if ( isset($_POST['dlsnap']) ) {
		if ( empty($_POST['nfwnonce']) || ! wp_verify_nonce($_POST['nfwnonce'], 'filecheck_save') ) {
			wp_nonce_ays('filecheck_save');
		}
		if (file_exists(NFW_LOG_DIR . '/nfwlog/cache/nfilecheck_snapshot.php') ) {
			$stat = stat(NFW_LOG_DIR . '/nfwlog/cache/nfilecheck_snapshot.php');
			$data = '== NinjaFirewall File Check (snapshot)'. "\n";
			$data.= '== ' . site_url() . "\n";
			$data.= '== ' . date_i18n('M d, Y @ H:i:s O', $stat['ctime']) . "\n\n";
			$fh = fopen(NFW_LOG_DIR . '/nfwlog/cache/nfilecheck_snapshot.php', 'r');
			while (! feof($fh) ) {
				$res = explode('::', fgets($fh) );
				if (! empty($res[0][0]) && $res[0][0] == '/') {
					$data .= $res[0] . "\n";
				}
			}
			fclose($fh);
			$data .= "\n== EOF\n";
			header('Content-Type: text/plain');
			header('Content-Length: '. strlen( $data ) );
			header('Content-Disposition: attachment; filename="'. $_SERVER['SERVER_NAME'] .'_snapshot.txt"');
			echo $data;
			exit;
		} else {
			wp_nonce_ays('filecheck_save');
		}
	}

	// Applies to admin only (unlike the WP+ Edition):
	if (! empty( $nfw_options['wl_admin'] ) ) {
		$_SESSION['nfw_goodguy'] = true;
		if (! empty( $nfw_options['bf_enable'] ) && ! empty( $nfw_options['bf_rand'] ) ) {
			$_SESSION['nfw_bfd'] = $nfw_options['bf_rand'];
		}
		return;
	}
	if ( isset( $_SESSION['nfw_goodguy'] ) ) {
		unset( $_SESSION['nfw_goodguy'] );
	}
}

add_action('admin_init', 'nfw_admin_init' );

/* ------------------------------------------------------------------ */
// Check if the user is an admin and if we must whitelist them.

function nfw_login_hook( $user_login, $user ) {

	nfw_session_start();

	$nfw_options = nfw_get_option( 'nfw_options' );

	// Don't do anything if NinjaFirewall is disabled:
	if ( empty( $nfw_options['enabled'] ) ) { return; }

	// Fetch user roles:
	$whoami = '';
	foreach( $user->roles as $k => $v ) {
		if ( $v == 'administrator' ) {
			$admin_flag = 1;
		}
		$whoami .= "$v ";
	}
	$whoami = trim( $whoami );

	// Still nothing: Maybe an additional superadmin
	if ( empty( $whoami ) && is_multisite() ) {
		// $user->ID is required here
		if ( is_super_admin( $user->ID ) ) {
			$admin_flag = 1;
			$whoami = 'administrator';
		}
	}

	// Are we supposed to send an alert?
	if (! empty($nfw_options['a_0']) ) {
		if ( ( $nfw_options['a_0'] == 1 && isset( $admin_flag ) ) || $nfw_options['a_0'] == 2 ) {
			nfw_send_loginemail( $user_login, $whoami );
			// Write event to log?
			if (! empty($nfw_options['a_41']) ) {
				nfw_log2('Logged in user', "{$user_login} ({$whoami})", 6, 0);
			}
		}
	}

	//Whitelist:
	if (! empty( $nfw_options['wl_admin']) ) {
		if ( ( $nfw_options['wl_admin'] == 1 && isset( $admin_flag ) ) || $nfw_options['wl_admin'] == 2 ) {
			// Set the goodguy flag:
			$_SESSION['nfw_goodguy'] = 1;
			return;
		}
	}

	// Clear the flag, this user isn't whitelisted:
	if ( isset( $_SESSION['nfw_goodguy'] ) ) {
		unset( $_SESSION['nfw_goodguy'] );
	}
}

add_action( 'wp_login', 'nfw_login_hook', 999, 2 );

/* ------------------------------------------------------------------ */

function nfw_logout_hook() {

	nfw_session_start();

	if ( isset( $_SESSION['nfw_goodguy'] ) ) {
		unset( $_SESSION['nfw_goodguy'] );
	}
	if (isset( $_SESSION['nfw_livelog'] ) ) {
		unset( $_SESSION['nfw_livelog'] );
	}
	if (isset( $_SESSION['nfw_user_can'] ) ) {
		unset( $_SESSION['nfw_user_can'] );
	}
}

add_action( 'wp_logout', 'nfw_logout_hook' );

/* ------------------------------------------------------------------ */
// FullWAF upgrade AJAX function.

add_action( 'wp_ajax_nfw_fullwafsetup', 'nfw_fullwafsetup' );

function nfw_fullwafsetup() {

	nf_not_allowed( 'block', __LINE__ );

	if (! check_ajax_referer( 'events_save', 'nonce', false ) ) {
		_e('Error: Security nonces do not match. Reload the page and try again.', 'ninjafirewall');
		wp_die();
	}

	$nfw_options = nfw_get_option( 'nfw_options' );
	if ( empty( $nfw_options['enabled'] ) ) {
		_e('Error: NinjaFirewall is disabled', 'ninjafirewall');
		wp_die();
	}

	if ( empty( $_POST['httpserver'] ) ) {
		printf( __('Error: missing parameter (%s).', 'ninjafirewall'), 'httpserver' );
		wp_die();
	}
	if ( preg_match('/^[^1-7]$/', $_POST['httpserver'] ) ) {
		printf( __('Error: wrong parameter value (%s).', 'ninjafirewall'), 'httpserver' );
		wp_die();
	}
	if ( empty( $_POST['diy'] ) || ! preg_match( '/^(nfw|usr)$/', $_POST['diy'] ) ) {
		printf( __('Error: wrong parameter value (%s).', 'ninjafirewall'), 'diy' );
		wp_die();
	}

	$time = time() + 300;

	// 1: Apache mod_php
	// 2: Apache + CGI/FastCGI or PHP-FPM
	// 3: Apache + suPHP
	// 4: Nginx + CGI/FastCGI or PHP-FPM
	// 5: Litespeed
	// 6: Openlitespeed
	//7: Other webserver + CGI/FastCGI or PHP-FPM
	$httpserver = (int) $_POST['httpserver'];

	// [6] Openlitespeed: nothing to do.
	if ( $httpserver == 6 ) {
		set_transient( 'nfw_fullwaf', "{$httpserver}:{$time}", 60 * 5 );
		echo '200';
		wp_die();
	}

	require_once __DIR__ .'/lib/install.php';

	// .htaccess mods only
	if ( $httpserver == 1 || $httpserver == 5 ) {
		// User wants to make the modification
		if ( $_POST['diy'] == 'usr' ) {
			// Nothing to do
			set_transient( 'nfw_fullwaf', "{$httpserver}:{$time}", 60 * 5 );
			echo '200';
			wp_die();
		}
		// Make changes
		$ret = nfw_fullwaf_htaccess( $httpserver );
		if ( $ret !== true ) {
			echo $ret;
		} else {
			set_transient( 'nfw_fullwaf', "{$httpserver}:{$time}", 60 * 5 );
			echo '200';
		}
		wp_die();
	}

	if ( $_POST['diy'] == 'usr' ) {
		// Nothing to do, but add 5-minute notice to the overview page
		// because an INI file is being used
		set_transient( 'nfw_fullwaf', "{$httpserver}:{$time}", 60 * 5 );
		echo '200';
		wp_die();
	}

	// [1] .user.ini
	// [2] php.ini
	if ( empty ( $_POST['initype'] ) || ! preg_match( '/^[12]$/', $_POST['initype'] ) ) {
		$initype = 1;
	} else {
		$initype = (int) $_POST['initype'];
	}

	if ( $httpserver == 3 ) { // Apache + suPHP
		// Set up the htaccess file
		$ret = nfw_fullwaf_htaccess( $httpserver );
		if ( $ret !== true ) {
			echo $ret;
			wp_die();
		}
	}
	// ini file
	$ret = nfw_fullwaf_ini( $httpserver, $initype );
	if ( $ret !== true ) {
		echo $ret;
		wp_die();
	} else {
		// Add 5-minute notice to the overview page
		// because an INI file is being used
		set_transient( 'nfw_fullwaf', "{$httpserver}:{$time}", 60 * 5 );
		echo 200;
	}
	wp_die();
}

/* ------------------------------------------------------------------ */

function is_nfw_enabled() {

	$nfw_options = nfw_get_option( 'nfw_options' );

	if (! defined('NFW_STATUS') ) {
		define('NF_DISABLED', 10);
		return;
	}

	if ( isset($nfw_options['enabled']) && $nfw_options['enabled'] == '0' ) {
		define('NF_DISABLED', 9);
		return;
	}

	if (NFW_STATUS == 21 || NFW_STATUS == 22 || NFW_STATUS == 23) {
		define('NF_DISABLED', 10);
		return;
	}

	// OK
	if (NFW_STATUS == 20) {
		define('NF_DISABLED', 0);
		return;
	}

	define('NF_DISABLED', NFW_STATUS);
	return;

}

/* ------------------------------------------------------------------ */

function ninjafirewall_admin_menu() {

	if ( nf_not_allowed( 0, __LINE__ ) ) { return; }

	if (! empty($_REQUEST['nfw_act']) && $_REQUEST['nfw_act'] == 99) {
		if ( empty($_GET['nfwnonce']) || ! wp_verify_nonce($_GET['nfwnonce'], 'show_phpinfo') ) {
			wp_nonce_ays('show_phpinfo');
		}
		phpinfo(33);
		exit;
	}

	add_menu_page( 'NinjaFirewall', 'NinjaFirewall', 'manage_options',
		'NinjaFirewall', 'nf_sub_main',	plugins_url( '/images/nf_icon.png', __FILE__ )
	);

	global $menu_hook;

	require_once plugin_dir_path(__FILE__) . 'lib/help.php';

	$menu_hook = add_submenu_page( 'NinjaFirewall', __('NinjaFirewall: Dashboard', 'ninjafirewall'), __('Dashboard', 'ninjafirewall'), 'manage_options',
		'NinjaFirewall', 'nf_sub_main' );
	add_action( 'load-' . $menu_hook, 'help_nfsubmain' );

	$menu_hook = add_submenu_page( 'NinjaFirewall', __('NinjaFirewall: Firewall Options', 'ninjafirewall'), __('Firewall Options', 'ninjafirewall'), 'manage_options',
		'nfsubopt', 'nf_sub_options' );
	add_action( 'load-' . $menu_hook, 'help_nfsubopt' );

	$menu_hook = add_submenu_page( 'NinjaFirewall', __('NinjaFirewall: Firewall Policies', 'ninjafirewall'), __('Firewall Policies', 'ninjafirewall'), 'manage_options',
		'nfsubpolicies', 'nf_sub_policies' );
	add_action( 'load-' . $menu_hook, 'help_nfsubpolicies' );

	$menu_hook = add_submenu_page( 'NinjaFirewall',  __('NinjaFirewall: Monitoring', 'ninjafirewall'), __( 'Monitoring', 'ninjafirewall'), 'manage_options',
		'nfsubfileguard', 'nf_sub_monitoring' );
	add_action( 'load-' . $menu_hook, 'help_nfsubfileguard' );

	$nscan_options = get_option( 'nscan_options' );
	if ( defined('NSCAN_NAME') && defined('NSCAN_SLUG') && ! empty( $nscan_options['scan_nfwpintegration'] ) ) {
		$menu_hook = add_submenu_page( 'NinjaFirewall', NSCAN_NAME, NSCAN_NAME, 'manage_options', NSCAN_NAME, 'nscan_main_menu' );
		require_once dirname( __DIR__ ).'/'. NSCAN_SLUG .'/lib/help.php';
		add_action( 'load-' . $menu_hook, 'nscan_help' );
	} else {
		$menu_hook = add_submenu_page( 'NinjaFirewall', __('NinjaFirewall: Anti-Malware', 'ninjafirewall'), __('Anti-Malware', 'ninjafirewall'), 'manage_options',
		'nfsubmalwarescan', 'nf_sub_malwarescan' );
	}

	$menu_hook = add_submenu_page( 'NinjaFirewall', __('NinjaFirewall: Network', 'ninjafirewall'), __('Network', 'ninjafirewall'), 'manage_network',
		'nfsubnetwork', 'nf_sub_network' );
	add_action( 'load-' . $menu_hook, 'help_nfsubnetwork' );

	$menu_hook = add_submenu_page( 'NinjaFirewall', __('NinjaFirewall: Event Notifications', 'ninjafirewall'), __('Event Notifications', 'ninjafirewall'), 'manage_options',
		'nfsubevent', 'nf_sub_event' );
	add_action( 'load-' . $menu_hook, 'help_nfsubevent' );

	$menu_hook = add_submenu_page( 'NinjaFirewall', __('NinjaFirewall: Log-in Protection', 'ninjafirewall'), __('Login Protection', 'ninjafirewall'), 'manage_options',
		'nfsubloginprot', 'nf_sub_loginprot' );
	add_action( 'load-' . $menu_hook, 'help_nfsublogin' );

	$menu_hook = add_submenu_page( 'NinjaFirewall', __('NinjaFirewall: Logs', 'ninjafirewall'), __('Logs', 'ninjafirewall'), 'manage_options',
		'nfsublog', 'nf_sub_log' );
	add_action( 'load-' . $menu_hook, 'help_nfsublog' );

	$menu_hook = add_submenu_page( 'NinjaFirewall', __('NinjaFirewall: Security Rules', 'ninjafirewall'), __('Security Rules', 'ninjafirewall'), 'manage_options',
		'nfsubupdates', 'nf_sub_updates' );
	add_action( 'load-' . $menu_hook, 'help_nfsubupdates' );

	$menu_hook = add_submenu_page( 'NinjaFirewall', 'NinjaFirewall: WP+ Edition', '<b style="color:#fcdc25">WP+ Edition</b>', 'manage_options',
		'nfsubwplus', 'nf_sub_wplus' );

}
// Must load before NinjaScanner (11):
if (! is_multisite() )  {
	add_action( 'admin_menu', 'ninjafirewall_admin_menu', 10 );
} else {
	add_action( 'network_admin_menu', 'ninjafirewall_admin_menu', 10 );
}

/* ------------------------------------------------------------------ */

function nf_admin_bar_status() {

	if (! current_user_can( 'manage_options' ) ) {
		return;
	}

	$nfw_options = nfw_get_option( 'nfw_options' );
	if ( @$nfw_options['nt_show_status'] != 1 && ! current_user_can('manage_network') ) {
		return;
	}

	if (! defined('NF_DISABLED') ) {
		is_nfw_enabled();
	}
	if (NF_DISABLED) { return; }

	global $wp_admin_bar;
	$wp_admin_bar->add_menu( array(
		'id'    => 'nfw_ntw1',
		'title' => '<img src="' . plugins_url() . '/ninjafirewall/images/ninjafirewall_20.png" ' .
				'style="vertical-align:middle;margin-right:5px" />',
	) );

	if ( current_user_can( 'manage_network' ) ) {
		$wp_admin_bar->add_menu( array(
			'parent' => 'nfw_ntw1',
			'id'     => 'nfw_ntw2',
			'title'  => __( 'NinjaFirewall Settings', 'ninjafirewall'),
			'href'   => network_admin_url() . 'admin.php?page=NinjaFirewall',
		) );
	} else {
		if ( defined('NFW_STATUS') ) {
			$wp_admin_bar->add_menu( array(
				'parent' => 'nfw_ntw1',
				'id'     => 'nfw_ntw2',
				'title'  => __( 'NinjaFirewall is enabled', 'ninjafirewall'),
			) );
		}
	}
}

if ( is_multisite() )  {
	add_action('admin_bar_menu', 'nf_admin_bar_status', 95);
}

/* ------------------------------------------------------------------ */

function nf_sub_main() {

	// Main menu (Overview)
//	require plugin_dir_path(__FILE__) . 'lib/overview.php';
	require plugin_dir_path(__FILE__) . 'lib/dashboard.php';

}

/* ------------------------------------------------------------------ */

function nf_sub_options() { // i18n

	require plugin_dir_path(__FILE__) . 'lib/firewall_options.php';

}

/* ------------------------------------------------------------------ */

function nf_sub_policies() {

	// Firewall Policies menu
	require plugin_dir_path(__FILE__) . 'lib/firewall_policies.php';

}

/* ------------------------------------------------------------------ */

function nf_sub_monitoring() {

	require plugin_dir_path(__FILE__) . 'lib/monitoring.php';

}
add_action('nfscanevent', 'nfscando');

function nfscando() {

	define('NFSCANDO', 1);
	nf_sub_monitoring();
}

/* ------------------------------------------------------------------ */

function nf_sub_network() {

	// Network menu (multi-site only)
	require plugin_dir_path(__FILE__) . 'lib/network.php';

}

/* ------------------------------------------------------------------ */

function nf_sub_malwarescan() {

	require plugin_dir_path(__FILE__) . 'lib/anti_malware.php';

}

/* ------------------------------------------------------------------ */

function nf_sub_event() {

	require plugin_dir_path(__FILE__) . 'lib/event_notifications.php';

}

add_action('shutdown', 'nf_check_dbdata', 1);

add_action('nfdailyreport', 'nfdailyreportdo');

function nfdailyreportdo() {
	define('NFREPORTDO', 1);
	nf_sub_event();
}

/* ------------------------------------------------------------------ */

function nf_sub_log() {

	require plugin_dir_path(__FILE__) . 'lib/logs.php';

}

/* ------------------------------------------------------------------ */

function nf_sub_loginprot() {

	require plugin_dir_path(__FILE__) . 'lib/login_protection.php';

}

/* ------------------------------------------------------------------ */

function nfw_log2($loginfo, $logdata, $loglevel, $ruleid) {

	// Write incident to the firewall log
	require plugin_dir_path(__FILE__) . 'lib/nfw_log.php';

}

/* ------------------------------------------------------------------ */

function nf_sub_updates() {

	require plugin_dir_path(__FILE__) . 'lib/security_rules.php';

}

add_action('nfsecupdates', 'nfupdatesdo');

function nfupdatesdo() {
	define('NFUPDATESDO', 1);
	nf_sub_updates();
}

/* ------------------------------------------------------------------ */

function nf_sub_wplus() {

	require plugin_dir_path(__FILE__) . 'lib/wpplus.php';
}

/* ------------------------------------------------------------------ */

function ninjafirewall_settings_link( $links ) {

	// Check if access is restricted to one or more specific admins
	// See: https://blog.nintechnet.com/restricting-access-to-ninjafirewall-wp-edition-settings/
	if ( nf_not_allowed( 0, __LINE__ ) ) {
		unset( $links );
		$links[] = __('Access Restricted', 'ninjafirewall');
		return $links;
	}

	if ( is_multisite() ) {	$net = 'network/'; } else { $net = '';	}

	$links[] = '<a href="'. get_admin_url(null, $net .'admin.php?page=NinjaFirewall') .'">'. __('Settings', 'ninjafirewall') .'</a>';
	$links[] = '<a href="https://nintechnet.com/ninjafirewall/wp-edition/?pricing" target="_blank">'. __('Upgrade to Premium', 'ninjafirewall'). '</a>';
	$links[] = '<a href="https://wordpress.org/support/view/plugin-reviews/ninjafirewall?rate=5#postform" target="_blank">'. __('Rate it!', 'ninjafirewall'). '</a>';
	unset( $links['edit'] );
   return $links;

}

if ( is_multisite() ) {
	add_filter( 'network_admin_plugin_action_links_' . plugin_basename(__FILE__), 'ninjafirewall_settings_link' );
} else {
	add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'ninjafirewall_settings_link' );
}

/* ------------------------------------------------------------------ */

function nfw_dashboard_widgets() {

	require plugin_dir_path(__FILE__) . 'lib/widget.php';

}

if ( is_multisite() ) {
	add_action( 'wp_network_dashboard_setup', 'nfw_dashboard_widgets' );
} else {
	add_action( 'wp_dashboard_setup', 'nfw_dashboard_widgets' );
}

/* ------------------------------------------------------------------ */

function nf_not_allowed($block, $line = 0) {

	if ( is_multisite() ) {
		if ( current_user_can('manage_network') ) {
			return false;
		}
	} else {
		if ( current_user_can('manage_options') &&
		     current_user_can('unfiltered_html') ) {
			// Check if that admin is allowed to use NinjaFirewall
			// (see NFW_ALLOWED_ADMIN at http://nin.link/nfwaa ):
			if ( defined('NFW_ALLOWED_ADMIN') ) {
				$current_user = wp_get_current_user();
				$admins = explode(',', NFW_ALLOWED_ADMIN );
				foreach ( $admins as $admin ) {
					if ( trim( $admin ) == $current_user->user_login ) {
						return false;
					}
				}
			} else {
				return false;
			}
		}
	}

	if ( $block ) {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			// Format text for WP-CLI:
			WP_CLI::error(
				sprintf( __('You are not allowed to perform this task (%s).', 'ninjafirewall'), $line)
			);
		} else {
			die( '<br /><br /><br /><div class="error notice is-dismissible"><p>' .
				sprintf( __('You are not allowed to perform this task (%s).', 'ninjafirewall'), $line) .
				'</p></div>' );
		}
	}
	return true;
}

/* ------------------------------------------------------------------ */
// EOF //
