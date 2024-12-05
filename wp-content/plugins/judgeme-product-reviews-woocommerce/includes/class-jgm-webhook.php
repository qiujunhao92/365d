<?php
/**
 * Judge.me Webhook class
 *
 * @author   Judge.me
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class JGM_Webhook {

	public function __construct() {
		$this->init();
	}

	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_wp_api_endpoints' ) );
	}

	public function register_wp_api_endpoints() {

		$namespace = constant( 'JGM_NAMESPACE' );

		// Preview Badge Webhook
		register_rest_route( $namespace, '/widget/preview_badge/updated', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'webhook_preview_badge_callback' ),
			'permission_callback' => '__return_true', // authentication is handled in `handle_callback()`
		) );

		// Preview Badge Webhook
		register_rest_route( $namespace, '/widget/review_widget/updated', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'webhook_review_widget_callback' ),
			'permission_callback' => '__return_true', // authentication is handled in `handle_callback()`
		) );

		// Setting Updated Webhook
		register_rest_route( $namespace, '/widget/settings/updated', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'webhook_setting_updated_callback' ),
			'permission_callback' => '__return_true', // authentication is handled in `handle_callback()`
		) );

		// All Reviews Count Webhook
		register_rest_route( $namespace, '/widget/all_reviews_count/updated', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'webhook_widget_all_reviews_count_callback' ),
			'permission_callback' => '__return_true', // authentication is handled in `handle_callback()`
		) );

		// All Reviews Rating Webhook
		register_rest_route( $namespace, '/widget/all_reviews_rating/updated', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'webhook_widget_all_reviews_rating_callback' ),
			'permission_callback' => '__return_true', // authentication is handled in `handle_callback()`
		) );

		// Verified Badge Webhook
		register_rest_route( $namespace, '/widget/verified_badge/updated', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'webhook_widget_verified_badge_callback' ),
			'permission_callback' => '__return_true', // authentication is handled in `handle_callback()`
		) );

		// All Reviews Webhook
		register_rest_route( $namespace, '/widget/all_reviews_page/updated', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'webhook_widget_all_reviews_callback' ),
			'permission_callback' => '__return_true', // authentication is handled in `handle_callback()`
		) );

		// Featured Carousel Webhook
		register_rest_route( $namespace, '/widget/featured_carousel/updated', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'webhook_widget_featured_carousel_callback' ),
			'permission_callback' => '__return_true', // authentication is handled in `handle_callback()`
		) );

		// UGC media grid Webhook
		register_rest_route( $namespace, '/widget/ugc_media_grid/updated', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'webhook_widget_ugc_media_grid_callback' ),
			'permission_callback' => '__return_true', // authentication is handled in `handle_callback()`
		) );

		// Reviews Tab Webhook
		register_rest_route( $namespace, '/widget/reviews_tab/updated', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'webhook_widget_reviews_tab_callback' ),
			'permission_callback' => '__return_true', // authentication is handled in `handle_callback()`
		) );

		// HTML Miracle Webhook
		register_rest_route( $namespace, '/widget/html_miracle/updated', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'webhook_widget_html_miracle_callback' ),
			'permission_callback' => '__return_true', // authentication is handled in `handle_callback()`
		) );

		// Domain check
		register_rest_route( $namespace, '/domain_check', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'webhook_domain_check_callback' ),
			'permission_callback' => '__return_true', // public endpoint
		) );

		// Review created
		register_rest_route( $namespace, '/review/created', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'webhook_review_created_callback' ),
			'permission_callback' => '__return_true', // authentication is handled in the callback
		) );

		register_rest_route( $namespace, '/review/created_fail', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'webhook_review_created_fail_callback' ),
			'permission_callback' => '__return_true', // authentication will be handled in the callback
		) );
	}

	public function webhook_preview_badge_callback( $data ) {
		$this->handle_callback( $data, 'preview_badge', false, true );
	}

	public function webhook_review_widget_callback( $data ) {
		$this->handle_callback( $data, 'review_widget', false, true );
	}

	public function webhook_setting_updated_callback( $data ) {
		$this->handle_callback( $data, 'settings', true );
	}

	public function webhook_widget_all_reviews_count_callback( $data ) {
		$this->handle_callback( $data, 'all_reviews_count' );
	}

	public function webhook_widget_all_reviews_rating_callback( $data ) {
		$this->handle_callback( $data, 'all_reviews_rating' );
	}

	public function webhook_widget_verified_badge_callback( $data ) {
		$this->handle_callback( $data, 'verified_badge' );
	}

	public function webhook_widget_all_reviews_callback( $data ) {
		$this->handle_callback( $data, 'all_reviews' );
	}

	public function webhook_widget_featured_carousel_callback( $data ) {
		$this->handle_callback( $data, 'feature_carousel' );
	}

	public function webhook_widget_ugc_media_grid_callback( $data ) {
		$this->handle_callback( $data, 'ugc_media_grid' );
	}

	public function webhook_widget_reviews_tab_callback( $data ) {
		$this->handle_callback( $data, 'reviews_tab', true );
	}

	public function webhook_widget_html_miracle_callback( $data ) {
		$this->handle_callback( $data, 'html_miracle', true );
	}

	public function webhook_domain_check_callback( $data ) {
		$token = get_option( 'judgeme_shop_token', false );
		if ( false == $token ) {
			return new WP_REST_Response( array( "loggedin" => false ), 200 );
		} else {
			return new WP_REST_Response( array( "loggedin" => true ), 200 );
		}
	}

	public function webhook_review_created_callback( $data ) {
		$token           = get_option( 'judgeme_shop_token' );
		$header_hashed   = $data->get_header( 'JUDGEME-HMAC-SHA256' );
		$internal_hashed = hash_hmac( 'sha256', $data->get_body(), $token, false );
		if ( hash_equals( $header_hashed, $internal_hashed ) ) {
			$body           = $data->get_json_params();
			$review_id      = $body['review']['id'];
			$reviewer_email = $body['review']['reviewer']['email'];
			$prod_id        = $body['review']['product_external_id'];

			$verified = wc_customer_bought_product( $reviewer_email, null, $prod_id );

			if ( $verified ) {
				$domain = constant( 'JGM_SHOP_DOMAIN' );
				$url    = constant( 'JGM_ADAPTER_HOST' ) . '/reviews/verify';
				$data   = array(
					'method'   => 'POST',
					'blocking' => false,
					'headers'  => array( 'Content-Type' => 'application/json' ),
					'body'     => json_encode( array(
						'domain'    => $domain,
						'token'     => $token,
						'review_id' => $review_id
					) ),
				);
				wp_safe_remote_post( $url, $data );
			}

			return new WP_REST_Response( array(
				'review'   => $review_id,
				'email'    => $reviewer_email,
				'verified' => $verified
			), 200 );
		} else {
			return new WP_REST_Response( array( "status" => "invalid data" ), 422 );
		}
	}

	public function webhook_review_created_fail_callback( $data ) {
		// TODO Handle review created failed webhook.
	}

	private function handle_callback( $data, $widget_name, $autoload = false, $single_product = null, $widget_prefix = true ) {
		$token = get_option( 'judgeme_shop_token' );

		$header_hashed   = $data->get_header( 'JUDGEME-HMAC-SHA256' );
		$internal_hashed = hash_hmac( 'sha256', $data->get_body(), $token, false );
		if ( hash_equals( $header_hashed, $internal_hashed ) ) {
			$body = $data->get_json_params();

			$transient_name = 'judgeme_';

			if ( true === $widget_prefix ) {
				$transient_name .= 'widget_';
			}

			$transient_name .= $widget_name;

			if ( true === $single_product ) {
				$id = $body['product_external_id'];
				update_post_meta( $id, '_' . $transient_name, wp_slash( $body ) );
			} else {
				update_option( $transient_name, $body, $autoload );
			}

			$skip_clearing_cache_by_default = get_option('judgeme_option_skip_clearing_cache_by_default');

			if ( !$skip_clearing_cache_by_default ) {
				$this->clear_wp_cache();
			}

			return new WP_REST_Response( array( 'status' => 'ok' ), 200 );
		} else {
			return new WP_REST_Response( array( 'status' => 'invalid data' ), 422 );
		}
	}

	private function clear_wp_cache() {

		global $wp_fastest_cache;

		if ( function_exists( 'rocket_clean_domain' ) ) { // WP Rocket
			rocket_clean_domain();
		}
		// Purge entire WP Rocket cache.


		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}

		if ( function_exists( 'wp_cache_clear_cache' ) ) { // WP Super Cache
			wp_cache_clear_cache();
		}

		if ( function_exists( 'w3tc_flush_posts' ) ) { // W3 Total Cache
			w3tc_flush_posts();
		}
		if ( has_action( 'ce_clear_cache' ) ) { // Cache Enabler
			do_action( 'ce_clear_cache' );
		}
		if ( class_exists( 'Breeze_PurgeCache' ) ) { // Breeze
			if ( method_exists( 'Breeze_PurgeCache', 'breeze_cache_flush' ) ) {
				Breeze_PurgeCache::breeze_cache_flush();
			}
		}

		if ( class_exists( 'Swift_Performance_Cache' ) ) {
			if ( method_exists( 'Swift_Performance_Cache', 'clear_all_cache' ) ) {
				Swift_Performance_Cache::clear_all_cache();
			}
		}


		if ( method_exists( 'WpFastestCache', 'deleteCache' ) && ! empty( $wp_fastest_cache ) ) { // WP Fastest Cache
			$wp_fastest_cache->deleteCache();
		}

		if ( class_exists( 'WpeCommon' ) ) { // Autoptimize
			if ( method_exists( 'WpeCommon', 'purge_memcached' ) ) {
				WpeCommon::purge_memcached();
			}
			if ( method_exists( 'WpeCommon', 'clear_maxcdn_cache' ) ) {
				WpeCommon::clear_maxcdn_cache();
			}
			if ( method_exists( 'WpeCommon', 'purge_varnish_cache' ) ) {
				WpeCommon::purge_varnish_cache();
			}
		}

		if ( function_exists('sg_cachepress_purge_cache') ) { // SGOptimzer
			sg_cachepress_purge_cache();
		}
	}

	private function clear_wp_cache_single( $post_id ) {
		//WP Super Cache
		if ( function_exists( 'wp_cache_post_change' ) ) {
			wp_cache_post_change( $post_id );
		}

		// W3 Total Cache
		if ( function_exists( 'w3tc_flush_post' ) ) {
			w3tc_flush_post( $post_id );
		}

		// Cache Enabler
		if ( has_action( 'ce_clear_post_cache' ) ) {
			do_action( 'ce_clear_post_cache', $post_id );
		}
	}

}
