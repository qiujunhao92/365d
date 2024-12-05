<?php
/**
 * Judge.me Hook class
 *
 * @author   Judge.me
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class JGM_Hook {
	public function __construct() {
		$this->init_woocommerce_hooks();
	}

	private function init_woocommerce_hooks() {
		// Product hook
		add_action( 'added_post_meta', array( $this, 'judgeme_product_updated_hook' ), 10, 4 );
		add_action( 'updated_post_meta', array( $this, 'judgeme_product_updated_hook' ), 10, 4 );
		add_action( 'woocommerce_product_duplicate', array( $this, 'judgeme_product_duplicate_hook' ), 10, 4 );
		add_action( 'wp_trash_post', array( $this, 'judgeme_product_delete_hook' ), 99 );
		add_action( 'before_delete_post', array( $this, 'judgeme_product_delete_hook' ), 99 );
		add_action( 'untrash_post', array( $this, 'judgeme_product_untrash_hook' ), 99 );

		// Checkout hook
		add_action( 'woocommerce_after_checkout_form', array( $this, 'judgeme_checkout_hook' ), 10 );

		// Order hook
		add_action( 'woocommerce_order_status_completed', array( $this, 'judgeme_order_completed_hook' ), 10 );
		add_action( 'woocommerce_order_status_cancelled', array( $this, 'judgeme_order_cancelled_hook' ), 10 );
	}

	public function judgeme_product_duplicate_hook( $duplicate, $product ) {
		$id = $duplicate->get_id();
		delete_post_meta( $id, '_judgeme_api_registered' );
		delete_post_meta( $id, '_judgeme_widget_preview_badge');
		delete_post_meta( $id, '_judgeme_widget_review_widget');
	}

	public function judgeme_checkout_hook() {
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item) {
			$prod_id = $cart_item['product_id'];
			JGM_ProductService::check_and_register_product( $prod_id );
		}
	}

	public function judgeme_product_updated_hook( $meta_id, $post_id, $meta_key, $meta_value ) {
		if ( $meta_key == '_edit_lock' ) { // we've been editing the post
			if ( get_post_type( $post_id ) == 'product' ) { // we've been editing a product
				JGM_ProductService::update_product( $post_id );
			}
		}
	}

	public function judgeme_product_delete_hook( $post_id) {
		if ( get_post_type( $post_id ) == 'product' ) {
 			JGM_ProductService::update_product( $post_id, false );
		}
	}

	public function judgeme_product_untrash_hook( $post_id) {
		if ( get_post_type( $post_id ) == 'product' ) {
 			JGM_ProductService::update_product( $post_id, true, true );
		}
	}

	public function judgeme_order_completed_hook( $order_id ) {
		$order    = wc_get_order( $order_id );
		$token    = get_option( 'judgeme_shop_token' );
		$domain   = constant( 'JGM_SHOP_DOMAIN' );
		$api_host = constant( 'JGM_API_HOST' );
		$url      = $api_host . '/orders';
		$lines    = array();

		foreach ( $order->get_items() as $key => $item ) {

			$quantity   = $item->get_quantity();
			$item_id    = $item->get_product_id();
			$item_price = $item->get_subtotal();
			$item_line  = array(
				'id'         => $key,
				'price'      => $item_price,
				'quantity'   => $quantity,
				'product_id' => $item_id
			);
			$lines      = array_merge( $lines, array( $item_line ) );
		}

		$completed_date = $order->get_date_completed();

		if ( ! $completed_date ) {
			$completed_date = $order->get_date_created();
		}

		if ( ! $completed_date ) {
			return;
		}

		$data = array(
			'method'   => 'POST',
			'blocking' => false,
			'headers'  => array( 'Content-Type' => 'application/json' ),
			'body'     => json_encode( array(
				'api_token'          => $token,
				'shop_domain'        => $domain,
				'id'                 => $order_id,
				'name'               => "#$order_id",
				'fulfillment_status' => 'fulfilled',
				'customer'           => array(
					'first_name'      => $order->get_billing_first_name(),
					'last_name'       => $order->get_billing_last_name(),
					'email'           => $order->get_billing_email(),
					'updated_at'      => $order->get_date_created()->date( "Y-m-d" ),
					'default_address' => array(
						'phone' => $order->get_billing_phone()
					),
				),
				'shipping_address'   => array(
					'country_code' => $order->get_shipping_country()
				),
				'fulfillments'       => array(
					array(
						'id'         => 1,
						'created_at' => $completed_date->date( "Y-m-d" )
					)
				),
				'line_items'         => $lines
			) )
		);

		wp_safe_remote_post( $url, $data );
	}

	public function judgeme_order_cancelled_hook( $order_id ) {
		$order    = wc_get_order( $order_id );
		$token    = get_option( 'judgeme_shop_token' );
		$domain   = constant( 'JGM_SHOP_DOMAIN' );
		$api_host = constant( 'JGM_API_HOST' );
		$url      = $api_host . '/orders/0';

		$data = array(
			'method'   => 'PUT',
			'blocking' => false,
			'headers'  => array( 'Content-Type' => 'application/json' ),
			'body'     => json_encode( array(
				'api_token'    => $token,
				'shop_domain'  => $domain,
				'external_id'  => $order_id,
				'cancelled_at' => $order->get_date_modified()->date( "Y-m-d" )
			) )
		);

		wp_safe_remote_post( $url, $data );
	}


}
