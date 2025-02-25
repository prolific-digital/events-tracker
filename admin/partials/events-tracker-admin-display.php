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

// Get some stats
$users_with_saved_events = 0;
$total_saved_events = 0;

// Get all users with saved events
$users_with_meta = get_users(array(
    'meta_key' => 'events_tracker_saved_events',
    'count_total' => true,
));

$users_with_saved_events = $users_with_meta;

// Calculate total saved events
if ($users_with_saved_events > 0) {
    $user_query = new WP_User_Query(array(
        'meta_key' => 'events_tracker_saved_events',
        'fields' => 'ID',
    ));
    
    $user_ids = $user_query->get_results();
    
    foreach ($user_ids as $user_id) {
        $saved_events = get_user_meta($user_id, 'events_tracker_saved_events', true);
        if (is_array($saved_events)) {
            $total_saved_events += count($saved_events);
        }
    }
}
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
            <h2><?php _e( 'Usage Statistics', 'events-tracker' ); ?></h2>
            <div class="events-tracker-content">
                <p>
                    <strong><?php _e( 'Users with Saved Events:', 'events-tracker' ); ?></strong> 
                    <?php echo esc_html($users_with_saved_events); ?>
                </p>
                <p>
                    <strong><?php _e( 'Total Saved Events:', 'events-tracker' ); ?></strong> 
                    <?php echo esc_html($total_saved_events); ?>
                </p>
            </div>
        </div>
        
        <div class="events-tracker-card">
            <h2><?php _e( 'Shortcodes', 'events-tracker' ); ?></h2>
            <div class="events-tracker-content">
                <p>
                    <code>[events_tracker_upcoming]</code> - 
                    <?php _e('Displays a list of upcoming events with pagination.', 'events-tracker'); ?>
                </p>
                <p>
                    <code>[events_tracker_saved]</code> - 
                    <?php _e('Displays a list of events saved by the current user.', 'events-tracker'); ?>
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