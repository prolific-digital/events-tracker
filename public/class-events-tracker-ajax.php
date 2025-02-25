<?php
/**
 * The AJAX functionality of the plugin
 *
 * @since      1.0.0
 * @package    Events_Tracker
 */

class Events_Tracker_Ajax {

    /**
     * AJAX handler for saving an event
     *
     * @since    1.0.0
     */
    public function save_event() {
        // Check for user login
        if (!is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => __('You must be logged in to save events.', 'events-tracker')
            ));
        }

        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'events_tracker_nonce')) {
            wp_send_json_error(array(
                'message' => __('Security check failed.', 'events-tracker')
            ));
        }

        // Get the event ID
        $event_id = isset($_POST['event_id']) ? absint($_POST['event_id']) : 0;

        // Check if event ID is valid
        if ($event_id <= 0) {
            wp_send_json_error(array(
                'message' => __('Invalid event ID.', 'events-tracker')
            ));
        }

        // Save the event for this user
        $success = Events_Tracker_Event::save_event($event_id);

        if ($success) {
            wp_send_json_success(array(
                'message' => __('Event saved successfully.', 'events-tracker')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('This event is already in your saved list.', 'events-tracker')
            ));
        }
    }

    /**
     * AJAX handler for removing an event
     *
     * @since    1.0.0
     */
    public function remove_event() {
        // Check for user login
        if (!is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => __('You must be logged in to remove events.', 'events-tracker')
            ));
        }

        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'events_tracker_nonce')) {
            wp_send_json_error(array(
                'message' => __('Security check failed.', 'events-tracker')
            ));
        }

        // Get the event ID
        $event_id = isset($_POST['event_id']) ? absint($_POST['event_id']) : 0;

        // Check if event ID is valid
        if ($event_id <= 0) {
            wp_send_json_error(array(
                'message' => __('Invalid event ID.', 'events-tracker')
            ));
        }

        // Remove the event for this user
        $success = Events_Tracker_Event::remove_event($event_id);

        if ($success) {
            wp_send_json_success(array(
                'message' => __('Event removed successfully.', 'events-tracker')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('This event is not in your saved list.', 'events-tracker')
            ));
        }
    }
}