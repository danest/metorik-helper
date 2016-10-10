<?php

/**
 * Customers API for Metorik.
 */
class Metorik_Helper_API_Customers extends WC_REST_Posts_Controller {
	public $namespace = 'wc/v1';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'customers_ids_route' ) );
	}

	/**
	 * Customers IDs route definition.
	 */
	public function customers_ids_route() {
		register_rest_route( $this->namespace, '/customers/ids/', array(
			'methods' => WP_REST_Server::READABLE,
			'callback' => array( $this, 'customers_api_callback' ),
			'permission_callback' => array( $this, 'get_items_permissions_check' ),
		) );
	}

	/**
	 * Check whether a given request has permission to read customers.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! wc_rest_check_user_permissions( 'read' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Callback for the Customer IDs API endpoint.
	 */
	public function customers_api_callback() {
		global $wpdb;

		/**
		 * Get users where their capability/role includes customer.
		 */
		$customers = $wpdb->get_results(
			"
				SELECT user_id
				FROM wp_usermeta
				WHERE meta_key = 'wp_capabilities' 
					AND meta_value LIKE '%customer%'
			"
		);

		/**
		 * No customers.
		 */
		if (! $customers) {
			return false;
		}

		/**
		 * Just get IDs.
		 */
		$customers = array_map( 'current', $customers );

		/**
		 * Prepare response.
		 */
		$data = array(
			'count' => count( $customers ),
			'ids' => $customers,
		);

		/**
		 * Response.
		 */
		$response = rest_ensure_response( $data );
		$response->set_status( 200 );

		return $response;
	}
}

new Metorik_Helper_API_Customers();