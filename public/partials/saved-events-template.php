<?php
/**
 * Template for displaying saved events from The Events Calendar
 *
 * @since      1.0.0
 * @package    Events_Tracker
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

$multisite = new Events_Tracker_Multisite();
$blog_id = $multisite->get_source_blog_id();
?>

<div class="events-tracker-saved-events">
    <div class="events-tracker-saved-events-header">
        <h3><?php _e('My Saved Events', 'events-tracker'); ?></h3>
        <p class="events-tracker-view-all">
            <a href="<?php echo esc_url(add_query_arg('list', 'upcoming', get_permalink())); ?>" class="button">
                <?php _e('View All Upcoming Events', 'events-tracker'); ?>
            </a>
        </p>
    </div>
    
    <!-- Card Grid Layout -->
    <div class="events-tracker-card-grid">
        <?php foreach ($saved_events as $event) : 
            // Get the event details (already fetched in the shortcode)
            $event_details = $event->details;
            
            // Get event categories (if available)
            $categories = isset($event->categories) ? $event->categories : array();
            
            // Format the date and venue information for display
            $start_date = isset($event_details['start_date']) ? $event_details['start_date'] : '';
            $end_date = isset($event_details['end_date']) ? $event_details['end_date'] : '';
            $formatted_date = Events_Tracker_Event::format_event_date($start_date, $end_date);
            
            $formatted_venue = Events_Tracker_Event::format_venue_location($event_details);
        ?>
            <div class="events-tracker-event-card" data-event-id="<?php echo esc_attr($event->ID); ?>">
                <div class="events-tracker-event-card-header">
                    <h3 class="events-tracker-event-card-title">
                        <a href="<?php echo esc_url($event_details['url']); ?>" target="_blank">
                            <?php echo esc_html($event->post_title); ?>
                        </a>
                        <?php if (!empty($event_details['cost'])) : ?>
                            <span class="events-tracker-event-cost">
                                <?php echo esc_html($event_details['cost']); ?>
                            </span>
                        <?php endif; ?>
                    </h3>
                    
                    <?php if (!empty($categories)) : ?>
                    <div class="events-tracker-event-card-categories">
                        <?php foreach ($categories as $category) : ?>
                            <span class="events-tracker-event-category">
                                <?php echo esc_html($category->name); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="events-tracker-event-card-body">
                    <div class="events-tracker-event-info">
                        <span class="events-tracker-event-info-label"><?php _e('Date:', 'events-tracker'); ?></span>
                        <span class="events-tracker-event-date"><?php echo wp_kses_post($formatted_date); ?></span>
                    </div>
                    
                    <?php if (!empty(trim($formatted_venue))) : ?>
                    <div class="events-tracker-event-info">
                        <span class="events-tracker-event-info-label"><?php _e('Location:', 'events-tracker'); ?></span>
                        <span class="events-tracker-event-venue"><?php echo wp_kses_post($formatted_venue); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="events-tracker-event-card-footer">
                    <button class="events-tracker-remove-event" data-event-id="<?php echo esc_attr($event->ID); ?>">
                        <?php _e('Remove from My List', 'events-tracker'); ?>
                    </button>
                    <span class="events-tracker-event-status"></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="events-tracker-powered-by">
        <p><?php _e('Powered by', 'events-tracker'); ?> 
           <a href="https://theeventscalendar.com/" target="_blank">The Events Calendar</a></p>
    </div>
    
</div>