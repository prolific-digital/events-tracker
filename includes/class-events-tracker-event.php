<?php
/**
 * Class for handling events
 *
 * @since      1.0.0
 * @package    Events_Tracker
 */

class Events_Tracker_Event {

    /**
     * Get upcoming events from a specific blog
     *
     * @since    1.0.0
     * @param    int       $blog_id     The blog ID to get events from.
     * @param    int       $per_page    Number of events per page.
     * @param    int       $paged       Current page number.
     * @param    string    $category    Optional category slug to filter by.
     * @param    string    $start_date  Optional start date in Y-m-d format.
     * @param    string    $end_date    Optional end date in Y-m-d format.
     * @return   array     Array of event posts and total count.
     */
    public static function get_upcoming_events($blog_id, $per_page = 10, $paged = 1, $category = '', $start_date = '', $end_date = '') {
        global $wpdb;
        
        $multisite = new Events_Tracker_Multisite();
        $events = array();
        $found_posts = 0;
        $max_pages = 0;
        $debug_info = array(
            'current_blog_id' => get_current_blog_id(),
            'target_blog_id' => $blog_id,
            'method_used' => '',
            'error' => '',
            'tribe_events_exists' => false,
            'post_types_found' => array(),
        );
        
        // Calculate pagination
        $offset = ($paged - 1) * $per_page;
        
        // Get current date in event date format
        $current_date = date('Y-m-d H:i:s');
        
        // Process custom date filters if provided
        if (!empty($start_date)) {
            // Format the start date to include time (start of day)
            $current_date = date('Y-m-d 00:00:00', strtotime($start_date));
        }
        
        // Process end date if provided
        $end_date_filter = null;
        if (!empty($end_date)) {
            // Format the end date to include time (end of day)
            $end_date_filter = date('Y-m-d 23:59:59', strtotime($end_date));
        }
        
        // If we're in the same blog, use regular WP_Query
        if (get_current_blog_id() == $blog_id) {
            $debug_info['method_used'] = 'wp_query_same_blog';
            
            // Check if tribe_events post type exists
            $post_types = get_post_types();
            $debug_info['post_types_found'] = $post_types;
            $debug_info['tribe_events_exists'] = isset($post_types['tribe_events']);
            
            // Regular WP_Query in current blog
            $args = array(
                'post_type'      => 'tribe_events',
                'posts_per_page' => $per_page,
                'paged'          => $paged,
                'meta_key'       => '_EventStartDate',
                'orderby'        => 'meta_value',
                'order'          => 'ASC',
                'meta_query'     => array(
                    array(
                        'key'     => '_EventStartDate',
                        'value'   => $current_date,
                        'compare' => '>=',
                        'type'    => 'DATETIME'
                    ),
                ),
            );
            
            // Add category filter if provided
            if (!empty($category)) {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'tribe_events_cat',
                        'field'    => 'slug',
                        'terms'    => $category,
                    ),
                );
            }
            
            // Add end date filter if provided
            if (!empty($end_date_filter)) {
                $args['meta_query'][] = array(
                    'key'     => '_EventStartDate',
                    'value'   => $end_date_filter,
                    'compare' => '<=',
                    'type'    => 'DATETIME'
                );
            }
            
            $query = new WP_Query($args);
            $events = $query->posts;
            $found_posts = $query->found_posts;
            $max_pages = $query->max_num_pages;
            
            $debug_info['sql'] = $query->request;
            $debug_info['found_posts'] = $found_posts;
        } 
        // For other blogs in the network, use direct database queries
        else {
            $debug_info['method_used'] = 'direct_db_query';
            
            try {
                // Switch to target blog to get the correct table prefix
                $switched = $multisite->switch_to_blog($blog_id);
                $debug_info['switched'] = $switched;
                
                if (!$switched) {
                    throw new Exception('Could not switch to blog ' . $blog_id);
                }
                
                // Check if tribe_events post type exists in the target blog
                $post_types = get_post_types();
                $debug_info['post_types_found'] = $post_types;
                $debug_info['tribe_events_exists'] = isset($post_types['tribe_events']);
                
                // Get the current blog's table prefix
                $blog_prefix = $wpdb->get_blog_prefix($blog_id);
                $debug_info['blog_prefix'] = $blog_prefix;
                
                // Tables we'll need
                $posts_table = $blog_prefix . 'posts';
                $postmeta_table = $blog_prefix . 'postmeta';
                $term_relationships_table = $blog_prefix . 'term_relationships';
                $term_taxonomy_table = $blog_prefix . 'term_taxonomy';
                $terms_table = $blog_prefix . 'terms';
                
                // Prepare SQL parts
                $joins = array();
                $wheres = array();
                
                // Start with basic joins and conditions
                $joins[] = "JOIN {$postmeta_table} pm ON p.ID = pm.post_id";
                $wheres[] = "p.post_type = 'tribe_events'";
                $wheres[] = "p.post_status = 'publish'";
                $wheres[] = "pm.meta_key = '_EventStartDate'";
                $wheres[] = $wpdb->prepare("pm.meta_value >= %s", $current_date);
                
                // Add end date filter if provided
                if (!empty($end_date_filter)) {
                    $joins[] = "JOIN {$postmeta_table} pm2 ON p.ID = pm2.post_id";
                    $wheres[] = "pm2.meta_key = '_EventStartDate'";
                    $wheres[] = $wpdb->prepare("pm2.meta_value <= %s", $end_date_filter);
                }
                
                // Add category filter if provided
                if (!empty($category)) {
                    $joins[] = "JOIN {$term_relationships_table} tr ON p.ID = tr.object_id";
                    $joins[] = "JOIN {$term_taxonomy_table} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";
                    $joins[] = "JOIN {$terms_table} t ON tt.term_id = t.term_id";
                    $wheres[] = "tt.taxonomy = 'tribe_events_cat'";
                    $wheres[] = $wpdb->prepare("t.slug = %s", $category);
                }
                
                // Combine all parts into a string
                $joins_sql = implode(' ', $joins);
                $wheres_sql = 'WHERE ' . implode(' AND ', $wheres);
                
                // Restore to original blog before running queries
                $multisite->restore_current_blog();
                
                // Query to get total posts count
                $count_query = "SELECT COUNT(DISTINCT p.ID) 
                                FROM {$posts_table} p 
                                {$joins_sql} 
                                {$wheres_sql}";
                
                $debug_info['count_query'] = $count_query;
                
                $found_posts = $wpdb->get_var($count_query);
                $debug_info['found_posts'] = (int)$found_posts;
                
                if ($found_posts > 0) {
                    // Calculate max pages
                    $max_pages = ceil($found_posts / $per_page);
                    
                    // Main query to get the events
                    $query = "SELECT p.*
                              FROM {$posts_table} p
                              {$joins_sql}
                              {$wheres_sql}
                              GROUP BY p.ID
                              ORDER BY pm.meta_value ASC
                              LIMIT {$offset}, {$per_page}";
                    
                    $debug_info['main_query'] = $query;
                    
                    // Get events
                    $events = $wpdb->get_results($query);
                    
                    // Convert DB results to proper WP_Post objects
                    foreach ($events as $index => $event) {
                        $events[$index] = new WP_Post($event);
                    }
                }
            } catch (Exception $e) {
                $debug_info['error'] = $e->getMessage();
            }
        }
        
        return array(
            'events' => $events,
            'found_posts' => $found_posts,
            'max_pages' => $max_pages,
            'debug_info' => $debug_info
        );
    }
    
    /**
     * Get a specific event from a blog
     *
     * @since    1.0.0
     * @param    int       $blog_id    The blog ID to get the event from.
     * @param    int       $event_id   The event ID.
     * @return   WP_Post   Event post object or null if not found.
     */
    public static function get_event($blog_id, $event_id) {
        global $wpdb;
        
        $multisite = new Events_Tracker_Multisite();
        $event = null;
        
        // If we're in the same blog, use regular get_post()
        if (get_current_blog_id() == $blog_id) {
            // Verify it's an Events Calendar event
            $post_type = get_post_type($event_id);
            
            if ($post_type === 'tribe_events') {
                // Get the event
                $event = get_post($event_id);
            }
        } 
        // For other blogs in the network, use direct database query
        else {
            try {
                // Switch to target blog to get the correct table prefix
                $switched = $multisite->switch_to_blog($blog_id);
                
                if (!$switched) {
                    return null;
                }
                
                // Get the blog's table prefix
                $blog_prefix = $wpdb->get_blog_prefix($blog_id);
                
                // Restore to original blog before running queries
                $multisite->restore_current_blog();
                
                // Query to get the event directly from the database
                $posts_table = $blog_prefix . 'posts';
                
                $query = $wpdb->prepare(
                    "SELECT * 
                    FROM {$posts_table} 
                    WHERE ID = %d 
                    AND post_type = 'tribe_events' 
                    AND post_status = 'publish'",
                    $event_id
                );
                
                $post_data = $wpdb->get_row($query);
                
                if ($post_data) {
                    // Convert DB result to a proper WP_Post object
                    $event = new WP_Post($post_data);
                }
            } catch (Exception $e) {
                // Log error if needed
                $event = null;
            }
        }
        
        return $event;
    }
    
    /**
     * Get event details from The Events Calendar
     *
     * @since    1.0.0
     * @param    int       $blog_id    The blog ID to get the event from.
     * @param    WP_Post   $event      The event post object.
     * @return   array     Array of event details.
     */
    public static function get_event_details($blog_id, $event) {
        global $wpdb;
        
        $multisite = new Events_Tracker_Multisite();
        $details = array(
            'start_date' => '',
            'end_date' => '',
            'venue' => '',
            'venue_id' => 0,
            'address' => '',
            'city' => '',
            'state' => '',
            'zip' => '',
            'country' => '',
            'cost' => '',
            'url' => '',
            'debug_info' => array(),
        );
        
        if (!$event) {
            return $details;
        }
        
        // If we're in the same blog, use regular functions
        if (get_current_blog_id() == $blog_id) {
            // Check if this is a tribe_events post type
            $post_type = get_post_type($event->ID);
            if ($post_type === 'tribe_events') {
                // Get event start and end dates
                $start_date = get_post_meta($event->ID, '_EventStartDate', true);
                $end_date = get_post_meta($event->ID, '_EventEndDate', true);
                
                // Format the dates
                $details['start_date'] = !empty($start_date) ? $start_date : '';
                $details['end_date'] = !empty($end_date) ? $end_date : '';
                
                // Get venue ID
                $venue_id = get_post_meta($event->ID, '_EventVenueID', true);
                $details['venue_id'] = $venue_id;
                
                if ($venue_id) {
                    // Get venue name
                    $venue = get_post($venue_id);
                    if ($venue) {
                        $details['venue'] = $venue->post_title;
                        
                        // Get venue details
                        $details['address'] = get_post_meta($venue_id, '_VenueAddress', true);
                        $details['city'] = get_post_meta($venue_id, '_VenueCity', true);
                        $details['state'] = get_post_meta($venue_id, '_VenueState', true);
                        $details['zip'] = get_post_meta($venue_id, '_VenueZip', true);
                        $details['country'] = get_post_meta($venue_id, '_VenueCountry', true);
                    }
                }
                
                // Get cost
                $cost = get_post_meta($event->ID, '_EventCost', true);
                $details['cost'] = !empty($cost) ? $cost : '';
                
                // Get event URL
                $details['url'] = get_permalink($event->ID);
            }
        }
        // For other blogs in the network, use direct database queries
        else {
            try {
                // Switch to the target blog to get the correct table prefix
                $switched = $multisite->switch_to_blog($blog_id);
                
                if (!$switched) {
                    return $details;
                }
                
                // Get the blog's table prefix
                $blog_prefix = $wpdb->get_blog_prefix($blog_id);
                
                // Get blog URL for building permalink
                $blog_url = get_site_url($blog_id);
                
                // Restore to original blog before running queries
                $multisite->restore_current_blog();
                
                // Tables we'll need
                $postmeta_table = $blog_prefix . 'postmeta';
                $posts_table = $blog_prefix . 'posts';
                
                // Get event meta data
                $event_meta_query = $wpdb->prepare(
                    "SELECT meta_key, meta_value 
                    FROM {$postmeta_table} 
                    WHERE post_id = %d",
                    $event->ID
                );
                
                $event_meta = $wpdb->get_results($event_meta_query, OBJECT_K);
                
                // Extract event details
                if (!empty($event_meta)) {
                    // Get event dates
                    $details['start_date'] = isset($event_meta['_EventStartDate']) ? $event_meta['_EventStartDate']->meta_value : '';
                    $details['end_date'] = isset($event_meta['_EventEndDate']) ? $event_meta['_EventEndDate']->meta_value : '';
                    
                    // Get venue ID
                    if (isset($event_meta['_EventVenueID'])) {
                        $venue_id = $event_meta['_EventVenueID']->meta_value;
                        $details['venue_id'] = $venue_id;
                        
                        if ($venue_id > 0) {
                            // Get venue details
                            $venue_query = $wpdb->prepare(
                                "SELECT p.post_title, pm.meta_key, pm.meta_value 
                                FROM {$posts_table} p
                                JOIN {$postmeta_table} pm ON p.ID = pm.post_id
                                WHERE p.ID = %d",
                                $venue_id
                            );
                            
                            $venue_data = $wpdb->get_results($venue_query);
                            
                            if (!empty($venue_data)) {
                                $details['venue'] = $venue_data[0]->post_title;
                                
                                // Process venue meta
                                foreach ($venue_data as $row) {
                                    switch ($row->meta_key) {
                                        case '_VenueAddress':
                                            $details['address'] = $row->meta_value;
                                            break;
                                        case '_VenueCity':
                                            $details['city'] = $row->meta_value;
                                            break;
                                        case '_VenueState':
                                            $details['state'] = $row->meta_value;
                                            break;
                                        case '_VenueZip':
                                            $details['zip'] = $row->meta_value;
                                            break;
                                        case '_VenueCountry':
                                            $details['country'] = $row->meta_value;
                                            break;
                                    }
                                }
                            }
                        }
                    }
                    
                    // Get cost
                    $details['cost'] = isset($event_meta['_EventCost']) ? $event_meta['_EventCost']->meta_value : '';
                    
                    // Build event URL
                    if (!empty($blog_url)) {
                        // Get the event slug
                        $details['url'] = trailingslashit($blog_url) . 'event/' . $event->post_name . '/';
                    }
                }
            } catch (Exception $e) {
                $details['debug_info']['error'] = $e->getMessage();
            }
        }
        
        return $details;
    }
    
    /**
     * Format event date in a human-readable way
     * 
     * @since    1.0.0
     * @param    string    $start_date   The event start date.
     * @param    string    $end_date     The event end date.
     * @return   string    Formatted date string.
     */
    public static function format_event_date($start_date, $end_date = '') {
        if (empty($start_date)) {
            return '';
        }
        
        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
        
        $start_timestamp = strtotime($start_date);
        $formatted_start_date = date_i18n($date_format, $start_timestamp);
        $formatted_start_time = date_i18n($time_format, $start_timestamp);
        
        $output = $formatted_start_date . ' @ ' . $formatted_start_time;
        
        // Add end date/time if provided and different from start date
        if (!empty($end_date) && $end_date !== $start_date) {
            $end_timestamp = strtotime($end_date);
            
            // If same day, just show end time
            if (date('Y-m-d', $start_timestamp) === date('Y-m-d', $end_timestamp)) {
                $formatted_end_time = date_i18n($time_format, $end_timestamp);
                $output .= ' - ' . $formatted_end_time;
            } else {
                $formatted_end_date = date_i18n($date_format, $end_timestamp);
                $formatted_end_time = date_i18n($time_format, $end_timestamp);
                $output .= ' - ' . $formatted_end_date . ' @ ' . $formatted_end_time;
            }
        }
        
        return $output;
    }
    
    /**
     * Format venue location in a human-readable way
     * 
     * @since    1.0.0
     * @param    array     $details   The event venue details.
     * @return   string    Formatted location string.
     */
    public static function format_venue_location($details) {
        // Check if we have any venue information at all
        $has_venue_info = !empty($details['venue']) || 
                          !empty($details['address']) || 
                          !empty($details['city']) || 
                          !empty($details['state']) || 
                          !empty($details['zip']) || 
                          !empty($details['country']);
        
        // If no venue information exists, return an empty string
        if (!$has_venue_info) {
            return '';
        }
        
        $location_parts = array();
        
        if (!empty($details['venue'])) {
            $location_parts[] = $details['venue'];
        }
        
        $address_parts = array();
        
        if (!empty($details['address'])) {
            $address_parts[] = $details['address'];
        }
        
        $city_state_zip = array();
        
        if (!empty($details['city'])) {
            $city_state_zip[] = $details['city'];
        }
        
        if (!empty($details['state'])) {
            $city_state_zip[] = $details['state'];
        }
        
        if (!empty($details['zip'])) {
            $city_state_zip[] = $details['zip'];
        }
        
        if (!empty($city_state_zip)) {
            $address_parts[] = implode(', ', $city_state_zip);
        }
        
        if (!empty($details['country']) && $details['country'] !== 'US') {
            $address_parts[] = $details['country'];
        }
        
        if (!empty($address_parts)) {
            $location_parts[] = implode(', ', $address_parts);
        }
        
        // Only return content if we actually have location parts
        if (empty($location_parts)) {
            return '';
        }
        
        return implode('<br>', $location_parts);
    }
    
    /**
     * Get saved events for a user
     *
     * @since    1.0.0
     * @param    int      $user_id    The user ID. Defaults to current user.
     * @return   array    Array of event IDs saved by the user.
     */
    public static function get_saved_events($user_id = null) {
        if (is_null($user_id)) {
            $user_id = get_current_user_id();
        }
        
        // Get saved events from user meta
        $saved_events = get_user_meta($user_id, 'events_tracker_saved_events', true);
        
        if (!is_array($saved_events)) {
            $saved_events = array();
        }
        
        return $saved_events;
    }
    
    /**
     * Save an event for a user
     *
     * @since    1.0.0
     * @param    int      $event_id   The event ID to save.
     * @param    int      $user_id    The user ID. Defaults to current user.
     * @return   bool     Whether the event was successfully saved.
     */
    public static function save_event($event_id, $user_id = null) {
        if (is_null($user_id)) {
            $user_id = get_current_user_id();
        }
        
        // Get existing saved events
        $saved_events = self::get_saved_events($user_id);
        
        // Add new event if not already in the list
        if (!in_array($event_id, $saved_events)) {
            $saved_events[] = $event_id;
            update_user_meta($user_id, 'events_tracker_saved_events', $saved_events);
            return true;
        }
        
        return false;
    }
    
    /**
     * Remove a saved event for a user
     *
     * @since    1.0.0
     * @param    int      $event_id   The event ID to remove.
     * @param    int      $user_id    The user ID. Defaults to current user.
     * @return   bool     Whether the event was successfully removed.
     */
    public static function remove_event($event_id, $user_id = null) {
        if (is_null($user_id)) {
            $user_id = get_current_user_id();
        }
        
        // Get existing saved events
        $saved_events = self::get_saved_events($user_id);
        
        // Remove event if it exists in the list
        $key = array_search($event_id, $saved_events);
        if ($key !== false) {
            unset($saved_events[$key]);
            // Reindex array
            $saved_events = array_values($saved_events);
            update_user_meta($user_id, 'events_tracker_saved_events', $saved_events);
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if an event is saved by a user
     *
     * @since    1.0.0
     * @param    int      $event_id   The event ID to check.
     * @param    int      $user_id    The user ID. Defaults to current user.
     * @return   bool     Whether the event is saved by the user.
     */
    public static function is_event_saved($event_id, $user_id = null) {
        if (is_null($user_id)) {
            $user_id = get_current_user_id();
        }
        
        // Get saved events and check if this event is in the list
        $saved_events = self::get_saved_events($user_id);
        return in_array($event_id, $saved_events);
    }
    
    /**
     * Get event categories for an event
     *
     * @since    1.0.0
     * @param    int       $blog_id    The blog ID to get the event categories from.
     * @param    int       $event_id   The event ID.
     * @return   array     Array of category objects.
     */
    public static function get_event_categories($blog_id, $event_id) {
        global $wpdb;
        
        $multisite = new Events_Tracker_Multisite();
        $categories = array();
        
        // If we're in the same blog, use regular WP functions
        if (get_current_blog_id() == $blog_id) {
            $terms = wp_get_post_terms($event_id, 'tribe_events_cat');
            if (!is_wp_error($terms)) {
                $categories = $terms;
            }
        } 
        // For other blogs in the network, use direct database queries
        else {
            try {
                // Switch to target blog to get the correct table prefix
                $switched = $multisite->switch_to_blog($blog_id);
                
                if (!$switched) {
                    return $categories;
                }
                
                // Get the blog's table prefix
                $blog_prefix = $wpdb->get_blog_prefix($blog_id);
                
                // Restore to original blog before running queries
                $multisite->restore_current_blog();
                
                // Tables we'll need
                $term_relationships_table = $blog_prefix . 'term_relationships';
                $term_taxonomy_table = $blog_prefix . 'term_taxonomy';
                $terms_table = $blog_prefix . 'terms';
                
                // Query to get the categories
                $query = $wpdb->prepare(
                    "SELECT t.*, tt.description 
                    FROM {$terms_table} t
                    JOIN {$term_taxonomy_table} tt ON t.term_id = tt.term_id
                    JOIN {$term_relationships_table} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
                    WHERE tr.object_id = %d
                    AND tt.taxonomy = 'tribe_events_cat'",
                    $event_id
                );
                
                $categories = $wpdb->get_results($query);
            } catch (Exception $e) {
                // Log error if needed
                $categories = array();
            }
        }
        
        return $categories;
    }
}