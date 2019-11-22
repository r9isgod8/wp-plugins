<?php
/**
 * The main file of the Flying Pages
 *
 * Plugin Name: Flying Pages
 * Plugin URI: https://wordpress.org/plugins/flying-pages/
 * Description: Load inner pages instantly, intelligently!
 * Author: Gijo Varghese
 * Author URI: https://wpspeedmatters.com/
 * Version: 2.1.2
 * Text Domain: flying-pages
 */

// If this file is called directly, abort.
if (! defined('WPINC')) die;

// Define constant with current version
if (!defined('FLYING_PAGES_VERSION'))
    define('FLYING_PAGES_VERSION', '2.1.2');

// Set default config on plugin load if not set
function flying_pages_set_default_config() {
    if (FLYING_PAGES_VERSION !== get_option('FLYING_PAGES_VERSION')) {
        if (get_option('flying_pages_config_delay') === false)
            update_option('flying_pages_config_delay', 0);
        if (get_option('flying_pages_config_ignore_keywords') === false)
            update_option('flying_pages_config_ignore_keywords', ['/wp-admin','/wp-login.php','/cart','add-to-cart','logout','#','?','.png','.jpeg','.jpg','.gif','.svg']);
        if (get_option('flying_pages_config_max_rps') === false)
            update_option('flying_pages_config_max_rps', 3);
        if (get_option('flying_pages_config_hover_delay') === false)
            update_option('flying_pages_config_hover_delay', 50);
        if (get_option('flying_pages_config_disable_on_login') === false)
            update_option('flying_pages_config_disable_on_login', false);
        update_option('FLYING_PAGES_VERSION', FLYING_PAGES_VERSION);
    }
}
add_action('plugins_loaded', 'flying_pages_set_default_config');

// Register settings menu
function flying_pages_register_settings_menu() {
    add_options_page('Flying Pages', 'Flying Pages', 'manage_options', 'flying-pages', 'flying_pages_settings_view');
}
add_action('admin_menu', 'flying_pages_register_settings_menu');

// Settings page
function flying_pages_settings_view() {
    // Validate nonce
    if(isset($_POST['submit']) && !wp_verify_nonce($_POST['flying_pages_settings_form'], 'flying_pages')) {
        echo '<div class="notice notice-error"><p>Nonce verification failed</p></div>';
        exit;
    }

    // Update config in database after form submission
    if (isset($_POST['submit'])) {

        $keywords = array_map('trim', explode("\n", str_replace("\r", "", $_POST['ignore_keywords'])));
        
        update_option('flying_pages_config_ignore_keywords', $keywords);
        update_option('flying_pages_config_delay', $_POST['delay']);
        update_option('flying_pages_config_max_rps', $_POST['max_rps']);
        update_option('flying_pages_config_hover_delay', $_POST['hover_delay']);
        update_option('flying_pages_config_disable_on_login', $_POST['disable_on_login']);
    }

    // Get config from db for displaying in the form
    $delay = get_option('flying_pages_config_delay');
    $ignore_keywords = get_option('flying_pages_config_ignore_keywords');
    $max_rps = get_option('flying_pages_config_max_rps');
    $hover_delay = get_option('flying_pages_config_hover_delay');
    $disable_on_login = get_option('flying_pages_config_disable_on_login');
    
    // Settings form
    include 'settings-form.php';
}

// Add links in plugins list
function flying_pages_add_action_links($links) {
    $plugin_shortcuts = array(
        '<a href="'.admin_url('options-general.php?page=flying-pages').'">Settings</a>',
        '<a href="https://www.buymeacoffee.com/gijovarghese" target="_blank" style="color:#3db634;">Buy developer a coffee</a>'
    );
    return array_merge($links, $plugin_shortcuts);
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'flying_pages_add_action_links');


// Embed the scripts we need for this plugin
function flying_pages_enqueue_scripts() {
    
    // Disable for logged admins
    if(get_option('flying_pages_config_disable_on_login') && current_user_can( 'manage_options' )) return;

    // Abort if the response is AMP since custom JavaScript isn't allowed
    if (function_exists('is_amp_endpoint') && is_amp_endpoint()) return;

    wp_enqueue_script('flying-pages', plugin_dir_url(__FILE__) . 'flying-pages.min.js', array(), FLYING_PAGES_VERSION, true);
    wp_add_inline_script(
        'flying-pages',
'window.addEventListener("load", () => {
    flyingPages({
        delay: '.get_option('flying_pages_config_delay').',
        ignoreKeywords: '.json_encode(get_option('flying_pages_config_ignore_keywords'), true).',
        maxRPS: '.get_option('flying_pages_config_max_rps').',
        hoverDelay: '.get_option('flying_pages_config_hover_delay').'
    });
});',
        "after"
    );
}
add_action('wp_enqueue_scripts', 'flying_pages_enqueue_scripts');

// Add defer attribute to Flying Pages script tag
function flying_pages_add_defer($tag, $handle) {
    if ('flying-pages' === $handle && false === strpos($tag, 'defer')) {
        $tag = preg_replace(':(?=></script>):', ' defer', $tag);
    }
    return $tag;
}
add_filter('script_loader_tag', 'flying_pages_add_defer', 10, 2);
