<?php
/**
 * Judge.me Initilizer class
 *
 * @author   Judge.me
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class JGM_Initilizer {
	public function __construct() {

		// Register plugin
		$this->register_judgeme_api();

		// Enqueue scripts to admin area.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Enqueue scripts to site.
		if ( ! defined( 'JGM_CDN_DOMAIN' ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_judgeme_cdn_scripts' ) );
			add_filter( 'script_loader_tag', array( $this, 'async_load_judgeme_cdn_js' ), 10, 2 );	
		}
	}

	public function register_judgeme_api() {
		if ( ! get_option( 'judgeme_shop_token' ) && ! get_option( 'judgeme_is_installing' ) ) {
			add_option( 'judgeme_is_installing', true );

			$domain = constant( 'JGM_SHOP_DOMAIN' );
			$url    = constant( 'JGM_ADAPTER_HOST' ) . '/shops';
			global $woocommerce;

			$name = get_bloginfo( 'name' );
			if (empty($name)) {
				$name = $domain;
			}


			$data = array(
				'method'   => 'POST',
				'blocking' => true,
				'headers'  => array( 'Content-Type' => 'application/json' ),
				'body'     => json_encode( array(
					'domain'   => $domain,
					'owner'    => $name,
					'email'    => get_bloginfo( 'admin_email' ),
					'name'     => $name,
					'country'  => $woocommerce->countries->get_base_country(),
					'timezone' => get_option( 'timezone_string' ),
					'is_ssl'   => is_ssl(),
				) ),
			);

			$response = wp_safe_remote_post( $url, $data );

			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				update_option('judgeme_register_error', $error_message);
				delete_option( 'judgeme_is_installing' );
			} else {
				$response_code = $response['response']['code'];
				$shop          = json_decode( $response['body'] );

				if ( 200 == $response_code ) { // Shop already been created before and registered in adapter.
					add_option( 'judgeme_shop_token', $shop->shop->token );
					delete_option( 'judgeme_is_installing' );
				} elseif ( 201 == $response_code ) { // Shop is new and just has been registered.
					add_option( 'judgeme_shop_token', $shop->shop->token );
					delete_option( 'judgeme_is_installing' );
				} elseif ( 202 == $response_code ) { // Shop is in the core api but not in adapter, probably moving from other platform.
					add_option( 'judgeme_shop_token', $shop->shop->token );
					delete_option( 'judgeme_is_installing' );
				} else { // Something went wrong.
					delete_option( 'judgeme_is_installing' );
				}
				update_option('judgeme_domain', $domain);
			}
		}
	}

	public function enqueue_admin_scripts( $hook ) {
		wp_enqueue_style( 'judgeme_admin', JGM_PLUGIN_URL . 'assets/stylesheets/judgeme.css' );

		if ( 'toplevel_page_judgeme' != $hook ) {
			return;
		}
		wp_enqueue_style( 'judgeme_all_css', JGM_PLUGIN_URL . '/vendor/stylesheets/all.css' );
		wp_enqueue_style( 'judgeme_bootstrap_css', JGM_PLUGIN_URL . '/vendor/stylesheets/bootstrap.css' );
		wp_enqueue_script( 'judgeme_admin', JGM_PLUGIN_URL . 'assets/javascript/judgeme.js' );
		wp_enqueue_script( 'judgeme_admin_clipboard_js', JGM_PLUGIN_URL . 'vendor/javascripts/clipboard.js' );
		wp_enqueue_script( 'judgeme_admin_bootstrap_js', JGM_PLUGIN_URL . '/vendor/javascripts/bootstrap.js' );
	}

	public function enqueue_judgeme_cdn_scripts() {
		wp_enqueue_script( 'judgeme_cdn', 'https://cdn.judge.me/judgeme_widget_v2.js', null, null, false );
		wp_enqueue_style( 'judgeme_cdn', 'https://cdn.judge.me/judgeme_widget_v2.css', null, null, 'all' );
	}

	public function async_load_judgeme_cdn_js( $tag, $handle ) {
		if ( 'judgeme_cdn' === $handle ) {
			return str_replace( ' src', ' data-cfasync="false" async src', $tag );
		}

		return $tag;
	}

}
