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
            <h4><?php _e('Shortcode Usage', 'events-tracker'); ?></h4>
            <p>
                <?php _e('Use the shortcode [events_tracker_upcoming] on any page to display the list of upcoming events.', 'events-tracker'); ?>
            </p>
            <p>
                <?php _e('Use the shortcode [events_tracker_saved] to show a user\'s saved events.', 'events-tracker'); ?>
            </p>
            <p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=events-tracker-shortcodes')); ?>">
                    <?php _e('Learn more about available shortcodes', 'events-tracker'); ?>
                </a>
            </p>
        </div>
    </div>
</div>