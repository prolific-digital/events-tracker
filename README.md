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
- Individual event pages with clean, developer-friendly markup

## Shortcodes

### Upcoming Events

```
[events_tracker_upcoming per_page="10"]
```

Displays a paginated list of upcoming events with "Add to My List" buttons. Only logged-in users can see the save functionality, but anyone can view the event listings.

Parameters:
- `per_page`: Number of events to display per page (default: 10)

### Saved Events

```
[events_tracker_saved]
```

Displays a list of events that the current user has saved to their personal list. Only logged-in users can see this content.

## Single Event Pages

The plugin creates a custom rewrite rule for single event pages, allowing visitors to view detailed information about a specific event. These pages are accessed at:

```
/tracked-event/{event_id}/
```

Single event pages include:
- Event title and details
- Date and time information
- Venue and location details
- Event cost (if available)
- "Add to My List" functionality for logged-in users
- Full event content rendered from blocks

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
7. Save your settings

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

The plugin templates are designed to be easily customizable:

1. **CSS Styling**: All templates use semantic class names that can be targeted with your theme's CSS
2. **Template Override**: Copy template files from the plugin's `public/partials` directory to your theme's `events-tracker` directory to override them
3. **Filter Hooks**: Use WordPress filter hooks to modify data before it's displayed

## Created By

Prolific Digital - [prolificdigital.com](https://prolificdigital.com)