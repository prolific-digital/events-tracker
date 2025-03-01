<?php
/**
 * The main plugin class
 *
 * @since      1.0.0
 * @package    Events_Tracker
 */

class Events_Tracker {

    /**
     * The base slug for single event pages
     *
     * @since    1.0.0
     * @access   public
     * @var      string    $event_slug    The base slug for single event pages.
     */
    public $event_slug = 'tracked-event';

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Events_Tracker_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * Initialize the plugin
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_shortcodes();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     */
    private function load_dependencies() {
        // Core plugin functionality
        require_once EVENTS_TRACKER_PLUGIN_DIR . 'includes/class-events-tracker-loader.php';
        require_once EVENTS_TRACKER_PLUGIN_DIR . 'includes/class-events-tracker-i18n.php';
        require_once EVENTS_TRACKER_PLUGIN_DIR . 'includes/class-events-tracker-event.php';
        require_once EVENTS_TRACKER_PLUGIN_DIR . 'includes/class-events-tracker-multisite.php';
        require_once EVENTS_TRACKER_PLUGIN_DIR . 'includes/class-events-tracker-shortcodes.php';

        // Admin-specific functionality
        require_once EVENTS_TRACKER_PLUGIN_DIR . 'admin/class-events-tracker-admin.php';
        require_once EVENTS_TRACKER_PLUGIN_DIR . 'admin/class-events-tracker-settings.php';

        // Public-facing functionality
        require_once EVENTS_TRACKER_PLUGIN_DIR . 'public/class-events-tracker-public.php';
        require_once EVENTS_TRACKER_PLUGIN_DIR . 'public/class-events-tracker-ajax.php';

        $this->loader = new Events_Tracker_Loader();
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     */
    private function define_admin_hooks() {
        $admin = new Events_Tracker_Admin();
        $settings = new Events_Tracker_Settings();

        // Admin menu & settings
        $this->loader->add_action('admin_menu', $admin, 'add_menu_pages');
        $this->loader->add_action('admin_init', $settings, 'register_settings');
        
        // Admin assets
        $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_scripts');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     */
    private function define_public_hooks() {
        $public = new Events_Tracker_Public();
        $ajax = new Events_Tracker_Ajax();

        // Public assets
        $this->loader->add_action('wp_enqueue_scripts', $public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $public, 'enqueue_scripts');
        
        // AJAX handlers for saving/removing events
        $this->loader->add_action('wp_ajax_events_tracker_save_event', $ajax, 'save_event');
        $this->loader->add_action('wp_ajax_events_tracker_remove_event', $ajax, 'remove_event');
        
        // Change the event URL to our custom URL
        $this->loader->add_filter('events_tracker_event_url', $this, 'modify_event_url', 10, 3);
    }
    
    /**
     * Add rewrite rules for single event pages - REMOVED
     * The plugin now uses shortcodes on regular pages instead of custom URL patterns
     * 
     * @since    1.0.0
     * @deprecated Since 1.1.0
     */
    public function add_rewrite_rules() {
        // Legacy functionality removed
    }
    
    /**
     * Add query vars for single event pages - REMOVED
     * The plugin now uses shortcodes on regular pages instead of custom URL patterns
     *
     * @since    1.0.0
     * @deprecated Since 1.1.0
     * @param    array    $query_vars    The array of query vars.
     * @return   array    The modified array of query vars.
     */
    public function add_query_vars($query_vars) {
        return $query_vars;
    }
    
    /**
     * Handle template for single event pages - REMOVED
     * The plugin now uses shortcodes on regular pages instead of custom URL patterns
     *
     * @since    1.0.0
     * @deprecated Since 1.1.0
     * @param    string    $template    The template to include.
     * @return   string    The modified template path.
     */
    public function handle_single_event_template($template) {
        return $template;
    }
    
    /**
     * Modify event URL to use our custom URL format
     *
     * @since    1.0.0
     * @param    string    $url       The original event URL.
     * @param    object    $event     The event object.
     * @param    int       $blog_id   The source blog ID.
     * @return   string    The modified event URL.
     */
    public function modify_event_url($url, $event, $blog_id) {
        if (!empty($event) && !empty($event->ID)) {
            // Get the custom single event page from settings
            $single_event_page_id = get_option('events_tracker_single_event_page', 0);
            
            if ($single_event_page_id > 0) {
                // Always use the block theme page with query parameter
                return add_query_arg('event_id', $event->ID, get_permalink($single_event_page_id));
            } else {
                // If no page is set, return to the events page to encourage admin to set up a page
                $events_page_id = get_option('events_tracker_events_page', 0);
                if ($events_page_id > 0) {
                    return add_query_arg('event_id', $event->ID, get_permalink($events_page_id));
                }
            }
        }
        
        // Default fallback
        return $url;
    }

    /**
     * Register all shortcodes
     *
     * @since    1.0.0
     */
    private function define_shortcodes() {
        $shortcodes = new Events_Tracker_Shortcodes();
        
        // Register shortcodes
        add_shortcode('events_tracker_upcoming', array($shortcodes, 'upcoming_events_shortcode'));
        add_shortcode('events_tracker_saved', array($shortcodes, 'saved_events_shortcode'));
        add_shortcode('events_tracker_single_event', array($shortcodes, 'single_event_shortcode'));
        add_shortcode('events_tracker_debug', array($this, 'debug_shortcode'));
    }
    
    /**
     * Debug shortcode to display information about rewrite rules and events
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string   HTML output for the shortcode.
     */
    public function debug_shortcode($atts) {
        // Only show to administrators
        if (!current_user_can('manage_options')) {
            return '<p>' . __('You do not have permission to view this content.', 'events-tracker') . '</p>';
        }
        
        // Process attributes
        $atts = shortcode_atts(
            array(
                'action' => 'info', // info, flush_rules, test_event
                'event_id' => '',
            ),
            $atts,
            'events_tracker_debug'
        );
        
        $output = '<div class="events-tracker-debug-output" style="background:#f8f8f8; padding:15px; border:1px solid #ddd; font-family:monospace;">';
        $output .= '<h3>Events Tracker Debug Information</h3>';
        
        switch ($atts['action']) {
            case 'flush_rules':
                // Flush the rewrite rules
                flush_rewrite_rules();
                $output .= '<p style="color:green;">Rewrite rules have been flushed.</p>';
                // Fall through to show info
                
            case 'info':
                // Show active rewrite rules
                global $wp_rewrite;
                $output .= '<h4>Current Rewrite Rules:</h4>';
                $output .= '<table border="1" cellpadding="5" style="border-collapse:collapse;">';
                $output .= '<tr><th>Pattern</th><th>Rewrite</th></tr>';
                
                $rules = $wp_rewrite->wp_rewrite_rules();
                $plugin_rules = array_filter($rules, function($key) {
                    return strpos($key, $this->event_slug) === 0;
                }, ARRAY_FILTER_USE_KEY);
                
                foreach ($plugin_rules as $pattern => $rewrite) {
                    $output .= '<tr>';
                    $output .= '<td>' . esc_html($pattern) . '</td>';
                    $output .= '<td>' . esc_html($rewrite) . '</td>';
                    $output .= '</tr>';
                }
                
                if (empty($plugin_rules)) {
                    $output .= '<tr><td colspan="2">No matching rewrite rules found.</td></tr>';
                }
                
                $output .= '</table>';
                
                // Show registered query vars
                $output .= '<h4>Registered Query Vars:</h4>';
                $output .= '<pre>' . esc_html(implode(', ', $wp_rewrite->public_query_vars)) . '</pre>';
                
                break;
                
            case 'test_event':
                // Test retrieving event by ID
                if (empty($atts['event_id'])) {
                    $output .= '<p style="color:red;">Please provide an event_id parameter to test.</p>';
                    break;
                }
                
                $event_id = intval($atts['event_id']);
                $multisite = new Events_Tracker_Multisite();
                $blog_id = $multisite->get_source_blog_id();
                
                $output .= '<h4>Testing Event Retrieval:</h4>';
                $output .= '<p>Attempting to get event ID ' . $event_id . ' from blog ID ' . $blog_id . '</p>';
                
                $event = Events_Tracker_Event::get_event($blog_id, $event_id);
                
                if ($event) {
                    $output .= '<p style="color:green;">Event found: ' . esc_html($event->post_title) . '</p>';
                    
                    // Get and display event details
                    $details = Events_Tracker_Event::get_event_details($blog_id, $event);
                    $output .= '<h5>Event Details:</h5>';
                    $output .= '<pre>' . esc_html(print_r($details, true)) . '</pre>';
                } else {
                    $output .= '<p style="color:red;">Event not found!</p>';
                }
                
                break;
        }
        
        $output .= '</div>';
        
        return $output;
    }

    /**
     * Run the plugin
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }
}