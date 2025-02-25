<?php
/**
 * Class for handling shortcodes
 *
 * @since      1.0.0
 * @package    Events_Tracker
 */

class Events_Tracker_Shortcodes {

    /**
     * Shortcode for displaying upcoming events
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string   HTML output for the shortcode.
     */
    public function upcoming_events_shortcode($atts) {
        // Get the source blog ID
        $multisite = new Events_Tracker_Multisite();
        $blog_id = $multisite->get_source_blog_id();
        
        // Check if The Events Calendar is active on the source site
        if (function_exists('events_tracker_check_tec_in_blog') && !events_tracker_check_tec_in_blog($blog_id)) {
            if (current_user_can('manage_options')) {
                // For admins, show a more detailed message
                $site_name = is_multisite() ? get_blog_details($blog_id)->blogname : get_bloginfo('name');
                return '<div class="events-tracker-notice events-tracker-error">' . 
                       sprintf(__('The Events Calendar plugin is not active on the source site "%s" (ID: %d). Please activate The Events Calendar on that site to display events.', 'events-tracker'), 
                               esc_html($site_name), 
                               $blog_id) . 
                       '</div>';
            } else {
                // For regular users, show a simpler message
                return '<div class="events-tracker-notice">' . __('No events available at this time. Please check back later.', 'events-tracker') . '</div>';
            }
        }

        // Default shortcode attributes
        $atts = shortcode_atts(
            array(
                'per_page' => get_option('events_tracker_events_per_page', 10),
                'category' => '',
                'tag' => '',
                'start_date' => '', // Format: YYYY-MM-DD, defaults to today
                'end_date' => '',   // Format: YYYY-MM-DD, defaults to no end limit
            ),
            $atts,
            'events_tracker_upcoming'
        );
        
        // Process filters from GET parameters (if any)
        $category_filter = $atts['category'];
        $start_date_filter = $atts['start_date'];
        $end_date_filter = $atts['end_date'];
        
        // If form was submitted, verify nonce and process filters
        if (isset($_GET['events_tracker_filter_nonce'])) {
            // Verify nonce
            if (wp_verify_nonce($_GET['events_tracker_filter_nonce'], 'events_tracker_filter')) {
                $category_filter = isset($_GET['tribe_eventcategory']) ? sanitize_text_field($_GET['tribe_eventcategory']) : $atts['category'];
                $start_date_filter = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : $atts['start_date'];
                $end_date_filter = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : $atts['end_date'];
            }
        }

        // Only show to logged in users
        if (!is_user_logged_in()) {
            return '<p>' . __('You must be logged in to view upcoming events.', 'events-tracker') . '</p>';
        }

        // Get current page and source blog ID
        $paged = get_query_var('paged') ? get_query_var('paged') : 1;
        $multisite = new Events_Tracker_Multisite();
        $blog_id = $multisite->get_source_blog_id();

        // Get events
        $event_data = Events_Tracker_Event::get_upcoming_events(
            $blog_id, 
            $atts['per_page'], 
            $paged, 
            $category_filter, 
            $start_date_filter, 
            $end_date_filter
        );
        $events = $event_data['events'];
        $max_pages = $event_data['max_pages'];
        
        // Get the user's saved events
        $saved_event_ids = Events_Tracker_Event::get_saved_events();
        
        // Fetch event details for each event
        foreach ($events as $key => $event) {
            // Get event details
            $events[$key]->details = Events_Tracker_Event::get_event_details($blog_id, $event);
            
            // Get event categories
            $events[$key]->categories = Events_Tracker_Event::get_event_categories($blog_id, $event->ID);
        }

        // Start output buffer
        ob_start();

        if (!empty($events)) {
            // Include the template
            include EVENTS_TRACKER_PLUGIN_DIR . 'public/partials/upcoming-events-template.php';
        } else {
            echo '<div class="events-tracker-no-events">';
            echo '<p>' . __('No upcoming events found.', 'events-tracker') . '</p>';
            echo '</div>';
        }

        return ob_get_clean();
    }

    /**
     * Shortcode for displaying saved events
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string   HTML output for the shortcode.
     */
    public function saved_events_shortcode($atts) {
        // Get the source blog ID
        $multisite = new Events_Tracker_Multisite();
        $blog_id = $multisite->get_source_blog_id();
        
        // Check if The Events Calendar is active on the source site
        if (function_exists('events_tracker_check_tec_in_blog') && !events_tracker_check_tec_in_blog($blog_id)) {
            if (current_user_can('manage_options')) {
                // For admins, show a more detailed message
                $site_name = is_multisite() ? get_blog_details($blog_id)->blogname : get_bloginfo('name');
                return '<div class="events-tracker-notice events-tracker-error">' . 
                       sprintf(__('The Events Calendar plugin is not active on the source site "%s" (ID: %d). Please activate The Events Calendar on that site to display events.', 'events-tracker'), 
                               esc_html($site_name), 
                               $blog_id) . 
                       '</div>';
            } else {
                // For regular users, show a simpler message
                return '<div class="events-tracker-notice">' . __('No events available at this time. Please check back later.', 'events-tracker') . '</div>';
            }
        }

        // Default shortcode attributes
        $atts = shortcode_atts(
            array(
                'per_page' => get_option('events_tracker_events_per_page', 10),
            ),
            $atts,
            'events_tracker_saved'
        );

        // Only show to logged in users
        if (!is_user_logged_in()) {
            return '<p>' . __('You must be logged in to view your saved events.', 'events-tracker') . '</p>';
        }

        // Get source blog ID
        $multisite = new Events_Tracker_Multisite();
        $blog_id = $multisite->get_source_blog_id();

        // Get saved event IDs
        $saved_event_ids = Events_Tracker_Event::get_saved_events();

        // Check if user has any saved events
        if (empty($saved_event_ids)) {
            return '<div class="events-tracker-no-events">' .
                   '<p>' . __('You have no saved events.', 'events-tracker') . '</p>' .
                   '<p><a href="' . esc_url(add_query_arg('list', 'upcoming', get_permalink())) . '" class="button">' .
                   __('View Upcoming Events', 'events-tracker') . '</a></p>' .
                   '</div>';
        }

        // Get event details for each saved event
        $saved_events = array();
        foreach ($saved_event_ids as $event_id) {
            $event = Events_Tracker_Event::get_event($blog_id, $event_id);
            if ($event) {
                // Get event details
                $event->details = Events_Tracker_Event::get_event_details($blog_id, $event);
                
                // Get event categories
                $event->categories = Events_Tracker_Event::get_event_categories($blog_id, $event->ID);
                
                $saved_events[] = $event;
            }
        }

        // Sort events by start date (ascending)
        usort($saved_events, function($a, $b) {
            $a_date = strtotime($a->details['start_date']);
            $b_date = strtotime($b->details['start_date']);
            return $a_date - $b_date;
        });

        // Start output buffer
        ob_start();

        if (!empty($saved_events)) {
            // Include the template
            include EVENTS_TRACKER_PLUGIN_DIR . 'public/partials/saved-events-template.php';
        } else {
            echo '<div class="events-tracker-no-events">';
            echo '<p>' . __('No saved events found.', 'events-tracker') . '</p>';
            echo '<p><a href="' . esc_url(add_query_arg('list', 'upcoming', get_permalink())) . '" class="button">' . 
                 __('View Upcoming Events', 'events-tracker') . '</a></p>';
            echo '</div>';
        }

        return ob_get_clean();
    }
}