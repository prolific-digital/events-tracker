<?php
/**
 * The admin settings functionality of the plugin
 *
 * @since      1.0.0
 * @package    Events_Tracker
 */

class Events_Tracker_Settings {

    /**
     * Register all settings for the plugin
     *
     * @since    1.0.0
     */
    public function register_settings() {
        // Register settings
        register_setting(
            'events_tracker_settings',
            'events_tracker_source_blog_id',
            array(
                'type' => 'integer',
                'description' => 'The blog ID to pull events from',
                'sanitize_callback' => array($this, 'sanitize_blog_id'),
            )
        );

        register_setting(
            'events_tracker_settings',
            'events_tracker_events_per_page',
            array(
                'type' => 'integer',
                'description' => 'Number of events to show per page',
                'sanitize_callback' => 'absint',
                'default' => 10,
            )
        );
        
        register_setting(
            'events_tracker_settings',
            'events_tracker_display_past_events',
            array(
                'type' => 'boolean',
                'description' => 'Display past events in saved events list',
                'sanitize_callback' => array($this, 'sanitize_checkbox'),
                'default' => false,
            )
        );
        
        register_setting(
            'events_tracker_settings',
            'events_tracker_events_page',
            array(
                'type' => 'integer',
                'description' => 'The page ID where events are displayed',
                'sanitize_callback' => 'absint',
                'default' => 0,
            )
        );

        // Add general settings section
        add_settings_section(
            'events_tracker_settings_section',
            __('General Settings', 'events-tracker'),
            array($this, 'settings_section_callback'),
            'events-tracker-settings'
        );

        // Add settings fields
        add_settings_field(
            'events_tracker_source_blog_id',
            __('Source Blog ID', 'events-tracker'),
            array($this, 'source_blog_id_callback'),
            'events-tracker-settings',
            'events_tracker_settings_section'
        );

        add_settings_field(
            'events_tracker_events_per_page',
            __('Events Per Page', 'events-tracker'),
            array($this, 'events_per_page_callback'),
            'events-tracker-settings',
            'events_tracker_settings_section'
        );
        
        add_settings_field(
            'events_tracker_display_past_events',
            __('Past Events', 'events-tracker'),
            array($this, 'display_past_events_callback'),
            'events-tracker-settings',
            'events_tracker_settings_section'
        );
        
        add_settings_field(
            'events_tracker_events_page',
            __('Events Page', 'events-tracker'),
            array($this, 'events_page_callback'),
            'events-tracker-settings',
            'events_tracker_settings_section'
        );
        
        // Add The Events Calendar section if it's active
        if (class_exists('Tribe__Events__Main')) {
            add_settings_section(
                'events_tracker_tec_section',
                __('The Events Calendar Integration', 'events-tracker'),
                array($this, 'tec_section_callback'),
                'events-tracker-settings'
            );
            
            // Check if The Events Calendar is active in the source blog
            $multisite = new Events_Tracker_Multisite();
            $blog_id = $multisite->get_source_blog_id();
            $tec_status = $this->check_tec_in_blog($blog_id);
            
            if ($tec_status['active']) {
                add_settings_field(
                    'events_tracker_tec_status',
                    __('Status', 'events-tracker'),
                    array($this, 'tec_status_callback'),
                    'events-tracker-settings',
                    'events_tracker_tec_section'
                );
            } else {
                add_settings_field(
                    'events_tracker_tec_error',
                    __('Status', 'events-tracker'),
                    array($this, 'tec_error_callback'),
                    'events-tracker-settings',
                    'events_tracker_tec_section',
                    array('error' => $tec_status['message'])
                );
            }
        }
    }

    /**
     * Settings section description
     *
     * @since    1.0.0
     */
    public function settings_section_callback() {
        echo '<p>' . __('Configure the Events Tracker plugin settings.', 'events-tracker') . '</p>';
    }
    
    /**
     * The Events Calendar section description
     *
     * @since    1.0.0
     */
    public function tec_section_callback() {
        echo '<p>' . __('Events Tracker integrates with The Events Calendar plugin to display events.', 'events-tracker') . '</p>';
    }

    /**
     * Source blog ID field callback
     *
     * @since    1.0.0
     */
    public function source_blog_id_callback() {
        $blog_id = get_option('events_tracker_source_blog_id', get_current_blog_id());
        
        echo '<input type="number" min="1" name="events_tracker_source_blog_id" value="' . esc_attr($blog_id) . '" class="regular-text" />';
        
        // If we're in a multisite environment, show a list of available sites
        if (is_multisite()) {
            $sites = get_sites(array('fields' => 'ids'));
            if (!empty($sites)) {
                echo '<p class="description">' . __('Available blog IDs: ', 'events-tracker');
                foreach ($sites as $site_id) {
                    $site = get_site($site_id);
                    echo '<code>' . $site_id . '</code> (' . $site->blogname . ') ';
                }
                echo '</p>';
            }
        }
        
        echo '<p class="description">' . __('The blog ID to pull events from. In a multisite environment, this determines which site\'s events will be displayed.', 'events-tracker') . '</p>';
        echo '<p class="description">' . __('Note: The selected blog must have The Events Calendar plugin active.', 'events-tracker') . '</p>';
    }

    /**
     * Events per page field callback
     *
     * @since    1.0.0
     */
    public function events_per_page_callback() {
        $per_page = get_option('events_tracker_events_per_page', 10);
        
        echo '<input type="number" min="1" max="100" name="events_tracker_events_per_page" value="' . esc_attr($per_page) . '" class="small-text" />';
        echo '<p class="description">' . __('Number of events to display per page.', 'events-tracker') . '</p>';
    }
    
    /**
     * Display past events field callback
     *
     * @since    1.0.0
     */
    public function display_past_events_callback() {
        $display_past = get_option('events_tracker_display_past_events', false);
        
        echo '<input type="checkbox" name="events_tracker_display_past_events" value="1" ' . checked(1, $display_past, false) . ' />';
        echo '<p class="description">' . __('Show past events in the saved events list. If unchecked, only upcoming events will be displayed.', 'events-tracker') . '</p>';
    }
    
    /**
     * Events page field callback
     *
     * @since    1.0.0
     */
    public function events_page_callback() {
        $page_id = get_option('events_tracker_events_page', 0);
        
        // Get all pages
        $pages = get_pages();
        
        echo '<select name="events_tracker_events_page" class="regular-text">';
        echo '<option value="0">' . __('-- Select a Page --', 'events-tracker') . '</option>';
        
        foreach ($pages as $page) {
            echo '<option value="' . esc_attr($page->ID) . '" ' . selected($page_id, $page->ID, false) . '>' . esc_html($page->post_title) . '</option>';
        }
        
        echo '</select>';
        
        echo '<p class="description">' . __('Select the page where you have added the events shortcodes. This page will be used for back links from single event views.', 'events-tracker') . '</p>';
        
        if ($page_id > 0) {
            $page_url = get_permalink($page_id);
            echo '<p><a href="' . esc_url($page_url) . '" target="_blank">' . __('View Events Page', 'events-tracker') . '</a></p>';
        }
    }
    
    /**
     * The Events Calendar status callback
     *
     * @since    1.0.0
     */
    public function tec_status_callback() {
        $multisite = new Events_Tracker_Multisite();
        $blog_id = $multisite->get_source_blog_id();
        $tec_status = $this->check_tec_in_blog($blog_id);
        
        if ($tec_status['active']) {
            echo '<div class="notice inline notice-success">';
            echo '<p><span class="dashicons dashicons-yes"></span> ';
            echo sprintf(__('The Events Calendar (version %s) is active in blog ID %d.', 'events-tracker'), 
                  esc_html($tec_status['version']), 
                  intval($blog_id));
            echo '</p></div>';
            
            // Show event categories if available
            if (!empty($tec_status['categories'])) {
                echo '<p><strong>' . __('Available Event Categories:', 'events-tracker') . '</strong></p>';
                echo '<ul class="events-tracker-categories-list">';
                foreach ($tec_status['categories'] as $term) {
                    echo '<li>' . esc_html($term->name) . ' (' . intval($term->count) . ' events)</li>';
                }
                echo '</ul>';
            }
        }
    }
    
    /**
     * The Events Calendar error callback
     *
     * @since    1.0.0
     * @param    array    $args    The field arguments.
     */
    public function tec_error_callback($args) {
        echo '<div class="notice inline notice-error">';
        echo '<p><span class="dashicons dashicons-warning"></span> ' . esc_html($args['error']) . '</p>';
        echo '</div>';
        echo '<p>' . __('Please ensure The Events Calendar plugin is installed and activated in the source blog.', 'events-tracker') . '</p>';
    }

    /**
     * Sanitize blog ID setting
     *
     * @since    1.0.0
     * @param    mixed    $input    The unsanitized input.
     * @return   int      The sanitized blog ID.
     */
    public function sanitize_blog_id($input) {
        $blog_id = absint($input);
        
        // Make sure the blog exists if we're in a multisite environment
        if (is_multisite() && !get_site($blog_id)) {
            add_settings_error(
                'events_tracker_source_blog_id',
                'events_tracker_invalid_blog_id',
                __('The specified blog ID does not exist.', 'events-tracker'),
                'error'
            );
            
            // Return the current setting or default to current blog
            return get_option('events_tracker_source_blog_id', get_current_blog_id());
        }
        
        // Check if The Events Calendar is active in this blog
        $tec_status = $this->check_tec_in_blog($blog_id);
        if (!$tec_status['active']) {
            // Show a warning, but allow the setting change
            add_settings_error(
                'events_tracker_source_blog_id',
                'events_tracker_tec_not_active',
                $tec_status['message'] . ' ' . __('Events will not be displayed until The Events Calendar is activated on the source site.', 'events-tracker'),
                'warning'
            );
        }
        
        return $blog_id;
    }
    
    /**
     * Sanitize checkbox setting
     *
     * @since    1.0.0
     * @param    mixed    $input    The unsanitized input.
     * @return   bool     The sanitized checkbox value.
     */
    public function sanitize_checkbox($input) {
        return (isset($input) && $input == 1) ? 1 : 0;
    }
    
    /**
     * Check if The Events Calendar is active in a blog
     *
     * @since    1.0.0
     * @param    int       $blog_id    The blog ID to check.
     * @return   array     Status information.
     */
    private function check_tec_in_blog($blog_id) {
        $status = array(
            'active' => false,
            'version' => '',
            'message' => '',
            'categories' => array(),
        );
        
        // Use our main utility function first
        if (function_exists('events_tracker_check_tec_in_blog')) {
            $is_active = events_tracker_check_tec_in_blog($blog_id);
            $status['active'] = $is_active;
            
            if (!$is_active) {
                $status['message'] = sprintf(__('The Events Calendar is not active in blog ID %d.', 'events-tracker'), $blog_id);
                return $status;
            }
        }
        
        $multisite = new Events_Tracker_Multisite();
        $switched = $multisite->switch_to_blog($blog_id);
        
        if (!$switched) {
            $status['message'] = sprintf(__('Could not switch to blog ID %d.', 'events-tracker'), $blog_id);
            return $status;
        }
        
        // Now we're in the target blog
        $status['active'] = true;
        
        // Try to get version if class is available
        if (class_exists('Tribe__Events__Main')) {
            $status['version'] = Tribe__Events__Main::VERSION;
        } else {
            $status['version'] = 'Unknown';
        }
        
        // Get event categories if the taxonomy exists
        if (taxonomy_exists('tribe_events_cat')) {
            $terms = get_terms(array(
                'taxonomy' => 'tribe_events_cat',
                'hide_empty' => true,
            ));
            
            if (!is_wp_error($terms)) {
                $status['categories'] = $terms;
            }
        }
        
        // If we're here but no version or categories were found, try direct DB approach
        if ($status['version'] == 'Unknown' && empty($status['categories'])) {
            global $wpdb;
            
            // Check for events in this blog
            $count_query = $wpdb->prepare(
                "SELECT COUNT(*) 
                FROM {$wpdb->posts} 
                WHERE post_type = %s 
                AND post_status = 'publish'",
                'tribe_events'
            );
            
            $count = $wpdb->get_var($count_query);
            
            if ($count > 0) {
                // We found events, so TEC must be active
                $status['event_count'] = $count;
            } else {
                // No events found, but post type might still exist
                $status['message'] = sprintf(__('No events found in blog ID %d.', 'events-tracker'), $blog_id);
            }
        }
        
        $multisite->restore_current_blog();
        
        return $status;
    }
}