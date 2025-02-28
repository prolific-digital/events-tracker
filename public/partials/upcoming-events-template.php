<?php
/**
 * Template for displaying upcoming events from The Events Calendar
 *
 * @since      1.0.0
 * @package    Events_Tracker
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get the current paged value
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$multisite = new Events_Tracker_Multisite();
$blog_id = $multisite->get_source_blog_id();

// Get available categories for the filter
$event_categories = array();
$switched = $multisite->switch_to_blog($blog_id);
if ($switched) {
    // Add debugging
    if (current_user_can('manage_options')) {
        echo "<!-- Checking for categories on blog ID: {$blog_id} -->";
    }
    
    // Check if tribe_events_cat taxonomy exists
    $taxonomies = get_taxonomies();
    $has_taxonomy = isset($taxonomies['tribe_events_cat']);
    
    if ($has_taxonomy) {
        // Try to get categories directly
        $terms = get_terms(array(
            'taxonomy' => 'tribe_events_cat',
            'hide_empty' => false, // Show all categories even if they don't have events
        ));
        
        // Check if we got valid terms and not an error
        if (!is_wp_error($terms) && is_array($terms)) {
            $event_categories = $terms;
            
            if (current_user_can('manage_options')) {
                echo "<!-- Found " . count($terms) . " categories -->";
            }
        } else {
            if (current_user_can('manage_options')) {
                if (is_wp_error($terms)) {
                    echo "<!-- Error getting terms: " . esc_html($terms->get_error_message()) . " -->";
                } else {
                    echo "<!-- No terms found or unexpected format returned -->";
                }
            }
        }
    } else {
        if (current_user_can('manage_options')) {
            echo "<!-- The 'tribe_events_cat' taxonomy does not exist on this blog -->";
        }
        
        // Try to get categories via direct database query
        global $wpdb;
        $blog_prefix = $wpdb->get_blog_prefix($blog_id);
        $term_taxonomy_table = $blog_prefix . 'term_taxonomy';
        $terms_table = $blog_prefix . 'terms';
        
        $query = $wpdb->prepare(
            "SELECT t.term_id, t.name, t.slug
            FROM {$terms_table} t
            JOIN {$term_taxonomy_table} tt ON t.term_id = tt.term_id
            WHERE tt.taxonomy = %s",
            'tribe_events_cat'
        );
        
        $results = $wpdb->get_results($query);
        
        if (!empty($results)) {
            $event_categories = $results;
            if (current_user_can('manage_options')) {
                echo "<!-- Found " . count($results) . " categories via direct DB query -->";
            }
        } else {
            if (current_user_can('manage_options')) {
                echo "<!-- No categories found via direct DB query -->";
            }
        }
    }
    
    $multisite->restore_current_blog();
}

// Get current filter values
$selected_category = isset($_GET['tribe_eventcategory']) ? sanitize_text_field($_GET['tribe_eventcategory']) : '';
$selected_start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
$selected_end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';

?>

<div class="events-tracker-upcoming-events">
    <!-- Filter Form -->
    <div class="events-tracker-filter-form">
        <h3><?php _e('Filter Events', 'events-tracker'); ?></h3>
        <form method="get">
            <?php wp_nonce_field('events_tracker_filter', 'events_tracker_filter_nonce'); ?>
            <div class="filter-row">
                <div class="filter-group">
                    <label for="tribe_eventcategory"><?php _e('Category', 'events-tracker'); ?></label>
                    <select name="tribe_eventcategory" id="tribe_eventcategory">
                        <option value=""><?php _e('All Categories', 'events-tracker'); ?></option>
                        <?php foreach ($event_categories as $category) : ?>
                            <option value="<?php echo esc_attr($category->slug); ?>" <?php selected($selected_category, $category->slug); ?>>
                                <?php echo esc_html($category->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="start_date"><?php _e('Start Date', 'events-tracker'); ?></label>
                    <input type="date" name="start_date" id="start_date" value="<?php echo esc_attr($selected_start_date); ?>">
                </div>
                <div class="filter-group">
                    <label for="end_date"><?php _e('End Date', 'events-tracker'); ?></label>
                    <input type="date" name="end_date" id="end_date" value="<?php echo esc_attr($selected_end_date); ?>">
                </div>
            </div>
            <div class="filter-buttons">
                <a href="<?php echo esc_url(remove_query_arg(array('tribe_eventcategory', 'start_date', 'end_date'))); ?>" class="reset-filter button">
                    <?php _e('Reset', 'events-tracker'); ?>
                </a>
                <button type="submit" class="apply-filter button"><?php _e('Apply Filters', 'events-tracker'); ?></button>
            </div>
        </form>
    </div>

    <!-- Card Grid Layout -->
    <div class="events-tracker-card-grid">
        <?php 
        foreach ($events as $event) : 
            // Get event details (already fetched in the shortcode)
            $event_details = $event->details;
            
            // Get event categories
            $categories = $event->categories;
            
            // Check if this event is already saved by the user
            $is_saved = in_array($event->ID, $saved_event_ids);
            
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
            </div>
        <?php endforeach; ?>
    </div>
    
    <?php if ($max_pages > 1) : ?>
        <div class="events-tracker-pagination">
            <?php
            $current_url = get_permalink();
            
            echo paginate_links(array(
                'base' => $current_url . '%_%',
                'format' => '?paged=%#%',
                'current' => $paged,
                'total' => $max_pages,
                'prev_text' => __('&laquo; Previous', 'events-tracker'),
                'next_text' => __('Next &raquo;', 'events-tracker'),
            ));
            ?>
        </div>
    <?php endif; ?>
    
    <div class="events-tracker-powered-by">
        <p><?php _e('Powered by', 'events-tracker'); ?> 
           <a href="https://theeventscalendar.com/" target="_blank">The Events Calendar</a></p>
    </div>
    
    
</div>