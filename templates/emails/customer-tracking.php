<?php
/**
 * Customer tracking email template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-tracking.php.
 *
 * @var WC_Order $order
 * @var string $email_heading
 * @var array $tracking_info
 * @var string $additional_content
 * @var bool $sent_to_admin
 * @var bool $plain_text
 * @var WC_Email $email
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action('woocommerce_email_header', $email_heading, $email);
?>

<?php /* translators: %s: Customer first name */ ?>
<p><?php printf(esc_html__('Hi %s,', 'carramba-woo-order-tracking'), esc_html($order->get_billing_first_name())); ?></p>

<p><?php esc_html_e('Your order has been shipped! Here is the tracking information:', 'carramba-woo-order-tracking'); ?></p>

<?php if ($tracking_info): ?>
<div style="margin-bottom: 40px;">
    <table cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; color: #636363; border: 1px solid #e5e5e5;" border="1">
        <tbody>
            <tr>
                <th style="color: #636363; border: 1px solid #e5e5e5; vertical-align: middle; padding: 12px; text-align: left; background-color: #f8f8f8; width: 30%;" scope="row">
                    <?php esc_html_e('Shipping Company:', 'carramba-woo-order-tracking'); ?>
                </th>
                <td style="color: #636363; border: 1px solid #e5e5e5; vertical-align: middle; padding: 12px; text-align: left;">
                    <?php echo esc_html($tracking_info['shipper_name']); ?>
                </td>
            </tr>
            <?php foreach ($tracking_info['tracking_items'] as $item): ?>
            <tr>
                <th style="color: #636363; border: 1px solid #e5e5e5; vertical-align: middle; padding: 12px; text-align: left; background-color: #f8f8f8;" scope="row">
                    <?php esc_html_e('Tracking Number:', 'carramba-woo-order-tracking'); ?>
                </th>
                <td style="color: #636363; border: 1px solid #e5e5e5; vertical-align: middle; padding: 12px; text-align: left;">
                    <a href="<?php echo esc_url($item['tracking_url']); ?>" style="color: #7f54b3; font-weight: normal; text-decoration: underline;" target="_blank">
                        <?php echo esc_html($item['tracking_number']); ?>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p style="margin: 16px 0 0;">
        <?php foreach ($tracking_info['tracking_items'] as $item): ?>
        <a href="<?php echo esc_url($item['tracking_url']); ?>" style="background-color: #7f54b3; border-radius: 3px; color: #ffffff; display: inline-block; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; font-size: 14px; font-weight: bold; line-height: 1; margin: 0 5px 10px 0; padding: 12px 24px; text-decoration: none; text-transform: uppercase; vertical-align: middle;" target="_blank">
            <?php
            /* translators: %s: tracking number */
            echo sprintf(esc_html__('Track %s', 'carramba-woo-order-tracking'), esc_html($item['tracking_number']));
            ?>
        </a>
        <?php endforeach; ?>
    </p>
</div>
<?php endif; ?>

<h2 style="color: #7f54b3; display: block; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; font-size: 18px; font-weight: bold; line-height: 130%; margin: 0 0 18px; text-align: left;">
    <?php
    /* translators: %s: Order number */
    printf(esc_html__('[Order #%s]', 'carramba-woo-order-tracking'), $order->get_order_number());
    ?>
</h2>

<?php
/*
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 */
do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);

/*
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email);

/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ($additional_content) {
    echo wp_kses_post(wpautop(wptexturize($additional_content)));
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action('woocommerce_email_footer', $email);
