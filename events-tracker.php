<?php
/**
 * Plugin Name: Events Tracker for The Events Calendar
 * Plugin URI: https://prolificdigital.com/events-tracker
 * Description: Allows users to track and save upcoming events from The Events Calendar in a multisite environment
 * Version: 1.0.0
 * Author: Prolific Digital
 * Author URI: https://prolificdigital.com
 * Text Domain: events-tracker
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires at least: 5.0
 * Requires PHP: 7.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Define plugin constants
 */
define( 'EVENTS_TRACKER_VERSION', '1.0.0' );
define( 'EVENTS_TRACKER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'EVENTS_TRACKER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'EVENTS_TRACKER_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'EVENTS_TRACKER_TEC_MIN_VERSION', '5.0.0' );

/**
 * Check if The Events Calendar is active in target site
 * 
 * @param int $blog_id The blog ID to check, defaults to current blog
 * @return bool Whether The Events Calendar is active in the specified blog
 */
function events_tracker_check_tec_in_blog($blog_id = null) {
    if (is_null($blog_id)) {
        $blog_id = get_current_blog_id();
    }
    
    // For current blog, check if The Events Calendar is active locally
    if ($blog_id == get_current_blog_id()) {
        // First try checking if the tribe_events post type exists
        if (post_type_exists('tribe_events')) {
            return true;
        }
        
        // As a fallback, check for the class
        if (class_exists('Tribe__Events__Main')) {
            return true;
        }
        
        return false;
    }
    
    // For remote blog in multisite, we need a different approach
    if (is_multisite()) {
        global $wpdb;
        
        // Get the blog's table prefix
        $blog_prefix = $wpdb->get_blog_prefix($blog_id);
        
        // Check if the blog exists
        if (!get_site($blog_id)) {
            return false;
        }
        
        // Check if the post type exists in this blog by looking for posts of that type
        $query = $wpdb->prepare(
            "SELECT COUNT(*) 
             FROM {$blog_prefix}posts 
             WHERE post_type = %s 
             LIMIT 1",
            'tribe_events'
        );
        
        $count = $wpdb->get_var($query);
        
        return ($count > 0);
    }
    
    return false;
}

/**
 * Check plugin dependencies when used on primary site
 */
function events_tracker_check_dependencies() {
    // Check if we're getting events from the current site or another site
    $source_blog_id = get_option('events_tracker_source_blog_id', get_current_blog_id());
    
    // Only require TEC locally if we're getting events from the current site
    if ($source_blog_id == get_current_blog_id()) {
        // If we're pulling events from the current site, we need TEC installed here
        if (!events_tracker_check_tec_in_blog()) {
            add_action('admin_notices', 'events_tracker_missing_tec_notice');
            return false;
        }
        
        // Check for minimum version of The Events Calendar
        if (class_exists('Tribe__Events__Main')) {
            $tec_version = Tribe__Events__Main::VERSION;
            if (version_compare($tec_version, EVENTS_TRACKER_TEC_MIN_VERSION, '<')) {
                add_action('admin_notices', 'events_tracker_outdated_tec_notice');
                return false;
            }
        }
    } else {
        // If we're pulling events from another site, check if TEC exists there
        if (!events_tracker_check_tec_in_blog($source_blog_id)) {
            add_action('admin_notices', 'events_tracker_remote_tec_notice');
            return true; // still allow plugin to run, but with a warning
        }
    }
    
    return true;
}

/**
 * Display admin notice if The Events Calendar is not active on current site
 */
function events_tracker_missing_tec_notice() {
    ?>
    <div class="notice notice-error is-dismissible">
        <p><?php _e('The Events Tracker is configured to pull events from this site, but The Events Calendar plugin is not active here. Please either install and activate The Events Calendar on this site, or change the source site in the Events Tracker settings.', 'events-tracker'); ?></p>
    </div>
    <?php
}

/**
 * Display admin notice if The Events Calendar version is outdated
 */
function events_tracker_outdated_tec_notice() {
    ?>
    <div class="notice notice-error is-dismissible">
        <p><?php 
            printf(
                __('Events Tracker requires The Events Calendar version %s or higher. Please update The Events Calendar to use this plugin.', 'events-tracker'),
                EVENTS_TRACKER_TEC_MIN_VERSION
            ); 
        ?></p>
    </div>
    <?php
}

/**
 * Display admin notice if The Events Calendar is not active on the remote site
 */
function events_tracker_remote_tec_notice() {
    $source_blog_id = get_option('events_tracker_source_blog_id', get_current_blog_id());
    $site_name = get_blog_details($source_blog_id)->blogname;
    ?>
    <div class="notice notice-warning is-dismissible">
        <p><?php 
            printf(
                __('Events Tracker is configured to pull events from site "%s" (ID: %d), but The Events Calendar plugin does not appear to be active on that site. No events will be displayed until The Events Calendar is activated on the source site.', 'events-tracker'),
                esc_html($site_name),
                $source_blog_id
            ); 
        ?></p>
    </div>
    <?php
}

/**
 * Include required files
 */
require_once EVENTS_TRACKER_PLUGIN_DIR . 'includes/class-events-tracker.php';

/**
 * The code that runs during plugin activation.
 */
function activate_events_tracker() {
    // Get the source blog ID (default to current blog)
    $source_blog_id = get_option('events_tracker_source_blog_id', get_current_blog_id());
    
    // If we're getting events from the current site, require TEC to be active
    if ($source_blog_id == get_current_blog_id() && !events_tracker_check_tec_in_blog()) {
        // Deactivate the plugin if pulling from current site and TEC not active
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            __('Events Tracker is set to pull events from the current site, but The Events Calendar plugin is not active. Please either install and activate The Events Calendar, or change the source site in the plugin settings after activation.', 'events-tracker'),
            'Plugin Activation Error',
            array('back_link' => true)
        );
    }
    
    require_once EVENTS_TRACKER_PLUGIN_DIR . 'includes/class-events-tracker-activator.php';
    Events_Tracker_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_events_tracker() {
    require_once EVENTS_TRACKER_PLUGIN_DIR . 'includes/class-events-tracker-deactivator.php';
    Events_Tracker_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_events_tracker' );
register_deactivation_hook( __FILE__, 'deactivate_events_tracker' );

/**
 * Begins execution of the plugin.
 */
function run_events_tracker() {
    // Only run if dependencies are met
    if ( events_tracker_check_dependencies() ) {
        $plugin = new Events_Tracker();
        $plugin->run();
    }
}
add_action( 'plugins_loaded', 'run_events_tracker', 20 );