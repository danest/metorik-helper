<?php
/**
 * Plugin Name: Metorik Helper
 * Plugin URI: https://metorik.com
 * Description: Provides some fixes & extensions for WooCommerce, required by Metorik.
 * Version: 0.2.3
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
			// Activate notice (shown once)
			add_action( 'admin_notices', array( $this, 'activate_notice' ) );

			// Require files for the plugin
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
		echo '<div class="notice notice-error"><p>' . sprintf( __( 'Metorik Helper requires %s to be installed and active.', 'metorik-helper' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</p></div>';
	}

	/**
	 * Run on activation.
	 */
	public function activate() {
		// Set Metorik's show activation notice option to true
		update_option( 'metorik_show_activation_notice', true );
	}

	/**
	 * Activate notice (if we should).
	 */
	public function activate_notice() {
		if ( get_option( 'metorik_show_activation_notice', false ) ) {
			echo '<div class="notice notice-success"><p>' . sprintf( __( 'The Metorik Helper is active! Go back to %s to complete the connection.', 'metorik-helper' ), '<a href="https://app.metorik.com/" target="_blank">Metorik</a>' ) . '</p></div>';

			// Disable notice option
			update_option( 'metorik_show_activation_notice', false );
		}
	}
}
new Metorik_Helper();

// Notice after it's been activated
register_activation_hook( __FILE__, array( 'Metorik_Helper', 'activate' ) );