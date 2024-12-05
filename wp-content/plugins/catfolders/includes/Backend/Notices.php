<?php

namespace CatFolders\Backend;

use CatFolders\Models\FolderModel;

defined( 'ABSPATH' ) || exit;

class Notices {
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'wp_ajax_catf_first_folder', array( $this, 'first_folder_notice' ) );
		add_option(
			'catf_first_folder',
			array(
				'time'         => 0,
				'dismiss_time' => 0,
			)
		);
	}

	public function admin_notices() {
		global $pagenow;

		$countFolder = FolderModel::getFolderCount();

		if ( intval( $countFolder ) > 0 || 'upload.php' === $pagenow ) {
			return;
		}

		include_once CATF_PLUGIN_PATH . '/includes/Views/Notices/html-notice-first-folder.php';
	}

	public function first_folder_notice() {
		check_ajax_referer( 'catf_nonce', 'nonce', true );
		$optionFirstFolder = get_option( 'catf_first_folder_notice_dismiss' );
		update_option(
			'catf_first_folder_notice_dismiss',
			array(
				'time'         => time() + 14 * 60 * 60 * 24,
				'dismiss_time' => $optionFirstFolder['dismiss_time'] + 1,
			)
		); //After 3 months show
		wp_send_json_success();
	}
}
