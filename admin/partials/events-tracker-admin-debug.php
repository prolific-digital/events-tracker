<?php
/**
 * Admin debugging tools page
 *
 * @since      1.0.0
 * @package    Events_Tracker
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get the source blog ID
$multisite = new Events_Tracker_Multisite();
$source_blog_id = $multisite->get_source_blog_id();

// Get sample event IDs if available
$sample_events = array();
if (is_multisite()) {
    switch_to_blog($source_blog_id);
    
    // Try to get a few sample events
    $args = array(
        'post_type' => 'tribe_events',
        'posts_per_page' => 5,
        'orderby' => 'rand',
    );
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $sample_events[] = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
            );
        }
    }
    
    wp_reset_postdata();
    restore_current_blog();
}

// Get rewrite rules
global $wp_rewrite;
$rules = $wp_rewrite->wp_rewrite_rules();
$tracker = new Events_Tracker();
$plugin_rules = array_filter($rules, function($key) use ($tracker) {
    return strpos($key, $tracker->event_slug) === 0;
}, ARRAY_FILTER_USE_KEY);
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="notice notice-warning">
        <p><?php _e('These debug tools are for site administrators only. Use them to troubleshoot issues with the Events Tracker plugin.', 'events-tracker'); ?></p>
    </div>
    
    <?php echo $debug_result; ?>
    
    <div class="metabox-holder">
        <div class="postbox">
            <h2 class="hndle"><?php _e('Test Event Retrieval', 'events-tracker'); ?></h2>
            <div class="inside">
                <form method="post" action="">
                    <input type="hidden" name="action" value="test_event">
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="blog_id"><?php _e('Source Blog ID', 'events-tracker'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="blog_id" id="blog_id" value="<?php echo esc_attr($source_blog_id); ?>" class="small-text">
                                <p class="description"><?php _e('The blog ID where events are stored.', 'events-tracker'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="event_id"><?php _e('Event ID', 'events-tracker'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="event_id" id="event_id" value="<?php echo esc_attr($event_id); ?>" class="regular-text" required>
                                <p class="description"><?php _e('Enter the post ID of the event to test.', 'events-tracker'); ?></p>
                                
                                <?php if (!empty($sample_events)) : ?>
                                <div class="sample-events">
                                    <p><strong><?php _e('Sample Events:', 'events-tracker'); ?></strong></p>
                                    <ul>
                                        <?php foreach ($sample_events as $sample) : ?>
                                        <li>
                                            <a href="#" class="sample-event-link" data-id="<?php echo esc_attr($sample['id']); ?>">
                                                <?php echo esc_html($sample['title']); ?> (ID: <?php echo esc_html($sample['id']); ?>)
                                            </a>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Test Event', 'events-tracker'); ?>">
                    </p>
                </form>
            </div>
        </div>
        
        <div class="postbox">
            <h2 class="hndle"><?php _e('Rewrite Rules', 'events-tracker'); ?></h2>
            <div class="inside">
                <h3><?php _e('Current Plugin Rewrite Rules', 'events-tracker'); ?></h3>
                
                <?php if (!empty($plugin_rules)) : ?>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php _e('Pattern', 'events-tracker'); ?></th>
                            <th><?php _e('Rewrite', 'events-tracker'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($plugin_rules as $pattern => $rewrite) : ?>
                        <tr>
                            <td><?php echo esc_html($pattern); ?></td>
                            <td><?php echo esc_html($rewrite); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else : ?>
                <p><?php _e('No plugin rewrite rules found. Try flushing the rewrite rules.', 'events-tracker'); ?></p>
                <?php endif; ?>
                
                <form method="post" action="">
                    <input type="hidden" name="action" value="flush_rules">
                    <p class="submit">
                        <input type="submit" name="submit" id="flush-rules" class="button button-secondary" value="<?php _e('Flush Rewrite Rules', 'events-tracker'); ?>">
                    </p>
                </form>
                
                <h3><?php _e('Registered Query Vars', 'events-tracker'); ?></h3>
                <p><?php echo esc_html(implode(', ', $wp_rewrite->public_query_vars)); ?></p>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $('.sample-event-link').on('click', function(e) {
        e.preventDefault();
        var eventId = $(this).data('id');
        $('#event_id').val(eventId);
    });
});
</script>