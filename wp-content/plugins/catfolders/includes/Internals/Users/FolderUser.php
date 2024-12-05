<?php
namespace CatFolders\Internals\Users;

use CatFolders\Core\Base;
use CatFolders\Models\OptionModel;
use CatFolders\Traits\Singleton;
use TenQuality\WP\Database\QueryBuilder;

defined( 'ABSPATH' ) || exit;

class FolderUser extends Base {
	public $needMerge = false;

	use Singleton;

	public function __construct() {
		if ( $this->should_run_merge() ) {
			add_action( 'wp_ajax_catf_run_merge', array( $this, 'run_merge' ) );
		}

		// add_filter( 'catf_folder_created_by', array( $this, 'get_folder_created_by' ) );
	}

	public function get_folder_created_by() {
		$userRestriction = OptionModel::get_option( 'userrestriction' );

		if ( '1' === $userRestriction ) {
			return get_current_user_id();
		}

		return 0;
	}

	public function should_run_merge() {
		$userRestriction = OptionModel::get_option( 'userrestriction' );

		if ( '1' == $userRestriction ) {
			$existingFolders = $this->get_restricted_folders();

			if ( $existingFolders ) {
				$this->needMerge = true;
			}
		}

		return $this->needMerge;
	}

	public function run_merge() {
		check_ajax_referer( 'catf_nonce', 'nonce', true );

		global $wpdb;

		if ( ! $this->needMerge ) {
			wp_send_json_error( array( 'message' => __( 'No folders to merge', 'catfolders' ) ) );
		}

		$result = $wpdb->query( "UPDATE {$wpdb->prefix}catfolders SET created_by = 0 WHERE created_by <> 0" );

		if ( $result ) {
			OptionModel::update_option( array( 'userrestriction' => '' ) );
			wp_send_json_success();
		}

		wp_send_json_error();
	}

	public function get_restricted_folders() {
		$query = QueryBuilder::create();

		$catf_table = self::CAT_FOLDERS_TABLE;

		$results = $query
			->select( 'id' )
			->from( $catf_table )
			->where(
				array(
					'created_by' => array(
						'operator' => '<>',
						'value'    => 0,
					),

				)
			)->limit( 1 )->get();

		return count( $results ) > 0;
	}
}
