<?php
/**
 * Email functionality for CWOT plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CWOT_Email {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Register custom tracking email class
        add_filter('woocommerce_email_classes', array($this, 'register_tracking_email'));

        // Add tracking info to order details on my account page
        add_action('woocommerce_order_details_after_order_table', array($this, 'add_tracking_info_to_order_details'));

        // Enqueue frontend styles for order tracking display
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_styles'));
    }

    /**
     * Register custom tracking email class
     *
     * @param array $email_classes Existing email classes
     * @return array Modified email classes
     */
    public function register_tracking_email($email_classes) {
        require_once CWOT_PLUGIN_PATH . 'includes/class-cwot-email-tracking.php';
        $email_classes['CWOT_Email_Tracking'] = new CWOT_Email_Tracking();
        return $email_classes;
    }
    
    /**
     * Enqueue frontend styles for order tracking display
     */
    public function enqueue_frontend_styles() {
        // Only enqueue on order view pages
        if (is_wc_endpoint_url('view-order') || is_wc_endpoint_url('order-received')) {
            wp_enqueue_style('cwot-order-style', CWOT_PLUGIN_URL . 'assets/css/order.css', array(), CWOT_VERSION);
        }
    }

    /**
     * Add tracking information to order details on my account page
     */
    public function add_tracking_info_to_order_details($order) {
        // Check if tracking info in order details is enabled
        $show_in_order_details = get_option('cwot_show_in_order_details', 1);
        if (!$show_in_order_details) {
            return;
        }
        
        $order_id = $order->get_id();
        $tracking_info = CWOT_Order_Tracking::get_order_tracking_info($order_id);
        
        // Only show if tracking information is available
        if (!$tracking_info) {
            return;
        }
        
        ?>
        <section class="woocommerce-order-tracking">
            <h2><?php esc_html_e('Order Tracking', 'carramba-woo-order-tracking'); ?></h2>
            <table class="woocommerce-table woocommerce-table--order-tracking shop_table order_tracking">
                <tbody>
                    <tr>
                        <th><?php esc_html_e('Shipping Company:', 'carramba-woo-order-tracking'); ?></th>
                        <td><?php echo esc_html($tracking_info['shipper_name']); ?></td>
                    </tr>
                    <?php foreach ($tracking_info['tracking_items'] as $item): ?>
                    <tr>
                        <th><?php esc_html_e('Tracking Number:', 'carramba-woo-order-tracking'); ?></th>
                        <td>
                            <a href="<?php echo esc_url($item['tracking_url']); ?>" target="_blank" class="woocommerce-order-tracking-link">
                                <?php echo esc_html($item['tracking_number']); ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <?php
    }
}