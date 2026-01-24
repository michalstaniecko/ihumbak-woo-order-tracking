/* Order edit page JavaScript for CWOT plugin */

jQuery(document).ready(function($) {
    var i18n = (typeof cwot_ajax !== 'undefined' && cwot_ajax.i18n) ? cwot_ajax.i18n : {};

    var trackingNumberTemplate = '<div class="cwot-tracking-number-row">' +
        '<input type="text" name="_cwot_tracking_numbers[]" value="" placeholder="' +
        (i18n.enterTrackingNumber || 'Enter tracking number') +
        '" class="cwot-tracking-number-input" />' +
        '<button type="button" class="button cwot-remove-tracking-number" title="' +
        (i18n.remove || 'Remove') +
        '">×</button>' +
        '</div>';

    // Add new tracking number field
    $(document).on('click', '.cwot-add-tracking-number', function(e) {
        e.preventDefault();
        $('.cwot-tracking-numbers-list').append(trackingNumberTemplate);
    });

    // Remove tracking number field
    $(document).on('click', '.cwot-remove-tracking-number', function(e) {
        e.preventDefault();
        $(this).closest('.cwot-tracking-number-row').remove();
    });

    // Validate and toggle Save Tracking button state
    function validateSaveButton() {
        var $button = $('#cwot-save-tracking');
        var $checkbox = $('#cwot_send_email');
        var shipperId = $('#_cwot_tracking_shipper_id').val();
        var hasTrackingNumber = false;

        $('.cwot-tracking-number-input').each(function() {
            if ($(this).val().trim() !== '') {
                hasTrackingNumber = true;
                return false; // break
            }
        });

        var isValid = shipperId && shipperId !== '' && shipperId !== '0' && hasTrackingNumber;

        // Disable/enable button based on validation
        $button.prop('disabled', !isValid);

        // Also disable checkbox if not valid
        $checkbox.prop('disabled', !isValid);
        if (!isValid) {
            $checkbox.prop('checked', false);
        }
    }

    // Trigger validation on shipper or tracking number change
    $(document).on('change keyup', '#_cwot_tracking_shipper_id, .cwot-tracking-number-input', function() {
        validateSaveButton();
    });

    // Re-validate after adding new tracking number field
    $(document).on('click', '.cwot-add-tracking-number', function() {
        setTimeout(validateSaveButton, 10);
    });

    // Re-validate after removing tracking number field
    $(document).on('click', '.cwot-remove-tracking-number', function() {
        setTimeout(validateSaveButton, 10);
    });

    // Initialize on page load
    validateSaveButton();

    // AJAX Save Tracking handler
    $(document).on('click', '#cwot-save-tracking', function(e) {
        e.preventDefault();

        if (typeof cwot_ajax === 'undefined') {
            console.error('CWOT AJAX not initialized');
            return;
        }

        var $button = $(this);
        var $status = $('#cwot-save-status');
        var orderId = $('#cwot-order-id').val();
        var shipperId = $('#_cwot_tracking_shipper_id').val();
        var sendEmail = $('#cwot_send_email').is(':checked');

        // Collect tracking numbers
        var trackingNumbers = [];
        $('.cwot-tracking-number-input').each(function() {
            var value = $(this).val().trim();
            if (value !== '') {
                trackingNumbers.push(value);
            }
        });

        // Disable button and show saving status
        $button.prop('disabled', true);
        $status.removeClass('success error').text(i18n.saving || 'Saving...');

        $.ajax({
            url: cwot_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'cwot_save_tracking',
                nonce: cwot_ajax.nonce,
                order_id: orderId,
                shipper_id: shipperId,
                tracking_numbers: trackingNumbers,
                send_email: sendEmail ? 'true' : 'false'
            },
            success: function(response) {
                validateSaveButton();

                if (response.success) {
                    $status.addClass('success').text(response.data.message || (i18n.saved || 'Saved!'));

                    // Uncheck the send email checkbox after successful send
                    if (sendEmail && response.data.email_sent) {
                        $('#cwot_send_email').prop('checked', false);

                        // Update or create the email sent info display
                        if (response.data.email_sent_at) {
                            var $emailInfo = $('.cwot-email-sent-info');
                            var emailSentHtml = '<p class="cwot-email-sent-info" style="background: #d4edda; padding: 8px 10px; border-radius: 4px; margin-bottom: 10px;">' +
                                '<span class="dashicons dashicons-email-alt" style="color: #155724; margin-right: 5px;"></span>' +
                                '<strong>' + (i18n.emailSent || 'Email sent:') + '</strong><br>' +
                                response.data.email_sent_at +
                                '</p>';

                            if ($emailInfo.length) {
                                // Update existing element
                                $emailInfo.replaceWith(emailSentHtml);
                            } else {
                                // Insert before the checkbox
                                $('#cwot_send_email').closest('p').before(emailSentHtml);
                            }
                        }
                    }

                    // Clear status message after 3 seconds
                    setTimeout(function() {
                        $status.removeClass('success').text('');
                    }, 3000);
                } else {
                    $status.addClass('error').text(response.data.message || (i18n.error || 'Error saving.'));
                }
            },
            error: function() {
                validateSaveButton();
                $status.addClass('error').text(i18n.error || 'Error saving.');
            }
        });
    });
});
