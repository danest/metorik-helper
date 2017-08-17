<?php

/**
 * Custom changes that Metorik implements, like tracking referer.
 */
class Metorik_Custom {
	public $possibleUtms = array( 'utm_source', 'utm_medium', 'utm_campaign' );

	public function __construct() {
		add_action( 'init', array( $this, 'set_referer' ) );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'set_order_referer' ) );
		add_action( 'woocommerce_checkout_update_user_meta', array( $this, 'set_customer_referer' ) );
		add_action( 'user_register', array( $this, 'set_customer_referer' ) );
	}

	/**
	 * Set the referer. We use a cookie, as sessions in WP
	 * are a bit unreliable and cookie here is easier.
	 */
	public function set_referer() {
		// check if none in cookie already and http referer is set
		if ( ! isset( $_COOKIE['metorik_http_referer'] ) && isset( $_SERVER['HTTP_REFERER'] ) ) {
			// get referer
			$referer = sanitize_text_field( $_SERVER['HTTP_REFERER'] );

			// if referer same this site, don't set it
			if ( strpos( $referer, home_url() ) !== false ) {
				return;
			}

			// store in cookie (sessions not reliable enough)
			$time = apply_filters( 'metorik_referer_cookie_time', 3600 * 24 * 180 ); // 180 days
			setcookie( 'metorik_http_referer', $referer, time() + $time, '/' );
		}

		// set UTM tags if there are any
		if ( isset( $_GET['utm_source'] ) || isset( $_GET['utm_medium']  ) || isset( $_GET['utm_campaign'] ) ) {
			$time = apply_filters( 'metorik_utm_cookie_time', 3600 * 24 * 180 ); // 180 days
			
			foreach ( $this->possibleUtms as $possible ) {
				if ( isset( $_GET[$possible] ) && $_GET[$possible] ) {
					$value = sanitize_text_field( $_GET[$possible] );
					setcookie( 'metorik_' . $possible, $value, time() + $time, '/' );
				}
			}
		}

		// set Engage data if have any
		if ( isset( $_GET['mtke'] ) ) {
			$time = apply_filters( 'metorik_engage', 3600 * 24 * 180 ); // 180 days
			$value = sanitize_text_field( $_GET['mtke'] );
			setcookie( 'metorik_engage', $value, time() + $time, '/' );
		}
	}

	/**
	 * Set the referer on the order in the post meta.
	 */
	public function set_order_referer( $order_id ) {
		// if we have a referer, get it and set in order meta
		if ( isset( $_COOKIE['metorik_http_referer'] ) ) {
			$referer = apply_filters( 'metorik_order_referer', sanitize_text_field( $_COOKIE['metorik_http_referer'] ) );
			update_post_meta( $order_id, '_metorik_referer', $referer );
		}

		// If we have any UTM tags, set them
		foreach ( $this->possibleUtms as $utm ) {
			if ( isset( $_COOKIE['metorik_' . $utm] ) ) {
				$value = sanitize_text_field( $_COOKIE['metorik_' . $utm] );
				update_post_meta( $order_id, '_metorik_' . $utm, $value );
			}
		}

		// if we have a metorik engage key, get it and set in order meta
		if ( isset( $_COOKIE['metorik_engage'] ) ) {
			$engage = apply_filters( 'metorik_order_engage', sanitize_text_field( $_COOKIE['metorik_engage'] ) );
			update_post_meta( $order_id, '_metorik_engage', $engage );
		}
	}

	/**
	 * Set the referer on the customer in the user meta.
	 */
	public function set_customer_referer( $user_id ) {
		// no user id? return
		if ( ! $user_id ) {
			return;
		}

		// if we have a referer, get it and set in order meta
		if ( isset( $_COOKIE['metorik_http_referer'] ) ) {
			$referer = apply_filters( 'metorik_customer_referer', sanitize_text_field( $_COOKIE['metorik_http_referer'] ) );
			update_user_meta( $user_id, '_metorik_referer', $referer );
		}

		// If we have any UTM tags, set them
		foreach ( $this->possibleUtms as $utm ) {
			if ( isset( $_COOKIE['metorik_' . $utm] ) ) {
				$value = sanitize_text_field( $_COOKIE['metorik_' . $utm] );
				update_user_meta( $user_id, '_metorik_' . $utm, $value );
			}
		}

		// if we have a metorik engage key, get it and set in customer meta
		if ( isset( $_COOKIE['metorik_engage'] ) ) {
			$engage = apply_filters( 'metorik_order_engage', sanitize_text_field( $_COOKIE['metorik_engage'] ) );
			update_user_meta( $user_id, '_metorik_engage', $engage );
		}
	}
}

new Metorik_Custom();