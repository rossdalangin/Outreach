<?php
namespace CloseClient\Outreach\Includes;

if (!defined('ABSPATH')) {
    exit;
}

use CloseClient\Outreach\Admin\Admin_Controller;
use CloseClient\Outreach\Automation\Cron_Handler;

class Plugin {

    private static $instance = null;

    /**
     * Singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        // Class dependencies will be autoloaded by SPL autoloader
    }

    private function define_admin_hooks() {
        if (is_admin()) {
            add_action('admin_menu', array($this, 'register_admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
            add_action('wp_ajax_cc_outreach_ajax_action', array($this, 'handle_ajax'));
        }
    }

    private function define_public_hooks() {
        add_action('cc_outreach_cron_event', array($this, 'run_cron_tasks'));
        add_action('rest_api_init', array('CloseClient\\Outreach\\Includes\\REST_API', 'register_routes'));
    }

    public function register_admin_menu() {
        if (class_exists('CloseClient\\Outreach\\Admin\\Admin_Controller')) {
            Admin_Controller::register_menu();
        }
    }

    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'closeclient-outreach') === false) {
            return;
        }

        wp_enqueue_style(
            'cc-outreach-admin-css',
            CC_OUTREACH_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            CC_OUTREACH_VERSION
        );

        wp_enqueue_script(
            'cc-outreach-admin-js',
            CC_OUTREACH_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            CC_OUTREACH_VERSION,
            true
        );

        wp_localize_script('cc-outreach-admin-js', 'ccOutreachVars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('cc_outreach_ajax_nonce'),
        ));
    }

    public function handle_ajax() {
        if (class_exists('CloseClient\\Outreach\\Admin\\Admin_Controller')) {
            Admin_Controller::handle_ajax_request();
        }
    }

    public function run_cron_tasks() {
        if (class_exists('CloseClient\\Outreach\\Automation\\Cron_Handler')) {
            Cron_Handler::execute();
        }
    }

    public function run() {
        // Plugin is initialized and active
    }
}
