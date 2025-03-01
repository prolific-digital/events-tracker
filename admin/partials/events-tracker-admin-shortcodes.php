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
        <div class="events-tracker-card" style="background-color: #f8f9fa; padding: 15px; margin-bottom: 20px;">
            <h3><?php _e('Shortcodes Overview', 'events-tracker'); ?></h3>
            <p><?php _e('Events Tracker provides the following shortcodes for displaying events on your site:', 'events-tracker'); ?></p>
            <table class="form-table" style="margin-top: 10px;">
                <tr>
                    <th scope="row"><code>[events_tracker_upcoming]</code></th>
                    <td><?php _e('Displays a paginated list of upcoming events with buttons to save events to a personal list.', 'events-tracker'); ?></td>
                </tr>
                <tr>
                    <th scope="row"><code>[events_tracker_saved]</code></th>
                    <td><?php _e('Shows a list of events that the current user has saved to their personal list.', 'events-tracker'); ?></td>
                </tr>
                <tr>
                    <th scope="row"><code>[events_tracker_single_event]</code></th>
                    <td><?php _e('Displays a single event with details, featured image, and action buttons in a block theme template.', 'events-tracker'); ?></td>
                </tr>
            </table>
            <p style="margin-top: 15px;"><?php _e('Each shortcode has various parameters that allow you to customize the display. Detailed documentation for each shortcode is provided below.', 'events-tracker'); ?></p>
        </div>
        <p><?php _e('Detailed documentation for each shortcode:', 'events-tracker'); ?></p>
        
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
                <tr>
                    <th scope="row">add_text</th>
                    <td><?php _e('Custom text for the "Add to My List" button.', 'events-tracker'); ?></td>
                    <td><code>[events_tracker_upcoming add_text="Add to Trip"]</code></td>
                </tr>
                <tr>
                    <th scope="row">remove_text</th>
                    <td><?php _e('Custom text for the "Remove from My List" button.', 'events-tracker'); ?></td>
                    <td><code>[events_tracker_upcoming remove_text="Remove from Trip"]</code></td>
                </tr>
                <tr>
                    <th scope="row">view_details_text</th>
                    <td><?php _e('Custom text for the "View Details" link.', 'events-tracker'); ?></td>
                    <td><code>[events_tracker_upcoming view_details_text="See Event"]</code></td>
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
                <tr>
                    <th scope="row">remove_text</th>
                    <td><?php _e('Custom text for the "Remove from My List" button.', 'events-tracker'); ?></td>
                    <td><code>[events_tracker_saved remove_text="Remove from Trip"]</code></td>
                </tr>
                <tr>
                    <th scope="row">view_details_text</th>
                    <td><?php _e('Custom text for the "View Details" link.', 'events-tracker'); ?></td>
                    <td><code>[events_tracker_saved view_details_text="See Event"]</code></td>
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
            <h3><?php _e('Single Event Shortcode', 'events-tracker'); ?></h3>
            <code>[events_tracker_single_event]</code>
            
            <h4><?php _e('Description', 'events-tracker'); ?></h4>
            <p><?php _e('Displays a single event in a block theme template. This allows site admins to use the native block editor controls while displaying event content from the plugin.', 'events-tracker'); ?></p>
            
            <h4><?php _e('Attributes', 'events-tracker'); ?></h4>
            <table class="form-table">
                <tr>
                    <th scope="row">event_id</th>
                    <td><?php _e('The ID of the event to display. If not provided, will attempt to get event ID from URL.', 'events-tracker'); ?></td>
                    <td><code>[events_tracker_single_event event_id="123"]</code></td>
                </tr>
                <tr>
                    <th scope="row">show_title</th>
                    <td><?php _e('Whether to show the event title (yes/no).', 'events-tracker'); ?></td>
                    <td><code>[events_tracker_single_event show_title="no"]</code></td>
                </tr>
                <tr>
                    <th scope="row">show_meta</th>
                    <td><?php _e('Whether to show the event meta info like date/time and venue (yes/no).', 'events-tracker'); ?></td>
                    <td><code>[events_tracker_single_event show_meta="yes"]</code></td>
                </tr>
                <tr>
                    <th scope="row">show_cost</th>
                    <td><?php _e('Whether to show the event cost (yes/no).', 'events-tracker'); ?></td>
                    <td><code>[events_tracker_single_event show_cost="yes"]</code></td>
                </tr>
                <tr>
                    <th scope="row">show_content</th>
                    <td><?php _e('Whether to show the event content (yes/no).', 'events-tracker'); ?></td>
                    <td><code>[events_tracker_single_event show_content="yes"]</code></td>
                </tr>
                <tr>
                    <th scope="row">show_actions</th>
                    <td><?php _e('Whether to show the "Add to My List"/"Remove from My List" buttons (yes/no).', 'events-tracker'); ?></td>
                    <td><code>[events_tracker_single_event show_actions="yes"]</code></td>
                </tr>
                <tr>
                    <th scope="row">show_navigation</th>
                    <td><?php _e('Whether to show navigation links to event listings (yes/no). Note: Default is now "no" as navigation links are not typically needed.', 'events-tracker'); ?></td>
                    <td><code>[events_tracker_single_event show_navigation="no"]</code></td>
                </tr>
                <tr>
                    <th scope="row">add_text</th>
                    <td><?php _e('Custom text for the "Add to My List" button.', 'events-tracker'); ?></td>
                    <td><code>[events_tracker_single_event add_text="Add to Trip"]</code></td>
                </tr>
                <tr>
                    <th scope="row">remove_text</th>
                    <td><?php _e('Custom text for the "Remove from My List" button.', 'events-tracker'); ?></td>
                    <td><code>[events_tracker_single_event remove_text="Remove from Trip"]</code></td>
                </tr>
                <tr>
                    <th scope="row">show_image</th>
                    <td><?php _e('Whether to show the featured image (yes/no).', 'events-tracker'); ?></td>
                    <td><code>[events_tracker_single_event show_image="yes"]</code></td>
                </tr>
                <tr>
                    <th scope="row">image_size</th>
                    <td><?php _e('Featured image size (thumbnail, medium, large, full, or custom size).', 'events-tracker'); ?></td>
                    <td><code>[events_tracker_single_event image_size="medium"]</code></td>
                </tr>
            </table>
            
            <h4><?php _e('Notes', 'events-tracker'); ?></h4>
            <ul>
                <li><?php _e('This shortcode is designed for use in block theme templates, allowing you to place the event content anywhere on a custom page layout.', 'events-tracker'); ?></li>
                <li><?php _e('Each component of the event display can be toggled on/off using the shortcode attributes.', 'events-tracker'); ?></li>
                <li><?php _e('The event ID is typically passed via the URL query parameter (e.g., "?event_id=123").', 'events-tracker'); ?></li>
                <li><?php _e('To use this shortcode, create a page with your desired layout, add the shortcode, then select this page in the plugin settings as your "Single Event Page Template".', 'events-tracker'); ?></li>
                <li><?php _e('Add/Remove buttons are only visible to logged-in users.', 'events-tracker'); ?></li>
                <li><?php _e('Only the first block of the event content is displayed to keep the page concise.', 'events-tracker'); ?></li>
            </ul>
            
            <h4><?php _e('Usage Examples', 'events-tracker'); ?></h4>
            <p><?php _e('Basic usage in a block theme template:', 'events-tracker'); ?></p>
            <pre>[events_tracker_single_event]</pre>
            
            <p><?php _e('Custom display with specific components:', 'events-tracker'); ?></p>
            <pre>[events_tracker_single_event show_title="no" show_navigation="no" show_actions="yes"]</pre>
            
            <p><?php _e('Display a specific event:', 'events-tracker'); ?></p>
            <pre>[events_tracker_single_event event_id="123"]</pre>
        </div>
        
        <div class="events-tracker-card">
            <h3><?php _e('Single Event Display', 'events-tracker'); ?></h3>
            <p><?php _e('The plugin uses the block editor for displaying single events:', 'events-tracker'); ?></p>
            
            <h4><?php _e('Block Theme Page with Shortcode', 'events-tracker'); ?></h4>
            <p><?php _e('Uses a page you create with the block editor and the URL pattern:', 'events-tracker'); ?></p>
            <?php 
            $page_id = get_option('events_tracker_single_event_page', 0);
            $page_url = ($page_id > 0) ? get_permalink($page_id) : home_url('/your-custom-page/');
            ?>
            <code><?php echo $page_url; ?>?event_id=EVENT_ID</code>
            <p><?php _e('To set up single event display:', 'events-tracker'); ?></p>
            <ol>
                <li><?php _e('Create a new page with your desired layout using the block editor', 'events-tracker'); ?></li>
                <li><?php _e('Add the [events_tracker_single_event] shortcode where you want the event content to appear', 'events-tracker'); ?></li>
                <li><?php _e('Go to Events Tracker Settings and select this page as your "Single Event Page Template"', 'events-tracker'); ?></li>
            </ol>
            <p><?php _e('With this approach, you have full control over the page layout using the block editor, while the plugin handles displaying the event content.', 'events-tracker'); ?></p>
            
            <div class="notice notice-info" style="margin:15px 0;padding:10px;">
                <p><strong><?php _e('Important:', 'events-tracker'); ?></strong> <?php _e('You must set a page in the Events Tracker Settings as your "Single Event Page Template" for event links to work correctly.', 'events-tracker'); ?></p>
            </div>
        </div>

        <div class="events-tracker-card">
            <h3><?php _e('Usage Examples', 'events-tracker'); ?></h3>
            <p><?php _e('You can create an events page with these shortcodes to allow users to browse upcoming events and view their saved events.', 'events-tracker'); ?></p>
            
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
            
            <h4><?php _e('Using Single Event Shortcode in Block Themes', 'events-tracker'); ?></h4>
            <p><?php _e('Create a block theme page template for event details, add blocks for header, footer, and other elements, then add the single event shortcode:', 'events-tracker'); ?></p>
            <pre>
&lt;!-- wp:paragraph {"className":"event-intro"} --&gt;
&lt;p class="event-intro"&gt;Here is a detailed look at this event:&lt;/p&gt;
&lt;!-- /wp:paragraph --&gt;

&lt;!-- wp:shortcode --&gt;
[events_tracker_single_event]
&lt;!-- /wp:shortcode --&gt;

&lt;!-- wp:paragraph {"className":"event-footer"} --&gt;
&lt;p class="event-footer"&gt;Please register early as space is limited.&lt;/p&gt;
&lt;!-- /wp:paragraph --&gt;
            </pre>
            
            <p><?php _e('When a user visits a tracked event URL, the shortcode will automatically fetch and display the event data while preserving your custom page layout.', 'events-tracker'); ?></p>
        </div>
    </div>
</div>