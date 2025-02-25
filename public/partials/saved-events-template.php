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

<style>
    .events-tracker-card-grid {
        display: flex;
        flex-direction: column;
        gap: 20px;
        margin-bottom: 30px;
    }
    .events-tracker-event-card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        transition: transform 0.2s, box-shadow 0.2s;
        background: white;
    }
    .events-tracker-event-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .events-tracker-event-card-header {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
    }
    .events-tracker-event-card-title {
        margin: 0 0 10px 0;
        font-size: 18px;
        font-weight: bold;
    }
    .events-tracker-event-card-title a {
        color: #0073aa;
        text-decoration: none;
    }
    .events-tracker-event-card-categories {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-bottom: 10px;
    }
    .events-tracker-event-category {
        font-size: 12px;
        padding: 3px 8px;
        background: #f0f0f0;
        border-radius: 12px;
        color: #666;
    }
    .events-tracker-event-card-body {
        padding: 15px;
    }
    .events-tracker-event-info {
        margin-bottom: 10px;
    }
    .events-tracker-event-info-label {
        font-weight: bold;
        color: #555;
        margin-right: 5px;
    }
    .events-tracker-event-card-footer {
        padding: 15px;
        border-top: 1px solid #f0f0f0;
        background: #f9f9f9;
        text-align: right;
    }
    .events-tracker-save-event,
    .events-tracker-remove-event {
        padding: 8px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        background: #0073aa;
        color: white;
        font-size: 14px;
    }
    .events-tracker-remove-event {
        background: #aa0000;
    }
    .events-tracker-event-cost {
        display: inline-block;
        margin-left: 10px;
        padding: 3px 8px;
        background: #f8f8f8;
        border-radius: 4px;
        color: #666;
        font-size: 14px;
    }
    .events-tracker-saved-events-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .events-tracker-view-all .button {
        padding: 8px 15px;
        background: #0073aa;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        display: inline-block;
    }
</style>

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
                        <span class="events-tracker-event-date"><?php echo $formatted_date; ?></span>
                    </div>
                    
                    <?php if (!empty(trim($formatted_venue))) : ?>
                    <div class="events-tracker-event-info">
                        <span class="events-tracker-event-info-label"><?php _e('Location:', 'events-tracker'); ?></span>
                        <span class="events-tracker-event-venue"><?php echo $formatted_venue; ?></span>
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
    
    <!-- JavaScript for handling event removal UI -->
    <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        // Handle Remove button clicks
        document.querySelectorAll('.events-tracker-remove-event').forEach(function(button) {
            button.addEventListener('click', function() {
                // Hide the card when remove is clicked (after a short delay to let the AJAX complete)
                setTimeout(function() {
                    const eventCard = button.closest('.events-tracker-event-card');
                    if (eventCard) {
                        eventCard.style.opacity = '0.5';
                        eventCard.style.transition = 'opacity 0.5s ease';
                        
                        setTimeout(function() {
                            eventCard.style.display = 'none';
                        }, 500);
                    }
                }, 300);
            });
        });
    });
    </script>
</div>