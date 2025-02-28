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
        
        // Debug tools submenu page (only for admins)
        add_submenu_page(
            'events-tracker',
            __( 'Debug Tools', 'events-tracker' ),
            __( 'Debug Tools', 'events-tracker' ),
            'manage_options',
            'events-tracker-debug',
            array( $this, 'display_debug_page' )
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
    
    /**
     * Display the debug tools page
     *
     * @since    1.0.0
     */
    public function display_debug_page() {
        // Initialize variables for the template
        $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : '';
        $blog_id = isset($_POST['blog_id']) ? intval($_POST['blog_id']) : '';
        $action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : '';
        $debug_result = '';
        
        // Process actions
        if ($action === 'test_event' && $event_id && $blog_id) {
            // Test event retrieval
            $event = Events_Tracker_Event::get_event($blog_id, $event_id);
            
            if ($event) {
                $debug_result = '<div class="notice notice-success inline"><p>';
                $debug_result .= sprintf(__('Event found: "%s" (ID: %d)', 'events-tracker'), 
                                        esc_html($event->post_title), 
                                        $event_id);
                $debug_result .= '</p></div>';
                
                // Get event details
                $details = Events_Tracker_Event::get_event_details($blog_id, $event);
                
                // Show event URL
                $tracker = new Events_Tracker();
                $url = home_url($tracker->event_slug . '/' . $event->ID . '/' . $event->post_name . '/');
                
                $debug_result .= '<h3>' . __('Event Details', 'events-tracker') . '</h3>';
                $debug_result .= '<p><strong>' . __('Title:', 'events-tracker') . '</strong> ' . esc_html($event->post_title) . '</p>';
                $debug_result .= '<p><strong>' . __('Start Date:', 'events-tracker') . '</strong> ' . esc_html($details['start_date']) . '</p>';
                $debug_result .= '<p><strong>' . __('End Date:', 'events-tracker') . '</strong> ' . esc_html($details['end_date']) . '</p>';
                $debug_result .= '<p><strong>' . __('Venue:', 'events-tracker') . '</strong> ' . esc_html($details['venue']) . '</p>';
                $debug_result .= '<p><strong>' . __('Original URL:', 'events-tracker') . '</strong> <a href="' . esc_url($details['url']) . '" target="_blank">' . esc_html($details['url']) . '</a></p>';
                $debug_result .= '<p><strong>' . __('Local URL:', 'events-tracker') . '</strong> <a href="' . esc_url($url) . '" target="_blank">' . esc_html($url) . '</a></p>';
                
                // Show post content excerpt
                $content_excerpt = wp_trim_words($event->post_content, 30, '...');
                $debug_result .= '<p><strong>' . __('Content Preview:', 'events-tracker') . '</strong> ' . esc_html($content_excerpt) . '</p>';
            } else {
                $debug_result = '<div class="notice notice-error inline"><p>';
                $debug_result .= sprintf(__('Event not found with ID %d in blog %d.', 'events-tracker'), 
                                        $event_id, 
                                        $blog_id);
                $debug_result .= '</p></div>';
            }
        } elseif ($action === 'flush_rules') {
            // Flush rewrite rules
            flush_rewrite_rules();
            $debug_result = '<div class="notice notice-success inline"><p>';
            $debug_result .= __('Rewrite rules have been flushed successfully.', 'events-tracker');
            $debug_result .= '</p></div>';
        }
        
        // Display the debug page
        include_once EVENTS_TRACKER_PLUGIN_DIR . 'admin/partials/events-tracker-admin-debug.php';
    }
}