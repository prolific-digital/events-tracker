/**
 * Public JavaScript for Events Tracker
 */

(function( $ ) {
    'use strict';

    /**
     * Initialize the Events Tracker functionality
     */
    function init() {
        // Button to save an event
        $('.events-tracker-save-event').on('click', function(e) {
            e.preventDefault();
            var eventId = $(this).data('event-id');
            var $button = $(this);
            var $statusSpan = $button.siblings('.events-tracker-event-status');

            // Save event via AJAX
            saveEvent(eventId, $button, $statusSpan);
        });

        // Button to remove an event
        $('.events-tracker-remove-event').on('click', function(e) {
            e.preventDefault();
            var eventId = $(this).data('event-id');
            var $button = $(this);
            var $statusSpan = $button.siblings('.events-tracker-event-status');

            // Remove event via AJAX
            removeEvent(eventId, $button, $statusSpan);
        });
    }

    /**
     * Save an event via AJAX
     * 
     * @param {number} eventId The event ID
     * @param {object} $button The button jQuery object
     * @param {object} $statusSpan The status span jQuery object
     */
    function saveEvent(eventId, $button, $statusSpan) {
        // Show loading state
        $button.prop('disabled', true);
        $statusSpan.text('Saving...');

        $.ajax({
            url: events_tracker_data.ajax_url,
            type: 'POST',
            data: {
                action: 'events_tracker_save_event',
                nonce: events_tracker_data.nonce,
                event_id: eventId
            },
            success: function(response) {
                if (response.success) {
                    // Success - update the UI
                    $button.removeClass('events-tracker-save-event').addClass('events-tracker-remove-event');
                    $button.text('Remove');
                    $statusSpan.text(events_tracker_data.save_event_msg);
                    
                    // Update the button event handler
                    $button.off('click').on('click', function(e) {
                        e.preventDefault();
                        removeEvent(eventId, $button, $statusSpan);
                    });
                } else {
                    // Error
                    $statusSpan.text(response.data.message);
                }
            },
            error: function() {
                // AJAX error
                $statusSpan.text(events_tracker_data.error_msg);
            },
            complete: function() {
                // Re-enable the button
                $button.prop('disabled', false);
                
                // Clear the status message after a delay
                setTimeout(function() {
                    $statusSpan.text('');
                }, 3000);
            }
        });
    }

    /**
     * Remove an event via AJAX
     * 
     * @param {number} eventId The event ID
     * @param {object} $button The button jQuery object
     * @param {object} $statusSpan The status span jQuery object
     */
    function removeEvent(eventId, $button, $statusSpan) {
        // Show loading state
        $button.prop('disabled', true);
        $statusSpan.text('Removing...');

        $.ajax({
            url: events_tracker_data.ajax_url,
            type: 'POST',
            data: {
                action: 'events_tracker_remove_event',
                nonce: events_tracker_data.nonce,
                event_id: eventId
            },
            success: function(response) {
                if (response.success) {
                    // Check if we're on the saved events page
                    if ($button.closest('.events-tracker-saved-events').length) {
                        // If on the saved events page, remove the card with animation
                        var $card = $button.closest('.events-tracker-event-card');
                        $card.css('opacity', '0.5');
                        
                        setTimeout(function() {
                            $card.slideUp(300, function() {
                                $(this).remove();
                                
                                // If no events left, reload the page
                                if ($('.events-tracker-saved-events .events-tracker-event-card').length === 0) {
                                    location.reload();
                                }
                            });
                        }, 300);
                    } else {
                        // If on the upcoming events page, update the button
                        $button.removeClass('events-tracker-remove-event').addClass('events-tracker-save-event');
                        $button.text('Add to My List');
                        $statusSpan.text(events_tracker_data.remove_event_msg);
                        
                        // Update the button event handler
                        $button.off('click').on('click', function(e) {
                            e.preventDefault();
                            saveEvent(eventId, $button, $statusSpan);
                        });
                    }
                } else {
                    // Error
                    $statusSpan.text(response.data.message);
                }
            },
            error: function() {
                // AJAX error
                $statusSpan.text(events_tracker_data.error_msg);
            },
            complete: function() {
                // Re-enable the button (not needed if we removed the row)
                if (!$button.closest('.events-tracker-saved-events').length) {
                    $button.prop('disabled', false);
                    
                    // Clear the status message after a delay
                    setTimeout(function() {
                        $statusSpan.text('');
                    }, 3000);
                }
            }
        });
    }

    // Initialize on document ready
    $(document).ready(function() {
        init();
    });

})( jQuery );