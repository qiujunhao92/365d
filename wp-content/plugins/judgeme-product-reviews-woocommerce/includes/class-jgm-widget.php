<?php
/**
 * Judge.me Widget class
 *
 * @author   Judge.me
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Judge.me Widget class
 */
class JGM_Widget {

	private $token;
	private $domain;
	private $api_host;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->token    = get_option( 'judgeme_shop_token' );
		$this->domain   = constant( 'JGM_SHOP_DOMAIN' );
		$this->api_host = constant( 'JGM_API_HOST' );
		$this->init();
	}

	public function init() {
		// Inject judge.me heading.
		add_action( 'wp_head', array( $this, 'judgeme_heading' ), 1 );

		// Remove built-in review (rating) code.
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );

		$hide_widget = get_option('judgeme_option_hide_widget');
		$hide_preview_badge_collection = get_option('judgeme_option_hide_preview_badge_collection');
		$hide_preview_badge_single = get_option('judgeme_option_hide_preview_badge_single');

		// Inject our rating code.
		if ( !$hide_preview_badge_single ) {
			add_action( 'woocommerce_single_product_summary', array( 'JGM_Widget', 'judgeme_preview_badge' ), 9 );
		}

		if ( !$hide_preview_badge_collection ) {
			add_action( 'woocommerce_after_shop_loop_item_title', array( 'JGM_Widget', 'judgeme_preview_badge' ), 5 );
		}

		// Add Review widget box.
		if ( !$hide_widget ) {
			add_action( 'woocommerce_after_single_product_summary', array( 'JGM_Widget', 'judgeme_review_widget' ), 14 );
		}

		// Remove built-in review tab.
		add_filter( 'woocommerce_product_tabs', array( $this, 'judgeme_remove_builtin_review_tab' ), 98 );

		add_shortcode( 'jgm-verified-badge', array( $this, 'judgeme_verified_badge' ) );
		add_shortcode( 'jgm-all-reviews', array( $this, 'judgeme_all_reviews' ) );
		add_shortcode( 'jgm-feature-carousel', array( $this, 'judgeme_feature_carousel' ) );
		add_shortcode( 'jgm-featured-carousel', array( $this, 'judgeme_feature_carousel' ) );
		add_shortcode( 'jgm-review-tab', array( $this, 'judgeme_review_tab' ) );
		add_shortcode( 'jgm-reviews-tab', array( $this, 'judgeme_review_tab' ) );
		add_shortcode( 'jgm-review-count', array( $this, 'judgeme_all_reviews_count' ) );
		add_shortcode( 'jgm-review-rating', array( $this, 'judgeme_all_reviews_rating' ) );
		add_shortcode( 'jgm-review-widget', array( $this, 'judgeme_review_widget_shortcode' ) );
		add_shortcode( 'jgm-reviews-widget', array( $this, 'judgeme_review_widget_shortcode' ) );
		add_shortcode( 'jgm-preview-badge', array( $this, 'judgeme_preview_badge_shortcode' ) );
		add_shortcode( 'jgm-star-badge', array( $this, 'judgeme_preview_badge_shortcode' ) );
		add_shortcode( 'jgm-ugc-media-grid', array( $this, 'judgeme_ugc_media_grid' ) );

		add_filter( 'widget_text', 'do_shortcode' );

		add_filter( 'woocommerce_structured_data_product', array(
			$this,
			'filter_woocommerce_structured_data_review'
		), 10, 2 );

		$this->get_all_reviews_count();
		add_action( 'wp_ajax_jgm_toggle_widget_placement', array( $this, 'jgm_toggle_widget_placement' ) );
	}

	public function jgm_toggle_widget_placement() {
		check_ajax_referer( 'jgm_export_reviews', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		$widget_type = sanitize_text_field( $_REQUEST['widget_type'] );

		$option = get_option( $widget_type );

		if ( $option == true) {
			update_option( $widget_type, false, true);
		} else {
			update_option( $widget_type, true, true);
		}

		wp_die( "$widget_type is updated" );
	}

	public function filter_woocommerce_structured_data_review( $markup, $product ) {
		unset( $markup['aggregateRating'] );

		return $markup;
	}

	public function judgeme_all_reviews_count() {
		$count = get_option( 'judgeme_widget_all_reviews_count' );
		if ( ! isset( $count['all_reviews_count'] ) ) {
			return 0;
		}

		return $count['all_reviews_count'];
	}

	public function judgeme_all_reviews_rating() {
		$rating = get_option( 'judgeme_widget_all_reviews_rating' );
		if ( ! isset( $rating['all_reviews_rating'] ) ) {
			return 0;
		}

		return number_format( (float) $rating['all_reviews_rating'], 2, '.', ',' );
	}

	public function judgeme_remove_builtin_review_tab( $tabs ) {
		unset( $tabs['reviews'] );

		return $tabs;
	}

	public static function judgeme_preview_badge() {
		global $product;
		$id = $product->get_id();

		$body = get_post_meta( $id, '_judgeme_widget_preview_badge', true );
		if ( ! isset( $body['badge'] ) ) {
			$body = array( 'badge' => '' );
		}

		include JGM_PLUGIN_PATH . 'templates/judgeme/preview-badge-template.php';
	}

	public function judgeme_preview_badge_shortcode($atts) {
		global $product;

		$atts = shortcode_atts( array(
			'id' => -1
		), $atts, 'judgeme_review_atts' );

		$id = $atts['id'];

		if ( $id == -1 ) {
			if ( ! empty( $product ) ) {
				$id = $product->get_id();
			} else {
				return '';
			}
		}

		$body = get_post_meta( $id, '_judgeme_widget_preview_badge', true );

		if ( ! isset( $body['badge'] ) ) {
			$body = array( 'badge' => '' );
		}

		ob_start();
		include JGM_PLUGIN_PATH . 'templates/judgeme/preview-badge-template.php';
		$output = ob_get_clean();

		return $output;
	}

	public static function judgeme_review_widget() {
		global $product;
		$id = $product->get_id();

		$body = get_post_meta( $id, '_judgeme_widget_review_widget', true );

		if ( ! isset( $body['widget'] ) ) {
			JGM_ProductService::check_and_register_product( $id );
			$body = array( 'widget' => '' );
		}

		include JGM_PLUGIN_PATH . 'templates/judgeme/review-widget-template.php';
	}

	public function judgeme_review_widget_shortcode($atts) {
		global $product;

		$atts = shortcode_atts( array(
			'id' => -1
		), $atts, 'judgeme_review_atts' );

		$id = $atts['id'];

		if ( $id == -1 ) {
			if ( ! empty( $product ) ) {
				$id = $product->get_id();
			} else {
				return '';
			}
		} else {
			$product = wc_get_product( $id );
			if ( empty ( $product ) ) {
				return '';
			}
		}

		$body = get_post_meta( $id, '_judgeme_widget_review_widget', true );

		if ( ! isset( $body['widget'] ) ) {
			JGM_ProductService::check_and_register_product( $id );
			$body = array( 'widget' => '' );
		}

		ob_start();
		include JGM_PLUGIN_PATH . 'templates/judgeme/review-widget-template.php';
		$output = ob_get_clean();

		return $output;
	}

	public function judgeme_heading() {
		$setting = get_option( 'judgeme_widget_settings' );
		$jdgm_cdn_domain = constant( 'JGM_CDN_DOMAIN' );

		if ( empty( $setting ) ) {
			$setting = json_decode( wp_remote_retrieve_body( wp_safe_remote_get( "{$this->api_host}/widgets/settings?api_token={$this->token}&shop_domain={$this->domain}" ) ), true );
			if ( isset( $setting['settings'] ) ) {
				update_option( 'judgeme_widget_settings', $setting, true );
			} else {
				$setting = array(
					'setting' => ''
				);
			}
		}

		$html_miracle = $this->get_html_miracle();

		include JGM_PLUGIN_PATH . 'templates/judgeme/header-template.php';
	}

	public function judgeme_verified_badge() {
		$verified_badge = get_option( 'judgeme_widget_verified_badge' );

		if ( empty( $verified_badge ) ) {
			$verified_badge = json_decode( wp_remote_retrieve_body( wp_safe_remote_get( "{$this->api_host}/widgets/verified_badge?api_token={$this->token}&shop_domain={$this->domain}" ) ), true );
			update_option( 'judgeme_widget_verified_badge', $verified_badge, true );
		}

		return $verified_badge['verified_badge'];
	}

	public function judgeme_all_reviews() {
		$all_reviews = get_option( 'judgeme_widget_all_reviews' );

		if ( empty( $all_reviews ) ) {
			$all_reviews = json_decode( wp_remote_retrieve_body( wp_safe_remote_get( "{$this->api_host}/widgets/all_reviews_page?api_token={$this->token}&shop_domain={$this->domain}" ) ), true );
			update_option( 'judgeme_widget_all_reviews', $all_reviews, true );

		}
		$all_review_rating = get_option( 'judgeme_widget_all_reviews_rating' );

		$all_review_count = get_option( 'judgeme_widget_all_reviews_count' );

		$shop_name = get_bloginfo( 'name' );
		ob_start();
		include JGM_PLUGIN_PATH . 'templates/judgeme/all-reviews-template.php';
		$output = ob_get_clean();

		return $output;
	}

	public function judgeme_feature_carousel( $atts ) {
		$atts = shortcode_atts( array(
			'title'            => 'Let customers speak for us',
			'all-reviews-page' => '#'
		), $atts, 'judgeme_feature_carousel_atts' );

		$carousel = get_option( 'judgeme_widget_feature_carousel' );

		if ( empty( $carousel ) ) {
			$carousel = json_decode( wp_remote_retrieve_body( wp_safe_remote_get( "{$this->api_host}/widgets/featured_carousel?api_token={$this->token}&shop_domain={$this->domain}" ) ), true );
			update_option( 'judgeme_widget_feature_carousel', $carousel, true );
		}

		$all_review_rating = get_option( 'judgeme_widget_all_reviews_rating' );

		$all_review_count = get_option( 'judgeme_widget_all_reviews_count' );

		ob_start();
		include JGM_PLUGIN_PATH . 'templates/judgeme/feature-carousel-template.php';
		$output = ob_get_clean();

		return $output;

	}

	public function judgeme_review_tab( $atts ) {

		$atts = shortcode_atts( array(
			'button' => '★ Judge.me Reviews',
			'title'  => 'Let customers speak for us',
		), $atts, 'judgeme_review_atts' );

		$review_tab = get_option( 'judgeme_widget_reviews_tab' );

		if ( empty( $review_tab ) ) {
			$review_tab = json_decode( wp_remote_retrieve_body( wp_safe_remote_get( "{$this->api_host}/widgets/reviews_tab?api_token={$this->token}&shop_domain={$this->domain}" ) ), true );
			update_option( 'judgeme_widget_reviews_tab', $review_tab, true );
		}

		$all_review_rating = get_option( 'judgeme_widget_all_reviews_rating' );

		$all_review_count = get_option( 'judgeme_widget_all_reviews_count' );

		include JGM_PLUGIN_PATH . 'templates/judgeme/review-tab-template.php';
	}

	public function judgeme_ugc_media_grid() {
		$widget = get_option( 'judgeme_widget_ugc_media_grid' );

		if ( empty( $widget ) ) {
			$widget = json_decode( wp_remote_retrieve_body( wp_safe_remote_get( "{$this->api_host}/widgets/ugc_media_grid?api_token={$this->token}&shop_domain={$this->domain}" ) ), true );
			update_option( 'judgeme_widget_ugc_media_grid', $widget, true );

		}

		ob_start();
		include JGM_PLUGIN_PATH . 'templates/judgeme/ugc-media-grid-template.php';
		$output = ob_get_clean();

		return $output;
	}


	private function get_all_reviews_count() {
		$all_review_rating = get_option( 'judgeme_widget_all_reviews_rating' );
		$all_review_count  = get_option( 'judgeme_widget_all_reviews_count' );


		if ( empty( $all_review_count ) || empty( $all_review_rating ) ) {
			$all_reviews_count = json_decode( wp_remote_retrieve_body( wp_safe_remote_get( "{$this->api_host}/widgets/all_reviews_count?api_token={$this->token}&shop_domain={$this->domain}" ) ), true );
			update_option( 'judgeme_widget_all_reviews_count', $all_reviews_count, true );

			$all_reviews_rating = json_decode( wp_remote_retrieve_body( wp_safe_remote_get( "{$this->api_host}/widgets/all_reviews_rating?api_token={$this->token}&shop_domain={$this->domain}" ) ), true );
			update_option( 'judgeme_widget_all_reviews_rating', $all_reviews_rating, true );
		}
	}

	private function get_html_miracle() {
		$html_miracle = get_option( 'judgeme_widget_html_miracle' );

		if ( empty( $html_miracle ) ) {
			$html_miracle = json_decode( wp_remote_retrieve_body( wp_safe_remote_get( "{$this->api_host}/widgets/html_miracle?api_token={$this->token}&shop_domain={$this->domain}" ) ), true );
			if ( $html_miracle ) {
				$html_miracle = $html_miracle['html_miracle'];
				update_option( 'judgeme_widget_html_miracle', $html_miracle, true );
			}
		}

		return $html_miracle;
	}
}
