<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Events_Tracker
 */

class Events_Tracker_Admin {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // No initialization needed at this time
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style( 
            'events-tracker-admin', 
            EVENTS_TRACKER_PLUGIN_URL . 'assets/css/events-tracker-admin.css', 
            array(), 
            EVENTS_TRACKER_VERSION, 
            'all' 
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script( 
            'events-tracker-admin', 
            EVENTS_TRACKER_PLUGIN_URL . 'assets/js/events-tracker-admin.js', 
            array( 'jquery' ), 
            EVENTS_TRACKER_VERSION, 
            false 
        );
    }

    /**
     * Add admin menu pages
     *
     * @since    1.0.0
     */
    public function add_menu_pages() {
        // Main menu page
        add_menu_page(
            __( 'Events Tracker', 'events-tracker' ),
            __( 'Events Tracker', 'events-tracker' ),
            'manage_options',
            'events-tracker',
            array( $this, 'display_events_page' ),
            'dashicons-calendar-alt',
            30
        );

        // Settings submenu page
        add_submenu_page(
            'events-tracker',
            __( 'Settings', 'events-tracker' ),
            __( 'Settings', 'events-tracker' ),
            'manage_options',
            'events-tracker-settings',
            array( $this, 'display_settings_page' )
        );
        
        // Shortcodes info submenu page
        add_submenu_page(
            'events-tracker',
            __( 'Shortcodes', 'events-tracker' ),
            __( 'Shortcodes', 'events-tracker' ),
            'manage_options',
            'events-tracker-shortcodes',
            array( $this, 'display_shortcodes_page' )
        );
    }

    /**
     * Display the main events page
     *
     * @since    1.0.0
     */
    public function display_events_page() {
        include_once EVENTS_TRACKER_PLUGIN_DIR . 'admin/partials/events-tracker-admin-display.php';
    }

    /**
     * Display the settings page
     *
     * @since    1.0.0
     */
    public function display_settings_page() {
        include_once EVENTS_TRACKER_PLUGIN_DIR . 'admin/partials/events-tracker-admin-settings.php';
    }
    
    /**
     * Display the shortcodes info page
     *
     * @since    1.0.0
     */
    public function display_shortcodes_page() {
        include_once EVENTS_TRACKER_PLUGIN_DIR . 'admin/partials/events-tracker-admin-shortcodes.php';
    }
}