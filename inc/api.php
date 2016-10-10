<?php

/**
 * This class loads Metorik's API endpoints/code.
 */
class Metorik_Helper_API {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->includes();
	}

	/**
	 * Include necessary files for the API.
	 */
	public function includes()
	{
		require_once( 'api/orders.php' );
		require_once( 'api/customers.php' );
		require_once( 'api/products.php' );
		require_once( 'api/metorik.php' );
	}
}

new Metorik_Helper_API();