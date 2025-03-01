<?php
/**
 * Class for handling shortcodes
 *
 * @since      1.0.0
 * @package    Events_Tracker
 */

class Events_Tracker_Shortcodes {
    
    /**
     * Helper method to get an image from a source blog in multisite
     *
     * @since    1.1.0
     * @param    int       $blog_id      The source blog ID.
     * @param    int       $image_id     The attachment ID.
     * @param    string    $image_size   The image size to retrieve.
     * @return   array|bool Array with image details or false if not found.
     */
    private static function get_image_from_source_blog($blog_id, $image_id, $image_size = 'large') {
        $multisite = new Events_Tracker_Multisite();
        
        // Default response
        $image_data = false;
        
        // Switch to the source blog
        $switched = $multisite->switch_to_blog($blog_id);
        
        if ($switched) {
            // Get the image data
            $image = wp_get_attachment_image_src($image_id, $image_size);
            
            if ($image) {
                $image_data = array(
                    'url' => $image[0],
                    'width' => $image[1],
                    'height' => $image[2]
                );
            }
            
            // Switch back to current blog
            $multisite->restore_current_blog();
        }
        
        return $image_data;
    }

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
                'add_text' => __('Add to My List', 'events-tracker'),
                'remove_text' => __('Remove from My List', 'events-tracker'),
                'view_details_text' => __('View Details', 'events-tracker'),
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
                'remove_text' => __('Remove from My List', 'events-tracker'),
                'view_details_text' => __('View Details', 'events-tracker'),
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
    
    /**
     * Shortcode for displaying a single event in a block theme template
     *
     * @since    1.1.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string   HTML output for the shortcode.
     */
    public function single_event_shortcode($atts) {
        // Default shortcode attributes
        $atts = shortcode_atts(
            array(
                'event_id' => 0,
                'show_title' => 'yes',
                'show_meta' => 'yes',
                'show_cost' => 'yes',
                'show_content' => 'yes',
                'show_actions' => 'yes',
                'show_navigation' => 'no',
                'show_image' => 'yes',
                'image_size' => 'large',
                'add_text' => __('Add to My List', 'events-tracker'),
                'remove_text' => __('Remove from My List', 'events-tracker'),
            ),
            $atts,
            'events_tracker_single_event'
        );
        
        // Get the event ID
        $event_id = absint($atts['event_id']);
        
        // Get event_id from query parameter if not set in shortcode
        if (empty($event_id) && isset($_GET['event_id'])) {
            $event_id = absint($_GET['event_id']);
        }
        
        // If still no event ID, try to get it from the URL path
        if (empty($event_id)) {
            $url_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
            $path_parts = explode('/', $url_path);
            
            // Look for the event slug in the URL
            $event_slug_pos = array_search('tracked-event', $path_parts);
            if ($event_slug_pos !== false && isset($path_parts[$event_slug_pos + 1])) {
                $event_id = absint($path_parts[$event_slug_pos + 1]);
            }
        }
        
        // If we still don't have an event ID, show an error
        if (empty($event_id)) {
            return '<div class="events-tracker-notice events-tracker-error">' . 
                   __('No event ID specified. Please provide an event ID using the shortcode parameter or URL.', 'events-tracker') . 
                   '</div>';
        }
        
        // Get event using our direct database query method
        $result = Events_Tracker_Event::get_single_event_direct($event_id);
        
        // Extract the event object and details
        $event = $result['event'];
        $event_details = $result['details'];
        
        // If we couldn't get the event, show an error
        if (!$event) {
            return '<div class="events-tracker-notice events-tracker-error">' . 
                   __('Event not found. Please check the event ID and try again.', 'events-tracker') . 
                   '</div>';
        }
        
        // Check if this event is already saved by the user
        $is_saved = false;
        if (is_user_logged_in()) {
            $saved_event_ids = Events_Tracker_Event::get_saved_events();
            $is_saved = in_array($event->ID, $saved_event_ids);
        }
        
        // Start output buffer
        ob_start();
        
        // Begin event container
        echo '<div id="event-' . esc_attr($event->ID) . '" class="events-tracker-single-event">';
        
        // Show title if enabled
        if ($atts['show_title'] === 'yes') {
            echo '<header class="entry-header">';
            echo '<h1 class="entry-title">' . esc_html($event->post_title) . '</h1>';
            echo '</header>';
        }
        
        // Show meta first (below title, above image)
        if ($atts['show_meta'] === 'yes' && (!empty($event_details['start_date']) || !empty(trim(Events_Tracker_Event::format_venue_location($event_details))))) {
            echo '<div class="event-meta">';
            
            // Date and time
            if (!empty($event_details['start_date'])) {
                $formatted_date = Events_Tracker_Event::format_event_date(
                    $event_details['start_date'],
                    $event_details['end_date']
                );
                echo '<div class="event-date-time">' . wp_kses_post($formatted_date) . '</div>';
            }
            
            // Location
            $formatted_venue = Events_Tracker_Event::format_venue_location($event_details);
            if (!empty(trim($formatted_venue))) {
                echo '<div class="event-location">' . wp_kses_post($formatted_venue) . '</div>';
            }
            
            // Event actions (save/remove buttons)
            if ($atts['show_actions'] === 'yes' && is_user_logged_in()) {
                echo '<div class="event-actions">';
                if ($is_saved) {
                    echo '<button class="events-tracker-remove-event" data-event-id="' . esc_attr($event->ID) . '">';
                    echo esc_html($atts['remove_text']);
                    echo '</button>';
                } else {
                    echo '<button class="events-tracker-save-event" data-event-id="' . esc_attr($event->ID) . '">';
                    echo esc_html($atts['add_text']);
                    echo '</button>';
                }
                echo '<span class="events-tracker-event-status"></span>';
                echo '</div>';
            }
            
            echo '</div>'; // .event-meta
        }
        
        // Show featured image if enabled and available (now below meta)
        if ($atts['show_image'] === 'yes' && !empty($event_details['thumbnail_id'])) {
            $image_size = sanitize_key($atts['image_size']);
            $multisite = new Events_Tracker_Multisite();
            $blog_id = $multisite->get_source_blog_id();
            
            echo '<div class="events-featured-image">';
            
            // If we're on the same blog as the event, use WordPress functions
            if (get_current_blog_id() == $blog_id) {
                $image_html = wp_get_attachment_image($event_details['thumbnail_id'], $image_size, false, array('class' => 'events-tracker-featured-image'));
                echo $image_html;
            } else {
                // For other blogs, we need to get the image from the source blog
                $image_url = self::get_image_from_source_blog($blog_id, $event_details['thumbnail_id'], $image_size);
                if (!empty($image_url)) {
                    echo '<img src="' . esc_url($image_url['url']) . '" ';
                    echo 'width="' . esc_attr($image_url['width']) . '" ';
                    echo 'height="' . esc_attr($image_url['height']) . '" ';
                    echo 'alt="' . esc_attr($event->post_title) . '" ';
                    echo 'class="events-tracker-featured-image" />';
                }
            }
            
            echo '</div>'; // .events-featured-image
        }
        
        // Show cost if enabled
        if ($atts['show_cost'] === 'yes' && !empty($event_details['cost'])) {
            echo '<div class="event-cost">';
            echo '<span class="event-cost-label">' . __('Cost:', 'events-tracker') . '</span> ';
            echo '<span class="event-cost-value">' . esc_html($event_details['cost']) . '</span>';
            echo '</div>';
        }
        
        // Show content if enabled (only first block)
        if ($atts['show_content'] === 'yes') {
            echo '<div class="entry-content">';
            $parsed_blocks = parse_blocks($event->post_content);
            // Only render the first block in the array
            if (!empty($parsed_blocks) && isset($parsed_blocks[0])) {
                echo render_block($parsed_blocks[0]);
            }
            echo '</div>';
        }
        
        // Navigation links have been removed as requested
        
        echo '</div>'; // .events-tracker-single-event
        
        return ob_get_clean();
    }
}