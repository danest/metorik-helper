<?php

/**
 * Coupon changes that Metorik implements, like auto-applying coupons from the URL.
 */
class Metorik_Coupons
{
    public function __construct()
    {
        add_action('wp_loaded', array($this, 'add_coupon_code_to_cart_session'));
        add_action('woocommerce_add_to_cart', array($this, 'add_coupon_code_to_cart'));
    }

    /**
     * Add a coupon code to the cart session.
     *
     * @return void
     */
    public function add_coupon_code_to_cart_session()
    {
        // Stop if no code in URL
        if (empty($_GET['mtkc'])) {
            return;
        }

        // no session? start so cart/notices work
        if (!WC()->session->has_session()) {
            WC()->session->set_customer_session_cookie(true);
        }

        // Set code in session
        $coupon_code = esc_attr($_GET['mtkc']);
        WC()->session->set('mtk_coupon', $coupon_code);

        // If there is an existing non empty cart active session we apply the coupon
        if (WC()->cart && !WC()->cart->is_empty() && !WC()->cart->has_discount($coupon_code)) {
            WC()->cart->calculate_totals();
            WC()->cart->add_discount($coupon_code);

            // Unset the coupon from the session
            WC()->session->__unset('mtk_coupon');
        }
    }

    /**
     * Add the Metorik session coupon code to the cart when adding a product.
     */
    public function add_coupon_code_to_cart()
    {
        $coupon_code = WC()->session ? WC()->session->get('mtk_coupon') : false;

        // no coupon code? stop
        if (!$coupon_code || empty($coupon_code)) {
            return;
        }

        // only if have a cart but not this discount yet
        if (WC()->cart && !WC()->cart->has_discount($coupon_code)) {
            WC()->cart->calculate_totals();
            WC()->cart->add_discount($coupon_code);

            // Unset the coupon from the session
            WC()->session->__unset('mtk_coupon');
        }
    }
}

new Metorik_Coupons();
