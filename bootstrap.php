<?php
/**
 * Plugin Name: Automizy Lead Generation
 * Plugin URI: http://wordpress-plugin.automizy.com/
 * Description: This plugin adds lead generation features to your WP websites.
 * Version: 1.0.0
 * Author: Gabor Koncz
 * Author URI: https://twitter.com/gkoncz77
 * License: GPLv2 or later
 */

require __DIR__ . '/vendor/autoload.php';
$automizyApp = new \Automizy\AutomizyApp();
