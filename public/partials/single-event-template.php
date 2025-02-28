<?php

/**
 * Template for displaying a single event from The Events Calendar
 *
 * @since      1.0.0
 * @package    Events_Tracker
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get the event ID from the query var
$event_id = get_query_var('tracked_event_id');

// Get event using our direct database query method with hardcoded blog ID
$result = Events_Tracker_Event::get_single_event_direct($event_id);

// Extract the event object and details
$event = $result['event'];
$event_details = $result['details'];

// If we couldn't get the event, show an error page
if (!$event) {
    // Create a fake post for the "not found" page
    $event = new WP_Post((object) array(
        'ID' => 0,
        'post_title' => __('Event Not Found', 'events-tracker'),
        'post_content' => __('The event you are looking for could not be found.', 'events-tracker'),
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_name' => 'event-not-found',
    ));

    // Set up globals
    $GLOBALS['post'] = $event;
    setup_postdata($event);

    get_header();
?>
    <main id="primary" class="site-main">
        <section class="error-404 not-found">
            <header class="page-header">
                <h1 class="page-title"><?php _e('Event Not Found', 'events-tracker'); ?></h1>
            </header>

            <div class="page-content">
                <p><?php _e('The event you are looking for could not be found.', 'events-tracker'); ?></p>
                <p><a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('Return Home', 'events-tracker'); ?></a></p>
            </div>
        </section>
    </main>
<?php
    get_footer();
    return;
}

// Set up post data for the theme
$GLOBALS['post'] = $event;
setup_postdata($event);

// Check if this event is already saved by the user
$is_saved = false;
if (is_user_logged_in()) {
    $saved_event_ids = Events_Tracker_Event::get_saved_events();
    $is_saved = in_array($event->ID, $saved_event_ids);
}

// Get header
get_header();
?>

<main id="primary" class="site-main">
    <article id="event-<?php echo esc_attr($event->ID); ?>" class="events-tracker-single-event">
        <header class="entry-header">
            <h1 class="entry-title"><?php echo esc_html($event->post_title); ?></h1>
            
            <div class="event-meta">
                <?php if (!empty($event_details['start_date'])) : 
                    $formatted_date = Events_Tracker_Event::format_event_date(
                        $event_details['start_date'],
                        $event_details['end_date']
                    );
                ?>
                <div class="event-date-time">
                    <?php echo wp_kses_post($formatted_date); ?>
                </div>
                <?php endif; ?>
                
                <?php 
                $formatted_venue = Events_Tracker_Event::format_venue_location($event_details);
                if (!empty(trim($formatted_venue))) : 
                ?>
                <div class="event-location">
                    <?php echo wp_kses_post($formatted_venue); ?>
                </div>
                <?php endif; ?>
                
                <?php if (is_user_logged_in()) : ?>
                <div class="event-actions">
                    <?php if ($is_saved) : ?>
                    <button class="events-tracker-remove-event" data-event-id="<?php echo esc_attr($event->ID); ?>">
                        <?php _e('Remove from My List', 'events-tracker'); ?>
                    </button>
                    <?php else : ?>
                    <button class="events-tracker-save-event" data-event-id="<?php echo esc_attr($event->ID); ?>">
                        <?php _e('Add to My List', 'events-tracker'); ?>
                    </button>
                    <?php endif; ?>
                    <span class="events-tracker-event-status"></span>
                </div>
                <?php endif; ?>
            </div>
        </header>

        <?php if (!empty($event_details['cost'])) : ?>
        <div class="event-cost">
            <span class="event-cost-label"><?php _e('Cost:', 'events-tracker'); ?></span>
            <span class="event-cost-value"><?php echo esc_html($event_details['cost']); ?></span>
        </div>
        <?php endif; ?>

        <div class="entry-content">
            <?php
            $parsed_blocks = parse_blocks($event->post_content);
            foreach ($parsed_blocks as $block) {
                echo render_block($block);
            }
            ?>
        </div>

        <div class="entry-footer">
            <div class="event-navigation">
                <a href="<?php echo esc_url(add_query_arg('list', 'upcoming', get_permalink(get_option('events_tracker_events_page', 0)))); ?>" class="event-nav-link event-nav-all">
                    <?php _e('View All Upcoming Events', 'events-tracker'); ?>
                </a>
                <?php if (is_user_logged_in()) : ?>
                <a href="<?php echo esc_url(add_query_arg('list', 'saved', get_permalink(get_option('events_tracker_events_page', 0)))); ?>" class="event-nav-link event-nav-saved">
                    <?php _e('View My Saved Events', 'events-tracker'); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </article>
</main>

<?php
wp_reset_postdata();
get_footer();
