<?php
/**
 * Fired during plugin activation
 *
 * @since      1.0.0
 * @package    Events_Tracker
 */

class Events_Tracker_Activator {

    /**
     * Set up plugin upon activation
     *
     * @since    1.0.0
     */
    public static function activate() {
        // Set default options
        if ( ! get_option( 'events_tracker_source_blog_id' ) ) {
            // Default to the current blog ID
            update_option( 'events_tracker_source_blog_id', get_current_blog_id() );
        }
        
        if ( ! get_option( 'events_tracker_events_per_page' ) ) {
            // Default number of events per page
            update_option( 'events_tracker_events_per_page', 10 );
        }
        
        // Flush rewrite rules to ensure our shortcodes work
        flush_rewrite_rules();
    }
}