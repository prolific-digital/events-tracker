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
                <tr>
                    <th scope="row">category</th>
                    <td><?php _e('Filter events by category slug.', 'events-tracker'); ?></td>
                    <td><code>[events_tracker_upcoming category="featured"]</code></td>
                </tr>
                <tr>
                    <th scope="row">tag</th>
                    <td><?php _e('Filter events by tag slug.', 'events-tracker'); ?></td>
                    <td><code>[events_tracker_upcoming tag="conference"]</code></td>
                </tr>
                <tr>
                    <th scope="row">start_date</th>
                    <td><?php _e('Start date for events to display (YYYY-MM-DD format).', 'events-tracker'); ?></td>
                    <td><code>[events_tracker_upcoming start_date="2025-03-01"]</code></td>
                </tr>
                <tr>
                    <th scope="row">end_date</th>
                    <td><?php _e('End date for events to display (YYYY-MM-DD format).', 'events-tracker'); ?></td>
                    <td><code>[events_tracker_upcoming end_date="2025-12-31"]</code></td>
                </tr>
            </table>
            
            <h4><?php _e('Notes', 'events-tracker'); ?></h4>
            <ul>
                <li><?php _e('Event listings are visible to all users, but "Add to My List" functionality is only available to logged-in users.', 'events-tracker'); ?></li>
                <li><?php _e('Pulls events from the source blog configured in settings.', 'events-tracker'); ?></li>
                <li><?php _e('Events are ordered by date (ascending).', 'events-tracker'); ?></li>
                <li><?php _e('Only shows events with dates equal to or after the current date (unless start_date is specified).', 'events-tracker'); ?></li>
                <li><?php _e('Users can add events to their personal list via AJAX (no page reload).', 'events-tracker'); ?></li>
                <li><?php _e('Event titles link to single event pages that show complete event details.', 'events-tracker'); ?></li>
                <li><?php _e('Includes built-in pagination and filtering options.', 'events-tracker'); ?></li>
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
                <li><?php _e('Only visible to logged-in users (shows a login prompt for logged-out users).', 'events-tracker'); ?></li>
                <li><?php _e('Pulls events from the source blog configured in settings.', 'events-tracker'); ?></li>
                <li><?php _e('Shows only events that the current user has saved.', 'events-tracker'); ?></li>
                <li><?php _e('Users can remove events from their personal list via AJAX (no page reload).', 'events-tracker'); ?></li>
                <li><?php _e('Event titles link to single event pages that show complete event details.', 'events-tracker'); ?></li>
                <li><?php _e('Can be filtered to show only upcoming events or include past events (based on plugin settings).', 'events-tracker'); ?></li>
            </ul>
        </div>
        
        <div class="events-tracker-card">
            <h3><?php _e('Single Event Pages', 'events-tracker'); ?></h3>
            <p><?php _e('The plugin creates single event pages accessible at the following URL pattern:', 'events-tracker'); ?></p>
            
            <code><?php echo home_url('tracked-event/EVENT_ID'); ?></code>
            
            <h4><?php _e('Features', 'events-tracker'); ?></h4>
            <ul>
                <li><?php _e('Displays complete event details including title, date/time, venue, and cost', 'events-tracker'); ?></li>
                <li><?php _e('Shows "Add to My List" buttons for logged-in users', 'events-tracker'); ?></li>
                <li><?php _e('Renders event content using WordPress block rendering', 'events-tracker'); ?></li>
                <li><?php _e('Includes navigation links back to event listings', 'events-tracker'); ?></li>
                <li><?php _e('Uses clean, semantic HTML that can be easily styled with CSS', 'events-tracker'); ?></li>
            </ul>
        </div>

        <div class="events-tracker-card">
            <h3><?php _e('Usage Examples', 'events-tracker'); ?></h3>
            <p><?php _e('You can create an events page with both shortcodes to allow users to browse upcoming events and view their saved events.', 'events-tracker'); ?></p>
            
            <h4><?php _e('Basic Usage', 'events-tracker'); ?></h4>
            <pre>
&lt;h2&gt;Upcoming Events&lt;/h2&gt;
[events_tracker_upcoming per_page="5"]

&lt;h2&gt;My Saved Events&lt;/h2&gt;
[events_tracker_saved]
            </pre>

            <h4><?php _e('Advanced Usage with Filtering', 'events-tracker'); ?></h4>
            <pre>
&lt;h2&gt;Featured Events&lt;/h2&gt;
[events_tracker_upcoming per_page="3" category="featured"]

&lt;h2&gt;Upcoming Conferences&lt;/h2&gt;
[events_tracker_upcoming per_page="5" tag="conference" start_date="<?php echo date('Y-m-d'); ?>" end_date="<?php echo date('Y-m-d', strtotime('+3 months')); ?>"]

&lt;h2&gt;My Saved Events&lt;/h2&gt;
[events_tracker_saved per_page="10"]
            </pre>
        </div>
    </div>
</div>