<?php
/**
 * Class for handling multisite functionality
 *
 * @since      1.0.0
 * @package    Events_Tracker
 */

class Events_Tracker_Multisite {

    /**
     * Flag to track if we've switched blogs
     *
     * @since    1.0.0
     * @access   private
     * @var      bool    $switched    Whether we've switched blogs.
     */
    private $switched = false;

    /**
     * Switch to a specific blog
     *
     * @since    1.0.0
     * @param    int    $blog_id    The blog ID to switch to.
     * @return   bool   Whether the switch was successful.
     */
    public function switch_to_blog($blog_id) {
        // Don't switch if we're already on this blog
        if (get_current_blog_id() == $blog_id) {
            return true;
        }

        // Make sure we're in a multisite environment
        if (!is_multisite()) {
            return false;
        }

        // Check if blog exists
        if (!get_site($blog_id)) {
            return false;
        }

        // Switch to the specified blog
        switch_to_blog($blog_id);
        $this->switched = true;

        return true;
    }

    /**
     * Restore to the current blog
     *
     * @since    1.0.0
     */
    public function restore_current_blog() {
        if ($this->switched && is_multisite()) {
            restore_current_blog();
            $this->switched = false;
        }
    }

    /**
     * Get the blog ID configured as the events source
     *
     * @since    1.0.0
     * @return   int    The source blog ID.
     */
    public function get_source_blog_id() {
        $blog_id = get_option('events_tracker_source_blog_id', get_current_blog_id());
        
        // Ensure the blog ID is valid
        if (is_multisite() && !get_site($blog_id)) {
            // Default to current blog if the configured one doesn't exist
            $blog_id = get_current_blog_id();
        }
        
        return $blog_id;
    }
}