<?php
/**
 * Plugin Name: Judge.me Product Reviews for WooCommerce
 * Plugin URI: https://judge.me/
 * Description: Judge.me collects, manages and displays reviews for your WooCommerce online shop, including photo reviews, in-email review, reminders, rich snippets, and much more.
 * Version: 1.3.22
 * Author: Judge.me
 * Author URI: https://judge.me
 * WC requires at least: 3.0
 * WC tested up to: 4.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$urlparts = wp_parse_url( site_url() );
$domain   = $urlparts['host'];

define( 'JGM_SHOP_DOMAIN', $domain );
define( 'JGM_API_HOST', 'https://judge.me/api/v1/' );
define( 'JGM_CORE_HOST', 'https://judge.me/' );
define( 'JGM_ADAPTER_HOST', 'https://woocommerce-adapter.judge.me' );
define( 'JGM_CDN_DOMAIN', 'cdn.judge.me' );
define( 'JGM_NAMESPACE', 'judgeme/v1' );
define( 'JGM_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'JGM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );
if ( ! class_exists( 'JudgeMe' ) ) {
	include JGM_PLUGIN_PATH . 'includes/class-judgeme.php';
}

if( !function_exists('is_plugin_active') ) {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

/**
 * Check if WooCommerce is active
 */
if ( !function_exists('is_plugin_active') || is_plugin_active( 'woocommerce/woocommerce.php') ) {

	add_action( 'init', 'judgeme_init', 9 );
	function judgeme_init() {
		new JudgeMe();

		// Register setting link to plugin list page.
		$plugin = plugin_basename( __FILE__ );
		add_filter( "plugin_action_links_$plugin", 'judgeme_add_settings_link' );
	}

	function judgeme_add_settings_link( $links ) {
		$settings_link = '<a href="admin.php?page=judgeme">' . __( 'Settings' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}
}
