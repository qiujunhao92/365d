<?php
/**
 * Judge.me Config class
 *
 * @author   Judge.me
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class JGM_Config {
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'judgeme_options_page' ) );
	}

	public function judgeme_options_page() {
		// add top level menu page
		$icon = JGM_PLUGIN_URL.'assets/images/jdgm-logo.png';
		add_menu_page(
			'Judge.me Reviews',
			'Judge.me',
			'manage_options',
			'judgeme',
			array( $this, 'judgeme_export_reviews_page_html' ),
			$icon
		);
	}

	public function judgeme_export_reviews_page_html() {
		$domain     = constant( 'JGM_SHOP_DOMAIN' );
		$token      = get_option( 'judgeme_shop_token' );
		$hmac       = hash_hmac( 'sha256', "no_iframe=1&platform=woocommerce&shop_domain={$domain}", $token, false );
		$url        = JGM_CORE_HOST . "index?no_iframe=1&shop_domain={$domain}&platform=woocommerce&hmac={$hmac}";
		$import_url = JGM_CORE_HOST . "import?no_iframe=1&shop_domain={$domain}&platform=woocommerce&hmac={$hmac}";
		$setting_url = JGM_CORE_HOST . "settings?no_iframe=1&shop_domain={$domain}&platform=woocommerce&hmac={$hmac}";
		include JGM_PLUGIN_PATH . 'templates/admin/style.php';
		include JGM_PLUGIN_PATH . 'templates/admin/export-reviews-template.php';
	}

}
