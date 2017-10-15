<?php

/**
 * These are small changes that help Metorik complete it's import of the store.
 */
class Metorik_Import_Helpers {
	public function __construct() {
		add_filter( 'woocommerce_rest_prepare_customer', array( $this, 'filter_prepare_customer' ), 10, 3);
	}

	/**
	 * Filter Woo's API prepare customer so we can filter customer
	 * meta data if it's Metorik making an API request.
	 */
	public function filter_prepare_customer( $response, $user_data, $request ) {
		// get request headers
		$headers = $request->get_headers();

		// check we have headers and user agent set and string,
		if ( 
			$headers && 
			isset( $headers['user_agent'] ) && 
			isset( $headers['user_agent'][0] ) &&
			is_string( $headers['user_agent'][0] )
		) {
			// get user agent
			$user_agent = strtolower( $headers['user_agent'][0] );

			// if user agent has metorik in it, filter user meta to stop total spend/order count calculations
			if ( strpos( $user_agent, 'metorik' ) !== false ) {
				add_filter( 'get_user_metadata', array( $this, 'filter_user_metadata' ), 10, 4 );
			}
		}

		// or as a backup method - check if no spend data param is set
		if ( $request->get_param( 'no_spend_data' ) ) {
			add_filter( 'get_user_metadata', array( $this, 'filter_user_metadata' ), 10, 4 );
		}

		// regardless, return response
		return $response;
	}

	/**
	 * Filter user meta for total spent + order count so that
	 * if it's not yet set, get_user_meta will return 0.
	 * This is so WC doesn't attempt to calculate it
	 * while Metorik is doing customer queries.
	 * 
	 * This is called when Metorik is making a customer-related API
	 * request to the store (determined by user agent/query param).
	 */
	public function filter_user_metadata( $value, $object_id, $meta_key, $single ) {
		// Check if it's one of the keys we want to filter
		if ( in_array( $meta_key, array( '_money_spent', '_order_count' ) ) ) {
			// Return 0 so WC doesn't try calculate it
			return 0;
		}

		// Default
		return $value;
	}
}

new Metorik_Import_Helpers();