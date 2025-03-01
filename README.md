# Events Tracker for The Events Calendar

A WordPress plugin that allows authenticated users to view and save upcoming events from The Events Calendar in a multisite environment.

## Description

Events Tracker is an add-on for The Events Calendar plugin, designed for WordPress multisite environments. It allows authenticated users to view a paginated list of upcoming events and add events to their personal saved list. The plugin can pull events from any site in the multisite network that has The Events Calendar installed.

## Core Features

- Display paginated lists of upcoming events from The Events Calendar
- Allow authenticated users to save events to a personal list
- AJAX-based saving/removing of events (no page reload)
- Admin settings to configure which site to pull events from
- Shortcodes for easy implementation on any page
- Fully responsive design with customizable template structure
- Displays event details including date, time, venue, and cost
- Works across multisite networks without requiring The Events Calendar on the current site
- Block editor integration for single event display
- Featured image support with customizable image sizes
- Customizable button text for "Add to My List" and "Remove" actions
- Clean, semantic HTML with developer-friendly markup

## Shortcodes

### Upcoming Events

```
[events_tracker_upcoming per_page="10" category="featured" start_date="2025-03-01" end_date="2025-12-31" add_text="Add to Trip" remove_text="Remove from Trip" view_details_text="See Details"]
```

Displays a paginated list of upcoming events with "Add to My List" buttons. Only logged-in users can see the save functionality, but anyone can view the event listings.

Parameters:
- `per_page`: Number of events to display per page (default: 10)
- `category`: Filter events by category slug (default: show all categories)
- `start_date`: Start date for events to display in YYYY-MM-DD format (default: current date)
- `end_date`: End date for events to display in YYYY-MM-DD format (default: no end date limit)
- `tag`: Filter events by tag slug (default: show all tags)
- `add_text`: Custom text for the "Add to My List" button (default: "Add to My List")
- `remove_text`: Custom text for the "Remove from My List" button (default: "Remove from My List")
- `view_details_text`: Custom text for the "View Details" link (default: "View Details")

### Saved Events

```
[events_tracker_saved per_page="10" remove_text="Remove from Trip" view_details_text="See Details"]
```

Displays a list of events that the current user has saved to their personal list. Only logged-in users can see this content.

Parameters:
- `per_page`: Number of events to display per page (default: 10)
- `remove_text`: Custom text for the "Remove from My List" button (default: "Remove from My List")
- `view_details_text`: Custom text for the "View Details" link (default: "View Details")

### Combined Usage

You can combine shortcodes on a single page to create a complete event management interface:

```
<h2>Upcoming Events</h2>
[events_tracker_upcoming per_page="5"]

<h2>My Saved Events</h2>
[events_tracker_saved]
```

## Single Event Display

The plugin provides a flexible shortcode for displaying single events in block theme templates:

```
[events_tracker_single_event]
```

This shortcode is the recommended way to display individual events on your site. You simply add this shortcode to a page created with the block editor, then set that page as your "Single Event Page Template" in the plugin settings. When users click on an event title from the listings, they'll be directed to this page with the event details automatically loaded.

### How to Set Up Single Event Display

1. Create a new page using the block editor (e.g., "Event Details")
2. Add any header, content blocks, or custom layout elements you want
3. Insert the `[events_tracker_single_event]` shortcode where you want the event content to appear
4. Go to Events Tracker Settings → Select this page as your "Single Event Page Template"
5. Events will now link to this page with the event_id passed as a URL parameter

### Shortcode Parameters

The shortcode offers many customization options:

- `event_id`: The ID of the event to display (default: detected from URL parameter)
- `show_title`: Whether to display the event title (yes/no, default: yes)
- `show_meta`: Whether to display event meta info (date, time, venue) (yes/no, default: yes)
- `show_cost`: Whether to display the event cost (yes/no, default: yes)
- `show_content`: Whether to display the event content (yes/no, default: yes)
- `show_actions`: Whether to display the save/remove buttons (yes/no, default: yes)
- `show_navigation`: Whether to show navigation links to event listings (yes/no, default: no)
- `show_image`: Whether to display the featured image (yes/no, default: yes)
- `image_size`: Size of the featured image (thumbnail, medium, large, full, default: large)
- `add_text`: Custom text for the "Add to My List" button (default: "Add to My List")
- `remove_text`: Custom text for the "Remove from My List" button (default: "Remove from My List")

### Examples

**Basic usage in a block theme template:**
```
[events_tracker_single_event]
```

**Custom display with specific components:**
```
[events_tracker_single_event show_image="yes" image_size="medium" add_text="Add to Trip" show_navigation="no"]
```

**Display a specific event regardless of URL parameter:**
```
[events_tracker_single_event event_id="123"]
```

**Simplified display for embedding in other content:**
```
[events_tracker_single_event show_title="no" show_actions="no" show_navigation="no"]
```

## Installation

1. Upload the `events-tracker` folder to the `/wp-content/plugins/` directory
2. Activate the Events Tracker plugin through the 'Plugins' menu in WordPress
3. Configure the plugin settings under 'Events Tracker' in the admin menu
4. Add the shortcodes to any page where you want to display events
5. Set up any pages that will use the single event template

## Configuration

1. Go to the Events Tracker settings page in the WordPress admin
2. Set the "Source Blog ID" to the site in your multisite network that contains the events
3. Make sure The Events Calendar is installed and active on the source site
4. Set the "Events Per Page" to control pagination
5. Choose whether to display past events in the saved events list
6. Select a page to use for the events list display
7. **Important:** Select a page with the `[events_tracker_single_event]` shortcode as your "Single Event Page Template"
8. Save your settings

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- The Events Calendar 5.0.0 or higher (required only on the source site)
- Multisite environment (though it will work in single-site mode too)

## How it Works

1. The plugin fetches events from The Events Calendar in the specified source site
2. Users can browse upcoming events and save them to their personal list
3. Saved events are stored in user meta data for easy retrieval
4. All interaction happens via AJAX for a smooth user experience
5. Single event pages use direct database queries to bypass blog switching limitations

## Multisite Usage Scenarios

This plugin supports several multisite usage patterns:

1. **Event Hub Model**: Install The Events Calendar on one "hub" site where all events are managed, then use Events Tracker on other sites to display those events.

2. **Distributed Management**: Install The Events Calendar on multiple sites where different teams manage events, then use Events Tracker on any site to display events from any source.

3. **Single Site**: Install both The Events Calendar and Events Tracker on the same site (works in both multisite and standalone WordPress).

## Technical Notes

- Event data is retrieved using The Events Calendar's post type and meta fields
- The plugin uses direct database queries for reliable cross-site data retrieval
- User saved events are stored in the `events_tracker_saved_events` user meta field as an array of event IDs
- The plugin includes security measures like nonce verification and capability checks
- Template files use semantic class names for easy CSS customization
- Single event pages display content using WordPress block rendering functions
- Featured images are properly handled across sites in a multisite network
- Shortcode attributes allow for complete customization of event display
- Button text can be customized to match specific use cases (e.g., "Add to Trip")

## The Events Calendar Integration

The plugin integrates with The Events Calendar by:

- Using the tribe_events post type to fetch event data
- Reading event details from meta fields (_EventStartDate, _EventEndDate, etc.)
- Reading venue details from venue posts
- Displaying events similarly to The Events Calendar's own views
- Supporting The Events Calendar's categories for filtering events

## License

This plugin is licensed under the GPL v2 or later.

---

**Note:** This plugin requires The Events Calendar to be installed and activated on the source site (the site from which events are being pulled). The Events Calendar is NOT required on the site where Events Tracker is installed if it's pulling events from another site in the network.

## Customization

The plugin is designed to be easily customizable:

1. **CSS Styling**: All templates use semantic class names that can be targeted with your theme's CSS
2. **Template Override**: Copy template files from the plugin's `public/partials` directory to your theme's `events-tracker` directory to override them
3. **Filter Hooks**: Use WordPress filter hooks to modify data before it's displayed
4. **Shortcode Attributes**: Use the many shortcode attributes to customize event display without touching code
5. **Block Editor Integration**: Create custom page layouts with the WordPress block editor and insert event content where you want it
6. **Custom Button Text**: Personalize the button text to match your specific use case (e.g., "Add to Trip" instead of "Add to My List")

## Created By

Prolific Digital - [prolificdigital.com](https://prolificdigital.com)