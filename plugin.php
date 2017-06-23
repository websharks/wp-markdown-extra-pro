<?php
/**
 * Plugin.
 *
 * @wp-plugin
 *
 * Version: 170623.22001
 * Text Domain: wp-markdown-extra
 * Plugin Name: WP Markdown Extra Pro
 *
 * Author: WP Sharks™
 * Author URI: https://wpsharks.com
 *
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * Plugin URI: https://wpsharks.com/product/wp-markdown-extra-pro
 * Description: WP Sharks™ plugin wp-markdown-extra.
 */
// PHP v5.2 compatible.

if (!defined('WPINC')) {
    exit('Do NOT access this file directly.');
}
require dirname(__FILE__).'/src/includes/wp-php-rv.php';

if (require(dirname(__FILE__).'/src/vendor/websharks/wp-php-rv/src/includes/check.php')) {
    require_once dirname(__FILE__).'/src/includes/plugin.php';
} else {
    wp_php_rv_notice('WP Markdown Extra Pro');
}
