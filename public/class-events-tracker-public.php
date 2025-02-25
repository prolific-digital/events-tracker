<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Events_Tracker
 */

class Events_Tracker_Public {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // No initialization needed at this time
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style( 
            'events-tracker-public', 
            EVENTS_TRACKER_PLUGIN_URL . 'assets/css/events-tracker-public.css', 
            array(), 
            EVENTS_TRACKER_VERSION, 
            'all' 
        );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script( 
            'events-tracker-public', 
            EVENTS_TRACKER_PLUGIN_URL . 'assets/js/events-tracker-public.js', 
            array( 'jquery' ), 
            EVENTS_TRACKER_VERSION, 
            false 
        );

        // Localize the script with data for the AJAX operations
        wp_localize_script( 
            'events-tracker-public', 
            'events_tracker_data', 
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'events_tracker_nonce' ),
                'save_event_msg' => __('Added to your list', 'events-tracker'),
                'remove_event_msg' => __('Removed from your list', 'events-tracker'),
                'error_msg' => __('Error processing request', 'events-tracker'),
            )
        );
    }
}