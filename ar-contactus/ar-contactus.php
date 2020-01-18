<?php
/**
 * @package ArContactUs
 */
/*
Plugin Name: Contact Us all-in-one button
Plugin URI: https://plugins.areama.net/ar-contactus/docs/
Description: Display contact us button with menu on every page. Callback request, reCaptcha V3 protection and many customizations!
Version: 1.3.8
Author: Areama
Author URI: https://areama.net/
License: GPLv2 or later
Text Domain: ar-contactus
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2005-2015 Automattic, Inc.
*/

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    die("Hi there!  I'm just a plugin, not much I can do when called directly.");
}

define('AR_CONTACTUS_VERSION', '1.3.8');
define('AR_CONTACTUS_MINIMUM_WP_VERSION', '3.7');
define('AR_CONTACTUS_DEBUG', false);
define('AR_CONTACTUS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AR_CONTACTUS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AR_CONTACTUS_PLUGIN_DIR_CLASSES', plugin_dir_path(__FILE__) . 'classes/');
define('AR_CONTACTUS_PLUGIN_DIR_MODELS', plugin_dir_path(__FILE__) . 'models/');
define('AR_CONTACTUS_PLUGIN_DIR_CONTROLLERS', plugin_dir_path(__FILE__) . 'controllers/');
define('AR_CONTACTUS_TEXT_DOMAIN', 'ar-contactus');

require_once(AR_CONTACTUS_PLUGIN_DIR_CLASSES . 'ArContactUsLoader.php');
require_once(AR_CONTACTUS_PLUGIN_DIR . 'functions.php');

ArContactUsLoader::loadClass('ArContactUs');
ArContactUsLoader::loadClass('ArContactUsTools');
$arContactUs = new ArContactUs();

register_activation_hook(__FILE__, array($arContactUs, 'activate'));
register_deactivation_hook(__FILE__, array($arContactUs, 'deactivate'));
register_uninstall_hook(__FILE__, 'arContactUsUninstall');

add_action('plugins_loaded', array($arContactUs, 'init'));

if (is_admin() || (defined('WP_CLI') && WP_CLI)){
    ArContactUsLoader::loadClass('ArContactUsAdmin');
    $arContactUsAdmin = new ArContactUsAdmin();
    add_action('init', array($arContactUsAdmin, 'init'));
}

function arContactUsUninstall()
{
    ArContactUsLoader::loadModel('ArContactUsConfigGeneral');
    ArContactUsLoader::loadModel('ArContactUsConfigButton');
    ArContactUsLoader::loadModel('ArContactUsConfigMenu');
    ArContactUsLoader::loadModel('ArContactUsConfigPopup');
    ArContactUsLoader::loadModel('ArContactUsConfigPrompt');
    ArContactUsLoader::loadModel('ArContactUsModel');
    ArContactUsLoader::loadModel('ArContactUsCallbackModel');
    ArContactUsLoader::loadModel('ArContactUsPromptModel');

    $generalConfig = new ArContactUsConfigGeneral('arcug_');
    $buttonConfig = new ArContactUsConfigButton('arcub_');
    $menuConfig = new ArContactUsConfigMenu('arcum_');
    $popupConfig = new ArContactUsConfigPopup('arcup_');
    $promptConfig = new ArContactUsConfigPrompt('arcupr_');
    
    $generalConfig->clearConfig();
    $buttonConfig->clearConfig();
    $popupConfig->clearConfig();
    $menuConfig->clearConfig();
    $promptConfig->clearConfig();
    
    delete_option('arcu_installed');
    ArContactUsModel::dropTable();
    ArContactUsCallbackModel::dropTable();
    ArContactUsPromptModel::dropTable();
}