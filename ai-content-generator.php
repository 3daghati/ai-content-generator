<?php
/**
 * Plugin Name: AI Content Generator for WooCommerce
 * Description: Advanced AI-powered content generator for WooCommerce products using DeepSeek AI
 * Version: 1.0.0
 * Author: 3daghati
 * Text Domain: ai-content-generator
 * Domain Path: /languages
 * Requires PHP: 7.4
 * Requires at least: 5.8
 * WC requires at least: 6.0
 */

defined('ABSPATH') || exit;

// Define constants
define('AI_CONTENT_GENERATOR_VERSION', '1.0.0');
define('AI_CONTENT_GENERATOR_PATH', plugin_dir_path(__FILE__));
define('AI_CONTENT_GENERATOR_URL', plugin_dir_url(__FILE__));

// Autoload classes
require_once AI_CONTENT_GENERATOR_PATH . 'vendor/autoload.php';

// Load core classes
use AI_Content_Generator\Admin\Admin_Interface;
use AI_Content_Generator\API\API_Handler;
use AI_Content_Generator\Content\Content_Generator;

add_action('plugins_loaded', function() {
    load_plugin_textdomain(
        'ai-content-generator',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages/'
    );

    Admin_Interface::init();
    API_Handler::init();
    Content_Generator::init();
});
