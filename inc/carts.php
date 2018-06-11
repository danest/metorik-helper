<?php

/**
 * This class loads Metorik's carts endpoints/code.
 */
class Metorik_Helper_Carts
{
    //protected $apiUrl = 'https://app.metorik.com/api/store';
    protected $apiUrl = 'http://metorik-app.test/api/store';

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Cart sending (ajax/actions)
        add_action('wp_ajax_nopriv_metorik_send_cart', array($this, 'ajax_send_cart'));
        add_action('wp_ajax_metorik_send_cart', array($this, 'ajax_send_cart'));
        add_action('woocommerce_cart_item_removed', array($this, 'check_cart_empty_and_send'));

        // Checkout
        add_action('woocommerce_checkout_order_processed', array($this, 'checkout_order_processed'));

        // Unset cart
        add_action('woocommerce_payment_complete', array($this, 'unset_cart_token'));
        add_action('woocommerce_thankyou', array($this, 'unset_cart_token'));

        // Cart recovery
        add_action('rest_api_init', array($this, 'api_recover_cart_route'));
        add_action('woocommerce_cart_loaded_from_session', array($this, 'maybe_apply_cart_recovery_coupon'), 11);
    }

    public function generate_cart_token()
    {
        $token = md5(time());

        return $token;
    }

    public function get_or_set_cart_token()
    {
        if (!$token = $this->get_cart_token()) {
            $token = $this->set_cart_token();
        }

        return $token;
    }

    public function get_cart_token($user_id = false)
    {
        if ($user_id || ($user_id = get_current_user_id())) {
            $token = get_user_meta($user_id, '_metorik_cart_token', true);

            // if no user meta token, check session cart token first and use that
            if (!$token && WC()->session && WC()->session->get('metorik_cart_token')) {
                update_user_meta($user_id, '_metorik_cart_token', $token);
            }

            return $token;
        } else {
            return (WC()->session) ? WC()->session->get('metorik_cart_token') : '';
        }
    }

    public function set_cart_token()
    {
        $token = $this->generate_cart_token();

        WC()->session->set('metorik_cart_token', $token);

        if ($user_id = get_current_user_id()) {
            update_user_meta($user_id, '_metorik_cart_token', $token);
        }

        return $token;
    }

    /**
     * Unset a cart token/recovery status.
     * Done when checking out after payment.
     */
    public function unset_cart_token()
    {
        if (WC()->session) {
            unset(WC()->session->metorik_cart_token, WC()->session->metorik_pending_recovery);
        }

        if ($user_id = get_current_user_id()) {
            delete_user_meta($user_id, '_metorik_cart_token');
            delete_user_meta($user_id, '_metorik_pending_recovery');
        }
    }

    /**
     * Was the current cart/checkout created by a Metorik recovery URL?
     *
     * @return bool
     */
    public static function cart_is_pending_recovery($user_id = null)
    {
        if ($user_id || ($user_id = get_current_user_id())) {
            return (bool) get_user_meta($user_id, '_metorik_pending_recovery', true);
        } elseif (isset(WC()->session)) {
            return (bool) WC()->session->metorik_pending_recovery;
        }

        return false;
    }

    /**
     * Send cart ajax. Only if have cart!
     * Only if metorik auth token set up.
     *
     * @return void
     */
    public function ajax_send_cart()
    {
        check_ajax_referer('metorik-js', 'security');

        // metorik auth token? if none, stop
        $metorik_auth_token = get_option('metorik_auth_token');
        if (!$metorik_auth_token) {
            return;
        }

        // variables
        $cart = WC()->cart->get_cart();
        $token = $this->get_or_set_cart_token();
        $customer_id = get_current_user_id();
        $email = isset($_POST['email']) && $_POST['email'] ? sanitize_email($_POST['email']) : null;

        $data = array(
            'api_token' => $metorik_auth_token,
            'data'      => array(
                'token'             => $token,
                'cart'              => $cart,
                'started_at'        => current_time('timestamp', true), // utc timestamp
                'total'             => (float) $this->get_cart_total(),
                'subtotal'          => (float) $this->get_cart_subtotal(),
                'total_tax'         => (float) (WC()->cart->tax_total + WC()->cart->shipping_tax_total),
                'total_discount'    => (float) WC()->cart->discount_cart,
                'total_shipping'    => (float) WC()->cart->shipping_total,
                'currency'          => get_woocommerce_currency(),
                'customer_id'       => $customer_id,
                'email'             => $email,
            ),
        );

        $response = wp_remote_post($this->apiUrl.'/incoming/carts', array(
            'body' => $data,
        ));

        wp_die();
    }

    /**
     * Hooks into a cart item being removed action.
     * Checks if the cart is empty. If so, sends the
     * empty cart to Metorik (and clears token).
     *
     * @return void
     */
    public function check_cart_empty_and_send()
    {
        // only continue if the cart is empty
        if (WC()->cart->is_empty()) {
            // metorik auth token? if none, stop
            $metorik_auth_token = get_option('metorik_auth_token');
            if (!$metorik_auth_token) {
                return;
            }

            // clear cart remotely by sending empty cart
            $token = $this->get_or_set_cart_token();

            $response = wp_remote_post($this->apiUrl.'/incoming/carts', array(
                'body' => array(
                    'api_token' => $metorik_auth_token,
                    'data'      => array(
                        'token'             => $token,
                        'cart'              => false,
                    ),
                ),
            ));

            // clear the cart token/data from the session/user
            $this->unset_cart_token();
        }
    }

    /**
     * Cart total.
     * Since WC won't calculate total unless on cart/checkout,
     * we need an alternative method to do it manually.
     */
    protected function get_cart_total()
    {
        if (
            is_checkout() ||
            is_cart() ||
            defined('WOOCOMMERCE_CHECKOUT') ||
            defined('WOOCOMMERCE_CART')
        ) {
            return WC()->cart->total;
        } else {
            // product page, etc. - total not calculated but tax/shipping maybe
            return WC()->cart->subtotal_ex_tax +
                WC()->cart->tax_total +
                WC()->cart->shipping_tax_total +
                WC()->cart->shipping_total;
        }
    }

    /**
     * Get the cart subtotal (maybe inclusive of taxes).
     */
    public function get_cart_subtotal()
    {
        if ('excl' === get_option('woocommerce_tax_display_cart')) {
            $subtotal = WC()->cart->subtotal_ex_tax;
        } else {
            $subtotal = WC()->cart->subtotal;
        }

        return $subtotal;
    }

    /**
     * This is called once the checkout has been processed and an order has been created.
     */
    public function checkout_order_processed($order_id)
    {
        // no metorik auth token? Stop
        $metorik_auth_token = get_option('metorik_auth_token');
        if (!$metorik_auth_token) {
            return;
        }

        $cart_token = $this->get_cart_token();

        // generate a token if needed? not sure if needed/possible to send cart now
        if (!$cart_token) {
            //
        }

        // save cart token to order meta
        if ($cart_token) {
            update_post_meta($order_id, '_metorik_cart_token', $cart_token);
        }

        // check if pending recovery - if so, set in order meta
        if ($this->cart_is_pending_recovery()) {
            $this->mark_order_as_recovered($order_id);
        }
    }

    /**
     * Mark an order as recovered by Metorik.
     */
    public function mark_order_as_recovered($order_id)
    {
        $order = wc_get_order($order_id);

        if (!$order instanceof WC_Order) {
            return;
        }

        update_post_meta($order_id, '_metorik_cart_recovered', true);

        $order->add_order_note(__('Order cart recovered by Metorik.', 'metorik'));
    }

    /**
     * Maybe apply the recovery coupon provided in the recovery URL.
     *
     * @since 1.1.0
     */
    public function maybe_apply_cart_recovery_coupon()
    {
        if ($this->cart_is_pending_recovery() && !empty($_REQUEST['coupon'])) {
            $coupon_code = wc_clean(rawurldecode($_REQUEST['coupon']));

            if (!WC()->cart->has_discount($coupon_code)) {
                WC()->cart->add_discount($coupon_code);
            }
        }
    }

    /**
     * Rest API route for recovering a cart.
     *
     * @return void
     */
    public function api_recover_cart_route()
    {
        register_rest_route('metorik/v1', '/recover-cart', array(
            'methods'  => 'GET',
            'callback' => array($this, 'recover_cart_callback'),
        ));
    }

    /**
     * API route callback for recovering a cart.
     */
    public function recover_cart_callback($request)
    {
        // Check token is set and has a value before continuing.
        if (isset($request['token']) && $cart_token = $request['token']) {
            // base checkout url
            $checkout_url = wc_get_checkout_url();

            // forward along any UTM or metorik params
            foreach ($request as $key => $val) {
                if (0 === strpos($key, 'utm_') || 0 === strpos($key, 'mtk')) {
                    $checkout_url = add_query_arg($key, $val, $checkout_url);
                }
            }

            // try restore the cart
            try {
                $this->restore_cart($cart_token);

                // check for coupon in recovery URL to apply before checkout redirect
                if (isset($request['coupon']) && $coupon = rawurldecode($request['coupon'])) {
                    $checkout_url = add_query_arg(array('coupon' => wc_clean($coupon)), $checkout_url);
                }
            } catch (Exception $e) {
                // no session? start so notices will be shown
                if (!WC()->session->has_session()) {
                    WC()->session->set_customer_session_cookie(true);
                }

                // add a notice
                wc_add_notice(__('Sorry, we were not able to restore your cart. Please try adding your items to your cart again.', 'metorik'), 'error');
            }

            // redirect checkout url
            wp_safe_redirect($checkout_url);
            exit;
        }
    }

    /**
     * Restore an actual cart.
     */
    public function restore_cart($cart_token)
    {
        // metorik auth token
        $metorik_auth_token = get_option('metorik_auth_token');
        if (!$metorik_auth_token) {
            throw new Exception('Missing Metorik authentication token');
        }

        // get cart
        $response = wp_remote_get($this->apiUrl.'/external/carts', array(
            'body' => array(
                'api_token'  => $metorik_auth_token,
                'cart_token' => $cart_token,
            ),
        ));

        // Error during response?
        if (is_wp_error($response)) {
            throw new Exception('Error getting cart from Metorik');
        }

        $body = wp_remote_retrieve_body($response);

        // no response body/cart?
        if (!$body) {
            throw new Exception('Error getting cart from Metorik');
        }

        // json decode
        $body = json_decode($body);

        // get cart
        $cart = $body->data->cart;

        // need to cast all to an array for putting back into the session
        $cart = json_decode(json_encode($cart), true);

        // Clear any existing cart
        WC()->cart->empty_cart();

        // Restore cart
        WC()->session->set('cart', $cart);

        // Set the cart token and pending recovery in session
        WC()->session->set('metorik_cart_token', $cart_token);
        WC()->session->set('metorik_pending_recovery', true);

        // Set the cart token / pending recovery in user meta if this cart has a user
        $user_id = $body->data->customer_id;
        if ($user_id) {
            update_user_meta($user_id, '_metorik_cart_token', $cart_token);
            update_user_meta($user_id, '_metorik_pending_recovery', true);
        }
    }
}

new Metorik_Helper_Carts();
