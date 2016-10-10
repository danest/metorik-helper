<?php

/**
 * These are small changes that help Metorik complete it's import of the store.
 */
class Metorik_Import_Helpers {
	public function __construct() {
		// Only if the metorik_importing_currently is set (through API)
		if ( get_option( 'metorik_importing_currently', false ) ) {
			add_filter( 'get_user_metadata', array( $this, 'filter_user_metadata' ), 10, 4 );
		}
	}

	/**
	 * Filter user meta for total spent + order count so that if
	 * it's not yet set, get_user_meta will return 0.
	 * This is so WC doesn't attempt to calculate it
	 * while Metorik is doing the customers import.
	 *
	 * Of course, this does mess with WC's reporting and API when it comes
	 * to returning a customer's total spent / order count, but if
	 * you're using Metorik, you have no need for that.
	 *
	 * However, the option for metorik_importing_currently needs to be true
	 * in order for this override to happen, and that's enabled/disabled
	 * by the Metorik API, so it's not a huge concern.
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