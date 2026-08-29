<?php
/**
 * Plugin Name: CloseClient Outreach
 * Plugin URI:  https://closeclient.com/plugins/outreach
 * Description: Lightweight AI-powered outreach CRM and automation system for CloseClient targeting coaches and consultants.
 * Version:     1.0.0
 * Author:      CloseClient Engineering
 * Author URI:  https://closeclient.com
 * License:     GPL-2.0+
 * Text Domain: closeclient-outreach
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('CC_OUTREACH_VERSION', '1.0.0');
define('CC_OUTREACH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CC_OUTREACH_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CC_OUTREACH_PLUGIN_FILE', __FILE__);

// Autoload plugin classes
spl_autoload_register(function ($class) {
    $prefix = 'CloseClient\\Outreach\\';
    $base_dir = CC_OUTREACH_PLUGIN_DIR;

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $parts = explode('\\', $relative_class);
    $class_name = array_pop($parts);

    // Convert camel case / standard names to class file naming conventions if needed
    // Look in directories matching namespace subparts
    $path = strtolower(implode('/', $parts));
    if ($path) {
        $path .= '/';
    }

    // Convert ClassName to class-classname.php
    $filename = 'class-' . strtolower(str_replace('_', '-', $class_name)) . '.php';
    $file = $base_dir . $path . $filename;

    if (file_exists($file)) {
        require_once $file;
    }
});

/**
 * Activation Hook
 */
function activate_closeclient_outreach() {
    require_once CC_OUTREACH_PLUGIN_DIR . 'includes/class-activator.php';
    \CloseClient\Outreach\Includes\Activator::activate();
}

/**
 * Deactivation Hook
 */
function deactivate_closeclient_outreach() {
    require_once CC_OUTREACH_PLUGIN_DIR . 'includes/class-deactivator.php';
    \CloseClient\Outreach\Includes\Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_closeclient_outreach');
register_deactivation_hook(__FILE__, 'deactivate_closeclient_outreach');

/**
 * Run the core plugin
 */
function run_closeclient_outreach() {
    require_once CC_OUTREACH_PLUGIN_DIR . 'includes/class-plugin.php';
    $plugin = \CloseClient\Outreach\Includes\Plugin::get_instance();
    $plugin->run();
}

run_closeclient_outreach();
