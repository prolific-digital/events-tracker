<?php
/**
 * Admin shortcodes info page for the plugin
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
    
    <div class="events-tracker-shortcodes-info">
        <p><?php _e('Events Tracker provides the following shortcodes for displaying events on your site.', 'events-tracker'); ?></p>
        
        <div class="events-tracker-card">
            <h3><?php _e('Upcoming Events Shortcode', 'events-tracker'); ?></h3>
            <code>[events_tracker_upcoming]</code>
            
            <h4><?php _e('Description', 'events-tracker'); ?></h4>
            <p><?php _e('Displays a paginated list of upcoming events with buttons to add events to a user\'s personal list.', 'events-tracker'); ?></p>
            
            <h4><?php _e('Attributes', 'events-tracker'); ?></h4>
            <table class="form-table">
                <tr>
                    <th scope="row">per_page</th>
                    <td><?php _e('Number of events to display per page.', 'events-tracker'); ?></td>
                    <td><code>[events_tracker_upcoming per_page="5"]</code></td>
                </tr>
            </table>
            
            <h4><?php _e('Notes', 'events-tracker'); ?></h4>
            <ul>
                <li><?php _e('Only visible to logged-in users.', 'events-tracker'); ?></li>
                <li><?php _e('Pulls events from the source blog configured in settings.', 'events-tracker'); ?></li>
                <li><?php _e('Events are ordered by date (ascending).', 'events-tracker'); ?></li>
                <li><?php _e('Only shows events with dates equal to or after the current date.', 'events-tracker'); ?></li>
                <li><?php _e('Users can add events to their personal list via AJAX (no page reload).', 'events-tracker'); ?></li>
            </ul>
        </div>
        
        <div class="events-tracker-card">
            <h3><?php _e('Saved Events Shortcode', 'events-tracker'); ?></h3>
            <code>[events_tracker_saved]</code>
            
            <h4><?php _e('Description', 'events-tracker'); ?></h4>
            <p><?php _e('Displays a list of events that the current user has saved to their personal list.', 'events-tracker'); ?></p>
            
            <h4><?php _e('Attributes', 'events-tracker'); ?></h4>
            <table class="form-table">
                <tr>
                    <th scope="row">per_page</th>
                    <td><?php _e('Number of events to display per page.', 'events-tracker'); ?></td>
                    <td><code>[events_tracker_saved per_page="10"]</code></td>
                </tr>
            </table>
            
            <h4><?php _e('Notes', 'events-tracker'); ?></h4>
            <ul>
                <li><?php _e('Only visible to logged-in users.', 'events-tracker'); ?></li>
                <li><?php _e('Pulls events from the source blog configured in settings.', 'events-tracker'); ?></li>
                <li><?php _e('Shows only events that the current user has saved.', 'events-tracker'); ?></li>
                <li><?php _e('Users can remove events from their personal list via AJAX (no page reload).', 'events-tracker'); ?></li>
            </ul>
        </div>
        
        <div class="events-tracker-card">
            <h3><?php _e('Usage Example', 'events-tracker'); ?></h3>
            <p><?php _e('You can create an events page with both shortcodes to allow users to browse upcoming events and view their saved events.', 'events-tracker'); ?></p>
            
            <pre>
&lt;h2&gt;Upcoming Events&lt;/h2&gt;
[events_tracker_upcoming per_page="5"]

&lt;h2&gt;My Saved Events&lt;/h2&gt;
[events_tracker_saved]
            </pre>
        </div>
    </div>
</div>