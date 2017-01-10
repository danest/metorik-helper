<?php

/**
 * Make changes to the admin UI, like links to resources in Metorik.
 */
class Metorik_UI {
	public function __construct() {
		// filter to hide it
		if ( apply_filters( 'metorik_show_ui', true ) ) {
			// product/order meta boxes
			add_action( 'admin_head', array( $this, 'custom_css' ) );
			add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );

			// customers table
			add_filter( 'manage_users_columns', array( $this, 'modify_user_table' ) );
			add_filter( 'manage_users_custom_column', array( $this, 'add_user_table_column' ), 10, 3 );

			// admin notices (for reports)
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		}
	}

	/**
	 * Custom CSS for admin.
	 */
	public function custom_css() {
		$ids = array(
			'metorik-product-box',
			'metorik-order-box',
		);

		echo '<style>';

		foreach ( $ids as $id ) {
			echo '
				#' . $id . ' button { display: none; }
				#' . $id . ' h2 { display: none; }
				#' . $id . ' .inside { padding: 0; margin: 0; }
				#' . $id . ' .inside a { display: block; font-weight: bold; padding: 12px; text-decoration: none; vertical-align: middle; }
				#' . $id . ' .inside a:hover { background: #fafafa; }
				#' . $id . ' .inside a img { display: inline-block; margin: -4px 5px 0 0; vertical-align: middle; width: 20px; }
				#' . $id . ' .inside a span { float: right; }
			';
		}

		echo '</style>';
	}

	/**
	 * Register meta box(es).
	 */
	public function register_meta_boxes() {
		add_meta_box( 'metorik-product-box', __( 'Metorik', 'metorik' ), array( $this, 'product_box_display' ), 'product', 'side', 'high' );
		add_meta_box( 'metorik-order-box', __( 'Metorik', 'metorik' ), array( $this, 'order_box_display' ), 'shop_order', 'side', 'high' );
	}
	 
	/**
	 * Product meta box display callback.
	 */
	public function product_box_display( $post ) {
		echo '<a href="https://app.metorik.com/product/' . $post->ID . '">
			<img src="' . Metorik_Helper()->url . 'assets/img/metorik.png" /> View on Metorik <span class="dashicons dashicons-arrow-right-alt2"></span>
		</a>';
	}

	/**
	 * Order meta box display callback.
	 */
	public function order_box_display( $post ) {
		echo '<a href="https://app.metorik.com/order/' . $post->ID . '">
			<img src="' . Metorik_Helper()->url . 'assets/img/metorik.png" /> View on Metorik <span class="dashicons dashicons-arrow-right-alt2"></span>
		</a>';
	}

	/**
	 * Add column header to users table.
	 */
	public function modify_user_table( $column ) {
		$column['metorik'] = 'Metorik';
		return $column;
	}

	/**
	 * Add column body to users table.
	 */
	public function add_user_table_column( $val, $column_name, $user_id ) {
		switch ($column_name) {
			case 'metorik' :
				return '<a href="https://app.metorik.com/customer/' . $user_id . '">View</a>';
				break;
			default:
		}
		return $val;
	}

	/**
	 * Admin notices.
	 */
	public function admin_notices() {
		$screen = get_current_screen()->base;
		$links = false; // default

		// reports
		if ( $screen == 'woocommerce_page_wc-reports' ) {
			$report = sanitize_text_field( $_GET['report'] );
			$tab = sanitize_text_field( $_GET['tab'] );

			// no report set? check if root of this tab
			if ( ! $report ) {
				switch ( $tab ) {
					case 'orders':
						$report = 'sales_by_date';
						break;
					case 'customers':
						$report = 'customers';
						break;
				}
			}

			// no tab? sales
			if ( ! isset( $GET['tab'] ) ) {
				$report = 'sales_by_date';
			}

			switch ( $report ) {
				case 'sales_by_date':
					$links = [
						[
							'report' => 'Sales Report',
							'link' => 'reports/orders',
						],
						[
							'report' => 'Refunds Report',
							'link' => 'reports/refunds',
						],
					];
					break;
				case 'sales_by_product':
					$links = [
						[
							'report' => 'All Products',
							'link' => 'products',
						],
						[
							'report' => 'Compare Products',
							'link' => 'reports/products',
						],
					];
					break;
				case 'sales_by_category':
					$links = [
						[
							'report' => 'All Categories',
							'link' => 'categories',
						],
					];
					break;
				case 'customers':
					$links = [
						[
							'report' => 'Customers Report',
							'link' => 'reports/customers',
						],
						[
							'report' => 'Customer Retention',
							'link' => 'reports/customer-retention',
						],
					];
					break;
				case 'customer_list':
					$links = [
						[
							'report' => 'All Customers',
							'link' => 'customers',
						],
					];
					break;
			}
		}

		// resources
		if ( $screen == 'edit' ) {
			$type = sanitize_text_field( $_GET['post_type'] );

			switch ( $type ) {
				case 'shop_order':
					$links = [
						[
							'report' => 'All Orders',
							'link' => 'orders',
						]
					];
					break;
				case 'product':
					$links = [
						[
							'report' => 'All Products',
							'link' => 'products',
						]
					];
					break;
			}
		}

		// users
		if ( $screen == 'users' ) {
			$links = [
				[
					'report' => 'All Customers',
					'link' => 'customers',
				]
			];
		}

		if ( $screen == 'edit-tags' ) {
			$tax = sanitize_text_field( $_GET['taxonomy'] );
			$type = sanitize_text_field( $_GET['post_type'] );
			if ( $tax == 'product_cat' && $type == 'product' ) {
				$links = [
					[
						'report' => 'All Categories',
						'link' => 'categories',
					],
				];
			}
		}

		// output notice if have links
		if ( $links ) {
			echo '<div class="updated"><p>You can view a more detailed, powerful, and accurate version of this on Metorik: ';
			foreach ( $links as $key => $link ) {
				echo '<a href="https://app.metorik.com/' . $link['link'] . '">' . $link['report'] . '</a>';
				if ( $key + 1 < count( $links ) ) {
					echo ' & ';
				}
			}
			echo '</p></div>';
		}
	}
}

new Metorik_UI();