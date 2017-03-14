<?php

/**
 * Subscriptions API for Metorik.
 */
class Metorik_Helper_API_Subscriptions extends WC_REST_Posts_Controller {
	public $namespace = 'wc/v1';

	public $post_type = 'shop_subscription';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'subscriptions_ids_route' ) );
		add_action( 'rest_api_init', array( $this, 'subscriptions_updated_route' ) );
	}

	/**
	 * Subscriptions IDs route definition.
	 */
	public function subscriptions_ids_route() {
		register_rest_route( $this->namespace, '/subscriptions/ids/', array(
			'methods' => WP_REST_Server::READABLE,
			'callback' => array( $this, 'subscriptions_ids_callback' ),
			'permission_callback' => array( $this, 'get_items_permissions_check' ),
		) );
	}

	/**
	 * Subscriptions updated route definition.
	 */
	public function subscriptions_updated_route() {
		register_rest_route( $this->namespace, '/subscriptions/updated/', array(
			'methods' => WP_REST_Server::READABLE,
			'callback' => array( $this, 'subscriptions_updated_callback' ),
			'permission_callback' => array( $this, 'get_items_permissions_check' ),
		) );
	}

	/**
	 * Callback for the Order IDs API endpoint.
	 * Will likely be depreciated in a future version in favour of the subscriptions updated endpoint.
	 */
	public function subscriptions_ids_callback() {
		/**
		 * Get subscriptions.
		 */
		$subscriptions = new WP_Query( array(
			'post_type' => $this->post_type,
			'posts_per_page' => -1,
			'post_status' => 'any',
			'fields' => 'ids',
		) );

		/**
		 * No subscriptions.
		 */
		if (! $subscriptions->have_posts()) {
			return false;
		}

		/**
		 * Prepare response.
		 */
		$data = array(
			'count' => $subscriptions->post_count,
			'ids' => $subscriptions->posts,
		);

		/**
		 * Response.
		 */
		$response = rest_ensure_response( $data );
		$response->set_status( 200 );

		return $response;
	}

	/**
	 * Callback for the Subscriptions updated API endpoint.
	 * Later this will likely replace the IDs endpoint completely as it gets depreciated.
	 */
	public function subscriptions_updated_callback( $request ) {
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
		 * Get subscriptions where the date modified is greater than x days ago.
		 */
		$subscriptions = $wpdb->get_results( $wpdb->prepare(
			"
				SELECT 
					id,
					UNIX_TIMESTAMP(CONVERT_TZ(post_modified_gmt, '+00:00', @@session.time_zone)) as last_updated
				FROM $wpdb->posts
				WHERE post_type = 'shop_subscription' 
					AND post_modified > %s
					AND post_status != 'trash'
			", array(
				$from
			)
		) );

		/**
		 * Prepare response.
		 */
		$data = array(
			'subscriptions' => $subscriptions,
		);

		/**
		 * Response.
		 */
		$response = rest_ensure_response( $data );
		$response->set_status( 200 );

		return $response;
	}
}

new Metorik_Helper_API_Subscriptions();