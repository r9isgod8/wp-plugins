<?php
/*
 * Plugin Name: FS Poster
 * Description: Facebook, Twitter , Instagram, Linkedin, Reddit, Tumblr, VK, Pinterest, OK.ru, Google My Business, Telegram, Medium Auto Poster Plugin. Post WooCommerce products. Schedule your posts i.e
 * Version: 3.4.2
 * Author: FS-Code
 * Author URI: https://www.fs-code.com
 * License: Commercial
 * Text Domain: fs-poster
 */

namespace FSPoster;

use FSPoster\App\Providers\Bootstrap;

defined( 'ABSPATH' ) or exit;

require_once __DIR__ . '/vendor/autoload.php';

new Bootstrap();
