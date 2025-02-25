<?php
/**
 * Fired during plugin deactivation
 *
 * @since      1.0.0
 * @package    Events_Tracker
 */

class Events_Tracker_Deactivator {

    /**
     * Clean up plugin upon deactivation
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Note: We do not delete the saved options during deactivation
        // This ensures that if the plugin is reactivated, the settings will remain
    }
}