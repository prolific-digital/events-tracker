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

// CSS for card layout
?>
<style>
    .events-tracker-filter-form {
        margin-bottom: 30px;
        padding: 15px;
        background: #f9f9f9;
        border: 1px solid #e0e0e0;
        border-radius: 5px;
    }
    .events-tracker-filter-form .filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 15px;
    }
    .events-tracker-filter-form .filter-group {
        flex: 1;
        min-width: 200px;
    }
    .events-tracker-filter-form label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    .events-tracker-filter-form select,
    .events-tracker-filter-form input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .events-tracker-filter-form .filter-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    .events-tracker-filter-form button {
        padding: 8px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .events-tracker-filter-form .apply-filter {
        background: #0073aa;
        color: white;
    }
    .events-tracker-filter-form .reset-filter {
        background: #f0f0f0;
        color: #333;
    }
    
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
</style>

<div class="events-tracker-upcoming-events">
    <!-- Filter Form -->
    <div class="events-tracker-filter-form">
        <h3><?php _e('Filter Events', 'events-tracker'); ?></h3>
        <form method="get">
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
    
    <?php if (current_user_can('manage_options') && isset($event_data['debug_info'])): ?>
        <div class="events-tracker-debug" style="margin-top: 30px; padding: 15px; background: #f8f8f8; border: 1px solid #ddd; font-family: monospace;">
            <h3>Debug Information (Only visible to administrators)</h3>
            <p>Current Blog ID: <?php echo get_current_blog_id(); ?></p>
            <p>Source Blog ID: <?php echo $blog_id; ?></p>
            <p>Successfully Switched: <?php echo $event_data['debug_info']['switched'] ? 'Yes' : 'No'; ?></p>
            <p>tribe_events Post Type Found: <?php echo isset($event_data['debug_info']['tribe_events_exists']) && $event_data['debug_info']['tribe_events_exists'] ? 'Yes' : 'No'; ?></p>
            <p>Events Found: <?php echo $event_data['debug_info']['found_posts']; ?></p>
            
            <?php if (!empty($event_data['debug_info']['error'])): ?>
                <p style="color: red;">Error: <?php echo $event_data['debug_info']['error']; ?></p>
            <?php endif; ?>
            
            <div>
                <p><strong>Available Post Types:</strong></p>
                <pre><?php if(isset($event_data['debug_info']['post_types_found'])) print_r($event_data['debug_info']['post_types_found']); else echo "No post types data available"; ?></pre>
            </div>
            
            <div>
                <p><strong>Query:</strong></p>
                <pre><?php if (isset($event_data['debug_info']['sql'])) echo $event_data['debug_info']['sql']; ?></pre>
            </div>
        </div>
    <?php endif; ?>
</div>