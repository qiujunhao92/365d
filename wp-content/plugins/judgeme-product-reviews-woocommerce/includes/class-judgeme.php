<?php
/**
 * Judge.me Main class
 *
 * @author   Judge.me
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class JudgeMe {
	public function __construct() {
		$this->includes();
		$this->init();
	}

	private function includes() {
		include_once JGM_PLUGIN_PATH . 'includes/class-jgm-productservice.php';
		include_once JGM_PLUGIN_PATH . 'includes/class-jgm-reviewexporter.php';
		include_once JGM_PLUGIN_PATH . 'includes/class-jgm-config.php';
		include_once JGM_PLUGIN_PATH . 'includes/class-jgm-initilizer.php';
		include_once JGM_PLUGIN_PATH . 'includes/class-jgm-widget.php';
		include_once JGM_PLUGIN_PATH . 'includes/class-jgm-webhook.php';
		include_once JGM_PLUGIN_PATH . 'includes/class-jgm-hook.php';
	}

	private function init() {
		new JGM_Initilizer();
		new JGM_Webhook();
		new JGM_Config();
		$token = get_option( 'judgeme_shop_token' );
		if ( ! empty( $token ) ) {
			new JGM_ProductService();
			new JGM_Widget();
			new JGM_Hook();
			new JGM_ReviewExporter();
		}
	}
}
