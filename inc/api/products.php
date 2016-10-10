<?php

/**
 * Products API for Metorik.
 */
class Metorik_Helper_API_Products extends WC_REST_Posts_Controller {
	public $namespace = 'wc/v1';

	public $post_type = 'product';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'products_ids_route' ) );
	}

	/**
	 * Products IDs route definition.
	 */
	public function products_ids_route() {
		register_rest_route( $this->namespace, '/products/ids/', array(
			'methods' => WP_REST_Server::READABLE,
			'callback' => array( $this, 'products_api_callback' ),
			'permission_callback' => array( $this, 'get_items_permissions_check' ),
		) );
	}

	/**
	 * Callback for the Order IDs API endpoint.
	 */
	public function products_api_callback() {
		/**
		 * Get products.
		 */
		$products = new WP_Query( array(
			'post_type' => $this->post_type,
			'posts_per_page' => -1,
			'post_status' => 'any',
			'fields' => 'ids',
		) );

		/**
		 * No products.
		 */
		if (! $products->have_posts()) {
			return false;
		}

		/**
		 * Prepare response.
		 */
		$data = array(
			'count' => $products->post_count,
			'ids' => $products->posts,
		);

		/**
		 * Response.
		 */
		$response = rest_ensure_response( $data );
		$response->set_status( 200 );

		return $response;
	}
}

new Metorik_Helper_API_Products();