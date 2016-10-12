<?php
/**
 * Plugin Name: Metorik Helper
 * Plugin URI: https://metorik.com
 * Description: Provides some fixes & extensions for WooCommerce, required by Metorik.
 * Version: 0.2.0
 * Author: Metorik
 * Author URI: https://metorik.com
*/

class Metorik_Helper {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Start plugin.
	 */
	public function init() {
		if ( class_exists( 'WooCommerce' ) ) {
			require_once( 'inc/import.php' );
			require_once( 'inc/api.php' );
		} else {
			add_action( 'admin_notices', array( $this, 'no_wc' ) );
		}
	}

	/**
	 * No WC notice.
	 */
	public function no_wc() {
		echo '<div class="error"><p>' . sprintf( __( 'Metorik Helper requires %s to be installed and active.', 'metorik-helper' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</p></div>';
	}
}
new Metorik_Helper();