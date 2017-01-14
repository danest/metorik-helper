<?php

/**
 * Custom changes that Metorik implements, like tracking referer.
 */
class Metorik_Custom {
	public function __construct() {
		add_action( 'init', array( $this, 'set_referer' ) );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'set_order_referer' ) );
		add_action( 'user_register', array( $this, 'set_customer_referer' ) );
	}

	/**
	 * Set the referer. We use a cookie, as sessions in WP
	 * are a bit unreliable and cookie here is easier.
	 */
	public function set_referer() {
		if (! is_admin() ) {
			// check if none in cookie already
			if (! isset( $_COOKIE['metorik_http_referer'] ) ) {
				// get referer
				$referer = sanitize_text_field( $_SERVER['HTTP_REFERER'] );

				// if referer same this site, don't set it
				if ( strpos( $referer, home_url() ) !== false ) {
					return;
				}

				// store in cookie (sessions not reliable enough)
				$time = apply_filters( 'metorik_referer_cookie_time', 3600 );
				setcookie( 'metorik_http_referer', $referer, time() + $time );
			}
		}
	}

	/**
	 * Set the referer on the order in the post meta.
	 */
	public function set_order_referer( $order_id ) {
		// if we have a referer, get it and set in order meta
		if ( isset( $_COOKIE['metorik_http_referer'] ) ) {
			$referer = sanitize_text_field( $_COOKIE['metorik_http_referer'] );
			update_post_meta( $order_id, '_metorik_referer', $referer );
		}
	}

	/**
	 * Set the referer on the customer in the user meta.
	 */
	public function set_customer_referer( $user_id ) {
		// if we have a referer, get it and set in order meta
		if ( isset( $_COOKIE['metorik_http_referer'] ) ) {
			$referer = sanitize_text_field( $_COOKIE['metorik_http_referer'] );
			update_user_meta( $user_id, '_metorik_referer', $referer );
		}
	}
}

new Metorik_Custom();