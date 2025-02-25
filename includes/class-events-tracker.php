<?php
/**
 * The main plugin class
 *
 * @since      1.0.0
 * @package    Events_Tracker
 */

class Events_Tracker {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Events_Tracker_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * Initialize the plugin
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_shortcodes();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     */
    private function load_dependencies() {
        // Core plugin functionality
        require_once EVENTS_TRACKER_PLUGIN_DIR . 'includes/class-events-tracker-loader.php';
        require_once EVENTS_TRACKER_PLUGIN_DIR . 'includes/class-events-tracker-i18n.php';
        require_once EVENTS_TRACKER_PLUGIN_DIR . 'includes/class-events-tracker-event.php';
        require_once EVENTS_TRACKER_PLUGIN_DIR . 'includes/class-events-tracker-multisite.php';
        require_once EVENTS_TRACKER_PLUGIN_DIR . 'includes/class-events-tracker-shortcodes.php';

        // Admin-specific functionality
        require_once EVENTS_TRACKER_PLUGIN_DIR . 'admin/class-events-tracker-admin.php';
        require_once EVENTS_TRACKER_PLUGIN_DIR . 'admin/class-events-tracker-settings.php';

        // Public-facing functionality
        require_once EVENTS_TRACKER_PLUGIN_DIR . 'public/class-events-tracker-public.php';
        require_once EVENTS_TRACKER_PLUGIN_DIR . 'public/class-events-tracker-ajax.php';

        $this->loader = new Events_Tracker_Loader();
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     */
    private function define_admin_hooks() {
        $admin = new Events_Tracker_Admin();
        $settings = new Events_Tracker_Settings();

        // Admin menu & settings
        $this->loader->add_action('admin_menu', $admin, 'add_menu_pages');
        $this->loader->add_action('admin_init', $settings, 'register_settings');
        
        // Admin assets
        $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_scripts');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     */
    private function define_public_hooks() {
        $public = new Events_Tracker_Public();
        $ajax = new Events_Tracker_Ajax();

        // Public assets
        $this->loader->add_action('wp_enqueue_scripts', $public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $public, 'enqueue_scripts');
        
        // AJAX handlers for saving/removing events
        $this->loader->add_action('wp_ajax_events_tracker_save_event', $ajax, 'save_event');
        $this->loader->add_action('wp_ajax_events_tracker_remove_event', $ajax, 'remove_event');
    }

    /**
     * Register all shortcodes
     *
     * @since    1.0.0
     */
    private function define_shortcodes() {
        $shortcodes = new Events_Tracker_Shortcodes();
        
        // Register shortcodes
        add_shortcode('events_tracker_upcoming', array($shortcodes, 'upcoming_events_shortcode'));
        add_shortcode('events_tracker_saved', array($shortcodes, 'saved_events_shortcode'));
    }

    /**
     * Run the plugin
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }
}