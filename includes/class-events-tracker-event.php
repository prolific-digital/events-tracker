<?php

/**
 * Class for handling events
 *
 * @since      1.0.0
 * @package    Events_Tracker
 */

class Events_Tracker_Event {

    /**
     * Get complete event details via direct database query
     * 
     * This method retrieves a single event and all its metadata directly from 
     * the database using the source blog ID from plugin settings.
     *
     * @since    1.0.0
     * @param    int       $event_id   The event ID to retrieve.
     * @return   array     Array containing the event and its details.
     */
    public static function get_single_event_direct($event_id) {
        global $wpdb;
        
        // Get blog ID from plugin settings
        $multisite = new Events_Tracker_Multisite();
        $blog_id = $multisite->get_source_blog_id();
        
        // Initialize result array
        $result = array(
            'event' => null,
            'details' => array(
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
            ),
        );
        
        try {
            // Get the blog's table prefix
            $blog_prefix = $wpdb->get_blog_prefix($blog_id);
            
            // Define the tables we'll need
            $posts_table = $blog_prefix . 'posts';
            $postmeta_table = $blog_prefix . 'postmeta';
            
            // Query to get the event post
            $event_query = $wpdb->prepare(
                "SELECT * FROM {$posts_table} WHERE ID = %d AND post_type = 'tribe_events' AND post_status = 'publish'",
                $event_id
            );
            
            $post_data = $wpdb->get_row($event_query);
            
            if (!$post_data) {
                return $result;
            }
            
            // Convert to WP_Post object
            $result['event'] = new WP_Post($post_data);
            
            // Query to get all event metadata
            $meta_query = $wpdb->prepare(
                "SELECT meta_key, meta_value FROM {$postmeta_table} WHERE post_id = %d",
                $event_id
            );
            
            $meta_data = $wpdb->get_results($meta_query);
            
            // Process the metadata
            foreach ($meta_data as $meta) {
                switch ($meta->meta_key) {
                    case '_EventStartDate':
                        $result['details']['start_date'] = $meta->meta_value;
                        break;
                    case '_EventEndDate':
                        $result['details']['end_date'] = $meta->meta_value;
                        break;
                    case '_EventVenueID':
                        $result['details']['venue_id'] = $meta->meta_value;
                        break;
                    case '_EventCost':
                        $result['details']['cost'] = $meta->meta_value;
                        break;
                    case '_thumbnail_id':
                        $result['details']['thumbnail_id'] = $meta->meta_value;
                        break;
                }
            }
            
            // If we have a venue ID, get venue details
            if (!empty($result['details']['venue_id'])) {
                $venue_id = $result['details']['venue_id'];
                
                // Get venue post
                $venue_query = $wpdb->prepare(
                    "SELECT post_title FROM {$posts_table} WHERE ID = %d AND post_status = 'publish'",
                    $venue_id
                );
                
                $venue = $wpdb->get_row($venue_query);
                
                if ($venue) {
                    $result['details']['venue'] = $venue->post_title;
                    
                    // Get venue metadata
                    $venue_meta_query = $wpdb->prepare(
                        "SELECT meta_key, meta_value FROM {$postmeta_table} WHERE post_id = %d",
                        $venue_id
                    );
                    
                    $venue_meta = $wpdb->get_results($venue_meta_query);
                    
                    foreach ($venue_meta as $meta) {
                        switch ($meta->meta_key) {
                            case '_VenueAddress':
                                $result['details']['address'] = $meta->meta_value;
                                break;
                            case '_VenueCity':
                                $result['details']['city'] = $meta->meta_value;
                                break;
                            case '_VenueState':
                                $result['details']['state'] = $meta->meta_value;
                                break;
                            case '_VenueZip':
                                $result['details']['zip'] = $meta->meta_value;
                                break;
                            case '_VenueCountry':
                                $result['details']['country'] = $meta->meta_value;
                                break;
                        }
                    }
                }
            }
            
            // Generate a URL for the event
            // Use the site URL from the current site
            $site_url = get_site_url();
            $result['details']['url'] = trailingslashit($site_url) . 'tracked-event/' . $event_id . '/';
            
        } catch (Exception $e) {
            // Simply return the default result on any error
        }
        
        return $result;
    }

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
                // Get information needed to construct the queries
                $debug_info['blog_id'] = $blog_id;

                // First, switch to the blog to get information we need
                $switched = $multisite->switch_to_blog($blog_id);
                $debug_info['switched'] = $switched;

                if (!$switched) {
                    throw new Exception('Could not switch to blog ' . $blog_id);
                }

                // Check if tribe_events post type exists in the target blog
                $post_types = get_post_types();
                $debug_info['post_types_found'] = $post_types;
                $debug_info['tribe_events_exists'] = isset($post_types['tribe_events']);

                // Get the blog's table prefix
                $blog_prefix = $wpdb->get_blog_prefix($blog_id);
                $debug_info['blog_prefix'] = $blog_prefix;

                // Restore to original blog - we have all the info we need now
                $multisite->restore_current_blog();

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
     * IMPORTANT: This function requires the actual post ID from the source blog,
     * not any internal tracking ID. The event_id parameter should be the original
     * post ID from the source blog (The Events Calendar).
     *
     * @since    1.0.0
     * @param    int       $blog_id    The blog ID to get the event from.
     * @param    int       $event_id   The event ID (post ID) in the source blog.
     * @return   WP_Post   Event post object or null if not found.
     */
    public static function get_event($blog_id, $event_id) {
        global $wpdb;

        $multisite = new Events_Tracker_Multisite();
        $event = null;
        $debug = array(
            'current_blog_id' => get_current_blog_id(),
            'target_blog_id' => $blog_id,
            'event_id' => $event_id,
            'method' => '',
            'error' => '',
            'query' => '',
            'result' => '',
        );

        if (empty($event_id) || !is_numeric($event_id)) {
            $debug['error'] = 'Invalid event ID: ' . var_export($event_id, true);
            return null;
        }

        // Make sure the event ID is a proper integer
        $event_id = intval($event_id);

        // If we're in the same blog, use regular get_post()
        if (get_current_blog_id() == $blog_id) {
            $debug['method'] = 'same_blog_get_post';

            // Verify it's an Events Calendar event
            $post_type = get_post_type($event_id);
            $debug['post_type'] = $post_type;

            if ($post_type === 'tribe_events') {
                // Get the event
                $event = get_post($event_id);
                $debug['found'] = !empty($event);
            } else {
                $debug['error'] = "Post is not a tribe_events type: {$post_type}";
            }
        }
        // For other blogs in the network, use direct database query
        else {
            $debug['method'] = 'remote_blog_query';

            try {
                // Make sure the blog exists
                if (is_multisite() && !get_site($blog_id)) {
                    $debug['error'] = "Blog ID {$blog_id} does not exist in this network";
                    return null;
                }

                // Switch to target blog to get the correct table prefix
                $switched = $multisite->switch_to_blog($blog_id);
                $debug['switched'] = $switched;

                if (!$switched) {
                    $debug['error'] = "Could not switch to blog {$blog_id}";
                    return null;
                }

                // Get the blog's table prefix
                $blog_prefix = $wpdb->get_blog_prefix($blog_id);
                $debug['blog_prefix'] = $blog_prefix;

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

                $debug['query'] = $query;
                $post_data = $wpdb->get_row($query);
                $debug['found'] = !empty($post_data);

                if ($post_data) {
                    // Convert DB result to a proper WP_Post object
                    $event = new WP_Post($post_data);
                }
            } catch (Exception $e) {
                $error_message = $e->getMessage();
                $debug['error'] = $error_message;
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
            'thumbnail_id' => 0,
            'country' => '',
            'cost' => '',
            'url' => '',
            'debug_info' => array(
                'method_used' => '',
                'error' => '',
                'event_id' => $event ? $event->ID : 0,
                'blog_id' => $blog_id,
                'current_blog_id' => get_current_blog_id(),
            ),
        );

        if (!$event) {
            $details['debug_info']['error'] = 'Event object is null';
            return $details;
        }

        // If we're in the same blog, use regular functions
        if (get_current_blog_id() == $blog_id) {
            $details['debug_info']['method_used'] = 'same_blog';

            // Check if this is a tribe_events post type
            $post_type = get_post_type($event->ID);
            $details['debug_info']['post_type'] = $post_type;

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
                $details['debug_info']['venue_id'] = $venue_id;

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
                    } else {
                        $details['debug_info']['venue_error'] = 'Venue not found';
                    }
                }

                // Get cost
                $cost = get_post_meta($event->ID, '_EventCost', true);
                $details['cost'] = !empty($cost) ? $cost : '';
                
                // Get featured image ID
                $thumbnail_id = get_post_thumbnail_id($event->ID);
                $details['thumbnail_id'] = !empty($thumbnail_id) ? $thumbnail_id : 0;

                // Get event URL
                $original_url = get_permalink($event->ID);
                $details['url'] = apply_filters('events_tracker_event_url', $original_url, $event, $blog_id);
            } else {
                $details['debug_info']['error'] = 'Not a tribe_events post type';
            }
        }
        // For other blogs in the network, use direct database queries
        else {
            $details['debug_info']['method_used'] = 'remote_blog';

            try {
                // Switch to the target blog to get the correct table prefix
                $switched = $multisite->switch_to_blog($blog_id);
                $details['debug_info']['switched'] = $switched;

                if (!$switched) {
                    $details['debug_info']['error'] = 'Failed to switch to blog ' . $blog_id;
                    return $details;
                }

                // Get the blog's table prefix
                $blog_prefix = $wpdb->get_blog_prefix($blog_id);
                $details['debug_info']['blog_prefix'] = $blog_prefix;

                // Get blog URL for building permalink
                $blog_url = get_site_url($blog_id);

                // Check if it's a tribe_events post type while we're in the context of that blog
                $post_type = get_post_type($event->ID);
                $details['debug_info']['post_type_check'] = $post_type;

                // Get direct database access to event meta while in the context of the source blog
                $start_date = get_post_meta($event->ID, '_EventStartDate', true);
                $end_date = get_post_meta($event->ID, '_EventEndDate', true);
                $venue_id = get_post_meta($event->ID, '_EventVenueID', true);
                $cost = get_post_meta($event->ID, '_EventCost', true);

                // Store these values for use after we switch back
                $direct_meta = array(
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'venue_id' => $venue_id,
                    'cost' => $cost
                );

                // Get venue details if we have a venue ID
                $venue_details = array();
                if (!empty($venue_id) && $venue_id > 0) {
                    $venue = get_post($venue_id);
                    if ($venue) {
                        $venue_details['title'] = $venue->post_title;
                        $venue_details['address'] = get_post_meta($venue_id, '_VenueAddress', true);
                        $venue_details['city'] = get_post_meta($venue_id, '_VenueCity', true);
                        $venue_details['state'] = get_post_meta($venue_id, '_VenueState', true);
                        $venue_details['zip'] = get_post_meta($venue_id, '_VenueZip', true);
                        $venue_details['country'] = get_post_meta($venue_id, '_VenueCountry', true);
                    }
                }

                // Store the actual permalink from the source blog
                $original_url = get_permalink($event->ID);

                // Now restore to the original blog
                $multisite->restore_current_blog();

                // Use the direct data we collected while in the source blog context
                $details['start_date'] = !empty($direct_meta['start_date']) ? $direct_meta['start_date'] : '';
                $details['end_date'] = !empty($direct_meta['end_date']) ? $direct_meta['end_date'] : '';
                $details['cost'] = !empty($direct_meta['cost']) ? $direct_meta['cost'] : '';
                $details['venue_id'] = !empty($direct_meta['venue_id']) ? $direct_meta['venue_id'] : 0;

                // Add venue details if we have them
                if (!empty($venue_details)) {
                    $details['venue'] = $venue_details['title'];
                    $details['address'] = $venue_details['address'];
                    $details['city'] = $venue_details['city'];
                    $details['state'] = $venue_details['state'];
                    $details['zip'] = $venue_details['zip'];
                    $details['country'] = $venue_details['country'];
                }

                // Set the URL
                $details['url'] = apply_filters('events_tracker_event_url', $original_url, $event, $blog_id);

                // Fallback to database queries if we didn't get what we need
                if (empty($details['start_date']) || empty($details['venue']) && !empty($direct_meta['venue_id'])) {
                    $details['debug_info']['fallback'] = 'Using database queries';

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
                    $details['debug_info']['event_meta_count'] = count($event_meta);

                    // Extract event details
                    if (!empty($event_meta)) {
                        // Get event dates if not already set
                        if (empty($details['start_date'])) {
                            $details['start_date'] = isset($event_meta['_EventStartDate']) ? $event_meta['_EventStartDate']->meta_value : '';
                        }
                        if (empty($details['end_date'])) {
                            $details['end_date'] = isset($event_meta['_EventEndDate']) ? $event_meta['_EventEndDate']->meta_value : '';
                        }

                        // Get venue ID if not already set
                        if (empty($details['venue_id']) && isset($event_meta['_EventVenueID'])) {
                            $venue_id = $event_meta['_EventVenueID']->meta_value;
                            $details['venue_id'] = $venue_id;

                            if ($venue_id > 0 && (empty($details['venue']) || empty($details['address']))) {
                                // Get venue details
                                $venue_query = $wpdb->prepare(
                                    "SELECT p.post_title, pm.meta_key, pm.meta_value 
                                    FROM {$posts_table} p
                                    JOIN {$postmeta_table} pm ON p.ID = pm.post_id
                                    WHERE p.ID = %d",
                                    $venue_id
                                );

                                $venue_data = $wpdb->get_results($venue_query);
                                $details['debug_info']['venue_data_count'] = count($venue_data);

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

                        // Get cost if not already set
                        if (empty($details['cost'])) {
                            $details['cost'] = isset($event_meta['_EventCost']) ? $event_meta['_EventCost']->meta_value : '';
                        }
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

        // Get WordPress date/time formats
        $date_format = get_option('date_format', 'F j, Y');
        $time_format = get_option('time_format', 'g:i a');

        $start_timestamp = strtotime($start_date);
        if (!$start_timestamp) {
            return '';
        }

        $formatted_start_date = date_i18n($date_format, $start_timestamp);
        $formatted_start_time = date_i18n($time_format, $start_timestamp);

        $output = '<span class="et-date">' . $formatted_start_date . '</span> @ <span class="et-time">' . $formatted_start_time . '</span>';

        // Add end date/time if provided and different from start date
        if (!empty($end_date) && $end_date !== $start_date) {
            $end_timestamp = strtotime($end_date);

            if ($end_timestamp) {
                // If same day, just show end time
                if (date('Y-m-d', $start_timestamp) === date('Y-m-d', $end_timestamp)) {
                    $formatted_end_time = date_i18n($time_format, $end_timestamp);
                    $output .= ' - <span class="et-time">' . $formatted_end_time . '</span>';
                } else {
                    $formatted_end_date = date_i18n($date_format, $end_timestamp);
                    $formatted_end_time = date_i18n($time_format, $end_timestamp);
                    $output .= ' - <span class="et-date">' . $formatted_end_date . '</span> @ <span class="et-time">' . $formatted_end_time . '</span>';
                }
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

        // Venue name with styling
        if (!empty($details['venue'])) {
            $location_parts[] = '<span class="et-venue-name">' . esc_html($details['venue']) . '</span>';
        }

        $address_parts = array();

        // Street address with styling
        if (!empty($details['address'])) {
            $address_parts[] = '<span class="et-venue-address">' . esc_html($details['address']) . '</span>';
        }

        $city_state_zip = array();

        // City, state, zip with styling
        if (!empty($details['city'])) {
            $city_state_zip[] = '<span class="et-venue-city">' . esc_html($details['city']) . '</span>';
        }

        if (!empty($details['state'])) {
            $city_state_zip[] = '<span class="et-venue-state">' . esc_html($details['state']) . '</span>';
        }

        if (!empty($details['zip'])) {
            $city_state_zip[] = '<span class="et-venue-zip">' . esc_html($details['zip']) . '</span>';
        }

        if (!empty($city_state_zip)) {
            $address_parts[] = implode(', ', $city_state_zip);
        }

        // Country with styling, only if not US
        if (!empty($details['country']) && $details['country'] !== 'US') {
            $address_parts[] = '<span class="et-venue-country">' . esc_html($details['country']) . '</span>';
        }

        if (!empty($address_parts)) {
            $location_parts[] = '<div class="et-venue-full-address">' . implode(', ', $address_parts) . '</div>';
        }

        // Only return content if we actually have location parts
        if (empty($location_parts)) {
            return '';
        }

        // Build the final formatted output with better separation
        return implode('<br>', $location_parts);
    }
    
    /**
     * Get single event with details using direct database queries
     *
     * This function uses direct database queries to retrieve a single event and all its metadata
     * from a specified blog in the multisite network. It's designed to work reliably across
     * different blogs in the network by using direct SQL queries instead of WordPress functions.
     *
     * @since    1.0.0
     * @param    int       $blog_id     The blog ID to get the event from.
     * @param    int       $event_id    The event ID to retrieve.
     * @return   array     Array containing the event post and all its metadata.
     */
    public static function get_single_event_with_details($blog_id, $event_id) {
        global $wpdb;
        
        $multisite = new Events_Tracker_Multisite();
        $result = array(
            'event' => null,
            'details' => array(
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
                'debug_info' => array(
                    'method_used' => 'direct_db_query',
                    'current_blog_id' => get_current_blog_id(),
                    'target_blog_id' => $blog_id,
                    'event_id' => $event_id,
                    'error' => '',
                ),
            ),
        );
        
        try {
            // Get information needed for the queries
            $result['details']['debug_info']['blog_id'] = $blog_id;
            
            // Get the blog's table prefix
            $blog_prefix = $wpdb->get_blog_prefix($blog_id);
            $result['details']['debug_info']['blog_prefix'] = $blog_prefix;
            
            // Define the tables we'll need
            $posts_table = $blog_prefix . 'posts';
            $postmeta_table = $blog_prefix . 'postmeta';
            
            // Query to get the event post
            $event_query = $wpdb->prepare(
                "SELECT * FROM {$posts_table} WHERE ID = %d AND post_type = 'tribe_events' AND post_status = 'publish'",
                $event_id
            );
            
            $post_data = $wpdb->get_row($event_query);
            
            if (!$post_data) {
                $result['details']['debug_info']['error'] = 'Event not found in database';
                return $result;
            }
            
            // Convert to WP_Post object
            $result['event'] = new WP_Post($post_data);
            
            // Query to get all event metadata
            $meta_query = $wpdb->prepare(
                "SELECT meta_key, meta_value FROM {$postmeta_table} WHERE post_id = %d",
                $event_id
            );
            
            $meta_data = $wpdb->get_results($meta_query);
            $result['details']['debug_info']['meta_count'] = count($meta_data);
            
            // Process the metadata
            foreach ($meta_data as $meta) {
                switch ($meta->meta_key) {
                    case '_EventStartDate':
                        $result['details']['start_date'] = $meta->meta_value;
                        break;
                    case '_EventEndDate':
                        $result['details']['end_date'] = $meta->meta_value;
                        break;
                    case '_EventVenueID':
                        $result['details']['venue_id'] = $meta->meta_value;
                        break;
                    case '_EventCost':
                        $result['details']['cost'] = $meta->meta_value;
                        break;
                    case '_thumbnail_id':
                        $result['details']['thumbnail_id'] = $meta->meta_value;
                        break;
                }
            }
            
            // If we have a venue ID, get venue details
            if (!empty($result['details']['venue_id'])) {
                $venue_id = $result['details']['venue_id'];
                
                // Get venue post
                $venue_query = $wpdb->prepare(
                    "SELECT post_title FROM {$posts_table} WHERE ID = %d AND post_status = 'publish'",
                    $venue_id
                );
                
                $venue = $wpdb->get_row($venue_query);
                
                if ($venue) {
                    $result['details']['venue'] = $venue->post_title;
                    
                    // Get venue metadata
                    $venue_meta_query = $wpdb->prepare(
                        "SELECT meta_key, meta_value FROM {$postmeta_table} WHERE post_id = %d",
                        $venue_id
                    );
                    
                    $venue_meta = $wpdb->get_results($venue_meta_query);
                    
                    foreach ($venue_meta as $meta) {
                        switch ($meta->meta_key) {
                            case '_VenueAddress':
                                $result['details']['address'] = $meta->meta_value;
                                break;
                            case '_VenueCity':
                                $result['details']['city'] = $meta->meta_value;
                                break;
                            case '_VenueState':
                                $result['details']['state'] = $meta->meta_value;
                                break;
                            case '_VenueZip':
                                $result['details']['zip'] = $meta->meta_value;
                                break;
                            case '_VenueCountry':
                                $result['details']['country'] = $meta->meta_value;
                                break;
                        }
                    }
                }
            }
            
            // Generate a URL for the tracked event
            $site_url = get_site_url();
            $result['details']['url'] = trailingslashit($site_url) . 'tracked-event/' . $event_id . '/' . $result['event']->post_name . '/';
            
            // Mark as successful
            $result['details']['debug_info']['success'] = true;
            
        } catch (Exception $e) {
            $result['details']['debug_info']['error'] = $e->getMessage();
        }
        
        return $result;
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
     * Get an event forcefully by direct database query
     * 
     * This is a fallback method when regular get_event fails.
     *
     * @since    1.0.0
     * @param    int       $blog_id    The blog ID to get the event from.
     * @param    int       $event_id   The event ID.
     * @return   WP_Post|null   Event post object or null if not found.
     */
    public static function get_event_forced($blog_id, $event_id) {
        global $wpdb;

        $multisite = new Events_Tracker_Multisite();
        $event = null;

        try {
            // Get the blog prefix
            $blog_prefix = $wpdb->get_blog_prefix($blog_id);

            // Posts table
            $posts_table = $blog_prefix . 'posts';

            // Direct query to get the event
            $query = $wpdb->prepare(
                "SELECT * FROM {$posts_table} WHERE ID = %d AND post_type = 'tribe_events' AND post_status = 'publish'",
                $event_id
            );

            $post_data = $wpdb->get_row($query);

            if ($post_data) {
                $event = new WP_Post($post_data);
            }
        } catch (Exception $e) {
            // Silent failure - debug info captured in return value
        }

        return $event;
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
                $error_message = $e->getMessage();
                $categories = array();
            }
        }

        return $categories;
    }
}
