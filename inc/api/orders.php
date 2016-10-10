<?php

/**
 * Orders API for Metorik.
 */
class Metorik_Helper_API_Orders extends WC_REST_Posts_Controller {
	public $namespace = 'wc/v1';

	public $post_type = 'shop_order';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'orders_ids_route' ) );
	}

	/**
	 * Orders IDs route definition.
	 */
	public function orders_ids_route() {
		register_rest_route( $this->namespace, '/orders/ids/', array(
			'methods' => WP_REST_Server::READABLE,
			'callback' => array( $this, 'orders_api_callback' ),
			'permission_callback' => array( $this, 'get_items_permissions_check' ),
		) );
	}

	/**
	 * Callback for the Order IDs API endpoint.
	 */
	public function orders_api_callback() {
		/**
		 * Get orders.
		 */
		$orders = new WP_Query( array(
			'post_type' => $this->post_type,
			'posts_per_page' => -1,
			'post_status' => 'any',
			'fields' => 'ids',
		) );

		/**
		 * No orders.
		 */
		if (! $orders->have_posts()) {
			return false;
		}

		/**
		 * Prepare response.
		 */
		$data = array(
			'count' => $orders->post_count,
			'ids' => $orders->posts,
		);

		/**
		 * Response.
		 */
		$response = rest_ensure_response( $data );
		$response->set_status( 200 );

		return $response;
	}
}

new Metorik_Helper_API_Orders();