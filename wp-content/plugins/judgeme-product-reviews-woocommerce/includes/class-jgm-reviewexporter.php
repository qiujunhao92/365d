<?php
/**
 * JGM_ReviewExporter class
 *
 * @package judgeme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class JGM_ReviewExporter
 * Handle bulk exporting Woocommerce's native reviews data
 */
class JGM_ReviewExporter {

	const PER_PAGE = 400;

	/**
	 * Class construction
	 */
	public function __construct() {
		add_action( 'wp_ajax_jgm_export_reviews', array( $this, 'jgm_export_reviews' ) );
		add_action( 'admin_post_jgm_download_file', array( $this, 'jgm_download_file' ) );
	}

	/**
	 *  Download csv file and delete that file afterward
	 *
	 * @return void
	 */
	public function jgm_download_file() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}
		$file_name = 'woocommerce_reviews.csv';
		$file_path = JGM_PLUGIN_PATH . 'csv/' . $file_name;

		if ( file_exists( $file_path ) ) {
			header( 'Content-Description: File Transfer' );
			header( 'Content-Type: application/octet-stream' );
			header( 'Content-Disposition: attachment; filename=' . ( $file_name ) );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate' );
			header( 'Pragma: public' );
			header( 'Content-Length: ' . filesize( $file_path ) );
			ob_clean();
			flush();
			readfile( $file_path );
			//unlink( $file_path );
		} else {
			wp_die( 'The reviews is not exported yet.', 404 );
		}

		exit;
	}

	/**
	 * Bulk export reviews
	 *
	 * @return void
	 */
	public function jgm_export_reviews() {
		check_ajax_referer( 'jgm_export_reviews', 'security' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		$file_name = 'woocommerce_reviews.csv';

		if ( isset( $_POST['total_pages'] ) ) {
			$total_pages = (int) sanitize_text_field( $_POST['total_pages'] ); // Input var okay.
		} else {
			$count       = self::get_total_reviews_count();
			$total_pages = $count / self::PER_PAGE;
			$total_pages = (int) $total_pages;
			if ( $count % self::PER_PAGE ) {
				$total_pages ++;
			}
		}

		$page = (int) sanitize_text_field( $_POST['page'] );

		1 === $page ? $file_mode = 'w' : $file_mode = 'a';

		$fp = fopen( JGM_PLUGIN_PATH . 'csv/' . $file_name, $file_mode );

		if ( 1 === $page ) {
			fputcsv( $fp, array( 'body', 'author', 'email', 'date', 'product_id', 'product_handle', 'review_score' ) );
		}

		if ( $page <= $total_pages ) {
			$reviews = $this->get_reviews( $page );

			foreach ( $reviews as $review ) {
				fputcsv( $fp, $review );
			}

			$message = "Exported $page / $total_pages pages...";
			$page ++;
		} else {
			$message = 'Finished.';
		}

		fclose( $fp );

		$result = array(
			'filename'    => $file_name,
			'next_page'   => $page,
			'total_pages' => $total_pages,
			'message'     => $message,
		);

		echo wp_json_encode( $result );
		wp_die();
	}

	/**
	 * Return the list of reviews
	 *
	 * @param int $page page number.
	 *
	 * @return array reviews.
	 */
	private function get_reviews( $page ) {
		global $wpdb;
		$query = "SELECT comment_content AS body,
						 comment_author AS author,
						 comment_author_email AS email,
						 comment_date AS date,
						 comment_post_id AS product_id,
						 post_name AS product_handle,
						 meta_value AS review_score
				  FROM `{$wpdb->prefix}comments`
				  INNER JOIN `{$wpdb->prefix}posts` ON `{$wpdb->prefix}posts`.`ID` = `{$wpdb->prefix}comments`.`comment_post_ID`
				  INNER JOIN `{$wpdb->prefix}commentmeta` ON `{$wpdb->prefix}commentmeta`.`comment_id` = `{$wpdb->prefix}comments`.`comment_ID`
				  WHERE `post_type` = 'product' AND meta_key='rating'
				  AND `{$wpdb->prefix}comments`.`comment_approved` = 1
				  ORDER BY `comment_post_id` ASC LIMIT %d, %d";

		$offset  = ( $page - 1 ) * self::PER_PAGE;
		$reviews = $wpdb->get_results( $wpdb->prepare( $query, $offset, self::PER_PAGE ), ARRAY_N );

		return $reviews;
	}

	public static function get_total_reviews_count() {
		global $wpdb;
		$query = "SELECT count(*)
				  FROM `{$wpdb->prefix}comments`
				  INNER JOIN `{$wpdb->prefix}posts` ON `{$wpdb->prefix}posts`.`ID` = `{$wpdb->prefix}comments`.`comment_post_ID`
				  INNER JOIN `{$wpdb->prefix}commentmeta` ON `{$wpdb->prefix}commentmeta`.`comment_id` = `{$wpdb->prefix}comments`.`comment_ID`
				  WHERE `post_type` = 'product' AND meta_key='rating'
				  AND `{$wpdb->prefix}comments`.`comment_approved` = 1
				  ";
		$count = $wpdb->get_var( $query );

		return $count;
	}
}
