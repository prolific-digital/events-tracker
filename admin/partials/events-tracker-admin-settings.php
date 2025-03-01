<?php
/**
 * Admin settings page for the plugin
 *
 * @since      1.0.0
 * @package    Events_Tracker
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    
    <p><?php _e('Configure how the Events Tracker plugin should work in your site.', 'events-tracker'); ?></p>
    
    <form method="post" action="options.php">
        <?php
        // This prints out all hidden setting fields
        settings_fields( 'events_tracker_settings' );
        do_settings_sections( 'events-tracker-settings' );
        submit_button();
        ?>
    </form>
    
    <div class="events-tracker-admin-help">
        <h3><?php _e('Help & Information', 'events-tracker'); ?></h3>
        
        <div class="events-tracker-card">
            <h4><?php _e('Multisite Configuration', 'events-tracker'); ?></h4>
            <p>
                <?php _e('When using Events Tracker in a multisite environment, you can specify which site to pull events from using the Source Blog ID setting.', 'events-tracker'); ?>
            </p>
            <p>
                <?php _e('The events must be created as posts of type "event" on the source blog, with meta fields for event details like date and location.', 'events-tracker'); ?>
            </p>
        </div>
        
        <div class="events-tracker-card">
            <h4><?php _e('Shortcode Overview', 'events-tracker'); ?></h4>
            <p>
                <?php _e('Events Tracker provides the following shortcodes:', 'events-tracker'); ?>
            </p>
            <ul>
                <li><strong>[events_tracker_upcoming]</strong> - <?php _e('Displays a paginated list of upcoming events with add/remove buttons', 'events-tracker'); ?></li>
                <li><strong>[events_tracker_saved]</strong> - <?php _e('Shows events the current user has saved to their personal list', 'events-tracker'); ?></li>
                <li><strong>[events_tracker_single_event]</strong> - <?php _e('Displays a single event in a block theme template with customizable components', 'events-tracker'); ?></li>
            </ul>
            <p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=events-tracker-shortcodes')); ?>">
                    <?php _e('View detailed shortcode documentation', 'events-tracker'); ?>
                </a>
            </p>
        </div>
    </div>
</div>