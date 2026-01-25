<?php
/**
 * AJAX handler for CWOT plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CWOT_Ajax {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_ajax_cwot_save_tracking', array($this, 'save_tracking'));
    }

    /**
     * AJAX handler for saving tracking data and optionally sending email
     */
    public function save_tracking() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cwot_ajax_action')) {
            wp_send_json_error(array(
                'message' => __('Security check failed.', 'carramba-woo-order-tracking')
            ));
        }

        // Check capability
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to perform this action.', 'carramba-woo-order-tracking')
            ));
        }

        // Get and validate order ID
        $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
        if (!$order_id) {
            wp_send_json_error(array(
                'message' => __('Invalid order ID.', 'carramba-woo-order-tracking')
            ));
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error(array(
                'message' => __('Order not found.', 'carramba-woo-order-tracking')
            ));
        }

        // Get tracking data
        $shipper_id = isset($_POST['shipper_id']) ? absint($_POST['shipper_id']) : 0;
        $tracking_numbers = isset($_POST['tracking_numbers']) && is_array($_POST['tracking_numbers'])
            ? array_map('sanitize_text_field', $_POST['tracking_numbers'])
            : array();
        $send_email = isset($_POST['send_email']) && $_POST['send_email'] === 'true';

        // Filter empty tracking numbers
        $tracking_numbers = array_filter($tracking_numbers, function($value) {
            return !empty(trim($value));
        });
        $tracking_numbers = array_values($tracking_numbers);

        // Save shipper ID and snapshot of shipper data
        if ($shipper_id > 0) {
            $shipper = CWOT_Database::get_shipper_by_id($shipper_id);
            if ($shipper) {
                $order->update_meta_data('_cwot_tracking_shipper_id', $shipper_id);
                $order->update_meta_data('_cwot_tracking_shipper_name', $shipper->name);
                $order->update_meta_data('_cwot_tracking_url', $shipper->tracking_url);
            }
        } else {
            $order->delete_meta_data('_cwot_tracking_shipper_id');
            $order->delete_meta_data('_cwot_tracking_shipper_name');
            $order->delete_meta_data('_cwot_tracking_url');
        }

        // Save tracking numbers
        if (!empty($tracking_numbers)) {
            $order->update_meta_data('_cwot_tracking_numbers', $tracking_numbers);
            // Also save the first tracking number in the old field for backward compatibility
            $order->update_meta_data('_cwot_tracking_number', $tracking_numbers[0]);
        } else {
            $order->delete_meta_data('_cwot_tracking_numbers');
            $order->delete_meta_data('_cwot_tracking_number');
        }

        $order->save();

        $response = array(
            'message' => __('Tracking information saved successfully.', 'carramba-woo-order-tracking'),
            'email_sent' => false,
        );

        // Send tracking email if requested and globally enabled
        $enable_tracking_email = get_option('cwot_enable_tracking_email', 1);
        if ($send_email && $enable_tracking_email && $shipper_id > 0 && !empty($tracking_numbers)) {
            $mailer = WC()->mailer();
            $emails = $mailer->get_emails();

            if (isset($emails['CWOT_Email_Tracking'])) {
                $emails['CWOT_Email_Tracking']->trigger($order_id);

                // Save the timestamp when email was sent (use time() for proper UTC timestamp)
                $email_sent_timestamp = time();
                $order->update_meta_data('_cwot_tracking_email_sent_at', $email_sent_timestamp);
                $order->save();

                // Format the datetime for display
                $date_format = get_option('date_format');
                $time_format = get_option('time_format');
                $formatted_datetime = date_i18n($date_format . ' ' . $time_format, $email_sent_timestamp);

                $response['email_sent'] = true;
                $response['email_sent_at'] = $formatted_datetime;
                $response['message'] = __('Tracking information saved and email sent successfully.', 'carramba-woo-order-tracking');
            } else {
                $response['message'] = __('Tracking information saved but email could not be sent.', 'carramba-woo-order-tracking');
            }
        }

        wp_send_json_success($response);
    }
}
