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
		add_action( 'rest_api_init', array( $this, 'orders_updated_route' ) );
	}

	/**
	 * Orders IDs route definition.
	 */
	public function orders_ids_route() {
		register_rest_route( $this->namespace, '/orders/ids/', array(
			'methods' => WP_REST_Server::READABLE,
			'callback' => array( $this, 'orders_ids_callback' ),
			'permission_callback' => array( $this, 'get_items_permissions_check' ),
		) );
	}

	/**
	 * Orders IDs route definition.
	 */
	public function orders_updated_route() {
		register_rest_route( $this->namespace, '/orders/updated/', array(
			'methods' => WP_REST_Server::READABLE,
			'callback' => array( $this, 'orders_updated_callback' ),
			'permission_callback' => array( $this, 'get_items_permissions_check' ),
		) );
	}

	/**
	 * Callback for the Order IDs API endpoint.
	 * Will likely be depreciated in a future version in favour of the orders updated endpoint.
	 */
	public function orders_ids_callback() {
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

	/**
	 * Callback for the Orders updated API endpoint.
	 * Later this will likely replace the IDs endpoint completely as it gets depreciated.
	 */
	public function orders_updated_callback( $request ) {
		global $wpdb;

		/**
		 * Check days set and use default if not.
		 */
		$days = 30;
		if (isset( $request['days'] ) ) {
			$days = intval( $request['days'] );
		}

		// How many days back?
		$time = strtotime( '- ' . $days . ' days' );
		$from = date( 'Y-m-d H:i:s', $time );

		/**
		 * Get orders where the date modified is greater than x days ago.
		 */
		$orders = $wpdb->get_results( $wpdb->prepare(
			"
				SELECT 
					id,
					UNIX_TIMESTAMP(post_modified_gmt) as last_updated
				FROM $wpdb->posts
				WHERE post_type = 'shop_order' 
					AND post_modified_gmt > %s
			", array(
				$from
			)
		) );

		/**
		 * Prepare response.
		 */
		$data = array(
			'orders' => $orders,
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