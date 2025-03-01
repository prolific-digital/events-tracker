<?php
/**
 * Admin dashboard display for the plugin
 *
 * @since      1.0.0
 * @package    Events_Tracker
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Get source blog ID
$multisite = new Events_Tracker_Multisite();
$blog_id = $multisite->get_source_blog_id();

// We no longer need usage statistics
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    
    <div class="events-tracker-dashboard">
        <div class="events-tracker-card">
            <h2><?php _e( 'Settings Summary', 'events-tracker' ); ?></h2>
            <div class="events-tracker-content">
                <p>
                    <strong><?php _e( 'Source Blog ID:', 'events-tracker' ); ?></strong> 
                    <?php echo esc_html($blog_id); ?>
                    <?php if (is_multisite() && ($site = get_site($blog_id))) : ?>
                        (<?php echo esc_html($site->blogname); ?>)
                    <?php endif; ?>
                </p>
                <p>
                    <strong><?php _e( 'Events Per Page:', 'events-tracker' ); ?></strong> 
                    <?php echo esc_html(get_option('events_tracker_events_per_page', 10)); ?>
                </p>
                <p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=events-tracker-settings')); ?>" class="button">
                        <?php _e('Edit Settings', 'events-tracker'); ?>
                    </a>
                </p>
            </div>
        </div>
        
        
        <div class="events-tracker-card">
            <h2><?php _e( 'Shortcodes', 'events-tracker' ); ?></h2>
            <div class="events-tracker-content">
                <p>
                    <code>[events_tracker_upcoming]</code> - 
                    <?php _e('Displays a paginated list of upcoming events with buttons to save events to a personal list.', 'events-tracker'); ?>
                </p>
                <p>
                    <code>[events_tracker_saved]</code> - 
                    <?php _e('Shows a list of events that the current user has saved to their personal list.', 'events-tracker'); ?>
                </p>
                <p>
                    <code>[events_tracker_single_event]</code> - 
                    <?php _e('Displays a single event with details, featured image, and action buttons in a block theme template.', 'events-tracker'); ?>
                </p>
                <p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=events-tracker-shortcodes')); ?>" class="button">
                        <?php _e('View Shortcode Details', 'events-tracker'); ?>
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>