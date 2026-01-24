<?php
/**
 * Order Tracking Email class for CWOT plugin
 *
 * @extends WC_Email
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CWOT_Email_Tracking extends WC_Email {

    /**
     * Constructor
     */
    public function __construct() {
        $this->id             = 'cwot_tracking';
        $this->customer_email = true;
        $this->title          = __('Order Tracking', 'carramba-woo-order-tracking');
        $this->description    = __('This email is sent to customers when tracking information is added to their order.', 'carramba-woo-order-tracking');
        $this->heading        = __('Your Order Tracking Information', 'carramba-woo-order-tracking');
        $this->subject        = __('Tracking information for your order #{order_number}', 'carramba-woo-order-tracking');

        $this->template_base  = CWOT_PLUGIN_PATH . 'templates/';
        $this->template_html  = 'emails/customer-tracking.php';
        $this->template_plain = 'emails/plain/customer-tracking.php';

        $this->placeholders = array(
            '{order_date}'   => '',
            '{order_number}' => '',
            '{site_title}'   => $this->get_blogname(),
        );

        // Call parent constructor
        parent::__construct();
    }

    /**
     * Get email subject
     */
    public function get_default_subject() {
        return __('Tracking information for your order #{order_number}', 'carramba-woo-order-tracking');
    }

    /**
     * Get email heading
     */
    public function get_default_heading() {
        return __('Your Order Tracking Information', 'carramba-woo-order-tracking');
    }

    /**
     * Trigger the sending of this email
     *
     * @param int $order_id The order ID
     */
    public function trigger($order_id) {
        $this->setup_locale();

        if ($order_id) {
            $this->object = wc_get_order($order_id);

            if ($this->object) {
                $this->recipient = $this->object->get_billing_email();
                $this->placeholders['{order_date}']   = wc_format_datetime($this->object->get_date_created());
                $this->placeholders['{order_number}'] = $this->object->get_order_number();
            }
        }

        if ($this->is_enabled() && $this->get_recipient()) {
            $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
        }

        $this->restore_locale();
    }

    /**
     * Get content HTML
     */
    public function get_content_html() {
        $tracking_info = CWOT_Order_Tracking::get_order_tracking_info($this->object->get_id());

        return wc_get_template_html(
            $this->template_html,
            array(
                'order'              => $this->object,
                'email_heading'      => $this->get_heading(),
                'tracking_info'      => $tracking_info,
                'additional_content' => $this->get_additional_content(),
                'sent_to_admin'      => false,
                'plain_text'         => false,
                'email'              => $this,
            ),
            '',
            $this->template_base
        );
    }

    /**
     * Get content plain text
     */
    public function get_content_plain() {
        $tracking_info = CWOT_Order_Tracking::get_order_tracking_info($this->object->get_id());

        return wc_get_template_html(
            $this->template_plain,
            array(
                'order'              => $this->object,
                'email_heading'      => $this->get_heading(),
                'tracking_info'      => $tracking_info,
                'additional_content' => $this->get_additional_content(),
                'sent_to_admin'      => false,
                'plain_text'         => true,
                'email'              => $this,
            ),
            '',
            $this->template_base
        );
    }

    /**
     * Get default additional content
     */
    public function get_default_additional_content() {
        return __('Thanks for shopping with us.', 'carramba-woo-order-tracking');
    }

    /**
     * Initialize settings form fields
     */
    public function init_form_fields() {
        $placeholder_text  = sprintf(
            /* translators: %s: list of placeholders */
            __('Available placeholders: %s', 'carramba-woo-order-tracking'),
            '<code>{site_title}</code>, <code>{order_date}</code>, <code>{order_number}</code>'
        );

        $this->form_fields = array(
            'enabled'            => array(
                'title'   => __('Enable/Disable', 'carramba-woo-order-tracking'),
                'type'    => 'checkbox',
                'label'   => __('Enable this email notification', 'carramba-woo-order-tracking'),
                'default' => 'yes',
            ),
            'subject'            => array(
                'title'       => __('Subject', 'carramba-woo-order-tracking'),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading'            => array(
                'title'       => __('Email heading', 'carramba-woo-order-tracking'),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_heading(),
                'default'     => '',
            ),
            'additional_content' => array(
                'title'       => __('Additional content', 'carramba-woo-order-tracking'),
                'description' => __('Text to appear below the main email content.', 'carramba-woo-order-tracking') . ' ' . $placeholder_text,
                'css'         => 'width:400px; height: 75px;',
                'placeholder' => __('N/A', 'carramba-woo-order-tracking'),
                'type'        => 'textarea',
                'default'     => $this->get_default_additional_content(),
                'desc_tip'    => true,
            ),
            'email_type'         => array(
                'title'       => __('Email type', 'carramba-woo-order-tracking'),
                'type'        => 'select',
                'description' => __('Choose which format of email to send.', 'carramba-woo-order-tracking'),
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => $this->get_email_type_options(),
                'desc_tip'    => true,
            ),
        );
    }
}
