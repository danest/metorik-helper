<?php

/**
 * Orders API for Metorik.
 */
class Metorik_Helper_API_Metorik extends WC_REST_Posts_Controller {
	public $namespace = 'wc/v1';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'metorik_info_route' ) );
		add_action( 'rest_api_init', array( $this, 'metorik_importing_route' ) );
	}

	/**
	 * Metorik info route definition.
	 */
	public function metorik_info_route() {
		register_rest_route( $this->namespace, '/metorik/info/', array(
			'methods' => WP_REST_Server::READABLE,
			'callback' => array( $this, 'metorik_info_callback' ),
			'permission_callback' => array( $this, 'get_items_permissions_check' ),
		) );
	}

	/**
	 * Metorik importing route definition.
	 */
	public function metorik_importing_route() {
		register_rest_route( $this->namespace, '/metorik/importing/', array(
			'methods' => WP_REST_Server::EDITABLE,
			'callback' => array( $this, 'update_metorik_importing_callback' ),
			'permission_callback' => array( $this, 'update_items_permissions_check' ),
		) );
	}

	/**
	 * Check whether a given request has permission to read info.
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! wc_rest_check_user_permissions( 'read' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Need write/create permission.
	 */
	public function update_items_permissions_check() {
		if ( ! wc_rest_check_user_permissions( 'create' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_create', __( 'Sorry, you are not allowed to create resources.', 'woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Callback for the Metorik Info API endpoint.
	 */
	public function metorik_info_callback() {
		/**
		 * Get plugins.
		 */
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = get_plugins();

		/**
		 * Prepare response.
		 */
		$data = array(
			'active' => true,
			'version' => Metorik_Helper()->version,
			'plugins' => $plugins,
		);

		/**
		 * Response.
		 */
		$response = rest_ensure_response( $data );
		$response->set_status( 200 );

		return $response;
	}

	/**
	 * Callback for the Orders API endpoint.
	 */
	public function update_metorik_importing_callback( $request ) {
		/**
		 * Check status set.
		 */
		if (! isset( $request['status'] ) ) {
			return new WP_Error( 'woocommerce_rest_metorik_invalid_importing_status', __( 'Invalid status.', 'woocommerce' ), array( 'status' => 400 ) );
		}

		/**
		 * Get and sanitize status.
		 */
		$status = $request['status'] ? true : false;

		/**
		 * Update status.
		 */
		update_option( 'metorik_importing_currently', $status );

		/**
		 * Prepare response.
		 */
		$data = array(
			'updated' => true,
			'status' => get_option( 'metorik_importing_currently' ),
		);

		/**
		 * Response.
		 */
		$response = rest_ensure_response( $data );
		$response->set_status( 200 );

		return $response;
	}
}

new Metorik_Helper_API_Metorik();